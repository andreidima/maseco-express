@php
    use \Carbon\Carbon;
@endphp

<!DOCTYPE  html>
<html lang="ro">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Factura</title>
    <style>
        html {
            /* margin: 0px 0px; */
            line-height: 70%;
        }
        /** Define the margins of your page **/
        @page {
            margin: 10px 0px;
        }

        /* header {
            position: fixed;
            top: 0px;
            left: 0px;
            right: 0px;
            height: 0px;
        } */

        body {
            font-family: DejaVu Sans, sans-serif;
            /* font-family: Arial, Helvetica, sans-serif; */
            font-size: 12px;
            margin-top: 10px;
            margin-left: 1cm;
            margin-right: 1cm;
            margin-bottom: 1cm;

            background-image: url({{ public_path('images/logo-2-background-factura.jpg') }});
            background-repeat: no-repeat;
            background-size: 700px;
            background-position: center;
        }

        * {
            /* padding: 0; */
            text-indent: 0;
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
            padding: 2px 2px;
            border: 0px;
            border-style: solid;

        }
        tr {
            border-style: solid;
            border-width: 1px;
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
    {{-- <header> --}}
        {{-- <img src="{{ asset('images/contract-header.jpg') }}" width="800px"> --}}
    {{-- </header> --}}

    {{-- <main> --}}

        <div style="page-break-inside: avoid; margin-bottom:0px;">

            <table style="border:1px solid black;">
                <tr valign="" style="">
                    <td style="border-width:0px; padding:5px; margin:0rem; width:36%;">
                        Furnizor: {{ $factura->furnizor_nume }}
                        <br>
                        Reg. com.: {{ $factura->furnizor_reg_com }}
                        <br>
                        CIF: {{ $factura->furnizor_cif }}
                        <br>
                        Adresa: {{ $factura->furnizor_adresa }}
                        <br>
                        {{ $factura->furnizor_banca }}
                        <br>
                        SWIFT CODE : {{ $factura->furnizor_swift_code }}
                        <br>
                        IBAN (EUR): <span style="font-size: 10px">{{ $factura->furnizor_iban_eur }}</span>
                        <br>
                        Banca: {{ $factura->furnizor_iban_eur_banca }}
                        <br>
                        IBAN (RON): <span style="font-size: 10px">{{ $factura->furnizor_iban_ron }}</span>
                        <br>
                        Banca: {{ $factura->furnizor_iban_ron_banca }}
                        <br>
                        Capital social: {{ $factura->furnizor_capital_social }}
                    </td>
                    <td valign="top" style="border-width:0px; padding:2px; margin:0rem; width:28%; text-align:center;">
                        <h1>FACTURA</h1>
                        <div style="border:1px solid black;">
                            Seria <b>{{ $factura->seria }}</b> nr. <b>{{ $factura->numar }}</b>
                            <br>
                            Data (zi/luna/an): <b>{{ $factura->data ? Carbon::parse($factura->data)->isoFormat("DD/MM/YYYY") : '' }}</b>
                            <br>
                            Cota TVA: {{ $factura->procentTva->nume }}%
                            @if ($factura->procentTva->nume === "0")
                                SDD art. 294 - conform Cod Fiscal
                            @endif
                        </div>
                        <br>
                        <img src="{{ public_path('images/logo-2.png') }}" width="100%">
                    </td>
                    <td valign="top" style="border-width:0px; padding:5px; margin:0rem; width:36%;">
                        Client: {{ $factura->client_nume }}
                        @if ($factura->client_reg_com)
                            <br>
                            Reg. com.: {{ $factura->client_reg_com }}
                        @endif
                        <br>
                        CIF: {{ $factura->client_cif }}
                        <br>
                        Adresa: {{ $factura->client_adresa }}
                        <br>
                        Tara: {{ $factura->clientTara->nume ?? '' }}
                    </td>
                </tr>
            </table>

            <br>

            <table>
                <tr style=" border:0px;">
                    {{-- daca are chitanta, se va scrie aceasta in josul paginii, deci nu mai este nevoie de lasat mult loc gol --}}
                    {{-- daca sunt multe produse, nu se mai lasa spatiu gol, pentru ca va arata urat pe a doua pagina --}}
                    <td valign="top" style="margin:0%; padding: 0%; border:0px;
                        {{ $factura->chitante->first()
                        ?
                            'height: 150px;'
                        :
                            (($factura->produse->count() < 10) ? 'height: 550px;' : '')
                        }}">
                        <table style="padding: 0%; border:0px;">
                            <tr valign="" style="border:0px;">
                                <th width="29px">
                                    #
                                </th>
                                {{-- <th style="width: 40%"> --}}
                                <th width="330px">
                                    Denumirea produselor sau a serviciilor
                                </th>
                                <th width="35px">
                                    U.M.
                                </th>
                                <th width="35px">
                                    Cant.
                                </th>
                                <th width="81px" style="text-align:right;">
                                    Pret unitar
                                    <br>
                                    (fara&nbsp;TVA)
                                    <br>
                                    -{{ $factura->moneda->nume ?? '' }}-
                                </th>
                                <th width="81px" style="text-align:right;">
                                    Valoarea
                                    <br>
                                    -{{ $factura->moneda->nume ?? '' }}-
                                </th>
                                <th width="81px" style="border-right:0px; text-align:right;">
                                    Valoarea TVA
                                    <br>
                                    -{{ $factura->moneda->nume ?? '' }}-
                                </th>
                            </tr>
                            {{-- <tr>
                                <td style="text-align:center">
                                    0
                                </td>
                                <td style="text-align:center;">
                                    1
                                </td>
                                <td style="text-align:center">
                                    2
                                </td>
                                <td style="text-align:center">
                                    3
                                </td>
                                <td style="text-align:center">
                                    4
                                </td>
                                <td style="text-align:center">
                                    5 (3x4)
                                </td>
                                <td style="text-align:center">
                                    6
                                </td>
                            </tr> --}}
                            @foreach ($factura->produse as $produs)
                                <tr valign="top" style="border-left:0px; border-right:0px">
                                    <td style="width:29px; text-align:center;">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td style="width:330px; text-align:left;">
                                        {{ $produs->denumire ?? '' }}
                                    </td>
                                    <td style="width:35px; text-align:center;">
                                        {{ $produs->um ?? '' }}
                                    </td>
                                    <td style="width:35px; text-align:center;">
                                        {{ $produs->cantitate ?? '' }}
                                    </td>
                                    <td style="width:81px; text-align:right;">
                                        {{ $produs->pret_unitar_fara_tva ?? '' }}
                                    </td>
                                    <td style="width:81px; text-align:right;">
                                        {{ $produs->valoare ?? '' }}
                                    </td>
                                    <td style="width:81px; text-align:right;">
                                        {{ $produs->valoare_tva ?? '' }}
                                    </td>
                                </tr>
                            @endforeach
                                            <tr style="border:0px">
                                                <td colspan="4"></td>
                                                <td valign="middle" style="border-bottom:1px solid black">
                                                    Total
                                                </td>
                                                <td style="text-align: right; border-bottom:1px solid black">
                                                    @if (($factura->moneda->nume ?? '') === "RON")
                                                        {{ $factura->total_fara_tva_lei }}lei
                                                    @else
                                                        {{ $factura->total_fara_tva_moneda }}{{ $factura->moneda->nume ?? '' }}
                                                        <br>
                                                        <span style="font-size: 90%">({{ $factura->total_fara_tva_lei }}lei)</span>
                                                    @endif
                                                </td>
                                                <td style="text-align: right; border-bottom:1px solid black">
                                                    @if (($factura->moneda->nume ?? '') === "RON")
                                                        {{ $factura->total_tva_lei }}lei
                                                    @else
                                                        {{ $factura->total_tva_moneda }}{{ $factura->moneda->nume ?? '' }}
                                                        <br>
                                                        <span style="font-size: 90%">({{ $factura->total_tva_lei }}lei)</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr style="border:0px">
                                                <td colspan="4" style="border:0px"></td>
                                                <td>
                                                    Total plata
                                                </td>
                                                <td colspan="2" style="text-align: right;">
                                                    @if (($factura->moneda->nume ?? '') === "RON")
                                                        {{ $factura->total_plata_lei }}lei
                                                    @else
                                                        {{ $factura->total_moneda }}{{ $factura->moneda->nume ?? '' }}
                                                        <br>
                                                        <span style="font-size: 90%">({{ $factura->total_lei }} lei)</span>
                                                    @endif
                                                </td>
                                            </tr>
                        </table>
                    </td>
                </tr>
                <tr style="border:0px">
                    {{-- daca are chitanta, se va scrie aceasta in josul paginii, deci nu mai este nevoie de lasat mult loc gol --}}
                    {{-- daca sunt multe produse, nu se mai lasa spatiu gol, pentru ca va arata urat pe a doua pagina --}}
                    <td valign="bottom" style="margin:0%; padding: 0%; border:0px;
                        {{ (!$factura->chitante->first() && ($factura->produse->count() < 10)) ? 'height: 220px;' : '' }}">
                        <table style="padding: 0%; border:0px">
                            @if (($factura->moneda->nume ?? '') !== "RON")
                                <tr style="border:0px">
                                    <td colspan="2" style="border-bottom:1px solid black; border-top:1px solid black">
                                        Curs 1 {{ $factura->moneda->nume ?? '' }} = {{ $factura->curs_moneda + 0 }} lei
                                    </td>
                                </tr>
                            @endif
                            @if ($factura->mentiuni)
                                <tr style="border:0px">
                                    <td colspan="2" style="border-bottom:1px solid black;">
                                        {{ $factura->mentiuni }}
                                    </td>
                                </tr>
                            @endif
                            @if ($factura->facturaOriginala)
                                <tr style="border:0px;">
                                    <td colspan="2" style="border-bottom:1px solid black;">
                                        Storno factura seria {{ $factura->facturaOriginala->seria }} nr. {{ $factura->facturaOriginala->numar }} din data {{ $factura->facturaOriginala->data ? Carbon::parse($factura->facturaOriginala->data)->isoFormat("DD/MM/YYYY") : '' }}</b>
                                    </td>
                                </tr>
                            @endif
                                <tr style="border:0px">
                                    <td style="padding-left:20px; border-bottom:1px solid black;">
                                        Intocmit de: {{ $factura->intocmit_de }}
                                        @if($factura->cnp)
                                            <br>
                                            CNP: {{ $factura->cnp }}
                                        @endif
                                        @if($factura->aviz_insotire)
                                            <br>
                                            Aviz insotire: {{ $factura->aviz_insotire }}
                                        @endif
                                    </td>
                                    <td style="border-bottom:1px solid black;">
                                        @if($factura->delegat)
                                            Numele delegatului: {{ $factura->delegat }}
                                        @endif
                                        @if($factura->buletin)
                                            <br>
                                            B.I/C.I: {{ $factura->buletin }}
                                        @endif
                                        @if($factura->auto)
                                            <br>
                                            Mijloc transport: {{ $factura->auto }}
                                        @endif
                                    </td>
                                </tr>
                                <tr style="border:0px">
                                    <td colspan="2" style="border-bottom:1px solid black;">
                                        <br>
                                        Expedierea s-a efectuat in prezenta noastra la data de .......................................... ora ...................
                                    </td>
                                </tr>
                                <tr style="border:0px">
                                    <td>
                                        <br>
                                        Semnaturile:
                                    </td>
                                    <td>
                                        <br>
                                        Semnatura de primire:
                                    </td>
                                </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <p style="margin:0px 0px; padding:0px 0px 0px 10px; color:rgb(100, 100, 100);">
                Factura este valabila fara semnatura si stampila, conform art. 319 alin. 29 din legea 227/2015.
            </p>
            <p style="margin:0px 0px; padding:0px 10px 0px 10px; text-align:right; color:rgb(100, 100, 100);">
            @if (($factura->moneda->nume ?? '') === "RON")
                <a href="https://validsoftware.ro/dezvoltare-aplicatii-web-personalizate/" class="text-white" target="_blank" style="text-decoration:none; color:cornflowerblue">
                    Aplicație web</a> dezvoltată de
                <a href="https://validsoftware.ro/" class="text-white" target="_blank" style="text-decoration:none; color:rgb(61, 125, 245)">validsoftware.ro</a>
            @else
                <a href="https://validsoftware.ro/dezvoltare-aplicatii-web-personalizate/" class="text-white" target="_blank" style="text-decoration:none; color:rgb(61, 125, 245)">
                    Web application</a> developed by
                <a href="https://validsoftware.ro/" class="text-white" target="_blank" style="text-decoration:none; color:rgb(61, 125, 245)">validsoftware.ro</a>
            @endif
            </p>
        </div>


        @if ($factura->chitante->first())
            <div style="page-break-inside: avoid; margin-top:50px">

                <table style="border:1px solid black;">
                    <tr valign="" style="border: 0px;">
                        <td style="border-width:0px; padding:5px; margin:0rem; width:50%;">
                            Furnizor: {{ $factura->furnizor_nume }}
                            <br>
                            Reg. com.: {{ $factura->furnizor_reg_com }}
                            <br>
                            CIF: {{ $factura->furnizor_cif }}
                            <br>
                            Adresa: {{ $factura->furnizor_adresa }}
                            <br>
                            {{ $factura->furnizor_banca }}
                            <br>
                            SWIFT CODE : {{ $factura->furnizor_swift_code }}
                            <br>
                            IBAN (EUR): <span style="font-size: 10px">{{ $factura->furnizor_iban_eur }}</span>
                            <br>
                            Banca: {{ $factura->furnizor_iban_eur_banca }}
                            <br>
                            IBAN (RON): <span style="font-size: 10px">{{ $factura->furnizor_iban_ron }}</span>
                            <br>
                            Banca: {{ $factura->furnizor_iban_ron_banca }}
                            <br>
                            Capital social: {{ $factura->furnizor_capital_social }}
                        </td>
                        <td valign="top" style="border-width:0px; padding:2px; margin:0rem; width:45%; text-align:center;">
                            <img src="{{ public_path('images/logo-2.png') }}" width="200px">
                            <h1>CHITANTA</h1>
                            <div style="border:1px solid black;">
                                Seria <b>{{ $factura->chitante->first()->seria ?? '' }}</b> nr. <b>{{ $factura->chitante->first()->numar ?? '' }}</b>
                                <br>
                                Data (zi/luna/an): <b>{{ $factura->chitante->first()->data ? Carbon::parse($factura->chitante->first()->data)->isoFormat("DD/MM/YYYY") : '' }}</b>
                            </div>
                            <br>
                        </td>
                        <td valign="top" style="border-width:0px; padding:2px; margin:0rem; width:5%; text-align:center;">
                        </td>
                    </tr>
                    <tr style="border:0px;">
                        <td colspan="3" style="border:0px; padding:5px">
                            <br>
                            Am primit de la: {{ $factura->client_nume }}, CIF: {{ $factura->client_cif }}, Reg. com.: {{ $factura->furnizor_reg_com }}
                            <br>
                            Adresa: {{ $factura->client_adresa }}
                            <br>
                            Suma de {{ $factura->chitante->first()->suma ?? '' }} Lei, adica
                            {{-- {{ $sumaInCuvinte = new NumberFormatter("ro", NumberFormatter::SPELLOUT)->format($factura->chitante->first()->suma) }} --}}
                            @php
                                $suma = $factura->chitante->first()->suma ?? 0;
                                $exp = explode('.', $suma);
                                $f = new NumberFormatter("ro_RO", NumberFormatter::SPELLOUT);
                                echo $f->format($exp[0]) . ' Lei si ' . $f->format($exp[1]) . ' bani';
                            @endphp
                            <br>
                            reprezentand contravaloarea facturii seria {{ $factura->seria }} nr {{ $factura->numar }} din data de {{ $factura->data ? Carbon::parse($factura->data)->isoFormat("DD/MM/YYYY") : '' }}
                        </td>
                    </tr>
                    <tr style="border:0px;">
                        <td style="border:0px;">
                        </td>
                        <td style="border:0px;">
                            <br>
                            Casier,
                            <br>
                            <br>
                        </td>
                        <td style="border:0px;">
                        </td>
                    </tr>
                </table>

                <p style="margin:0px 0px; padding:0px 10px 0px 10px; text-align:right; color:rgb(100, 100, 100);">
                @if (($factura->moneda->nume ?? '') === "RON")
                    <a href="https://validsoftware.ro/dezvoltare-aplicatii-web-personalizate/" class="text-white" target="_blank" style="text-decoration:none; color:cornflowerblue">
                        Aplicație web</a> dezvoltată de
                    <a href="https://validsoftware.ro/" class="text-white" target="_blank" style="text-decoration:none; color:rgb(61, 125, 245)">validsoftware.ro</a>
                @else
                    <a href="https://validsoftware.ro/dezvoltare-aplicatii-web-personalizate/" class="text-white" target="_blank" style="text-decoration:none; color:rgb(61, 125, 245)">
                        Web application</a> developed by
                    <a href="https://validsoftware.ro/" class="text-white" target="_blank" style="text-decoration:none; color:rgb(61, 125, 245)">validsoftware.ro</a>
                @endif
                </p>

            </div>
        @endif


        {{-- Here's the magic. This MUST be inside body tag. Page count / total, centered at bottom of page --}}
        {{-- <script type="text/php">
            if (isset($pdf)) {
                $text = "Pagina {PAGE_NUM} / {PAGE_COUNT}";
                $size = 10;
                $font = $fontMetrics->getFont("helvetica");
                $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                $x = ($pdf->get_width() - $width) / 2;
                $y = $pdf->get_height() - 35;
                $pdf->page_text($x, $y, $text, $font, $size);
            }
        </script> --}}


    {{-- </main> --}}
</body>

</html>
