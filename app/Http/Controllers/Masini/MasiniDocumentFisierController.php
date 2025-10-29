<?php

namespace App\Http\Controllers\Masini;

use App\Http\Controllers\Controller;
use App\Models\Masini\Masina;
use App\Models\Masini\MasinaDocument;
use App\Models\Masini\MasinaDocumentFisier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class MasiniDocumentFisierController extends Controller
{
    public function store(Request $request, Masina $masina, MasinaDocument $document): RedirectResponse
    {
        abort_unless($document->masina_id === $masina->id, 404);

        $validated = $request->validate([
            'fisier' => ['required', 'file', 'mimes:pdf', 'max:51200'],
        ]);

        $path = $validated['fisier']->store('masini-documente/' . $document->id, 'public');

        $document->fisiere()->create([
            'cale' => $path,
            'nume_fisier' => basename($path),
            'nume_original' => $validated['fisier']->getClientOriginalName(),
            'mime_type' => $validated['fisier']->getMimeType(),
            'dimensiune' => $validated['fisier']->getSize(),
        ]);

        return Redirect::back()->with('status', 'Fișierul a fost încărcat.');
    }

    public function destroy(Masina $masina, MasinaDocument $document, MasinaDocumentFisier $fisier): RedirectResponse
    {
        abort_unless($document->masina_id === $masina->id && $fisier->document_id === $document->id, 404);

        if ($fisier->cale) {
            Storage::disk('public')->delete($fisier->cale);
        }

        $fisier->delete();

        return Redirect::back()->with('status', 'Fișierul a fost șters.');
    }

    public function download(Masina $masina, MasinaDocument $document, MasinaDocumentFisier $fisier)
    {
        abort_unless($document->masina_id === $masina->id && $fisier->document_id === $document->id, 404);

        return Storage::disk('public')->download($fisier->cale, $fisier->nume_original);
    }

    public function preview(Masina $masina, MasinaDocument $document, MasinaDocumentFisier $fisier)
    {
        abort_unless($document->masina_id === $masina->id && $fisier->document_id === $document->id, 404);

        return Storage::disk('public')->response($fisier->cale, $fisier->nume_original);
    }
}
