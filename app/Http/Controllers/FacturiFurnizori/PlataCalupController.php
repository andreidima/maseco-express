<?php

namespace App\Http\Controllers\FacturiFurnizori;

use App\Http\Controllers\Controller;
use App\Http\Requests\FacturiFurnizori\AttachFacturiRequest;
use App\Http\Requests\FacturiFurnizori\PlataCalupRequest;
use App\Models\FacturiFurnizori\FacturaFurnizor;
use App\Models\FacturiFurnizori\PlataCalup;
use App\Models\FacturiFurnizori\PlataCalupFisier;
use App\Services\FacturiFurnizori\PlataCalupService;
use App\Support\FacturiFurnizori\FacturiIndexFilterState;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PlataCalupController extends Controller
{
    public function __construct(private PlataCalupService $plataCalupService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'data_plata_de_la' => $request->string('data_plata_de_la')->toString() ?: null,
            'data_plata_pana' => $request->string('data_plata_pana')->toString() ?: null,
            'cauta' => $request->string('cauta')->toString() ?: null,
        ];

        $query = PlataCalup::query()->withCount('facturi');

        if ($filters['data_plata_de_la']) {
            $query->whereDate('data_plata', '>=', Carbon::parse($filters['data_plata_de_la']));
        }

        if ($filters['data_plata_pana']) {
            $query->whereDate('data_plata', '<=', Carbon::parse($filters['data_plata_pana']));
        }

        if ($filters['cauta']) {
            $query->where(function ($sub) use ($filters) {
                $sub->where('denumire_calup', 'like', '%' . $filters['cauta'] . '%')
                    ->orWhere('observatii', 'like', '%' . $filters['cauta'] . '%');
            });
        }

        $calupuri = $query
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('facturiFurnizori.calupuri.index', [
            'calupuri' => $calupuri,
            'filters' => $filters,
        ]);
    }

    public function create(Request $request)
    {
        $facturiSelectate = collect($request->input('facturi', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        $facturiDisponibile = FacturaFurnizor::query()
            ->whereDoesntHave('calupuri')
            ->orderBy('data_scadenta')
            ->orderBy('denumire_furnizor')
            ->get();

        return view('facturiFurnizori.calupuri.create', [
            'facturiDisponibile' => $facturiDisponibile,
            'facturiSelectate' => $facturiSelectate,
        ]);
    }

    public function store(PlataCalupRequest $request)
    {
        $payload = $request->validated();
        $facturi = $payload['facturi'] ?? [];
        unset($payload['facturi']);

        $fisiereNoi = $this->normalizeUploadedFiles($request->file('fisiere_pdf'));
        unset($payload['fisiere_pdf']);

        $calup = PlataCalup::create($payload);

        if (!empty($fisiereNoi)) {
            $this->adaugaFisiere($calup, $fisiereNoi);
            $calup->load('fisiere');
        }

        if (!empty($facturi)) {
            $this->plataCalupService->attachFacturi($calup, $facturi);
            $calup->refresh();
        }

        return redirect()
            ->route('facturi-furnizori.facturi.index', FacturiIndexFilterState::get())
            ->with('status', 'Calupul a fost creat cu succes.');
    }

    public function show(PlataCalup $plataCalup)
    {
        $plataCalup->load(['facturi' => fn ($query) => $query->orderByRaw('data_scadenta IS NULL')->orderBy('data_scadenta')]);

        $facturiCalup = $plataCalup->facturi
            ->sortBy(fn (FacturaFurnizor $factura) => $factura->data_scadenta?->timestamp ?? PHP_INT_MAX)
            ->values();

        $facturiDisponibile = FacturaFurnizor::query()
            ->whereDoesntHave('calupuri')
            ->orderByRaw('data_scadenta IS NULL')
            ->orderBy('data_scadenta')
            ->orderBy('denumire_furnizor')
            ->get();

        return view('facturiFurnizori.calupuri.show', [
            'calup' => $plataCalup,
            'facturiCalup' => $facturiCalup,
            'facturiDisponibile' => $facturiDisponibile,
        ]);
    }

    public function edit(PlataCalup $plataCalup)
    {
        return redirect()->route('facturi-furnizori.plati-calupuri.show', $plataCalup);
    }

    public function update(PlataCalupRequest $request, PlataCalup $plataCalup)
    {
        $payload = $request->validated();
        $facturi = $payload['facturi'] ?? null;
        unset($payload['facturi']);

        $fisiereNoi = $this->normalizeUploadedFiles($request->file('fisiere_pdf'));
        unset($payload['fisiere_pdf']);

        $plataCalup->update($payload);

        if (!empty($fisiereNoi)) {
            $this->adaugaFisiere($plataCalup, $fisiereNoi);
            $plataCalup->load('fisiere');
        }

        if (is_array($facturi)) {
            $idsCurente = $plataCalup->facturi()->pluck('service_ff_facturi.id')->toArray();
            $deLegat = array_diff($facturi, $idsCurente);
            $deDezlegat = array_diff($idsCurente, $facturi);

            if (!empty($deLegat)) {
                $this->plataCalupService->attachFacturi($plataCalup, $deLegat);
                $plataCalup->refresh();
            }

            if (!empty($deDezlegat)) {
                $facturiDetasate = FacturaFurnizor::query()->whereIn('id', $deDezlegat)->get();
                foreach ($facturiDetasate as $factura) {
                    $this->plataCalupService->detachFactura($plataCalup, $factura);
                }
                $plataCalup->refresh();
            }
        }

        return redirect()
            ->route('facturi-furnizori.plati-calupuri.show', $plataCalup)
            ->with('status', 'Calupul a fost actualizat.');
    }

    public function destroy(PlataCalup $plataCalup)
    {
        $plataCalup->load(['facturi', 'fisiere']);

        foreach ($plataCalup->facturi as $factura) {
            $this->plataCalupService->detachFactura($plataCalup, $factura);
        }

        foreach ($plataCalup->fisiere as $fisier) {
            if ($fisier->cale && Storage::exists($fisier->cale)) {
                Storage::delete($fisier->cale);
            }
        }

        $plataCalup->delete();

        return redirect()
            ->route('facturi-furnizori.plati-calupuri.index')
            ->with('status', 'Calupul a fost sters.');
    }

    public function ataseazaFacturi(AttachFacturiRequest $request, PlataCalup $plataCalup)
    {
        $facturi = $request->validated('facturi');
        $this->plataCalupService->attachFacturi($plataCalup, $facturi);
        $plataCalup->refresh();

        return redirect()
            ->route('facturi-furnizori.plati-calupuri.show', $plataCalup)
            ->with('status', 'Facturile au fost atasate calupului.');
    }

    public function detaseazaFactura(PlataCalup $plataCalup, FacturaFurnizor $factura)
    {
        $this->plataCalupService->detachFactura($plataCalup, $factura);

        return back()->with('status', 'Factura a fost eliminata din calup.');
    }

    public function vizualizeazaFisier(PlataCalup $plataCalup, PlataCalupFisier $fisier)
    {
        if ($fisier->plata_calup_id !== $plataCalup->id) {
            abort(404);
        }

        if (!$fisier->isPreviewable()) {
            return back()->with('error', 'Fisierul nu poate fi deschis în browser.');
        }

        if (!Storage::exists($fisier->cale)) {
            return back()->with('error', 'Fisierul nu a putut fi gasit.');
        }

        $displayName = $fisier->nume_original ?: basename($fisier->cale);
        $safeDisplayName = addcslashes($displayName, "\\\"");

        $headers = [
            'Content-Disposition' => 'inline; filename="' . $safeDisplayName . '"',
        ];

        $mimeType = Storage::mimeType($fisier->cale);

        if ($mimeType) {
            $headers['Content-Type'] = $mimeType;
        }

        return Storage::response($fisier->cale, $displayName, $headers);
    }

    public function descarcaFisier(PlataCalup $plataCalup, ?PlataCalupFisier $fisier = null)
    {
        if ($fisier && $fisier->plata_calup_id !== $plataCalup->id) {
            abort(404);
        }

        $fisierSelectat = $fisier;

        if (!$fisierSelectat) {
            $fisierSelectat = $plataCalup->fisiere()->orderBy('created_at')->first();
        }

        if (!$fisierSelectat || !Storage::exists($fisierSelectat->cale)) {
            return back()->with('error', 'Fisierul nu a putut fi descarcat.');
        }

        $downloadName = $fisierSelectat->nume_original ?: basename($fisierSelectat->cale);

        return Storage::download($fisierSelectat->cale, $downloadName);
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
    private function adaugaFisiere(PlataCalup $plataCalup, array $files): void
    {
        foreach ($files as $file) {
            $this->salveazaFisier($plataCalup, $file);
        }
    }

    private function salveazaFisier(PlataCalup $plataCalup, UploadedFile $file): PlataCalupFisier
    {
        $folder = $this->folderPentruCalup($plataCalup);
        $filename = $this->genereazaNumeFisier($file, $folder);

        Storage::makeDirectory($folder);
        Storage::putFileAs($folder, $file, $filename);

        return $plataCalup->fisiere()->create([
            'cale' => $folder . '/' . $filename,
            'nume_original' => $file->getClientOriginalName() ?: $filename,
        ]);
    }

    private function genereazaNumeFisier(UploadedFile $file, string $folder): string
    {
        $originalName = $file->getClientOriginalName() ?: 'calup';
        $extension = strtolower($file->getClientOriginalExtension() ?: 'pdf');
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $baseName = Str::slug(Str::ascii($baseName) ?: 'calup', '_');

        if ($baseName === '') {
            $baseName = 'calup';
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

    private function folderPentruCalup(PlataCalup $plataCalup): string
    {
        return 'facturi-furnizori/calupuri/' . $plataCalup->id;
    }

    public function stergeFisier(PlataCalup $plataCalup, PlataCalupFisier $fisier)
    {
        if ($fisier->plata_calup_id !== $plataCalup->id) {
            abort(404);
        }

        if ($fisier->cale && Storage::exists($fisier->cale)) {
            Storage::delete($fisier->cale);
        }

        $fisier->delete();

        return back()->with('status', 'Fisierul a fost sters.');
    }
}

