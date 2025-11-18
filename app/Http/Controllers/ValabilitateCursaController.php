<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\HandlesValabilitatiCurseListings;
use App\Http\Requests\ValabilitateCursaBulkAssignRequest;
use App\Http\Requests\ValabilitateCursaRequest;
use App\Models\Valabilitate;
use App\Models\ValabilitateCursa;
use App\Support\Valabilitati\ValabilitateCursaOrderer;
use App\Support\Valabilitati\ValabilitatiCurseFilterState;
use App\Support\Valabilitati\ValabilitatiFilterState;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ValabilitateCursaController extends Controller
{
    use HandlesValabilitatiCurseListings;

    private const PER_PAGE = 20;

    public function index(Request $request, Valabilitate $valabilitate): View
    {
        $this->authorize('view', $valabilitate);

        $curse = $this->paginateCurse($request, $valabilitate);

        ValabilitatiCurseFilterState::remember($request, $valabilitate, []);

        $valabilitate->loadMissing(['sofer', 'taxeDrum', 'cursaGrupuri', 'divizie']);
        $summary = $this->buildSummaryData($valabilitate, $curse);

        $modalViewData = $this->buildModalViewData(
            $valabilitate,
            $curse,
            true,
            old('form_type'),
            old('form_id')
        );

        return view('valabilitati.curse.index', [
            'valabilitate' => $valabilitate,
            'curse' => $curse,
            'nextPageUrl' => $this->buildNextPageUrl($request, $valabilitate, $curse),
            'backUrl' => ValabilitatiFilterState::route(),
            'summary' => $summary,
            'modalViewData' => $modalViewData,
            'bulkAssignRoute' => route('valabilitati.curse.bulk-assign', $valabilitate),
        ]);
    }

    public function paginate(Request $request, Valabilitate $valabilitate): JsonResponse
    {
        $this->authorize('view', $valabilitate);

        $curse = $this->paginateCurse($request, $valabilitate);

        ValabilitatiCurseFilterState::remember($request, $valabilitate, []);
        $valabilitate->loadMissing(['sofer', 'taxeDrum', 'cursaGrupuri', 'divizie']);
        $summary = $this->buildSummaryData($valabilitate, $curse);

        return response()->json([
            'rows_html' => view('valabilitati.curse.partials.rows', [
                'valabilitate' => $valabilitate,
                'curse' => $curse,
            ])->render(),
            'modals_html' => view('valabilitati.curse.partials.modals', $this->buildModalViewData(
                $valabilitate,
                $curse,
                false
            ))->render(),
            'next_url' => $this->buildNextPageUrl($request, $valabilitate, $curse),
            'summary_html' => $this->renderSummaryHtml($valabilitate, $summary),
        ]);
    }

    public function store(ValabilitateCursaRequest $request, Valabilitate $valabilitate): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $valabilitate);

        $data = $request->validated();

        if (! array_key_exists('nr_ordine', $data)) {
            $data['nr_ordine'] = $this->resolveNextNrOrdine($valabilitate);
        }

        $valabilitate->curse()->create($data);

        return $this->respondAfterMutation($request, $valabilitate, 'Cursa a fost adăugată cu succes.');
    }

    public function update(ValabilitateCursaRequest $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): RedirectResponse|JsonResponse
    {
        $this->assertBelongsToValabilitate($valabilitate, $cursa);

        $this->authorize('update', $valabilitate);

        $cursa->update($request->validated());

        return $this->respondAfterMutation($request, $valabilitate, 'Cursa a fost actualizată.');
    }

    public function destroy(Request $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): RedirectResponse|JsonResponse
    {
        $this->assertBelongsToValabilitate($valabilitate, $cursa);

        $this->authorize('update', $valabilitate);

        $cursa->delete();

        return $this->respondAfterMutation($request, $valabilitate, 'Cursa a fost ștearsă.');
    }

    public function bulkAssign(
        ValabilitateCursaBulkAssignRequest $request,
        Valabilitate $valabilitate
    ): RedirectResponse|JsonResponse {
        $this->authorize('update', $valabilitate);

        $data = $request->validated();
        $cursaIds = $data['curse_ids'] ?? [];
        $grupId = $data['cursa_grup_id'];

        $updated = $valabilitate
            ->curse()
            ->whereIn('id', $cursaIds)
            ->update(['cursa_grup_id' => $grupId]);

        $message = match ($updated) {
            0 => 'Nicio cursă nu a fost actualizată.',
            1 => '1 cursă a fost adăugată în grup.',
            default => sprintf('%d curse au fost adăugate în grup.', $updated),
        };

        return $this->respondAfterMutation($request, $valabilitate, $message);
    }

    public function reorder(Request $request, Valabilitate $valabilitate, ValabilitateCursa $cursa): RedirectResponse|JsonResponse
    {
        $this->assertBelongsToValabilitate($valabilitate, $cursa);

        $this->authorize('update', $valabilitate);

        $validated = $request->validate([
            'direction' => ['required', Rule::in(['up', 'down'])],
        ]);

        $direction = $validated['direction'];

        $moved = ValabilitateCursaOrderer::move($cursa, $direction);

        $message = $moved
            ? 'Ordinea cursei a fost actualizată.'
            : ($direction === 'up'
                ? 'Cursa este deja la începutul listei.'
                : 'Cursa este deja la finalul listei.');

        if ($request->expectsJson()) {
            return $this->listingJsonResponse($valabilitate, $message);
        }

        return redirect(ValabilitatiCurseFilterState::route($valabilitate))->with('status', $message);
    }

    private function resolveNextNrOrdine(Valabilitate $valabilitate): int
    {
        $max = (int) $valabilitate->curse()->max('nr_ordine');

        return $max > 0 ? $max + 1 : 1;
    }

    private function assertBelongsToValabilitate(Valabilitate $valabilitate, ValabilitateCursa $cursa): void
    {
        if ($cursa->valabilitate_id !== $valabilitate->getKey()) {
            abort(404);
        }
    }

    protected function perPage(): int
    {
        return self::PER_PAGE;
    }

    protected function displayGroupSummaryInResponses(): bool
    {
        return false;
    }
}
