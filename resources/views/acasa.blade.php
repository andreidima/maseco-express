@extends('layouts.app')

@php
    use \Carbon\Carbon;
    use \App\Models\Comanda;
    use \App\Models\ComandaIstoric;
    use \App\Models\ComandaFisierEmail;
    use \App\Models\Moneda;
    use \App\Models\Firma;
    use \App\Models\User;

    // Define date ranges
    $startOfThisMonth = Carbon::now()->startOfMonth();
    // $startOfLastMonth = Carbon::now()->subMonth()->startOfMonth();
    // $endOfLastMonth = Carbon::now()->subMonth()->endOfMonth();

    $comenziLunaCurenta = Comanda::with('intermediere:id,comanda_id,motis,dkv,astra')
        ->select('id', 'transportator_valoare_contract', 'transportator_moneda_id', 'client_valoare_contract', 'client_moneda_id')
        ->whereDate('data_creare', '>=', $startOfThisMonth)->get();

    // KPI report
    $usersIDsForThisReport = [6, 7, 8, 12, 16, 17, 21];

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
        <div class="col-md-8">
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
        <div class="col-md-4">
            <div class="row">
                <div class="col-md-12 mb-5">
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
                                        @if ($comanda->user_name === 'Tcaciuc Alexandru')
                                            <span class="badge bg-warning text-dark">000000 Poziție Gold</span>
                                        @endif
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
@endsection

