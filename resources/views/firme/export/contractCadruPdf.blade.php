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
            font-size: 11px;
            margin-top: 10px;
            margin-left: 1.4cm;
            margin-right: 0.9cm;
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
        p {
            margin: 0px;
            padding: 0px;
        }
    </style>
</head>

<body>
    {{-- <header> --}}
        {{-- <img src="{{ asset('images/contract-header.jpg') }}" width="800px"> --}}
    {{-- </header> --}}

    <main>

        <div>

            {{-- <table style="">
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

            <br> --}}

            {{-- <table style="font-size: 10px;">
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
            </table> --}}

            <h2 style="margin:0; text-align:center"><b>CONTRACT EXCLUSIVITATE SUBCONTRACTARE SERVICII TRANSPORT</b></h2>
            <h2 style="margin:0; text-align:center"><b>CCME {{ $firma->contract_nr }}/{{ $firma->contract_data ? \Carbon\Carbon::parse($firma->contract_data)->isoFormat('DD.MM.YYYY') : '' }}</b></h2>

            <br><br>

            <h3 style="margin:0; text-align:center"><b>CAP. I.	PĂRȚILE</b></h3>
            <p style="text-indent: -20px;">
                <b>1.1. MASECO EXPRES S.R.L.</b>, CUI RO26994418, J33/359/2010, având sediul în: mun. Suceava, Str. Baladei, Nr. 2, Bl. 25, Sc. C, Ap. 9, Suceava, Romania, c. p. 720159 și adresa de  corespondență: mun. Suceava, str. Fântâna Mare nr. 2, bl. 2A, sc. A, et. 1, ap. 4, jud Suceava, c.p. 720279, email: info@masecoexpres.net, tel.: 0740.800.852, <b>în calitate de Expeditor</b>,
                <b>prin director general Adrian Tcaciuc</b>,
            </p>
            <p style="text-indent: -20px;">
                <b>1.2.	{{ $firma->nume ?? '' }} </b> , CUI  {{ $firma->cui ?? '' }} , J {{ $firma->reg_com ?? '' }} , având sediul / adresa de corespondență în: {{ $firma->adresa ?? '' }}, {{ $firma->oras ?? '' }}, {{ $firma->judet ?? '' }}, {{ $firma->tara->nume ?? '' }}, email: {{ $firma->email }} , tel.: {{ $firma->telefon }} , <b>în calitate de Transportator</b>,
                <b>prin director general {{ $firma->persoana_contact }}</b> ,
            </p>
            au decis încheierea prezentului contract cadru de transport (”Contractul”), cu respectarea următoarelor clauze:
            <br><br>

            <h3 style="margin:0; text-align:center"><b>CAP. II. OBIECTUL ȘI DURATA CONTRACTULUI</b></h3>
            <p style="text-indent: -20px;"><b>2.1.</b> Obiectul Contractului constă în prestarea de servicii de transport mărfuri de către Cărăuș, cu propriile camioane, în numele și pe seama Expeditorului, în condițiile stabilite prin Contract.</p>
            <p style="text-indent: -20px;"><b>2.2.</b> Contractul se completează cu dispozițiile Convenției CMR din 19.05.1965, Convenției TIR din 14.11.1975, OUG nr. 27/2011 și art. 1.955 - art. 2.001 Cod civil.</p>
            <p style="text-indent: -20px;"><b>2.3.</b> Contractul este încheiat de părți pe o perioadă nedeterminată.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. III. PLASAREA ȘI ACCEPTAREA COMENZILOR</b></h3>
            <p style="text-indent: -20px;"><b>3.1.</b> Expeditorul va comunica în scris Cărăușului o comandă fermă, ce va cuprinde informațiile necesare efectuării transportului: cantitatea și natura mărfurilor, stațiile și termenele de încărcare și descărcare, prețul, modul și termenul de plată, eventuale observații speciale.</p>
            <p style="text-indent: -20px;"><b>3.2.</b> Comanda se consideră confirmată și acceptată prin acordul de voință manifestat de Cărăuș, exprimat în orice formă și comunicat în scris Expeditorului.</p>
            <p style="text-indent: -20px;"><b>3.3.</b> Comanda plasată de Expeditor și acceptată de Cărăuș este irevocabilă. Prin excepție, Expeditorul are dreptul de a modifica sau anula o comandă confirmată, în orice moment, în cazul în care intervin evenimente ce fac imposibilă realizarea obiectului comenzii.</p>
            <p style="text-indent: -20px;"><b>3.4.</b> Părțile au convenit ca întreaga corespondență scrisă privind plasarea și acceptarea comenzilor, dar și pe tot parcursul efectuării transportului, să se realizeze prin mijloacele de comunicare electronice utilizate de părți, cum ar fi, dar fără a se limita la: sms, email, Whatsapp,  Messenger, Facebook, Skype, etc., la alegerea Expeditorului (”mijloacele de comunicare electronice”).</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. IV. LIVRAREA MĂRFURILOR</b></h3>
            <p style="text-indent: -20px;"><b>4.1.</b> Înainte de începerea cursei, Cărăușul va comunica Expeditorului, link-ul GPRS al autocamionului utilizat, cu valabilitate pe toată perioada cursei, și o copie a asigurării CMR, cu valabilitate pe toată perioada cursei (în valoare de minim: 15.000 euro, pentru autocamioane de 3,5 tone, 50.000 euro, pentru autocamioane de 7,5 tone, sau minim 100.000 euro, pentru autocamioane de 24 tone).</p>
            <p style="text-indent: -20px;"><b>4.2.</b> Cărăușul este obligat să dețină licență de transport, valabilă pe toată perioada cursei, și se va asigura că șoferul desemnat este instruit SSM, are salariul stabilit conform legislației aplicabile și deține asupra sa Declarația de detașare, conform Pachetului de mobilitate.</p>
            <p style="text-indent: -20px;"><b>4.3.</b> Cărăușul va lua în primire mărfurile, la data, ora și locația specificate în comanda plasată de Expeditor. În măsura în care părțile nu convin altfel, încărcarea mărfurilor în autocamion se va efectua de beneficiarul transportului, la stația de încărcare.</p>
            <p style="text-indent: -20px;"><b>4.4.</b> Cărăușul se va asigura că la momentul încărcării mărfurile sunt integrale, nedeteriorate, ambalate și asigurate corespunzător. În cazul constatării unor lipsuri ori deficiențe, Cărăușul va suspenda încărcarea și va comunica imediat Expeditorului fotografii doveditoare. Orice lipsuri ori deficiențe în privința mărfurilor, neanunțate in momentul încărcării, vor fi considerate ca fiind produse pe timpul transportului, culpa revenind exclusiv Cărăușului.</p>
            <p style="text-indent: -20px;"><b>4.5.</b> Cărăușul are responsabilitatea completării documentelor de transport sau, după caz, va verifica integralitatea și exactitatea documentelor de transport puse la dispoziție la stația de încărcare, și le va semna doar după remedierea eventualelor deficiențe.</p>
            <p style="text-indent: -20px;"><b>4.6.</b> Imediat după încărcarea mărfurilor, Cărăușul este obligat sa comunice Expeditorului fotografii ale mărfurilor încărcate în autocamion și copii ale documentele de transport. Cărăușul nu va părăsi locul de încărcare, fără acordul scris din partea Expeditorului.</p>
            <p style="text-indent: -20px;"><b>4.7.</b> Cărăușul are obligația să supravegheze conservarea, paza și integritatea bunurilor pe tot parcursul transportului, de la preluare și până la predarea lor către destinatar, având pe deplin responsabilitatea oricăror lipsuri sau deficiențe provocate pe toată durata transportului. În cazul pierderii sau producerii unor daune mărfurilor, pe parcursul transportului, Cărăușul va anunța imediat Expeditorul și îi va comunica fotografii doveditoare.</p>
            <p style="text-indent: -20px;"><b>4.8.</b> Cărăușul va folosit pentru transport autocamioane adecvate, dotate cu toate echipamentele necesare și în stare bună tehnică (minim: 24 bucăți chingi STF 500 DAN, 2 colțare de protective pentru fiecare chingă, 2 covorașe antiderapante pentru fiecare palet, prelată, etc.).</p>
            <p style="text-indent: -20px;"><b>4.9.</b> Daca transportul se efectuează cu autocamion de 24 tone, Cărăușul nu va deconecta semiremorca de capul tractor, pana la descărcarea mărfurilor, fără acordul scris al Expeditorului.</p>
            <p style="text-indent: -20px;"><b>4.10.</b> Cărăușul are obligația de a preda mărfurile, la data, ora și locația specificate în comanda plasată de Expeditor. În măsura în care părțile nu convin altfel, descărcarea mărfurilor din autocamion se va efectua de către destinatarul transportului, la stația de descărcare</p>
            <p style="text-indent: -20px;"><b>4.11.</b> Cărăușul va supraveghea descărcarea mărfurilor și, în cazul producerii unor daune mărfurilor în acest timp, va anunța imediat Expeditorul și îi va comunica fotografii doveditoare.</p>
            <p style="text-indent: -20px;"><b>4.12.</b> Imediat după descărcarea mărfurilor, Cărăușul va comunica Expeditorului copii ale CMR, semnat și acceptat fără obiecțiuni, acte adiționale și lieferschein, prin mijloacele de comunicare electronice și la adresa de email. pod@masecoexpres.net. Cărăușul nu va părăsi locul de descărcare decât după primirea acceptului scris din partea Expeditorului.</p>
            <p style="text-indent: -20px;"><b>4.13.</b> În termen de maxim 7 zile lucrătoare, pentru transportul intern, sau maxim 10 zile lucrătoare, pentru transportul extern, de la efectuarea transportului, Cărăușul va comunica Expeditorului, prin curier, la adresa de corespondență, în original: comanda de transport semnată și ștampilată, cele 2 exemplare ale CMR-ului și celelalte documente ce au însoțit transportul.</p>
            <p style="text-indent: -20px;"><b>4.14.</b> Întârzierea comunicării documentelor de transport poate atrage amânarea termenului de plată. În cazul pierderii sau distrugerii documentelor de transport originale, Expeditorul are dreptul de a refuza plata transportului.</p>
            <p style="text-indent: -20px;"><b>4.15.</b> Cărăușul declară că înțelege și acceptă că pot exista eventuale întârzieri (de câteva ore) la încărcare și/sau la descărcare mărfurilor.</p>
            <p style="text-indent: -20px;"><b>4.16.</b> În cazul în care, prin acordul părților încărcarea și/sau descărcarea mărfurilor se va face de către Transportator, manual, Cărăușul are obligația de a comunica imediat Expeditorului fotografii doveditoare.</p>
            <p style="text-indent: -20px;"><b>4.17.</b> Este interzisă transbordarea, încărcarea sau descărcarea mărfurilor, în afara stațiilor de încărcare sau descărcare, fără acordul scris al Expeditorului.</p>
            <p style="text-indent: -20px;"><b>4.18.</b> Este interzisă subcontractarea comenzilor acceptate de Cărăuș către alți transportatori, fără acordul scris al Expeditorului.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. V. PLATA PREȚULUI</b></h3>
            <p style="text-indent: -20px;"><b>5.1.</b> Prețul, modalitatea și termenul de plată a serviciilor de transport vor fi determinate de părți prin comandă și confirmare de comandă.</p>
            <p style="text-indent: -20px;"><b>5.2.</b> Dacă părțile convin asupra prețului transportului în EUR, plățile se vor efectua la cursul BNR din ziua emiterii facturii. Cărăușul are obligația sa menționeze în cuprinsul facturilor contravaloarea serviciilor în ambele valute (RON si EURO). Lipsa acestor informații, poate duce la prelungirea termenului de plată.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. VI. RĂSPUNDERE CONTRACTUALĂ</b></h3>
            <p style="text-indent: -20px;"><b>6.1.</b> Nerespectarea de către Cărăuș a obligațiilor privind livrarea mărfurilor, dacă nu sunt prevăzute expres ale sancțiuni, poate atrage anularea comenzii de către Expeditor, fără punere în întârziere, și/sau plata de către Cărăuș de daune interese egale cu dublul prețului cursei anulate, sau în cuantumul impus de beneficiarul transportului, și prejudicii de imagine în sumă de 500 euro.</p>
            <p style="text-indent: -20px;"><b>6.2.</b> Întârzierea efectuării transportului de către Cărăuș, poate atrage plata de daune interese de 100 euro pentru fiecare oră de întârziere, sau în cuantum egal cu cele aplicate Expeditorului de către beneficiarului transportului, dacă depășesc această valoare.</p>
            <p style="text-indent: -20px;"><b>6.3.</b> Neîndeplinirea de către Expeditor a obligațiilor privind plata prețului atrage plata către Cărăuș de penalități de întârziere în cuantum egal cu dobânda legală penalizatoare stabilită de BNR.</p>
            <p style="text-indent: -20px;"><b>6.4.</b> Pentru cursele la care clientul nostru ne obligă să folosim aplicatia Zekju, șoferul are obligația la primirea link-ului de la noi să acceseze acel link urmând pașii. Nefolosirea link-ului gps duce la o penalitate de 50e.</p>
            <p style="text-indent: -20px;"><b>6.5.</b> Părțile pot agrea într-un alt mod asupra penalităților contractuale.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. VII. ÎNCETAREA CONTRACTULUI</b></h3>
            <p style="text-indent: -20px;"><b>7.1.</b> Contractul încetează în următoarele cazuri:</p>
                a)	Prin acordul scris al părților;
                c)	Prin reziliere, de către partea îndreptățită, la data comunicării unei notificări scrise către partea aflată în culpă contractuală.
                d)	Prin denunțare unilaterală de către oricare dintre părți, în termen de 30 de zile de la data comunicării notificării scrise.
            <p style="text-indent: -20px;"><b>7.2.</b> Încetarea totală sau parțială a Contractului, din orice cauză, nu va aduce atingerea obligațiilor deja scadente între părți, pe care acestea sunt ținute să le îndeplinească până la încetarea efectivă a obligațiilor.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. VIII. FORŢA MAJORĂ</b></h3>
            <p style="text-indent: -20px;"><b>8.1.</b> Forța majoră, așa cum este definită de lege, exonerează de răspundere partea care o invocă în condițiile legii, dacă se menține pe o perioadă mai mare de 30 de zile.</p>
            <p style="text-indent: -20px;"><b>8.2.</b> Starea de forță majoră trebuie notificată în prealabil în scris, în termen de 2 zile de la apariția cazului de forță majoră și dovedită pe baza certificatului eliberat de Camera de Comerț și Industrie autorizată unde a apărut cazul de forță majoră.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. IX CONFIDENȚIALITATE</b></h3>
            <p style="text-indent: -20px;"><b>9.1.</b> Părțile au obligația de a păstra confidențialitatea tuturor  informațiile tehnice sau comerciale, furnizate cu ocazia executării prezentului contract, fiind obligate să se abțină de la divulgarea lor către terți, chiar și după încetarea relațiilor contractuale, sub sancțiunea plății de daune interese în cuantum de 10.000 euro.</p>
            <p style="text-indent: -20px;"><b>9.2.</b> Pe toată perioada valabilității Contractului, Cărăușul nu are dreptul de a lua direct legătura cu beneficiarii transporturilor, fără acordul expres al Expeditorului, sub sancțiunea plății de daune interese în cuantum de 10.000 euro.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. X. PROTECȚIA DATELOR PERSONALE</b></h3>
            <p style="text-indent: -20px;"><b>10.1.</b> Părțile contractului, în calitate de operatori, prelucrează datele cu caracter personal furnizate reciproc, cu ocazia încheierii și pe parcursul executării Contractului, cum ar fi, dar fără a se limita la, identitatea reprezentanților și persoanele de contact, identitatea și locația GPRS a șoferilor, etc. (”persoanele vizate”), în scopul executării Contractului și altor interese legitime ale părților, cum ar fi, dar fără a se limita la, prevenirea fraudei, realizarea raportărilor interne, etc., cu respectarea Regulamentul UE 679/2016 și  Legii nr. 190/2018.</p>
            <p style="text-indent: -20px;"><b>11.2.</b> Părțile se obligă să asigure securitatea, confidențialitatea și exactitatea informațiilor colectate, scop în care vor aplica toate măsurile tehnice și organizatorice necesare. Datele cu caracter personal nu sunt transferate în alte state, fiind utilizate exclusiv de părți, prin prepuși și împuterniciți.</p>
            <p style="text-indent: -20px;"><b>11.3.</b> Informațiile sunt păstrate pe o perioadă determinată, conform procedurilor interne ale părților, până la îndeplinirea scopului prelucrării, fiind ulterior șterse.</p>
            <p style="text-indent: -20px;"><b>11.4.</b> Persoanele vizate au dreptul la informare, acces, rectificare, ștergere, opoziție și portare a datelor personale prelucrate, prin formularea unei cereri scrise la adresele de corespondență ale părților prevăzute în Contract.</p>
            <p style="text-indent: -20px;"><b>11.5.</b> În conformitate cu art. 12 și 13 din Regulamentul UE nr. 2016/679, părțile se obligă să aducă la cunoștință propriilor salariați sau împuterniciți aceste drepturi, în măsura în care aceștia sunt sau devin persoane vizate.</p>
            <p style="text-indent: -20px;"><b>11.6.</b> În cazul nerespectării obligațiilor privind prelucrarea datelor cu caracter personal de către părți, persoanele vizate se pot adresa cu o reclamație la ANSPDCP.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. XI.  LITIGII</b></h3>
            <p style="text-indent: -20px;"><b>11.1.</b> Eventualele litigii care s-ar ivi în legătură cu desfășurarea Contractului dintre părți vor fi soluționate pe cale amiabilă, iar dacă acest lucru este imposibil, litigiul va fi depus spre soluționare instanțelor competente de la sediul Expeditorului.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. XII. CLAUZE FINALE</b></h3>
            <p style="text-indent: -20px;"><b>12.1.</b> Prezentul Contract a fost semnat astăzi, {{ $firma->contract_data ? \Carbon\Carbon::parse($firma->contract_data)->isoFormat('DD.MM.YYYY') : '' }} , data intrării în vigoare.</p>
            <p style="text-indent: -20px;"><b>12.2.</b> Modificarea datelor de identitate și/sau de corespondență trebuie notificate imediat celeilalte părți, sub sancțiunea neluării în considerare a schimbărilor.</p>
            <p style="text-indent: -20px;"><b>12.3.</b> În cazul în care o clauză sau o parte a Contractului va fi declarată nulă, clauzele rămase valide vor continua să-și producă efectele.</p>
            <p style="text-indent: -20px;"><b>12.4.</b> Cu excepția unor cazuri particulare prevăzute în prezentul Contract, modificarea Contractului se face numai prin act adițional încheiat de părți.</p>
            <p style="text-indent: -20px;"><b>12.5.</b> Toate termenele precizate în prezentul Contract sunt considerate pe zile calendaristice, dacă nu se precizează în mod expres altfel.</p>
            <p style="text-indent: -20px;"><b>12.6.</b> Părțile declară că au studiat, înțeles și acceptat toate obligațiile contractuale.</p>
            <p style="text-indent: -20px;"><b>12.6.</b> Contractul conține 2 pagini și a fost încheiat în 2 exemplare, unul pentru fiecare parte.</p>
            <br>


            Semnături:
            <br>

            <table>
                <tr valign="top">
                    <td style="padding:0px 2px; margin:0rem; width:50%;">
                        Reprezentant Expeditor,
                        <br>
                        <img src="{{ public_path('images/semnatura_stampila.jpg') }}" width="143px" style="margin-left:50px">
                        {{-- <br> --}}
                        <hr width="70%" style="margin-left:0;">
                    </td>
                    <td style="padding:0px 2px; margin:0rem; width:50%;">
                        Reprezentant Cărăuș,
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <br>
                        <hr width="70%" style="margin-left:0;">
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
                $y = $pdf->get_height() - 25;
                $pdf->page_text($x, $y, $text, $font, $size);
            }
        </script>


    </main>
</body>

</html>
