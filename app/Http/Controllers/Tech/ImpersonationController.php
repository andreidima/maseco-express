<?php

namespace App\Http\Controllers\Tech;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ImpersonationController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));

        $users = User::query()
            ->with('roles')
            ->where('activ', 1)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('activ', 'desc')
            ->orderByPrimaryRole()
            ->orderBy('name')
            ->simplePaginate(100)
            ->appends(['search' => $search]);

        return view('tech.impersonation.index', [
            'users' => $users,
            'search' => $search,
            'isImpersonating' => $request->session()->has('impersonated_by'),
            'activeUserId' => Auth::id(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $targetUser = User::query()->findOrFail($validated['user_id']);
        $currentUser = $request->user();

        if ($currentUser && $targetUser->is($currentUser)) {
            return redirect()
                ->route('tech.impersonation.index')
                ->with('impersonation_status', 'Ești deja autentificat ca acest utilizator.');
        }

        $request->session()->put('impersonated_by', $currentUser?->id);
        $request->session()->put('impersonated_by_name', $currentUser?->name);

        Auth::login($targetUser);

        if ($targetUser->hasRole('mecanic')) {
            return redirect()->route('service-masini.index');
        }

        return redirect('/');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $originalUserId = $request->session()->pull('impersonated_by');
        $originalUserName = $request->session()->pull('impersonated_by_name');

        if ($originalUserId) {
            $originalUser = User::find($originalUserId);

            if ($originalUser) {
                Auth::login($originalUser);

                return redirect('/')
                    ->with('impersonation_status', sprintf('Ai revenit la contul %s.', $originalUserName ?? $originalUser->name));
            }
        }

        Auth::logout();

        return redirect('/');
    }
}
