<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comanda;
use App\Models\ComandaIstoric;
use App\Models\Firma;
use App\Models\Limba;

class ComandaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->session()->forget('ComandaReturnUrl');

        $searchDataCreare = $request->searchDataCreare;
        $searchTransportatorContract = $request->searchTransportatorContract;
        $searchTransportatorNume = $request->searchTransportatorNume;

        $query = Comanda::with('firma')
            ->when($searchDataCreare, function ($query, $searchDataCreare) {
                return $query->where('data_creare', 'like', '%' . $searchDataCreare . '%');
            })
            ->when($searchTransportatorContract, function ($query, $searchTransportatorContract) {
                return $query->where('telefon', 'like', '%' . $searchTransportatorContract . '%');
            })
            ->when($searchTransportatorNume, function ($query, $searchTransportatorNume) {
                return $query->whereHas('firme', function ($query, $searchTransportatorNume) {
                    $query->where('nume', 'like', '%' . $searchTransportatorNume . '%');
                });
            })
            ->latest();

        $comenzi = $query->simplePaginate(25);

        return view('comenzi.index', compact('comenzi', 'searchDataCreare', 'searchTransportatorContract', 'searchTransportatorNume'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // dd($request);
        // $comanda = new Comanda;
        // $comanda->transportator_contract = 'MSX-' . ( (preg_replace('/[^0-9]/', '', Comanda::latest()->first()->transportator_contract ?? '0') ) + 1);
        // $comanda->save();

        $firmeClienti = Firma::select('id', 'nume')->where('tip_partener', 1)->orderBy('nume')->get();
        $firmeTransportatori = Firma::select('id', 'nume')->where('tip_partener', 2)->orderBy('nume')->get();
        $limbi = Limba::select('id', 'nume')->get();

        $request->session()->get('ComandaReturnUrl') ?? $request->session()->put('ComandaReturnUrl', url()->previous());

        return view('comenzi.create', compact('firmeClienti', 'firmeTransportatori', 'limbi'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $comanda = Comanda::create($this->validateRequest($request));

        // Salvare in istoric
        $comanda_istoric = new ComandaIstoric;
        $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
        $comanda_istoric->operare_descriere = 'Adaugare';
        $comanda_istoric->save();

        return redirect($request->session()->get('ComandaReturnUrl') ?? ('/comenzi'))->with('status', 'Comandaul „' . ($comanda->numar_inmatriculare ?? '') . '” a fost adăugat cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Comanda  $comanda
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Comanda $comanda)
    {
        $this->authorize('update', $comanda);

        $request->session()->get('ComandaReturnUrl') ?? $request->session()->put('ComandaReturnUrl', url()->previous());

        return view('comenzi.show', compact('comanda'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Comanda  $comanda
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Comanda $comanda)
    {
        $firme = Firma::select('id', 'nume')->where('tip_partener', 2)->orderBy('nume')->get();
        $tipuriCamioane = Comanda::select('tip_comanda')->distinct()->orderBy('tip_comanda')->get();

        $request->session()->get('ComandaReturnUrl') ?? $request->session()->put('ComandaReturnUrl', url()->previous());

        return view('comenzi.edit', compact('comanda', 'firme', 'tipuriCamioane'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Comanda  $comanda
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Comanda $comanda)
    {
        $comanda->update($this->validateRequest($request));

        // Salvare in istoric
        if ($comanda->wasChanged()){
            $comanda_istoric = new ComandaIstoric;
            $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
            $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
            $comanda_istoric->operare_descriere = 'Modificare';
            $comanda_istoric->save();
        }

        return redirect($request->session()->get('ComandaReturnUrl') ?? ('/comenzi'))->with('status', 'Comandaul „' . ($comanda->numar_inmatriculare ?? '') . '” a fost modificat cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Comanda  $comanda
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Comanda $comanda)
    {
        // Salvare in istoric
        $comanda_istoric = new ComandaIstoric;
        $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
        $comanda_istoric->operare_descriere = 'Stergere';
        $comanda_istoric->save();

        $comanda->delete();

        return back()->with('status', 'Comandaul „' . ($comanda->numar_inmatriculare ?? '') . '” a fost șters cu succes!');
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
                'data_creare' => 'required',
                'transportator_contract' => 'required|max:20',
                'transportator_limba_id' => 'required',
                'transportator_valoare_contract' => 'numeric|between:-9999999,9999999',
                'transportator_moneda_id' => 'required',
                'transportator_zile_scadente' => 'nullable|numeric',
                'transportator_termen_plata_id' => '',
                'transportator_transportator_id' => '',
                'transportator_procent_tva_id' => '',
                'transportator_metoda_de_plata' => '',
                'transportator_tarif_pe_km' => '',
                // 'observatii' => 'nullable|max:2000',
            ],
            [
                // 'tara_id.required' => 'Câmpul țara este obligatoriu'
            ]
        );
    }

    // public function restaurareIstoric(Request $request, Comanda $comanda = null, ComandaIstoric $comanda_istoric = null){
    //     $comanda->nume = $comanda_istoric->nume;
    //     $comanda->telefon = $comanda_istoric->telefon;
    //     $comanda->adresa = $comanda_istoric->adresa;
    //     $comanda->status = $comanda_istoric->status;
    //     $comanda->intrare = $comanda_istoric->intrare;
    //     $comanda->lansare = $comanda_istoric->lansare;
    //     $comanda->oferta_pret = $comanda_istoric->oferta_pret;
    //     $comanda->avans = $comanda_istoric->avans;
    //     $comanda->observatii = $comanda_istoric->observatii;
    //     $comanda->user_id = $comanda_istoric->user_id;

    //     $comanda->save();

    //     // Salvare in istoric
    //     if ($comanda->wasChanged()){
    //         $comanda_istoric = new ComandaIstoric;
    //         $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
    //         $comanda_istoric->operatie = 'Modificare';
    //         $comanda_istoric->operatie_user_id = auth()->user()->id ?? null;
    //         $comanda_istoric->save();
    //         return redirect($request->session()->get('ComandaReturnUrl') ?? ('/comenzi'))->with('status', 'Comanda „' . ($comanda->nume ?? '') . '” a fost restaurată cu succes!');
    //     } else {
    //         return redirect($request->session()->get('ComandaReturnUrl') ?? ('/comenzi'))->with('warning', 'Comanda „' . ($comanda->nume ?? '') . '” nu a avut nimic de restaurat!');
    //     }

    // }
}
