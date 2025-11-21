<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesValabilitatiCurseListings;
use App\Http\Requests\ValabilitateAlimentareRequest;
use App\Models\ValabilitatiAlimentare;
use App\Models\Valabilitate;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;

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

    public function updateField(
        Request $request,
        Valabilitate $valabilitate,
        ValabilitatiAlimentare $alimentare
    ): JsonResponse {
        $this->assertBelongsToValabilitate($valabilitate, $alimentare);

        $this->authorize('update', $valabilitate);

        $field = $request->input('field');

        $rules = match ($field) {
            'data_ora_alimentare' => ['required', 'date'],
            'litrii', 'pret_pe_litru', 'total_pret' => ['required', 'numeric', 'min:0'],
            'observatii' => ['nullable', 'string'],
            default => null,
        };

        if ($rules === null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Câmp invalid.',
            ], 422);
        }

        $validator = Validator::make($request->only('value'), ['value' => $rules]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first('value'),
            ], 422);
        }

        $value = $validator->validated()['value'];

        if ($field === 'data_ora_alimentare') {
            $value = Carbon::parse($value);
        }

        $alimentare->update([$field => $value]);

        return response()->json([
            'status' => 'success',
            'value' => $this->normalizeFieldValue($alimentare, $field),
            'displayValue' => $this->formatField($alimentare, $field),
        ]);
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

    private function formatField(ValabilitatiAlimentare $alimentare, string $field): string
    {
        return match ($field) {
            'data_ora_alimentare' => optional($alimentare->data_ora_alimentare)?->format('d.m.Y H:i') ?? '',
            'litrii' => $this->formatNumber($alimentare->litrii, 2),
            'pret_pe_litru' => $this->formatNumber($alimentare->pret_pe_litru, 4),
            'total_pret' => $this->formatNumber($alimentare->total_pret, 4),
            'observatii' => (string) $alimentare->observatii,
            default => '',
        };
    }

    private function normalizeFieldValue(ValabilitatiAlimentare $alimentare, string $field): string
    {
        return match ($field) {
            'data_ora_alimentare' => optional($alimentare->data_ora_alimentare)?->format('Y-m-d\\TH:i') ?? '',
            'litrii' => $this->formatNumber($alimentare->litrii, 2),
            'pret_pe_litru' => $this->formatNumber($alimentare->pret_pe_litru, 4),
            'total_pret' => $this->formatNumber($alimentare->total_pret, 4),
            'observatii' => (string) $alimentare->observatii,
            default => '',
        };
    }

    private function formatNumber($value, int $decimals = 2): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        $trimmed = rtrim(rtrim(number_format((float) $value, $decimals, '.', ''), '0'), '.');

        return $trimmed === '-0' ? '0' : $trimmed;
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
