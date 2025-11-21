<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesValabilitatiCurseListings;
use App\Http\Requests\ValabilitateAlimentareRequest;
use App\Models\ValabilitatiAlimentare;
use App\Models\Valabilitate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ValabilitateAlimentareController extends Controller
{
    use HandlesValabilitatiCurseListings;

    private const PER_PAGE = 15;

    public function index(Request $request, Valabilitate $valabilitate): View
    {
        $this->authorize('view', $valabilitate);

        $valabilitate->loadMissing(['sofer', 'taxeDrum', 'divizie', 'curse']);
        $summary = $this->buildSummaryData($valabilitate, $valabilitate->curse);

        $alimentariQuery = $valabilitate->alimentari();

        $alimentari = (clone $alimentariQuery)
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $alimentariAggregates = $alimentariQuery
            ->selectRaw('SUM(litrii) as total_litri, AVG(pret_pe_litru) as average_pret_pe_litru, SUM(total_pret) as total_pret')
            ->first();

        $metrics = $this->buildAlimentariMetrics($valabilitate);

        return view('valabilitati.alimentari.index', [
            'valabilitate' => $valabilitate,
            'summary' => $summary,
            'alimentari' => $alimentari,
            'alimentariMetrics' => $metrics,
            'backUrl' => route('valabilitati.index'),
        ]);
    }

    public function store(
        ValabilitateAlimentareRequest $request,
        Valabilitate $valabilitate
    ): RedirectResponse|JsonResponse {
        $this->authorize('update', $valabilitate);

        $alimentare = $valabilitate->alimentari()->create($request->validated());

        if ($request->expectsJson()) {
            $valabilitate->loadMissing(['divizie', 'curse']);

            return response()->json([
                'message' => 'Alimentarea a fost adăugată.',
                'alimentare' => $this->formatAlimentarePayload($valabilitate, $alimentare),
                'metrics' => $this->buildAlimentariMetrics($valabilitate),
            ]);
        }

        return redirect()
            ->route('valabilitati.alimentari.index', $valabilitate)
            ->with('status', 'Alimentarea a fost adăugată.');
    }

    public function update(
        ValabilitateAlimentareRequest $request,
        Valabilitate $valabilitate,
        ValabilitatiAlimentare $alimentare
    ): RedirectResponse|JsonResponse {
        $this->assertBelongsToValabilitate($valabilitate, $alimentare);

        $this->authorize('update', $valabilitate);

        $alimentare->update($request->validated());

        if ($request->expectsJson()) {
            $alimentare->refresh();
            $valabilitate->loadMissing(['curse', 'divizie']);

            return response()->json([
                'message' => 'Alimentarea a fost actualizată.',
                'alimentare' => $this->formatAlimentarePayload($valabilitate, $alimentare),
                'metrics' => $this->buildAlimentariMetrics($valabilitate),
            ]);
        }

        return redirect()
            ->route('valabilitati.alimentari.index', $valabilitate)
            ->with('status', 'Alimentarea a fost actualizată.');
    }

    public function destroy(
        Valabilitate $valabilitate,
        ValabilitatiAlimentare $alimentare
    ): RedirectResponse {
        $this->assertBelongsToValabilitate($valabilitate, $alimentare);

        $this->authorize('update', $valabilitate);

        $alimentare->delete();

        return redirect()
            ->route('valabilitati.alimentari.index', $valabilitate)
            ->with('status', 'Alimentarea a fost ștearsă.');
    }

    private function assertBelongsToValabilitate(Valabilitate $valabilitate, ValabilitatiAlimentare $alimentare): void
    {
        if ($alimentare->valabilitate_id !== $valabilitate->getKey()) {
            abort(404);
        }
    }

    protected function perPage(): int
    {
        return self::PER_PAGE;
    }

    protected function displayGroupSummaryInResponses(): bool
    {
        return false;
    }

    private function buildAlimentariMetrics(Valabilitate $valabilitate): array
    {
        $aggregates = $valabilitate->alimentari()
            ->selectRaw('SUM(litrii) as total_litri, AVG(pret_pe_litru) as average_pret_pe_litru, SUM(total_pret) as total_pret')
            ->first();

        $totalLitri = (float) ($aggregates->total_litri ?? 0);
        $valabilitate->loadMissing(['curse', 'divizie']);
        $summary = $this->buildSummaryData($valabilitate, $valabilitate->curse);
        $kmTotal = $summary['kmTotal'] ?? null;
        $consum = $kmTotal !== null ? ($totalLitri * $kmTotal) / 100 : null;

        return [
            'totalLitri' => $totalLitri,
            'averagePret' => $aggregates->average_pret_pe_litru !== null
                ? (float) $aggregates->average_pret_pe_litru
                : null,
            'totalPret' => (float) ($aggregates->total_pret ?? 0),
            'consum' => $consum,
        ];
    }

    private function formatAlimentarePayload(Valabilitate $valabilitate, ValabilitatiAlimentare $alimentare): array
    {
        return [
            'id' => $alimentare->getKey(),
            'update_url' => route('valabilitati.alimentari.update', [$valabilitate, $alimentare]),
            'delete_url' => route('valabilitati.alimentari.destroy', [$valabilitate, $alimentare]),
            'data_ora_alimentare' => optional($alimentare->data_ora_alimentare)->format('Y-m-d\TH:i'),
            'data_ora_display' => optional($alimentare->data_ora_alimentare)->format('d.m.Y H:i'),
            'litrii' => $alimentare->litrii !== null ? (float) $alimentare->litrii : null,
            'pret_pe_litru' => $alimentare->pret_pe_litru !== null ? (float) $alimentare->pret_pe_litru : null,
            'total_pret' => $alimentare->total_pret !== null ? (float) $alimentare->total_pret : null,
            'observatii' => $alimentare->observatii,
        ];
    }
}
