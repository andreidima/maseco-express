<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Tara;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursaGrup;
use App\Support\Valabilitati\ValabilitatiCurseFilterState;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator as BasePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HandlesValabilitatiCurseListings
{
    abstract protected function perPage(): int;

    protected function paginateCurse(Request $request, Valabilitate $valabilitate, ?array $query = null): LengthAwarePaginator
    {
        $paginator = $valabilitate->curse()
            ->with([
                'incarcareTara',
                'descarcareTara',
                'cursaGrup',
            ])
            ->reorder()
            ->orderBy('nr_ordine')
            ->orderBy('data_cursa')
            ->paginate($this->perPage());

        if ($query !== null) {
            return $paginator->appends($query);
        }

        return $paginator->withQueryString();
    }

    protected function buildNextPageUrl(Request $request, Valabilitate $valabilitate, LengthAwarePaginator $paginator): ?string
    {
        if (! $paginator->hasMorePages()) {
            return null;
        }

        $queryParameters = Arr::except($request->query(), ['page']);
        $nextPage = $paginator->currentPage() + 1;

        return route('valabilitati.curse.paginate', array_merge([
            'valabilitate' => $valabilitate->getKey(),
        ], $queryParameters, ['page' => $nextPage]));
    }

    protected function respondAfterMutation(Request $request, Valabilitate $valabilitate, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return $this->listingJsonResponse($valabilitate, $message);
        }

        return redirect($this->resolveListingRedirectUrl($request, $valabilitate))->with('status', $message);
    }

    protected function listingJsonResponse(Valabilitate $valabilitate, string $message): JsonResponse
    {
        $query = ValabilitatiCurseFilterState::get($valabilitate);
        $filtersRequest = Request::create('', 'GET', $query);
        $curse = $this->paginateCurse($filtersRequest, $valabilitate, $query);
        $valabilitate->loadMissing(['sofer', 'taxeDrum', 'cursaGrupuri']);
        $summary = $this->buildSummaryData($valabilitate, $curse);

        return response()->json([
            'message' => $message,
            'message_type' => 'success',
            'table_html' => view('valabilitati.curse.partials.rows', [
                'valabilitate' => $valabilitate,
                'curse' => $curse,
            ])->render(),
            'modals_html' => view('valabilitati.curse.partials.modals', $this->buildModalViewData(
                $valabilitate,
                $curse,
                true,
                old('form_type'),
                old('form_id')
            ))->render(),
            'next_url' => $this->buildNextPageUrl($filtersRequest, $valabilitate, $curse),
            'summary_html' => $this->renderSummaryHtml($valabilitate, $summary),
        ]);
    }

    protected function buildSummaryData(Valabilitate $valabilitate, $curse): array
    {
        $curseCollection = $this->resolveCurseCollection($curse);

        $kmPlecare = $curseCollection
            ->pluck('km_bord_incarcare')
            ->filter(static fn ($value) => $value !== null && $value !== '')
            ->min();

        $kmSosire = $curseCollection
            ->pluck('km_bord_descarcare')
            ->filter(static fn ($value) => $value !== null && $value !== '')
            ->max();

        $kmTotal = $kmPlecare !== null && $kmSosire !== null
            ? (float) $kmSosire - (float) $kmPlecare
            : null;

        $totalKmMaps = $curseCollection
            ->pluck('km_maps')
            ->filter(static fn ($value) => $value !== null && $value !== '' && is_numeric($value))
            ->map(static fn ($value) => (float) $value)
            ->sum();

        $totalKmBord2 = $curseCollection
            ->map(static function ($cursa) {
                $start = $cursa->km_bord_incarcare !== null && $cursa->km_bord_incarcare !== ''
                    ? (float) $cursa->km_bord_incarcare
                    : null;
                $end = $cursa->km_bord_descarcare !== null && $cursa->km_bord_descarcare !== ''
                    ? (float) $cursa->km_bord_descarcare
                    : null;

                return $start !== null && $end !== null ? $end - $start : null;
            })
            ->filter(static fn ($value) => $value !== null)
            ->sum();

        $totalKmDiff = $curseCollection
            ->map(static function ($cursa) {
                $start = $cursa->km_bord_incarcare !== null && $cursa->km_bord_incarcare !== ''
                    ? (float) $cursa->km_bord_incarcare
                    : null;
                $end = $cursa->km_bord_descarcare !== null && $cursa->km_bord_descarcare !== ''
                    ? (float) $cursa->km_bord_descarcare
                    : null;
                $maps = is_numeric($cursa->km_maps) ? (float) $cursa->km_maps : null;

                $bord2 = $start !== null && $end !== null ? $end - $start : null;

                return $bord2 !== null && $maps !== null ? $bord2 - $maps : null;
            })
            ->filter(static fn ($value) => $value !== null)
            ->sum();

        $dataInceput = $valabilitate->data_inceput;
        $dataSfarsit = $valabilitate->data_sfarsit;
        $totalZile = $dataInceput && $dataSfarsit
            ? $dataInceput->diffInDays($dataSfarsit) + 1
            : null;

        $groupFinancials = $valabilitate->cursaGrupuri
            ->map(static function ($grup) {
                $incasata = $grup->suma_incasata !== null ? (float) $grup->suma_incasata : null;
                $calculata = $grup->suma_calculata !== null ? (float) $grup->suma_calculata : null;
                $diferenta = null;

                if ($incasata !== null || $calculata !== null) {
                    $diferenta = ($incasata ?? 0.0) - ($calculata ?? 0.0);
                }

                return [
                    'id' => $grup->getKey(),
                    'nume' => $grup->nume,
                    'format' => $grup->format_documente,
                    'format_label' => $grup->formatDocumenteLabel(),
                    'suma_incasata' => $incasata,
                    'suma_calculata' => $calculata,
                    'diferenta' => $diferenta,
                    'numar_factura' => $grup->numar_factura,
                    'data_factura' => $grup->data_factura,
                    'culoare_hex' => $grup->culoare_hex,
                ];
            })
            ->values();

        $groupTotals = [
            'suma_incasata' => $groupFinancials->pluck('suma_incasata')->filter(static fn ($v) => $v !== null)->sum(),
            'suma_calculata' => $groupFinancials->pluck('suma_calculata')->filter(static fn ($v) => $v !== null)->sum(),
            'diferenta' => $groupFinancials->pluck('diferenta')->filter(static fn ($v) => $v !== null)->sum(),
        ];

        return [
            'kmPlecare' => $kmPlecare,
            'kmSosire' => $kmSosire,
            'kmTotal' => $kmTotal,
            'totalKmMaps' => $totalKmMaps,
            'totalKmBord2' => $totalKmBord2,
            'totalKmDiff' => $totalKmDiff,
            'totalZile' => $totalZile,
            'groupFinancials' => $groupFinancials,
            'groupFinancialTotals' => $groupTotals,
        ];
    }

    protected function resolveCurseCollection($curse): Collection
    {
        if ($curse instanceof Collection) {
            return $curse;
        }

        if ($curse instanceof LengthAwarePaginator
            || $curse instanceof BasePaginator
            || $curse instanceof CursorPaginator) {
            return collect($curse->items());
        }

        if (is_array($curse)) {
            return collect($curse);
        }

        return collect();
    }

    protected function renderSummaryHtml(Valabilitate $valabilitate, array $summary): string
    {
        return view('valabilitati.curse.partials.summary', [
            'valabilitate' => $valabilitate,
            'summary' => $summary,
            'showGroupSummary' => $this->displayGroupSummaryInResponses(),
        ])->render();
    }

    protected function displayGroupSummaryInResponses(): bool
    {
        return true;
    }

    protected function resolveListingRedirectUrl(Request $request, Valabilitate $valabilitate): string
    {
        return $this->sanitizeRedirectTarget((string) $request->input('redirect_to'))
            ?? ValabilitatiCurseFilterState::route($valabilitate);
    }

    private function sanitizeRedirectTarget(?string $target): ?string
    {
        if (! is_string($target)) {
            return null;
        }

        $target = trim($target);

        if ($target === '') {
            return null;
        }

        if (Str::startsWith($target, '/')) {
            return $target;
        }

        $appUrl = rtrim(url('/'), '/');

        if (Str::startsWith($target, $appUrl)) {
            return $target;
        }

        return null;
    }

    protected function loadTari()
    {
        return Tara::orderBy('nume')->get(['id', 'nume']);
    }

    protected function buildModalViewData(
        Valabilitate $valabilitate,
        $curse,
        bool $includeCreate,
        ?string $formType = null,
        ?int $formId = null
    ): array {
        return [
            'valabilitate' => $valabilitate,
            'curse' => $curse,
            'includeCreate' => $includeCreate,
            'formType' => $formType,
            'formId' => $formId,
            'tari' => $this->loadTari(),
            'groupFormatOptions' => ValabilitateCursaGrup::documentFormats(),
            'groupColorOptions' => ValabilitateCursaGrup::colorPalette(),
            'bulkAssignRoute' => route('valabilitati.curse.bulk-assign', $valabilitate),
        ];
    }
}
