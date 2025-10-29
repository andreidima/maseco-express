<?php

namespace App\Http\Controllers\Masini;

use App\Http\Controllers\Controller;
use App\Models\Masini\Masina;
use App\Models\Masini\MasinaDocument;
use App\Models\Masini\MasinaDocumentFisier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MasiniDocumentFisierController extends Controller
{
    public function store(Request $request, Masina $masina, MasinaDocument|string|int $document): JsonResponse|RedirectResponse
    {
        $document = $this->resolveDocument($masina, $document);

        abort_unless($document->masina_id === $masina->id, 404);

        $files = $request->file('fisier');

        if (is_array($files)) {
            $files = array_values(array_filter($files));
        } elseif ($files !== null) {
            $files = [$files];
        } else {
            $files = [];
        }

        $validator = Validator::make([
            'fisier' => $files,
        ], [
            'fisier' => ['required', 'array', 'min:1'],
            'fisier.*' => ['file', 'mimes:pdf', 'max:51200'],
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                throw new ValidationException($validator);
            }

            return Redirect::route('masini-mementouri.documente.edit', [$masina, $document])
                ->withErrors($validator)
                ->withInput();
        }

        $files = $validator->validated();
        $files = $files['fisier'] ?? [];

        $storedCount = 0;

        foreach ($files as $file) {
            $path = $file->store('masini-documente/' . $document->id, 'public');

            $document->fisiere()->create([
                'cale' => $path,
                'nume_fisier' => basename($path),
                'nume_original' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'dimensiune' => $file->getSize(),
            ]);

            $storedCount++;
        }

        $message = $storedCount === 1
            ? __('Fișierul a fost încărcat.')
            : __('Fișierele au fost încărcate.');

        if ($request->expectsJson()) {
            $document->load('fisiere');

            return response()->json([
                'status' => 'ok',
                'message' => $message,
                'files_html' => view('masini-mementouri.partials.document-files-list', [
                    'masina' => $masina,
                    'document' => $document,
                ])->render(),
            ]);
        }

        return Redirect::route('masini-mementouri.documente.edit', [$masina, $document])
            ->with('status', $message);
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
