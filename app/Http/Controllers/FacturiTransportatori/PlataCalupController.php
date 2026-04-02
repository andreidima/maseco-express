<?php

namespace App\Http\Controllers\FacturiTransportatori;

use App\Http\Controllers\Controller;
use App\Http\Requests\FacturiTransportatori\AttachComenziRequest;
use App\Http\Requests\FacturiTransportatori\MoveComenziToCalupRequest;
use App\Http\Requests\FacturiTransportatori\PlataCalupRequest;
use App\Models\Comanda;
use App\Models\FacturiTransportatori\PlataCalup;
use App\Models\FacturiTransportatori\PlataCalupFisier;
use App\Services\FacturiTransportatori\PlataCalupService;
use App\Support\FacturiTransportatori\FacturiIndexFilterState;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

        $query = PlataCalup::query()->withCount(['comenzi', 'fisiere']);

        if ($filters['data_plata_de_la']) {
            $query->whereDate('data_plata', '>=', Carbon::parse($filters['data_plata_de_la']));
        }

        if ($filters['data_plata_pana']) {
            $query->whereDate('data_plata', '<=', Carbon::parse($filters['data_plata_pana']));
        }

        if ($filters['cauta']) {
            $query->where(function ($subQuery) use ($filters) {
                $subQuery->where('denumire_calup', 'like', '%' . $filters['cauta'] . '%')
                    ->orWhere('observatii', 'like', '%' . $filters['cauta'] . '%');
            });
        }

        $calupuri = $query
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('facturi_transportatori.calupuri.index', [
            'calupuri' => $calupuri,
            'filters' => $filters,
        ]);
    }

    public function store(PlataCalupRequest $request)
    {
        $payload = $request->validated();
        $comenzi = $payload['comenzi'] ?? [];
        unset($payload['comenzi']);

        $fisiereNoi = $this->normalizeUploadedFiles($request->file('fisiere_pdf'));
        unset($payload['fisiere_pdf']);

        $calup = DB::transaction(function () use ($payload, $comenzi) {
            $calup = PlataCalup::create($payload);

            if (! empty($comenzi)) {
                $this->plataCalupService->attachComenzi($calup, $comenzi);
            }

            return $calup;
        });

        if (! empty($fisiereNoi)) {
            $this->adaugaFisiere($calup, $fisiereNoi);
            $calup->load('fisiere');
        }

        return redirect()
            ->route('facturi-transportatori.index', FacturiIndexFilterState::get())
            ->with('status', 'Calupul a fost creat cu succes.');
    }

    public function show(PlataCalup $plataCalup)
    {
        $plataCalup->load([
            'fisiere',
            'comenzi' => fn ($query) => $query
                ->with([
                    'transportator:id,nume',
                    'transportatorMoneda:id,nume',
                    'facturiIncarcateDeTransportator:id,comanda_id,nume,este_factura',
                    'locuriOperareDescarcari:id',
                ])
                ->orderByRaw('data_scadenta_plata_transportator IS NULL')
                ->orderBy('data_scadenta_plata_transportator')
                ->orderBy('transportator_contract'),
        ]);

        $comenziCalup = $plataCalup->comenzi->values();

        $totaluriPeMoneda = $comenziCalup
            ->filter(fn (Comanda $comanda) => ! is_null($comanda->transportator_valoare_contract))
            ->groupBy(fn (Comanda $comanda) => $comanda->transportatorMoneda->nume ?? '-')
            ->map(fn ($comenziMoneda) => $comenziMoneda->sum('transportator_valoare_contract'))
            ->sortKeys();

        $facturiDisponibile = $this->queryComenziDisponibile()
            ->get();

        $calupuriDestinatie = PlataCalup::query()
            ->whereKeyNot($plataCalup->id)
            ->orderByDesc('data_plata')
            ->orderByDesc('created_at')
            ->get(['id', 'denumire_calup', 'data_plata']);

        return view('facturi_transportatori.calupuri.show', [
            'calup' => $plataCalup,
            'comenziCalup' => $comenziCalup,
            'totaluriPeMoneda' => $totaluriPeMoneda,
            'facturiDisponibile' => $facturiDisponibile,
            'calupuriDestinatie' => $calupuriDestinatie,
        ]);
    }

    public function update(PlataCalupRequest $request, PlataCalup $plataCalup)
    {
        $payload = $request->validated();
        $comenzi = $payload['comenzi'] ?? null;
        unset($payload['comenzi']);

        $fisiereNoi = $this->normalizeUploadedFiles($request->file('fisiere_pdf'));
        unset($payload['fisiere_pdf']);

        $plataCalup->update($payload);

        if (! empty($fisiereNoi)) {
            $this->adaugaFisiere($plataCalup, $fisiereNoi);
            $plataCalup->load('fisiere');
        }

        if (is_array($comenzi)) {
            $idsCurente = $plataCalup->comenzi()->pluck('comenzi.id')->toArray();
            $deLegat = array_diff($comenzi, $idsCurente);
            $deDezlegat = array_diff($idsCurente, $comenzi);

            if (! empty($deLegat)) {
                $this->plataCalupService->attachComenzi($plataCalup, $deLegat);
                $plataCalup->refresh();
            }

            if (! empty($deDezlegat)) {
                $comenziDetasate = Comanda::query()->whereIn('id', $deDezlegat)->get();

                foreach ($comenziDetasate as $comanda) {
                    $this->plataCalupService->detachComanda($plataCalup, $comanda);
                }

                $plataCalup->refresh();
            }
        }

        return redirect()
            ->route('facturi-transportatori.calupuri.show', $plataCalup)
            ->with('status', 'Calupul a fost actualizat.');
    }

    public function destroy(PlataCalup $plataCalup)
    {
        $plataCalup->load(['comenzi', 'fisiere']);

        foreach ($plataCalup->comenzi as $comanda) {
            $this->plataCalupService->detachComanda($plataCalup, $comanda);
        }

        foreach ($plataCalup->fisiere as $fisier) {
            if ($fisier->cale && Storage::exists($fisier->cale)) {
                Storage::delete($fisier->cale);
            }
        }

        $plataCalup->delete();

        return redirect()
            ->route('facturi-transportatori.calupuri.index')
            ->with('status', 'Calupul a fost sters.');
    }

    public function ataseazaComenzi(AttachComenziRequest $request, PlataCalup $plataCalup)
    {
        $comenzi = $request->validated('comenzi');
        $this->plataCalupService->attachComenzi($plataCalup, $comenzi);
        $plataCalup->refresh();

        return redirect()
            ->route('facturi-transportatori.calupuri.show', $plataCalup)
            ->with('status', 'Comenzile au fost atasate calupului.');
    }

    public function detaseazaComanda(PlataCalup $plataCalup, Comanda $comanda)
    {
        $this->plataCalupService->detachComanda($plataCalup, $comanda);

        return back()->with('status', 'Comanda a fost eliminata din calup.');
    }

    public function mutaComanda(MoveComenziToCalupRequest $request, PlataCalup $plataCalup, Comanda $comanda)
    {
        if (! $plataCalup->comenzi()->whereKey($comanda->id)->exists()) {
            abort(404);
        }

        $calupDestinatie = PlataCalup::query()->findOrFail($request->validated('plata_calup_id'));
        $this->plataCalupService->moveComenzi($calupDestinatie, [$comanda->id]);

        return redirect()
            ->route('facturi-transportatori.calupuri.show', $plataCalup)
            ->with('status', 'Comanda a fost mutata in alt calup.');
    }

    public function vizualizeazaFisier(PlataCalup $plataCalup, PlataCalupFisier $fisier)
    {
        if ($fisier->plata_calup_id !== $plataCalup->id) {
            abort(404);
        }

        if (! $fisier->isPreviewable()) {
            return back()->with('error', 'Fisierul nu poate fi deschis in browser.');
        }

        if (! Storage::exists($fisier->cale)) {
            return back()->with('error', 'Fisierul nu a putut fi gasit.');
        }

        $displayName = $fisier->nume_original ?: basename($fisier->cale);

        return Storage::response($fisier->cale, $displayName, [
            'Content-Disposition' => 'inline; filename="' . addcslashes($displayName, "\\\"") . '"',
            'Content-Type' => Storage::mimeType($fisier->cale) ?: 'application/pdf',
        ]);
    }

    public function descarcaFisier(PlataCalup $plataCalup, ?PlataCalupFisier $fisier = null)
    {
        if ($fisier && $fisier->plata_calup_id !== $plataCalup->id) {
            abort(404);
        }

        $fisierSelectat = $fisier ?: $plataCalup->fisiere()->orderBy('created_at')->first();

        if (! $fisierSelectat || ! Storage::exists($fisierSelectat->cale)) {
            return back()->with('error', 'Fisierul nu a putut fi descarcat.');
        }

        $downloadName = $fisierSelectat->nume_original ?: basename($fisierSelectat->cale);

        return Storage::download($fisierSelectat->cale, $downloadName);
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

    private function queryComenziDisponibile()
    {
        return Comanda::query()
            ->select([
                'id',
                'cheie_unica',
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
            ])
            ->whereDoesntHave('calupuriFacturiTransportatori')
            ->where(function ($query) {
                $query->whereNotNull('factura_transportator')
                    ->orWhereNotNull('data_factura_transportator')
                    ->orWhereNotNull('data_scadenta_plata_transportator')
                    ->orWhere('factura_transportator_incarcata', 1);
            })
            ->orderByRaw('data_scadenta_plata_transportator IS NULL')
            ->orderBy('data_scadenta_plata_transportator')
            ->orderBy('transportator_contract');
    }

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
        return 'facturi-transportatori/calupuri/' . $plataCalup->id;
    }
}
