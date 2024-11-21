@extends ('layouts.app')

@php
    use \Carbon\Carbon;
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-file me-1"></i>Documente transportatori
                </span>
            </div>
            <div class="col-lg-6">
            </div>
            <div class="col-lg-3 text-end">
            </div>
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors')

            <div class="row">
                <div class="col-lg-4">
                    <div class="table-responsive rounded">
                        <table class="table table-striped table-hover rounded">
                            <thead class="text-white rounded culoare2">
                                <tr>
                                    <th colspan="3" class="text-center">
                                        Comenzi cu toate documentele validate în ultimele 24 de ore
                                        <br>
                                        @php
                                            // dd($comenzi);
                                        @endphp
                                        {{-- {{ $comenzi->count() }}
                                        <br>
                                        {{ $comenzi->where('ultimulEmailPentruFisiereIncarcateDeTransportator.created_at', '>', Carbon::now()->subDay())->count() }}
                                        <br>
                                        @foreach ($comenzi as $comanda)
                                            / {{ $comanda->ultimulEmailPentruFisiereIncarcateDeTransportator->tip }}
                                        @endforeach --}}
                                    </th>
                                </tr>
                                <tr class="" style="padding:2rem">
                                    <th class="">#</th>
                                    <th class="">Comanda</th>
                                    <th class="text-end">Data finalizare documente</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($comenzi->where('ultimulEmailPentruFisiereIncarcateDeTransportator.tip', 2)->where('ultimulEmailPentruFisiereIncarcateDeTransportator.created_at', '>', Carbon::now()->subDay()) as $comanda)
                                    <tr>
                                        <td align="">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="">
                                            <a href="/comanda-documente-transportator/{{ $comanda->cheie_unica }}">
                                                {{ $comanda->transportator_contract }}</a>
                                        </td>
                                        <td class="text-end">
                                            {{ $comanda->ultimulEmailPentruFisiereIncarcateDeTransportator->created_at ? Carbon::parse($comanda->ultimulEmailPentruFisiereIncarcateDeTransportator->created_at)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                                </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-4 mx-auto">
                    <div class="table-responsive rounded">
                        <table class="table table-striped table-hover rounded">
                            <thead class="text-white rounded culoare2">
                                <tr>
                                    <th colspan="3" class="text-center">
                                        Comenzi cu documente în lucru care nu au mai fost actualizate de peste de 24 de ore
                                        <br>
                                        @php
                                            // dd($comenzi);
                                        @endphp
                                        {{-- {{ $comenzi->count() }}
                                        <br>
                                        {{ $comenzi->where('ultimulEmailMasecoCatreTransportator.created_at', '>', Carbon::now()->subDay())->count() }}
                                        <br>
                                        @foreach ($comenzi as $comanda)
                                            / {{ $comanda->ultimulEmailMasecoCatreTransportator->tip }}
                                        @endforeach --}}
                                    </th>
                                </tr>
                                <tr class="" style="padding:2rem">
                                    <th class="">#</th>
                                    <th class="">Comanda</th>
                                    <th class="text-end">Data ultimului mesaj</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($comenzi->where('ultimulEmailPentruFisiereIncarcateDeTransportator.tip', '<>', 2)->where('ultimulEmailPentruFisiereIncarcateDeTransportator.created_at', '<', Carbon::now()->subDay()) as $comanda)
                                    <tr>
                                        <td align="">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="">
                                            <a href="/comanda-documente-transportator/{{ $comanda->cheie_unica }}">
                                                {{ $comanda->transportator_contract }}</a>
                                        </td>
                                        <td class="text-end">
                                            {{ $comanda->ultimulEmailPentruFisiereIncarcateDeTransportator->created_at ? Carbon::parse($comanda->ultimulEmailPentruFisiereIncarcateDeTransportator->created_at)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                                </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-4 mx-auto">
                    <div class="table-responsive rounded">
                        <table class="table table-striped table-hover rounded">
                            <thead class="text-white rounded culoare2">
                                <tr>
                                    <th colspan="3" class="text-center">
                                        Comenzi finalizate de mai bine de 24 de ore ce nu au nici un document încărcat
                                    </th>
                                </tr>
                                <tr class="" style="padding:2rem">
                                    <th class="">#</th>
                                    <th class="">Comanda</th>
                                    <th class="text-end">Data ultima descărcare</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($comenziFaraFisiere as $comanda)
                                    @php
                                        $dataUltimaDescarcareDeAfisat = null;
                                        $dataCreareDeAfisat = null;
                                    @endphp
                                    @if ($ultimaDescarcare = $comanda->locuriOperareDescarcari->sortByDesc('pivot.data_ora')->first())
                                        @if (isset($ultimaDescarcare->pivot->data_ora))
                                            @php
                                                $durata = Carbon::parse($ultimaDescarcare->pivot->durata);
                                                $dataUltimaDescarcarePlusDurata = Carbon::parse($ultimaDescarcare->pivot->data_ora)->addHours($durata->hour)->addMinutes($durata->minute);

                                                $diferenta_fus_orar = 3-substr($ultimaDescarcare->tara->gmt_offset, 0, -3);
                                                $dataUltimaDescarcarePlusDurataPlusGmtOffset = $dataUltimaDescarcarePlusDurata->addHours($diferenta_fus_orar);
                                            @endphp
                                            {{-- {{ Carbon::parse($ultimaDescarcare->pivot->data_ora) }}
                                            <br>
                                            {{ $durata }}
                                            <br>
                                            {{ $ultimaDescarcare->tara->nume ?? '' }} {{ $diferenta_fus_orar }}
                                            <br>
                                            {{ $dataUltimaDescarcarePlusDurata }}
                                            <br> --}}
                                            @if ($dataUltimaDescarcarePlusDurataPlusGmtOffset->lt(Carbon::now()->subDay()))
                                                @php
                                                    $dataUltimaDescarcareDeAfisat = $dataUltimaDescarcarePlusDurataPlusGmtOffset;
                                                @endphp
                                            @endif
                                        @endif
                                    @elseif (Carbon::parse($comanda->data_creare)->lt(Carbon::now()->subDay()))
                                        @php
                                            $dataCreareDeAfisat = Carbon::parse($comanda->data_creare);
                                        @endphp
                                    @endif

                                    @if (isset($dataUltimaDescarcareDeAfisat) || isset($dataCreareDeAfisat))
                                        <tr>
                                            <td align="">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td class="">
                                                <a href="/comanda-documente-transportator/{{ $comanda->cheie_unica }}">
                                                    {{ $comanda->transportator_contract }}</a>
                                            </td>
                                            <td class="text-end">
                                                @if (isset($dataUltimaDescarcareDeAfisat))
                                                    {{ $dataUltimaDescarcareDeAfisat->isoFormat('DD.MM.YYYY HH:mm') }}
                                                @elseif (isset($dataCreareDeAfisat))
                                                    Nu există descărcări
                                                    <br>
                                                    Comandă creată:
                                                    {{ $dataCreareDeAfisat->isoFormat('DD.MM.YYYY') }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                @endforelse
                                </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
