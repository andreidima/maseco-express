<?php

namespace App\Http\Controllers\FacturiFurnizori;

use App\Http\Controllers\Controller;
use App\Http\Requests\FacturiFurnizori\FacturaFurnizorRequest;
use App\Models\FacturiFurnizori\FacturaFurnizor;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class FacturaFurnizorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $allowedStatuses = ['neplatite', 'platite'];
        $defaultStatus = 'neplatite';
        $statusParam = $request->query('status', '__absent__');

        if ($statusParam === '__absent__') {
            $statusFilter = $defaultStatus;
            $statusValueForForm = $defaultStatus;
        } else {
            $statusValueForForm = is_string($statusParam) ? $statusParam : '';
            $statusFilter = in_array($statusValueForForm, $allowedStatuses, true)
                ? $statusValueForForm
                : null;
        }

        $filters = [
            'furnizor' => $request->string('furnizor')->toString() ?: null,
            'departament' => $request->string('departament')->toString() ?: null,
            'moneda' => $request->string('moneda')->toString() ?: null,
            'scadenta_de_la' => $request->string('scadenta_de_la')->toString() ?: null,
            'scadenta_pana' => $request->string('scadenta_pana')->toString() ?: null,
            'calup' => $request->string('calup')->toString() ?: null,
            'calup_data_plata' => $request->string('calup_data_plata')->toString() ?: null,
            'status' => $statusValueForForm,
        ];

        $query = FacturaFurnizor::query();

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

        if ($filters['calup'] || $filters['calup_data_plata']) {
            $query->whereHas('calupuri', function ($subQuery) use ($filters) {
                if ($filters['calup']) {
                    $subQuery->where('denumire_calup', 'like', '%' . $filters['calup'] . '%');
                }

                if ($filters['calup_data_plata']) {
                    $subQuery->whereDate('data_plata', Carbon::parse($filters['calup_data_plata']));
                }
            });
        }

        if ($statusFilter === 'neplatite') {
            $query->whereDoesntHave('calupuri');
        } elseif ($statusFilter === 'platite') {
            $query->whereHas('calupuri');
        }

        $facturi = $query
            ->with('calupuri:id,denumire_calup,data_plata')
            ->orderByRaw('data_scadenta IS NULL')
            ->orderBy('data_scadenta')
            ->orderBy('denumire_furnizor')
            ->paginate(25)
            ->withQueryString();

        $monede = FacturaFurnizor::query()
            ->select('moneda')
            ->distinct()
            ->orderBy('moneda')
            ->pluck('moneda');

        $neplatiteCount = FacturaFurnizor::query()->whereDoesntHave('calupuri')->count();

        return view('facturiFurnizori.facturi.index', [
            'facturi' => $facturi,
            'filters' => $filters,
            'monede' => $monede,
            'neplatiteCount' => $neplatiteCount,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('facturiFurnizori.facturi.save', [
            'factura' => new FacturaFurnizor(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FacturaFurnizorRequest $request)
    {
        $payload = $request->validated();
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
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FacturaFurnizorRequest $request, FacturaFurnizor $factura)
    {
        $payload = $request->validated();
        $payload['moneda'] = strtoupper($payload['moneda']);

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
        if ($factura->calupuri()->exists()) {
            return back()->with('error', 'Factura atasata unui calup nu poate fi stearsa.');
        }

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
        $cautare = trim($request->string('q')->toString());
        $initial = $request->boolean('initial');

        $implicitLimit = $initial ? 50 : 10;
        $limitMax = $initial ? 75 : 20;
        $limitSolicitat = (int) $request->integer('limit', $implicitLimit);
        $limit = max(1, min($limitSolicitat, $limitMax));

        $coloana = match ($tip) {
            'furnizor' => 'denumire_furnizor',
            'departament' => 'departament_vehicul',
            default => null,
        };

        if (!$coloana) {
            return response()->json(['message' => 'Tip de sugestie necunoscut.'], 422);
        }

        $valori = FacturaFurnizor::query()
            ->whereNotNull($coloana)
            ->when($cautare !== '', function ($query) use ($coloana, $cautare) {
                $query->where($coloana, 'like', $cautare . '%');
            })
            ->distinct()
            ->orderBy($coloana)
            ->limit($limit)
            ->pluck($coloana);

        return response()->json($valori);
    }

}
