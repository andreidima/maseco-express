<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\FlotaStatusInformatie;
use Carbon\Carbon;

class FlotaStatusInformatieController extends Controller
{
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->session()->get('flotaStatusInformatieReturnUrl') ?? $request->session()->put('flotaStatusInformatieReturnUrl', url()->previous());

        $flotaStatusInformatie = new FlotaStatusInformatie;

        return view('flotaStatusuriInformatii.create', compact('flotaStatusInformatie'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $flotaStatusInformatie = FlotaStatusInformatie::create($this->validateRequest($request));

        return redirect($request->session()->get('flotaStatusInformatieReturnUrl') ?? ('/flota-statusuri'))->with('status', 'Informația a fost adăugată cu succes!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\FlotaStatusInformatie  $flotaStatusInformatie
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, FlotaStatusInformatie $flotaStatusInformatie)
    {
        $request->session()->get('flotaStatusInformatieReturnUrl') ?? $request->session()->put('flotaStatusInformatieReturnUrl', url()->previous());

        return view('flotaStatusuriInformatii.edit', compact('flotaStatusInformatie'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\FlotaStatusInformatie  $flotaStatusInformatie
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, FlotaStatusInformatie $flotaStatusInformatie)
    {
        $flotaStatusInformatie->update($this->validateRequest($request));

        return redirect($request->session()->get('flotaStatusInformatieReturnUrl') ?? ('/flota-statusuri'))->with('status', 'Informația a fost modificată cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\FlotaStatusInformatie  $flotaStatusInformatie
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, FlotaStatusInformatie $flotaStatusInformatie)
    {
        $flotaStatusInformatie->delete();

        return back()->with('status', 'Informația a fost ștearsă cu succes!');
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

        return $request->validate(
            [
                'modalitate_de_plata' => 'nullable|max:255',
                'spot' => 'nullable|max:255',
                'termen' => 'nullable|max:255',
                'info' => 'nullable|max:255',
                'info_2' => 'nullable|max:255',
            ],
            [
                // 'tara_id.required' => 'Câmpul țara este obligatoriu'
            ]
        );
    }
}
