<?php

namespace App\Http\Controllers\Masini;

use App\Http\Controllers\Controller;
use App\Models\Masini\Masina;
use App\Models\Masini\MasinaDocument;
use App\Models\Masini\MasinaDocumentFisier;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $dateProvided = $request->has('data_expirare');
        $rawDate = $dateProvided ? $request->input('data_expirare') : null;
        $normalizedDate = $rawDate === '' ? null : $rawDate;
        $rawNoExpiry = $request->input('fara_expirare');
        $incomingNoExpiry = filter_var($rawNoExpiry, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

        $shouldRelaxFileRequirement = $incomingNoExpiry && count($files) === 0;

        $payload = [
            'fisier' => $shouldRelaxFileRequirement ? null : $files,
            'fara_expirare' => $rawNoExpiry,
        ];

        if ($dateProvided) {
            $payload['data_expirare'] = $normalizedDate;
        }

        $validator = Validator::make($payload, [
            'fisier' => ['nullable', 'array'],
            'fisier.*' => ['file', 'mimes:pdf', 'max:51200'],
            'data_expirare' => ['nullable', 'date'],
            'fara_expirare' => ['nullable', 'boolean'],
        ]);

        $validator->after(function ($validator) use ($document, $dateProvided, $normalizedDate, $files, $incomingNoExpiry) {
            if ($incomingNoExpiry) {
                return;
            }

            if (count($files) === 0) {
                $validator->errors()->add('fisier', __('Te rugăm să încarci cel puțin un fișier.'));

                if (!$dateProvided && !$document->fara_expirare) {
                    return;
                }
            }

            if (!$dateProvided && !$document->fara_expirare) {
                return;
            }

            $currentDate = optional($document->data_expirare)->format('Y-m-d');
            $incomingDate = $normalizedDate ? Carbon::parse($normalizedDate)->format('Y-m-d') : null;

            if ($incomingDate !== $currentDate && count($files) === 0) {
                $validator->errors()->add('data_expirare', __('Pentru a modifica data expirării este necesar să atașezi cel puțin un fișier.'));
            }
        });

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                throw new ValidationException($validator);
            }

            return Redirect::route('masini-mementouri.documente.edit', [
                'masini_mementouri' => $masina->getRouteKey(),
                'document' => MasinaDocument::buildRouteKey($document->document_type, $document->tara),
            ])
                ->withErrors($validator)
                ->withInput($request->except('fisier'));
        }

        $validated = $validator->validated();
        $files = $validated['fisier'] ?? [];

        $shouldResetNotifications = false;
        $currentDate = $document->data_expirare ? $document->data_expirare->copy()->startOfDay() : null;
        $faraExpirare = array_key_exists('fara_expirare', $validated)
            ? (bool) $validated['fara_expirare']
            : (bool) $document->fara_expirare;

        $incomingDate = $currentDate;

        if ($faraExpirare) {
            $incomingDate = null;
        } elseif (array_key_exists('data_expirare', $validated)) {
            $incomingDate = $validated['data_expirare']
                ? Carbon::parse($validated['data_expirare'])->startOfDay()
                : null;
        }

        if ($faraExpirare !== (bool) $document->fara_expirare) {
            $shouldResetNotifications = true;
        }

        if (!$faraExpirare && array_key_exists('data_expirare', $validated)) {
            if ($incomingDate?->ne($currentDate) ?? $currentDate !== null) {
                $shouldResetNotifications = true;
            }
        }

        if ($faraExpirare && $currentDate !== null) {
            $shouldResetNotifications = true;
        }

        $document->fara_expirare = $faraExpirare;

        if ($faraExpirare) {
            $document->data_expirare = null;
        } elseif (array_key_exists('data_expirare', $validated)) {
            $document->data_expirare = $incomingDate?->toDateString();
        }

        if ($shouldResetNotifications) {
            $document->notificare_60_trimisa = false;
            $document->notificare_30_trimisa = false;
            $document->notificare_15_trimisa = false;
            $document->notificare_1_trimisa = false;
        }

        if ($document->isDirty(['data_expirare', 'fara_expirare']) || $shouldResetNotifications) {
            $document->save();
        }

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

        if ($storedCount === 0) {
            $message = __('Documentul a fost actualizat.');
        } elseif ($storedCount === 1) {
            $message = __('Fișierul a fost încărcat.');
        } else {
            $message = __('Fișierele au fost încărcate.');
        }

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
