<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Factura;
use App\Models\FacturaProdus;
use App\Models\Comanda;
use App\Models\Moneda;
use App\Models\ProcentTVA;
use App\Models\CursBnr;
use App\Models\Firma;
use App\Models\Tara;
use App\Models\Limba;
use App\Models\FacturaChitanta;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FacturaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->session()->forget('facturaReturnUrl');

        $searchSeria = $request->searchSeria;
        $searchNumar = $request->searchNumar;

        $query = Factura::with('comanda')
            ->when($searchSeria, function ($query, $searchSeria) {
                return $query->where('seria', $searchSeria);
            })
            ->when($searchNumar, function ($query, $searchNumar) {
                return $query->where('numar', $searchNumar);
            })
            ->latest();

        $facturi = $query->simplePaginate(25);

        return view('facturi.index', compact('facturi', 'searchSeria', 'searchNumar'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->session()->get('facturaReturnUrl') ?? $request->session()->put('facturaReturnUrl', url()->previous());

        $factura = new Factura;
        $factura->data = Carbon::now();
        $factura->intocmit_de = auth()->user()->name;

        $firmeClienti = Firma::select('id', 'nume')->where('tip_partener', 1)->orderBy('nume')->get();
        $tari = Tara::select('id', 'nume')->get();
        $monede = Moneda::select('id', 'nume')->get();
        $procenteTva = ProcentTVA::select('id', 'nume')->get();

        $dateFacturiIntocmitDeVechi = Factura::whereIn('id', Factura::selectRaw('max(id) as id, intocmit_de')->groupBy('intocmit_de')->get()->pluck('id'))->select('intocmit_de', 'cnp')->orderBy('intocmit_de')->get();
        $dateFacturiDelegatVechi = Factura::whereIn('id', Factura::selectRaw('max(id) as id, delegat')->groupBy('delegat')->get()->pluck('id'))->select('delegat', 'buletin', 'auto')->orderBy('delegat')->get();
        $dateFacturiMentiuniVechi = Factura::whereIn('id', Factura::selectRaw('max(id) as id, mentiuni')->groupBy('mentiuni')->get()->pluck('id'))->select('mentiuni', 'created_at')->latest()->get();

        return view('facturi.create', compact('factura', 'firmeClienti', 'tari', 'monede', 'procenteTva', 'dateFacturiIntocmitDeVechi', 'dateFacturiDelegatVechi', 'dateFacturiMentiuniVechi'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validateRequest($request);
        // dd($request);

        // Curs BNR - se actualizeaza daca este cazul
        // Cursul bnr se actualizeaza pe site-ul BNR in fiecare zi imediat dupa ora 13:00
        $cursBnrEur = CursBnr::where('moneda_nume', 'EUR')->first();
        if ( ((Carbon::now()->hour >= 14) && (Carbon::parse($cursBnrEur->updated_at)->lessThan(Carbon::now()->hour(14))))
            || ((Carbon::now()->hour < 14) && (Carbon::parse($cursBnrEur->updated_at)->lessThan(Carbon::yesterday()->hour(14))))
        ){
            $xml = @simplexml_load_file('https://www.bnr.ro/nbrfxrates.xml'); // @ - error supression
            if (!$xml){ // daca xml nu este citit corect de pe site-ul bnr, se intoarce inapoi cu eroare
                return back()->with('error', 'Cursul valutar nu a putut fi preluat de la „Banca Națională a României”. Reîncercați.')->withInput();
            }
            foreach($xml->Body->Cube->children() as $curs_bnr) {
                $monedaDb = CursBnr::where('moneda_nume', (string) $curs_bnr['currency'])->first();
                if ($monedaDb){
                    $monedaDb->update(['valoare' => ($curs_bnr[0] / ($curs_bnr['multiplier'] ?? 1)), 'updated_at' => Carbon::now()]);
                }
            }
        }

        $datePersonale = DB::table('date_personale')->where('id', 1)->first();

        $factura = new Factura;
        $factura->furnizor_nume = $datePersonale->nume;
        $factura->furnizor_reg_com = $datePersonale->reg_com;
        $factura->furnizor_cif = $datePersonale->cif;
        $factura->furnizor_adresa = $datePersonale->adresa;
        $factura->furnizor_banca = $datePersonale->banca_nume;
        $factura->furnizor_swift_code = $datePersonale->swift_code;
        $factura->furnizor_iban_eur = $datePersonale->iban_eur;
        $factura->furnizor_iban_eur_banca = $datePersonale->iban_eur_banca;
        $factura->furnizor_iban_ron	 = $request->furnizor_iban_ron;
        $factura->furnizor_iban_ron_banca = $datePersonale->iban_ron_banca;
        $factura->furnizor_capital_social = $datePersonale->capital_social;
        $factura->seria = $request->seria;
        $factura->numar = (Factura::select('numar')->where('seria', $request->seria)->latest()->first()->numar ?? 0) + 1;
        $factura->data = $request->data;
        $factura->moneda_id = $request->moneda_id;
        $factura->curs_moneda = CursBnr::select('valoare')->where('moneda_nume', $factura->moneda->nume ?? 0)->first()->valoare;
        $factura->procent_tva_id = $request->procent_tva_id;
        $factura->zile_scadente = $request->zile_scadente;
        $factura->alerte_scadenta = $request->alerte_scadenta;
        $factura->client_id = $request->client_id;
        $factura->client_nume = $request->client_nume;
        $factura->client_reg_com = $request->client_reg_com;
        $factura->client_cif = $request->client_cif;
        $factura->client_adresa = $request->client_adresa;
        $factura->client_tara_id = $request->client_tara_id;
        $factura->client_telefon = $request->client_telefon;
        $factura->client_email = $request->client_email;
        $factura->total_fara_tva_moneda = $request->total_fara_tva_moneda;
        $factura->total_tva_moneda = $request->total_tva_moneda;
        $factura->total_moneda = $factura->total_fara_tva_moneda + $factura->total_tva_moneda;
        $factura->total_tva_lei = $factura->total_tva_moneda * $factura->curs_moneda;
        $factura->total_fara_tva_lei = $factura->total_fara_tva_moneda * $factura->curs_moneda;
        $factura->total_lei = $factura->total_tva_lei + $factura->total_fara_tva_lei;
        $factura->intocmit_de = $request->intocmit_de;
        $factura->cnp = $request->cnp;
        $factura->aviz_insotire = $request->aviz_insotire;
        $factura->delegat = $request->delegat;
        $factura->buletin = $request->buletin;
        $factura->auto = $request->auto;
        $factura->mentiuni = $request->mentiuni;
        $factura->save();

        foreach ($request->produse as $key=>$produs){
            $produsDb = new FacturaProdus;
            $produsDb->factura_id = $factura->id;
            $produsDb->comanda_id = $produs['comanda_id'];
            $produsDb->nr_crt = $key + 1;
            $produsDb->denumire = $produs['denumire'];
            $produsDb->um = $produs['um'];
            $produsDb->cantitate = $produs['cantitate'];
            $produsDb->pret_unitar_fara_tva = $produs['pret_unitar_fara_tva'];
            $produsDb->valoare = $produs['valoare'];
            $produsDb->valoare_tva = $produs['valoare_tva'];
            $produsDb->save();
        }

        if ($request->chitanta_suma_incasata){
            $chitanta = new FacturaChitanta;
            $chitanta->factura_id = $factura->id;
            $chitanta->seria = $factura->seria;
            $chitanta->numar = (FacturaChitanta::select('numar')->where('seria', $chitanta->seria)->latest()->first()->numar ?? 0) + 1;
            $chitanta->data = $factura->data;
            $chitanta->suma = $request->chitanta_suma_incasata;
            $chitanta->save();
        }

        return redirect($request->session()->get('facturaReturnUrl') ?? ('/facturi'))->with('status', 'Factura seria ' . $factura->seria . ' nr. ' . $factura->numar . ' a fost adăugată cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Factura  $factura
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Factura $factura)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Factura  $factura
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Factura $factura)
    {
        $request->session()->get('facturaReturnUrl') ?? $request->session()->put('facturaReturnUrl', url()->previous());

        $firmeClienti = Firma::select('id', 'nume')->where('tip_partener', 1)->orderBy('nume')->get();
        $tari = Tara::select('id', 'nume')->get();
        $monede = Moneda::select('id', 'nume')->get();
        $procenteTva = ProcentTVA::select('id', 'nume')->get();

        $dateFacturiIntocmitDeVechi = Factura::whereIn('id', Factura::selectRaw('max(id) as id, intocmit_de')->groupBy('intocmit_de')->get()->pluck('id'))->select('intocmit_de', 'cnp')->orderBy('intocmit_de')->get();
        $dateFacturiDelegatVechi = Factura::whereIn('id', Factura::selectRaw('max(id) as id, delegat')->groupBy('delegat')->get()->pluck('id'))->select('delegat', 'buletin', 'auto')->orderBy('delegat')->get();
        $dateFacturiMentiuniVechi = Factura::whereIn('id', Factura::selectRaw('max(id) as id, mentiuni')->groupBy('mentiuni')->get()->pluck('id'))->select('mentiuni', 'created_at')->latest()->get();

        return view('facturi.edit', compact('factura', 'firmeClienti', 'tari', 'monede', 'procenteTva', 'dateFacturiIntocmitDeVechi', 'dateFacturiDelegatVechi', 'dateFacturiMentiuniVechi'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Factura  $factura
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Factura $factura)
    {
        $this->validateRequest($request);
        // dd($request);

        // Curs BNR - se actualizeaza daca este cazul
        // Cursul bnr se actualizeaza pe site-ul BNR in fiecare zi imediat dupa ora 13:00
        $cursBnrEur = CursBnr::where('moneda_nume', 'EUR')->first();
        if ( ((Carbon::now()->hour >= 14) && (Carbon::parse($cursBnrEur->updated_at)->lessThan(Carbon::now()->hour(14))))
            || ((Carbon::now()->hour < 14) && (Carbon::parse($cursBnrEur->updated_at)->lessThan(Carbon::yesterday()->hour(14))))
        ){
            $xml = @simplexml_load_file('https://www.bnr.ro/nbrfxrates.xml'); // @ - error supression
            if (!$xml){ // daca xml nu este citit corect de pe site-ul bnr, se intoarce inapoi cu eroare
                return back()->with('error', 'Cursul valutar nu a putut fi preluat de la „Banca Națională a României”. Reîncercați.')->withInput();
            }
            foreach($xml->Body->Cube->children() as $curs_bnr) {
                $monedaDb = CursBnr::where('moneda_nume', (string) $curs_bnr['currency'])->first();
                if ($monedaDb){
                    $monedaDb->update(['valoare' => ($curs_bnr[0] / ($curs_bnr['multiplier'] ?? 1)), 'updated_at' => Carbon::now()]);
                }
            }
        }

        $factura->data = $request->data;
        $factura->moneda_id = $request->moneda_id;
        $factura->curs_moneda = CursBnr::select('valoare')->where('moneda_nume', $factura->moneda->nume ?? 0)->first()->valoare;
        $factura->procent_tva_id = $request->procent_tva_id;
        $factura->zile_scadente = $request->zile_scadente;
        $factura->alerte_scadenta = $request->alerte_scadenta;
        $factura->client_id = $request->client_id;
        $factura->client_nume = $request->client_nume;
        $factura->client_reg_com = $request->client_reg_com;
        $factura->client_cif = $request->client_cif;
        $factura->client_adresa = $request->client_adresa;
        $factura->client_tara_id = $request->client_tara_id;
        $factura->client_telefon = $request->client_telefon;
        $factura->client_email = $request->client_email;
        $factura->total_fara_tva_moneda = $request->total_fara_tva_moneda;
        $factura->total_tva_moneda = $request->total_tva_moneda;
        $factura->total_moneda = $factura->total_fara_tva_moneda + $factura->total_tva_moneda;
        $factura->total_tva_lei = $factura->total_tva_moneda * $factura->curs_moneda;
        $factura->total_fara_tva_lei = $factura->total_fara_tva_moneda * $factura->curs_moneda;
        $factura->total_lei = $factura->total_tva_lei + $factura->total_fara_tva_lei;
        $factura->intocmit_de = $request->intocmit_de;
        $factura->cnp = $request->cnp;
        $factura->aviz_insotire = $request->aviz_insotire;
        $factura->delegat = $request->delegat;
        $factura->buletin = $request->buletin;
        $factura->auto = $request->auto;
        $factura->furnizor_iban_ron	 = $request->furnizor_iban_ron;
        $factura->mentiuni = $request->mentiuni;
        $factura->save();

        // Stergerea produselor ce nu mai sunt la factura
        FacturaProdus::where('factura_id', $factura->id)->whereNotIn('id', collect($request->produse)->whereNotNull('id')->pluck('id'))->delete();
        // Adaugarea / modificarea produselor facturii
        foreach ($request->produse as $key=>$produs){
            $produs['id'] ? ($produsDb = FacturaProdus::find($produs['id'])) : ($produsDb = new FacturaProdus);
            $produsDb->factura_id = $factura->id;
            $produsDb->comanda_id = $produs['comanda_id'];
            $produsDb->nr_crt = $key + 1;
            $produsDb->denumire = $produs['denumire'];
            $produsDb->um = $produs['um'];
            $produsDb->cantitate = $produs['cantitate'];
            $produsDb->pret_unitar_fara_tva = $produs['pret_unitar_fara_tva'];
            $produsDb->valoare = $produs['valoare'];
            $produsDb->valoare_tva = $produs['valoare_tva'];
            $produsDb->save();
        }

        if ($request->chitanta_suma_incasata){
            if ($chitanta = FacturaChitanta::where('factura_id', $factura->id)->first()){
                $chitanta->data = $factura->data;
                $chitanta->suma = $request->chitanta_suma_incasata;
                $chitanta->save();
            } else {
                $chitanta = new FacturaChitanta;
                $chitanta->factura_id = $factura->id;
                $chitanta->seria = $factura->seria;
                $chitanta->numar = (FacturaChitanta::select('numar')->where('seria', $chitanta->seria)->latest()->first()->numar ?? 0) + 1;
                $chitanta->data = $factura->data;
                $chitanta->suma = $request->chitanta_suma_incasata;
                $chitanta->save();
            }
        } else {
            FacturaChitanta::where('factura_id', $factura->id)->delete();
        }

        return redirect($request->session()->get('facturaReturnUrl') ?? ('/facturi'))->with('status', 'Factura seria ' . $factura->seria . ' nr. ' . $factura->numar . ' a fost modificată cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Factura  $factura
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Factura $factura)
    {
        $factura->delete();

        // Daca se sterge o factura Storno, cea originala se reactiveaza
        if ($factura->stornare_factura_id_originala !== null){
            Factura::find($factura->stornare_factura_id_originala)->update(['stornata' => 0]);
        }

        return back()->with('status', 'Factura seria ' . $factura->seria . ' nr. ' . $factura->numar . ' a fost ștearsă cu succes!');
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
                'seria' => $request->isMethod('post') ? 'required|max:5' : '',
                'data' => 'required',
                'moneda_id' => 'required',
                'procent_tva_id' => 'required|numeric|between:0,100',
                'zile_scadente' => 'required|numeric|between:1,100',
                'alerte_scadenta' =>
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value){
                            $zileInainte = preg_split ("/\,/", $value);
                            foreach ($zileInainte as $ziInainte){
                                if (!(intval($ziInainte) == $ziInainte)){
                                    $fail('Câmpul „Cu câte zile înainte de scadență să se trimită memento” nu este completat corect');
                                }elseif ($ziInainte < 0){
                                    $fail('Câmpul „Cu câte zile înainte de scadență să se trimită memento” nu poate conține valori negative');
                                }elseif ($ziInainte > 100){
                                    $fail('Câmpul „Cu câte zile înainte de scadență să se trimită memento” nu poate conține valori mai mari de 100');
                                }
                            }
                        }
                    },

                // 'client_id' => 'required',
                'client_nume' => 'required|max:500',
                'client_reg_com' => 'nullable|max:500',
                'client_cif' => 'nullable|max:500',
                'client_adresa' => 'nullable|max:500',
                'client_tara_id' => 'required',
                'client_telefon' => 'nullable|max:500',
                'client_email' => 'nullable|email:rfc,dns|max:500|required_with:alerte_scadenta',

                'produse' => 'required',
                'produse.*.comanda_id' => '',
                'produse.*.denumire' => 'required',
                'produse.*.um' => 'required',
                'produse.*.cantitate' => 'required',
                'produse.*.pret_unitar_fara_tva' => 'required',
                'produse.*.valoare' => 'required',
                'produse.*.valoare_tva' => 'required',
                'chitanta_suma_incasata' => 'nullable|numeric',

                'intocmit_de' => 'required|max:500',
                'cnp' => 'nullable|max:500',
                'aviz_insotire' => 'nullable|max:500',
                'delegat' => 'nullable|max:500',
                'buletin' => 'nullable|max:500',
                'auto' => 'nullable|max:500',
                'furnizor_iban_ron' => 'required|max:500',
                'mentiuni' => 'nullable|max:2000',

            ],
            [
                'produse.*.denumire' => 'Produsul #:position trebuie sa aibă o denumire',
                'produse.*.um' => 'Produsul #:position trebuie sa aibă o unitate de măsură',
                'produse.*.cantitate' => 'Produsul #:position trebuie sa aibă o cantitate',
                'produse.*.pret_unitar_fara_tva' => 'Produsul #:position trebuie sa aibă un pret unitar fără tva',
                'produse.*.valoare' => 'Produsul #:position trebuie sa aibă o valoare',
                'produse.*.valoare_tva' => 'Produsul #:position trebuie sa aibă o valoare tva',
            ]
        );
    }

    public function axiosCautaClient(Request $request)
    {
        $client = Firma::with('tara')->find($request->client_id);

        return response()->json([
            'client' => $client,
        ]);
    }

    public function axiosCautaComanda(Request $request)
    {
        $comanda = Comanda::with('client.tara', 'clientMoneda', 'clientProcentTva', 'locuriOperareIncarcari', 'locuriOperareDescarcari')->where('transportator_contract', 'MSX-' . $request->numarDeCautat)->first();

        return response()->json([
            'comanda' => $comanda,
        ]);
    }

    public function storneaza(Request $request, Factura $factura = null)
    {
        $factura_storno = $factura->replicate();
        $factura_storno->numar = (Factura::select('numar')->where('seria', $factura->seria)->latest()->first()->numar ?? 0) + 1;
        $factura_storno->total_fara_tva_moneda = -$factura->total_fara_tva_moneda;
        $factura_storno->total_fara_tva_lei = -$factura->total_fara_tva_lei;
        $factura_storno->total_tva_moneda = -$factura->total_tva_moneda;
        $factura_storno->total_tva_lei = -$factura->total_tva_lei;
        $factura_storno->total_plata_moneda = -$factura->total_plata_moneda;
        $factura_storno->total_plata_lei = -$factura->total_plata_lei;
        $factura_storno->stornare_factura_id_originala = $factura->id;
        $factura_storno->stornare_motiv = $request->stornare_motiv ?? '';
        $factura_storno->save();

        $produs = $factura->produse()->first()->replicate();
        $produs->factura_id = $factura_storno->id;
        $produs->cantitate = -$produs->cantitate;
        $produs->valoare = -$produs->valoare;
        $produs->valoare_tva = -$produs->valoare_tva;
        $produs->save();

        $factura->update(['stornata' => 1]);

        return back()->with('status', 'Factura seria ' . $factura->seria . ' nr. ' . $factura->seria . ' a fost stornată și a fost generată Factură Storno cu success!');

    }

    public function exportPdf(Request $request, Factura $factura)
    {
        $factura = Factura::with('produse', 'facturaOriginala')->find($factura->id);

        if ($request->view_type === 'html') {
            return view('facturi.export.facturaPdf', compact('factura'));
        } elseif ($request->view_type === 'pdf') {
            $pdf = \PDF::loadView('facturi.export.facturaPdf', compact('factura'))
                ->setPaper('a4', 'portrait');
            $pdf->getDomPDF()->set_option("enable_php", true);
            // return $pdf->download('Contract ' . $comanda->transportator_contract . '.pdf');
            // return $pdf->stream();

            $output= \Illuminate\Support\Facades\View::make('facturi.export.facturaPdf')->with(compact('factura'))->render();

            //add xml header - blade does not seem to like it
            $xml = "<?xml version=\"1.0\" ?>\n" . $output;
            return $xml;
        }
    }


    // Zona doar pentru mementouri facturi, create din pagina de comenzi, eventual pana este gata modulul de facturare, daca se va mai face
    public function createOrUpdateMementoFactura(Request $request, Comanda $comanda)
    {
        // Se verifica intai daca clientul acestei comenzi are email atasat, pentru ca altfel nu are unde sa se trimita comanda
        if (!$comanda->client->email_factura){
            return back()->with('error', 'Nu se poate crea memento pentru factură pentru că lipsește emailul de facturare al clientului. Adăugați mai întâi emailul de facturare în fișa clientului.');
        }

        $request->session()->get('ComandaReturnUrl') ?? $request->session()->put('ComandaReturnUrl', url()->previous());

        if (!($factura = $comanda->factura)) {
            $factura = new Factura;
            $factura->data = Carbon::now();
            $factura->client_nume = $comanda->client->nume ?? '';
            $factura->client_email = $comanda->client->email_factura ?? '';
            $factura->client_contract = $comanda->client_contract ?? '';
            $factura->client_limba_id = $comanda->client_limba_id ?? '';
            $factura->save();

            $comanda->factura_id = $factura->id;
            $comanda->save();
        }

        $limbi = Limba::select('id', 'nume')->whereIn('id', [1,2])->get();

        return view('facturi.doarPentruMemento.createOrEditMementoFactura', compact('limbi', 'factura'));
    }
    public function storeOrUpdateMementoFactura(Request $request, Factura $factura)
    {
        $validatedRequest = $request->validate(
            [
                'client_email' => 'required|email:rfc,dns',
                'client_contract' => 'nullable|max:20',
                'client_limba_id' => '',
                'seria' => 'nullable|max:5',
                'numar' => 'required|numeric|min:1',
                'data' => 'required',
                'zile_scadente' => 'required|numeric|between:1,100',
                'alerte_scadenta' => ['required',
                    function ($attribute, $value, $fail) use ($request) {
                        if ($value){
                            $zileInainte = preg_split ("/\,/", $value);
                            foreach ($zileInainte as $ziInainte){
                                if (!(intval($ziInainte) == $ziInainte)){
                                    $fail('Câmpul „Cu câte zile înainte de scadență să se trimită memento” nu este completat corect');
                                }elseif ($ziInainte < 0){
                                    $fail('Câmpul „Cu câte zile înainte de scadență să se trimită memento” nu poate conține valori negative');
                                }elseif ($ziInainte > 100){
                                    $fail('Câmpul „Cu câte zile înainte de scadență să se trimită memento” nu poate conține valori mai mari de 100');
                                }
                            }
                        }
                    }],
            ]
        );

        $factura->update($validatedRequest);

        return redirect($request->session()->get('ComandaReturnUrl') ?? ('/comenzi'))->with('status', 'Mementoul pentru factura „' . $factura->seria . $factura->numar . '” a fost salvat cu succes!');
    }
}
