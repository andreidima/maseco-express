<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Firma;
use App\Models\FirmaIstoric;

class FirmaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->session()->forget('firma_return_url');

        $search_nume = $request->search_nume;
        $search_telefon = $request->search_telefon;
        $search_email = $request->search_email;

        $query = Firma::with('user', 'istoricuri')
            ->when($search_nume, function ($query, $search_nume) {
                return $query->where('nume', 'like', '%' . $search_nume . '%');
            })
            ->when($search_telefon, function ($query, $search_telefon) {
                return $query->where('telefon', 'like', '%' . $search_telefon . '%');
            })
            ->when($search_email, function ($query, $search_email) {
                return $query->where('email', 'like', '%' . $search_email . '%');
            })
            ->latest();

        $firme = $query->simplePaginate(25);

        return view('firme.index', compact('firme', 'search_nume', 'search_telefon', 'search_email'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->session()->get('firma_return_url') ?? $request->session()->put('firma_return_url', url()->previous());

        return view('firme.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $firma = Firma::create($this->validateRequest($request));

        // Salvare in istoric
        $firma_istoric = new FirmaIstoric;
        $firma_istoric->fill($firma->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $firma_istoric->operatie = 'Adaugare';
        $firma_istoric->operatie_user_id = auth()->user()->id ?? null;
        $firma_istoric->save();

        return redirect($request->session()->get('firma_return_url') ?? ('/firme'))->with('status', 'Firma „' . ($firma->nume ?? '') . '” a fost adăugată cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Firma  $firma
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Firma $firma)
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
    public function edit(Request $request, Firma $firma)
    {
        $this->authorize('update', $firma);

        $request->session()->get('firma_return_url') ?? $request->session()->put('firma_return_url', url()->previous());

        return view('firme.edit', compact('firma'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Firma  $firma
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Firma $firma)
    {
        $this->authorize('update', $firma);

        $firma->update($this->validateRequest($request));

        // Salvare in istoric
        if ($firma->wasChanged()){
            $firma_istoric = new FirmaIstoric;
            $firma_istoric->fill($firma->makeHidden(['created_at', 'updated_at'])->attributesToArray());
            $firma_istoric->operatie = 'Modificare';
            $firma_istoric->operatie_user_id = auth()->user()->id ?? null;
            $firma_istoric->save();
        }

        return redirect($request->session()->get('firma_return_url') ?? ('/firme'))->with('status', 'Firma „' . ($firma->nume ?? '') . '” a fost modificată cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Firma  $firma
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Firma $firma)
    {
        $this->authorize('update', $firma);

        // Salvare in istoric
        $firma_istoric = new FirmaIstoric;
        $firma_istoric->fill($firma->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $firma_istoric->operatie = 'Stergere';
        $firma_istoric->operatie_user_id = auth()->user()->id ?? null;
        $firma_istoric->save();

        $firma->delete();

        return back()->with('status', 'Firma „' . ($firma->nume ?? '') . '” a fost șters cu succes!');
    }

    /**
     * Validate the request attributes.
     *
     * @return array
     */
    protected function validateRequest(Request $request)
    {
        // Se adauga userul doar la adaugare, iar la modificare nu se schimba
        if ($request->isMethod('post')) {
            $request->request->add(['user_id' => $request->user()->id]);
        }

        // if ($request->isMethod('post')) {
        //     $request->request->add(['cheie_unica' => uniqid()]);
        // }

        return $request->validate(
            [
                'nume' => 'required|max:500',
                'telefon' => 'nullable|max:500',
                'adresa' => 'nullable|max:500',
                'status' => 'nullable|max:500',
                'intrare' => '',
                'lansare' => '',
                'oferta_pret' => 'nullable|integer',
                'avans' => 'nullable|integer',
                'observatii' => '',
                'user_id' => '',
                // 'cheie_unica' => ''
            ],
            [

            ]
        );
    }

    public function restaurareIstoric(Request $request, Firma $firma = null, FirmaIstoric $firma_istoric = null){
        $firma->nume = $firma_istoric->nume;
        $firma->telefon = $firma_istoric->telefon;
        $firma->adresa = $firma_istoric->adresa;
        $firma->status = $firma_istoric->status;
        $firma->intrare = $firma_istoric->intrare;
        $firma->lansare = $firma_istoric->lansare;
        $firma->oferta_pret = $firma_istoric->oferta_pret;
        $firma->avans = $firma_istoric->avans;
        $firma->observatii = $firma_istoric->observatii;
        $firma->user_id = $firma_istoric->user_id;

        $firma->save();

        // Salvare in istoric
        if ($firma->wasChanged()){
            $firma_istoric = new FirmaIstoric;
            $firma_istoric->fill($firma->makeHidden(['created_at', 'updated_at'])->attributesToArray());
            $firma_istoric->operatie = 'Modificare';
            $firma_istoric->operatie_user_id = auth()->user()->id ?? null;
            $firma_istoric->save();
            return redirect($request->session()->get('firma_return_url') ?? ('/firme'))->with('status', 'Firma „' . ($firma->nume ?? '') . '” a fost restaurată cu succes!');
        } else {
            return redirect($request->session()->get('firma_return_url') ?? ('/firme'))->with('warning', 'Firma „' . ($firma->nume ?? '') . '” nu a avut nimic de restaurat!');
        }

    }
}
