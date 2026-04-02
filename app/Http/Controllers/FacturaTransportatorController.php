<?php

namespace App\Http\Controllers;

use App\Models\Comanda;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FacturaTransportatorController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'transportator' => $request->string('transportator')->toString() ?: null,
            'comanda' => $request->string('comanda')->toString() ?: null,
            'scadenta_de_la' => $request->string('scadenta_de_la')->toString() ?: null,
            'scadenta_pana' => $request->string('scadenta_pana')->toString() ?: null,
            'are_pdf' => $request->string('are_pdf')->toString() ?: null,
        ];

        $query = Comanda::query()
            ->select([
                'id',
                'cheie_unica',
                'data_creare',
                'transportator_transportator_id',
                'transportator_contract',
                'factura_transportator',
                'data_factura_transportator',
                'data_scadenta_plata_transportator',
                'transportator_valoare_contract',
                'transportator_moneda_id',
                'factura_transportator_incarcata',
            ])
            ->with([
                'transportator:id,nume',
                'transportatorMoneda:id,nume',
                'facturiIncarcateDeTransportator:id,comanda_id,nume,este_factura',
                'locuriOperareDescarcari:id',
            ])
            ->whereNotNull('transportator_transportator_id')
            ->where(function ($query) {
                $query->whereNotNull('factura_transportator')
                    ->orWhereNotNull('data_factura_transportator')
                    ->orWhereNotNull('data_scadenta_plata_transportator')
                    ->orWhere('factura_transportator_incarcata', 1);
            })
            ->when($filters['transportator'], function ($query, $transportator) {
                $query->whereHas('transportator', function ($subQuery) use ($transportator) {
                    $subQuery->where('nume', 'like', '%' . $transportator . '%');
                });
            })
            ->when($filters['comanda'], function ($query, $comanda) {
                $query->where('transportator_contract', 'like', '%' . $comanda . '%');
            })
            ->when($filters['scadenta_de_la'], function ($query, $scadentaDeLa) {
                $query->whereDate('data_scadenta_plata_transportator', '>=', Carbon::parse($scadentaDeLa));
            })
            ->when($filters['scadenta_pana'], function ($query, $scadentaPana) {
                $query->whereDate('data_scadenta_plata_transportator', '<=', Carbon::parse($scadentaPana));
            })
            ->when($filters['are_pdf'] === 'da', function ($query) {
                $query->whereHas('facturiIncarcateDeTransportator');
            })
            ->when($filters['are_pdf'] === 'nu', function ($query) {
                $query->whereDoesntHave('facturiIncarcateDeTransportator');
            })
            ->orderByRaw('data_scadenta_plata_transportator IS NULL')
            ->orderBy('data_scadenta_plata_transportator')
            ->orderByRaw('data_factura_transportator IS NULL')
            ->orderBy('data_factura_transportator')
            ->orderByDesc('id');

        $comenzi = $query
            ->simplePaginate(25)
            ->withQueryString();

        return view('facturi_transportatori.index', [
            'comenzi' => $comenzi,
            'filters' => $filters,
        ]);
    }
}
