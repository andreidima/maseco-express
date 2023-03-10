<!DOCTYPE  html>
<html lang="ro">

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Contract</title>
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
            border-width: 0px;
            width: 100%;
            word-wrap:break-word;
        }

        th, td {
            padding: 1px 10px;
            border-width: 0px;
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

        <div>

            <table style="">
                <tr valign="" style="">
                    <td style="border-width:0px; padding:0rem; margin:0rem; width:40%; text-align:center;">
                        <img src="{{ public_path('images/logo.jpg') }}" width="250px">
                    </td>
                    <td style="border-width:0px; padding:0rem; margin:0rem; width:60%; text-align:center;">
                        <h2>
                            CONTRACT NUMĂR {{ $firma->contract_nr }}
                        </h2>
                    </td>
                </tr>
            </table>

            <br>

            <table style="font-size: 10px;">
                <tr valign="top" style="">
                    <td style="padding:2px; margin:0rem; width:50%; border:1px solid black;">
                        SC MASECO EXPRES SRL
                        <br>
                        Reg. com: J33/359/2010
                        <br>
                        CIF: RO26994418
                        <br>
                        Adresa: Str. Baladei, Nr. 2, Bl. 25, Sc. C, Ap. 9, C.P. 720159 Suceava, Jud. Suceava
                        <br>
                        BANCA TRANSILVANIA SWIFT CODE : BTRLRO22
                        <br>
                        IBAN (EUR): RO10BTRLEURCRT0400132501
                        <br>
                        Banca: BANCA TRANSILVANIA
                        <br>
                        IBAN (RON): RO60BTRLRONCRT0400132501
                        <br>
                        Banca: BANCA TRANSILVANIA
                        <br>
                        Capital social: 200200 Lei
                    </td>
                    <td style="border-width:0px; padding:2px; margin:0rem; width:50%; border:1px solid black;">
                        {{ $firma->nume ?? '' }}
                        <br>
                        Reg. com: {{ $firma->reg_com ?? '' }}
                        <br>
                        CIF: {{ $firma->cui ?? '' }}
                        <br>
                        @php
                            // dd( $firma->nume);
                        @endphp
                        Adresa: {{ $firma->adresa ?? '' }}, {{ $firma->oras ?? '' }}, {{ $firma->judet ?? '' }}, {{ $firma->tara->nume ?? '' }}
                        <br>
                        Banca(RON): {{ $firma->banca ?? '' }}
                        <br>
                        IBAN(RON): {{ $firma->cont_iban ?? '' }}
                        <br>
                        Banca(EUR): {{ $firma->banca_eur ?? '' }}
                        <br>
                        IBAN(EUR): {{ $firma->cont_iban_eur ?? '' }}
                        <br>
                    </td>
                </tr>
            </table>
        </div>

        {{-- <table>
            <tr valign="top">
                <td style="padding:0px 2px; margin:0rem; width:50%;">
                    SC Maseco Expres SRL
                    <br>
                    {{ $comanda->user->name ?? '' }}
                    <br>
                    {{ $comanda->user->telefon ?? '' }}
                    <br>
                    info@masecoexpres@gmail.com
                </td>
                <td style="padding:0px 2px; margin:0rem; width:50%;">
                    {{ $comanda->transportator->nume ?? '' }}
                    <br>
                    {{ $comanda->transportator->persoana_contact ?? '' }}
                    <br>
                    {{ $comanda->transportator->telefon ?? '' }}
                    <br>
                    {{ $comanda->transportator->email ?? '' }}
                </td>
            </tr>
        </table> --}}


        {{-- Here's the magic. This MUST be inside body tag. Page count / total, centered at bottom of page --}}
        <script type="text/php">
            if (isset($pdf)) {
                $text = "Pagina {PAGE_NUM} / {PAGE_COUNT}";
                $size = 10;
                $font = $fontMetrics->getFont("helvetica");
                $width = $fontMetrics->get_text_width($text, $font, $size) / 2;
                $x = ($pdf->get_width() - $width) / 2;
                $y = $pdf->get_height() - 35;
                $pdf->page_text($x, $y, $text, $font, $size);
            }
        </script>


    </main>
</body>

</html>
