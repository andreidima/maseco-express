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

        {{-- <div style="page-break-after: always"> --}}
        <div>

            <table style="">
                <tr valign="" style="">
                    <td style="border-width:0px; padding:0rem; margin:0rem; width:40%; text-align:center;">
                        <img src="{{ public_path('images/logo.jpg') }}" width="250px">
                    </td>
                    <td style="border-width:0px; padding:0rem; margin:0rem; width:60%; text-align:center;">
                        <h2 style="color: red">
                            DEBIT NOTE
                            <br>
                            ANEXĂ LA COMANDA
                            <br>
                            {{ $comanda->transportator_contract }} / {{ (isset($comanda->data_creare) ? (\Carbon\Carbon::parse($comanda->data_creare)->isoFormat('DD.MM.YYYY')) : '') }}
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
                        {{ $comanda->transportator->nume ?? '' }}
                        <br>
                        Reg. com: {{ $comanda->transportator->reg_com ?? '' }}
                        <br>
                        CIF: {{ $comanda->transportator->cui ?? '' }}
                        <br>
                        @php
                            // dd( $comanda->transportator->nume);
                        @endphp
                        Adresa: {{ $comanda->transportator->adresa ?? '' }}, {{ $comanda->transportator->oras ?? '' }}, {{ $comanda->transportator->judet ?? '' }}, {{ $comanda->transportator->tara->nume ?? '' }}
                        <br>
                        Banca(RON): {{ $comanda->transportator->banca ?? '' }}
                        <br>
                        IBAN(RON): {{ $comanda->transportator->cont_iban ?? '' }}
                        <br>
                        Banca(EUR): {{ $comanda->transportator->banca_eur ?? '' }}
                        <br>
                        IBAN(EUR): {{ $comanda->transportator->cont_iban_eur ?? '' }}
                        <br>
                    </td>
                </tr>
            </table>

            <h4 style="padding:0rem 1rem; color:red; text-align:center">
                Conform punctului 12 din comanda de transport, regăsiți costurile aferente întârzierii efectuării transportului concordat cu comanda de transport {{ $comanda->transportator_contract }}
                <br>
                {!! nl2br(e($comanda->debit_note)) !!}
            <h4>

            @php
                $nr_incarcari = $comanda->locuriOperareIncarcari ? count($comanda->locuriOperareIncarcari) : 0;
                $nr_descarcari = $comanda->locuriOperareDescarcari ? count($comanda->locuriOperareDescarcari) : 0;
            @endphp
            <table style="">
                @for ($i = 0; $i < max($nr_incarcari, $nr_descarcari); $i++)
                    <tr valign="top">
                        <td style="padding:2px; margin:0rem; width:50%; border:1px solid black; border-right: 0px;">
                            @isset ($comanda->locuriOperareIncarcari[$i])
                                ÎNCĂRCARE {{ $i+1 }}
                                <br>
                                @if ($comanda->locuriOperareIncarcari[$i]->pivot->data_ora)
                                    @php
                                        $data_ora = \Carbon\Carbon::parse($comanda->locuriOperareIncarcari[$i]->pivot->data_ora)
                                    @endphp
                                    {{ 'Data ' . $data_ora->isoFormat('DD.MM.YYYY'). ', ora ' . $data_ora->isoFormat('HH:mm') }}
                                    @if ($comanda->locuriOperareIncarcari[$i]->pivot->durata)
                                        @php
                                            $durata = \Carbon\Carbon::parse($comanda->locuriOperareIncarcari[$i]->pivot->durata)
                                        @endphp
                                        {{
                                            ' - ' . $data_ora->addHours($durata->hour)->addMinutes($durata->minute)->isoFormat('HH:mm')
                                        }}
                                    @endif
                                @endif
                                <br>
                                {{ $comanda->locuriOperareIncarcari[$i]->nume ?? ''}}
                                <br>
                                {{ $comanda->locuriOperareIncarcari[$i]->adresa ?? ''}}
                                <br>
                                {{ $comanda->locuriOperareIncarcari[$i]->cod_postal ?? ''}}, {{ $comanda->locuriOperareIncarcari[$i]->oras ?? ''}}
                                <br>
                                {{ $comanda->locuriOperareIncarcari[$i]->tara->nume ?? '' }}
                            @endisset
                        </td>
                        <td style="padding:2px; margin:0rem; width:50%; border:1px solid black; border-left: 0px;">
                            @isset ($comanda->locuriOperareDescarcari[$i])
                                DESCĂRCARE {{ $i+1 }}
                                <br>
                                @if ($comanda->locuriOperareDescarcari[$i]->pivot->data_ora)
                                    @php
                                        $data_ora = \Carbon\Carbon::parse($comanda->locuriOperareDescarcari[$i]->pivot->data_ora)
                                    @endphp
                                    {{ 'Data ' . $data_ora->isoFormat('DD.MM.YYYY'). ', ora ' . $data_ora->isoFormat('HH:mm') }}
                                    @if ($comanda->locuriOperareDescarcari[$i]->pivot->durata)
                                        @php
                                            $durata = \Carbon\Carbon::parse($comanda->locuriOperareDescarcari[$i]->pivot->durata)
                                        @endphp
                                        {{
                                            ' - ' . $data_ora->addHours($durata->hour)->addMinutes($durata->minute)->isoFormat('HH:mm')
                                        }}
                                    @endif
                                @endif
                                <br>
                                {{ $comanda->locuriOperareDescarcari[$i]->nume ?? ''}}
                                <br>
                                {{ $comanda->locuriOperareDescarcari[$i]->adresa ?? ''}}
                                <br>
                                {{ $comanda->locuriOperareDescarcari[$i]->cod_postal ?? ''}}, {{ $comanda->locuriOperareDescarcari[$i]->oras ?? ''}}
                                <br>
                                {{ $comanda->locuriOperareDescarcari[$i]->tara->nume ?? '' }}
                            @endisset
                        </td>
                    </tr>
                @endfor
            </table>

            <br>

            @if ($comanda->descriere_marfa)
                <table>
                    <tr>
                        <td style="padding:0px 2px; margin:0rem; width:50%; border:1px solid black;">
                            DESCRIERE MARFĂ:
                            {{ $comanda->descriere_marfa }}
                        </td>
                    </tr>
                </table>
                <br>
            @endif

            <table>
                @foreach ($comanda->locuriOperareIncarcari as $locOperareIncarcare)
                    @if ($locOperareIncarcare->pivot->referinta)
                        <tr>
                            <td style="padding:0px 2px; margin:0rem; width:50%; border:1px solid black;">
                                DETALII ÎNCĂRCARE {{ $loop->iteration }}:
                                {{ $locOperareIncarcare->pivot->referinta ?? ''}}
                            </td>
                        </tr>
                    @endif
                    @if ($locOperareIncarcare->pivot->observatii)
                        <tr>
                            <td style="padding:0px 2px; margin:0rem; width:50%; border:1px solid black;">
                                OBSERVAȚII ÎNCĂRCARE {{ $loop->iteration }}:
                                {{ $locOperareIncarcare->pivot->observatii ?? ''}}
                            </td>
                        </tr>
                    @endif
                @endforeach
            </table>

            <br>

            <table>
                @foreach ($comanda->locuriOperareDescarcari as $locOperareDescarcare)
                    @if ($locOperareDescarcare->pivot->referinta)
                        <tr>
                            <td style="padding:0px 2px; margin:0rem; width:50%; border:1px solid black;">
                                DETALII DESCĂRCARE {{ $loop->iteration }}:
                                {{ $locOperareDescarcare->pivot->referinta ?? ''}}
                            </td>
                        </tr>
                    @endif
                    @if ($locOperareDescarcare->pivot->observatii)
                        <tr>
                            <td style="padding:0px 2px; margin:0rem; width:50%; border:1px solid black;">
                                OBSERVAȚII DESCĂRCARE {{ $loop->iteration }}:
                                {{ $locOperareDescarcare->pivot->observatii ?? ''}}
                            </td>
                        </tr>
                    @endif
                @endforeach
            </table>



            <br>

            <table>
                <tr>
                    <td style="padding:0px 2px; margin:0rem; width:50%; border:1px solid black;">
                        NUMĂR AUTO: {{ $comanda->camion->numar_inmatriculare ?? '' }}
                        <br>
                        CONDUCĂTOR: {{ $comanda->camion->nume_sofer ?? ''}}
                        <br>
                        NUMĂR TELEFON: {{ $comanda->camion->telefon_sofer ?? ''}}
                    </td>
                </tr>
                <tr>
                    <td style="padding:0px 2px; margin:0rem; width:50%; border:1px solid black;">
                        PREȚ TRANSPORT: {{ $comanda->transportator_valoare_contract }} {{ $comanda->transportatorMoneda->nume ?? '' }}
                        {{-- Doar pentru romania se adauga mentiunea +TVA --}}
                        @if (($comanda->transportator->tara_id ?? '') === null || ($comanda->transportator->tara_id ?? '') === 0 || ($comanda->transportator->tara_id ?? '') === 141)
                            {{-- If there is no mention for TVA, it should not appear -> Ionut Ciobanu 15.11.2024 --}}
                            @if ($comanda->transportator_procent_tva_id)
                                +TVA
                            @endif
                        @endif
                        <br>
                        @if ($comanda->transportator_tarif_pe_km == 1)
                            Preț km goi: {{ $comanda->transportator_km_goi }} km * {{ $comanda->transportator_pret_km_goi }} {{ $comanda->transportatorMoneda->nume ?? '' }} = {{ $comanda->transportator_valoare_km_goi }} {{ $comanda->transportatorMoneda->nume ?? '' }}
                            <br>
                            Preț km plini: {{ $comanda->transportator_km_plini }} km * {{ $comanda->transportator_pret_km_plini }} {{ $comanda->transportatorMoneda->nume ?? '' }} = {{ $comanda->transportator_valoare_km_plini }} {{ $comanda->transportatorMoneda->nume ?? '' }}
                            <br>
                        @endif
                        @if ($comanda->transportator_pret_autostrada && ($comanda->transportator_pret_autostrada > 0))
                            Preț autostrada: {{ $comanda->transportator_pret_autostrada }} {{ $comanda->transportatorMoneda->nume ?? '' }}
                            <br>
                        @endif
                        MODALITATE DE PLATĂ: {{ $comanda->transportatorMetodaDePlata->nume ?? '' }}
                        <br>
                        TERMEN DE PLATĂ: la {{ $comanda->transportator_zile_scadente }} de zile {{ $comanda->transportatorTermenDePlata->nume ?? '' }}
                    </td>
                </tr>
            </table>
        </div>

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
