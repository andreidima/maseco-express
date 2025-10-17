<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreServiceSheetRequest;
use App\Http\Requests\Service\UpdateServiceSheetRequest;
use App\Models\Service\Masina;
use App\Models\Service\ServiceSheet;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ServiceSheetController extends Controller
{
    public function create(Masina $masina): View
    {
        return view('service.masini.service-sheet.create', [
            'masina' => $masina,
        ]);
    }

    public function store(StoreServiceSheetRequest $request, Masina $masina): RedirectResponse
    {
        $sheet = DB::transaction(function () use ($request, $masina) {
            $data = $request->validated();

            /** @var \App\Models\Service\ServiceSheet $sheet */
            $sheet = $masina->serviceSheets()->create([
                'km_bord' => (int) $data['km_bord'],
                'data_service' => $data['data_service'],
            ]);

            $sheet->items()->createMany(
                collect($data['items'])
                    ->map(function (array $item, int $index) {
                        return [
                            'position' => $index + 1,
                            'descriere' => $item['descriere'],
                        ];
                    })
                    ->all()
            );

            return $sheet->fresh(['masina', 'items']);
        });

        return redirect()
            ->route('service-masini.index', [
                'masina_id' => $masina->id,
                'view' => 'service-sheets',
            ])
            ->with('status', 'Foaia de service a fost salvată.');
    }

    public function edit(Masina $masina, ServiceSheet $sheet): View
    {
        $this->ensureSheetBelongsToMasina($masina, $sheet);

        return view('service.masini.service-sheet.edit', [
            'masina' => $masina,
            'sheet' => $sheet->loadMissing('items'),
        ]);
    }

    public function update(UpdateServiceSheetRequest $request, Masina $masina, ServiceSheet $sheet): RedirectResponse
    {
        $this->ensureSheetBelongsToMasina($masina, $sheet);

        DB::transaction(function () use ($request, $sheet): void {
            $data = $request->validated();

            $sheet->update([
                'km_bord' => (int) $data['km_bord'],
                'data_service' => $data['data_service'],
            ]);

            $sheet->items()->delete();
            $sheet->items()->createMany(
                collect($data['items'])
                    ->map(function (array $item, int $index) {
                        return [
                            'position' => $index + 1,
                            'descriere' => $item['descriere'],
                        ];
                    })
                    ->all()
            );
        });

        return redirect()
            ->route('service-masini.index', [
                'masina_id' => $masina->id,
                'view' => 'service-sheets',
            ])
            ->with('status', 'Foaia de service a fost actualizată.');
    }

    public function destroy(Masina $masina, ServiceSheet $sheet): RedirectResponse
    {
        $this->ensureSheetBelongsToMasina($masina, $sheet);

        $sheet->delete();

        return redirect()
            ->route('service-masini.index', [
                'masina_id' => $masina->id,
                'view' => 'service-sheets',
            ])
            ->with('status', 'Foaia de service a fost ștearsă.');
    }

    public function download(Masina $masina, ServiceSheet $sheet): BinaryFileResponse
    {
        $this->ensureSheetBelongsToMasina($masina, $sheet);

        return $this->downloadSheet($sheet->loadMissing('masina', 'items'));
    }

    protected function downloadSheet(ServiceSheet $sheet): BinaryFileResponse
    {
        $sheet->loadMissing('masina', 'items');

        $items = $sheet->items
            ->sortBy('position')
            ->values()
            ->map(function ($item, int $index) {
                return [
                    'index' => $index + 1,
                    'descriere' => $item->descriere,
                ];
            });

        $pdf = Pdf::loadView('pdf.service-sheet', [
            'masina' => $sheet->masina,
            'km_bord' => (int) $sheet->km_bord,
            'data_service' => $sheet->data_service,
            'items' => $items,
        ]);

        $identifier = $sheet->masina->numar_inmatriculare ?: $sheet->masina->denumire ?: 'masina';
        $filename = 'foaie-service-' . Str::slug($identifier) . '-' . $sheet->id . '.pdf';

        return $pdf->download($filename);
    }

    protected function ensureSheetBelongsToMasina(Masina $masina, ServiceSheet $sheet): void
    {
        if ($sheet->masina_id !== $masina->id) {
            abort(404);
        }
    }
}
