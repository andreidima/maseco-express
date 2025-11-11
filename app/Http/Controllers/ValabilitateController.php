<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Valabilitate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ValabilitateController extends Controller
{
    private const PER_PAGE = 20;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = $this->extractFilters($request);
        $valabilitati = $this->paginateValabilitati($request, $filters);
        $today = now()->startOfDay();

        $statusCounts = [
            'active' => Valabilitate::query()
                ->where(function ($subQuery) use ($today): void {
                    $subQuery
                        ->whereNull('data_sfarsit')
                        ->orWhereDate('data_sfarsit', '>=', $today);
                })
                ->count(),
            'expirate' => Valabilitate::query()
                ->whereNotNull('data_sfarsit')
                ->whereDate('data_sfarsit', '<', $today)
                ->count(),
        ];

        $denumiri = Valabilitate::query()
            ->select('denumire')
            ->distinct()
            ->orderBy('denumire')
            ->pluck('denumire');

        $numereAuto = Valabilitate::query()
            ->select('numar_auto')
            ->distinct()
            ->orderBy('numar_auto')
            ->pluck('numar_auto');

        $soferi = User::query()
            ->select('name')
            ->whereHas('valabilitati')
            ->orderBy('name')
            ->pluck('name');

        return view('valabilitati.index', [
            'valabilitati' => $valabilitati,
            'filters' => $filters,
            'statusCounts' => $statusCounts,
            'denumiri' => $denumiri,
            'numereAuto' => $numereAuto,
            'soferi' => $soferi,
            'nextPageUrl' => $this->buildNextPageUrl($request, $valabilitati),
        ]);
    }

    public function paginate(Request $request): JsonResponse
    {
        $filters = $this->extractFilters($request);
        $valabilitati = $this->paginateValabilitati($request, $filters);

        return response()->json([
            'rows_html' => view('valabilitati.partials.rows', [
                'valabilitati' => $valabilitati,
            ])->render(),
            'next_url' => $this->buildNextPageUrl($request, $valabilitati),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function extractFilters(Request $request): array
    {
        return [
            'status' => $request->input('status', 'toate'),
            'denumire' => trim((string) $request->input('denumire', '')),
            'sofer' => trim((string) $request->input('sofer', '')),
            'numar_auto' => trim((string) $request->input('numar_auto', '')),
            'inceput_de_la' => $request->input('inceput_de_la', ''),
            'inceput_pana_la' => $request->input('inceput_pana_la', ''),
            'sfarsit_de_la' => $request->input('sfarsit_de_la', ''),
            'sfarsit_pana_la' => $request->input('sfarsit_pana_la', ''),
            'fara_sfarsit' => $request->boolean('fara_sfarsit'),
        ];
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function buildFilteredQuery(array $filters): Builder
    {
        $query = Valabilitate::query()->with(['sofer']);

        if ($filters['denumire'] !== '') {
            $query->where('denumire', 'like', '%' . $filters['denumire'] . '%');
        }

        if ($filters['sofer'] !== '') {
            $query->whereHas('sofer', function (Builder $builder) use ($filters): void {
                $builder->where('name', 'like', '%' . $filters['sofer'] . '%');
            });
        }

        if ($filters['numar_auto'] !== '') {
            $query->where('numar_auto', 'like', '%' . $filters['numar_auto'] . '%');
        }

        if ($filters['inceput_de_la'] !== '') {
            $query->whereDate('data_inceput', '>=', $filters['inceput_de_la']);
        }

        if ($filters['inceput_pana_la'] !== '') {
            $query->whereDate('data_inceput', '<=', $filters['inceput_pana_la']);
        }

        if ($filters['sfarsit_de_la'] !== '') {
            $query->whereDate('data_sfarsit', '>=', $filters['sfarsit_de_la']);
        }

        if ($filters['sfarsit_pana_la'] !== '') {
            $query->whereDate('data_sfarsit', '<=', $filters['sfarsit_pana_la']);
        }

        if ($filters['fara_sfarsit']) {
            $query->whereNull('data_sfarsit');
        }

        $today = now()->startOfDay();
        $statusFilter = $filters['status'];

        if ($statusFilter === 'active') {
            $query->where(function (Builder $builder) use ($today): void {
                $builder
                    ->whereNull('data_sfarsit')
                    ->orWhereDate('data_sfarsit', '>=', $today);
            });
        } elseif ($statusFilter === 'expirate') {
            $query->whereNotNull('data_sfarsit')->whereDate('data_sfarsit', '<', $today);
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
