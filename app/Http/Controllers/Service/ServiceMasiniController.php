<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreMasinaRequest;
use App\Http\Requests\Service\StoreMasinaServiceEntryRequest;
use App\Http\Requests\Service\UpdateMasinaRequest;
use App\Http\Requests\Service\UpdateMasinaServiceEntryRequest;
use App\Models\Service\GestiunePiesa;
use App\Models\Service\Masina;
use App\Models\Service\MasinaServiceEntry;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ServiceMasiniController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'masina_id' => $request->query('masina_id'),
            'numar_inmatriculare' => trim((string) $request->query('numar_inmatriculare', '')),
            'data_start' => $request->query('data_start'),
            'data_end' => $request->query('data_end'),
            'piesa' => trim((string) $request->query('piesa', '')),
            'cod' => trim((string) $request->query('cod', '')),
        ];

        $masini = $this->getMasiniList($filters['numar_inmatriculare']);
        $selectedMasina = $this->resolveSelectedMasina($masini, $filters['masina_id']);
        $editingEntry = $this->resolveEditingEntry($selectedMasina, (int) $request->query('entry_id'));

        $entries = $selectedMasina
            ? $this->getServiceEntries($selectedMasina->id, $filters)
            : $this->emptyPaginator();

        return view('service.masini.index', [
            'masini' => $masini,
            'selectedMasina' => $selectedMasina,
            'entries' => $entries,
            'filters' => $filters,
            'availablePieces' => $this->getAvailablePieces($editingEntry),
            'editingEntry' => $editingEntry,
        ]);
    }

    public function storeMasina(StoreMasinaRequest $request): RedirectResponse
    {
        $masina = Masina::query()->create($request->validated());

        return redirect()
            ->route('service-masini.index', ['masina_id' => $masina->id])
            ->with('status', 'Mașina a fost adăugată cu succes.');
    }

    public function storeEntry(StoreMasinaServiceEntryRequest $request, Masina $masina): RedirectResponse
    {
        $filters = $this->extractFilterState($request);

        try {
            DB::transaction(function () use ($request, $masina): void {
                $data = $request->validated();
                $user = $request->user();

                $entry = new MasinaServiceEntry();
                $entry->masina_id = $masina->id;
                $entry->tip = $data['tip'];
                $entry->data_montaj = $data['data_montaj'];
                $entry->nume_mecanic = $data['nume_mecanic'];
                $entry->observatii = $data['observatii'] ?? null;
                $entry->nume_utilizator = $user?->name;
                $entry->user_id = $user?->id;

                if ($data['tip'] === 'piesa') {
                    $piesa = $request->piece() ?: GestiunePiesa::query()->find($data['gestiune_piesa_id']);

                    if (! $piesa) {
                        throw new \RuntimeException('Piesa selectată nu a fost găsită.');
                    }

                    $cantitate = (float) $data['cantitate'];

                    $entry->gestiune_piesa_id = $piesa->id;
                    $entry->denumire_piesa = $piesa->denumire;
                    $entry->cod_piesa = $piesa->cod;
                    $entry->cantitate = $cantitate;

                    $updated = GestiunePiesa::query()
                        ->whereKey($piesa->id)
                        ->where('nr_bucati', '>=', $cantitate)
                        ->decrement('nr_bucati', $cantitate);

                    if ($updated === 0) {
                        throw new \RuntimeException('Stocul piesei s-a modificat între timp.');
                    }
                } else {
                    $entry->denumire_interventie = $data['denumire_interventie'];
                }

                $entry->save();
            });
        } catch (Throwable $exception) {
            Log::error('Unable to save service entry', [
                'exception' => $exception,
                'masina_id' => $masina->id,
            ]);

            return redirect()
                ->route('service-masini.index', ['masina_id' => $masina->id] + $filters)
                ->withErrors(['general' => 'Nu am putut salva intervenția. Încearcă din nou.']);
        }

        return redirect()
            ->route('service-masini.index', ['masina_id' => $masina->id] + $filters)
            ->with('status', 'Intervenția a fost salvată.');
    }

    public function updateMasina(UpdateMasinaRequest $request, Masina $masina): RedirectResponse
    {
        $masina->update($request->validated());

        return redirect()
            ->route('service-masini.index', ['masina_id' => $masina->id])
            ->with('status', 'Mașina a fost actualizată.');
    }

    public function destroyMasina(Request $request, Masina $masina): RedirectResponse
    {
        $filters = $this->extractFilterState($request);

        try {
            DB::transaction(function () use ($masina): void {
                $masina->serviceEntries()
                    ->where('tip', 'piesa')
                    ->chunkById(100, function ($entries): void {
                        foreach ($entries as $entry) {
                            if ($entry->gestiune_piesa_id && $entry->cantitate) {
                                GestiunePiesa::query()
                                    ->whereKey($entry->gestiune_piesa_id)
                                    ->increment('nr_bucati', (float) $entry->cantitate);
                            }
                        }
                    });

                $masina->delete();
            });
        } catch (Throwable $exception) {
            Log::error('Unable to delete masina', [
                'exception' => $exception,
                'masina_id' => $masina->id,
            ]);

            return redirect()
                ->route('service-masini.index', ['masina_id' => $masina->id] + $filters)
                ->withErrors(['general' => 'Nu am putut șterge mașina. Încearcă din nou.']);
        }

        return redirect()
            ->route('service-masini.index', $filters)
            ->with('status', 'Mașina a fost ștearsă.');
    }

    public function updateEntry(UpdateMasinaServiceEntryRequest $request, Masina $masina, MasinaServiceEntry $entry): RedirectResponse
    {
        $filters = $this->extractFilterState($request);

        if ($entry->masina_id !== $masina->id) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($request, $entry): void {
                if ($entry->tip === 'piesa' && $entry->gestiune_piesa_id && $entry->cantitate) {
                    GestiunePiesa::query()
                        ->whereKey($entry->gestiune_piesa_id)
                        ->increment('nr_bucati', (float) $entry->cantitate);
                }

                $data = $request->validated();

                $entry->tip = $data['tip'];
                $entry->data_montaj = $data['data_montaj'];
                $entry->nume_mecanic = $data['nume_mecanic'];
                $entry->observatii = $data['observatii'] ?? null;

                if ($data['tip'] === 'piesa') {
                    $piesa = $request->piece() ?: GestiunePiesa::query()->find($data['gestiune_piesa_id']);

                    if (! $piesa) {
                        throw new \RuntimeException('Piesa selectată nu a fost găsită.');
                    }

                    $cantitate = (float) $data['cantitate'];

                    $entry->gestiune_piesa_id = $piesa->id;
                    $entry->denumire_piesa = $piesa->denumire;
                    $entry->cod_piesa = $piesa->cod;
                    $entry->denumire_interventie = null;
                    $entry->cantitate = $cantitate;

                    $updated = GestiunePiesa::query()
                        ->whereKey($piesa->id)
                        ->where('nr_bucati', '>=', $cantitate)
                        ->decrement('nr_bucati', $cantitate);

                    if ($updated === 0) {
                        throw new \RuntimeException('Stocul piesei s-a modificat între timp.');
                    }
                } else {
                    $entry->gestiune_piesa_id = null;
                    $entry->denumire_piesa = null;
                    $entry->cod_piesa = null;
                    $entry->cantitate = null;
                    $entry->denumire_interventie = $data['denumire_interventie'];
                }

                $entry->save();
            });
        } catch (Throwable $exception) {
            Log::error('Unable to update service entry', [
                'exception' => $exception,
                'entry_id' => $entry->id,
                'masina_id' => $masina->id,
            ]);

            return redirect()
                ->route('service-masini.index', ['masina_id' => $masina->id] + $filters + ['entry_id' => $entry->id])
                ->withErrors(['general' => 'Nu am putut actualiza intervenția. Încearcă din nou.'])
                ->withInput();
        }

        return redirect()
            ->route('service-masini.index', ['masina_id' => $masina->id] + $filters)
            ->with('status', 'Intervenția a fost actualizată.');
    }

    public function destroyEntry(Request $request, Masina $masina, MasinaServiceEntry $entry): RedirectResponse
    {
        $filters = $this->extractFilterState($request);

        if ($entry->masina_id !== $masina->id) {
            abort(404);
        }

        try {
            DB::transaction(function () use ($entry): void {
                if ($entry->tip === 'piesa' && $entry->gestiune_piesa_id && $entry->cantitate) {
                    GestiunePiesa::query()
                        ->whereKey($entry->gestiune_piesa_id)
                        ->increment('nr_bucati', (float) $entry->cantitate);
                }

                $entry->delete();
            });
        } catch (Throwable $exception) {
            Log::error('Unable to delete service entry', [
                'exception' => $exception,
                'entry_id' => $entry->id,
                'masina_id' => $masina->id,
            ]);

            return redirect()
                ->route('service-masini.index', ['masina_id' => $masina->id] + $filters)
                ->withErrors(['general' => 'Nu am putut șterge intervenția. Încearcă din nou.']);
        }

        return redirect()
            ->route('service-masini.index', ['masina_id' => $masina->id] + $filters)
            ->with('status', 'Intervenția a fost ștearsă.');
    }

    public function export(Request $request)
    {
        $filters = [
            'masina_id' => $request->query('masina_id'),
            'numar_inmatriculare' => trim((string) $request->query('numar_inmatriculare', '')),
            'data_start' => $request->query('data_start'),
            'data_end' => $request->query('data_end'),
            'piesa' => trim((string) $request->query('piesa', '')),
            'cod' => trim((string) $request->query('cod', '')),
        ];

        $masini = $this->getMasiniList($filters['numar_inmatriculare']);
        $selectedMasina = $this->resolveSelectedMasina($masini, $filters['masina_id']);

        if (! $selectedMasina) {
            abort(404, 'Mașina selectată nu există.');
        }

        $entries = $this->getServiceEntriesCollection($selectedMasina->id, $filters);

        $pdf = Pdf::loadView('service.masini.export', [
            'masina' => $selectedMasina,
            'entries' => $entries,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');

        $filename = 'service-masini-' . Str::slug($selectedMasina->numar_inmatriculare ?: $selectedMasina->denumire) . '.pdf';

        return $pdf->download($filename);
    }

    protected function getMasiniList(string $numarInmatriculare = ''): EloquentCollection
    {
        $query = Masina::query()->orderBy('denumire');

        if ($numarInmatriculare !== '') {
            $query->where(function ($builder) use ($numarInmatriculare): void {
                $builder->where('numar_inmatriculare', 'like', '%' . $numarInmatriculare . '%')
                    ->orWhere('denumire', 'like', '%' . $numarInmatriculare . '%');
            });
        }

        return $query->get();
    }

    protected function resolveSelectedMasina(EloquentCollection $masini, $masinaId): ?Masina
    {
        if ($masini->isEmpty()) {
            return null;
        }

        if ($masinaId) {
            $selected = $masini->firstWhere('id', (int) $masinaId);

            if ($selected) {
                return $selected;
            }
        }

        return $masini->first();
    }

    protected function getServiceEntries(int $masinaId, array $filters): LengthAwarePaginator
    {
        return $this->baseEntriesQuery($masinaId, $filters)
            ->with(['user'])
            ->orderByDesc('data_montaj')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();
    }

    protected function getServiceEntriesCollection(int $masinaId, array $filters): Collection
    {
        return $this->baseEntriesQuery($masinaId, $filters)
            ->with(['user'])
            ->orderBy('data_montaj')
            ->orderBy('id')
            ->get();
    }

    protected function baseEntriesQuery(int $masinaId, array $filters)
    {
        $query = MasinaServiceEntry::query()->where('masina_id', $masinaId);

        if ($filters['data_start']) {
            try {
                $query->whereDate('data_montaj', '>=', $filters['data_start']);
            } catch (Throwable $exception) {
                Log::warning('Invalid start date filter for service entries', ['exception' => $exception]);
            }
        }

        if ($filters['data_end']) {
            try {
                $query->whereDate('data_montaj', '<=', $filters['data_end']);
            } catch (Throwable $exception) {
                Log::warning('Invalid end date filter for service entries', ['exception' => $exception]);
            }
        }

        if ($filters['piesa'] !== '') {
            $query->where(function ($builder) use ($filters): void {
                $builder->where('denumire_piesa', 'like', '%' . $filters['piesa'] . '%')
                    ->orWhere('denumire_interventie', 'like', '%' . $filters['piesa'] . '%');
            });
        }

        if ($filters['cod'] !== '') {
            $query->where('cod_piesa', 'like', '%' . $filters['cod'] . '%');
        }

        return $query;
    }

    protected function getAvailablePieces(?MasinaServiceEntry $editingEntry = null): Collection
    {
        try {
            $pieces = GestiunePiesa::query()
                ->where('nr_bucati', '>', 0)
                ->orderBy('denumire')
                ->get(['id', 'denumire', 'cod', 'nr_bucati']);

            if ($editingEntry && $editingEntry->tip === 'piesa' && $editingEntry->gestiune_piesa_id) {
                if (! $pieces->firstWhere('id', $editingEntry->gestiune_piesa_id)) {
                    $editingPiece = GestiunePiesa::query()
                        ->find($editingEntry->gestiune_piesa_id, ['id', 'denumire', 'cod', 'nr_bucati']);

                    if ($editingPiece) {
                        $pieces = $pieces
                            ->push($editingPiece)
                            ->sortBy('denumire')
                            ->values();
                    }
                }
            }

            return $pieces;
        } catch (Throwable $exception) {
            Log::warning('Unable to load available pieces', ['exception' => $exception]);

            return collect();
        }
    }

    protected function resolveEditingEntry(?Masina $masina, int $entryId): ?MasinaServiceEntry
    {
        if (! $masina || $entryId <= 0) {
            return null;
        }

        return $masina->serviceEntries()->whereKey($entryId)->first();
    }

    protected function extractFilterState(Request $request): array
    {
        $keys = ['numar_inmatriculare', 'data_start', 'data_end', 'piesa', 'cod'];

        $data = [];
        foreach ($keys as $key) {
            $value = $request->input($key);
            if ($value !== null && $value !== '') {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    protected function emptyPaginator(): LengthAwarePaginator
    {
        return new LengthAwarePaginator(
            [],
            0,
            25,
            1,
            [
                'path' => LengthAwarePaginator::resolveCurrentPath(),
                'query' => request()->query(),
            ]
        );
    }
}
