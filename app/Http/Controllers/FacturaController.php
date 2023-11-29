<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Factura;
use App\Models\FacturaProdus;
use App\Models\Comanda;
use App\Models\Moneda;
use App\Models\CursBnr;

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

        $monede = Moneda::select('id', 'nume')->get();

        return view('facturi.create', compact('monede'));
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

        // Curs BNR - se actualizeaza daca este cazul
        // Cursul bnr se actualizeaza pe site-ul BNR in fiecare zi imediat dupa ora 13:00
        $cursBnrEur = CursBnr::where('moneda_nume', 'EUR')->first();
        if ( (Carbon::now()->hour >= 14) && (Carbon::parse($cursBnrEur->updated_at)->lessThan(Carbon::now()->hour(14)))
            || (Carbon::now()->hour < 14) && (Carbon::parse($cursBnrEur->updated_at)->lessThan(Carbon::yesterday()->hour(14)))
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
        $factura->seria = $request->seria;
        $factura->numar = (Factura::select('numar')->where('seria', $request->seria)->latest()->first()->numar ?? 0) + 1;
        $factura->data = $request->data;
        $factura->furnizor_nume = $datePersonale->nume;
        $factura->furnizor_reg_com = $datePersonale->reg_com;
        $factura->furnizor_cif = $datePersonale->cif;
        $factura->furnizor_adresa = $datePersonale->adresa;
        $factura->furnizor_banca = $datePersonale->banca_nume;
        $factura->furnizor_swift_code = $datePersonale->swift_code;
        $factura->furnizor_iban_eur = $datePersonale->iban_eur;
        $factura->furnizor_iban_eur_banca = $datePersonale->iban_eur_banca;
        $factura->furnizor_iban_ron	 = $datePersonale->iban_ron;
        $factura->furnizor_iban_ron_banca = $datePersonale->iban_ron_banca;
        $factura->furnizor_capital_social = $datePersonale->capital_social;
        $factura->client_nume = $request->client;
        $factura->client_cif = $request->cif;
        $factura->client_adresa = $request->adresa;
        $factura->client_tara = $request->tara;
        $factura->client_email = $request->email;
        $factura->moneda = Moneda::select('nume')->where('id', $request->moneda)->latest()->first()->nume;
        $factura->curs_moneda = CursBnr::select('valoare')->where('moneda_nume', $factura->moneda)->first()->valoare;
        $factura->intocmit_de = $request->intocmit_de;
        $factura->valoare_contract = $request->valoare_contract;
        $factura->procent_tva = $request->procent_tva;
        $factura->total_tva_moneda = $request->valoare_contract * $request->procent_tva / 100;
        $factura->total_fara_tva_moneda = $request->valoare_contract - $factura->total_tva_moneda;
        $factura->total_plata_moneda = $factura->total_tva_moneda + $factura->total_fara_tva_moneda;
        $factura->total_tva_lei = $factura->total_tva_moneda * $factura->curs_moneda;
        $factura->total_fara_tva_lei = $factura->total_fara_tva_moneda * $factura->curs_moneda;
        $factura->total_plata_lei = $factura->total_tva_lei + $factura->total_fara_tva_lei;
        $factura->zile_scadente = $request->zile_scadente;
        $factura->alerte_scadenta = $request->alerte_scadenta;
        $factura->save();

        $produs = new FacturaProdus;
        $produs->factura_id = $factura->id;
        $produs->comanda_id = $request->comandaId;
        $produs->nr_crt = 1;
        $produs->denumire = $request->produse;
        $produs->um = 'buc';
        $produs->cantitate = 1;
        $produs->pret_unitar = $factura->total_fara_tva_moneda;
        $produs->valoare = $factura->total_fara_tva_moneda;
        $produs->valoare_tva = $factura->total_tva_moneda;
        $produs->save();

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
        if ($factura->anulare_factura_id_originala !== null){
            Factura::find($factura->anulare_factura_id_originala)->update(['anulata' => 0]);
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
                'seria' => 'required|max:5',
                'data' => 'required',
                'intocmit_de' => 'required|max:500',
                'comandaId' => 'required',
                'client' => 'required|max:500',
                'cif' => 'nullable|max:500',
                'adresa' => 'nullable|max:500',
                'tara' => 'nullable|max:500',
                'email' => 'nullable|email:rfc,dns|max:500|required_with:alerte_scadenta',
                'produse' => 'required|max:500',
                'valoare_contract' => 'required|numeric|min:-9999999|max:9999999',
                'procent_tva' => 'required|numeric|between:0,100',
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
            ],
            [
                // 'tara_id.required' => 'Câmpul țara este obligatoriu'
            ]
        );
    }

    public function axiosCautaComanda(Request $request)
    {
        $comanda = Comanda::with('client', 'clientMoneda', 'clientProcentTva', 'locuriOperareIncarcari', 'locuriOperareDescarcari')->where('transportator_contract', $request->serieSiNumarDeCautat)->first();

        return response()->json([
            'comanda' => $comanda,
        ]);
    }

    public function anuleaza(Request $request, Factura $factura = null)
    {
        $factura_storno = $factura->replicate();
        $factura_storno->numar = (Factura::select('numar')->where('seria', $factura->seria)->latest()->first()->numar ?? 0) + 1;
        $factura_storno->total_fara_tva_moneda = -$factura->total_fara_tva_moneda;
        $factura_storno->total_fara_tva_lei = -$factura->total_fara_tva_lei;
        $factura_storno->total_tva_moneda = -$factura->total_tva_moneda;
        $factura_storno->total_tva_lei = -$factura->total_tva_lei;
        $factura_storno->total_plata_moneda = -$factura->total_plata_moneda;
        $factura_storno->total_plata_lei = -$factura->total_plata_lei;
        $factura_storno->anulare_factura_id_originala = $factura->id;
        $factura_storno->anulare_motiv = $request->anulare_motiv ?? '';
        $factura_storno->save();

        $produs = $factura->produse()->first()->replicate();
        $produs->factura_id = $factura_storno->id;
        $produs->cantitate = -$produs->cantitate;
        $produs->valoare = -$produs->valoare;
        $produs->valoare_tva = -$produs->valoare_tva;
        $produs->save();

        $factura->update(['anulata' => 1]);

        return back()->with('status', 'Factura seria ' . $factura->seria . ' nr. ' . $factura->seria . ' a fost anulată și a fost generată Factură Storno cu success!');

    }

    public function exportPdf(Request $request, Factura $factura)
    {
        if ($request->view_type === 'html') {
            return view('facturi.export.facturaPdf', compact('factura'));
        } elseif ($request->view_type === 'pdf') {
            $pdf = \PDF::loadView('facturi.export.facturaPdf', compact('factura'))
                ->setPaper('a4', 'portrait');
            $pdf->getDomPDF()->set_option("enable_php", true);
            // return $pdf->download('Contract ' . $comanda->transportator_contract . '.pdf');
            return $pdf->stream();
        }
    }
}
