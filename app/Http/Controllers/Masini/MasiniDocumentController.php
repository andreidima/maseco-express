<?php

namespace App\Http\Controllers\Masini;

use App\Http\Controllers\Controller;
use App\Models\Masini\Masina;
use App\Models\Masini\MasinaDocument;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class MasiniDocumentController extends Controller
{
    public function edit(Masina $masini_mementouri, MasinaDocument|string|int $document): View
    {
        $masina = $masini_mementouri;

        $document = $this->resolveDocument($masina, $document);

        abort_unless($document->masina_id === $masina->id, 404);

        $document->loadMissing('fisiere');

        return view('masini-mementouri.document-edit', [
            'masina' => $masina,
            'document' => $document,
            'documentLabel' => $document->label(),
        ]);
    }

    public function update(Request $request, Masina $masini_mementouri, MasinaDocument|string|int $document): JsonResponse|RedirectResponse
    {
        $masina = $masini_mementouri;

        $document = $this->resolveDocument($masina, $document);

        abort_unless($document->masina_id === $masina->id, 404);

        if ($request->boolean('fara_expirare')) {
            $request->merge(['data_expirare' => null]);
        }

        $validated = $request->validate([
            'data_expirare' => ['nullable', 'date'],
            'email_notificare' => ['nullable', 'email:rfc'],
            'fara_expirare' => ['sometimes', 'boolean'],
        ]);

        $currentDate = $document->data_expirare ? $document->data_expirare->copy()->startOfDay() : null;
        $shouldResetNotifications = false;

        $faraExpirare = (bool) ($validated['fara_expirare'] ?? $document->fara_expirare);
        $validated['fara_expirare'] = $faraExpirare;

        $incomingDate = null;

        if (!$faraExpirare && array_key_exists('data_expirare', $validated)) {
            $incomingDate = $validated['data_expirare']
                ? Carbon::parse($validated['data_expirare'])->startOfDay()
                : null;
        }

        if ($faraExpirare) {
            $validated['data_expirare'] = null;
        }

        if ($faraExpirare !== (bool) $document->fara_expirare) {
            $shouldResetNotifications = true;
        }

        if (!$faraExpirare && array_key_exists('data_expirare', $validated)) {
            if ($incomingDate?->ne($currentDate) ?? $currentDate !== null) {
                $shouldResetNotifications = true;
            }
        }

        $document->fill(Arr::only($validated, ['data_expirare', 'email_notificare', 'fara_expirare']));

        if ($shouldResetNotifications) {
            $document->notificare_60_trimisa = false;
            $document->notificare_30_trimisa = false;
            $document->notificare_15_trimisa = false;
            $document->notificare_1_trimisa = false;
        }

        $document->save();

        if ($request->expectsJson()) {
            $document->refresh();

            return response()->json([
                'status' => 'ok',
                'color_class' => $document->colorClass(),
                'days_until_expiry' => $document->daysUntilExpiry(),
                'formatted_date' => $document->formattedExpiryDate(),
                'readable_date' => $document->readableExpiryDate(),
                'fara_expirare' => $document->isWithoutExpiry(),
                'message' => __('Modificarea a fost salvată.'),
            ]);
        }

        return Redirect::back()->with('status', 'Documentul a fost actualizat.');
    }

    protected function resolveDocument(Masina $masini_mementouri, MasinaDocument|string|int $document): MasinaDocument
    {
        if ($document instanceof MasinaDocument) {
            return $document;
        }

        return MasinaDocument::resolveForMasina($masini_mementouri, $document);
    }
}
