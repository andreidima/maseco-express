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
        /* html {
            margin: 0px 0px;
        } */
        /** Define the margins of your page **/
        @page {
            margin: 0px 0px;
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
            padding: 2px 5px;
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
    {{-- <header> --}}
        {{-- <img src="{{ asset('images/contract-header.jpg') }}" width="800px"> --}}
    {{-- </header> --}}

    <main>

        <div style="">

            <table style="font-size: 10px; border:1px solid black;">
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
                        IBAN (EUR): {{ $factura->furnizor_iban_eur }}
                        <br>
                        Banca: {{ $factura->furnizor_iban_eur_banca }}
                        <br>
                        IBAN (RON): {{ $factura->furnizor_iban_ron }}
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
                            Cota TVA: {{ $factura->procent_tva }}%SDD art. 294 - conform Cod Fiscal
                        </div>
                        <br>
                        <img src="{{ public_path('images/logo-2.png') }}" width="100%">
                    </td>
                    <td valign="top" style="border-width:0px; padding:5px; margin:0rem; width:36%;">
                        Client: {{ $factura->client_nume }}
                        <br>
                        CIF: {{ $factura->client_cif }}
                        <br>
                        Adresa: {{ $factura->client_adresa }}
                        <br>
                        Tara: {{ $factura->client_tara }}
                    </td>
                </tr>
            </table>

            <table style="font-size: 10px; border:1px solid black;">
                <tr valign="" style="">
                    <td style="text-align:center">
                        Nr. crt
                    </td>
                    <td style="text-align:center">
                        Denumirea produselor sau a serviciilor
                    </td>
                    <td style="text-align:center">
                        U.M.
                    </td>
                    <td style="text-align:center">
                        Cant.
                    </td>
                    <td style="text-align:center">
                        Pret unitar
                        <br>
                        (fara TVA)
                        <br>
                        -{{ $factura->moneda }}-
                    </td>
                    <td style="text-align:center">
                        Valoarea
                        <br>
                        -{{ $factura->moneda }}-
                    </td>
                    <td style="text-align:center">
                        Valoarea TVA
                        <br>
                        -{{ $factura->moneda }}-
                    </td>
                </tr>
                <tr>
                    <td style="text-align:center">
                        0
                    </td>
                    <td style="text-align:center">
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
                </tr>
                <tr valign="top">
                    <td style="text-align:center; height:550px;">
                        1
                    </td>
                    <td style="text-align:left">
                        {{ $factura->produse()->first()->denumire ?? '' }}
                    </td>
                    <td style="text-align:center">
                        {{ $factura->produse()->first()->um ?? '' }}
                    </td>
                    <td style="text-align:center">
                        {{ $factura->produse()->first()->cantitate ?? '' }}
                    </td>
                    <td style="text-align:right">
                        {{ $factura->produse()->first()->pret_unitar ?? '' }}
                    </td>
                    <td style="text-align:right">
                        {{ $factura->produse()->first()->valoare ?? '' }}
                    </td>
                    <td style="text-align:right">
                        {{ $factura->produse()->first()->valoare_tva ?? '' }}
                    </td>
                </tr>
                @if ($factura->moneda !== "RON")
                <tr>
                    <td colspan="7">
                        Curs 1 {{ $factura->moneda }} = {{ $factura->curs_moneda }} lei
                    </td>
                </tr>
                @endif
                <tr valign="top">
                    <td colspan="4" rowspan="3">
                        Intocmit de: {{ $factura->intocmit_de }}
                        <br>
                        CNP: -
                        <br>
                        Numele delegatului: -
                        <br>
                        B.I/C.I: -
                        <br>
                        Mijloc transport: -
                        <br>
                        Expedierea s-a efectuat in prezenta noastra la data de ....................ora.........
                        <br>
                        Semnaturile:
                    </td>
                    <td>
                        Total
                    </td>
                    <td style="text-align: right">
                        @if ($factura->moneda === "RON")
                            {{ $factura->total_fara_tva_lei }} lei
                        @else
                            {{ $factura->total_fara_tva_moneda }} {{ $factura->moneda }}
                            <br>
                            ({{ $factura->total_fara_tva_lei }} lei)
                        @endif
                    </td>
                    <td style="text-align: right">
                        @if ($factura->moneda === "RON")
                            {{ $factura->total_tva_lei }} lei
                        @else
                            {{ $factura->total_tva_moneda }} {{ $factura->moneda }}
                            <br>
                            ({{ $factura->total_tva_lei }} lei)
                        @endif
                    </td>
                </tr>
                <tr>
                    <td>
                        Total plata
                    </td>
                    <td colspan="2" style="text-align: right">
                        @if ($factura->moneda === "RON")
                            {{ $factura->total_plata_lei }} lei
                        @else
                            {{ $factura->total_plata_moneda }} {{ $factura->moneda }}
                            <br>
                            ({{ $factura->total_plata_lei }} lei)
                        @endif
                    </td>
                </tr>
                <tr>
                    <td colspan="3">
                        Semnatura de primire:
                    </td>
                </tr>

            </table>

            <p style="margin:0px 0px; padding:0px 0px 0px 10px; font-size: 10px; color:rgb(100, 100, 100);">
                Factura este valabila fara semnatura si stampila, conform art. 319 alin. 29 din legea 227/2015.
            </p>
            <p style="margin:0px 0px; padding:0px 10px 0px 10px; text-align:right; font-size: 10px; color:rgb(100, 100, 100);">
            @if ($factura->moneda === "RON")
                <a href="https://validsoftware.ro/dezvoltare-aplicatii-web-personalizate/" class="text-white" target="_blank" style="text-decoration:none; color:cornflowerblue">
                    Aplicație web</a> dezvoltată de
                <a href="https://validsoftware.ro/" class="text-white" target="_blank" style="text-decoration:none; color:rgb(61, 125, 245)">validsoftware.ro</a>
            @else
                <a href="https://validsoftware.ro/dezvoltare-aplicatii-web-personalizate/" class="text-white" target="_blank" style="text-decoration:none; color:rgb(61, 125, 245)">
                    Web application</a> developed by
                <a href="https://validsoftware.ro/" class="text-white" target="_blank" style="text-decoration:none; color:rgb(61, 125, 245)">validsoftware.ro</a>
            @endif
        </div>



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


    </main>
</body>

</html>
