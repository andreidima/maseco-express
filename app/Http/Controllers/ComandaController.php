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
        $comanda->user_id = $request->user()->id;
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
        // $locuriOperare = LocOperare::select('*')->orderBy('nume')->get();
        $incarcari = $comanda->locuriOperareIncarcari()->get();
        $descarcari = $comanda->locuriOperareDescarcari()->get();

        $request->session()->get('ComandaReturnUrl') ?? $request->session()->put('ComandaReturnUrl', url()->previous());

        return view('comenzi.edit', compact('comanda', 'firmeClienti', 'firmeTransportatori', 'limbi', 'monede', 'procenteTVA', 'metodeDePlata', 'termeneDePlata', 'camioane', 'incarcari', 'descarcari'));
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
        // dd($request->request);
        // dd($request->request, $request->except(['incarcari']), $this->validateRequest($request));
        $comanda_campuri = $this->validateRequest($request);
        unset($comanda_campuri['incarcari']);
        unset($comanda_campuri['descarcari']);
        $comanda->update($comanda_campuri);


        // // Incarcari
        //     // Se verifica daca exista duplicate in locatiile vechi din baza de date
        //     if ($comanda->locuriOperareIncarcari()->count() > 0) {
        //         foreach ($comanda->locuriOperareIncarcari()->get() as $key=>$locOperare){
        //             $temp_array[$key] = $locOperare->id;
        //         }
        //         $temp_array = array_unique($temp_array);
        //         $existaDuplicateInIncarcarileVechi = sizeof($temp_array) != sizeof($comanda->locuriOperare()->get());
        //     }

        //     // Se verifica daca exista duplicate in locatiile noi
        //     if (count($request->incarcari) > 0) {
        //         for ($i = 0; $i < count($request->incarcari); $i++) {
        //             $temp_array[$i] = ($request->incarcari[$i]['id']);
        //         }
        //         $temp_array = array_unique($temp_array);
        //         $existaDuplicateInIncarcarileNoi = sizeof($temp_array) != sizeof($request->incarcari);
        //     }

        //     // Daca exista duplicate, in locatiile vechi sau noi, se creaza un array, cu index incepand cu 0, care la sync va sterge toate lociile vechi si apoi va readauga toate locatiile noi
        //     // Daca nu exista duplicate, se creaza un array, cu index id-ul locatiilor, care la sync va face update doar daca este cazul
        //     if ((isset($existaDuplicateInIncarcarileVechi) && ($existaDuplicateInIncarcarileVechi > 0)) || (isset($existaDuplicateInIncarcarileNoi) && ($existaDuplicateInIncarcarileNoi > 0))) {
        //         for ($i = 0; $i < count($request->incarcari); $i++) {
        //             $incarcari_id_array[$i] = ['loc_operare_id' => intval($request->incarcari[$i]['id']), 'tip' => 1, 'ordine' => $i+1, 'data_ora' => $request->incarcari[$i]['pivot']['data_ora'], 'observatii' => $request->incarcari[$i]['pivot']['observatii']];
        //         }
        //     } else {
        //         for ($i = 0; $i < count($request->incarcari); $i++) {
        //             $incarcari_id_array[$request->incarcari[$i]['id']] = ['tip' => 1, 'ordine' => $i+1, 'data_ora' => $request->incarcari[$i]['pivot']['data_ora'], 'observatii' => $request->incarcari[$i]['pivot']['observatii']];
        //         }
        //     }
        //     $comanda->locuriOperareIncarcari()->sync($incarcari_id_array);


        // // Descarcari
        //     // Se verifica daca exista duplicate in locatiile vechi din baza de date
        //     if ($comanda->locuriOperareDescarcari()->count() > 0) {
        //         foreach ($comanda->locuriOperareDescarcari()->get() as $key=>$locOperare){
        //             $temp_array[$key] = $locOperare->id;
        //         }
        //         $temp_array = array_unique($temp_array);
        //         $existaDuplicateInDescarcarileVechi = sizeof($temp_array) != sizeof($comanda->locuriOperare()->get());
        //     }

        //     // Se verifica daca exista duplicate in locatiile noi
        //     if (count($request->descarcari) > 0) {
        //         for ($i = 0; $i < count($request->descarcari); $i++) {
        //             $temp_array[$i] = ($request->descarcari[$i]['id']);
        //         }
        //         $temp_array = array_unique($temp_array);
        //         $existaDuplicateInDescarcarileNoi = sizeof($temp_array) != sizeof($request->descarcari);
        //     }

        //     // Daca exista duplicate, in locatiile vechi sau noi, se creaza un array, cu index incepand cu 0, care la sync va sterge toate lociile vechi si apoi va readauga toate locatiile noi
        //     // Daca nu exista duplicate, se creaza un array, cu index id-ul locatiilor, care la sync va face update doar daca este cazul
        //     if ((isset($existaDuplicateInDescarcarileVechi) && ($existaDuplicateInDescarcarileVechi > 0)) || (isset($existaDuplicateInDescarcarileNoi) && ($existaDuplicateInDescarcarileNoi > 0))) {
        //         for ($i = 0; $i < count($request->descarcari); $i++) {
        //             $descarcari_id_array[$i] = ['loc_operare_id' => intval($request->descarcari[$i]['id']), 'tip' => 2, 'ordine' => $i+1, 'data_ora' => $request->descarcari[$i]['pivot']['data_ora'], 'observatii' => $request->descarcari[$i]['pivot']['observatii']];
        //         }
        //     } else {
        //         for ($i = 0; $i < count($request->descarcari); $i++) {
        //             $descarcari_id_array[$request->descarcari[$i]['id']] = ['tip' => 2, 'ordine' => $i+1, 'data_ora' => $request->descarcari[$i]['pivot']['data_ora'], 'observatii' => $request->descarcari[$i]['pivot']['observatii']];
        //         }
        //     }
        //     $comanda->locuriOperareDescarcari()->sync($descarcari_id_array);
        //     dd($request->request, $incarcari_id_array, $descarcari_id_array);


        // Incarcari
            // 1. Se verifica daca exista duplicate in locatiile vechi din baza de date
            if ($comanda->locuriOperare()->count() > 0) {
                foreach ($comanda->locuriOperare()->get() as $key=>$locOperare){
                    $temp_array[$key] = $locOperare->id;
                }
                $temp_array = array_unique($temp_array);
                $existaDuplicateInLocatiileVechi = sizeof($temp_array) != sizeof($comanda->locuriOperare()->get());
            }

            // 2. Daca nu sunt duplicate in locatiile vechi din baza de date, se verifica daca exista in cele ce urmeaza a fi introduse
            if (!(isset($existaDuplicateInLocatiileVechi) && ($existaDuplicateInLocatiileVechi > 0))){
                $temp_array = [];
                if (($request->incarcari && (count($request->incarcari) > 0)) || ($request->descarcari && (count($request->descarcari) > 0))) {
                    if ($request->incarcari){
                        for ($i = 0; $i < count($request->incarcari); $i++) {
                            $temp_array[$i] = ($request->incarcari[$i]['id']);
                        }
                    }
                    if ($request->descarcari){
                        for ($i = 0; $i < count($request->descarcari); $i++) {
                            $temp_array[count($temp_array) ?? 0] = ($request->descarcari[$i]['id']);
                        }
                    }
                    $temp_array = array_unique($temp_array);
                    $existaDuplicateInLocatiileNoi = sizeof($temp_array) != (sizeof($request->incarcari) + sizeof($request->descarcari));
                }
            }
