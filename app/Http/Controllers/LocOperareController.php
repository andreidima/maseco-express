<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LocOperare;
use App\Models\LocOperareIstoric;
use App\Models\Tara;

class LocOperareController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->session()->forget('locOperareReturnUrl');

        $searchNume = $request->searchNume;
        $searchPersoanaContact = $request->searchPersoanaContact;
        $searchTelefon = $request->searchTelefon;

        $query = LocOperare::with('tara')
            ->when($searchNume, function ($query, $searchNume) {
                return $query->where('nume', 'like', '%' . $searchNume . '%');
            })
            ->when($searchPersoanaContact, function ($query, $searchPersoanaContact) {
                return $query->where('persoana_contact', 'like', '%' . $searchPersoanaContact . '%');
            })
            ->when($searchTelefon, function ($query, $searchTelefon) {
                return $query->where('telefon', 'like', '%' . $searchTelefon . '%');
            })
            ->latest();

        $locuriOperare = $query->simplePaginate(25);

        return view('locuriOperare.index', compact('locuriOperare', 'searchNume', 'searchPersoanaContact', 'searchTelefon'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $tari = Tara::select('id', 'nume')->orderBy('nume')->get();

        $request->session()->get('locOperareReturnUrl') ?? $request->session()->put('locOperareReturnUrl', url()->previous());

        return view('locuriOperare.create', compact('tari'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $locOperare = LocOperare::create($this->validateRequest($request));

        // Salvare in istoric
        $locOperareIstoric = new LocOperareIstoric;
        $locOperareIstoric->fill($locOperare->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $locOperareIstoric->operare_user_id = auth()->user()->id ?? null;
        $locOperareIstoric->operare_descriere = 'Adaugare';
        $locOperareIstoric->save();

        // Daca locul de operare a fost adaugat din formularul Comanda, se trimite in sesiune, pentru a fi folosit in comanda
        if ($request->session()->exists('comandaRequest')) {
            $request->session()->put('comandaLocOperareId', $locOperare->id);
        }

        return redirect($request->session()->get('locOperareReturnUrl') ?? ('/locuri-operare'))->with('status', 'Locul de operare „' . ($locOperare->nume ?? '') . '” a fost adăugat cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\LocOperare  $locOperare
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, LocOperare $locOperare)
    {
        $this->authorize('update', $locOperare);

        $request->session()->get('locOperareReturnUrl') ?? $request->session()->put('locOperareReturnUrl', url()->previous());

        return view('locuriOperare.show', compact('locOperare'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\LocOperare  $locOperare
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, LocOperare $locOperare)
    {
        $tari = Tara::select('id', 'nume')->orderBy('nume')->get();

        $request->session()->get('locOperareReturnUrl') ?? $request->session()->put('locOperareReturnUrl', url()->previous());

        return view('locuriOperare.edit', compact('tari', 'locOperare'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\LocOperare  $locOperare
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, LocOperare $locOperare)
    {
        $locOperare->update($this->validateRequest($request));

        // Salvare in istoric
        if ($locOperare->wasChanged()){
            $locOperareIstoric = new LocOperareIstoric;
            $locOperareIstoric->fill($locOperare->makeHidden(['created_at', 'updated_at'])->attributesToArray());
            $locOperareIstoric->operare_user_id = auth()->user()->id ?? null;
            $locOperareIstoric->operare_descriere = 'Modificare';
            $locOperareIstoric->save();
        }

        return redirect($request->session()->get('locOperareReturnUrl') ?? ('/locuri-operare'))->with('status', 'Locul de operare „' . ($locOperare->nume ?? '') . '” a fost modificat cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\LocOperare  $locOperare
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, LocOperare $locOperare)
    {
        if (count($locOperare->comenzi) > 0){
            return back()->with('error', 'Nu puteți șterge locul de operare „' . ($locOperare->nume ?? '') . '” pentru că este adăugat la comenzi!');
        }

        // Salvare in istoric
        $locOperareIstoric = new LocOperareIstoric;
        $locOperareIstoric->fill($locOperare->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $locOperareIstoric->operare_user_id = auth()->user()->id ?? null;
        $locOperareIstoric->operare_descriere = 'Stergere';
        $locOperareIstoric->save();

        $locOperare->delete();

        return back()->with('status', 'Locul de operare „' . ($locOperare->nume ?? '') . '” a fost șters cu succes!');
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
                'tara_id' => 'required|numeric',
                'judet' => 'nullable|max:500',
                'oras' => 'nullable|max:500',
                'adresa' => 'nullable|max:500',
                'cod_postal' => 'nullable|max:500',
                'persoana_contact' => 'nullable|max:500',
                'skype' => 'nullable|max:500',
                'telefon' => 'nullable|max:500',
                'observatii' => '',
            ],
            [
                'tara_id.required' => 'Câmpul țara este obligatoriu'
            ]
        );
    }
}
