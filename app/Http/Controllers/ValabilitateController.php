<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Valabilitate;
use App\Support\Valabilitati\ValabilitatiFilterState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ValabilitateController extends Controller
{
    private const PER_PAGE = 20;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = $this->validateFilters($request);
        $valabilitati = $this->paginateValabilitati($request, $filters);

        ValabilitatiFilterState::remember($request, $this->filtersToQueryString($filters));

        return view('valabilitati.index', [
            'valabilitati' => $valabilitati,
            'filters' => $filters,
            'nextPageUrl' => $this->buildNextPageUrl($request, $valabilitati),
            'soferi' => $this->getSoferOptions(),
        ]);
    }

    public function paginate(Request $request): JsonResponse
    {
        $filters = $this->validateFilters($request);
        $valabilitati = $this->paginateValabilitati($request, $filters);

        return response()->json([
            'rows_html' => view('valabilitati.partials.rows', [
                'valabilitati' => $valabilitati,
            ])->render(),
            'modals_html' => view('valabilitati.partials.modals', [
                'valabilitati' => $valabilitati,
                'soferi' => $this->getSoferOptions(),
                'includeCreate' => false,
            ])->render(),
            'next_url' => $this->buildNextPageUrl($request, $valabilitati),
        ]);
    }

    public function show(Valabilitate $valabilitate): View
    {
        $valabilitate->loadMissing([
            'sofer',
            'curse' => fn ($query) => $query
                ->orderByDesc('data_cursa')
                ->orderByDesc('created_at'),
        ]);

        return view('valabilitati.show', [
            'valabilitate' => $valabilitate,
            'backUrl' => ValabilitatiFilterState::route(),
            'soferi' => $this->getSoferOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $this->validateValabilitate($request);

        if ($validated instanceof RedirectResponse) {
            return $validated;
        }

        Valabilitate::create($validated);

        return $this->respondAfterMutation($request, 'Valabilitatea a fost adăugată.');
    }

    public function update(Request $request, Valabilitate $valabilitate): RedirectResponse|JsonResponse
    {
        $validated = $this->validateValabilitate($request, $valabilitate);

        if ($validated instanceof RedirectResponse) {
            return $validated;
        }

        $valabilitate->update($validated);

        return $this->respondAfterMutation($request, 'Valabilitatea a fost actualizată.');
    }

    public function destroy(Request $request, Valabilitate $valabilitate): RedirectResponse|JsonResponse
    {
        $valabilitate->delete();

        return $this->respondAfterMutation($request, 'Valabilitatea a fost ștearsă.');
    }

    private function validateFilters(Request $request): array
    {
        return $this->parseFilters($request->only([
            'numar_auto',
            'sofer',
            'denumire',
            'interval_start',
            'interval_end',
        ]));
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    private function filtersToQueryString(array $filters): array
    {
        return array_filter($filters, static fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function buildFilteredQuery(array $filters): Builder
    {
        $query = Valabilitate::query()->with(['sofer']);

        if ($filters['denumire'] !== '') {
            $denumire = Str::lower($filters['denumire']);
            $query->whereRaw('LOWER(denumire) LIKE ?', ["%{$denumire}%"]);
        }

        if ($filters['sofer'] !== '') {
            $sofer = Str::lower($filters['sofer']);
            $query->whereHas('sofer', function (Builder $builder) use ($sofer): void {
                $builder->whereRaw('LOWER(name) LIKE ?', ["%{$sofer}%"]);
            });
        }

        if ($filters['numar_auto'] !== '') {
            $numarAuto = Str::lower($filters['numar_auto']);
            $query->whereRaw('LOWER(numar_auto) LIKE ?', ["%{$numarAuto}%"]);
        }

        if ($filters['interval_start']) {
            $query->whereDate('data_inceput', '>=', $filters['interval_start']);
        }

        if ($filters['interval_end']) {
            $query->whereDate('data_inceput', '<=', $filters['interval_end']);
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function paginateValabilitati(Request $request, array $filters, ?array $query = null): LengthAwarePaginator
    {
        $paginator = $this->buildFilteredQuery($filters)
            ->orderByDesc('data_inceput')
            ->orderBy('denumire')
            ->paginate(self::PER_PAGE);

        if ($query !== null) {
            return $paginator->appends($query);
        }

        return $paginator->withQueryString();
    }

    private function buildNextPageUrl(Request $request, LengthAwarePaginator $paginator): ?string
    {
        if (! $paginator->hasMorePages()) {
            return null;
        }

        $queryParameters = Arr::except($request->query(), ['page']);
        $nextPage = $paginator->currentPage() + 1;

        return route('valabilitati.paginate', array_merge($queryParameters, ['page' => $nextPage]));
    }

    /**
     * @param array<string, mixed> $input
     */
    private function parseFilters(array $input): array
    {
        $validator = Validator::make($input, [
            'numar_auto' => ['nullable', 'string', 'max:255'],
            'sofer' => ['nullable', 'string', 'max:255'],
            'denumire' => ['nullable', 'string', 'max:255'],
            'interval_start' => ['nullable', 'date'],
            'interval_end' => ['nullable', 'date', 'after_or_equal:interval_start'],
        ]);

        $validated = $validator->validate();

        return [
            'numar_auto' => trim((string) ($validated['numar_auto'] ?? '')),
            'sofer' => trim((string) ($validated['sofer'] ?? '')),
            'denumire' => trim((string) ($validated['denumire'] ?? '')),
            'interval_start' => $validated['interval_start'] ?? null,
            'interval_end' => $validated['interval_end'] ?? null,
        ];
    }

    private function getSoferOptions(): array
    {
        return User::query()
            ->where('activ', 1)
            ->whereHas('roles', function (Builder $query): void {
                $query->where('slug', 'sofer');
            })
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    /**
     * @return array<string, mixed>|RedirectResponse
     */
    private function validateValabilitate(Request $request, ?Valabilitate $valabilitate = null)
    {
        $validator = Validator::make($request->all(), [
            'numar_auto' => ['required', 'string', 'max:255'],
            'sofer_id' => ['required', 'integer', 'exists:users,id'],
            'denumire' => ['required', 'string', 'max:255'],
            'data_inceput' => ['required', 'date'],
            'data_sfarsit' => ['nullable', 'date', 'after_or_equal:data_inceput'],
        ], [], [
            'numar_auto' => 'număr auto',
            'sofer_id' => 'șofer',
            'denumire' => 'denumire',
            'data_inceput' => 'data de început',
            'data_sfarsit' => 'data de sfârșit',
        ]);

        if ($validator->fails() && ! $request->expectsJson()) {
            $modalKey = $valabilitate ? 'edit:' . $valabilitate->id : 'create';

            return redirect(ValabilitatiFilterState::route())
                ->withErrors($validator)
                ->withInput()
                ->with('valabilitati.modal', $modalKey);
        }

        try {
            return $validator->validate();
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                throw $exception;
            }

            $modalKey = $valabilitate ? 'edit:' . $valabilitate->id : 'create';

            return redirect(ValabilitatiFilterState::route())
                ->withErrors($exception->validator)
                ->withInput()
                ->with('valabilitati.modal', $modalKey);
        }
    }

    private function respondAfterMutation(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return $this->listingJsonResponse($message);
        }

        return redirect(ValabilitatiFilterState::route())->with('status', $message);
    }

    private function listingJsonResponse(string $message): JsonResponse
    {
        $query = ValabilitatiFilterState::get();
        $filtersRequest = Request::create('', 'GET', $query);
        $filters = $this->parseFilters($query);
        $valabilitati = $this->paginateValabilitati($filtersRequest, $filters, $query);

        return response()->json([
            'message' => $message,
            'table_html' => view('valabilitati.partials.rows', [
                'valabilitati' => $valabilitati,
            ])->render(),
            'modals_html' => view('valabilitati.partials.modals', [
                'valabilitati' => $valabilitati,
                'soferi' => $this->getSoferOptions(),
                'includeCreate' => true,
            ])->render(),
            'next_url' => $this->buildNextPageUrl($filtersRequest, $valabilitati),
        ]);
    }
}
