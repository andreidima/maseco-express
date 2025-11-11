<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValabilitateCursaRequest;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ValabilitateCursaController extends Controller
{
    public function store(ValabilitateCursaRequest $request, Valabilitate $valabilitate): RedirectResponse
    {
        $this->authorize('update', $valabilitate);

        $valabilitate->curse()->create($request->validated());

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
        ]);
    }

    public function update(ValabilitateCursaRequest $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): RedirectResponse
    {
        $this->assertBelongsToValabilitate($valabilitate, $cursa);

        $this->authorize('update', $valabilitate);

        $cursa->update($request->validated());

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
}
