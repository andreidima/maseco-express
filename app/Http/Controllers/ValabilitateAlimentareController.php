<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesValabilitatiCurseListings;
use App\Http\Requests\ValabilitateAlimentareRequest;
use App\Models\ValabilitatiAlimentare;
use App\Models\Valabilitate;
use Illuminate\Contracts\View\View;
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

        $totalLitri = (float) ($alimentariAggregates->total_litri ?? 0);
        $kmTotal = $summary['kmTotal'] ?? null;
        $consum = $kmTotal !== null ? ($totalLitri * $kmTotal) / 100 : null;

        return view('valabilitati.alimentari.index', [
            'valabilitate' => $valabilitate,
            'summary' => $summary,
            'alimentari' => $alimentari,
            'alimentariMetrics' => [
                'totalLitri' => $totalLitri,
                'averagePret' => $alimentariAggregates->average_pret_pe_litru !== null
                    ? (float) $alimentariAggregates->average_pret_pe_litru
                    : null,
                'totalPret' => (float) ($alimentariAggregates->total_pret ?? 0),
                'consum' => $consum,
            ],
            'backUrl' => route('valabilitati.index'),
        ]);
    }

    public function store(
        ValabilitateAlimentareRequest $request,
        Valabilitate $valabilitate
    ): RedirectResponse {
        $this->authorize('update', $valabilitate);

        $valabilitate->alimentari()->create($request->validated());

        return redirect()
            ->route('valabilitati.alimentari.index', $valabilitate)
            ->with('status', 'Alimentarea a fost adăugată.');
    }

    public function update(
        ValabilitateAlimentareRequest $request,
        Valabilitate $valabilitate,
        ValabilitatiAlimentare $alimentare
    ): RedirectResponse {
        $this->assertBelongsToValabilitate($valabilitate, $alimentare);

        $this->authorize('update', $valabilitate);

        $alimentare->update($request->validated());

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
}
