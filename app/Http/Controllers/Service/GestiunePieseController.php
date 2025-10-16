<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Service\MasinaServiceEntry;
use Carbon\CarbonInterface;

class GestiunePieseController extends Controller
{
    public function index(Request $request)
    {
        $denumireSearch = trim((string) $request->query('denumire'));
        $codSearch = trim((string) $request->query('cod'));
        $invoiceDateSearch = trim((string) $request->query('data_factura'));
        $invoiceDateFilter = null;
        $useExactInvoiceDate = false;

        if ($invoiceDateSearch !== '') {
            try {
                $invoiceDateFilter = Carbon::parse($invoiceDateSearch)->toDateString();
                $useExactInvoiceDate = true;
            } catch (\Throwable $exception) {
                $invoiceDateFilter = $invoiceDateSearch;
            }
        }

        $columns = [];
        $hasTable = false;
        $items = null;
        $loadError = null;
        $displayColumns = [];
        $invoiceDateAlias = null;
        $invoiceJoinColumn = null;

        $user = Auth::user();
        $isMechanic = $user && $user->hasRole('mecanic');

        try {
            $hasTable = Schema::hasTable('service_gestiune_piese');

            if ($hasTable) {
                $columns = Schema::getColumnListing('service_gestiune_piese');
            }
        } catch (\Throwable $exception) {
            $hasTable = false;
            $columns = [];
            Log::warning('Unable to inspect service_gestiune_piese structure', ['exception' => $exception]);
        }

        if ($hasTable) {
            try {
                $query = DB::table('service_gestiune_piese as gp');

                if (! empty($columns)) {
                    foreach ($columns as $column) {
                        $query->addSelect("gp.$column");
                    }
                } else {
                    $query->select('gp.*');
                }

                if (! $isMechanic) {
                    $invoiceJoinColumn = collect([
                        'factura_id',
                        'facturi_furnizori_id',
                        'ff_factura_id',
                        'ff_facturi_id',
                        'service_ff_factura_id',
                        'service_ff_facturi_id',
                    ])->first(static fn ($column) => in_array($column, $columns, true));

                    if ($invoiceJoinColumn) {
                        $invoiceDateAlias = 'factura_data_factura';
                        $query->leftJoin('service_ff_facturi as ff', "ff.id", '=', "gp.$invoiceJoinColumn");
                        $query->addSelect('ff.data_factura as '.$invoiceDateAlias);
                    }
                }

                if ($denumireSearch !== '' && in_array('denumire', $columns, true)) {
                    $query->where('gp.denumire', 'like', "%$denumireSearch%");
                }

                if ($codSearch !== '' && in_array('cod', $columns, true)) {
                    $query->where('gp.cod', 'like', "%$codSearch%");
                }

                if ($invoiceDateAlias && $invoiceDateFilter !== null) {
                    if ($useExactInvoiceDate) {
                        $query->whereDate('ff.data_factura', $invoiceDateFilter);
                    } else {
                        $query->where('ff.data_factura', 'like', "%$invoiceDateFilter%");
                    }
                }

                $sort = $request->query('sort');
                $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
                $customSortApplied = false;

                if ($sort === 'factura_data_factura' && $invoiceDateAlias) {
                    $query->orderBy('ff.data_factura', $direction);
                    $customSortApplied = true;
                } elseif ($sort && in_array($sort, $columns, true)) {
                    $query->orderBy("gp.$sort", $direction);
                    $customSortApplied = true;
                }

                if (! $customSortApplied) {
                    if (in_array('nr_bucati', $columns, true)) {
                        $query->orderByRaw('CASE WHEN gp.nr_bucati IS NULL OR gp.nr_bucati = 0 THEN 1 ELSE 0 END');
                    }

                    if (in_array('created_at', $columns, true)) {
                        $query->orderBy('gp.created_at', 'desc');
                    } elseif (in_array('id', $columns, true)) {
                        $query->orderBy('gp.id', 'desc');
                    }
                }

                $items = $query->simplePaginate(100)->withQueryString();
            } catch (\Throwable $exception) {
                $loadError = 'Nu am putut încărca datele din service_gestiune_piese.';
                Log::error('Failed to load service_gestiune_piese data', ['exception' => $exception]);
            }
        }

        $stockDetails = $items ? $this->buildStockDetails($items) : [];

        $displayColumns = array_values(array_filter(
            $columns,
            static fn ($column) => ! in_array($column, ['id', 'factura_id', 'created_at', 'updated_at'], true)
        ));
        if ($invoiceDateAlias && ! in_array($invoiceDateAlias, $displayColumns, true)) {
            $displayColumns[] = $invoiceDateAlias;
        }

        return view('service.gestiune-piese.index', [
            'denumire' => $denumireSearch,
            'cod' => $codSearch,
            'dataFactura' => $useExactInvoiceDate ? $invoiceDateFilter : $invoiceDateSearch,
            'columns' => $displayColumns,
            'items' => $items,
            'hasTable' => $hasTable,
            'loadError' => $loadError,
            'invoiceColumn' => $invoiceJoinColumn,
            'stockDetails' => $stockDetails,
        ]);
    }

