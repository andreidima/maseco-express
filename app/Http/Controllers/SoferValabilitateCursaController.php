<?php

namespace App\Http\Controllers;

use App\Http\Requests\SoferValabilitateCursaRequest;
use App\Models\Tara;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SoferValabilitateCursaController extends Controller
{
    public function show(Request $request, Valabilitate $valabilitate): View
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);

        $valabilitate->load([
            'curse' => function ($query) {
                $query->orderBy('nr_ordine')->orderBy('data_cursa');
            },
            'curse.incarcareTara',
            'curse.descarcareTara',
        ]);

        return view('sofer.valabilitati.show', [
            'valabilitate' => $valabilitate,
            'curse' => $valabilitate->curse,
        ]);
    }

    public function reorder(Request $request, Valabilitate $valabilitate): JsonResponse
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);

        $validated = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'min:1'],
        ]);

        $order = array_values(array_unique(array_map('intval', $validated['order'] ?? [])));

        if ($order === []) {
            return response()->json([
                'message' => 'Ordinea curselor a fost actualizată.',
            ]);
        }

        $allowedIds = $valabilitate->curse()
            ->whereIn('id', $order)
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        if (count($allowedIds) !== count($order)) {
            return response()->json([
                'message' => 'Ordinea furnizată nu este validă.',
            ], 422);
        }

        $timestamp = now();

        DB::transaction(function () use ($order, $valabilitate, $timestamp): void {
            foreach ($order as $index => $id) {
                DB::table('valabilitati_curse')
                    ->where('id', $id)
                    ->where('valabilitate_id', $valabilitate->getKey())
                    ->update([
                        'nr_ordine' => $index + 1,
                        'updated_at' => $timestamp,
                    ]);
            }
        });

        return response()->json([
            'message' => 'Ordinea curselor a fost actualizată.',
        ]);
    }

    public function create(Request $request, Valabilitate $valabilitate): View
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);

        $tari = Tara::query()
            ->orderBy('nume')
            ->get(['id', 'nume']);

        $requiresTime = ! $valabilitate->curse()->exists();

        return view('sofer.valabilitati.curse.create', [
            'valabilitate' => $valabilitate,
            'tari' => $tari,
            'requiresTime' => $requiresTime,
            'lockTime' => $requiresTime,
            'romanianCountryIds' => $this->determineRomanianCountryIds($tari),
            'nextNrOrdine' => $this->resolveNextNrOrdine($valabilitate),
        ]);
    }

    public function edit(Request $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): View
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);
        $this->ensureCursaBelongsToValabilitate($valabilitate, $cursa);

        $cursa->loadMissing(['incarcareTara', 'descarcareTara']);

        $tari = Tara::query()
            ->orderBy('nume')
            ->get(['id', 'nume']);

        $hasDateTime = $cursa->data_cursa !== null;

        return view('sofer.valabilitati.curse.edit', [
            'valabilitate' => $valabilitate,
            'cursa' => $cursa,
            'tari' => $tari,
            'requiresTime' => $hasDateTime,
            'lockTime' => false,
            'romanianCountryIds' => $this->determineRomanianCountryIds($tari),
            'nextNrOrdine' => $this->resolveNextNrOrdine($valabilitate),
        ]);
    }

    public function store(SoferValabilitateCursaRequest $request, Valabilitate $valabilitate): RedirectResponse
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);

        $valabilitate->curse()->create($request->validated());

        return redirect()
            ->route('sofer.valabilitati.show', $valabilitate)
            ->with('status', 'Cursa a fost adăugată cu succes.');
    }

    public function update(SoferValabilitateCursaRequest $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): RedirectResponse
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);
        $this->ensureCursaBelongsToValabilitate($valabilitate, $cursa);

        $cursa->update($request->validated());

        return redirect()
            ->route('sofer.valabilitati.show', $valabilitate)
            ->with('status', 'Cursa a fost actualizată.');
    }

    public function destroy(Request $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): RedirectResponse
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);
        $this->ensureCursaBelongsToValabilitate($valabilitate, $cursa);

        $cursa->delete();

        return redirect()
            ->route('sofer.valabilitati.show', $valabilitate)
            ->with('status', 'Cursa a fost ștearsă.');
    }

    private function ensureDriverOwnsValabilitate(Request $request, Valabilitate $valabilitate): Valabilitate
    {
        $userId = $request->user()?->id;

        abort_unless((int) $valabilitate->sofer_id === (int) $userId, 403);

        return $valabilitate;
    }

    private function ensureCursaBelongsToValabilitate(Valabilitate $valabilitate, ValabilitateCursa $cursa): void
    {
        abort_unless((int) $cursa->valabilitate_id === (int) $valabilitate->id, 404);
    }

    /**
     * @param  Collection<int, Tara>  $tari
     * @return array<int, int>
     */
    private function determineRomanianCountryIds(Collection $tari): array
    {
        return $tari
            ->filter(static function (Tara $tara) {
                $normalized = Str::lower($tara->nume);

                return in_array($normalized, ['romania', 'românia'], true);
            })
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function resolveNextNrOrdine(Valabilitate $valabilitate): int
    {
        $max = (int) $valabilitate->curse()->max('nr_ordine');

        return $max > 0 ? $max + 1 : 1;
    }
}
