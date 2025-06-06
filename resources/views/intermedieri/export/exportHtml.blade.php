<!DOCTYPE  html>
<html lang="ro">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <title>Intermedieri</title>
    <style>
        /* html {
            margin: 0px 0px;
        } */
        /** Define the margins of your page **/
        @page {
            margin: 0px 0px;
        }

        header {
            position: fixed;
            top: 0px;
            left: 0px;
            right: 0px;
            height: 0px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            /* font-family: Arial, Helvetica, sans-serif; */
            font-size: 12px;
            /* margin-top: 1cm; */
            margin-top: 0.3cm;
            margin-left: 0.3cm;
            margin-right: 0.3cm;
            margin-bottom: 0.3cm;
        }

        * {
            /* padding: 0; */
            text-indent: 0;
            text-align: justify;
        }

        table{
            border-collapse:collapse;
            margin: 0px;
            padding: 5px;
            margin-top: 0px;
            border-style: solid;
            border-width: 1px;
            width: 100%;
            word-wrap:break-word;
        }

        th, td {
            padding: 1px 10px;
            border-width: 1px;
            border-style: solid;

        }
        tr {
            border-style: solid;
            border-width: 0px;
        }
        hr {
            display: block;
            margin-top: 0.5em;
            margin-bottom: 0.5em;
            margin-left: auto;
            margin-right: auto;
            border-style: inset;
            border-width: 0.5px;
        }
    </style>
</head>

