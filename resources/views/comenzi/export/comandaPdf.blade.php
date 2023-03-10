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

        <div style="page-break-after: always">

            <table style="">
                <tr valign="" style="">
                    <td style="border-width:0px; padding:0rem; margin:0rem; width:40%; text-align:center;">
                        <img src="{{ public_path('images/logo.jpg') }}" width="250px">
                    </td>
                    <td style="border-width:0px; padding:0rem; margin:0rem; width:60%; text-align:center;">
                        <h2>
                            COMANDĂ NUMĂR
                            <br>
                            {{ $comanda->transportator_contract }} / {{ (isset($comanda->data_creare) ? (\Carbon\Carbon::parse($comanda->data_creare)->isoFormat('DD.MM.YYYY')) : '') }} - {{ $comanda->client_contract }}
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

            <br>

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
                                {{ $comanda->locuriOperareIncarcari[$i]->pivot->data_ora ?
                                    'Data ' . \Carbon\Carbon::parse($comanda->locuriOperareIncarcari[$i]->pivot->data_ora)->isoFormat('DD.MM.YYYY') .
                                    ', ora ' . \Carbon\Carbon::parse($comanda->locuriOperareIncarcari[$i]->pivot->data_ora)->isoFormat('HH:mm')
                                    : ''}}
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
                                {{ $comanda->locuriOperareIncarcari[$i]->pivot->data_ora ?
                                    'Data ' . \Carbon\Carbon::parse($comanda->locuriOperareIncarcari[$i]->pivot->data_ora)->isoFormat('DD.MM.YYYY') .
                                    ', ora ' . \Carbon\Carbon::parse($comanda->locuriOperareIncarcari[$i]->pivot->data_ora)->isoFormat('HH:mm')
                                    : ''}}
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

            <table>
                @if ($comanda->descriere_marfa)
                    <tr>
                        <td style="padding:0px 2px; margin:0rem; width:50%; border:1px solid black;">
                            Descriere marfă:
                            {{ $comanda->descriere_marfa }}
                        </td>
                    </tr>
                @endif
                @foreach ($comanda->locuriOperareIncarcari as $locOperareIncarcare)
                    @if ($locOperareIncarcare->pivot->referinta)
                        <tr>
                            <td style="padding:0px 2px; margin:0rem; width:50%; border:1px solid black;">
                                DETALII MARFĂ ÎNCĂRCARE {{ $loop->iteration }}:
                                {{ $locOperareIncarcare->pivot->referinta ?? ''}}
                            </td>
                        </tr>
                    @endif
                @endforeach
                @foreach ($comanda->locuriOperareDescarcari as $locOperareDescarcare)
                    @if ($locOperareDescarcare->pivot->referinta)
                        <tr>
                            <td style="padding:0px 2px; margin:0rem; width:50%; border:1px solid black;">
                                DETALII MARFĂ DESCĂRCARE {{ $loop->iteration }}:
                                {{ $locOperareDescarcare->pivot->referinta ?? ''}}
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
                        PREȚ TRANSPORT: {{ $comanda->transportator_valoare_contract }}
                        <br>
                        MODALITATE DE PLATĂ: {{ $comanda->transportatorMetodaDePlata->nume ?? '' }}
                        <br>
                        TERMEN DE PLATĂ: {{ $comanda->transportatorTermenDePlata->nume ?? '' }}
                    </td>
                </tr>
            </table>

            <table>
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
            </table>
        </div>

        <h2>
            COMANDĂ NUMĂR
            <br>
            {{ $comanda->transportator_contract }} / {{ (isset($comanda->data_creare) ? (\Carbon\Carbon::parse($comanda->data_creare)->isoFormat('DD.MM.YYYY')) : '') }} - {{ $comanda->client_contract }}
        </h2>

        <h5 style="text-align:center; margin-bottom: 0px;">
            ATENȚIE !!!ACEASTĂ PAGINĂ NU SE ÎNMÂNEAZĂ ȘOFERULUI!!!
            <br>
            CONTRAVALOAREA FACTURII DE TRANSPORT VA FI ACHITATĂ CU CONDIȚIA RECEPTIONĂRII FACTURII DE TRANSPORT ȘI 2 CMR-URI ÎN ORIGINAL, PLUS AVIZELE AFERENTE CURSEI, DACĂ ESTE NECESAR.
            <br>
            CMR-ul ȘI DOCUMENTELE SPECIFICE AFERENTE COMENZII TREBUIE TRIMISE ÎN FORMAT PDF ÎN ZIUA ÎN CURS AEFECTUĂRII TRANSPORTULUI LA: pod@masecoexpres.net
        </h5>
        <h4 style="text-align:center; color:red">
            !!!ADRESĂ CORESPONDENȚĂ!!!
            <br>
            BLD. ACADEMICIAN VASILE GRECU NR. 2, 720239 SUCEAVA
            <br>
            NU ACCEPTĂM DOCUMENTE TRIMISE CU POȘTA !!!!DOAR CURIERI!!!
            <br>
            ATENȚIE! FIRMA NOASTRĂ ESTE PLATITOARE DE TVA
        </h4>

        <br>

        <ol>ALTE CLAUZE SI OBSERVATII:
            <li>Aceasta comanda de transport este sustinuta de Conventiile CMR si TIR, pentru transportul specificat in continutul sau.</li>
            <li>Prezentul contract este considerat acceptat de ambele parti, pentru suma convenita si trebuie executat conform cerintelor mentionate.</li>
            <li>In cazul in care, carausul nu confirma acest transport in maxim 30 minute, comanda se considera acceptata, cu toate conditiile mentionate! Anularea se poate realiza doar in scris, in maxim 10 minute de la primirea comenzii.</li>
            <li>Anularea comenzii de transport, dupa confirmarea ei se penalizeaza cu 30.000 euro, cu chemarea in instanta.</li>
            <li>Carausul are obligatia ca in momentul efectuarii trasnportului sa detina, pentru autovehiculul declarant, aigurare CMR in valoare de 15.000 euro pentru autovehiculele de 3,5 tone, 50.000 euro pentru autovehiculele de 7,5 tone sau 100.000 euro pentru autovehiculele de 24 tone. In cazul in care, in momentul efectuarii trasnportului, carausul detine o asigurare CMR invalida, el is asuma in totalitate valoarea daunelor produse.</li>
            <li>Soferul este direct responsabil de cantitatea si calitatea marfii. El trebuie sa se asigure ca marfa pe care o incarca este nedeteriorata, iar daca la descarcare ajunge marfa deteriorate sau lipsa, carausul este obligat sa achite pagubele create + 200 euro despagubire de imagine pentru SC Maseco Expres SRL, in termen maxim de 15 zile calendaristice de la acceptarea acestei comenzi de transport.</li>
            <li>Daca transportul se efectueaza cu autovehicul de 24 tone, este interzis ca semiremorca sa se decupleze de capul tractor, pana la livrarea marfii.</li>
            <li>Autovehiculul cu care se transporta marfa trebuie sa aiba toate echipamentele necesare, precum chingi (pana la 24 bucati) care sa reziste la o tensiune de 500 DAN (STF=500DAN) fara ciupituri, coltare de protective (minim 2 bucati pentru fiecare chinga), covorase antiderapante ( minim 2 bucati pentru fiecare palet incarcat), iar prelate sa fie in stare buna. Daca soferul se prezinta la incarcare fara toate aceste echipamente mentionate sau nu are echipamentele de protective complete, carausul raspunde pentru eventuala anulare a comenzii din acesta cauza (si este obligat sa achite costurile de anulare +200 euro pentru despagubirea imaginii SC Maseco Expres SRL) sau sa achite aceste echipamente in caz de soferul le primeste de la locul de incarcare.</li>
            <li>Orice problema lagata de calitatea, cantitatea sau greutatea marfii, neanuntata in momentul incarcarii, SC Maseco Expres SRL nu raspunde de eventualele penalizari.</li>
            <li>Este interzisa transbordarea marfii sau incarcarea sau descarcarea (exceptie facand punctele de incarcare sau livrare scrise in comanda de transport) fara acordul SC Maseco Expres SRL. Fara acordul scris legat de acest aspect, factura nu va fi achitata.</li>
            <li>Dupa incarcare, carausul este obligat sa informeze SC Maseco Expres SRL cu privire la cantitatea de marfa incarcata si greutatea ei, dar sa si trimita poze, atat cu marfa cat si cu documentele primite de la incarcare. Nu se pleaca de la locul de incarcare decat dupa primirea acceptului scris din partea SC Maseco Expres SRL.</li>
            <li>Dupa descarcarea marfii, carausul are obligatia sa informeze acest aspect, iar in maxim 15 minute de la livrare sa trimita poze cu documentele semnate si stampilate de la descarcare (atat pe whatsapp sau skype, dar si pe adresa de email pod@masecoexpres.net). Nerespectarea acestei regului poate duce la prelunguirea cu 15 zile a platii.</li>
            <li>Daca transportul va fi realizat incomplet sau fara a respecta una sau mai multe din caluzele prezentei comenzii de transport, factura nu se achita.</li>
            <li>SC Maseco Expres SRL isi rezerva dreptul de a notifica carausul despre problememe ce pot aparea sau chiar sa anuleze comanda de transport.</li>
            <li>Pentru orice intarzere nejustificata la incarcare sau descarcare, carausul este obligat sa achite o despagubire de 150 euro/ora, iar pentru neprezentarea la incarcare, dupa confirmarea comenzii de transport, carausul este obligat sa achite o despagubire de 500 euro.</li>
            <li>Dupa efectuarea trasnportului, carausul are obligatia ca in maxim 7 zile lucratoare (pentru transportul intern) sau 10 zile lucratoare (pentru transportul extern), dupa efectuarea transportului, sa trimita prin curier documentele aferente acestui transport (comanda de transport confirmata cu semnatura si stampila, 2 CMR in original si alte documente specifice). Nerespectarea acestei regului poate duce la prelunguirea cu 15 zile a platii.</li>
            <li>Orice necompletarea corespunzatoare ale documentelor de transport sau deteriorarea lor duce la o penalizare de 50 euro. Pentru pierderea lor, carausul nu poate sa primeasca contravaloarea transportului ci va fi penalizat cu suma impusa de client + 200 euro pentru despagubirea imaginii SC Maseco Expres SRL.</li>
            <li>Plata transportului se face in RON sau EURO, la cursul BNR din ziua emiterii facturii. Carausul are obligatia sa mentioneze ambele sume (RON si EURO) pe factura. Lipsa acestor informatii, poate duce la prelunguirea cu 15 zile a platii.</li>
            <li>Carausul este obligat, prin acceptarea acestei comenzi, sa respecte fiecare regula mentionata. Nerespectarea termenilor poate dup ace neachitarea facturii de transport.</li>
            <li>Carausul este obligat sa detina licenta de transport valabilia si sa se asigure ca șoferul are completata Declarația De Detașare conform pachetului de Mobilitate intrat în vigoare la data de 02.02.2021 21. Se poate astepta la incarcare/descarcare cateva ore 22. Dupa primirea comenzi transportatorul are obligatia sa trimita linkul GPS al masini cu o valabilitate pe toata perioada cursei</li>
        </ol>

        <br>
        <br>

        <table>
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
        </table>


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
