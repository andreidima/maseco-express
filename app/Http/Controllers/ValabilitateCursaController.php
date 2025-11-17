<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValabilitateCursaRequest;
use App\Models\Tara;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use App\Support\Valabilitati\ValabilitateCursaOrderer;
use App\Support\Valabilitati\ValabilitatiCurseFilterState;
use App\Support\Valabilitati\ValabilitatiFilterState;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator as BasePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class ValabilitateCursaController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request, Valabilitate $valabilitate): View
    {
        $this->authorize('view', $valabilitate);

        $curse = $this->paginateCurse($request, $valabilitate);

        ValabilitatiCurseFilterState::remember($request, $valabilitate, []);

        $valabilitate->loadMissing(['sofer', 'taxeDrum']);
        $summary = $this->buildSummaryData($valabilitate, $curse);

        return view('valabilitati.curse.index', [
            'valabilitate' => $valabilitate,
            'curse' => $curse,
            'nextPageUrl' => $this->buildNextPageUrl($request, $valabilitate, $curse),
            'backUrl' => ValabilitatiFilterState::route(),
            'tari' => $this->loadTari(),
            'summary' => $summary,
        ]);
    }

    public function paginate(Request $request, Valabilitate $valabilitate): JsonResponse
    {
        $this->authorize('view', $valabilitate);

        $curse = $this->paginateCurse($request, $valabilitate);

        ValabilitatiCurseFilterState::remember($request, $valabilitate, []);
        $valabilitate->loadMissing(['sofer', 'taxeDrum']);
        $summary = $this->buildSummaryData($valabilitate, $curse);

        return response()->json([
            'rows_html' => view('valabilitati.curse.partials.rows', [
                'valabilitate' => $valabilitate,
                'curse' => $curse,
            ])->render(),
            'modals_html' => view('valabilitati.curse.partials.modals', [
                'valabilitate' => $valabilitate,
                'curse' => $curse,
                'includeCreate' => false,
                'tari' => $this->loadTari(),
            ])->render(),
            'next_url' => $this->buildNextPageUrl($request, $valabilitate, $curse),
            'summary_html' => $this->renderSummaryHtml($valabilitate, $summary),
        ]);
    }

    public function store(ValabilitateCursaRequest $request, Valabilitate $valabilitate): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $valabilitate);

        $data = $request->validated();

        if (! array_key_exists('nr_ordine', $data)) {
            $data['nr_ordine'] = $this->resolveNextNrOrdine($valabilitate);
        }

        $valabilitate->curse()->create($data);

        return $this->respondAfterMutation($request, $valabilitate, 'Cursa a fost adăugată cu succes.');
    }

    public function update(ValabilitateCursaRequest $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): RedirectResponse|JsonResponse
    {
        $this->assertBelongsToValabilitate($valabilitate, $cursa);

        $this->authorize('update', $valabilitate);

        $cursa->update($request->validated());

        return $this->respondAfterMutation($request, $valabilitate, 'Cursa a fost actualizată.');
    }

    public function destroy(Request $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): RedirectResponse|JsonResponse
    {
        $this->assertBelongsToValabilitate($valabilitate, $cursa);

        $this->authorize('update', $valabilitate);

        $cursa->delete();

        return $this->respondAfterMutation($request, $valabilitate, 'Cursa a fost ștearsă.');
    }

    public function reorder(Request $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): RedirectResponse|JsonResponse
    {
        $this->assertBelongsToValabilitate($valabilitate, $cursa);

        $this->authorize('update', $valabilitate);

        $validated = $request->validate([
            'direction' => ['required', Rule::in(['up', 'down'])],
        ]);

        $direction = $validated['direction'];

        $moved = ValabilitateCursaOrderer::move($cursa, $direction);

        $message = $moved
            ? 'Ordinea cursei a fost actualizată.'
            : ($direction === 'up'
                ? 'Cursa este deja la începutul listei.'
                : 'Cursa este deja la finalul listei.');

        if ($request->expectsJson()) {
            return $this->listingJsonResponse($valabilitate, $message);
        }

        return redirect(ValabilitatiCurseFilterState::route($valabilitate))->with('status', $message);
    }

    private function paginateCurse(Request $request, Valabilitate $valabilitate, ?array $query = null): LengthAwarePaginator
    {
        $paginator = $valabilitate->curse()->with([
            'incarcareTara',
            'descarcareTara',
        ])
            ->reorder()
            ->orderBy('nr_ordine')
            ->orderBy('data_cursa')
            ->paginate(self::PER_PAGE);

        if ($query !== null) {
            return $paginator->appends($query);
        }

        return $paginator->withQueryString();
    }

    private function buildNextPageUrl(Request $request, Valabilitate $valabilitate, LengthAwarePaginator $paginator): ?string
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

    private function respondAfterMutation(Request $request, Valabilitate $valabilitate, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return $this->listingJsonResponse($valabilitate, $message);
        }

        return redirect(ValabilitatiCurseFilterState::route($valabilitate))->with('status', $message);
    }

    private function listingJsonResponse(Valabilitate $valabilitate, string $message): JsonResponse
    {
        $query = ValabilitatiCurseFilterState::get($valabilitate);
        $filtersRequest = Request::create('', 'GET', $query);
        $curse = $this->paginateCurse($filtersRequest, $valabilitate, $query);
        $valabilitate->loadMissing(['sofer', 'taxeDrum']);
        $summary = $this->buildSummaryData($valabilitate, $curse);

        return response()->json([
            'message' => $message,
            'table_html' => view('valabilitati.curse.partials.rows', [
                'valabilitate' => $valabilitate,
                'curse' => $curse,
            ])->render(),
            'modals_html' => view('valabilitati.curse.partials.modals', [
                'valabilitate' => $valabilitate,
                'curse' => $curse,
                'includeCreate' => true,
                'formType' => old('form_type'),
                'formId' => old('form_id'),
                'tari' => $this->loadTari(),
            ])->render(),
            'next_url' => $this->buildNextPageUrl($filtersRequest, $valabilitate, $curse),
            'summary_html' => $this->renderSummaryHtml($valabilitate, $summary),
        ]);
    }

    private function resolveNextNrOrdine(Valabilitate $valabilitate): int
    {
        $max = (int) $valabilitate->curse()->max('nr_ordine');

        return $max > 0 ? $max + 1 : 1;
    }

    private function buildSummaryData(Valabilitate $valabilitate, $curse): array
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

        return [
            'kmPlecare' => $kmPlecare,
            'kmSosire' => $kmSosire,
            'kmTotal' => $kmTotal,
            'totalKmMaps' => $totalKmMaps,
            'totalKmBord2' => $totalKmBord2,
            'totalKmDiff' => $totalKmDiff,
            'totalZile' => $totalZile,
        ];
    }

    private function resolveCurseCollection($curse): Collection
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

    private function renderSummaryHtml(Valabilitate $valabilitate, array $summary): string
    {
        return view('valabilitati.curse.partials.summary', [
            'valabilitate' => $valabilitate,
            'summary' => $summary,
        ])->render();
    }

    private function assertBelongsToValabilitate(Valabilitate $valabilitate, ValabilitateCursa $cursa): void
    {
        if ($cursa->valabilitate_id !== $valabilitate->getKey()) {
            abort(404);
        }
    }

    private function loadTari()
    {
        return Tara::orderBy('nume')->get(['id', 'nume']);
    }
}
