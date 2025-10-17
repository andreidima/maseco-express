<?php

namespace App\Http\Controllers\FacturiFurnizori;

use App\Http\Controllers\Controller;
use App\Http\Requests\FacturiFurnizori\FacturaFurnizorRequest;
use App\Models\FacturiFurnizori\FacturaFurnizor;
use App\Models\FacturiFurnizori\FacturaFurnizorFisier;
use App\Models\Service\GestiunePiesa;
use App\Models\Service\MasinaServiceEntry;
use App\Support\FacturiFurnizori\FacturiIndexFilterState;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\CarbonInterface;

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
            'furnizor' => $request->string('furnizor')->toString() ?: null,
            'departament' => $request->string('departament')->toString() ?: null,
            'moneda' => $request->string('moneda')->toString() ?: null,
            'numar_factura' => $request->string('numar_factura')->toString() ?: null,
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

        if ($filters['numar_factura']) {
            $query->where('numar_factura', $filters['numar_factura']);
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
            ->withCount('fisiere')
            ->orderByRaw('data_scadenta IS NULL')
            ->orderBy('data_scadenta')
            ->orderBy('denumire_furnizor')
            ->cursorPaginate(25)
            ->withQueryString();

        $monede = FacturaFurnizor::query()
            ->select('moneda')
            ->distinct()
            ->orderBy('moneda')
            ->pluck('moneda');

        $neplatiteCount = FacturaFurnizor::query()->whereDoesntHave('calupuri')->count();

        FacturiIndexFilterState::remember($request);

        if ($request->expectsJson()) {
            return response()->json([
                'rows_html' => view('facturiFurnizori.facturi.partials.factura-rows', [
                    'facturi' => $facturi,
                    'selectedFacturiOld' => [],
                ])->render(),
                'modals_html' => view('facturiFurnizori.facturi.partials.factura-modals', [
                    'facturi' => $facturi,
                ])->render(),
                'next_url' => $facturi->nextPageUrl(),
            ]);
        }

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
        $payload['cont_iban'] = $this->normalizeContIban($payload['cont_iban'] ?? null);

        $produse = $this->prepareProduse($payload);
        unset($payload['produse']);

        $fisiereNoi = $this->normalizeUploadedFiles($request->file('fisiere_pdf'));
        unset($payload['fisiere_pdf']);

        $factura = DB::transaction(function () use ($payload, $produse) {
            $factura = FacturaFurnizor::create($payload);

            if (! empty($produse)) {
                $pieces = collect($produse)
                    ->map(fn ($produs) => $this->formatPieceForPersistence($produs))
                    ->map(fn ($produs) => collect($produs)->except(['id', 'form_index'])->all())
                    ->all();

                if (! empty($pieces)) {
                    $factura->piese()->createMany($pieces);
                }
            }

            return $factura;
        });

        if (!empty($fisiereNoi)) {
            $this->adaugaFisiere($factura, $fisiereNoi);
        }

        return $this->redirectToIndexWithFilters()
            ->with('status', 'Factura a fost adaugata cu succes.');
    }

    /**
     * Display the specified resource.
     */
    public function show(FacturaFurnizor $factura)
    {
        $factura->load(['calupuri', 'piese', 'fisiere']);

        $stockDetails = $this->buildPieceStockDetails($factura->piese);

        return view('facturiFurnizori.facturi.show', [
            'factura' => $factura,
            'stockDetails' => $stockDetails,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(FacturaFurnizor $factura)
    {
        $factura->loadMissing(['calupuri:id,denumire_calup', 'piese', 'fisiere']);

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
        $payload['cont_iban'] = $this->normalizeContIban($payload['cont_iban'] ?? null);

        $produse = $this->prepareProduse($payload);
        unset($payload['produse']);

        $fisiereNoi = $this->normalizeUploadedFiles($request->file('fisiere_pdf'));
        unset($payload['fisiere_pdf']);

        DB::transaction(function () use ($factura, $payload, $produse) {
            $factura->update($payload);

            $existingPieces = $factura->piese()->get()->keyBy('id');
            $usageTotals = $this->fetchPieceUsageTotals($existingPieces->keys()->all());

            foreach ($produse as $produs) {
                $pieceId = $produs['id'] ?? null;
                $formIndex = $produs['form_index'] ?? null;

                if ($pieceId && $existingPieces->has($pieceId)) {
                    /** @var GestiunePiesa $existing */
                    $existing = $existingPieces->get($pieceId);
                    $used = $usageTotals[$pieceId] ?? 0.0;

                    $data = $this->formatPieceForPersistence($produs, $used, $existing);
                    $this->assertInitialQuantityIsValid($data, $used, $formIndex);

                    $existing->update(collect($data)->except(['id', 'form_index'])->all());
                    $existingPieces->forget($pieceId);
                } else {
                    $data = $this->formatPieceForPersistence($produs);
                    $this->assertInitialQuantityIsValid($data, 0.0, $formIndex);

                    $factura->piese()->create(collect($data)->except(['id', 'form_index'])->all());
                }
            }

            if ($existingPieces->isNotEmpty()) {
                foreach ($existingPieces as $piece) {
                    $used = $usageTotals[$piece->id] ?? 0.0;

                    if ($used > 0) {
                        throw ValidationException::withMessages([
                            'produse' => 'Nu poți elimina piesa „' . $piece->denumire . '” deoarece este deja alocată către mașini.',
                        ]);
                    }

                    $piece->delete();
                }
            }
        });

        if (!empty($fisiereNoi)) {
            $factura->refresh();
            $this->adaugaFisiere($factura, $fisiereNoi);
        }

        return $this->redirectToIndexWithFilters()
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

        $arePieseMontate = $factura->piese()->whereHas('serviceEntries')->exists();

        if ($arePieseMontate) {
            return back()->with('error', 'Factura nu poate fi stearsa deoarece are piese montate pe masini.');
        }

        $factura->loadMissing('fisiere');

        foreach ($factura->fisiere as $fisier) {
            if ($fisier->cale && Storage::exists($fisier->cale)) {
                Storage::delete($fisier->cale);
            }
        }

        $factura->delete();

        return $this->redirectToIndexWithFilters()
            ->with('status', 'Factura a fost stearsa.');
    }

    protected function redirectToIndexWithFilters(): RedirectResponse
    {
        $filters = FacturiIndexFilterState::get();

        return redirect()->route('facturi-furnizori.facturi.index', $filters);
    }

    protected function prepareProduse(array $payload): array
    {
        $produse = collect($payload['produse'] ?? [])
            ->map(function ($row) {
                $denumire = isset($row['denumire']) ? trim((string) $row['denumire']) : '';
                $cod = isset($row['cod']) ? trim((string) $row['cod']) : '';
                $id = isset($row['id']) ? (int) $row['id'] : null;
                $formIndex = $row['form_index'] ?? null;
                $nrBucati = isset($row['nr_bucati']) && $row['nr_bucati'] !== ''
                    ? round((float) $row['nr_bucati'], 2)
                    : null;
                $cantitateInitiala = isset($row['cantitate_initiala']) && $row['cantitate_initiala'] !== ''
                    ? round((float) $row['cantitate_initiala'], 2)
                    : null;
                $pret = isset($row['pret']) && $row['pret'] !== ''
                    ? round((float) $row['pret'], 2)
                    : null;
                $tvaCota = isset($row['tva_cota']) && $row['tva_cota'] !== ''
                    ? round((float) $row['tva_cota'], 2)
                    : null;

                if ($denumire === '' && $cod === '' && $nrBucati === null && $pret === null && $tvaCota === null) {
                    return null;
                }

                if ($denumire === '') {
                    return null;
                }

                $pretBrut = null;

                if ($pret !== null && $tvaCota !== null) {
                    $rate = $tvaCota / 100;
                    $quantityForCalc = $nrBucati !== null && $nrBucati > 0 ? $nrBucati : 1;
                    $totalNet = round($pret * $quantityForCalc, 2);
                    $totalGross = round($totalNet * (1 + $rate), 2);
                    $pretBrut = round($quantityForCalc > 0 ? $totalGross / $quantityForCalc : 0, 2);
                }

                return [
                    'id' => $id,
                    'denumire' => $denumire,
                    'cod' => $cod !== '' ? $cod : null,
                    'cantitate_initiala' => $cantitateInitiala,
                    'nr_bucati' => $nrBucati,
                    'pret' => $pret,
                    'tva_cota' => $tvaCota,
                    'pret_brut' => $pretBrut,
                    'form_index' => $formIndex,
                ];
            })
            ->filter()
            ->values()
            ->all();

        return $produse;
    }

    private function formatPieceForPersistence(array $produs, float $used = 0.0, ?GestiunePiesa $existing = null): array
    {
        $denumire = $produs['denumire'] ?? null;
        $cod = $produs['cod'] ?? null;

        $cantitateInitiala = $this->toNullableFloat($produs['cantitate_initiala'] ?? null);
        $nrBucati = $this->toNullableFloat($produs['nr_bucati'] ?? null);
        $pret = $this->toNullableFloat($produs['pret'] ?? null);
        $tvaCota = $this->toNullableFloat($produs['tva_cota'] ?? null);
        $pretBrut = $this->toNullableFloat($produs['pret_brut'] ?? null);

        if ($existing) {
            if ($cantitateInitiala === null && $existing->cantitate_initiala !== null) {
                $cantitateInitiala = (float) $existing->cantitate_initiala;
            }

            if ($nrBucati === null && $existing->nr_bucati !== null) {
                $nrBucati = (float) $existing->nr_bucati;
            }

            if ($pret === null && $existing->pret !== null) {
                $pret = (float) $existing->pret;
            }

            if ($tvaCota === null && $existing->tva_cota !== null) {
                $tvaCota = (float) $existing->tva_cota;
            }

            if ($pretBrut === null && $existing->pret_brut !== null) {
                $pretBrut = (float) $existing->pret_brut;
            }
        }

        if ($cantitateInitiala === null && $nrBucati !== null) {
            $cantitateInitiala = round($nrBucati + $used, 2);
        }

        if ($cantitateInitiala !== null && $nrBucati === null) {
            $nrBucati = round(max($cantitateInitiala - $used, 0), 2);
        }

        return [
            'denumire' => $denumire,
            'cod' => $cod !== null && $cod !== '' ? $cod : null,
            'cantitate_initiala' => $cantitateInitiala,
            'nr_bucati' => $nrBucati,
            'pret' => $pret,
            'tva_cota' => $tvaCota,
            'pret_brut' => $pretBrut,
        ];
    }

    private function fetchPieceUsageTotals(array $pieceIds): array
    {
        if (empty($pieceIds)) {
            return [];
        }

        return DB::table('service_masina_service_entries')
            ->select('gestiune_piesa_id', DB::raw('SUM(cantitate) as total'))
            ->whereIn('gestiune_piesa_id', $pieceIds)
            ->where('tip', 'piesa')
            ->whereNotNull('cantitate')
            ->groupBy('gestiune_piesa_id')
            ->pluck('total', 'gestiune_piesa_id')
            ->map(fn ($value) => round((float) $value, 2))
            ->all();
    }

    private function buildPieceStockDetails($pieces): array
    {
        $rows = $pieces instanceof \Illuminate\Support\Collection ? $pieces : collect($pieces);

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
                'cantitate' => round($quantity, 2),
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

    private function assertInitialQuantityIsValid(array $data, float $used, $formIndex): void
    {
        $initial = $data['cantitate_initiala'] ?? null;

        if ($used > 0 && $initial === null) {
            $message = 'Cantitatea inițială este obligatorie deoarece există deja alocări (' . number_format($used, 2) . ').';
            $key = is_numeric($formIndex) ? 'produse.' . $formIndex . '.cantitate_initiala' : 'produse';

            throw ValidationException::withMessages([$key => $message]);
        }

        if ($initial !== null && $initial + 1e-6 < $used) {
            $message = 'Cantitatea inițială nu poate fi mai mică decât cantitatea deja alocată (' . number_format($used, 2) . ').';
            $key = is_numeric($formIndex) ? 'produse.' . $formIndex . '.cantitate_initiala' : 'produse';

            throw ValidationException::withMessages([$key => $message]);
        }
    }

    private function toNullableFloat($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return round((float) $value, 2);
    }

    /**
     * @param array<int, UploadedFile>|UploadedFile|null $files
     * @return array<int, UploadedFile>
     */
    private function normalizeUploadedFiles($files): array
    {
        if (empty($files)) {
            return [];
        }

        if ($files instanceof UploadedFile) {
            $files = [$files];
        }

        return array_values(array_filter($files, fn ($file) => $file instanceof UploadedFile));
    }

    /**
     * @param array<int, UploadedFile> $files
     */
    private function adaugaFisiere(FacturaFurnizor $factura, array $files): void
    {
        foreach ($files as $file) {
            $this->salveazaFisier($factura, $file);
        }
    }

    private function salveazaFisier(FacturaFurnizor $factura, UploadedFile $file): FacturaFurnizorFisier
    {
        $folder = $this->folderPentruFactura($factura);
        $filename = $this->genereazaNumeFisier($file, $folder);

        Storage::makeDirectory($folder);
        Storage::putFileAs($folder, $file, $filename);

        return $factura->fisiere()->create([
            'cale' => $folder . '/' . $filename,
            'nume_original' => $file->getClientOriginalName() ?: $filename,
        ]);
    }

    private function genereazaNumeFisier(UploadedFile $file, string $folder): string
    {
        $originalName = $file->getClientOriginalName() ?: 'factura';
        $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = Str::slug(Str::ascii($baseName) ?: 'factura', '_');

        if ($baseName === '') {
            $baseName = 'factura';
        }

        $baseName = substr($baseName, 0, 120);
        $filename = $baseName . '.' . $extension;
        $counter = 1;

        while (Storage::exists($folder . '/' . $filename)) {
            $filename = $baseName . '_' . $counter . '.' . $extension;
            $counter++;
        }

        return $filename;
    }

    private function folderPentruFactura(FacturaFurnizor $factura): string
    {
        return 'facturi-furnizori/facturi/' . $factura->id;
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

    public function ultimulContIban(Request $request)
    {
        $furnizor = trim($request->string('furnizor')->toString());

        if ($furnizor === '') {
            return response()->json(['message' => 'Furnizorul este necesar.'], 422);
        }

        $factura = FacturaFurnizor::query()
            ->where('denumire_furnizor', $furnizor)
            ->whereNotNull('cont_iban')
            ->orderByDesc('data_factura')
            ->orderByDesc('created_at')
            ->first();

        return response()->json([
            'cont_iban' => $factura?->cont_iban,
        ]);
    }

    protected function normalizeContIban(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : strtoupper($trimmed);
    }
}
