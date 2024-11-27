<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Intermediere;
use App\Models\Comanda;
use App\Models\User;
use Carbon\Carbon;

class IntermediereController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->session()->forget('intermediereReturnUrl');

        $searchUser = $request->searchUser;
        // $searchLuna = $request->searchLuna ?? Carbon::now()->month;
        // $searchAn = $request->searchAn ?? Carbon::now()->year;
        $searchInterval = $request->searchInterval;

        $comenzi = Comanda::with('intermediere', 'user:id,name', 'client:id,nume', 'transportator:id,nume', 'camion:id,numar_inmatriculare', 'clientMoneda', 'transportatorMoneda', 'locuriOperareIncarcari', 'factura:id,client_nume,client_contract,seria,numar,data,data_plata_transportator')
            ->when($searchUser, function ($query, $searchUser) {
                return $query->whereHas('user', function ($query) use ($searchUser) {
                    $query->where('id', $searchUser);
                });
            }, function ($q) { // return nothing if there is no user selected
                return $q->where('id', -1);
            })
            // ->when($searchLuna, function ($query, $searchLuna) {
            //     $query->whereMonth('data_creare', $searchLuna);
            // })
            // ->when($searchAn, function ($query, $searchAn) {
            //     $query->whereYear('data_creare', $searchAn);
            // })
            ->when($searchInterval, function ($query, $searchInterval) {
                return $query->whereBetween('data_creare', [strtok($searchInterval, ','), strtok( '' )]);
            })
            // ->latest()
            ->orderBy('data_creare')
            ->simplePaginate(100);


        $useri = User::select('id' , 'name')->where('name', '<>', 'Andrei Dima')->where('activ', 1)->orderBy('name')->get();

        return view('intermedieri.index', compact('comenzi', 'useri', 'searchUser', 'searchInterval'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->session()->get('intermediereReturnUrl') ?? $request->session()->put('intermediereReturnUrl', url()->previous());

        $intermediere = new Intermediere;
        $intermediere->comanda_id = $request->comandaId;

        $intermediere->save();

        return redirect('/intermedieri/' . $intermediere->id . '/modifica');
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Intermediere  $intermediere
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Intermediere $intermediere)
    {
        $request->session()->get('intermediereReturnUrl') ?? $request->session()->put('intermediereReturnUrl', url()->previous());

        return view('intermedieri.edit', compact('intermediere'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Intermediere  $intermediere
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Intermediere $intermediere)
    {
        $intermediere->update($this->validateRequest($request));

        return redirect($request->session()->get('intermediereReturnUrl') ?? ('/intermedieri'))->with('status', 'Intermedierea pentru comanda„' . ($intermediere->comanda->transportator_contract ?? '') . '” a fost modificată cu succes!');
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
                'aplicatie' => 'nullable|max:255',
                'observatii' => 'nullable|max:2000',
                'motis' => 'nullable|max:255',
                'dkv' => 'nullable|max:255',
            ],
            [
                // 'tara_id.required' => 'Câmpul țara este obligatoriu'
            ]
        );
    }
}
