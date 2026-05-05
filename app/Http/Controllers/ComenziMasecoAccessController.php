<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComenziMasecoAccessController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        $configuredToken = (string) config('app.maseco_comenzi_token', '');
        $userId = (int) config('app.maseco_comenzi_user_id');

        if ($configuredToken === '' || ! hash_equals($configuredToken, $token)) {
            abort(404);
        }

        $user = User::query()
            ->whereKey($userId)
            ->where('activ', 1)
            ->first();

        abort_unless($user, 404);

        Auth::login($user);

        $request->session()->regenerate();
        $request->session()->put('maseco_presentation_mode', true);

        return redirect('/comenzi');
    }
}
