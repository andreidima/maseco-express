<?php

namespace App\Http\Controllers;

use App\Models\Valabilitate;
use App\Support\Valabilitati\ValabilitatiFilterState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
            'next_url' => $this->buildNextPageUrl($request, $valabilitati),
        ]);
    }

    private function validateFilters(Request $request): array
    {
        $validated = $request->validate([
            'numar_auto' => ['nullable', 'string', 'max:255'],
            'sofer' => ['nullable', 'string', 'max:255'],
            'denumire' => ['nullable', 'string', 'max:255'],
            'interval_start' => ['nullable', 'date'],
            'interval_end' => ['nullable', 'date', 'after_or_equal:interval_start'],
        ]);

        return [
            'numar_auto' => trim((string) ($validated['numar_auto'] ?? '')),
            'sofer' => trim((string) ($validated['sofer'] ?? '')),
            'denumire' => trim((string) ($validated['denumire'] ?? '')),
            'interval_start' => $validated['interval_start'] ?? null,
            'interval_end' => $validated['interval_end'] ?? null,
        ];
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
    private function paginateValabilitati(Request $request, array $filters): LengthAwarePaginator
    {
        return $this->buildFilteredQuery($filters)
            ->orderByDesc('data_inceput')
            ->orderBy('denumire')
            ->paginate(self::PER_PAGE)
            ->withQueryString();
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
}
