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
        $documentWord = DocumentWord::create($this->validateRequest($request));

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
        // $this->authorize('update', $documentWord);

        // $request->session()->get('documentWordReturnUrl') ?? $request->session()->put('documentWordReturnUrl', url()->previous());

        // return view('documenteWord.show', compact('documentWord'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\DocumentWord  $documentWord
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, DocumentWord $documentWord)
    {
        // This will throw an authorization exception if the user is not allowed
        $this->authorize('update', $documentWord);

        // Lock the record
        $documentWord->update([
            'locked_by' => auth()->id(),
            'locked_at' => now(),
        ]);

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
        // This will throw an authorization exception if the user is not allowed
        $this->authorize('update', $documentWord);

        $data = $this->validateRequest($request);

        // Add the lock release fields to the update data
        $data['locked_by'] = null;
        $data['locked_at'] = null;

        $documentWord->update($data);

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
        // This will throw an authorization exception if the user is not allowed
        $this->authorize('update', $documentWord);

        $documentWord->delete();

        return redirect($request->session()->get('documentWordReturnUrl') ?? ('/documente-word'))->with('status', 'Documentul word „' . ($documentWord->nume ?? '') . '” a fost șters cu succes!');
    }

    /**
     * Validate the request attributes.
     *
     * @return array
     */
    protected function validateRequest(Request $request)
    {
        return $request->validate(
            [
                'nume' => 'required|max:255',
                'nivel_acces' => 'nullable|integer|between:1,2',
                'continut' => 'json',
            ],
            [
            ]
        );
    }

    public function unlock(Request $request, DocumentWord $documentWord)
    {
        // Unlock the record
        $documentWord->update([
            'locked_by' => null,
            'locked_at' => null,
        ]);

        return redirect($request->session()->get('documentWordReturnUrl') ?? ('/documente-word'))->with('status', 'Documentul word „' . ($documentWord->nume ?? '') . '” a fost deblocat cu succes!');
    }
}
