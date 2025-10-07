<?php

namespace App\Http\Controllers\FacturiFurnizori;

use App\Http\Controllers\Controller;
use App\Http\Requests\FacturiFurnizori\AttachFacturiRequest;
use App\Http\Requests\FacturiFurnizori\PlataCalupRequest;
use App\Models\FacturiFurnizori\FacturaFurnizor;
use App\Models\FacturiFurnizori\PlataCalup;
use App\Services\FacturiFurnizori\PlataCalupService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

class PlataCalupController extends Controller
{
    public function __construct(private PlataCalupService $plataCalupService)
    {
    }

    public function index(Request $request)
    {
        $filters = [
            'status' => $request->string('status')->toString() ?: null,
            'data_plata_de_la' => $request->string('data_plata_de_la')->toString() ?: null,
            'data_plata_pana' => $request->string('data_plata_pana')->toString() ?: null,
            'cauta' => $request->string('cauta')->toString() ?: null,
        ];

        $query = PlataCalup::query()->withCount('facturi');

        if ($filters['status']) {
            $query->where('status', $filters['status']);
        }

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
            'statusOptions' => $this->statusOptions(),
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
            ->where('status', FacturaFurnizor::STATUS_NEPLATITA)
            ->orderBy('data_scadenta')
            ->orderBy('denumire_furnizor')
            ->get();

        return view('facturiFurnizori.calupuri.create', [
            'statusOptions' => $this->statusOptions(),
            'facturiDisponibile' => $facturiDisponibile,
            'facturiSelectate' => $facturiSelectate,
        ]);
    }

    public function store(PlataCalupRequest $request)
    {
        $payload = $request->validated();
        $facturi = $payload['facturi'] ?? [];
        unset($payload['facturi']);

        $payload['status'] = PlataCalup::STATUS_DESCHIS;

        if ($request->hasFile('fisier_pdf')) {
            $payload['fisier_pdf'] = $this->uploadFisierPdf($request->file('fisier_pdf'));
        } else {
            unset($payload['fisier_pdf']);
        }

        $calup = PlataCalup::create($payload);

        if (!empty($facturi)) {
            $this->plataCalupService->attachFacturi($calup, $facturi);
            $calup->refresh();
        }

        return redirect()
            ->route('facturi-furnizori.facturi.index', array_filter([
                'status' => $request->input('status', FacturaFurnizor::STATUS_NEPLATITA),
            ], fn ($value) => !is_null($value) && $value !== ''))
            ->with('status', 'Calupul a fost creat cu succes.');
    }

    public function show(PlataCalup $plataCalup)
    {
        $plataCalup->load(['facturi' => fn ($query) => $query->orderBy('data_scadenta')]);

        $facturiDisponibile = FacturaFurnizor::query()
            ->where('status', FacturaFurnizor::STATUS_NEPLATITA)
            ->orderBy('data_scadenta')
            ->orderBy('denumire_furnizor')
            ->get();

        return view('facturiFurnizori.calupuri.show', [
            'calup' => $plataCalup,
            'facturiDisponibile' => $facturiDisponibile,
            'statusOptions' => $this->statusOptions(),
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

        if ($request->hasFile('fisier_pdf')) {
            if ($plataCalup->fisier_pdf) {
                Storage::delete($plataCalup->fisier_pdf);
            }
            $payload['fisier_pdf'] = $this->uploadFisierPdf($request->file('fisier_pdf'));
        } else {
            unset($payload['fisier_pdf']);
        }

        $statusCurent = $plataCalup->status;
        $statusNou = $payload['status'] ?? $statusCurent;

        if ($statusCurent !== PlataCalup::STATUS_PLATIT && $statusNou === PlataCalup::STATUS_PLATIT) {
            $this->plataCalupService->markAsPaid($plataCalup, isset($payload['data_plata']) ? Carbon::parse($payload['data_plata']) : null);
            $plataCalup->refresh();
            unset($payload['status'], $payload['data_plata']);
        } elseif ($statusCurent === PlataCalup::STATUS_PLATIT && $statusNou === PlataCalup::STATUS_DESCHIS) {
            $this->plataCalupService->reopen($plataCalup);
            $plataCalup->refresh();
            unset($payload['status']);
        } else {
            $plataCalup->update($payload);
        }

        if (is_array($facturi)) {
            $idsCurente = $plataCalup->facturi()->pluck('ff_facturi.id')->toArray();
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
        if ($plataCalup->status === PlataCalup::STATUS_PLATIT) {
            return back()->with('error', 'Un calup platit nu poate fi sters.');
        }

        $plataCalup->load('facturi');

        foreach ($plataCalup->facturi as $factura) {
            $this->plataCalupService->detachFactura($plataCalup, $factura);
        }

        if ($plataCalup->fisier_pdf) {
            Storage::delete($plataCalup->fisier_pdf);
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

    public function marcheazaPlatit(Request $request, PlataCalup $plataCalup)
    {
        $dataPlata = $request->string('data_plata')->toString();
        $this->plataCalupService->markAsPaid($plataCalup, $dataPlata ? Carbon::parse($dataPlata) : null);

        return redirect()
            ->route('facturi-furnizori.plati-calupuri.show', $plataCalup)
            ->with('status', 'Calupul a fost marcat ca platit.');
    }

    public function redeschide(PlataCalup $plataCalup)
    {
        $this->plataCalupService->reopen($plataCalup);

        return redirect()
            ->route('facturi-furnizori.plati-calupuri.show', $plataCalup)
            ->with('status', 'Calupul a fost redeschis.');
    }

    public function descarcaFisier(PlataCalup $plataCalup)
    {
        if (!$plataCalup->fisier_pdf || !Storage::exists($plataCalup->fisier_pdf)) {
            return back()->with('error', 'Fisierul nu a putut fi descarcat.');
        }

        return Storage::download($plataCalup->fisier_pdf, basename($plataCalup->fisier_pdf));
    }

    private function uploadFisierPdf(UploadedFile $file): string
    {
        $folder = 'facturi-furnizori/calupuri';
        $filename = $file->getClientOriginalName() ?: ('calup_' . uniqid() . '.pdf');
        $path = $folder . '/' . $filename;

        while (Storage::exists($path)) {
            $name = pathinfo($filename, PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension() ?: 'pdf';
            $filename = $name . '_' . uniqid() . '.' . $extension;
            $path = $folder . '/' . $filename;
        }

        Storage::putFileAs($folder, $file, $filename);

        return $path;
    }

    private function statusOptions(): array
    {
        return [
            PlataCalup::STATUS_DESCHIS => 'Deschis',
            PlataCalup::STATUS_PLATIT => 'Plătit',
            PlataCalup::STATUS_ANULAT => 'Anulat',
        ];
    }
}

