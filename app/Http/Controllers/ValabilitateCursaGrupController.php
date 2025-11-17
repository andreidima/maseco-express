<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesValabilitatiCurseListings;
use App\Http\Requests\ValabilitateCursaGrupRequest;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursaGrup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ValabilitateCursaGrupController extends Controller
{
    use HandlesValabilitatiCurseListings;

    private const PER_PAGE = 20;

    public function store(ValabilitateCursaGrupRequest $request, Valabilitate $valabilitate): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $valabilitate);

        $valabilitate->cursaGrupuri()->create($request->validated());

        return $this->respondAfterMutation($request, $valabilitate, 'Grupul a fost creat.');
    }

    public function update(
        ValabilitateCursaGrupRequest $request,
        Valabilitate $valabilitate,
        ValabilitateCursaGrup $grup
    ): JsonResponse|RedirectResponse {
        $this->assertBelongsToValabilitate($valabilitate, $grup);

        $this->authorize('update', $valabilitate);

        $grup->update($request->validated());

        return $this->respondAfterMutation($request, $valabilitate, 'Grupul a fost actualizat.');
    }

    public function destroy(Request $request, Valabilitate $valabilitate, ValabilitateCursaGrup $grup): JsonResponse|RedirectResponse
    {
        $this->assertBelongsToValabilitate($valabilitate, $grup);

        $this->authorize('update', $valabilitate);

        $grup->delete();

        return $this->respondAfterMutation($request, $valabilitate, 'Grupul a fost șters.');
    }

    private function assertBelongsToValabilitate(Valabilitate $valabilitate, ValabilitateCursaGrup $grup): void
    {
        abort_unless((int) $grup->valabilitate_id === (int) $valabilitate->getKey(), 404);
    }

    protected function perPage(): int
    {
        return self::PER_PAGE;
    }
}
