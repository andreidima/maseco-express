<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValabilitateCursaRequest;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use App\Support\CountryList;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ValabilitateCursaController extends Controller
{
    public function store(ValabilitateCursaRequest $request, Valabilitate $valabilitate): RedirectResponse
    {
        $this->authorize('update', $valabilitate);

        $data = $request->validated();

        $cursa = $valabilitate->curse()->create($data);

        $this->syncUltimaCursa($valabilitate, $cursa, $data['ultima_cursa'] ?? false);

        return redirect()
            ->route('valabilitati.show', $valabilitate)
            ->with('status', 'Cursa a fost adăugată cu succes.');
    }

    public function edit(Valabilitate $valabilitate, ValabilitateCursa $cursa): View
    {
        $this->assertBelongsToValabilitate($valabilitate, $cursa);

        $this->authorize('update', $valabilitate);

        return view('valabilitati.curse.edit', [
            'valabilitate' => $valabilitate,
            'cursa' => $cursa,
            'countries' => CountryList::options(),
            'isFirstTrip' => $this->isFirstTrip($valabilitate, $cursa),
        ]);
    }

    public function update(ValabilitateCursaRequest $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): RedirectResponse
    {
        $this->assertBelongsToValabilitate($valabilitate, $cursa);

        $this->authorize('update', $valabilitate);

        $data = $request->validated();

        $cursa->update($data);

        $this->syncUltimaCursa($valabilitate, $cursa, $data['ultima_cursa'] ?? false);

        return redirect()
            ->route('valabilitati.show', $valabilitate)
            ->with('status', 'Cursa a fost actualizată.');
    }

    public function destroy(Valabilitate $valabilitate, ValabilitateCursa $cursa): RedirectResponse
    {
        $this->assertBelongsToValabilitate($valabilitate, $cursa);

        $this->authorize('update', $valabilitate);

        $cursa->delete();

        return redirect()
            ->route('valabilitati.show', $valabilitate)
            ->with('status', 'Cursa a fost ștearsă.');
    }

    private function assertBelongsToValabilitate(Valabilitate $valabilitate, ValabilitateCursa $cursa): void
    {
        if ($cursa->valabilitate_id !== $valabilitate->getKey()) {
            abort(404);
        }
    }

    private function isFirstTrip(Valabilitate $valabilitate, ?ValabilitateCursa $ignored = null): bool
    {
        $query = $valabilitate->curse();

        if ($ignored) {
            $query->whereKeyNot($ignored->getKey());
        }

        return $query->doesntExist();
    }

    private function syncUltimaCursa(Valabilitate $valabilitate, ValabilitateCursa $cursa, bool $isUltima): void
    {
        if (! $isUltima) {
            return;
        }

        $valabilitate->curse()
            ->whereKeyNot($cursa->getKey())
            ->where('ultima_cursa', true)
            ->update(['ultima_cursa' => false]);
    }
}
