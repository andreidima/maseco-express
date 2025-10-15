<?php

namespace App\Http\Controllers\FacturiFurnizori;

use App\Http\Controllers\Controller;
use App\Http\Requests\FacturiFurnizori\FacturaFurnizorRequest;
use App\Models\FacturiFurnizori\FacturaFurnizor;
use App\Models\FacturiFurnizori\FacturaFurnizorFisier;
use App\Support\FacturiFurnizori\FacturiIndexFilterState;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

            if (!empty($produse)) {
                $factura->piese()->createMany($produse);
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

        return view('facturiFurnizori.facturi.show', [
            'factura' => $factura,
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

            $factura->piese()->delete();

            if (!empty($produse)) {
                $factura->piese()->createMany($produse);
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
                $nrBucati = isset($row['nr_bucati']) && $row['nr_bucati'] !== ''
                    ? round((float) $row['nr_bucati'], 2)
                    : null;
                $pret = isset($row['pret']) && $row['pret'] !== ''
                    ? round((float) $row['pret'], 2)
                    : null;

                if ($denumire === '' && $cod === '' && $nrBucati === null && $pret === null) {
                    return null;
                }

                if ($denumire === '') {
                    return null;
                }

                return [
                    'denumire' => $denumire,
                    'cod' => $cod !== '' ? $cod : null,
                    'nr_bucati' => $nrBucati,
                    'pret' => $pret,
                ];
            })
            ->filter()
            ->values()
            ->all();

        return $produse;
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
