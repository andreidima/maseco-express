<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comanda;
use App\Models\ComandaIstoric;
use App\Models\Firma;
use App\Models\Limba;
use App\Models\Moneda;
use App\Models\ProcentTVA;
use App\Models\MetodaDePlata;
use App\Models\TermenDePlata;
use App\Models\Camion;
use App\Models\LocOperare;

use Carbon\Carbon;

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
        $searchTransportatorId = $request->searchTransportatorId;
        $searchClientId = $request->searchClientId;

        $query = Comanda::with('client')
            ->when($searchDataCreare, function ($query, $searchDataCreare) {
                return $query->whereDate('data_creare', $searchDataCreare);
            })
            ->when($searchTransportatorContract, function ($query, $searchTransportatorContract) {
                return $query->where('transportator_contract', 'like', '%' . $searchTransportatorContract . '%');
            })
            ->when($searchTransportatorId, function ($query, $searchTransportatorId) {
                return $query->whereHas('transportator', function ($query) use ($searchTransportatorId) {
                    $query->where('id', $searchTransportatorId);
                });
            })
            ->when($searchClientId, function ($query, $searchClientId) {
                return $query->whereHas('client', function ($query) use ($searchClientId) {
                    $query->where('id', $searchClientId);
                });
            })
            ->latest();

        $comenzi = $query->simplePaginate(25);

        $firmeClienti = Firma::select('id', 'nume')->where('tip_partener', 1)->orderBy('nume')->get();
        $firmeTransportatori = Firma::select('id', 'nume')->where('tip_partener', 2)->orderBy('nume')->get();

        return view('comenzi.index', compact('comenzi', 'firmeClienti', 'firmeTransportatori', 'searchDataCreare', 'searchTransportatorContract', 'searchTransportatorId', 'searchClientId'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $comanda = new Comanda;
        $comanda->transportator_contract = 'MSX-' . ( (preg_replace('/[^0-9]/', '', Comanda::latest()->first()->transportator_contract ?? '0') ) + 1);
        $comanda->data_creare = Carbon::today();
        $comanda->transportator_limba_id = 1; // Romana
        $comanda->transportator_tarif_pe_km = 0;
        $comanda->client_limba_id = 1; // Romana
        $comanda->client_tarif_pe_km = 0;
        $comanda->save();

        return redirect( $comanda->path() . '/modifica');

        // $firmeClienti = Firma::select('id', 'nume')->where('tip_partener', 1)->orderBy('nume')->get();
        // $firmeTransportatori = Firma::select('id', 'nume')->where('tip_partener', 2)->orderBy('nume')->get();
        // $limbi = Limba::select('id', 'nume')->get();

        // $request->session()->get('ComandaReturnUrl') ?? $request->session()->put('ComandaReturnUrl', url()->previous());

        // return view('comenzi.create', compact('firmeClienti', 'firmeTransportatori', 'limbi'));
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

        return redirect($request->session()->get('ComandaReturnUrl') ?? ('/comenzi'))->with('status', 'Comanda „' . $comanda->transportator_contract . '” a fost adăugată cu succes!');
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
        $firmeClienti = Firma::select('id', 'nume')->where('tip_partener', 1)->orderBy('nume')->get();
        $firmeTransportatori = Firma::select('id', 'nume')->where('tip_partener', 2)->orderBy('nume')->get();
        $limbi = Limba::select('id', 'nume')->get();
        $monede = Moneda::select('id', 'nume')->get();
        $procenteTVA = ProcentTVA::select('id', 'nume')->get();
        $metodeDePlata = MetodaDePlata::select('id', 'nume')->get();
        $termeneDePlata = TermenDePlata::select('id', 'nume')->get();
        $camioane = Camion::select('id', 'numar_inmatriculare', 'tip_camion')->orderBy('numar_inmatriculare')->get();
        // $locuriOperare = LocOperare::select('id', 'nume')->orderBy('nume')->get();
        $locuriOperare = LocOperare::select('*')->orderBy('nume')->get();

        $request->session()->get('ComandaReturnUrl') ?? $request->session()->put('ComandaReturnUrl', url()->previous());

        return view('comenzi.edit', compact('comanda', 'firmeClienti', 'firmeTransportatori', 'limbi', 'monede', 'procenteTVA', 'metodeDePlata', 'termeneDePlata', 'camioane', 'locuriOperare'));
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
        // if ($comanda->wasChanged()){
        //     $comanda_istoric = new ComandaIstoric;
        //     $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        //     $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
        //     $comanda_istoric->operare_descriere = 'Modificare';
        //     $comanda_istoric->save();
        // }

        return redirect($request->session()->get('ComandaReturnUrl') ?? ('/comenzi'))->with('status', 'Comanda „' . $comanda->transportator_contract . '” a fost salvată cu succes!');
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
        // $comanda_istoric = new ComandaIstoric;
        // $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        // $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
        // $comanda_istoric->operare_descriere = 'Stergere';
        // $comanda_istoric->save();

        $comanda->delete();

        return back()->with('status', 'Comanda „' . $comanda->transportator_contract . '” a fost ștearsă cu succes!');
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
                // 'transportator_contract' => 'required|max:20',
                'transportator_limba_id' => '',
                'transportator_valoare_contract' => 'nullable|numeric|min:-9999999|max:9999999',
                'transportator_moneda_id' => '',
                'transportator_zile_scadente' => 'nullable|numeric|min:-100|max:300',
                'transportator_termen_plata_id' => '',
                'transportator_transportator_id' => 'required',
                'transportator_procent_tva_id' => '',
                'transportator_metoda_de_plata_id' => '',
                'transportator_tarif_pe_km' => '',
                'client_contract' => 'nullable|max:20',
                'client_limba_id' => '',
                'client_valoare_contract' => 'nullable|numeric|min:-9999999|max:9999999',
                'client_moneda_id' => '',
                'client_zile_scadente' => 'nullable|numeric|min:-100|max:300',
                'client_termen_plata_id' => '',
                'client_client_id' => 'required',
                'client_procent_tva_id' => '',
                'client_metoda_de_plata_id' => '',
                'client_tarif_pe_km' => '',
                'descriere_marfa' => 'nullable|max:500',
                'camion_id' => '',
                // 'observatii' => 'nullable|max:2000',
            ],
            [
                'transportator_transportator_id.required' => 'Câmpul Transportator este obligatoriu',
                'client_client_id.required' => 'Câmpul Client este obligatoriu'
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
