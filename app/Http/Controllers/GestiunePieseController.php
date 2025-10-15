<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Throwable;

class GestiunePieseController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $filters = collect($request->input('filters', []))
            ->map(function ($value) {
                if (is_string($value)) {
                    return trim($value);
                }

                if (is_array($value)) {
                    return array_filter(array_map(static fn ($item) => is_string($item) ? trim($item) : $item, $value), static fn ($item) => $item !== '' && $item !== null);
                }

                return $value;
            })
            ->filter(function ($value) {
                if (is_array($value)) {
                    return ! empty($value);
                }

                return $value !== '' && $value !== null;
            });

        $columns = [];
        $hasTable = false;
        $items = null;
        $loadError = null;
        $displayColumns = [];
        $invoiceDateAlias = null;

        try {
            $hasTable = Schema::hasTable('gestiune_piese');

            if ($hasTable) {
                $columns = Schema::getColumnListing('gestiune_piese');
            }
        } catch (Throwable $exception) {
            $hasTable = false;
            $columns = [];
            Log::warning('Unable to inspect gestiune_piese structure', ['exception' => $exception]);
        }

        if ($hasTable) {
            try {
                $query = DB::table('gestiune_piese as gp');

                if (! empty($columns)) {
                    foreach ($columns as $column) {
                        $query->addSelect("gp.$column");
                    }
                } else {
                    $query->select('gp.*');
                }

                $invoiceJoinColumn = collect([
                    'factura_id',
                    'facturi_furnizori_id',
                    'ff_factura_id',
                    'ff_facturi_id',
                ])->first(static fn ($column) => in_array($column, $columns, true));

                if ($invoiceJoinColumn) {
                    $invoiceDateAlias = 'factura_data_factura';
                    $query->leftJoin('ff_facturi as ff', "ff.id", '=', "gp.$invoiceJoinColumn");
                    $query->addSelect('ff.data_factura as '.$invoiceDateAlias);
                }

                if ($search !== '') {
                    $searchableColumns = collect($columns)
                        ->reject(static fn ($column) => in_array($column, ['id'], true));

                    if ($invoiceDateAlias) {
                        $searchableColumns = $searchableColumns->push($invoiceDateAlias);
                    }

                    if ($searchableColumns->isNotEmpty()) {
                        $query->where(function ($innerQuery) use ($searchableColumns, $search) {
                            foreach ($searchableColumns as $column) {
                                $innerQuery->orWhere($column === 'factura_data_factura' ? 'ff.data_factura' : "gp.$column", 'like', "%$search%");
                            }
                        });
                    }
                }

                foreach ($filters as $column => $value) {
                    if ($column === 'factura_data_factura' && $invoiceDateAlias) {
                        if (is_array($value)) {
                            $query->whereIn('ff.data_factura', $value);
                        } else {
                            $query->where('ff.data_factura', $value);
                        }

                        continue;
                    }

                    if (! in_array($column, $columns, true)) {
                        continue;
                    }

                    if (is_array($value)) {
                        $query->whereIn("gp.$column", $value);
                    } else {
                        $query->where("gp.$column", 'like', "%$value%");
                    }
                }

                $sort = $request->query('sort');
                $direction = strtolower($request->query('direction', 'desc')) === 'asc' ? 'asc' : 'desc';

                if ($sort === 'factura_data_factura' && $invoiceDateAlias) {
                    $query->orderBy('ff.data_factura', $direction);
                } elseif ($sort && in_array($sort, $columns, true)) {
                    $query->orderBy("gp.$sort", $direction);
                } elseif ($invoiceDateAlias) {
                    $query->orderBy('ff.data_factura', 'desc');
                } elseif (in_array('updated_at', $columns, true)) {
                    $query->orderBy('gp.updated_at', 'desc');
                } elseif (in_array('created_at', $columns, true)) {
                    $query->orderBy('gp.created_at', 'desc');
                } elseif (in_array('id', $columns, true)) {
                    $query->orderBy('gp.id', 'desc');
                }

                $items = $query->simplePaginate(100)->withQueryString();
            } catch (Throwable $exception) {
                $loadError = 'Nu am putut încărca datele din gestiune_piese.';
                Log::error('Failed to load gestiune_piese data', ['exception' => $exception]);
            }
        }

        $displayColumns = $columns;
        if ($invoiceDateAlias && ! in_array($invoiceDateAlias, $displayColumns, true)) {
            $displayColumns[] = $invoiceDateAlias;
        }

        return view('gestiunePiese.index', [
            'search' => $search,
            'filters' => $filters->toArray(),
            'columns' => $displayColumns,
            'items' => $items,
            'hasTable' => $hasTable,
            'loadError' => $loadError,
        ]);
    }
}
