<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comanda;
use App\Models\ComandaIstoric;
use App\Models\ComandaCronjob;
use App\Models\ComandaClient;
use App\Models\ComandaClientIstoric;
use App\Models\ComandaLocOperareIstoric;
use App\Models\Firma;
use App\Models\Limba;
use App\Models\Moneda;
use App\Models\ProcentTVA;
use App\Models\MetodaDePlata;
use App\Models\TermenDePlata;
use App\Models\Camion;
use App\Models\LocOperare;
use App\Models\User;
use App\Models\Factura;

use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        $request->session()->forget('_old_input'); // se sterge din sesiune, pentru ca desi ar trebuie sa se stearga automat dupa validare si update, tot mai raman date aici

        $searchDataCreare = $request->searchDataCreare;
        $searchTransportatorContract = $request->searchTransportatorContract;
        $searchClientContract = $request->searchClientContract;
        $searchStare = $request->searchStare;
        $searchUser = $request->searchUser;
        $searchOperatorUser = $request->searchOperatorUser;
        $searchTransportatorId = $request->searchTransportatorId;
        $searchClientId = $request->searchClientId;
        $searchNrAuto = $request->searchNrAuto;

        $query = Comanda::with([
                'clienti:id,nume',
                'transportator:id,nume',
                'locuriOperareIncarcari',
                'locuriOperareDescarcari',
                'camion:id,numar_inmatriculare',
                'mesajeTrimiseEmail:id,comanda_id,categorie,email,created_at',
                'mesajeTrimiseSms:id,categorie,subcategorie,referinta_id,telefon,mesaj,content,trimis,raspuns,created_at',
                'user:id,name',
                'operator:id,name'
            ])
            ->withCount('contracteTrimisePeEmailCatreTransportator')
            ->when($searchDataCreare, function ($query, $searchDataCreare) {
                $dates = explode(',', $searchDataCreare);
                return $query->whereBetween('data_creare', [$dates[0], $dates[1] ?? $dates[0]]);
            })
            ->when($searchTransportatorContract, function ($query, $searchTransportatorContract) {
                return $query->where('transportator_contract', 'like', '%' . $searchTransportatorContract . '%');
            })
            ->when($searchClientContract, function ($query, $searchClientContract) {
                return $query->where('client_contract', $searchClientContract);
            })
            ->when($searchStare, function ($query, $searchStare) {
                return $query->where('stare', $searchStare);
            })
            ->when($searchUser, function ($query, $searchUser) {
                return $query->whereHas('user', function ($query) use ($searchUser) {
                    $query->where('id', $searchUser);
                });
            })
            ->when($searchOperatorUser, function ($query, $searchOperatorUser) {
                return $query->whereHas('operator', function ($query) use ($searchOperatorUser) {
                    $query->where('operator_user_id', $searchOperatorUser);
                });
            })
            ->when($searchTransportatorId, function ($query, $searchTransportatorId) {
                return $query->whereHas('transportator', function ($query) use ($searchTransportatorId) {
                    $query->where('id', $searchTransportatorId);
                });
            })
            ->when($searchClientId, function ($query, $searchClientId) {
                return $query->whereHas('clienti', function ($query) use ($searchClientId) {
                    $query->where('firme.id', $searchClientId);
                });
            })
            ->when($searchNrAuto, function ($query, $searchNrAuto) {
                return $query->whereHas('camion', function ($query) use ($searchNrAuto) {
                    $query->where('numar_inmatriculare', $searchNrAuto);
                });
            })
            ->latest();

        $comenzi = $query->simplePaginate(25);

        $firmeClienti = Firma::select('id', 'nume')->where('tip_partener', 1)->orderBy('nume')->get();
        $firmeTransportatori = Firma::select('id', 'nume')->where('tip_partener', 2)->orderBy('nume')->get();
        $useri = User::select('id' , 'name')->where('name', '<>', 'Andrei Dima')->where('activ', 1)->orderBy('name')->get();

        return view('comenzi.index', compact('comenzi', 'firmeClienti', 'firmeTransportatori', 'useri', 'searchDataCreare', 'searchTransportatorContract', 'searchClientContract', 'searchStare', 'searchUser', 'searchOperatorUser', 'searchTransportatorId', 'searchClientId', 'searchNrAuto'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $comanda = new Comanda;
        $comanda->transportator_contract = 'MSX-' . ( (preg_replace('/[^0-9]/', '', Comanda::orderBy('id', 'desc')->first()->transportator_contract ?? '0') ) + 1);
        $comanda->data_creare = Carbon::today();
        $comanda->interval_notificari = '03:00:00';
        $comanda->transportator_limba_id = 1; // Romana
        $comanda->transportator_tarif_pe_km = 0;
        // $comanda->client_limba_id = 1; // Romana
        // $comanda->client_tarif_pe_km = 0;
        $comanda->user_id = $request->user()->id;
        $comanda->operator_user_id = $request->user()->id;
        $comanda->cheie_unica = uniqid();
        $comanda->save();

        // Salvare in istoric
        $comanda_istoric = new ComandaIstoric;
        $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
        $comanda_istoric->operare_descriere = 'Adaugare';
        $comanda_istoric->save();

        return redirect( $comanda->path() . '/modifica');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // $comanda = Comanda::create($this->validateRequest($request));

        // Salvare in istoric
        // $comanda_istoric = new ComandaIstoric;
        // $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        // $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
        // $comanda_istoric->operare_descriere = 'Adaugare';
        // $comanda_istoric->save();

        // return redirect($request->session()->get('ComandaReturnUrl') ?? ('/comenzi'))->with('status', 'Comanda „' . $comanda->transportator_contract . '” a fost adăugată cu succes!');
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
        // Daca a fost adaugat un transportator din comanda, se revine in formularul comenzii si campurile trebuie sa se recompleteze automat
        if ($request->session()->exists('comandaRequest')) {
            session()->put('_old_input', $request->session()->pull('comandaRequest', 'default'));
            if ($request->session()->exists('comandaFirmaTip')) {
                if ($request->session()->pull('comandaFirmaTip') === 'clienti') {
                    $firma = Firma::find($request->session()->pull('comandaFirmaId', ''));
                    $clientOrdine = $request->session()->pull('comandaFirmaOrdine', '');
                    session()->put('_old_input.clienti.' . $clientOrdine . '.id', $firma->id);
                    session()->put('_old_input.clienti.' . $clientOrdine . '.nume', $firma->nume);
                } else {
                    session()->put('_old_input.transportator_transportator_id', $request->session()->pull('comandaFirmaId', ''));
                }
            } else if ($request->session()->exists('comandaCamionId')) {
                session()->put('_old_input.camion_id', $request->session()->pull('comandaCamionId', ''));
            } else if ($request->session()->exists('comandaLocOperareId')) {
                $locOperare = LocOperare::with('tara')->find($request->session()->pull('comandaLocOperareId', ''));
                $locOperareTip = $request->session()->pull('comandaLocOperareTip', '');
                $locOperareOrdine = $request->session()->pull('comandaLocOperareOrdine', '');
                session()->put('_old_input.' . $locOperareTip . '.' . $locOperareOrdine . '.id', $locOperare->id);
                session()->put('_old_input.' . $locOperareTip . '.' . $locOperareOrdine . '.nume', $locOperare->nume);
                session()->put('_old_input.' . $locOperareTip . '.' . $locOperareOrdine . '.adresa', $locOperare->adresa);
                session()->put('_old_input.' . $locOperareTip . '.' . $locOperareOrdine . '.oras', $locOperare->oras);
                session()->put('_old_input.' . $locOperareTip . '.' . $locOperareOrdine . '.tara.id', $locOperare->tara->id);
                session()->put('_old_input.' . $locOperareTip . '.' . $locOperareOrdine . '.tara.nume', $locOperare->tara->nume);
            }
        }
        // dd($comanda, session()->getOldInput());

        $useri = User::select('id', 'name')->where('name', '<>', 'Andrei Dima')->where('activ', 1)->orderBy('name')->get();
        $firmeClienti = Firma::select('id', 'nume')->where('tip_partener', 1)->orderBy('nume')->get();
        // $firmeTransportatori = Firma::select('id', 'nume')->where('tip_partener', 2)->orderBy('nume')->get();
        $firmeTransportatori = Firma::select('id', 'tara_id', 'nume', 'cui', 'oras', 'judet', 'adresa', 'cod_postal')->with('tara:id,nume')->where('tip_partener', 2)->orderBy('nume')->get();
        $limbi = Limba::select('id', 'nume')->get();
        $monede = Moneda::select('id', 'nume')->get();
        $procenteTVA = ProcentTVA::select('id', 'nume')->get();
        $metodeDePlata = MetodaDePlata::select('id', 'nume')->get();
        $termeneDePlata = TermenDePlata::select('id', 'nume')->get();
        $camioane = Camion::select('id', 'numar_inmatriculare', 'tip_camion', 'pret_km_goi', 'pret_km_plini')->orderBy('numar_inmatriculare')->get();
        // $locuriOperare = LocOperare::select('id', 'nume')->orderBy('nume')->get();
        // $locuriOperare = LocOperare::select('*')->orderBy('nume')->get();
        // $incarcari = $comanda->locuriOperareIncarcari()->get();
        // $descarcari = $comanda->locuriOperareDescarcari()->get();

        $request->session()->get('ComandaReturnUrl') ?? $request->session()->put('ComandaReturnUrl', url()->previous());
// dd($comanda);
        return view('comenzi.edit', compact('comanda', 'useri', 'firmeClienti', 'firmeTransportatori', 'limbi', 'monede', 'procenteTVA', 'metodeDePlata', 'termeneDePlata', 'camioane'));
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
        // dd($request, $request->request);
        // dd($request->request, $request->except(['incarcari']), $this->validateRequest($request));
        $comanda_campuri = $this->validateRequest($request);

        unset($comanda_campuri['clienti']);
        unset($comanda_campuri['incarcari']);
        unset($comanda_campuri['descarcari']);
        // If the prices are allready saved in the database
        // if (($comanda->transportator_valoare_km_goi !== null) && ($comanda->transportator_valoare_km_plini !== null)) {
        //     unset($comanda_campuri['transportator_km_goi']);
        //     unset($comanda_campuri['transportator_km_plini']);
        // }

        // if ($comanda->transportator_tarif_pe_km) {
        //     $transportator_valoare_km_goi = ($comanda->transportator_km_goi ?? $comanda_campuri['transportator_km_goi'] ?? 0) * ($comanda->camion->pret_km_goi ?? 0);
        //     $transportator_valoare_km_plini = ($comanda->transportator_km_goi ?? $comanda_campuri['transportator_km_plini'] ?? 0) * ($comanda->camion->pret_km_plini ?? 0);
        //     unset($comanda_campuri['transportator_valoare_contract']);
        //     $comanda->transportator_valoare_contract = $transportator_valoare_km_goi + $transportator_valoare_km_plini;
        // }

        $comanda->update($comanda_campuri);

        // Salvare in istoric a comenzii
        if ($comanda->wasChanged()){
            $comanda_istoric = new ComandaIstoric;
            $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
            $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
            $comanda_istoric->operare_descriere = 'Modificare';
            $comanda_istoric->save();
        }


        // Added on 14.01.2025 - after that we went to more that one client to a command
        // Curent clients from pivot table
        $currentClients = $comanda->clienti->mapWithKeys(function ($client) {
            return [$client->pivot->id => $client->pivot->toArray()];
        });

        $newClients = collect($request->clienti); // Clients from the request

        $newClients->each(function ($client, $index) use ($comanda, $currentClients) {
            $clientId = $client['pivot']['id'];
            $newPivotData = [
                'comanda_id' => $comanda->id,
                'client_id' => $client['id'],
                'ordine_afisare' => $index + 1,
                'contract' => $client['pivot']['contract'] ?? null,
                'limba_id' => $client['pivot']['limba_id'] ?? null,
                'valoare_contract_initiala' => $client['pivot']['valoare_contract_initiala'] ?? null,
                'moneda_id' => $client['pivot']['moneda_id'] ?? null,
                'procent_tva_id' => $client['pivot']['procent_tva_id'] ?? null,
                'metoda_de_plata_id' => $client['pivot']['metoda_de_plata_id'] ?? null,
                'termen_plata_id' => $client['pivot']['termen_plata_id'] ?? null,
                'zile_scadente' => $client['pivot']['zile_scadente'] ?? null,
                'tarif_pe_km' => $client['pivot']['tarif_pe_km'] ?? null,
            ];

            $historyData = array_merge($newPivotData, [
                'id' => $clientId,
                'operare_user_id' => auth()->user()->id ?? null,
                'operare_data' => now(),
            ]);

            if (isset($currentClients[$clientId])) { // The client exists in the current clients
                // Compare pivot data
                $currentPivotData = $currentClients[$clientId];

                if ($newPivotData != array_intersect_key($currentPivotData, $newPivotData)) {
                    // Update the pivot data if it's changed
                    ComandaClient::where('id', $clientId) // Find by pivot ID
                        ->update($newPivotData); // Update with new data

                    // Add an entry to the history table
                    $historyData['operare_descriere'] = 'Modificare';
                    ComandaClientIstoric::create($historyData);
                }
            } else {
                // Add new relationship
                $comandaClient = ComandaClient::create($newPivotData);
                // Create a factura for it aswell
                $factura = new Factura();
                $factura->comanda_client_id = $comandaClient->id;
                $factura->data = Carbon::now();
                $factura->client_nume = $client['nume'] ?? '';
                $factura->client_email = $client['email_factura'] ?? '';
                $factura->client_contract = $comandaClient['contract'] ?? '';
                $factura->client_limba_id = $comandaClient['limba_id'] ?? '';
                $factura->save();

                // Add an entry to the history table
                $historyData['operare_descriere'] = 'Adaugare';
                $historyData['id'] = $comandaClient->id;
                ComandaClientIstoric::create($historyData);
            }
        });

        // Handle deleted clients
        $currentClientIds = $currentClients->keys()->toArray(); // Extract current client IDs
        $newClientIds = $newClients->pluck('pivot.id')->toArray(); // Extract new client IDs
        $clientsToDelete = array_diff($currentClientIds, $newClientIds); // IDs to be deleted

        foreach ($clientsToDelete as $clientId) {
                $comandaClient = ComandaClient::find($clientId); // Retrieve the instance by ID

                if ($comandaClient) {
                    // Delete the associated factura if it exists
                    if ($comandaClient->factura) {
                        $comandaClient->factura->delete();
                    }

                    // Delete the ComandaClient instance
                    $comandaClient->delete();

                    // Add an entry to the history table
                    ComandaClientIstoric::create([
                        'id' => $comandaClient->id,
                        'comanda_id' => $comanda->id,
                        'operare_user_id' => auth()->user()->id ?? null,
                        'operare_descriere' => 'Stergere',
                        'operare_data' => now(),
                    ]);
                }
        }


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
                    $existaDuplicateInLocatiileNoi = sizeof($temp_array) != (sizeof($request->incarcari ?? []) + sizeof($request->descarcari ?? []));
                }
            }

            // Daca exista duplicate, in locatiile vechi sau noi, se creaza un array, cu index incepand cu 0, care la sync va sterge toate locatiile vechi si apoi va readauga toate locatiile noi
            // Daca nu exista duplicate, se creaza un array, cu index id-ul locatiilor, care la sync va face update doar daca este cazul
            if ((isset($existaDuplicateInLocatiileVechi) && ($existaDuplicateInLocatiileVechi)) || (isset($existaDuplicateInLocatiileNoi) && ($existaDuplicateInLocatiileNoi))) {
                if ($request->incarcari){
                    for ($i = 0; $i < count($request->incarcari); $i++) {
                        $locatii_id_array[$i] = [
                            'loc_operare_id' => intval($request->incarcari[$i]['id']),
                            'tip' => 1,
                            'ordine' => $i+1,
                            'data_ora' => $request->incarcari[$i]['pivot']['data_ora'],
                            'durata' => $request->incarcari[$i]['pivot']['durata'],
                            'observatii' => $request->incarcari[$i]['pivot']['observatii'],
                            'referinta' => $request->incarcari[$i]['pivot']['referinta']
                        ];
                    }
                }
                if ($request->descarcari){
                    for ($i = 0; $i < count($request->descarcari); $i++) {
                        $locatii_id_array[count($locatii_id_array ?? [])] = [
                            'loc_operare_id' => intval($request->descarcari[$i]['id']),
                            'tip' => 2,
                            'ordine' => $i+1,
                            'data_ora' => $request->descarcari[$i]['pivot']['data_ora'],
                            'durata' => $request->descarcari[$i]['pivot']['durata'],
                            'observatii' => $request->descarcari[$i]['pivot']['observatii'],
                            'referinta' => $request->descarcari[$i]['pivot']['referinta']
                        ];
                    }
                }
            } else {
                if ($request->incarcari){
                    for ($i = 0; $i < count($request->incarcari); $i++) {
                        $locatii_id_array[$request->incarcari[$i]['id']] = [
                            'tip' => 1,
                            'ordine' => $i+1,
                            'data_ora' => $request->incarcari[$i]['pivot']['data_ora'],
                            'durata' => $request->incarcari[$i]['pivot']['durata'],
                            'observatii' => $request->incarcari[$i]['pivot']['observatii'],
                            'referinta' => $request->incarcari[$i]['pivot']['referinta']
                        ];
                    }
                }
                if ($request->descarcari){
                    for ($i = 0; $i < count($request->descarcari); $i++){
                        $locatii_id_array[$request->descarcari[$i]['id']] = [
                            'tip' => 2,
                            'ordine' => $i+1,
                            'data_ora' => $request->descarcari[$i]['pivot']['data_ora'],
                            'durata' => $request->descarcari[$i]['pivot']['durata'],
                            'observatii' => $request->descarcari[$i]['pivot']['observatii'],
                            'referinta' => $request->descarcari[$i]['pivot']['referinta']
                        ];
                    }
                }
            }

            $locuriOperareIncarcariVechi = $comanda->locuriOperareIncarcari; // necesar pentru salvarea in istoric
            $locuriOperareDescarcariVechi = $comanda->locuriOperareDescarcari; // necesar pentru salvarea in istoric

            if (isset($locatii_id_array)){
                $comanda->locuriOperare()->sync($locatii_id_array);
            } else {
                $comanda->locuriOperare()->detach();
            }


            /**
            * Salvare in istoric a locurilor de incarcare si descarcare
            */
            $comanda = Comanda::find($comanda->id); // se readuce din baza de date comanda, pentru a fi cu relatiile actualizate
            $locuriOperareIncarcariNoi = $comanda->locuriOperareIncarcari;
            $locuriOperareDescarcariNoi = $comanda->locuriOperareDescarcari;

            // echo 'locuriOperareIncarcariVechi = ' . $locuriOperareIncarcariVechi->count() . '<br>';
            // echo 'locuriOperareIncarcariNoi = ' . $locuriOperareIncarcariNoi->count() . '<br>';
            for ($i = 0; $i < max($locuriOperareIncarcariVechi->count(), $locuriOperareIncarcariNoi->count()); $i++ ){
                if (isset($locuriOperareIncarcariNoi[$i])){
                    if (!isset($locuriOperareIncarcariVechi[$i])){
                        $comandaLocOperareIstoric = new ComandaLocOperareIstoric;
                        $comandaLocOperareIstoric->id = $locuriOperareIncarcariNoi[$i]->pivot->id;
                        $comandaLocOperareIstoric->comanda_id = $locuriOperareIncarcariNoi[$i]->pivot->comanda_id;
                        $comandaLocOperareIstoric->loc_operare_id = $locuriOperareIncarcariNoi[$i]->pivot->loc_operare_id;
                        $comandaLocOperareIstoric->tip = $locuriOperareIncarcariNoi[$i]->pivot->tip;
                        $comandaLocOperareIstoric->ordine = $locuriOperareIncarcariNoi[$i]->pivot->ordine;
                        $comandaLocOperareIstoric->data_ora = $locuriOperareIncarcariNoi[$i]->pivot->data_ora;
                        $comandaLocOperareIstoric->durata = $locuriOperareIncarcariNoi[$i]->pivot->durata;
                        $comandaLocOperareIstoric->observatii = $locuriOperareIncarcariNoi[$i]->pivot->observatii;
                        $comandaLocOperareIstoric->referinta = $locuriOperareIncarcariNoi[$i]->pivot->referinta;
                        $comandaLocOperareIstoric->operare_user_id = auth()->user()->id ?? null;
                        $comandaLocOperareIstoric->operare_descriere = 'Adaugare';
                        $comandaLocOperareIstoric->save();

                        $comanda_istoric = new ComandaIstoric;
                        $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
                        $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
                        $comanda_istoric->operare_descriere = 'Adaugare incarcare';
                        $comanda_istoric->save();
                    } else if (
                        ($locuriOperareIncarcariVechi[$i]->id !== $locuriOperareIncarcariNoi[$i]->id)
                        || ($locuriOperareIncarcariVechi[$i]->pivot->ordine !== $locuriOperareIncarcariNoi[$i]->pivot->ordine)
                        || ($locuriOperareIncarcariVechi[$i]->pivot->data_ora !== $locuriOperareIncarcariNoi[$i]->pivot->data_ora)
                        || ($locuriOperareIncarcariVechi[$i]->pivot->durata !== $locuriOperareIncarcariNoi[$i]->pivot->durata)
                        || ($locuriOperareIncarcariVechi[$i]->pivot->observatii !== $locuriOperareIncarcariNoi[$i]->pivot->observatii)
                        || ($locuriOperareIncarcariVechi[$i]->pivot->referinta !== $locuriOperareIncarcariNoi[$i]->pivot->referinta)
                    ){
                            $comandaLocOperareIstoric = new ComandaLocOperareIstoric;
                            $comandaLocOperareIstoric->id = $locuriOperareIncarcariNoi[$i]->pivot->id;
                            $comandaLocOperareIstoric->comanda_id = $locuriOperareIncarcariNoi[$i]->pivot->comanda_id;
                            $comandaLocOperareIstoric->loc_operare_id = $locuriOperareIncarcariNoi[$i]->pivot->loc_operare_id;
                            $comandaLocOperareIstoric->tip = $locuriOperareIncarcariNoi[$i]->pivot->tip;
                            $comandaLocOperareIstoric->ordine = $locuriOperareIncarcariNoi[$i]->pivot->ordine;
                            $comandaLocOperareIstoric->data_ora = $locuriOperareIncarcariNoi[$i]->pivot->data_ora;
                            $comandaLocOperareIstoric->durata = $locuriOperareIncarcariNoi[$i]->pivot->durata;
                            $comandaLocOperareIstoric->observatii = $locuriOperareIncarcariNoi[$i]->pivot->observatii;
                            $comandaLocOperareIstoric->referinta = $locuriOperareIncarcariNoi[$i]->pivot->referinta;
                            $comandaLocOperareIstoric->operare_user_id = auth()->user()->id ?? null;
                            $comandaLocOperareIstoric->operare_descriere = 'Modificare';
                            $comandaLocOperareIstoric->save();

                            $comanda_istoric = new ComandaIstoric;
                            $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
                            $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
                            $comanda_istoric->operare_descriere = 'Modificare incarcare';
                            $comanda_istoric->save();
                    }

                } else if (!isset($locuriOperareIncarcariNoi[$i])){
                    $comandaLocOperareIstoric = new ComandaLocOperareIstoric;
                    $comandaLocOperareIstoric->id = $locuriOperareIncarcariVechi[$i]->pivot->id;
                    $comandaLocOperareIstoric->comanda_id = $locuriOperareIncarcariVechi[$i]->pivot->comanda_id;
                    $comandaLocOperareIstoric->loc_operare_id = $locuriOperareIncarcariVechi[$i]->pivot->loc_operare_id;
                    $comandaLocOperareIstoric->tip = $locuriOperareIncarcariVechi[$i]->pivot->tip;
                    $comandaLocOperareIstoric->ordine = $locuriOperareIncarcariVechi[$i]->pivot->ordine;
                    $comandaLocOperareIstoric->data_ora = $locuriOperareIncarcariVechi[$i]->pivot->data_ora;
                    $comandaLocOperareIstoric->durata = $locuriOperareIncarcariVechi[$i]->pivot->durata;
                    $comandaLocOperareIstoric->observatii = $locuriOperareIncarcariVechi[$i]->pivot->observatii;
                    $comandaLocOperareIstoric->referinta = $locuriOperareIncarcariVechi[$i]->pivot->referinta;
                    $comandaLocOperareIstoric->operare_user_id = auth()->user()->id ?? null;
                    $comandaLocOperareIstoric->operare_descriere = 'Stergere';
                    $comandaLocOperareIstoric->save();

                    $comanda_istoric = new ComandaIstoric;
                    $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
                    $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
                    $comanda_istoric->operare_descriere = 'Stergere incarcare';
                    $comanda_istoric->save();
                }

                // if (isset($locuriOperareIncarcariNoi[$i])){
                //     echo 'Id nou ' . $locuriOperareIncarcariNoi[$i]->id . '<br>';
                // }
                // if (isset($locuriOperareIncarcariVechi[$i])){
                //     echo 'Id vechi ' . $locuriOperareIncarcariVechi[$i]->id . ', ordine ' . $locuriOperareIncarcariVechi[$i]->pivot->ordine . '<br>';
                // }
                // if (isset($locuriOperareIncarcariNoi[$i])){
                //     echo 'Id nou ' . $locuriOperareIncarcariNoi[$i]->id . '<br>';
                // }
            }

            for ($i = 0; $i < max($locuriOperareDescarcariVechi->count(), $locuriOperareDescarcariNoi->count()); $i++ ){
                if (isset($locuriOperareDescarcariNoi[$i])){
                    if (!isset($locuriOperareDescarcariVechi[$i])){
                        $comandaLocOperareIstoric = new ComandaLocOperareIstoric;
                        $comandaLocOperareIstoric->id = $locuriOperareDescarcariNoi[$i]->pivot->id;
                        $comandaLocOperareIstoric->comanda_id = $locuriOperareDescarcariNoi[$i]->pivot->comanda_id;
                        $comandaLocOperareIstoric->loc_operare_id = $locuriOperareDescarcariNoi[$i]->pivot->loc_operare_id;
                        $comandaLocOperareIstoric->tip = $locuriOperareDescarcariNoi[$i]->pivot->tip;
                        $comandaLocOperareIstoric->ordine = $locuriOperareDescarcariNoi[$i]->pivot->ordine;
                        $comandaLocOperareIstoric->data_ora = $locuriOperareDescarcariNoi[$i]->pivot->data_ora;
                        $comandaLocOperareIstoric->durata = $locuriOperareDescarcariNoi[$i]->pivot->durata;
                        $comandaLocOperareIstoric->observatii = $locuriOperareDescarcariNoi[$i]->pivot->observatii;
                        $comandaLocOperareIstoric->referinta = $locuriOperareDescarcariNoi[$i]->pivot->referinta;
                        $comandaLocOperareIstoric->operare_user_id = auth()->user()->id ?? null;
                        $comandaLocOperareIstoric->operare_descriere = 'Adaugare';
                        $comandaLocOperareIstoric->save();

                        $comanda_istoric = new ComandaIstoric;
                        $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
                        $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
                        $comanda_istoric->operare_descriere = 'Adaugare descarcare';
                        $comanda_istoric->save();
                    } else if (
                        ($locuriOperareDescarcariVechi[$i]->id !== $locuriOperareDescarcariNoi[$i]->id)
                        || ($locuriOperareDescarcariVechi[$i]->pivot->ordine !== $locuriOperareDescarcariNoi[$i]->pivot->ordine)
                        || ($locuriOperareDescarcariVechi[$i]->pivot->data_ora !== $locuriOperareDescarcariNoi[$i]->pivot->data_ora)
                        || ($locuriOperareDescarcariVechi[$i]->pivot->durata !== $locuriOperareDescarcariNoi[$i]->pivot->durata)
                        || ($locuriOperareDescarcariVechi[$i]->pivot->observatii !== $locuriOperareDescarcariNoi[$i]->pivot->observatii)
                        || ($locuriOperareDescarcariVechi[$i]->pivot->referinta !== $locuriOperareDescarcariNoi[$i]->pivot->referinta)
                    ){
                            $comandaLocOperareIstoric = new ComandaLocOperareIstoric;
                            $comandaLocOperareIstoric->id = $locuriOperareDescarcariNoi[$i]->pivot->id;
                            $comandaLocOperareIstoric->comanda_id = $locuriOperareDescarcariNoi[$i]->pivot->comanda_id;
                            $comandaLocOperareIstoric->loc_operare_id = $locuriOperareDescarcariNoi[$i]->pivot->loc_operare_id;
                            $comandaLocOperareIstoric->tip = $locuriOperareDescarcariNoi[$i]->pivot->tip;
                            $comandaLocOperareIstoric->ordine = $locuriOperareDescarcariNoi[$i]->pivot->ordine;
                            $comandaLocOperareIstoric->data_ora = $locuriOperareDescarcariNoi[$i]->pivot->data_ora;
                            $comandaLocOperareIstoric->durata = $locuriOperareDescarcariNoi[$i]->pivot->durata;
                            $comandaLocOperareIstoric->observatii = $locuriOperareDescarcariNoi[$i]->pivot->observatii;
                            $comandaLocOperareIstoric->referinta = $locuriOperareDescarcariNoi[$i]->pivot->referinta;
                            $comandaLocOperareIstoric->operare_user_id = auth()->user()->id ?? null;
                            $comandaLocOperareIstoric->operare_descriere = 'Modificare';
                            $comandaLocOperareIstoric->save();

                            $comanda_istoric = new ComandaIstoric;
                            $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
                            $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
                            $comanda_istoric->operare_descriere = 'Modificare descarcare';
                            $comanda_istoric->save();
                    }

                } else if (!isset($locuriOperareDescarcariNoi[$i])){
                    $comandaLocOperareIstoric = new ComandaLocOperareIstoric;
                    $comandaLocOperareIstoric->id = $locuriOperareDescarcariVechi[$i]->pivot->id;
                    $comandaLocOperareIstoric->comanda_id = $locuriOperareDescarcariVechi[$i]->pivot->comanda_id;
                    $comandaLocOperareIstoric->loc_operare_id = $locuriOperareDescarcariVechi[$i]->pivot->loc_operare_id;
                    $comandaLocOperareIstoric->tip = $locuriOperareDescarcariVechi[$i]->pivot->tip;
                    $comandaLocOperareIstoric->ordine = $locuriOperareDescarcariVechi[$i]->pivot->ordine;
                    $comandaLocOperareIstoric->data_ora = $locuriOperareDescarcariVechi[$i]->pivot->data_ora;
                    $comandaLocOperareIstoric->durata = $locuriOperareDescarcariVechi[$i]->pivot->durata;
                    $comandaLocOperareIstoric->observatii = $locuriOperareDescarcariVechi[$i]->pivot->observatii;
                    $comandaLocOperareIstoric->referinta = $locuriOperareDescarcariVechi[$i]->pivot->referinta;
                    $comandaLocOperareIstoric->operare_user_id = auth()->user()->id ?? null;
                    $comandaLocOperareIstoric->operare_descriere = 'Stergere';
                    $comandaLocOperareIstoric->save();

                    $comanda_istoric = new ComandaIstoric;
                    $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
                    $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
                    $comanda_istoric->operare_descriere = 'Stergere descarcare';
                    $comanda_istoric->save();
                }

                // if (isset($locuriOperareDescarcariNoi[$i])){
                //     echo 'Id nou ' . $locuriOperareDescarcariNoi[$i]->id . '<br>';
                // }
                // if (isset($locuriOperareDescarcariVechi[$i])){
                //     echo 'Id vechi ' . $locuriOperareDescarcariVechi[$i]->id . ', ordine ' . $locuriOperareDescarcariVechi[$i]->pivot->ordine . '<br>';
                // }
                // if (isset($locuriOperareDescarcariNoi[$i])){
                //     echo 'Id nou ' . $locuriOperareDescarcariNoi[$i]->id . '<br>';
                // }
            }


        /**
         * Salvare cronJob
         */
        if ($comanda->primaIncarcare() && $comanda->Ultimadescarcare()){
            if ($comanda->primaIncarcare()->tara->gmt_offset ?? ''){
                // GMT +3 ora Romaniei - GTM ora tarii unde este clientul
                $diferenta_fus_orar_incarcare = 3-substr($comanda->primaIncarcare()->tara->gmt_offset, 0, -3);
            } else {
                $diferenta_fus_orar_incarcare = 0;
            }
            if ($comanda->Ultimadescarcare()->tara->gmt_offset ?? ''){
                // GMT +3 ora Romaniei - GTM ora tarii unde este clientul
                $diferenta_fus_orar_descarcare = 3-substr($comanda->Ultimadescarcare()->tara->gmt_offset, 0, -3);
            } else {
                $diferenta_fus_orar_descarcare = 0;
            }

            $comanda->cronjob()->updateOrCreate(
                ['comanda_id' => $comanda->id],
                [
                    'inceput' => Carbon::parse($comanda->primaIncarcare()->pivot->data_ora)->addHours($diferenta_fus_orar_incarcare),
                    'sfarsit' => Carbon::parse($comanda->ultimaDescarcare()->pivot->data_ora)->addHours($diferenta_fus_orar_descarcare)->addMinutes(Carbon::parse($comanda->ultimaDescarcare()->pivot->durata)->diffInMinutes(Carbon::today())),
                ]);
        } else {
            $comanda->cronjob()->updateOrCreate(
                ['comanda_id' => $comanda->id],
                [
                    'inceput' => NULL,
                    'sfarsit' => NULL,
                ]);
        }

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
        if($comanda->fisiereIncarcateDeTransportator->count() > 0){
            return back()->with('error', 'Comanda „' . $comanda->transportator_contract . '” nu poate fi ștearsă pentru că are documente atașate. Verifică întâi documentele și ștergele pe fiecare în parte, și apoi poți șterge și comanda.');
        }

        $comanda->locuriOperare()->detach();
        $comanda->cronjob ? $comanda->cronjob->delete() : '';
        foreach ($comanda->facturi as $factura) {
            $factura->delete();
        }
        $comanda->clienti()->detach();
        $comanda->delete();


        // Salvare in istoric
        $comanda_istoric = new ComandaIstoric;
        $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
        $comanda_istoric->operare_descriere = 'Stergere';
        $comanda_istoric->save();

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
// dd($request);
        return $request->validate(
            [
                'data_creare' => 'required',
                'interval_notificari' => 'required',
                // 'transportator_contract' => 'required|max:20',
                'transportator_limba_id' => '',
                'transportator_valoare_contract' => 'required|numeric|min:-9999999|max:9999999',
                'transportator_moneda_id' => 'required',
                'transportator_zile_scadente' => 'nullable|numeric|min:-100|max:300',
                'transportator_termen_plata_id' => '',
                'transportator_transportator_id' => 'required',
                'transportator_procent_tva_id' => '',
                'transportator_metoda_de_plata_id' => '',
                'transportator_format_documente' => 'required',
                'transportator_tarif_pe_km' => '',
                'transportator_pret_km_goi' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',
                'transportator_pret_km_plini' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',
                'transportator_km_goi' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',
                'transportator_km_plini' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',
                'transportator_valoare_km_goi' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',
                'transportator_valoare_km_plini' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',
                'transportator_pret_autostrada' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',
                'transportator_pret_ferry' => 'required_if:transportator_tarif_pe_km,1|numeric|min:0|max:99999',


                // Comented on 14.01.2025 - after that we went to more that one client to a command
                // 'client_contract' => 'nullable|max:255',
                // 'client_limba_id' => '',
                // 'client_valoare_contract_initiala' => 'required|numeric|min:-9999999|max:9999999',
                // 'client_valoare_contract' => 'required|numeric|min:-9999999|max:9999999',
                // 'client_moneda_id' => 'required',
                // 'client_zile_scadente' => 'nullable|numeric|min:-100|max:300',
                // 'client_termen_plata_id' => '',
                // 'client_client_id' => 'required',
                // 'client_procent_tva_id' => '',
                // 'client_metoda_de_plata_id' => '',
                // 'client_tarif_pe_km' => '',

                // This was commented before 2024
                // 'client_data_factura' => '',
                // 'client_zile_inainte_de_scadenta_memento_factura' =>
                //     function ($attribute, $value, $fail) use ($request) {
                //         if ($value){
                //             $zileInainte = preg_split ("/\,/", $value);
                //             foreach ($zileInainte as $ziInainte){
                //                 if (!(intval($ziInainte) == $ziInainte)){
                //                     $fail('Câmpul „Cu câte zile înainte de scadență să se trimită memento” nu este completat corect');
                //                 }elseif ($ziInainte < 0){
                //                     $fail('Câmpul „Cu câte zile înainte de scadență să se trimită memento” nu poate conține valori negative');
                //                 }elseif ($ziInainte > 100){
                //                     $fail('Câmpul „Cu câte zile înainte de scadență să se trimită memento” nu poate conține valori mai mari de 100');
                //                 }
                //             }
                //         }
                //     },

                // Added on 14.01.2025 - after that we went to more that one client to a command
                'clienti.*.id' => 'required',
                'clienti.*.pivot.contract' => 'required|max:255',
                'clienti.*.pivot.valoare_contract_initiala' => 'required|numeric|between:-9999999,9999999',
                'clienti.*.pivot.moneda_id' => 'required',
                'clienti.*.pivot.client_procent_tva_id' => '',
                'clienti.*.pivot.client_metoda_de_plata_id' => '',
                'clienti.*.pivot.client_termen_plata_id' => '',
                'clienti.*.pivot.client_zile_scadente' => 'nullable|numeric|min:-100|max:300',
                'clienti.*.pivot.client_tarif_pe_km' => '',

                // those 2 fields are used for all raports: is the summ value for all clients
                'client_valoare_contract' => 'required|numeric|min:-9999999|max:9999999',
                'client_moneda_id' => 'required',

                'descriere_marfa' => 'nullable|max:500',
                'camion_id' => 'required',

                'incarcari.*.id' => 'required',
                // 'incarcari.*.nume' => 'required|max:500',
                // 'incarcari.*.oras' => 'nullable|max:500',
                'incarcari.*.pivot.data_ora' => 'required',
                'incarcari.*.pivot.durata' => 'required',

                'descarcari.*.id' => 'required',
                'descarcari.*.pivot.data_ora' => 'required',
                'descarcari.*.pivot.durata' => 'required',

                'observatii_interne' => 'nullable|max:2000',
                'observatii_externe' => 'nullable|max:2000',

                'user_id' => 'required',
                'operator_user_id' => 'nullable',

                // 'observatii' => 'nullable|max:2000',
            ],
            [
                'transportator_transportator_id.required' => 'Câmpul Transportator este obligatoriu',

                // Comented on 14.01.2025 - after that we went to more that one client to a command
                'client_client_id.required' => 'Câmpul Client este obligatoriu',
                // Added on 14.01.2025 - after that we went to more that one client to a command
                'clienti.*.id' => 'Clientul #:position este obligatoriu de selectat din baza de date',
                'clienti.*.pivot.contract.required' => 'Câmpul Contract pentru clientul #:position este obligatoriu',
                'clienti.*.pivot.contract.max' => 'Câmpul Contract pentru clientul #:position nu poate avea mai mult de 255 de caractere',
                'clienti.*.pivot.moneda_id' => 'Câmpul Moneda pentru clientul #:position este obligatoriu',
                'clienti.*.pivot.valoare_contract_initiala.required' => 'Câmpul Valoare Contract Inițială pentru clientul #:position este obligatoriu',
                'clienti.*.pivot.valoare_contract_initiala.numeric' => 'Câmpul Valoare Contract Inițială pentru clientul #:position trebuie să fie un număr',
                'clienti.*.pivot.valoare_contract_initiala.between' => 'Câmpul Valoare Contract Inițială pentru clientul #:position trebuie să fie între -9999999 și 9999999',

                'client_valoare_contract.required' => 'Câmpul Valoare contract finală este obligatoriu',
                'client_valoare_contract.numeric' => 'Câmpul Valoare contract finală trebuie să fie numeric',
                'client_valoare_contract.min' => 'Câmpul Valoare contract finală poate fi minim 9999999',
                'client_valoare_contract.max' => 'Câmpul Valoare contract finală poate fi maxim 9999999',
                'client_moneda_id.required' => 'Câmpul Moneda de la Valoare contract finală este obligatoriu',

                'camion_id.required' => 'Câmpul Camion este obligatoriu',

                'incarcari.*.id' => 'Încărcarea #:position este obligatoriu de selectat din baza de date',
                'incarcari.*.pivot.data_ora' => 'Câmpul Data și ora pentru încărcarea #:position este obligatoriu',
                'incarcari.*.pivot.durata' => 'Câmpul Durata pentru încărcarea #:position este obligatoriu',
                'descarcari.*.id' => 'Descărcarea #:position este obligatoriu de selectat din baza de date',
                'descarcari.*.pivot.data_ora' => 'Câmpul Data și ora pentru descărcarea #:position este obligatoriu',
                'descarcari.*.pivot.durata' => 'Câmpul Durata pentru descărcarea #:position este obligatoriu',
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

    public function comandaExportExcel(Request $request, Comanda $comanda)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

        $sheet->getStyle('A1:N1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:N1')->getFont()->setBold(true);
        $sheet->setCellValue('A1', 'Transport ID');
        $sheet->setCellValue('B1', 'Stop Type');
        $sheet->setCellValue('C1', 'References / Deliveries');
        $sheet->setCellValue('D1', 'Location Name');
        $sheet->setCellValue('E1', 'Street');
        $sheet->setCellValue('F1', 'Postcode');
        $sheet->setCellValue('G1', 'City');
        $sheet->setCellValue('H1', 'Country');
        $sheet->setCellValue('I1', 'Time Slot Start Date');
        $sheet->setCellValue('J1', 'Time Slot Start Time');
        $sheet->setCellValue('K1', 'Time Slot End Date');
        $sheet->setCellValue('L1', 'Time Slot End Time');
        $sheet->setCellValue('M1', 'Grouping Code');
        $sheet->setCellValue('N1', 'Stop Remarks');

        $rand = 2;
        foreach ($comanda->locuriOperareIncarcari as $key=>$locOperareIncarcare){
            $sheet->setCellValue('A' . $rand, $comanda->transportator_contract);
            $sheet->setCellValue('B' . $rand, 'Loading');
            $sheet->getStyle('B' . $rand)->getFont()->getColor()->setRGB('00B0F0');
            $sheet->setCellValue('C' . $rand, $locOperareIncarcare->pivot->referinta ?? '');
            $sheet->setCellValue('D' . $rand, $locOperareIncarcare->nume);
            $sheet->setCellValue('E' . $rand, $locOperareIncarcare->adresa);
            $sheet->setCellValue('F' . $rand, $locOperareIncarcare->cod_postal);
            $sheet->setCellValue('G' . $rand, $locOperareIncarcare->oras);
            $sheet->setCellValue('H' . $rand, $locOperareIncarcare->tara->nume ?? '');
            if ($locOperareIncarcare->pivot->data_ora){
                $sheet->getStyle('I' . $rand)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
                $sheet->setCellValue('I' . $rand, Carbon::parse($locOperareIncarcare->pivot->data_ora)->isoFormat('YYYY-MM-DD'));
                $sheet->setCellValue('J' . $rand, Carbon::parse($locOperareIncarcare->pivot->data_ora)->isoFormat('HH:mm:ss'));

                $durata = Carbon::parse($locOperareIncarcare->pivot->durata);
                $sfarsit = Carbon::parse($locOperareIncarcare->pivot->data_ora)->addHours($durata->hour)->addMinutes($durata->minute);
                $sheet->getStyle('K' . $rand)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
                $sheet->setCellValue('K' . $rand, $sfarsit->isoFormat('YYYY-MM-DD'));
                $sheet->setCellValue('L' . $rand, $sfarsit->isoFormat('HH:mm:ss'));
            }
            $sheet->setCellValue('N' . $rand, 'Loading ' . $key+1 );
            $rand ++;
        }
        foreach ($comanda->locuriOperareDescarcari as $key=>$locOperareDescarcare){
            $sheet->setCellValue('A' . $rand, $comanda->transportator_contract);
            $sheet->setCellValue('B' . $rand, 'Unloading');
            $sheet->getStyle('B' . $rand)->getFont()->getColor()->setRGB('FF2F92');
            $sheet->setCellValue('C' . $rand, $locOperareDescarcare->pivot->referinta ?? '');
            $sheet->setCellValue('D' . $rand, $locOperareDescarcare->nume);
            $sheet->setCellValue('E' . $rand, $locOperareDescarcare->adresa);
            $sheet->setCellValue('F' . $rand, $locOperareDescarcare->cod_postal);
            $sheet->setCellValue('G' . $rand, $locOperareDescarcare->oras);
            $sheet->setCellValue('H' . $rand, $locOperareDescarcare->tara->nume ?? '');
            if ($locOperareDescarcare->pivot->data_ora){
                $sheet->getStyle('I' . $rand)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
                $sheet->setCellValue('I' . $rand, Carbon::parse($locOperareDescarcare->pivot->data_ora)->isoFormat('YYYY-MM-DD'));
                $sheet->setCellValue('J' . $rand, Carbon::parse($locOperareDescarcare->pivot->data_ora)->isoFormat('HH:mm:ss'));

                $durata = Carbon::parse($locOperareDescarcare->pivot->durata);
                $sfarsit = Carbon::parse($locOperareDescarcare->pivot->data_ora)->addHours($durata->hour)->addMinutes($durata->minute);
                $sheet->getStyle('K' . $rand)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);
                $sheet->setCellValue('K' . $rand, $sfarsit->isoFormat('YYYY-MM-DD'));
                $sheet->setCellValue('L' . $rand, $sfarsit->isoFormat('HH:mm:ss'));

            }
            $sheet->setCellValue('N' . $rand, 'Unloading ' . $key+1 );
            $rand ++;
        }

        // Se parcug toate coloanele si se stabileste latimea AUTO
        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }
        // $sheet->getColumnDimension('A')->setWidth(90);

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Comanda ' . $comanda->transportator_contract . '.xlsx"');
        $writer->save('php://output');
        exit();
    }

    public function comandaTrimiteCatreTransportator(Request $request, Comanda $comanda)
    {
        if (isset($comanda->transportator->email)){
            Mail::to($comanda->transportator->email)->send(new \App\Mail\TrimiteContractComandaCatreTransportator($comanda));

            $emailTrimis = new \App\Models\MesajTrimisEmail;
            $emailTrimis->comanda_id = $comanda->id;
            $emailTrimis->firma_id = $comanda->transportator->id ?? '';
            $emailTrimis->categorie = 3;
            $emailTrimis->email = $comanda->transportator->email;
            $emailTrimis->save();

            // Nu se trimit notificari decat daca a fost trimisa comanda pe email catre transportator
            $comanda->cronjob()->updateOrCreate(['comanda_id' => $comanda->id],['contract_trimis_pe_email_catre_transportator' => 1]);

            return back()->with('status', 'Emailul către „' . $comanda->transportator->nume . '” a fost trimis cu succes!');
        } else {
            return back()->with('error', 'Nu există un email valid!');
        }
    }

    public function stare(Request $request, Comanda $comanda, $stare = null)
    {
        switch ($stare) {
            case 'deschide':
                $comanda->stare = 1;
                break;
            case 'inchide':
                $comanda->stare = 2;
                break;
            case 'anuleaza':
                $comanda->stare = 3;
                break;
        }

        $comanda->save();

        // Salvare in istoric a comenzii
        if ($comanda->wasChanged()){
            $comanda_istoric = new ComandaIstoric;
            $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
            $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
            $comanda_istoric->operare_descriere = 'Modificare stare';
            $comanda_istoric->save();
        }

        return back()->with('status', 'Comanda „' . $comanda->transportator_contract . '” a fost ' . (($comanda->stare === 1) ? 'deschisa' : (($comanda->stare === 2) ? 'inchisa' : (($comanda->stare === 3) ? 'anulata' : '' ))) . '!');
    }

    public function comandaAdaugaResursa(Request $request, Comanda $comanda, $resursa = null, $tip = null, $ordine = null)
    {
        $request->session()->put('comandaRequest', $request->all());

        switch($resursa){
            case 'transportator':
                $request->session()->put('firma_return_url', url()->previous());
                return redirect('/firme/transportatori/adauga');
                break;
            case 'client':
                // dd('here', $ordine);
                $request->session()->put('firma_return_url', url()->previous());
                $request->session()->put('comandaFirmaOrdine', $ordine);
                return redirect('/firme/clienti/adauga');
                break;
            case 'camion':
                $request->session()->put('camion_return_url', url()->previous());
                return redirect('/camioane/adauga');
                break;
            case 'loc-operare':
                $request->session()->put('locOperareReturnUrl', url()->previous());
                $request->session()->put('comandaLocOperareTip', $tip);
                $request->session()->put('comandaLocOperareOrdine', $ordine);
                return redirect('locuri-operare/adauga');
                break;
        }

    }
}
