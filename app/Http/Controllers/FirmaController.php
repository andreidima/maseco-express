<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Firma;
use App\Models\FirmaIstoric;
use App\Models\Tara;
use App\Models\Camion;

use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class FirmaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $tipPartener = null)
    {
        $request->session()->forget('firma_return_url');

        $search_nume = $request->search_nume;
        $search_cui = $request->search_cui;
        $search_telefon = $request->search_telefon;
        $search_email = $request->search_email;

        $query = Firma::with('tara')
            ->withCount('contracteCcaTrimisePeEmailCatreTransportator')
            ->when($search_nume, function ($query, $search_nume) {
                return $query->where('nume', 'like', '%' . $search_nume . '%');
            })
            ->when($search_cui, function ($query, $search_cui) {
                return $query->where('cui', $search_cui);
            })
            ->when($search_telefon, function ($query, $search_telefon) {
                return $query->where('telefon', 'like', '%' . $search_telefon . '%');
            })
            ->when($search_email, function ($query, $search_email) {
                return $query->where('email', 'like', '%' . $search_email . '%');
            })
            ->where('tip_partener' , (($tipPartener === 'clienti') ? 1 : (($tipPartener === 'transportatori') ? 2 : '')))
            // ->latest();
            ->orderBy('nume');

        $firme = $query->simplePaginate(25);

        return view('firme.index', compact('firme', 'search_nume', 'search_cui', 'search_telefon', 'search_email', 'tipPartener'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $tipPartener = null)
    {
        $tari = Tara::select('id', 'nume')->orderBy('nume')->get();

        $request->session()->get('firma_return_url') ?? $request->session()->put('firma_return_url', url()->previous());

        return view('firme.create', compact('tipPartener', 'tari'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $tipPartener = null)
    {
        $firma = Firma::create($this->validateRequest($request));

        // Salvare in istoric
        $firma_istoric = new FirmaIstoric;
        $firma_istoric->fill($firma->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $firma_istoric->operare_user_id = auth()->user()->id ?? null;
        $firma_istoric->operare_descriere = 'Adaugare';
        $firma_istoric->save();

        // Daca firma a fost adaugata din formularul Comanda, se trimite in sesiune, pentru a fi folosita in comanda
        if ($request->session()->exists('comandaRequest')) {
            $request->session()->put('comandaFirmaId', $firma->id);
            $request->session()->put('comandaFirmaTip', $tipPartener);
        }

        // Whenever a new client is created into the database, Maseco is alerted by email
        if ($firma->tip_partener === 1) {
            Mail::to('info@masecoexpres.net')->send(new \App\Mail\InformareAdaugareClientNouInDB($firma));
            $emailTrimis = new \App\Models\MesajTrimisEmail;
            $emailTrimis->firma_id = $firma->id;
            $emailTrimis->categorie = 9;
            $emailTrimis->email = 'info@masecoexpres.net';
            $emailTrimis->save();
        }

        return redirect($request->session()->get('firma_return_url') ?? ('/firme/clienti'))->with('status', 'Firma „' . ($firma->nume ?? '') . '” a fost adăugată cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Firma  $firma
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $tipPartener = null, Firma $firma)
    {
        $this->authorize('update', $firma);

        $request->session()->get('firma_return_url') ?? $request->session()->put('firma_return_url', url()->previous());

        return view('firme.show', compact('firma'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Firma  $firma
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $tipPartener = null, Firma $firma)
    {
        $tari = Tara::select('id', 'nume')->orderBy('nume')->get();

        $request->session()->get('firma_return_url') ?? $request->session()->put('firma_return_url', url()->previous());

        return view('firme.edit', compact('tipPartener', 'tari', 'firma'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Firma  $firma
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $tipPartener = null, Firma $firma)
    {
        $firma->update($this->validateRequest($request));

        // Salvare in istoric
        if ($firma->wasChanged()){
            $firma_istoric = new FirmaIstoric;
            $firma_istoric->fill($firma->makeHidden(['created_at', 'updated_at'])->attributesToArray());
            $firma_istoric->operare_user_id = auth()->user()->id ?? null;
            $firma_istoric->operare_descriere = 'Modificare';
            $firma_istoric->save();
        }

        return redirect($request->session()->get('firma_return_url') ?? ('/firme/clienti'))->with('status', 'Firma „' . ($firma->nume ?? '') . '” a fost modificată cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Firma  $firma
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $tipPartener = null, Firma $firma)
    {
        if ($firma->camioane->isNotEmpty()){
            return back()->with('error', 'Nu puteți șterge firma „' . ($firma->nume ?? '') . '” pentru că are camioane atașate! Ștergeți mai întâi camioanele.');
        }
        if ($firma->comenziCaSiClient->isNotEmpty()){
            return back()->with('error', 'Nu puteți șterge firma „' . ($firma->nume ?? '') . '” pentru că are comenzi ca și client! Ștergeți mai întâi comenzile respective.');
        }
        if ($firma->comenziCaSiTransportator->isNotEmpty()){
            return back()->with('error', 'Nu puteți șterge firma „' . ($firma->nume ?? '') . '” pentru că are comenzi ca și transportator! Ștergeți mai întâi comenzile respective.');
        }

        // Salvare in istoric
        $firma_istoric = new FirmaIstoric;
        $firma_istoric->fill($firma->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $firma_istoric->operare_user_id = auth()->user()->id ?? null;
        $firma_istoric->operare_descriere = 'Stergere';
        $firma_istoric->save();

        $firma->delete();

        return back()->with('status', 'Firma „' . ($firma->nume ?? '') . '” a fost ștearsă cu succes!');
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
                'nume' => 'required|max:500',
                'tip_partener' => 'required',
                'tara_id' => 'nullable|numeric',
                'cui' => 'required|max:500',
                'reg_com' => 'nullable|max:500',
                'format_documente' => '',
                'oras' => 'nullable|max:500',
                'judet' => 'nullable|max:500',
                'adresa' => 'nullable|max:500',
                'cod_postal' => 'nullable|max:500',
                'banca' => 'nullable|max:500',
                'cont_iban' => 'nullable|max:500',
                'banca_eur' => 'nullable|max:500',
                'cont_iban_eur' => 'nullable|max:500',
                'zile_scadente' => 'nullable|numeric',
                'persoana_contact' => 'nullable|max:500',
                'skype' => 'nullable|max:500',
                'email' => 'required|email:rfc,dns',
                'email_factura' => 'required|email:rfc,dns',
                'telefon' => 'nullable|max:500',
                'fax' => 'nullable|max:500',
                'website' => 'nullable|max:500',
                'observatii' => '',
            ],
            [
                'tara_id.required' => 'Câmpul țara este obligatoriu.',
                'email_factura.required' => 'Câmpul email contabilitate este obligatoriu.',
                'email_factura.email' => 'Câmpul email contabilitate trebuie să fie o adresă de e-mail validă.',
            ]
        );
    }


    public function contractExportPDF(Request $request, $tipPartener, Firma $firma)
    {
        if (is_null($firma->contract_nr)){
            $firma->contract_nr = (Firma::max('contract_nr') ?? '0') + 1;
            $firma->contract_data = Carbon::now();
            $firma->save();
        }

        if ($request->view_type === 'export-html') {
            return view('firme.export.contractCadruPdf', compact('firma'));
        } elseif ($request->view_type === 'export-pdf') {
            $pdf = \PDF::loadView('firme.export.contractCadruPdf', compact('firma'))
                ->setPaper('a4', 'portrait');
            $pdf->getDomPDF()->set_option("enable_php", true);
            // return $pdf->download('Contract ' . $firma->transportator_contract . '.pdf');
            return $pdf->stream();
        }
    }

    public function contractCcaTrimiteCatreTransportator(Request $request, $tipPartener, Firma $firma)
    {
        if (isset($firma->email)){
            Mail::to($firma->email)->send(new \App\Mail\TrimiteContractCcaCatreTransportator($firma));

            $emailTrimis = new \App\Models\MesajTrimisEmail;
            // $emailTrimis->comanda_id = $comanda->id;
            $emailTrimis->firma_id = $firma->id ?? '';
            $emailTrimis->categorie = 4;
            $emailTrimis->email = $firma->email;
            $emailTrimis->save();

            return back()->with('status', 'Emailul către „' . $firma->nume . '” a fost trimis cu succes!');
        } else {
            return back()->with('error', 'Nu există un email valid!');
        }
    }
}
