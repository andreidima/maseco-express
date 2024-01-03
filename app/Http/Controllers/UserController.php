<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

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

        $useri = User::
            when($searchNume, function ($query, $searchNume) {
                return $query->where('name', 'like', '%' . $searchNume . '%');
            })
            ->where('id', '>', 1) // se sare pentru user 1, Andrei Dima
            ->orderBy('activ', 'desc')
            ->orderBy('role')
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

        return view('useri.create');
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
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

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

        return view('useri.edit', compact('user'));
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
        $data = $this->validateRequest($request);
        if (is_null($data['password'])){
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }
        $user->update($data);

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
    protected function validateRequest(Request $request)
    {
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
                'role' => 'required',
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
}
