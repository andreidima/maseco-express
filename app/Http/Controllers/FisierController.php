<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Fisier;
use App\Models\FisierIstoric;

use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;

class FisierController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $categorieFisier = null)
    {
        $request->session()->forget('fisier_return_url');

        $search_nume = $request->search_nume;
        $search_fisier = $request->search_fisier;

        $query = Fisier::
            when($search_nume, function ($query, $search_nume) {
                return $query->where('nume', 'like', '%' . $search_nume . '%');
            })
            ->when($search_fisier, function ($query, $search_fisier) {
                return $query->where('fisier_nume', 'like', '%' . $search_fisier . '%');
            })
            ->where('categorie' , (($categorieFisier === 'maseco') ? 1 : (($categorieFisier === 'masini') ? 2 : '')))
            ->latest();

        $fisiere = $query->simplePaginate(25);

        return view('fisiere.index', compact('fisiere', 'search_nume', 'search_fisier', 'categorieFisier'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request, $categorieFisier = null)
    {
        $request->session()->get('fisier_return_url') ?? $request->session()->put('fisier_return_url', url()->previous());

        return view('fisiere.create', compact('categorieFisier'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $categorieFisier = null)
    {
        $this->validateRequest($request);

        $fisier = $request->file('fisier');

        $fisierNume = $fisier->getClientOriginalName();
        $fisierCale = "uploads/" . $categorieFisier . '/';

        if (!$fisierNume || Storage::exists($fisierCale . $fisierNume)){
            return back()->with('eroare', 'Baza de date conține deja un fișier cu această denumire. Schimbați numele fișierului dacă doriți să îl încărcați de mai multe ori');
        }

        $fisier->storeAs($fisierCale, $fisierNume);

        $fisier_database = new Fisier;
        $fisier_database->nume = $request->nume;
        $fisier_database->categorie = $request->categorie;
        $fisier_database->fisier_nume = $fisierNume;
        $fisier_database->fisier_cale = $fisierCale;
        $fisier_database->observatii = $request->observatii;
        $fisier_database->save();

        // Salvare in istoric
        $fisier_istoric = new FisierIstoric;
        $fisier_istoric->fill($fisier_database->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $fisier_istoric->operare_user_id = auth()->user()->id ?? null;
        $fisier_istoric->operare_descriere = 'Adaugare';
        $fisier_istoric->save();

        return redirect($request->session()->get('fisier_return_url') ?? ('/fisiere/maseco'))->with('status', 'Fisierul „' . ($fisier->nume ?? '') . '” a fost adăugat cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Fisier  $fisier
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $categorieFisier = null, Fisier $fisier)
    {
        $request->session()->get('fisier_return_url') ?? $request->session()->put('fisier_return_url', url()->previous());

        return view('fisiere.show', compact('firma'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Fisier  $fisier
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $categorieFisier = null, Fisier $fisier)
    {
        $request->session()->get('fisier_return_url') ?? $request->session()->put('fisier_return_url', url()->previous());

        return view('fisiere.edit', compact('categorieFisier', 'fisier'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Fisier  $fisier
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $categorieFisier = null, Fisier $fisier)
    {
        $fisier->update($this->validateRequest($request));

        // Salvare in istoric
        if ($fisier->wasChanged()){
            $fisier_istoric = new FisierIstoric;
            $fisier_istoric->fill($fisier->makeHidden(['created_at', 'updated_at'])->attributesToArray());
            $fisier_istoric->operare_user_id = auth()->user()->id ?? null;
            $fisier_istoric->operare_descriere = 'Modificare';
            $fisier_istoric->save();
        }

        return redirect($request->session()->get('fisier_return_url') ?? ('/fisiere/maseco'))->with('status', 'Fișierul „' . ($fisier->nume ?? '') . '” a fost modificat cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Fisier  $fisier
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $categorieFisier = null, Fisier $fisier)
    {
        Storage::delete($fisier->fisier_cale . $fisier->fisier_nume);

        $fisier->delete();

        // Salvare in istoric
        $fisier_istoric = new FisierIstoric;
        $fisier_istoric->fill($fisier->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $fisier_istoric->operare_user_id = auth()->user()->id ?? null;
        $fisier_istoric->operare_descriere = 'Stergere';
        $fisier_istoric->save();


        return back()->with('status', 'Fișierul „' . ($fisier->nume ?? '') . '” a fost șters cu succes!');
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
                'categorie' => 'required|max:500',
                'fisier' => $request->isMethod('post') ? 'required|mimes:pdf,doc,docx,xls,xlsx,csv,xml|max:20000' : '',
                'observatii' => 'nullable|max:2000',
            ],
            [
                'fisier.max' => 'Câmpul fisier nu poate avea mai mult de 20MB.'
            ]
        );
    }

    public function descarca($categorieFisier = null, Fisier $fisier)
    {
        if (($fisier->fisier_cale) && ($fisier->fisier_nume) && Storage::exists($fisier->fisier_cale . $fisier->fisier_nume)) {
            return Storage::download($fisier->fisier_cale . $fisier->fisier_nume);
        } else {
            return back()->with('eroare', 'Nu a putut fi descărcat fișierul.');
        }
    }

}
