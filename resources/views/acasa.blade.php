@extends('layouts.app')

@php
    use \Carbon\Carbon;
    use \App\Models\Comanda;
    use \App\Models\ComandaIstoric;
    use \App\Models\ComandaFisierEmail;
@endphp

@section('content')
{{-- <div class="container"> --}}
<div class="mx-2">
    <div class="row justify-content-center">
        <div class="col-md-6 mb-5">
            <div class="card culoare2">
                <div class="card-header">{{ __('Dashboard') }}</div>

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

    <div class="row justify-content-center">
        <div class="col-md-12 mb-3">
            <div class="card culoare2">
                <div class="card-header text-center">KPI (performanță 2025)</div>
                <div class="card-body text-center">
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        @php
            $comenziLunaCurenta = Comanda::select('id', 'transportator_valoare_contract', 'transportator_moneda_id', 'client_valoare_contract', 'client_moneda_id')
                                                        ->whereDate('data_creare', '>=', Carbon::today()->startOfMonth())->get();

            $monede = \App\Models\Moneda::select('id', 'nume')->get();
            $leiLunaCurenta = $comenziLunaCurenta->where('client_moneda_id', 1)->sum('client_valoare_contract') - $comenziLunaCurenta->where('transportator_moneda_id', 1)->sum('transportator_valoare_contract')
        @endphp
        <div class="col-md-4 mb-3">
            <div class="card culoare2">
                <div class="card-header text-center">Clienți noi luna curentă</div>
                <div class="card-body text-center">
                    <b class="fs-2">{{ \App\Models\Firma::where('tip_partener', 1)->whereDate('created_at', '>=', Carbon::today()->startOfMonth())->count() }}</b>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card culoare2">
                <div class="card-header text-center">Transportatori noi luna curentă</div>
                <div class="card-body text-center">
                    <b class="fs-2">{{ \App\Models\Firma::where('tip_partener', 2)->whereDate('created_at', '>=', Carbon::today()->startOfMonth())->count() }}</b>
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
                <div class="card-header text-center">Profit comenzi luna curenta
                    <a href="comenzi/totaluri-luna-curenta" target="_blank">
                        <i class="fa-solid fa-circle-info text-white" title="Detaliază"></i>
                    </a>
                </div>
                <div class="card-body text-center">
                    @foreach ($monede as $moneda)
                        @if (($suma = $comenziLunaCurenta->where('client_moneda_id', $moneda->id)->sum('client_valoare_contract') - $comenziLunaCurenta->where('transportator_moneda_id', $moneda->id)->sum('transportator_valoare_contract')) !== 0)
                            <b class="fs-2">{{ $suma }} {{ $moneda->nume }}</b>
                            {{-- (<small><a href="">Detaliază</a></small>) --}}
                            &nbsp;&nbsp;
                            <br>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>


    <div class="row justify-content-center">
        <div class="col-md-4 mb-5">
            <div class="table-responsive rounded">
                <table class="table table-striped table-hover rounded">
                    <thead class="text-white rounded culoare2">
                        <tr><th colspan="3" class="text-center">Observații interne</th></tr>
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
                        <tr><th colspan="5" class="text-center">Activitate recentă</th></tr>
                        <tr class="" style="padding:2rem">
                            <th class="">#</th>
                            <th class="">Contract</th>
                            <th class="">Operare</th>
                            <th class="">Utilizator</th>
                            <th class="">Data</th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach (ComandaIstoric::with('user')->orderBy('id_pk', 'desc')->take(20)->get() as $comandaIstoric)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $comandaIstoric->transportator_contract }}</td>
                            <td>{{ $comandaIstoric->operare_descriere }}</td>
                            <td>{{ $comandaIstoric->user->name ?? '' }}</td>
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

