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

        <h2>
            COMANDĂ NUMĂR
            <br>
            {{ $comanda->transportator_contract }} / {{ (isset($comanda->data_creare) ? (\Carbon\Carbon::parse($comanda->data_creare)->isoFormat('DD.MM.YYYY')) : '') }}
        </h2>

        <h4 style="text-align:center; margin-bottom: 0px;">
            ATENȚIE!!! ACEASTĂ PAGINĂ NU SE ÎNMÂNEAZĂ ȘOFERULUI!!!
            <br>
            CONTRAVALOAREA FACTURII DE TRANSPORT VA FI ACHITATĂ CU CONDIȚIA RECEPȚIONĂRII FACTURII DE TRANSPORT ȘI 2 CMR-URI ÎN ORIGINAL, PLUS AVIZELE AFERENTE CURSEI, DACĂ ESTE NECESAR.
            {{-- <br>
            CMR-UL ȘI DOCUMENTELE SPECIFICE AFERENTE COMENZII TREBUIE TRIMISE ÎN FORMAT PDF ÎN ZIUA ÎN CURS AEFECTUĂRII TRANSPORTULUI LA: pod@masecoexpres.net --}}
        </h4>
        <h4 style="text-align:center; color:red">
            @if ($comanda->transportator_format_documente == "2")
                DACĂ DOCUMENTELE NU SUNT ÎNCĂRCATE ÎN 24 ORE DE LA FINALIZAREA TRANSPORTULUI,
                <br>
                TERMENUL DE PLATA SE PRELUNGEȘTE CU 30 DE ZILE!
                <br>
                <br>
            @endif
            !!!ADRESĂ CORESPONDENȚĂ!!!DOAR CURIERI!!!
            <br>
            STRADA FÂNTAÂNA ALBĂ, NR. 2, BL. 2A, SC. A, ET. 1, AP. 4, 720264 SUCEAVA
            <br>
            NU ACCEPTĂM DOCUMENTE TRIMISE CU POȘTA
            <br>
            ATENȚIE! FIRMA NOASTRĂ ESTE PLĂTITOARE DE TVA!
            <br>
            FACTURA SE VA EMITE OBLIGATORIU PÂNĂ PE DATA DE 5 ALE LUNII URMĂTOARE ȘI SE VA TRIMITE ÎN SPV. NERESPECTAREA ACESTOR DOUĂ CONDIȚII, DUCE LA NEPLATA FACTURII.
        </h4>

        <ol><center><b>CONDIȚII SPECIALE ALE COMENZII DE TRANSPORT</b></center>
            <li>Prezentele condiții reglementează modul de desfășurare a livrării mărfurilor, ce fac obiectul comenzii, și se completează cu prevederile contractului cadru, dispozițiile Convenției CMR, Convenției TIR, OUG nr. 27/2011 și art. 1.955 - art. 2.001 Cod civil.</li>
            <li>Comanda se consideră confirmată și acceptată prin acordul de voință manifestat de Cărăuș, exprimat în orice formă și comunicat în scris Expeditorului, prin mijloacele de comunicare electronice utilizate de părți  (ex: Skype, Whatsapp). </li>
            <li>Comanda plasată de Expeditor și acceptată de Cărăuș este irevocabilă. Prin excepție, Expeditorul are dreptul de a modifica sau anula o comandă confirmată, în orice moment, în cazul în care intervin evenimente ce fac imposibilă realizarea obiectului comenzii. </li>
            <li>Este interzisă subcontractarea comenzilor acceptate de Cărăuș către alți transportatori, fără acordul scris al Expeditorului.</li>
            <li>Înainte de începerea cursei, Cărăușul va comunica Expeditorului, link-ul GPRS al autocamionului utilizat, cu valabilitate pe toată perioada cursei, și o copie a asigurării CMR, cu valabilitate pe toată perioada cursei (în valoare de minim: 15.000 euro, pentru autocamioane de 3,5 tone, 50.000 euro, pentru autocamioane de 7,5 tone, sau minim 100.000 euro, pentru autocamioane de 24 tone).</li>
            <li>Cărăușul este obligat să dețină licență de transport, valabilă pe toată perioada cursei, și se va asigura că șoferul desemnat este instruit SSM, are drepturile salariale stabilite și plătite conform legislației aplicabile și deține asupra sa Declarația de detașare, conform Pachetului de mobilitate în vigoare.</li>
            <li>Cărăușul va folosit pentru transport autocamioane adecvate, dotate cu toate echipamentele necesare și în stare bună tehnică (minim: 24 bucăți chingi STF 500 DAN, 2 colțare de protective pentru fiecare chingă, 2 covorașe antiderapante pentru fiecare palet, prelată, etc.).</li>
            <li>Cărăușul se va asigura că la momentul încărcării mărfurile sunt integrale, nedeteriorate, ambalate și asigurate corespunzător. Orice lipsuri ori deficiențe în privința mărfurilor, neanunțate in momentul încărcării, vor fi considerate ca fiind produse pe timpul transportului, culpa revenind exclusiv Cărăușului.</li>
            <li>Imediat după încărcarea mărfurilor, Cărăușul este obligat sa comunice Expeditorului fotografii ale mărfurilor încărcate în autocamion și copii ale documentele de transport. Cărăușul nu va părăsi locul de încărcare, fără acordul scris din partea Expeditorului.</li>
            <li>Cărăușul are obligația să asigure supravegherea, conservarea, paza și integritatea bunurilor pe tot parcursul transportului, având pe deplin responsabilitatea oricăror lipsuri sau deficiențe provocate.</li>
            {{-- <li>Imediat după descărcarea mărfurilor, Cărăușul va comunica Expeditorului copii ale CMR, semnat si acceptat fără obiecțiuni, prin mijloacele de comunicare electronice utilizate de părți și la adresa de email pod@masecoexpres.net, sub sancțiunea amânării plății cursei cu până la 30 de zile. Cărăușul nu va părăsi locul de descărcare decât după primirea acceptului scris din partea Expeditorului.</li> --}}
            <li>Imediat după descărcarea mărfurilor, Cărăușul va comunica Expeditorului copii ale CMR, semnat si acceptat fără obiecțiuni, prin mijloacele de comunicare electronice utilizate de părți și încărcarea documentelor aferent cursei în linkul primit pe adresa transportatorului de mail, sub sancțiunea amânării plății cursei cu până la 30 de zile. Cărăușul nu va părăsi locul de descărcare decât după primirea acceptului scris din partea Expeditorului.</li>
            <li>Întârzierea efectuării transportului de Cărăuș, poate atrage plata de daune interese de 100 euro pentru fiecare oră de întârziere, sau în cuantum egal cu cele aplicate Expeditorului de către beneficiarului transportului, dacă au fost reclamate și depășesc această valoare.</li>
            <li>Cărăușul declară că înțelege și acceptă că pot exista eventuale întârzieri (de câteva ore) la încărcare și/sau la descărcare mărfurilor.</li>
            <li>În cazul în care, prin acordul părților încărcarea și/sau descărcarea mărfurilor se va face de către Transportator, manual, Cărăușul are obligația de a comunica imediat Expeditorului fotografii doveditoare.</li>
            <li>Este interzisă transbordarea, încărcarea sau descărcarea mărfurilor, în afara stațiilor de încărcare sau descărcare, fără acordul scris al Expeditorului. Daca transportul se efectuează cu autocamion de 24 tone, Cărăușul nu va deconecta semiremorca de capul tractor, pana la descărcarea mărfurilor, fără acordul scris al Expeditorului.</li>
            <li>În termen de maxim 7 zile lucrătoare, pentru transportul intern, sau maxim 10 zile lucrătoare, pentru transportul extern, de la efectuarea transportului, Cărăușul va comunica Expeditorului, prin curier, la adresa de corespondență, în original, comanda de transport semnată și ștampilată, cele 2 exemplare ale CMR-ului și celelalte documente ce au însoțit transportul, sub sancțiunea amânării plății curse cu până la 30 de zile. În cazul pierderii, distrugerii sau deteriorării grave a documentelor de transport originale, Expeditorul are dreptul de a refuza plata transportului.</li>
            <li>Nerespectarea de către Cărăuș a obligațiilor asumate prin prezenta comandă, poate atrage anularea comenzii de către Expeditor, fără punere în întârziere, refuzul plății cursei și/sau plata de către Cărăuș de daune interese egale cu dublul prețului cursei anulate, sau în cuantum egal cu cele aplicate Expeditorului de către beneficiarului transportului, dacă au fost reclamate și depășesc această valoare, precum și  prejudicii de imagine în sumă de 500 euro.</li>
            <li>Dacă părțile convin asupra prețului transportului în EUR, plățile se vor efectua la cursul BNR din ziua emiterii facturii. Cărăușul are obligația sa menționeze în cuprinsul facturilor contravaloarea serviciilor în ambele valute (RON si EURO). Lipsa acestor informații, poate atrage refuzul plății facturii până la remediere.</li>
            <li>Părțile se obligă să respecte legislația cu privire la protecția, confidențialitatea și prelucrarea datelor cu caracter personal, respectiv Regulamentul UE 679/2016, Legea nr. 190/2018 și orice alte acte normative în domeniu în vigoare.</li>
            <li>Pentru cursele la care clientul nostru ne obligă să folosim aplicatia Zekju, șoferul are obligația la primirea link-ului de la noi să acceseze acel link urmând pașii. Nefolosirea link-ului gps duce la o penalitate de 50e.</li>
            <li>Pentru cursele cu vamă aferent țărilor care nu fac parte din UE tranzitul se calculează fară timpi de așteptare în vamă.</li>

            {{-- <li>Aceasta comanda de transport este sustinuta de Conventiile CMR si TIR, pentru transportul specificat in continutul sau.</li>
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
            <li>Carausul este obligat sa detina licenta de transport valabilia si sa se asigure ca șoferul are completata Declarația De Detașare conform pachetului de Mobilitate intrat în vigoare la data de 02.02.2021 21. Se poate astepta la incarcare/descarcare cateva ore 22. Dupa primirea comenzi transportatorul are obligatia sa trimita linkul GPS al masini cu o valabilitate pe toata perioada cursei</li> --}}
        </ol>

        <br>
        <br>

        <table style="width:90%; margin-left: auto; margin-right: auto;">
            <tr valign="top">
                <td style="padding:0px 2px; margin:0rem; width:60%;">
                    SC Maseco Expres SRL
                    <br>
                    {{ $comanda->user->name ?? '' }}
                    <br>
                    {{ $comanda->user->telefon ?? '' }}
                    <br>
                    info@masecoexpres.net
                </td>
                <td style="padding:0px 2px; margin:0rem; width:40%;">
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