<body>
    {{-- <header style="margin:0px 0px 0px 0px; text-align: center;">
        <img src="{{ asset('images/logo2-400x103.jpg') }}" width="400px">
    </header> --}}

    <main>
        @php
            use Carbon\Carbon;
            $azi = Carbon::today();
        @endphp

        <table>
            <thead>
                <tr>
                    <th colspan="20" style="text-align: center">
                        @php
                            // dd(App\Models\User::find($searchUser)->name);
                        @endphp
                        Interval: {{ $searchInterval ? (Carbon::parse(strtok($searchInterval, ','))->isoFormat('DD.MM.YYYY')) : null }} - {{ $searchInterval ? (Carbon::parse(strtok( '' ))->isoFormat('DD.MM.YYYY')) : null }}
                        /
                        Utilizator: {{ $searchUser ? App\Models\User::find($searchUser)->name : null }}
                        /
                        Predare la contabilitate: {{ $searchPredat ?? '-' }}
                    </th>
                </tr>
                <tr>
                    <th class="">#</th>
                    <th class="">Msc order</th>
                    <th class="">Spediteur</th>
                    <th class="">Carrier</th>
                    <th class="text-end">Sold inițial</th>
                    <th class="text-end">Sold final</th>
                    <th class="text-end">Job value</th>
                    <th class="text-center">Dată creare</th>
                    <th class="text-center">Contract client</th>
                    <th class="">Frm<br>Doc</th>
                    <th class="">Factură Maseco</th>
                    <th class="">Factură Transp.</th>
                    <th class="">Data factură</th>
                    <th class="">Achitat Transp.</th>
                    <th class="">Observații</th>
                    <th class="">Număr mașină</th>
                    <th class="">Motis</th>
                    <th class="">DKV</th>
                    <th class="">Astra</th>
                    <th class="">Plată client</th>
                    <th class="fs-6 text-center">Predat<br> contab.</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($comenzi as $comanda)
                    @if (
                            // Commented on 21.01.2025
                            // // Documents are per post and at leat 1 is uploaded by an operator
                            // (($comanda->transportator_format_documente == "1") && ($comanda->fisiereTransportatorIncarcateDeOperator->count() > 0))
                            // ||
                            // // Documents are digital and the operator sent the last email that they are good
                            // (($comanda->transportator_format_documente == "2") && (($comanda->ultimulEmailPentruFisiereIncarcateDeTransportator->tip ?? null) == "2"))

                            // A different rule was added on 21.01.2025
                            $comanda->factura_transportator_incarcata == "1"
                        )
                        <tr style="background-color: rgb(171, 196, 255)">
                    @elseif (isset($comanda->data_plata_transportator) && ($comanda->data_plata_transportator <= $azi))
                        <tr style="background-color: rgb(174, 255, 171)">
                    @else
                        <tr>
                    @endif
                        <td class="fs-6" align="">
                            {{ $loop->index + 1 }}
                        </td>
                        <td class="fs-6">
                            {{ $comanda->transportator_contract }}
                        </td>
                        <td class="fs-6">
                            {{-- {{ $comanda->factura->client_nume ?? '' }} --}}
                            @forelse ($comanda->clientiComanda as $clientComanda)
                                {{ $clientComanda->factura->client_nume ?? '' }}
                                <br>
                            @empty
                            @endforelse
                        </td>
                        <td class="fs-6">
                            {{ $comanda->transportator->nume ?? '' }}
                        </td>
                        <td class="fs-6 text-end">
                            {{-- {{ $comanda->client_valoare_contract_initiala }} {{ $comanda->clientMoneda->nume ?? null }} --}}
                            @forelse ($comanda->clientiComanda as $clientComanda)
                                {{ $clientComanda->valoare_contract_initiala ?? '' }} {{ $clientComanda->moneda->nume ?? null }}
                                <br>
                            @empty
                            @endforelse
                        </td>
                        <td class="fs-6 text-end">
                            {{ $comanda->client_valoare_contract }} {{ $comanda->clientMoneda->nume ?? null }}
                        </td>
                        <td class="fs-6 text-end">
                            {{ $comanda->transportator_valoare_contract }} {{ $comanda->transportatorMoneda->nume ?? null }}
                        </td>
                        <td class="fs-6 text-center">
                            {{ $comanda->data_creare ? Carbon::parse($comanda->data_creare)->isoFormat('DD.MM.YYYY') : null }}
                        </td>
                        <td class="fs-6 text-center">
                            {{-- @if ($comanda->factura && $comanda->factura->client_contract)
                                @foreach(explode('+', $comanda->factura->client_contract) as $part)
                                    {{ $part }}
                                    <br>
                                @endforeach
                            @endif --}}
                            @forelse ($comanda->clientiComanda as $clientComanda)
                                @if ($clientComanda->factura && $clientComanda->factura->client_contract)
                                    @foreach(explode('+', $clientComanda->factura->client_contract) as $part)
                                        {{ $part }}
                                        <br>
                                    @endforeach
                                @endif
                            @empty
                            @endforelse
                        </td>
                        <td class="fs-6">
                            @if ($comanda->transportator_format_documente == "1")
                                Posta
                            @elseif ($comanda->transportator_format_documente == "2")
                                Digital
                            @endif
                        </td>
                        <td class="fs-6">
                            @forelse ($comanda->clientiComanda as $clientComanda)
                                {{ $clientComanda->factura->seria ?? null }} {{ $clientComanda->factura->numar ?? null }}
                                <br>
                            @empty
                            @endforelse
                        </td>
                        <td class="fs-6">
                            {{ $comanda->factura_transportator ?? null }}
                        </td>
                        <td class="fs-6">
                            @forelse ($comanda->clientiComanda as $clientComanda)
                                @if ($clientComanda->factura)
                                    {{ $clientComanda->factura->data ? Carbon::parse($clientComanda->factura->data)->isoFormat('DD.MM.YYYY') : null }}
                                    <br>
                                @endif
                            @empty
                            @endforelse
                        </td>
                        <td class="fs-6">
                            {{ $comanda->data_plata_transportator ? Carbon::parse($comanda->data_plata_transportator)->isoFormat('DD.MM.YYYY') : null }}
                        </td>
                        <td class="fs-6">
                            {{ $comanda->intermediere->observatii ?? null }}
                        </td>
                        <td class="fs-6">
                            {{ $comanda->camion->numar_inmatriculare ?? null }}
                        </td>
                        <td class="fs-6">
                            {{ $comanda->intermediere->motis ?? null }}
                        </td>
                        <td class="fs-6">
                            {{ $comanda->intermediere->dkv ?? null }}
                        </td>
                        <td class="fs-6">
                            {{ $comanda->intermediere->astra ?? null }}
                        </td>
                        {{-- <td class="fs-6">
                            {{ $comanda->intermediere->plata_client ?? null }}
                        </td> --}}
                        <td class="fs-6 text-center">
                            @if (($comanda->intermediere->predat_la_contabilitate ?? null) == 1)
                                DA
                            @else
                                NU
                            @endif
                            </a>
                        </td>
                    </tr>
                @empty
                @endforelse
                    <tr class="" style="padding:2rem">
                        <th class="fs-6"></th>
                        <th class="fs-6"></th>
                        <th class="fs-6"></th>
                        <th class="fs-6"></th>
                        <th class="fs-6 text-end">
                            {{-- Flatten all 'clientiComanda' collections from each 'Comanda' into a single collection --}}
                            {{-- For each 'Comanda', return its related 'clientiComanda' collection                                     --}}
                            {{-- Step 3: Sum the 'valoare_contract_initiala' from each 'clientiComanda' record --}}
                            {{-- This ensures we're summing the field from the related table, not from 'Comanda' --}}
                            {{
                                $totalSoldInitial = $comenzi->flatMap(function ($comanda) {
                                    return $comanda->clientiComanda;
                                })->sum(function ($clientComanda) {
                                    return $clientComanda->valoare_contract_initiala;
                                })
                            }}
                            {{ $comanda->clientMoneda->nume ?? null }}
                        </th>
                        <th class="fs-6 text-end">
                            {{ $comenzi->sum('client_valoare_contract') }} {{ $comanda->clientMoneda->nume ?? null }}
                        </th>
                        <th class="fs-6 text-end">
                            {{ $totalJobValue = $comenzi->sum('transportator_valoare_contract') }} {{ $comanda->transportatorMoneda->nume ?? null }}
                        </th>
                        <th class="fs-6 text-end"></th>
                        <th class="fs-6 text-center"></th>
                        <th class="fs-6 text-center"></th>
                        <th class="fs-6"></th>
                        <th class="fs-6"></th>
                        <th class="fs-6"></th>
                        <th class="fs-6"></th>
                        <th class="fs-6"></th>
                        <th class="fs-6"></th>
                        <th class="fs-6">{{ $totalMotis = $comenzi->sum(fn($comanda) => $comanda->intermediere->motis ?? 0) }}</th>
                        <th class="fs-6">{{ $totalDkv = $comenzi->sum(fn($comanda) => $comanda->intermediere->dkv ?? 0) }}</th>
                        <th class="fs-6">{{ $totalAstra = $comenzi->sum(fn($comanda) => $comanda->intermediere->astra ?? 0) }}</th>
                        <th class="fs-6">{{ ($totalSoldInitial ?? 0) - ($totalJobValue ?? 0) - ($totalMotis ?? 0) - ($totalDkv ?? 0) - ($totalAstra ?? 0) }}</th>
                        <th class="fs-6"></th>
                    </tr>
            </tbody>
        </table>
    </main>
</body>

</html>
