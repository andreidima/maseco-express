<!DOCTYPE  html>
<html lang="ro">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <title>Comenzi</title>
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

            $monede = \App\Models\Moneda::select('id', 'nume')->get();
        @endphp
    @foreach ($monede as $moneda)
        @php
            $comenziLunaCurentaPentruAceastaMoneda = \App\Models\Comanda::select('id', 'transportator_contract', 'transportator_valoare_contract', 'transportator_moneda_id', 'client_valoare_contract', 'client_moneda_id', 'data_creare')
                ->whereDate('data_creare', '>=', Carbon::today()->startOfMonth())
                ->where(function($query) use ($moneda) {
                    return $query->where('transportator_moneda_id', $moneda->id)
                                ->orWhere('client_moneda_id', $moneda->id);
                })
                ->get();
        @endphp
        @if ($comenziLunaCurentaPentruAceastaMoneda->count() > 0)
        <table>
            <tr>
                <th colspan="4" style="font-size: 150%;">Moneda {{ $moneda->nume }}</th>
            </tr>
            <tr>
                <th>Contract</th>
                <th>Dată creare</th>
                <th>Valoare<br>contract<br>cransportator</th>
                <th>Valoare<br>contract<br>client</th>
            </tr>
            @foreach ($comenziLunaCurentaPentruAceastaMoneda as $comanda)
                <tr>
                    <td>
                        {{ $comanda->transportator_contract }}
                    </td>
                    <td class="">
                        {{ $comanda->data_creare ? Carbon::parse($comanda->data_creare)->isoFormat('DD.MM.YYYY') : '' }}
                    </td>
                    <td>
                        @if ($comanda->transportator_moneda_id == $moneda->id)
                            {{ $comanda->transportator_valoare_contract }}
                        @endif
                    </td>
                    <td>
                        @if ($comanda->client_moneda_id == $moneda->id)
                            {{ $comanda->client_valoare_contract }}
                        @endif
                    </td>
                </tr>
            @endforeach
            <tr>
                <th colspan="2" style="text-align: end;">
                    Total
                </th>
                <th>
                    {{ $comenziLunaCurentaPentruAceastaMoneda->where('transportator_moneda_id', $moneda->id)->sum('transportator_valoare_contract') }}
                </th>
                <th>
                    {{ $comenziLunaCurentaPentruAceastaMoneda->where('client_moneda_id', $moneda->id)->sum('client_valoare_contract') }}
                </th>
            </tr>
            <tr>
                <th colspan="4" style="text-align: center">
                    Total profit =
                    {{
                        $comenziLunaCurentaPentruAceastaMoneda->where('client_moneda_id', $moneda->id)->sum('client_valoare_contract')
                        -
                        $comenziLunaCurentaPentruAceastaMoneda->where('transportator_moneda_id', $moneda->id)->sum('transportator_valoare_contract')
                    }}
                </th>
            </tr>
        </table>
        <br><br><br><br>
        @endif
    @endforeach
    </main>
</body>

</html>