// dd($existaDuplicateInLocatiileNoi);
            // Daca exista duplicate, in locatiile vechi sau noi, se creaza un array, cu index incepand cu 0, care la sync va sterge toate lociile vechi si apoi va readauga toate locatiile noi
            // Daca nu exista duplicate, se creaza un array, cu index id-ul locatiilor, care la sync va face update doar daca este cazul
            if ((isset($existaDuplicateInLocatiileVechi) && ($existaDuplicateInLocatiileVechi)) || (isset($existaDuplicateInLocatiileNoi) && ($existaDuplicateInLocatiileNoi))) {
                if ($request->incarcari){
                    for ($i = 0; $i < count($request->incarcari); $i++) {
                        $locatii_id_array[$i] = ['loc_operare_id' => intval($request->incarcari[$i]['id']), 'tip' => 1, 'ordine' => $i+1, 'data_ora' => $request->incarcari[$i]['pivot']['data_ora'], 'observatii' => $request->incarcari[$i]['pivot']['observatii'], 'referinta' => $request->incarcari[$i]['pivot']['referinta']];
                    }
                }
                if ($request->descarcari){
                    for ($i = 0; $i < count($request->descarcari); $i++) {
                        $locatii_id_array[count($locatii_id_array)] = ['loc_operare_id' => intval($request->descarcari[$i]['id']), 'tip' => 2, 'ordine' => $i+1, 'data_ora' => $request->descarcari[$i]['pivot']['data_ora'], 'observatii' => $request->descarcari[$i]['pivot']['observatii'], 'referinta' => $request->descarcari[$i]['pivot']['referinta']];
                    }
                }
            } else {
                if ($request->incarcari){
                    for ($i = 0; $i < count($request->incarcari); $i++) {
                        $locatii_id_array[$request->incarcari[$i]['id']] = ['tip' => 1, 'ordine' => $i+1, 'data_ora' => $request->incarcari[$i]['pivot']['data_ora'], 'observatii' => $request->incarcari[$i]['pivot']['observatii'], 'referinta' => $request->incarcari[$i]['pivot']['referinta']];
                    }
                }
                if ($request->descarcari){
                    for ($i = 0; $i < count($request->descarcari); $i++){
                        $locatii_id_array[$request->descarcari[$i]['id']] = ['tip' => 2, 'ordine' => $i+1, 'data_ora' => $request->descarcari[$i]['pivot']['data_ora'], 'observatii' => $request->descarcari[$i]['pivot']['observatii'], 'referinta' => $request->descarcari[$i]['pivot']['referinta']];
                    }
                }
            }

            if (isset($locatii_id_array)){
                $comanda->locuriOperare()->sync($locatii_id_array);
            }



        // for ($i = 0; $i < count($request->incarcari['id']); $i++) {
        //     $comanda->locuriOperare()->sync($request->incarcari['id'][$i], ['tip' => 1, 'ordine' => $i+1]);
        // }


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

        $comanda->locuriOperare()->detach();
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

        // dd($request->request);

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

                'incarcari.*.id' => 'required',
                // 'incarcari.*.nume' => 'required|max:500',
                // 'incarcari.*.oras' => 'nullable|max:500',
                'incarcari.*.pivot.data_ora' => 'required',

                'descarcari.*.id' => 'required',
                'descarcari.*.pivot.data_ora' => 'required',

                // 'observatii' => 'nullable|max:2000',
            ],
            [
                'transportator_transportator_id.required' => 'Câmpul Transportator este obligatoriu',
                'client_client_id.required' => 'Câmpul Client este obligatoriu',
                'incarcari.*.id' => 'Încărcarea #:position este obligatoriu de selectat din baza de date',
                'incarcari.*.pivot.data_ora' => 'Câmpul Data și ora pentru încărcarea #:position este obligatoriu',
                'descarcari.*.id' => 'Descărcarea #:position este obligatoriu de selectat din baza de date',
                'descarcari.*.pivot.data_ora' => 'Câmpul Data și ora pentru descărcarea #:position este obligatoriu',
            ]
        );
    }

    public function comandaExportPDF(Request $request, Comanda $comanda)
    {
        if ($request->view_type === 'export-html') {
            return view('comenzi.export.comandaPdf', compact('comanda'));
        } elseif ($request->view_type === 'export-pdf') {
            $pdf = \PDF::loadView('comenzi.export.comandaPdf', compact('comanda'))
                ->setPaper('a4', 'portrait');
            $pdf->getDomPDF()->set_option("enable_php", true);
            // return $pdf->download('Contract ' . $comanda->transportator_contract . '.pdf');
            return $pdf->stream();
        }
    }
}
