<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesValabilitatiCurseListings;
use App\Http\Requests\ValabilitateCursaGrupRequest;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursaGrup;
use App\Support\Valabilitati\ValabilitatiFilterState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ValabilitateCursaGrupController extends Controller
{
    use HandlesValabilitatiCurseListings;

    private const PER_PAGE = 20;

    public function index(Request $request, Valabilitate $valabilitate): View
    {
        $this->authorize('view', $valabilitate);

        $curse = $this->paginateCurse($request, $valabilitate);
        $valabilitate->loadMissing(['sofer', 'taxeDrum']);

        $grupuriQuery = $valabilitate->cursaGrupuri()->withCount('curse')->orderBy('nume');
        $grupuri = (clone $grupuriQuery)->paginate($this->perPage());
        $valabilitate->setRelation('cursaGrupuri', $grupuriQuery->get());

        $summary = $this->buildSummaryData($valabilitate, $curse);

        $modalViewData = $this->buildModalViewData(
            $valabilitate,
            $curse,
            true,
            old('form_type'),
            old('form_id')
        );

        $modalViewData['renderCurseModals'] = false;
        $modalViewData['redirectTo'] = $request->fullUrl();

        return view('valabilitati.grupuri.index', [
            'valabilitate' => $valabilitate,
            'grupuri' => $grupuri,
            'summary' => $summary,
            'backUrl' => ValabilitatiFilterState::route(),
            'modalViewData' => $modalViewData,
        ]);
    }

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

        if ($grup->curse()->exists()) {
            $message = 'Grupul nu poate fi șters deoarece are curse asociate.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'message_type' => 'error',
                ]);
            }

            return redirect($this->resolveListingRedirectUrl($request, $valabilitate))
                ->with('error', $message);
        }

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

    protected function displayGroupSummaryInResponses(): bool
    {
        $redirectTo = request()->input('redirect_to');

        if (is_string($redirectTo) && Str::contains($redirectTo, '/grupuri')) {
            return true;
        }

        return request()->routeIs('valabilitati.grupuri.*');
    }
}
