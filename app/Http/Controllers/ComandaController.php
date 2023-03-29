<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Comanda;
use App\Models\ComandaIstoric;
use App\Models\ComandaCronjob;
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

use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

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
        $searchStare = $request->searchStare;
        $searchUser = $request->searchUser;
        $searchTransportatorId = $request->searchTransportatorId;
        $searchClientId = $request->searchClientId;

        $query = Comanda::with('client:id,nume', 'transportator:id,nume', 'mesajeTrimiseEmail:id,comanda_id,categorie,email,created_at', 'mesajeTrimiseSms:id,categorie,subcategorie,referinta_id,telefon,mesaj,content,trimis,raspuns,created_at', 'user:id,name')
            ->withCount('contracteTrimisePeEmailCatreTransportator')
            ->when($searchDataCreare, function ($query, $searchDataCreare) {
                return $query->whereDate('data_creare', $searchDataCreare);
            })
            ->when($searchTransportatorContract, function ($query, $searchTransportatorContract) {
                return $query->where('transportator_contract', 'like', '%' . $searchTransportatorContract . '%');
            })
            ->when($searchStare, function ($query, $searchStare) {
                return $query->where('stare', $searchStare);
            })
            ->when($searchUser, function ($query, $searchUser) {
                return $query->whereHas('user', function ($query) use ($searchUser) {
                    $query->where('id', $searchUser);
                });
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
// dd($comenzi);
        $firmeClienti = Firma::select('id', 'nume')->where('tip_partener', 1)->orderBy('nume')->get();
        $firmeTransportatori = Firma::select('id', 'nume')->where('tip_partener', 2)->orderBy('nume')->get();
        $useri = User::select('id' , 'name')->get();

        return view('comenzi.index', compact('comenzi', 'firmeClienti', 'firmeTransportatori', 'useri', 'searchDataCreare', 'searchTransportatorContract', 'searchStare', 'searchUser', 'searchTransportatorId', 'searchClientId'));
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
        $comanda->interval_notificari = '03:00:00';
        $comanda->transportator_limba_id = 1; // Romana
        $comanda->transportator_tarif_pe_km = 0;
        $comanda->client_limba_id = 1; // Romana
        $comanda->client_tarif_pe_km = 0;
        $comanda->user_id = $request->user()->id;
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
            echo '1';
            session()->put('_old_input', $request->session()->pull('comandaRequest', 'default'));
            if ($request->session()->exists('comandaFirmaTip')) {
                if ($request->session()->pull('comandaFirmaTip') === 'clienti') {
                    session()->put('_old_input.client_client_id', $request->session()->pull('comandaFirmaId', ''));
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
        // $incarcari = $comanda->locuriOperareIncarcari()->get();
        // $descarcari = $comanda->locuriOperareDescarcari()->get();

        $request->session()->get('ComandaReturnUrl') ?? $request->session()->put('ComandaReturnUrl', url()->previous());

        return view('comenzi.edit', compact('comanda', 'firmeClienti', 'firmeTransportatori', 'limbi', 'monede', 'procenteTVA', 'metodeDePlata', 'termeneDePlata', 'camioane'));
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
        unset($comanda_campuri['incarcari']);
        unset($comanda_campuri['descarcari']);
        $comanda->update($comanda_campuri);

        // Salvare in istoric a comenzii
        if ($comanda->wasChanged()){
            $comanda_istoric = new ComandaIstoric;
            $comanda_istoric->fill($comanda->makeHidden(['created_at', 'updated_at'])->attributesToArray());
            $comanda_istoric->operare_user_id = auth()->user()->id ?? null;
            $comanda_istoric->operare_descriere = 'Modificare';
            $comanda_istoric->save();
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
            if ($comanda->client->tara->gmt_offset ?? ''){
                // GMT +3 ora Romaniei - GTM ora tarii unde este clientul
                $diferenta_fus_orar = 3-substr($comanda->client->tara->gmt_offset, 0, -3);
            } else {
                $diferenta_fus_orar = 0;
            }

            $cronjob = ComandaCronJob::where('comanda_id', $comanda->id)->first() ?? new ComandaCronJob;
            $cronjob->comanda_id = $comanda->id;
            $cronjob->inceput = Carbon::parse($comanda->primaIncarcare()->pivot->data_ora)->addHours($diferenta_fus_orar);
            $cronjob->sfarsit = Carbon::parse($comanda->ultimaDescarcare()->pivot->data_ora)->addHours($diferenta_fus_orar);
            // if (!isset ($cronjob->urmatorul_mesaj_incepand_cu)){
            //     $cronjob->urmatorul_mesaj_incepand_cu = Carbon::parse($comanda->primaIncarcare()->pivot->data_ora)->addHours($diferenta_fus_orar);
            // }
            // $cronjob->save();
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
        $comanda->locuriOperare()->detach();
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

        return $request->validate(
            [
                'data_creare' => 'required',
                'interval_notificari' => 'required',
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
                'incarcari.*.pivot.durata' => 'required',

                'descarcari.*.id' => 'required',
                'descarcari.*.pivot.data_ora' => 'required',
                'descarcari.*.pivot.durata' => 'required',

                // 'observatii' => 'nullable|max:2000',
            ],
            [
                'transportator_transportator_id.required' => 'Câmpul Transportator este obligatoriu',
                'client_client_id.required' => 'Câmpul Client este obligatoriu',
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
                $request->session()->put('firma_return_url', url()->previous());
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
