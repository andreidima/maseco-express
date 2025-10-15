<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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

        try {
            $hasTable = Schema::hasTable('gestiune_piese');

            if ($hasTable) {
                $columns = Schema::getColumnListing('gestiune_piese');
            }
        } catch (\Throwable $exception) {
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
            } catch (\Throwable $exception) {
                $loadError = 'Nu am putut încărca datele din gestiune_piese.';
                Log::error('Failed to load gestiune_piese data', ['exception' => $exception]);
            }
        }

        $displayColumns = array_values(array_filter(
            $columns,
            static fn ($column) => ! in_array($column, ['factura_id', 'created_at', 'updated_at'], true)
        ));
        if ($invoiceDateAlias && ! in_array($invoiceDateAlias, $displayColumns, true)) {
            $displayColumns[] = $invoiceDateAlias;
        }

        return view('gestiunePiese.index', [
            'denumire' => $denumireSearch,
            'cod' => $codSearch,
            'dataFactura' => $invoiceDateSearch,
            'columns' => $displayColumns,
            'items' => $items,
            'hasTable' => $hasTable,
            'loadError' => $loadError,
            'invoiceColumn' => $invoiceJoinColumn,
        ]);
    }
}
