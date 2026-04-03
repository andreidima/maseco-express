<?php

namespace App\Http\Controllers;

use App\Http\Requests\FacturiTransportatori\MoveComenziToCalupRequest;
use App\Models\Comanda;
use App\Models\FacturiTransportatori\PlataCalup;
use App\Services\FacturiTransportatori\PlataCalupService;
use App\Support\FacturiTransportatori\FacturiIndexFilterState;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FacturaTransportatorController extends Controller
{
    public function __construct(private PlataCalupService $plataCalupService)
    {
    }

    public function index(Request $request)
    {
        $allowedStatuses = ['neplatite', 'platite'];
        $defaultStatus = 'neplatite';
        $statusParam = $request->query('status', '__absent__');

        if ($statusParam === '__absent__') {
            $statusFilter = $defaultStatus;
            $statusValueForForm = $defaultStatus;
        } else {
            $normalizedStatus = is_string($statusParam) ? strtolower(trim($statusParam)) : '';

            if ($normalizedStatus === '' || $normalizedStatus === 'toate') {
                $statusFilter = null;
                $statusValueForForm = 'toate';
            } elseif (in_array($normalizedStatus, $allowedStatuses, true)) {
                $statusFilter = $normalizedStatus;
                $statusValueForForm = $normalizedStatus;
            } else {
                $statusFilter = null;
                $statusValueForForm = 'toate';
            }
        }

        $filters = [
            'transportator' => $request->string('transportator')->toString() ?: null,
            'comanda' => $request->string('comanda')->toString() ?: null,
            'scadenta_de_la' => $request->string('scadenta_de_la')->toString() ?: null,
            'scadenta_pana' => $request->string('scadenta_pana')->toString() ?: null,
            'are_pdf' => $request->string('are_pdf')->toString() ?: null,
            'calup' => $request->string('calup')->toString() ?: null,
            'calup_data_plata' => $request->string('calup_data_plata')->toString() ?: null,
            'status' => $statusValueForForm,
        ];

        $query = $this->baseQuery();

        if ($filters['transportator']) {
            $query->whereHas('transportator', function ($subQuery) use ($filters) {
                $subQuery->where('nume', 'like', '%' . $filters['transportator'] . '%');
            });
        }

        if ($filters['comanda']) {
            $query->where('transportator_contract', 'like', '%' . $filters['comanda'] . '%');
        }

        if ($filters['scadenta_de_la']) {
            $query->whereDate('data_scadenta_plata_transportator', '>=', Carbon::parse($filters['scadenta_de_la']));
        }

        if ($filters['scadenta_pana']) {
            $query->whereDate('data_scadenta_plata_transportator', '<=', Carbon::parse($filters['scadenta_pana']));
        }

        if ($filters['are_pdf'] === 'da') {
            $query->whereHas('facturiIncarcateDeTransportator');
        } elseif ($filters['are_pdf'] === 'nu') {
            $query->whereDoesntHave('facturiIncarcateDeTransportator');
        }

        if ($filters['calup'] || $filters['calup_data_plata']) {
            $query->whereHas('calupuriFacturiTransportatori', function ($subQuery) use ($filters) {
                if ($filters['calup']) {
                    $subQuery->where('denumire_calup', 'like', '%' . $filters['calup'] . '%');
                }

                if ($filters['calup_data_plata']) {
                    $subQuery->whereDate('data_plata', Carbon::parse($filters['calup_data_plata']));
                }
            });
        }

        if ($statusFilter === 'neplatite') {
            $query->whereDoesntHave('calupuriFacturiTransportatori');
        } elseif ($statusFilter === 'platite') {
            $query->whereHas('calupuriFacturiTransportatori');
        }

        $comenzi = $query
            ->orderByRaw('data_scadenta_plata_transportator IS NULL')
            ->orderBy('data_scadenta_plata_transportator')
            ->orderByRaw('data_factura_transportator IS NULL')
            ->orderBy('data_factura_transportator')
            ->orderByDesc('id')
            ->simplePaginate(25)
            ->withQueryString();

        $calupuriDisponibile = PlataCalup::query()
            ->orderByDesc('data_plata')
            ->orderByDesc('created_at')
            ->get(['id', 'denumire_calup', 'data_plata']);

        $neplatiteCount = $this->baseQuery()
            ->whereDoesntHave('calupuriFacturiTransportatori')
            ->count();

        FacturiIndexFilterState::remember($request);

        return view('facturi_transportatori.index', [
            'comenzi' => $comenzi,
            'filters' => $filters,
            'calupuriDisponibile' => $calupuriDisponibile,
            'neplatiteCount' => $neplatiteCount,
        ]);
    }

    public function moveToCalup(MoveComenziToCalupRequest $request)
    {
        $calup = PlataCalup::query()->findOrFail($request->validated('plata_calup_id'));
        $comenzi = $request->validated('comenzi');

        $this->plataCalupService->moveComenzi($calup, $comenzi);

        $count = count($comenzi);
        $message = $count === 1
            ? 'Comanda selectata a fost mutata in calupul ales.'
            : 'Comenzile selectate au fost mutate in calupul ales.';

        return $this->redirectToIndexWithFilters()->with('status', $message);
    }

    protected function redirectToIndexWithFilters()
    {
        return redirect()->route('facturi-transportatori.index', FacturiIndexFilterState::get());
    }

    protected function baseQuery()
    {
        return Comanda::query()
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
                'calupuriFacturiTransportatori:id,denumire_calup,data_plata',
            ])
            ->whereNotNull('transportator_transportator_id')
            ->where(function ($query) {
                $query->whereNotNull('factura_transportator')
                    ->orWhereNotNull('data_factura_transportator')
                    ->orWhereNotNull('data_scadenta_plata_transportator')
                    ->orWhere('factura_transportator_incarcata', 1);
            });
    }
}
