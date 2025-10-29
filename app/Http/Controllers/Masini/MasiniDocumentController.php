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
    public function edit(Masina $masina, MasinaDocument $document): View
    {
        abort_unless($document->masina_id === $masina->id, 404);

        $document->loadMissing('fisiere');

        return view('masini-mementouri.document-edit', [
            'masina' => $masina,
            'document' => $document,
            'documentLabel' => $document->label(),
        ]);
    }

    public function update(Request $request, Masina $masina, MasinaDocument $document): JsonResponse|RedirectResponse
    {
        abort_unless($document->masina_id === $masina->id, 404);

        $validated = $request->validate([
            'data_expirare' => ['nullable', 'date'],
            'email_notificare' => ['nullable', 'email:rfc'],
        ]);

        $shouldResetNotifications = false;

        if (array_key_exists('data_expirare', $validated)) {
            $incomingDate = $validated['data_expirare'] ? Carbon::parse($validated['data_expirare'])->startOfDay() : null;
            $currentDate = $document->data_expirare ? $document->data_expirare->copy()->startOfDay() : null;

            if ($incomingDate?->ne($currentDate) ?? $currentDate !== null) {
                $shouldResetNotifications = true;
            }
        }

        $document->fill(Arr::only($validated, ['data_expirare', 'email_notificare']));

        if ($shouldResetNotifications) {
            $document->notificare_60_trimisa = false;
            $document->notificare_30_trimisa = false;
            $document->notificare_1_trimisa = false;
        }

        $document->save();

        if ($request->expectsJson()) {
            $document->refresh();

            return response()->json([
                'status' => 'ok',
                'color_class' => $document->colorClass(),
                'days_until_expiry' => $document->daysUntilExpiry(),
                'formatted_date' => $document->data_expirare?->format('Y-m-d'),
                'readable_date' => $document->data_expirare?->format('d.m.Y'),
                'message' => __('Modificarea a fost salvată.'),
            ]);
        }

        return Redirect::back()->with('status', 'Documentul a fost actualizat.');
    }
}
