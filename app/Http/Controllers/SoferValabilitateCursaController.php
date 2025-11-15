<?php

namespace App\Http\Controllers;

use App\Http\Requests\SoferValabilitateCursaRequest;
use App\Models\Tara;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use App\Support\Valabilitati\ValabilitateCursaOrderer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SoferValabilitateCursaController extends Controller
{
    public function show(Request $request, Valabilitate $valabilitate): View
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);

        $valabilitate->load([
            'sofer',
            'curse' => function ($query) {
                $query->orderBy('nr_ordine')->orderBy('data_cursa');
            },
            'curse.incarcareTara',
            'curse.descarcareTara',
        ]);

        $curse = $valabilitate->curse;

        $startDate = $valabilitate->data_inceput;
        $endDate = $valabilitate->data_sfarsit;

        $totalDays = null;

        if ($startDate && $endDate) {
            $totalDays = $startDate->diffInDays($endDate) + 1;
        }

        $kmMapsAggregate = $curse->reduce(
            static function (array $carry, ValabilitateCursa $cursa): array {
                if ($cursa->km_maps !== null) {
                    $carry['sum'] += (int) $cursa->km_maps;
                    $carry['count']++;
                }

                return $carry;
            },
            ['sum' => 0, 'count' => 0]
        );

        $kmBord2Aggregate = $curse->reduce(
            static function (array $carry, ValabilitateCursa $cursa): array {
                if ($cursa->km_bord_incarcare !== null && $cursa->km_bord_descarcare !== null) {
                    $carry['sum'] += (int) $cursa->km_bord_descarcare - (int) $cursa->km_bord_incarcare;
                    $carry['count']++;
                }

                return $carry;
            },
            ['sum' => 0, 'count' => 0]
        );

        $kmDifferenceAggregate = $curse->reduce(
            static function (array $carry, ValabilitateCursa $cursa): array {
                if (
                    $cursa->km_maps !== null &&
                    $cursa->km_bord_incarcare !== null &&
                    $cursa->km_bord_descarcare !== null
                ) {
                    $bord2 = (int) $cursa->km_bord_descarcare - (int) $cursa->km_bord_incarcare;
                    $carry['sum'] += $bord2 - (int) $cursa->km_maps;
                    $carry['count']++;
                }

                return $carry;
            },
            ['sum' => 0, 'count' => 0]
        );

        $kmMapsTotal = $kmMapsAggregate['count'] > 0 ? $kmMapsAggregate['sum'] : null;
        $kmBord2Total = $kmBord2Aggregate['count'] > 0 ? $kmBord2Aggregate['sum'] : null;
        $kmDifferenceTotal = $kmDifferenceAggregate['count'] > 0 ? $kmDifferenceAggregate['sum'] : null;

        return view('sofer.valabilitati.show', [
            'valabilitate' => $valabilitate,
            'curse' => $curse,
            'summary' => [
                'vehicle' => $valabilitate->numar_auto ?: '—',
                'driver' => $valabilitate->sofer?->name ?: '—',
                'period_start' => $startDate?->format('d.m.Y'),
                'period_end' => $endDate?->format('d.m.Y'),
                'total_days' => $totalDays,
                'total_courses' => $curse->count(),
                'km_maps' => $kmMapsTotal,
                'km_bord_2' => $kmBord2Total,
                'km_difference' => $kmDifferenceTotal,
            ],
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
        ]);
    }

    public function store(SoferValabilitateCursaRequest $request, Valabilitate $valabilitate): RedirectResponse
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);

        $data = $request->validated();

        if (! array_key_exists('nr_ordine', $data)) {
            $data['nr_ordine'] = $this->resolveNextNrOrdine($valabilitate);
        }

        $valabilitate->curse()->create($data);

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

    public function reorder(Request $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): RedirectResponse
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);
        $this->ensureCursaBelongsToValabilitate($valabilitate, $cursa);

        $validated = $request->validate([
            'direction' => ['required', Rule::in(['up', 'down'])],
        ]);

        $direction = $validated['direction'];

        $moved = ValabilitateCursaOrderer::move($cursa, $direction);

        $message = $moved
            ? 'Ordinea cursei a fost actualizată.'
            : ($direction === 'up'
                ? 'Această cursă este deja prima în listă.'
                : 'Această cursă este deja ultima în listă.');

        return redirect()
            ->route('sofer.valabilitati.show', $valabilitate)
            ->with('status', $message);
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
