<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValabilitateCursaRequest;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use App\Support\Valabilitati\ValabilitatiFilterState;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ValabilitateCursaController extends Controller
{
    public function index(Valabilitate $valabilitate): View
    {
        $this->authorize('view', $valabilitate);

        $valabilitate->loadMissing([
            'sofer',
            'curse' => fn ($query) => $query
                ->orderByDesc('data_cursa')
                ->orderByDesc('created_at'),
        ]);

        return view('valabilitati.curse.index', [
            'valabilitate' => $valabilitate,
            'backUrl' => ValabilitatiFilterState::route(),
        ]);
    }

    public function store(ValabilitateCursaRequest $request, Valabilitate $valabilitate): RedirectResponse
    {
        $this->authorize('update', $valabilitate);

        $valabilitate->curse()->create($request->validated());

        return redirect()
            ->route('valabilitati.curse.index', $valabilitate)
            ->with('status', 'Cursa a fost adăugată cu succes.');
    }

    public function edit(Valabilitate $valabilitate, ValabilitateCursa $cursa): View
    {
        $this->assertBelongsToValabilitate($valabilitate, $cursa);

        $this->authorize('update', $valabilitate);

        return view('valabilitati.curse.edit', [
            'valabilitate' => $valabilitate,
            'cursa' => $cursa,
        ]);
    }

    public function update(ValabilitateCursaRequest $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): RedirectResponse
    {
        $this->assertBelongsToValabilitate($valabilitate, $cursa);

        $this->authorize('update', $valabilitate);

        $cursa->update($request->validated());

        return redirect()
            ->route('valabilitati.curse.index', $valabilitate)
            ->with('status', 'Cursa a fost actualizată.');
    }

    public function destroy(Valabilitate $valabilitate, ValabilitateCursa $cursa): RedirectResponse
    {
        $this->assertBelongsToValabilitate($valabilitate, $cursa);

        $this->authorize('update', $valabilitate);

        $cursa->delete();

        return redirect()
            ->route('valabilitati.curse.index', $valabilitate)
            ->with('status', 'Cursa a fost ștearsă.');
    }

    private function assertBelongsToValabilitate(Valabilitate $valabilitate, ValabilitateCursa $cursa): void
    {
        if ($cursa->valabilitate_id !== $valabilitate->getKey()) {
            abort(404);
        }
    }
}
