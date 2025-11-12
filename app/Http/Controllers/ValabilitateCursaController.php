<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValabilitateCursaRequest;
use App\Models\Tara;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use App\Support\Valabilitati\ValabilitatiCurseFilterState;
use App\Support\Valabilitati\ValabilitatiFilterState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ValabilitateCursaController extends Controller
{
    private const PER_PAGE = 20;

    public function index(Request $request, Valabilitate $valabilitate): View
    {
        $this->authorize('view', $valabilitate);

        $filters = $this->validateFilters($request);
        $curse = $this->paginateCurse($request, $valabilitate, $filters);

        ValabilitatiCurseFilterState::remember($request, $valabilitate, $this->filtersToQueryString($filters));

        $valabilitate->loadMissing(['sofer']);

        return view('valabilitati.curse.index', [
            'valabilitate' => $valabilitate,
            'curse' => $curse,
            'filters' => $filters,
            'nextPageUrl' => $this->buildNextPageUrl($request, $valabilitate, $curse),
            'backUrl' => ValabilitatiFilterState::route(),
            'tari' => $this->loadTari(),
        ]);
    }

    public function paginate(Request $request, Valabilitate $valabilitate): JsonResponse
    {
        $this->authorize('view', $valabilitate);

        $filters = $this->validateFilters($request);
        $curse = $this->paginateCurse($request, $valabilitate, $filters);

        ValabilitatiCurseFilterState::remember($request, $valabilitate, $this->filtersToQueryString($filters));

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

        $valabilitate->curse()->create($request->validated());

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

    private function validateFilters(Request $request): array
    {
        return $this->parseFilters($request->only([
            'localitate',
            'cod_postal',
            'data_start',
            'data_end',
            'observatii',
        ]));
    }

    private function parseFilters(array $input): array
    {
        $validator = validator($input, [
            'localitate' => ['nullable', 'string', 'max:255'],
            'cod_postal' => ['nullable', 'string', 'max:255'],
            'data_start' => ['nullable', 'date'],
            'data_end' => ['nullable', 'date', 'after_or_equal:data_start'],
            'observatii' => ['nullable', 'string'],
        ]);

        $validated = $validator->validate();

        return [
            'localitate' => trim((string) ($validated['localitate'] ?? '')),
            'cod_postal' => trim((string) ($validated['cod_postal'] ?? '')),
            'data_start' => $validated['data_start'] ?? null,
            'data_end' => $validated['data_end'] ?? null,
            'observatii' => trim((string) ($validated['observatii'] ?? '')),
        ];
    }

    private function filtersToQueryString(array $filters): array
    {
        return array_filter($filters, static fn ($value) => $value !== null && $value !== '');
    }

    private function buildFilteredQuery(Valabilitate $valabilitate, array $filters): HasMany
    {
        $query = $valabilitate->curse()->with([
            'incarcareTara',
            'descarcareTara',
        ]);

        if ($filters['localitate'] !== '') {
            $term = Str::lower($filters['localitate']);
            $query->where(function (Builder $builder) use ($term): void {
                $builder
                    ->whereRaw('LOWER(incarcare_localitate) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(descarcare_localitate) LIKE ?', ["%{$term}%"]);
            });
        }

        if ($filters['cod_postal'] !== '') {
            $term = Str::lower($filters['cod_postal']);
            $query->where(function (Builder $builder) use ($term): void {
                $builder
                    ->whereRaw('LOWER(incarcare_cod_postal) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(descarcare_cod_postal) LIKE ?', ["%{$term}%"]);
            });
        }

        if ($filters['observatii'] !== '') {
            $term = Str::lower($filters['observatii']);
            $query->whereRaw('LOWER(observatii) LIKE ?', ["%{$term}%"]);
        }

        if ($filters['data_start']) {
            $query->whereDate('data_cursa', '>=', $filters['data_start']);
        }

        if ($filters['data_end']) {
            $query->whereDate('data_cursa', '<=', $filters['data_end']);
        }

        return $query;
    }

    private function paginateCurse(Request $request, Valabilitate $valabilitate, array $filters, ?array $query = null): LengthAwarePaginator
    {
        $paginator = $this->buildFilteredQuery($valabilitate, $filters)
            ->orderByDesc('data_cursa')
            ->orderByDesc('created_at')
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
        $filters = $this->parseFilters($query);
        $curse = $this->paginateCurse($filtersRequest, $valabilitate, $filters, $query);

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
                'tari' => $this->loadTari(),
            ])->render(),
            'next_url' => $this->buildNextPageUrl($filtersRequest, $valabilitate, $curse),
        ]);
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