    private function buildStockDetails($items): array
    {
        if (! $items) {
            return [];
        }

        $rows = collect($items->items());

        if ($rows->isEmpty()) {
            return [];
        }

        $pieceIds = $rows
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();

        if (empty($pieceIds)) {
            return [];
        }

        $usageByPiece = [];

        $entries = MasinaServiceEntry::query()
            ->select(['id', 'gestiune_piesa_id', 'masina_id', 'cantitate', 'data_montaj', 'created_at'])
            ->with(['masina:id,numar_inmatriculare,denumire'])
            ->whereIn('gestiune_piesa_id', $pieceIds)
            ->whereNotNull('cantitate')
            ->orderByDesc(DB::raw('COALESCE(data_montaj, created_at)'))
            ->orderByDesc('id')
            ->get();

        foreach ($entries as $entry) {
            $pieceId = (int) $entry->gestiune_piesa_id;

            if ($pieceId <= 0) {
                continue;
            }

            $quantity = (float) $entry->cantitate;

            if (! isset($usageByPiece[$pieceId])) {
                $usageByPiece[$pieceId] = [
                    'used' => 0.0,
                    'machines' => [],
                ];
            }

            $usageByPiece[$pieceId]['used'] += $quantity;

            $machine = $entry->masina;

            if (! $machine) {
                continue;
            }

            $machineId = (int) $machine->getKey();

            if ($machineId <= 0) {
                continue;
            }

            $date = $entry->data_montaj instanceof CarbonInterface
                ? $entry->data_montaj
                : ($entry->created_at instanceof CarbonInterface ? $entry->created_at : null);

            $usageByPiece[$pieceId]['machines'][] = [
                'masina_id' => $machineId,
                'numar_inmatriculare' => $machine->numar_inmatriculare,
                'denumire' => $machine->denumire,
                'cantitate' => $quantity,
                'data' => $date ? $date->format('d.m.Y') : null,
            ];
        }

        $details = [];

        foreach ($rows as $row) {
            $id = (int) ($row->id ?? 0);

            if ($id <= 0) {
                continue;
            }

            $remaining = isset($row->nr_bucati) ? (float) $row->nr_bucati : null;
            $initial = isset($row->cantitate_initiala) ? (float) $row->cantitate_initiala : null;
            $used = $usageByPiece[$id]['used'] ?? null;

            if ($used === null) {
                if ($initial !== null && $remaining !== null) {
                    $used = max($initial - $remaining, 0);
                } else {
                    $used = 0.0;
                }
            }

            if ($initial === null && $remaining !== null) {
                $initial = $remaining + $used;
            } elseif ($initial !== null && $remaining === null) {
                $remaining = max($initial - $used, 0);
            }

            $machines = array_map(static function ($machine) {
                return [
                    'masina_id' => $machine['masina_id'],
                    'numar_inmatriculare' => $machine['numar_inmatriculare'],
                    'denumire' => $machine['denumire'],
                    'cantitate' => round((float) $machine['cantitate'], 2),
                    'data' => $machine['data'] ?? null,
                ];
            }, array_values($usageByPiece[$id]['machines'] ?? []));

            $details[$id] = [
                'initial' => $initial !== null ? round($initial, 2) : null,
                'remaining' => $remaining !== null ? round($remaining, 2) : null,
                'used' => round($used, 2),
                'machines' => $machines,
            ];
        }

        return $details;
    }
}
