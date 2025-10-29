<?php

namespace App\Http\Controllers\Masini;

use App\Http\Controllers\Controller;
use App\Models\Masini\Masina;
use App\Models\Masini\MasinaDocument;
use App\Models\Masini\MasinaDocumentFisier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;

class MasiniDocumentFisierController extends Controller
{
    public function store(Request $request, Masina $masina, MasinaDocument|string|int $document): JsonResponse|RedirectResponse
    {
        $document = $this->resolveDocument($masina, $document);

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

        if ($request->expectsJson()) {
            $document->load('fisiere');

            return response()->json([
                'status' => 'ok',
                'message' => __('Fișierul a fost încărcat.'),
                'files_html' => view('masini-mementouri.partials.document-files-list', [
                    'masina' => $masina,
                    'document' => $document,
                ])->render(),
            ]);
        }

        return Redirect::back()->with('status', 'Fișierul a fost încărcat.');
    }

    public function destroy(Request $request, Masina $masina, MasinaDocument|string|int $document, MasinaDocumentFisier $fisier): JsonResponse|RedirectResponse
    {
        $document = $this->resolveDocument($masina, $document);

        abort_unless($document->masina_id === $masina->id && $fisier->document_id === $document->id, 404);

        if ($fisier->cale) {
            Storage::disk('public')->delete($fisier->cale);
        }

        $fisier->delete();

        if ($request->expectsJson()) {
            $document->load('fisiere');

            return response()->json([
                'status' => 'ok',
                'message' => __('Fișierul a fost șters.'),
                'files_html' => view('masini-mementouri.partials.document-files-list', [
                    'masina' => $masina,
                    'document' => $document,
                ])->render(),
            ]);
        }

        return Redirect::back()->with('status', 'Fișierul a fost șters.');
    }

    public function download(Masina $masina, MasinaDocument|string|int $document, MasinaDocumentFisier $fisier)
    {
        $document = $this->resolveDocument($masina, $document);

        abort_unless($document->masina_id === $masina->id && $fisier->document_id === $document->id, 404);

        $headers = [];

        if ($mimeType = $fisier->guessMimeType()) {
            $headers['Content-Type'] = $mimeType;
        }

        return Storage::disk('public')->download(
            $fisier->cale,
            $fisier->downloadName(),
            $headers
        );
    }

    public function preview(Masina $masina, MasinaDocument|string|int $document, MasinaDocumentFisier $fisier)
    {
        $document = $this->resolveDocument($masina, $document);

        abort_unless($document->masina_id === $masina->id && $fisier->document_id === $document->id, 404);

        abort_unless($fisier->isPreviewable(), 404);

        $headers = [];

        if ($mimeType = $fisier->guessMimeType()) {
            $headers['Content-Type'] = $mimeType;
        }

        return Storage::disk('public')->response(
            $fisier->cale,
            $fisier->downloadName(),
            $headers
        );
    }

    protected function resolveDocument(Masina $masina, MasinaDocument|string|int $document): MasinaDocument
    {
        if ($document instanceof MasinaDocument) {
            return $document;
        }

        return MasinaDocument::resolveForMasina($masina, $document);
    }
}
