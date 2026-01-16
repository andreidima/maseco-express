@extends('layouts.app')

@php
    use \Carbon\Carbon;
    use \App\Models\Comanda;
    use \App\Models\ComandaIstoric;
    use \App\Models\ComandaFisierEmail;
    use \App\Models\Moneda;
    use \App\Models\Firma;
    use \App\Models\User;
    use \Illuminate\Support\Facades\Schema;

    // Define date ranges
    $startOfThisMonth = Carbon::now()->startOfMonth();
    // $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
    // $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

    $comenziLunaCurenta = Comanda::with('intermediere:id,comanda_id,motis,dkv,astra')
        ->select('id', 'transportator_valoare_contract', 'transportator_moneda_id', 'client_valoare_contract', 'client_moneda_id')
        ->whereDate('data_creare', '>=', $startOfThisMonth)->get();

    // KPI report
    $usersIDsForThisReport = [7, 12, 26];

    $comenziKPI = Comanda::select(
        'user_id',
        'users.name as user_name',
        DB::raw("
            SUM(
                CASE
                    WHEN data_creare >= '$startOfThisMonth'
                        AND (
                            client_valoare_contract - transportator_valoare_contract
                            - COALESCE(intermedieri.motis, 0)
                            - COALESCE(intermedieri.dkv, 0)
                            - COALESCE(intermedieri.astra, 0)
                        ) > 0
                    THEN 1
                    ELSE 0
                END
            ) as this_month_greater_than_zero
        "),
        DB::raw("
            SUM(
                CASE
                    WHEN data_creare >= '$startOfThisMonth'
                        AND (
                            client_valoare_contract - transportator_valoare_contract
                            - COALESCE(intermedieri.motis, 0)
                            - COALESCE(intermedieri.dkv, 0)
                            - COALESCE(intermedieri.astra, 0)
                        ) < 0
                    THEN 1
                    ELSE 0
                END
            ) as this_month_less_than_zero
        "),
        DB::raw("
            SUM(
                CASE
                    WHEN data_creare >= '$startOfThisMonth'
                        AND (
                            client_valoare_contract - transportator_valoare_contract
                            - COALESCE(intermedieri.motis, 0)
                            - COALESCE(intermedieri.dkv, 0)
                            - COALESCE(intermedieri.astra, 0)
                        ) = 0
                    THEN 1
                    ELSE 0
                END
            ) as this_month_equal_to_zero
        "),
        // Calculate total profit for orders of the current month:
        DB::raw("
            SUM(
                CASE
                    WHEN data_creare >= '$startOfThisMonth'
                    THEN client_valoare_contract - transportator_valoare_contract
                        - COALESCE(intermedieri.motis, 0)
                        - COALESCE(intermedieri.dkv, 0)
                        - COALESCE(intermedieri.astra, 0)
                    ELSE 0
                END
            ) as total_profit
        ")
    )
    ->join('users', 'comenzi.user_id', '=', 'users.id')
    ->leftJoin('intermedieri', 'comenzi.id', '=', 'intermedieri.comanda_id')
    ->whereIn('user_id', $usersIDsForThisReport)
    ->groupBy('user_id', 'users.name')
    // ->orderBy('users.name')
    // Order by the total_profit descending (highest profit first)
    ->orderBy('total_profit', 'desc')
    ->get();

    $monede = Moneda::select('id', 'nume')->get();
    $leiLunaCurenta = $comenziLunaCurenta->where('client_moneda_id', 1)->sum('client_valoare_contract') - $comenziLunaCurenta->where('transportator_moneda_id', 1)->sum('transportator_valoare_contract');

    // Weekly marks table (rated users are not part of KPI list)
    $weeklyRatedUserIds = [16, 27];

    $weeklyMarksCurrentWeekStart = Carbon::now()->startOfWeek(Carbon::MONDAY);
    $weeklyMarksSelectedWeekStartInput = request()->query('week_start');
    $weeklyMarksSelectedWeekStart = $weeklyMarksCurrentWeekStart->copy();
    if ($weeklyMarksSelectedWeekStartInput) {
        try {
            $weeklyMarksSelectedWeekStart = Carbon::parse($weeklyMarksSelectedWeekStartInput)->startOfWeek(Carbon::MONDAY);
        } catch (\Exception $exception) {
            $weeklyMarksSelectedWeekStart = $weeklyMarksCurrentWeekStart->copy();
        }
    }

    if ($weeklyMarksSelectedWeekStart->greaterThan($weeklyMarksCurrentWeekStart)) {
        $weeklyMarksSelectedWeekStart = $weeklyMarksCurrentWeekStart->copy();
    }

    $weeklyMarksSelectedWeekStartDate = $weeklyMarksSelectedWeekStart->toDateString();
    $weeklyMarksSelectedWeekEnd = $weeklyMarksSelectedWeekStart->copy()->addDays(6);

    $weeklyMarkEvaluators = User::select('id', 'name')
        ->whereIn('id', $usersIDsForThisReport)
        ->orderByRaw('FIELD(id, ' . implode(',', $usersIDsForThisReport) . ')')
        ->get();

    $weeklyMarkTargets = User::select('id', 'name')
        ->whereIn('id', $weeklyRatedUserIds)
        ->orderByRaw('FIELD(id, ' . implode(',', $weeklyRatedUserIds) . ')')
        ->get();

    $weeklyMarks = collect();
    if (Schema::hasTable('kpi_weekly_marks')) {
        $weeklyMarks = DB::table('kpi_weekly_marks')
            ->select('rated_user_id', 'rated_by_user_id', 'mark')
            ->where('week_start_date', $weeklyMarksSelectedWeekStartDate)
            ->whereIn('rated_user_id', $weeklyRatedUserIds)
            ->whereIn('rated_by_user_id', $usersIDsForThisReport)
            ->get();
    }

    $weeklyMarksMatrix = [];
    foreach ($weeklyMarks as $weeklyMark) {
        $weeklyMarksMatrix[$weeklyMark->rated_user_id][$weeklyMark->rated_by_user_id] = $weeklyMark->mark;
    }

    $weeklyMarksAverageFormatted = [];
    foreach ($weeklyRatedUserIds as $ratedUserId) {
        $existingMarks = [];

        foreach ($usersIDsForThisReport as $evaluatorId) {
            if (array_key_exists($ratedUserId, $weeklyMarksMatrix) && array_key_exists($evaluatorId, $weeklyMarksMatrix[$ratedUserId])) {
                $markValue = $weeklyMarksMatrix[$ratedUserId][$evaluatorId];
                if ($markValue !== null) {
                    $existingMarks[] = (int) $markValue;
                }
            }
        }

        $weeklyMarksAverageFormatted[$ratedUserId] = count($existingMarks) === 0
            ? '-'
            : number_format(array_sum($existingMarks) / count($existingMarks), 1, '.', '');
    }
@endphp

@section('content')
{{-- <div class="container"> --}}
<div class="mx-2">
    <div class="row justify-content-center">
        <div class="col-md-6 mb-5">
            <div class="card culoare2">
                <div class="card-header">Pagina principală</div>

                <div class="card-body">
                    Bine ai venit <b>{{ auth()->user()->name ?? '' }}</b>!
                    <br><br>
                    Comenzi operate de tine în luna curentă: <b>{{ Comanda::whereDate('data_creare', '>=', Carbon::today()->startOfMonth())->where('operator_user_id', auth()->user()->id)->count(); }}</b>
                    <br>
                    Comenzi operate de tine în luna trecută: <b>{{ Comanda::whereYear('data_creare', Carbon::now()->subMonthNoOverflow())->whereMonth('data_creare', Carbon::now()->subMonthNoOverflow())->where('operator_user_id', auth()->user()->id)->count(); }}</b>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="row justify-content-center">
                <div class="col-md-4 mb-3">
                    <div class="card culoare2">
                        <div class="card-header text-center">Clienți noi luna curentă</div>
                        <div class="card-body text-center">
                            <b class="fs-2">{{ Firma::where('tip_partener', 1)->whereDate('created_at', '>=', Carbon::today()->startOfMonth())->count() }}</b>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card culoare2">
                        <div class="card-header text-center">Transportatori noi luna curentă</div>
                        <div class="card-body text-center">
                            <b class="fs-2">{{ Firma::where('tip_partener', 2)->whereDate('created_at', '>=', Carbon::today()->startOfMonth())->count() }}</b>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card culoare2">
                        <div class="card-header text-center">Total comenzi</div>
                        <div class="card-body text-center">
                            <b class="fs-2">{{ Comanda::where('stare', '<>', 3)->count() }}</b>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card culoare2">
                        <div class="card-header text-center">Comenzi deschise</div>
                        <div class="card-body text-center">
                            <b class="fs-2">{{ Comanda::where('stare', 1)->count() }}</b>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card culoare2">
                        <div class="card-header text-center">Comenzi luna curentă</div>
                        <div class="card-body text-center">
                            <b class="fs-2">{{ $comenziLunaCurenta->count() }}</b>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-5">
                    <div class="card culoare2">
                        <div class="card-header text-center">Profit comenzi luna curentă
                            <a href="comenzi/totaluri-luna-curenta" target="_blank">
                                <i class="fa-solid fa-circle-info text-white" title="Detaliază"></i>
                            </a>
                        </div>
                        <div class="card-body text-center">
                            @php
                                $suma = $comenziLunaCurenta->sum('client_valoare_contract') -
                                    $comenziLunaCurenta->sum('transportator_valoare_contract') -
                                    $comenziLunaCurenta->sum('intermediere.motis') -
                                    $comenziLunaCurenta->sum('intermediere.dkv') -
                                    $comenziLunaCurenta->sum('intermediere.astra');
                            @endphp
                            <b class="fs-2">{{ $suma }} EUR</b>
                            &nbsp;&nbsp;
                            <br>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="row">
                <div class="col-md-12 mb-2">
                    <div class="table-responsive rounded">
                        <table class="table table-sm table-striped table-hover rounded">
                            <thead class="text-white rounded culoare2">
                                <tr>
                                    <th colspan="4" class="py-0 text-center">
                                        KPI (performanță 2025) - Comenzi luna curentă
                                        {{-- <a href="key-performance-indicators">
                                            <i class="fa-solid fa-circle-info text-white" title="Detaliază"></i>
                                        </a> --}}
                                    </th>
                                </tr>
                                <tr class="" style="padding:2rem">
                                    <th class="py-0">Utilizator</th>
                                    <th class="py-0 text-center">Pe plus</th>
                                    <th class="py-0 text-center">Pe minus</th>
                                    <th class="py-0 text-center">Pe 0</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach ($comenziKPI as $comanda)
                                <tr>
                                    <td class="py-0">
                                        {{-- @if ($comanda->user_name === 'Tcaciuc Alexandru')
                                            <span class="badge bg-warning text-dark">000000 Poziție Gold</span>
                                        @endif --}}
                                        {{ $comanda->user_name }}
                                    </td>
                                    <td class="py-0 text-center">{{ $comanda->this_month_greater_than_zero }}</td>
                                    <td class="py-0 text-center">{{ $comanda->this_month_less_than_zero }}</td>
                                    <td class="py-0 text-center">{{ $comanda->this_month_equal_to_zero }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-12 mb-5">
                    <div class="table-responsive rounded">
                        <table class="table table-sm table-striped table-hover rounded" id="weeklyMarksTable">
                            <thead class="text-white rounded culoare2">
                                <tr>
                                    <th colspan="{{ 2 + $weeklyMarkEvaluators->count() }}" class="py-0 text-center">
                                        Note săptămânale
                                        <span class="ms-2">
                                            <a
                                                class="text-white text-decoration-none me-2"
                                                href="{{ url()->current() }}?week_start={{ $weeklyMarksSelectedWeekStart->copy()->subWeek()->toDateString() }}"
                                                title="Săptămâna anterioară"
                                            >
                                                <i class="fa-solid fa-chevron-left"></i>
                                            </a>
                                            @if ($weeklyMarksSelectedWeekStart->equalTo($weeklyMarksCurrentWeekStart))
                                                <span class="text-white-50" title="Ești deja la săptămâna curentă">
                                                    <i class="fa-solid fa-chevron-right"></i>
                                                </span>
                                            @else
                                                <a
                                                    class="text-white text-decoration-none"
                                                    href="{{ url()->current() }}?week_start={{ $weeklyMarksSelectedWeekStart->copy()->addWeek()->toDateString() }}"
                                                    title="Săptămâna următoare"
                                                >
                                                    <i class="fa-solid fa-chevron-right"></i>
                                                </a>
                                            @endif
                                        </span>
                                        <span class="ms-3 small">
                                            ({{ $weeklyMarksSelectedWeekStart->isoFormat('DD.MM.YYYY') }} - {{ $weeklyMarksSelectedWeekEnd->isoFormat('DD.MM.YYYY') }})
                                        </span>
                                    </th>
                                </tr>
                                <tr>
                                    <th class="py-0">Utilizator</th>
                                    @foreach ($weeklyMarkEvaluators as $evaluator)
                                        <th class="py-0 text-center">{{ $evaluator->name }}</th>
                                    @endforeach
                                    <th class="py-0 text-center">Medie</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($weeklyMarkTargets as $target)
                                    <tr data-weekly-target-row="{{ $target->id }}">
                                        <td class="py-0">{{ $target->name }}</td>
                                        @foreach ($weeklyMarkEvaluators as $evaluator)
                                            @php
                                                $cellMark = $weeklyMarksMatrix[$target->id][$evaluator->id] ?? null;
                                                $cellDisplay = $cellMark === null ? '-' : $cellMark;
                                                $isEditable = (auth()->id() === $evaluator->id) && in_array(auth()->id(), $usersIDsForThisReport, true);
                                            @endphp
                                            <td class="py-0 text-center">
                                                @if ($isEditable)
                                                    <a
                                                        href="#"
                                                        class="weekly-mark-cell text-decoration-none"
                                                        data-week-start="{{ $weeklyMarksSelectedWeekStartDate }}"
                                                        data-rated-user-id="{{ $target->id }}"
                                                        data-rated-user-name="{{ $target->name }}"
                                                        data-current-mark="{{ $cellMark === null ? '' : $cellMark }}"
                                                    >
                                                        <span class="weekly-mark-value">{{ $cellDisplay }}</span>
                                                        <i class="fa-solid fa-pen-to-square ms-1 small text-primary"></i>
                                                    </a>
                                                @else
                                                    <span class="weekly-mark-value">{{ $cellDisplay }}</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="py-0 text-center weekly-mark-average" data-weekly-average-for="{{ $target->id }}">
                                            {{ $weeklyMarksAverageFormatted[$target->id] ?? '-' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="row justify-content-center">
        <div class="col-md-4 mb-5">
            <div class="table-responsive rounded">
                <table class="table table-striped table-hover rounded">
                    <thead class="text-white rounded culoare2">
                        <tr><th colspan="3" class="text-center">Observații interne
                            <a href="/comenzi/observatii-interne"><span class="badge bg-primary">Vezi toate</span></a>
                        </th></tr>
                        <tr class="" style="padding:2rem">
                            <th class="">#</th>
                            <th class="">Comanda</th>
                            <th class="">Observație</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach (Comanda::where('observatii_interne', '<>', null)->latest()->take(20)->get() as $comanda)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $comanda->transportator_contract }}</td>
                            <td>{{ $comanda->observatii_interne }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-4 mb-5">
            <div class="table-responsive rounded">
                <table class="table table-striped table-hover rounded">
                    <thead class="text-white rounded culoare2">
                        <tr><th colspan="3" class="text-center">Observații externe</th></tr>
                        <tr class="" style="padding:2rem">
                            <th class="">#</th>
                            <th class="">Comanda</th>
                            <th class="">Observație</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach (Comanda::where('observatii_externe', '<>', null)->latest()->take(20)->get() as $comanda)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $comanda->transportator_contract }}</td>
                            <td>{{ $comanda->observatii_externe }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-4 mb-5">
            <div class="table-responsive rounded">
                <table class="table table-striped table-hover rounded">
                    <thead class="text-white rounded culoare2">
                        <tr><th colspan="5" class="text-center">Activitate recentă
                            <a href="/comenzi/activitate-recenta"><span class="badge bg-primary">Vezi toate</span></a>
                        </th></tr>
                        <tr class="" style="padding:2rem">
                            <th class="">#</th>
                            <th class="">Contract</th>
                            <th class="">Operare</th>
                            <th class="">Utilizator</th>
                            <th class="">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach (ComandaIstoric::with('userOperare')->orderBy('id_pk', 'desc')->take(20)->get() as $comandaIstoric)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $comandaIstoric->transportator_contract }}</td>
                            <td>{{ $comandaIstoric->operare_descriere }}</td>
                            <td>{{ $comandaIstoric->userOperare->name ?? '' }}</td>
                            <td>{{ $comandaIstoric->operare_data ? Carbon::parse($comandaIstoric->operare_data)->isoFormat('DD.MM.YYYY HH:mm') : '' }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="modal fade text-dark" id="weeklyMarkModal" tabindex="-1" aria-labelledby="weeklyMarkModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header culoare2">
                <h5 class="modal-title text-white" id="weeklyMarkModalLabel">Nota săptămânală</h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Închide"></button>
            </div>
            <form id="weeklyMarkForm">
                <div class="modal-body" style="text-align:left;">
                    <div class="mb-2">
                        <div><b id="weeklyMarkModalUserName">—</b></div>
                        <div class="text-muted small">
                            {{ $weeklyMarksSelectedWeekStart->isoFormat('DD.MM.YYYY') }} - {{ $weeklyMarksSelectedWeekEnd->isoFormat('DD.MM.YYYY') }}
                        </div>
                    </div>

                    <input type="hidden" id="weeklyMarkWeekStart" value="{{ $weeklyMarksSelectedWeekStartDate }}">
                    <input type="hidden" id="weeklyMarkRatedUserId" value="">

                    <label for="weeklyMarkValue" class="form-label mb-1">Nota (0–3)</label>
                    <select id="weeklyMarkValue" class="form-control">
                        <option value="">— Nesetat</option>
                        <option value="0">0</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                    <button type="submit" class="btn btn-primary text-white" id="weeklyMarkSaveButton">
                        <span class="me-1"><i class="fa-solid fa-floppy-disk"></i></span>Salvează
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('weeklyMarkModal');
            if (!modalEl || !window.bootstrap) {
                return;
            }

            const saveUrl = @json(route('acasa.weekly-marks.upsert'));

            const modal = new bootstrap.Modal(modalEl);
            const form = document.getElementById('weeklyMarkForm');
            const userNameEl = document.getElementById('weeklyMarkModalUserName');
            const ratedUserIdEl = document.getElementById('weeklyMarkRatedUserId');
            const weekStartEl = document.getElementById('weeklyMarkWeekStart');
            const markSelectEl = document.getElementById('weeklyMarkValue');
            const saveButton = document.getElementById('weeklyMarkSaveButton');

            let activeCell = null;

            function setSavingState(isSaving) {
                if (!saveButton) return;
                saveButton.disabled = isSaving;
                saveButton.innerHTML = isSaving
                    ? '<i class="fas fa-spinner fa-spin me-1"></i>Se salvează'
                    : '<span class="me-1"><i class="fa-solid fa-floppy-disk"></i></span>Salvează';
            }

            function openModalForCell(cell) {
                activeCell = cell;

                const ratedUserId = cell.getAttribute('data-rated-user-id') || '';
                const ratedUserName = cell.getAttribute('data-rated-user-name') || '—';
                const weekStart = cell.getAttribute('data-week-start') || '';
                const currentMark = cell.getAttribute('data-current-mark') || '';

                ratedUserIdEl.value = ratedUserId;
                weekStartEl.value = weekStart;
                userNameEl.textContent = ratedUserName;
                markSelectEl.value = currentMark;

                modal.show();
            }

            document.querySelectorAll('.weekly-mark-cell').forEach((cell) => {
                cell.addEventListener('click', function (event) {
                    event.preventDefault();
                    openModalForCell(cell);
                });
            });

            if (!form || !window.axios) {
                return;
            }

            form.addEventListener('submit', function (event) {
                event.preventDefault();

                if (!activeCell) {
                    return;
                }

                const ratedUserId = Number(ratedUserIdEl.value || 0);
                const weekStart = weekStartEl.value || '';
                const markValue = markSelectEl.value;
                const mark = markValue === '' ? null : Number(markValue);

                setSavingState(true);

                window.axios.post(saveUrl, {
                    rated_user_id: ratedUserId,
                    week_start: weekStart,
                    mark: mark,
                })
                .then((response) => {
                    const data = response && response.data ? response.data : null;

                    const newDisplay = mark === null ? '-' : String(mark);
                    const valueEl = activeCell.querySelector('.weekly-mark-value');
                    if (valueEl) {
                        valueEl.textContent = newDisplay;
                    }
                    activeCell.setAttribute('data-current-mark', mark === null ? '' : String(mark));

                    const row = activeCell.closest('tr');
                    const avgEl = row ? row.querySelector('.weekly-mark-average') : null;
                    if (avgEl && data && typeof data.average_formatted !== 'undefined') {
                        avgEl.textContent = data.average_formatted;
                    }

                    modal.hide();
                })
                .catch((error) => {
                    console.error('Error saving weekly mark:', error);
                    alert('Eroare la salvare. Încearcă din nou.');
                })
                .finally(() => {
                    setSavingState(false);
                });
            });
        });
    </script>
@endpush
