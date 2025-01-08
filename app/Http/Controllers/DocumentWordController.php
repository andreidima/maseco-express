<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\DocumentWord;
use App\Models\DocumentWordIstoric;

class DocumentWordController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $request->session()->forget('documentWordReturnUrl');

        $searchNume = $request->searchNume;

        $query = DocumentWord::
            when($searchNume, function ($query, $searchNume) {
                return $query->where('nume', 'like', '%' . $searchNume . '%');
            })
            ->when(auth()->user()->role !== 1, function ($query) {
                // Non-admin users can see only "operator" documents
                return $query->where('nivel_acces', 2);
            })
            ->orderBy('nume')
            ->latest();

        $documenteWord = $query->simplePaginate(25);

        return view('documenteWord.index', compact('documenteWord', 'searchNume'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $request->session()->get('documentWordReturnUrl') ?? $request->session()->put('documentWordReturnUrl', url()->previous());

        $documentWord = new DocumentWord;
        $documentWord->nivel_acces = 2;

        return view('documenteWord.create', compact('documentWord'));
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
        $documentWord = DocumentWord::create($this->validateRequest($request));

        // Salvare in istoric
        $documentWordIstoric = new DocumentWordIstoric;
        $documentWordIstoric->fill($documentWord->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $documentWordIstoric->operare_user_id = auth()->user()->id ?? null;
        $documentWordIstoric->operare_descriere = 'Adaugare';
        $documentWordIstoric->save();

        return redirect($request->session()->get('documentWordReturnUrl') ?? ('/documente-word'))->with('status', 'Documentul word „' . ($documentWord->nume ?? '') . '” a fost adăugat cu succes!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\DocumentWord  $documentWord
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, DocumentWord $documentWord)
    {
        $this->authorize('update', $documentWord);

        $request->session()->get('documentWordReturnUrl') ?? $request->session()->put('documentWordReturnUrl', url()->previous());

        return view('documenteWord.show', compact('documentWord'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DocumentWord  $documentWord
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, DocumentWord $documentWord)
    {
        $request->session()->get('documentWordReturnUrl') ?? $request->session()->put('documentWordReturnUrl', url()->previous());

        return view('documenteWord.edit', compact('documentWord'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\DocumentWord  $documentWord
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DocumentWord $documentWord)
    {
        $documentWord->update($this->validateRequest($request));

        // Salvare in istoric
        if ($documentWord->wasChanged()){
            $documentWordIstoric = new DocumentWordIstoric;
            $documentWordIstoric->fill($documentWord->makeHidden(['created_at', 'updated_at'])->attributesToArray());
            $documentWordIstoric->operare_user_id = auth()->user()->id ?? null;
            $documentWordIstoric->operare_descriere = 'Modificare';
            $documentWordIstoric->save();
        }

        return redirect($request->session()->get('documentWordReturnUrl') ?? ('/documente-word'))->with('status', 'Documentul word „' . ($documentWord->nume ?? '') . '” a fost modificat cu succes!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\DocumentWord  $documentWord
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, DocumentWord $documentWord)
    {
        $documentWord->delete();

        // Salvare in istoric
        $documentWordIstoric = new DocumentWordIstoric;
        $documentWordIstoric->fill($documentWord->makeHidden(['created_at', 'updated_at'])->attributesToArray());
        $documentWordIstoric->operare_user_id = auth()->user()->id ?? null;
        $documentWordIstoric->operare_descriere = 'Stergere';
        $documentWordIstoric->save();

        // return back()->with('status', 'Documentul word „' . ($documentWord->nume ?? '') . '” a fost șters cu succes!');
        return redirect($request->session()->get('documentWordReturnUrl') ?? ('/documente-word'))->with('status', 'Documentul word „' . ($documentWord->nume ?? '') . '” a fost șters cu succes!');
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
                'nume' => 'required|max:255',
                'nivel_acces' => 'required|integer|between:1,2',
                'continut' => 'json',
            ],
            [
            ]
        );
    }
}
