<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValabilitateCursaRequest;
use App\Models\Tara;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use App\Support\Valabilitati\ValabilitateCursaOrderer;
use App\Support\Valabilitati\ValabilitatiCurseFilterState;
use App\Support\Valabilitati\ValabilitatiFilterState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class ValabilitateCursaController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request, Valabilitate $valabilitate): View
    {
        $this->authorize('view', $valabilitate);

        $curse = $this->paginateCurse($request, $valabilitate);

        ValabilitatiCurseFilterState::remember($request, $valabilitate, []);

        $valabilitate->loadMissing(['sofer']);

        return view('valabilitati.curse.index', [
            'valabilitate' => $valabilitate,
            'curse' => $curse,
            'nextPageUrl' => $this->buildNextPageUrl($request, $valabilitate, $curse),
            'backUrl' => ValabilitatiFilterState::route(),
            'tari' => $this->loadTari(),
        ]);
    }

    public function paginate(Request $request, Valabilitate $valabilitate): JsonResponse
    {
        $this->authorize('view', $valabilitate);

        $curse = $this->paginateCurse($request, $valabilitate);

        ValabilitatiCurseFilterState::remember($request, $valabilitate, []);

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
        ]);
    }

    private function resolveNextNrOrdine(Valabilitate $valabilitate): int
    {
        $max = (int) $valabilitate->curse()->max('nr_ordine');

        return $max > 0 ? $max + 1 : 1;
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
