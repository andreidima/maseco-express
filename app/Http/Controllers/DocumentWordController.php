<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Http\Requests\DocumentWordRequest;
use PDF; // Barryvdh\DomPDF\Facade

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
            ->when(! $request->user()?->hasPermission('documente-word-manage'), function ($query) {
                // Users without the admin document permission can see only "operator" documents
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
        $this->authorize('create', DocumentWord::class);

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
    public function store(DocumentWordRequest $request)
    {
        $this->authorize('create', DocumentWord::class);

        $data = $request->validated();

        if (! $request->user()?->hasPermission('documente-word-manage')) {
            $data['nivel_acces'] = 2;
        }

        $documentWord = DocumentWord::create($data);

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
    public function update(DocumentWordRequest $request, DocumentWord $documentWord)
    {
        // This will throw an authorization exception if the user is not allowed
        $this->authorize('update', $documentWord);

        $data = $request->validated();

        if (! $request->user()?->hasPermission('documente-word-manage')) {
            $data['nivel_acces'] = 2;
        }

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
        $this->authorize('delete', $documentWord);

        $documentWord->delete();

        return redirect($request->session()->get('documentWordReturnUrl') ?? ('/documente-word'))->with('status', 'Documentul word „' . ($documentWord->nume ?? '') . '” a fost șters cu succes!');
    }

    public function unlock(Request $request, DocumentWord $documentWord)
    {
        $this->authorize('unlock', $documentWord);

        // Unlock the record
        $documentWord->update([
            'locked_by' => null,
            'locked_at' => null,
        ]);

        return redirect($request->session()->get('documentWordReturnUrl') ?? ('/documente-word'))->with('status', 'Documentul word „' . ($documentWord->nume ?? '') . '” a fost deblocat cu succes!');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $this->authorize('create', DocumentWord::class);

        $request->validate([
            'image' => ['required', 'image', 'max:5120'],
        ]);

        $file = $request->file('image');
        $path = $file->store('documente-word/images', 'public');

        return response()->json([
            'url' => Storage::disk('public')->url($path),
            'path' => $path,
            'disk' => 'public',
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
        ], 201);
    }
}
