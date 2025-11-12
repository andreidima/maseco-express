<?php

namespace App\Http\Controllers;

use App\Http\Requests\SoferValabilitateCursaRequest;
use App\Models\Tara;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SoferValabilitateCursaController extends Controller
{
    public function show(Request $request, Valabilitate $valabilitate): View
    {
        $valabilitate = $this->ensureDriverOwnsValabilitate($request, $valabilitate);

        $valabilitate->load([
            'curse' => function ($query) {
                $query->orderBy('data_cursa')->orderBy('id');
            },
            'curse.incarcareTara',
            'curse.descarcareTara',
        ]);

        $tari = Tara::query()
            ->orderBy('nume')
            ->get(['id', 'nume']);

        return view('sofer.valabilitati.show', [
            'valabilitate' => $valabilitate,
            'curse' => $valabilitate->curse,
            'tari' => $tari,
            'modalKey' => session('sofer_curse_modal'),
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
}
