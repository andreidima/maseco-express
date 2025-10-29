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
    private const STORAGE_DISK = MasinaDocumentFisier::STORAGE_DISK;
    private const STORAGE_DIRECTORY = MasinaDocumentFisier::STORAGE_DIRECTORY;

    public function store(Request $request, Masina $masini_mementouri, MasinaDocument|string|int $document): JsonResponse|RedirectResponse
    {
        $masina = $masini_mementouri;

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

            return Redirect::route('masini-mementouri.documente.edit', [
                'masini_mementouri' => $masina->getRouteKey(),
                'document' => MasinaDocument::buildRouteKey($document->document_type, $document->tara),
            ])
                ->withErrors($validator)
                ->withInput();
        }

        $files = $validator->validated();
        $files = $files['fisier'] ?? [];

        $storedCount = 0;

        foreach ($files as $file) {
            $path = $file->store(self::STORAGE_DIRECTORY . '/' . $document->id, self::STORAGE_DISK);

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

        return Redirect::route('masini-mementouri.documente.edit', [
            'masini_mementouri' => $masina->getRouteKey(),
            'document' => MasinaDocument::buildRouteKey($document->document_type, $document->tara),
        ])
            ->with('status', $message);
    }

    public function destroy(Request $request, Masina $masini_mementouri, MasinaDocument|string|int $document, MasinaDocumentFisier $fisier): JsonResponse|RedirectResponse
    {
        $masina = $masini_mementouri;

        $document = $this->resolveDocument($masina, $document);

        abort_unless($document->masina_id === $masina->id && $fisier->document_id === $document->id, 404);

        if ($fisier->cale) {
            Storage::disk(self::STORAGE_DISK)->delete($fisier->cale);
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

    public function download(Masina $masini_mementouri, MasinaDocument|string|int $document, MasinaDocumentFisier $fisier)
    {
        $masina = $masini_mementouri;

        $document = $this->resolveDocument($masina, $document);

        abort_unless($document->masina_id === $masina->id && $fisier->document_id === $document->id, 404);

        $headers = [];

        if ($mimeType = $fisier->guessMimeType()) {
            $headers['Content-Type'] = $mimeType;
        }

        return Storage::disk(self::STORAGE_DISK)->download(
            $fisier->cale,
            $fisier->downloadName(),
            $headers
        );
    }

    public function preview(Masina $masini_mementouri, MasinaDocument|string|int $document, MasinaDocumentFisier $fisier)
    {
        $masina = $masini_mementouri;

        $document = $this->resolveDocument($masina, $document);

        abort_unless($document->masina_id === $masina->id && $fisier->document_id === $document->id, 404);

        abort_unless($fisier->isPreviewable(), 404);

        $headers = [];

        if ($mimeType = $fisier->guessMimeType()) {
            $headers['Content-Type'] = $mimeType;
        }

        return Storage::disk(self::STORAGE_DISK)->response(
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
