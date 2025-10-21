<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use App\Models\Role;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->session()->forget('userReturnUrl');

        $searchNume = $request->searchNume;

        $useri = User::with('roles')
            ->when($searchNume, function ($query, $searchNume) {
                return $query->where('name', 'like', '%' . $searchNume . '%');
            })
            ->where('id', '>', 1) // se sare pentru user 1, Andrei Dima
            ->orderBy('activ', 'desc')
            ->orderByPrimaryRole()
            ->orderBy('name')
            ->simplePaginate(100);

        return view('useri.index', compact('useri', 'searchNume'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->session()->get('userReturnUrl') ?? $request->session()->put('userReturnUrl', url()->previous());

        $user = new User();
        $roles = $this->availableRolesForUser();

        return view('useri.create', compact('user', 'roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = $this->validateRequest($request);
        $roleId = (int) Arr::pull($data, 'role');
        $data['password'] = Hash::make($data['password']);

        $user = DB::transaction(function () use ($data, $roleId) {
            $user = User::create($data);
            $user->roles()->sync([$roleId]);

            return $user;
        });

        return redirect($request->session()->get('userReturnUrl') ?? ('/utilizatori'))->with('status', 'Utilizatorul „' . $user->name . '” a fost adăugat cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, User $user)
    {
        $request->session()->get('userReturnUrl') ?? $request->session()->put('userReturnUrl', url()->previous());

        $user->load('roles');

        return view('useri.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, User $user)
    {
        $request->session()->get('userReturnUrl') ?? $request->session()->put('userReturnUrl', url()->previous());

        $user->load('roles');
        $roles = $this->availableRolesForUser($user);

        return view('useri.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user)
    {
        $data = $this->validateRequest($request, $user);
        $roleId = (int) Arr::pull($data, 'role');

        if (array_key_exists('password', $data)) {
            if (is_null($data['password'])) {
                unset($data['password']);
            } else {
                $data['password'] = Hash::make($data['password']);
            }
        }

        DB::transaction(function () use ($user, $data, $roleId) {
            $user->update($data);
            $user->roles()->sync([$roleId]);
        });

        return redirect($request->session()->get('userReturnUrl') ?? ('/utilizatori'))->with('status', 'Utilizatorul „' . $user->name . '” a fost modificat cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, User $user)
    {
        $user->delete();

        return back()->with('status', 'Utilizatorul „' . $user->name . '” a fost șters cu success!');
    }

    /**
     * Validate the request attributes.
     *
     * @return array
     */
    protected function validateRequest(Request $request, ?User $targetUser = null)
    {
        $allowSuperAdmin = $targetUser && $targetUser->hasRole('super-admin');

        // Se adauga userul doar la adaugare, iar la modificare nu se schimba
        // if ($request->isMethod('post')) {
        //     $request->request->add(['user_id' => $request->user()->id]);
        // }

        // if ($request->isMethod('post')) {
        //     $request->request->add(['cheie_unica' => uniqid()]);
        // }
// dd($request, $request->isMethod('post'));
        return $request->validate(
            [
                'role' => [
                    'required',
                    'integer',
                    Rule::exists('roles', 'id')->where(function ($query) use ($allowSuperAdmin) {
                        if (! $allowSuperAdmin) {
                            $query->where('slug', '!=', 'super-admin');
                        }
                    }),
                ],
                'name' => 'required|max:255',
                'telefon' => 'nullable|max:50',
                'email' => 'required|max:255|email:rfc,dns|unique:users,email,' . $request->id,
                'password' => ($request->isMethod('POST') ? 'required' : 'nullable') . '|min:8|max:255|confirmed',
                'activ' => 'required',
            ],
            [
                'password.required' => 'Câmpul parola este obligatoriu.',
                'password.max' => 'Câmpul parola nu poate conține mai mult de 255 de caractere.',
            ]
        );
    }

    protected function availableRolesForUser(?User $user = null)
    {
        $query = Role::query()->orderBy('id');

        if ($user && $user->hasRole('super-admin')) {
            return $query->get();
        }

        return $query->where('slug', '!=', 'super-admin')->get();
    }
}
