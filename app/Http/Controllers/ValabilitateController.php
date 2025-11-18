<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Valabilitate;
use App\Models\ValabilitatiDivizie;
use App\Support\Valabilitati\ValabilitatiFilterState;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ValabilitateController extends Controller
{
    private const PER_PAGE = 20;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $filters = $this->validateFilters($request);
        $valabilitati = $this->paginateValabilitati($request, $filters);

        ValabilitatiFilterState::remember($request, $this->filtersToQueryString($filters));

        return view('valabilitati.index', [
            'valabilitati' => $valabilitati,
            'filters' => $filters,
            'nextPageUrl' => $this->buildNextPageUrl($request, $valabilitati),
            'soferi' => $this->getSoferOptions(),
        ]);
    }

    public function paginate(Request $request): JsonResponse
    {
        $filters = $this->validateFilters($request);
        $valabilitati = $this->paginateValabilitati($request, $filters);

        return response()->json([
            'rows_html' => view('valabilitati.partials.rows', [
                'valabilitati' => $valabilitati,
            ])->render(),
            'next_url' => $this->buildNextPageUrl($request, $valabilitati),
        ]);
    }

    public function create(): View
    {
        return view('valabilitati.create', [
            'backUrl' => ValabilitatiFilterState::route(),
            'soferi' => $this->getSoferOptions(),
            'divizii' => $this->getDivizieOptions(),
        ]);
    }

    public function edit(Valabilitate $valabilitate): View
    {
        $valabilitate->loadMissing([
            'sofer',
            'taxeDrum',
            'divizie',
        ]);

        return view('valabilitati.edit', [
            'valabilitate' => $valabilitate,
            'backUrl' => ValabilitatiFilterState::route(),
            'soferi' => $this->getSoferOptions(),
            'divizii' => $this->getDivizieOptions(),
        ]);
    }

    public function show(Valabilitate $valabilitate): View
    {
        $valabilitate->loadMissing([
            'sofer',
            'taxeDrum',
            'divizie',
        ]);

        return view('valabilitati.show', [
            'valabilitate' => $valabilitate,
            'backUrl' => ValabilitatiFilterState::route(),
            'soferi' => $this->getSoferOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $this->validateValabilitate($request);

        if ($validated instanceof RedirectResponse) {
            return $validated;
        }

        $taxeDrum = $validated['taxe_drum'] ?? [];
        $attributes = Arr::except($validated, ['taxe_drum']);

        $valabilitate = Valabilitate::create($attributes);

        $this->syncRoadTaxes($valabilitate, $taxeDrum);

        $valabilitate->refresh();

        return $this->respondAfterMutation($request, 'Valabilitatea a fost adăugată.', $valabilitate);
    }

    public function update(Request $request, Valabilitate $valabilitate): RedirectResponse|JsonResponse
    {
        $validated = $this->validateValabilitate($request, $valabilitate);

        if ($validated instanceof RedirectResponse) {
            return $validated;
        }

        $taxeDrum = $validated['taxe_drum'] ?? [];
        $attributes = Arr::except($validated, ['taxe_drum']);

        $valabilitate->update($attributes);

        $this->syncRoadTaxes($valabilitate, $taxeDrum);

        $valabilitate->refresh();

        return $this->respondAfterMutation($request, 'Valabilitatea a fost actualizată.', $valabilitate);
    }

    public function destroy(Request $request, Valabilitate $valabilitate): RedirectResponse|JsonResponse
    {
        if ($valabilitate->curse()->exists()) {
            $message = 'Valabilitatea nu poate fi ștearsă deoarece are curse asociate.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'errors' => [
                        'curse' => [$message],
                    ],
                    'should_close_modal' => true,
                    'feedback_type' => 'danger',
                ], 422);
            }

            return back()->with('error', $message);
        }

        $valabilitate->delete();

        return $this->respondAfterMutation($request, 'Valabilitatea a fost ștearsă.');
    }

    private function validateFilters(Request $request): array
    {
        return $this->parseFilters($request->only([
            'numar_auto',
            'sofer',
            'divizie',
            'inceput_start',
            'inceput_end',
            'sfarsit_start',
            'sfarsit_end',
            'interval_start',
            'interval_end',
        ]));
    }

    /**
     * @param array<string, mixed> $filters
     *
     * @return array<string, mixed>
     */
    private function filtersToQueryString(array $filters): array
    {
        return array_filter($filters, static fn ($value) => $value !== null && $value !== '');
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function buildFilteredQuery(array $filters): Builder
    {
        $query = Valabilitate::query()->with([
            'sofer',
            'taxeDrum',
            'divizie',
        ]);

        if ($filters['divizie'] !== '') {
            $divizie = Str::lower($filters['divizie']);
            $query->whereHas('divizie', function (Builder $builder) use ($divizie): void {
                $builder->whereRaw('LOWER(nume) LIKE ?', ["%{$divizie}%"]);
            });
        }

        if ($filters['sofer'] !== '') {
            $sofer = Str::lower($filters['sofer']);
            $query->whereHas('sofer', function (Builder $builder) use ($sofer): void {
                $builder->whereRaw('LOWER(name) LIKE ?', ["%{$sofer}%"]);
            });
        }

        if ($filters['numar_auto'] !== '') {
            $numarAuto = Str::lower($filters['numar_auto']);
            $query->whereRaw('LOWER(numar_auto) LIKE ?', ["%{$numarAuto}%"]);
        }

        if ($filters['inceput_start']) {
            $query->whereDate('data_inceput', '>=', $filters['inceput_start']);
        }

        if ($filters['inceput_end']) {
            $query->whereDate('data_inceput', '<=', $filters['inceput_end']);
        }

        if ($filters['sfarsit_start']) {
            $query->whereDate('data_sfarsit', '>=', $filters['sfarsit_start']);
        }

        if ($filters['sfarsit_end']) {
            $query->whereDate('data_sfarsit', '<=', $filters['sfarsit_end']);
        }

        return $query;
    }

    /**
     * @param array<string, mixed> $filters
     */
    private function paginateValabilitati(Request $request, array $filters, ?array $query = null): LengthAwarePaginator
    {
        $paginator = $this->buildFilteredQuery($filters)
            ->orderByDesc('data_inceput')
            ->orderBy(
                ValabilitatiDivizie::select('nume')
                    ->whereColumn('valabilitati_divizii.id', 'valabilitati.divizie_id')
            )
            ->paginate(self::PER_PAGE);

        if ($query !== null) {
            return $paginator->appends($query);
        }

        return $paginator->withQueryString();
    }

    private function buildNextPageUrl(Request $request, LengthAwarePaginator $paginator): ?string
    {
        if (! $paginator->hasMorePages()) {
            return null;
        }

        $queryParameters = Arr::except($request->query(), ['page']);
        $nextPage = $paginator->currentPage() + 1;

        return route('valabilitati.paginate', array_merge($queryParameters, ['page' => $nextPage]));
    }

    /**
     * @param array<string, mixed> $input
     */
    private function parseFilters(array $input): array
    {
        $validator = Validator::make($input, [
            'numar_auto' => ['nullable', 'string', 'max:255'],
            'sofer' => ['nullable', 'string', 'max:255'],
            'divizie' => ['nullable', 'string', 'max:255'],
            'inceput_start' => ['nullable', 'date'],
            'inceput_end' => ['nullable', 'date', 'after_or_equal:inceput_start'],
            'sfarsit_start' => ['nullable', 'date'],
            'sfarsit_end' => ['nullable', 'date', 'after_or_equal:sfarsit_start'],
            'interval_start' => ['nullable', 'date'],
            'interval_end' => ['nullable', 'date', 'after_or_equal:interval_start'],
        ]);

        $validated = $validator->validate();

        $inceputStart = $validated['inceput_start'] ?? $validated['interval_start'] ?? null;
        $inceputEnd = $validated['inceput_end'] ?? $validated['interval_end'] ?? null;

        return [
            'numar_auto' => trim((string) ($validated['numar_auto'] ?? '')),
            'sofer' => trim((string) ($validated['sofer'] ?? '')),
            'divizie' => trim((string) ($validated['divizie'] ?? '')),
            'inceput_start' => $inceputStart,
            'inceput_end' => $inceputEnd,
            'sfarsit_start' => $validated['sfarsit_start'] ?? null,
            'sfarsit_end' => $validated['sfarsit_end'] ?? null,
        ];
    }

    private function getSoferOptions(): array
    {
        return User::query()
            ->where('activ', 1)
            ->whereHas('roles', function (Builder $query): void {
                $query->where('slug', 'sofer');
            })
            ->orderBy('name')
            ->pluck('name', 'id')
            ->toArray();
    }

    private function getDivizieOptions(): array
    {
        return ValabilitatiDivizie::query()
            ->orderBy('nume')
            ->pluck('nume', 'id')
            ->toArray();
    }

    /**
     * @return array<string, mixed>|RedirectResponse
     */
    private function validateValabilitate(Request $request, ?Valabilitate $valabilitate = null)
    {
        $sanitizedRoadTaxes = $this->sanitizeRoadTaxes($request->input('taxe_drum', []));

        $input = array_merge($request->all(), [
            'taxe_drum' => $sanitizedRoadTaxes,
        ]);

        $validator = Validator::make($input, [
            'numar_auto' => ['required', 'string', 'max:255'],
            'sofer_id' => ['required', 'integer', 'exists:users,id'],
            'divizie_id' => ['required', 'integer', 'exists:valabilitati_divizii,id'],
            'data_inceput' => ['required', 'date'],
            'data_sfarsit' => ['nullable', 'date', 'after_or_equal:data_inceput'],
            'taxe_drum' => ['array'],
            'taxe_drum.*.nume' => ['required', 'string', 'max:255'],
            'taxe_drum.*.tara' => ['nullable', 'string', 'max:255'],
            'taxe_drum.*.suma' => ['nullable', 'numeric', 'min:0'],
            'taxe_drum.*.moneda' => ['nullable', 'string', 'max:10'],
            'taxe_drum.*.data' => ['nullable', 'date'],
            'taxe_drum.*.observatii' => ['nullable', 'string'],
        ], [], [
            'numar_auto' => 'număr auto',
            'sofer_id' => 'șofer',
            'divizie_id' => 'divizie',
            'data_inceput' => 'data de început',
            'data_sfarsit' => 'data de sfârșit',
            'taxe_drum' => 'taxe de drum',
            'taxe_drum.*.nume' => 'nume',
            'taxe_drum.*.tara' => 'țară',
            'taxe_drum.*.suma' => 'sumă',
            'taxe_drum.*.moneda' => 'monedă',
            'taxe_drum.*.data' => 'dată',
            'taxe_drum.*.observatii' => 'observații',
        ]);

        $oldInput = $this->buildOldInput($request, $sanitizedRoadTaxes);

        if ($validator->fails() && ! $request->expectsJson()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput($oldInput);
        }

        try {
            $validated = $validator->validate();
            $validated['taxe_drum'] = $this->normalizeRoadTaxesForStorage($sanitizedRoadTaxes);

            return $validated;
        } catch (ValidationException $exception) {
            if ($request->expectsJson()) {
                throw $exception;
            }

            return redirect()->back()
                ->withErrors($exception->validator)
                ->withInput($oldInput);
        }
    }

    private function respondAfterMutation(Request $request, string $message, ?Valabilitate $valabilitate = null): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return $this->listingJsonResponse($message, $valabilitate);
        }

        return redirect(ValabilitatiFilterState::route())->with('status', $message);
    }

    private function listingJsonResponse(string $message, ?Valabilitate $valabilitate = null): JsonResponse
    {
        $query = ValabilitatiFilterState::get();
        $filtersRequest = Request::create('', 'GET', $query);
        $filters = $this->parseFilters($query);
        $valabilitati = $this->paginateValabilitati($filtersRequest, $filters, $query);

        $response = [
            'message' => $message,
            'table_html' => view('valabilitati.partials.rows', [
                'valabilitati' => $valabilitati,
            ])->render(),
            'next_url' => $this->buildNextPageUrl($filtersRequest, $valabilitati),
        ];

        if ($valabilitate) {
            $valabilitate->loadMissing(['sofer', 'taxeDrum', 'divizie']);
            $response['valabilitate'] = $valabilitate->toArray();
        }

        return response()->json($response);
    }

    /**
     * @param array<int, mixed>|mixed $input
     *
     * @return array<int, array<string, string>>
     */
    private function sanitizeRoadTaxes(mixed $input): array
    {
        if (! is_array($input)) {
            return [];
        }

        $sanitized = [];

        foreach ($input as $taxa) {
            if (! is_array($taxa)) {
                continue;
            }

            $nume = isset($taxa['nume']) ? trim((string) $taxa['nume']) : '';
            $tara = isset($taxa['tara']) ? trim((string) $taxa['tara']) : '';
            $suma = isset($taxa['suma']) ? trim((string) $taxa['suma']) : '';
            $moneda = isset($taxa['moneda']) ? trim((string) $taxa['moneda']) : '';
            $data = isset($taxa['data']) ? trim((string) $taxa['data']) : '';
            $observatii = isset($taxa['observatii']) ? trim((string) $taxa['observatii']) : '';

            if ($nume === '' && $tara === '' && $suma === '' && $moneda === '' && $data === '' && $observatii === '') {
                continue;
            }

            $sanitized[] = [
                'nume' => $nume,
                'tara' => $tara,
                'suma' => $suma === '' ? '' : str_replace(',', '.', $suma),
                'moneda' => $moneda,
                'data' => $data,
                'observatii' => $observatii,
            ];
        }

        return array_values($sanitized);
    }

    /**
     * @param array<int, array<string, string>> $taxeDrum
     *
     * @return array<int, array<string, mixed>>
     */
    private function normalizeRoadTaxesForStorage(array $taxeDrum): array
    {
        $normalized = [];

        foreach ($taxeDrum as $taxa) {
            $normalized[] = [
                'nume' => $taxa['nume'],
                'tara' => $taxa['tara'] !== '' ? $taxa['tara'] : null,
                'suma' => $this->formatRoadTaxAmount($taxa['suma']),
                'moneda' => $taxa['moneda'] !== '' ? Str::upper($taxa['moneda']) : null,
                'data' => $taxa['data'] !== '' ? $taxa['data'] : null,
                'observatii' => $taxa['observatii'] !== '' ? $taxa['observatii'] : null,
            ];
        }

        return $normalized;
    }

    private function formatRoadTaxAmount(string $amount): ?string
    {
        if ($amount === '') {
            return null;
        }

        $numeric = (float) $amount;

        return number_format($numeric, 2, '.', '');
    }

    private function buildOldInput(Request $request, array $taxeDrum): array
    {
        return array_merge($request->except('taxe_drum'), [
            'taxe_drum' => $taxeDrum,
        ]);
    }

    private function syncRoadTaxes(Valabilitate $valabilitate, array $taxeDrum): void
    {
        $valabilitate->taxeDrum()->delete();

        if ($taxeDrum === []) {
            return;
        }

        $valabilitate->taxeDrum()->createMany($taxeDrum);
    }
}
