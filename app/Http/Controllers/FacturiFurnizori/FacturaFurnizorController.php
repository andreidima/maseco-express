<?php

namespace App\Http\Controllers\FacturiFurnizori;

use App\Http\Controllers\Controller;
use App\Http\Requests\FacturiFurnizori\FacturaFurnizorRequest;
use App\Models\FacturiFurnizori\FacturaFurnizor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FacturaFurnizorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = [
            'status' => null,
            'furnizor' => $request->string('furnizor')->toString() ?: null,
            'departament' => $request->string('departament')->toString() ?: null,
            'moneda' => $request->string('moneda')->toString() ?: null,
            'scadenta_de_la' => $request->string('scadenta_de_la')->toString() ?: null,
            'scadenta_pana' => $request->string('scadenta_pana')->toString() ?: null,
            'scadente_in_zile' => $request->has('scadente_in_zile')
                ? (int) $request->input('scadente_in_zile')
                : null,
        ];

        $query = FacturaFurnizor::query();

        $requestedStatus = $request->string('status')->toString();
        $validStatuses = [
            FacturaFurnizor::STATUS_NEPLATITA,
            FacturaFurnizor::STATUS_PLATITA,
        ];

        if ($requestedStatus === 'all') {
            $filters['status'] = 'all';
        } elseif (in_array($requestedStatus, $validStatuses, true)) {
            $filters['status'] = $requestedStatus;
        } else {
            $filters['status'] = FacturaFurnizor::STATUS_NEPLATITA;
        }

        if ($filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        if ($filters['furnizor']) {
            $query->where('denumire_furnizor', 'like', '%' . $filters['furnizor'] . '%');
        }

        if ($filters['departament']) {
            $query->where('departament_vehicul', 'like', '%' . $filters['departament'] . '%');
        }

        if ($filters['moneda']) {
            $query->where('moneda', strtoupper($filters['moneda']));
        }

        if ($filters['scadenta_de_la']) {
            $query->whereDate('data_scadenta', '>=', Carbon::parse($filters['scadenta_de_la']));
        }

        if ($filters['scadenta_pana']) {
            $query->whereDate('data_scadenta', '<=', Carbon::parse($filters['scadenta_pana']));
        }

        if (!is_null($filters['scadente_in_zile'])) {
            $days = $filters['scadente_in_zile'];
            $start = now()->startOfDay();
            $end   = ($days === 0)
                ? $start->copy()->endOfDay()        // exactly today
                : now()->addDays($days)->endOfDay(); // next N days

            $query->whereBetween('data_scadenta', [$start, $end]);
        }

        $facturi = $query
            ->with('calupuri:id,denumire_calup,status')
            ->orderBy('data_scadenta')
            ->orderBy('denumire_furnizor')
            ->paginate(25)
            ->withQueryString();

        $statusCounts = FacturaFurnizor::query()
            ->select('status', DB::raw('count(*) as total'))
            ->whereIn('status', $validStatuses)
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalFacturi = FacturaFurnizor::query()->count();

        $monede = FacturaFurnizor::query()
            ->select('moneda')
            ->distinct()
            ->orderBy('moneda')
            ->pluck('moneda');

        return view('facturiFurnizori.facturi.index', [
            'facturi' => $facturi,
            'filters' => $filters,
            'statusCounts' => $statusCounts,
            'statusOptions' => $this->statusOptions(),
            'statusTabs' => [
                FacturaFurnizor::STATUS_NEPLATITA => 'Neplătite',
                FacturaFurnizor::STATUS_PLATITA => 'Plătite',
                'all' => 'Toate',
            ],
            'totalFacturi' => $totalFacturi,
            'monede' => $monede,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('facturiFurnizori.facturi.save', [
            'factura' => new FacturaFurnizor(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FacturaFurnizorRequest $request)
    {
        $payload = $request->validated();
        $payload['status'] = FacturaFurnizor::STATUS_NEPLATITA;
        $payload['moneda'] = strtoupper($payload['moneda']);

        $factura = FacturaFurnizor::create($payload);

        return redirect()
            ->route('facturi-furnizori.facturi.index')
            ->with('status', 'Factura a fost adaugata cu succes.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FacturaFurnizor $factura)
    {
        $factura->load('calupuri');

        return view('facturiFurnizori.facturi.show', [
            'factura' => $factura,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FacturaFurnizor $factura)
    {
        $factura->loadMissing('calupuri:id,denumire_calup');

        return view('facturiFurnizori.facturi.save', [
            'factura' => $factura,
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FacturaFurnizorRequest $request, FacturaFurnizor $factura)
    {
        $payload = $request->validated();
        $payload['moneda'] = strtoupper($payload['moneda']);

        // Status is controlled by workflows, so keep the existing value.
        unset($payload['status']);

        $factura->update($payload);

        return redirect()
            ->route('facturi-furnizori.facturi.index')
            ->with('status', 'Factura a fost actualizata cu succes.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(FacturaFurnizor $factura)
    {
        if ($factura->status === FacturaFurnizor::STATUS_PLATITA) {
            return back()->with('error', 'Factura platita nu poate fi stearsa.');
        }

        $factura->calupuri()->detach();
        $factura->delete();

        return redirect()
            ->route('facturi-furnizori.facturi.index')
            ->with('status', 'Factura a fost stearsa.');
    }

    /**
     * Return suggestions for typeahead inputs.
     */
    public function sugestii(Request $request)
    {
        $tip = $request->string('tip')->toString();
        $cautare = $request->string('q')->toString();
        $limit = min($request->integer('limit', 10), 20);

        $coloana = match ($tip) {
            'furnizor' => 'denumire_furnizor',
            'departament' => 'departament_vehicul',
            default => null,
        };

        if (!$coloana) {
            return response()->json(['message' => 'Tip de sugestie necunoscut.'], 422);
        }

        $valori = FacturaFurnizor::query()
            ->when($cautare, function ($query) use ($coloana, $cautare) {
                $query->where($coloana, 'like', $cautare . '%');
            })
            ->whereNotNull($coloana)
            ->distinct()
            ->orderBy($coloana)
            ->limit($limit)
            ->pluck($coloana);

        return response()->json($valori);
    }

    private function statusOptions(): array
    {
        return [
            FacturaFurnizor::STATUS_NEPLATITA => 'Neplătită',
            FacturaFurnizor::STATUS_PLATITA => 'Plătită',
        ];
    }
}
