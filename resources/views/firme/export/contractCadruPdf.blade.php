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
                            CONTRACT NUM??R {{ $firma->contract_nr }}
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

            <h2 style="margin:0; text-align:center"><b>CONTRACT CADRU DE TRANSPORT</b></h2>

            <br><br>

            <h3 style="margin:0; text-align:center"><b>CAP. I.	P??R??ILE</b></h3>
            <p style="text-indent: -20px;">
                <b>1.1. MASECO EXPRES S.R.L.</b>, CUI RO26994418, J33/359/2010, av??nd sediul ??n: mun. Suceava, Str. Baladei, Nr. 2, Bl. 25, Sc. C, Ap. 9, Suceava, Romania, c. p. 72016 ??i adresa de  coresponden????: mun. Suceava, str. F??nt??na Mare nr. 2, bl. 2A, sc. A, et. 1, ap. 4, jud Suceava, c.p. 720279, email: info@masecoexpres.net, tel.: 0752.219.049, <b>??n calitate de Expeditor</b>,
                <b>prin director general Alexandru Tcaciuc</b>,
            </p>
            <p style="text-indent: -20px;">
                <b>1.2.	{{ $firma->nume ?? '' }} </b> , CUI  {{ $firma->cui ?? '' }} , J {{ $firma->reg_com ?? '' }} , av??nd sediul / adresa de coresponden???? ??n: {{ $firma->adresa ?? '' }}, {{ $firma->oras ?? '' }}, {{ $firma->judet ?? '' }}, {{ $firma->tara->nume ?? '' }}, email: {{ $firma->email }} , tel.: {{ $firma->telefon }} , <b>??n calitate de Transportator</b>,
                <b>prin director general {{ $firma->persoana_contact }}</b> ,
            </p>
            au decis ??ncheierea prezentului contract cadru de transport (???Contractul???), cu respectarea urm??toarelor clauze:
            <br><br>

            <h3 style="margin:0; text-align:center"><b>CAP. II. OBIECTUL ??I DURATA CONTRACTULUI</b></h3>
            <p style="text-indent: -20px;"><b>2.1.</b> Obiectul Contractului const?? ??n prestarea de servicii de transport m??rfuri de c??tre C??r??u??, cu propriile camioane, ??n numele ??i pe seama Expeditorului, ??n condi??iile stabilite prin Contract.</p>
            <p style="text-indent: -20px;"><b>2.2.</b> Contractul se completeaz?? cu dispozi??iile Conven??iei CMR din 19.05.1965, Conven??iei TIR din 14.11.1975, OUG nr. 27/2011 ??i art. 1.955 - art. 2.001 Cod civil.</p>
            <p style="text-indent: -20px;"><b>2.3.</b> Contractul este ??ncheiat de p??r??i pe o perioad?? nedeterminat??.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. III. PLASAREA ??I ACCEPTAREA COMENZILOR</b></h3>
            <p style="text-indent: -20px;"><b>3.1.</b> Expeditorul va comunica ??n scris C??r??u??ului o comand?? ferm??, ce va cuprinde informa??iile necesare efectu??rii transportului: cantitatea ??i natura m??rfurilor, sta??iile ??i termenele de ??nc??rcare ??i desc??rcare, pre??ul, modul ??i termenul de plat??, eventuale observa??ii speciale.</p>
            <p style="text-indent: -20px;"><b>3.2.</b> Comanda se consider?? confirmat?? ??i acceptat?? prin acordul de voin???? manifestat de C??r??u??, exprimat ??n orice form?? ??i comunicat ??n scris Expeditorului.</p>
            <p style="text-indent: -20px;"><b>3.3.</b> Comanda plasat?? de Expeditor ??i acceptat?? de C??r??u?? este irevocabil??. Prin excep??ie, Expeditorul are dreptul de a modifica sau anula o comand?? confirmat??, ??n orice moment, ??n cazul ??n care intervin evenimente ce fac imposibil?? realizarea obiectului comenzii.</p>
            <p style="text-indent: -20px;"><b>3.4.</b> P??r??ile au convenit ca ??ntreaga coresponden???? scris?? privind plasarea ??i acceptarea comenzilor, dar ??i pe tot parcursul efectu??rii transportului, s?? se realizeze prin mijloacele de comunicare electronice utilizate de p??r??i, cum ar fi, dar f??r?? a se limita la: sms, email, Whatsapp,  Messenger, Facebook, Skype, etc., la alegerea Expeditorului (???mijloacele de comunicare electronice???).</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. IV. LIVRAREA M??RFURILOR</b></h3>
            <p style="text-indent: -20px;"><b>4.1.</b> ??nainte de ??nceperea cursei, C??r??u??ul va comunica Expeditorului, link-ul GPRS al autocamionului utilizat, cu valabilitate pe toat?? perioada cursei, ??i o copie a asigur??rii CMR, cu valabilitate pe toat?? perioada cursei (??n valoare de minim: 15.000 euro, pentru autocamioane de 3,5 tone, 50.000 euro, pentru autocamioane de 7,5 tone, sau minim 100.000 euro, pentru autocamioane de 24 tone).</p>
            <p style="text-indent: -20px;"><b>4.2.</b> C??r??u??ul este obligat s?? de??in?? licen???? de transport, valabil?? pe toat?? perioada cursei, ??i se va asigura c?? ??oferul desemnat este instruit SSM, are salariul stabilit conform legisla??iei aplicabile ??i de??ine asupra sa Declara??ia de deta??are, conform Pachetului de mobilitate.</p>
            <p style="text-indent: -20px;"><b>4.3.</b> C??r??u??ul va lua ??n primire m??rfurile, la data, ora ??i loca??ia specificate ??n comanda plasat?? de Expeditor. ??n m??sura ??n care p??r??ile nu convin altfel, ??nc??rcarea m??rfurilor ??n autocamion se va efectua de beneficiarul transportului, la sta??ia de ??nc??rcare.</p>
            <p style="text-indent: -20px;"><b>4.4.</b> C??r??u??ul se va asigura c?? la momentul ??nc??rc??rii m??rfurile sunt integrale, nedeteriorate, ambalate ??i asigurate corespunz??tor. ??n cazul constat??rii unor lipsuri ori deficien??e, C??r??u??ul va suspenda ??nc??rcarea ??i va comunica imediat Expeditorului fotografii doveditoare. Orice lipsuri ori deficien??e ??n privin??a m??rfurilor, neanun??ate in momentul ??nc??rc??rii, vor fi considerate ca fiind produse pe timpul transportului, culpa revenind exclusiv C??r??u??ului.</p>
            <p style="text-indent: -20px;"><b>4.5.</b> C??r??u??ul are responsabilitatea complet??rii documentelor de transport sau, dup?? caz, va verifica integralitatea ??i exactitatea documentelor de transport puse la dispozi??ie la sta??ia de ??nc??rcare, ??i le va semna doar dup?? remedierea eventualelor deficien??e.</p>
            <p style="text-indent: -20px;"><b>4.6.</b> Imediat dup?? ??nc??rcarea m??rfurilor, C??r??u??ul este obligat sa comunice Expeditorului fotografii ale m??rfurilor ??nc??rcate ??n autocamion ??i copii ale documentele de transport. C??r??u??ul nu va p??r??si locul de ??nc??rcare, f??r?? acordul scris din partea Expeditorului.</p>
            <p style="text-indent: -20px;"><b>4.7.</b> C??r??u??ul are obliga??ia s?? supravegheze conservarea, paza ??i integritatea bunurilor pe tot parcursul transportului, de la preluare ??i p??n?? la predarea lor c??tre destinatar, av??nd pe deplin responsabilitatea oric??ror lipsuri sau deficien??e provocate pe toat?? durata transportului. ??n cazul pierderii sau producerii unor daune m??rfurilor, pe parcursul transportului, C??r??u??ul va anun??a imediat Expeditorul ??i ??i va comunica fotografii doveditoare.</p>
            <p style="text-indent: -20px;"><b>4.8.</b> C??r??u??ul va folosit pentru transport autocamioane adecvate, dotate cu toate echipamentele necesare ??i ??n stare bun?? tehnic?? (minim: 24 buc????i chingi STF 500 DAN, 2 col??are de protective pentru fiecare ching??, 2 covora??e antiderapante pentru fiecare palet, prelat??, etc.).</p>
            <p style="text-indent: -20px;"><b>4.9.</b> Daca transportul se efectueaz?? cu autocamion de 24 tone, C??r??u??ul nu va deconecta semiremorca de capul tractor, pana la desc??rcarea m??rfurilor, f??r?? acordul scris al Expeditorului.</p>
            <p style="text-indent: -20px;"><b>4.10.</b> C??r??u??ul are obliga??ia de a preda m??rfurile, la data, ora ??i loca??ia specificate ??n comanda plasat?? de Expeditor. ??n m??sura ??n care p??r??ile nu convin altfel, desc??rcarea m??rfurilor din autocamion se va efectua de c??tre destinatarul transportului, la sta??ia de desc??rcare</p>
            <p style="text-indent: -20px;"><b>4.11.</b> C??r??u??ul va supraveghea desc??rcarea m??rfurilor ??i, ??n cazul producerii unor daune m??rfurilor ??n acest timp, va anun??a imediat Expeditorul ??i ??i va comunica fotografii doveditoare.</p>
            <p style="text-indent: -20px;"><b>4.12.</b> Imediat dup?? desc??rcarea m??rfurilor, C??r??u??ul va comunica Expeditorului copii ale CMR, semnat ??i acceptat f??r?? obiec??iuni, acte adi??ionale ??i lieferschein, prin mijloacele de comunicare electronice ??i la adresa de email. pod@masecoexpres.net. C??r??u??ul nu va p??r??si locul de desc??rcare dec??t dup?? primirea acceptului scris din partea Expeditorului.</p>
            <p style="text-indent: -20px;"><b>4.13.</b> ??n termen de maxim 7 zile lucr??toare, pentru transportul intern, sau maxim 10 zile lucr??toare, pentru transportul extern, de la efectuarea transportului, C??r??u??ul va comunica Expeditorului, prin curier, la adresa de coresponden????, ??n original: comanda de transport semnat?? ??i ??tampilat??, cele 2 exemplare ale CMR-ului ??i celelalte documente ce au ??nso??it transportul.</p>
            <p style="text-indent: -20px;"><b>4.14.</b> ??nt??rzierea comunic??rii documentelor de transport poate atrage am??narea termenului de plat??. ??n cazul pierderii sau distrugerii documentelor de transport originale, Expeditorul are dreptul de a refuza plata transportului.</p>
            <p style="text-indent: -20px;"><b>4.15.</b> C??r??u??ul declar?? c?? ??n??elege ??i accept?? c?? pot exista eventuale ??nt??rzieri (de c??teva ore) la ??nc??rcare ??i/sau la desc??rcare m??rfurilor.</p>
            <p style="text-indent: -20px;"><b>4.16.</b> ??n cazul ??n care, prin acordul p??r??ilor ??nc??rcarea ??i/sau desc??rcarea m??rfurilor se va face de c??tre Transportator, manual, C??r??u??ul are obliga??ia de a comunica imediat Expeditorului fotografii doveditoare.</p>
            <p style="text-indent: -20px;"><b>4.17.</b> Este interzis?? transbordarea, ??nc??rcarea sau desc??rcarea m??rfurilor, ??n afara sta??iilor de ??nc??rcare sau desc??rcare, f??r?? acordul scris al Expeditorului.</p>
            <p style="text-indent: -20px;"><b>4.18.</b> Este interzis?? subcontractarea comenzilor acceptate de C??r??u?? c??tre al??i transportatori, f??r?? acordul scris al Expeditorului.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. V. PLATA PRE??ULUI</b></h3>
            <p style="text-indent: -20px;"><b>5.1.</b> Pre??ul, modalitatea ??i termenul de plat?? a serviciilor de transport vor fi determinate de p??r??i prin comand?? ??i confirmare de comand??.</p>
            <p style="text-indent: -20px;"><b>5.2.</b> Dac?? p??r??ile convin asupra pre??ului transportului ??n EUR, pl????ile se vor efectua la cursul BNR din ziua emiterii facturii. C??r??u??ul are obliga??ia sa men??ioneze ??n cuprinsul facturilor contravaloarea serviciilor ??n ambele valute (RON si EURO). Lipsa acestor informa??ii, poate duce la prelungirea termenului de plat??.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. VI. R??SPUNDERE CONTRACTUAL??</b></h3>
            <p style="text-indent: -20px;"><b>6.1.</b> Nerespectarea de c??tre C??r??u?? a obliga??iilor privind livrarea m??rfurilor, dac?? nu sunt prev??zute expres ale sanc??iuni, poate atrage anularea comenzii de c??tre Expeditor, f??r?? punere ??n ??nt??rziere, ??i/sau plata de c??tre C??r??u?? de daune interese egale cu dublul pre??ului cursei anulate, sau ??n cuantumul impus de beneficiarul transportului, ??i prejudicii de imagine ??n sum?? de 500 euro.</p>
            <p style="text-indent: -20px;"><b>6.2.</b> ??nt??rzierea efectu??rii transportului de c??tre C??r??u??, poate atrage plata de daune interese de 100 euro pentru fiecare or?? de ??nt??rziere, sau ??n cuantum egal cu cele aplicate Expeditorului de c??tre beneficiarului transportului, dac?? dep????esc aceast?? valoare.</p>
            <p style="text-indent: -20px;"><b>6.3.</b> Ne??ndeplinirea de c??tre Expeditor a obliga??iilor privind plata pre??ului atrage plata c??tre C??r??u?? de penalit????i de ??nt??rziere ??n cuantum egal cu dob??nda legal?? penalizatoare stabilit?? de BNR.</p>
            <p style="text-indent: -20px;"><b>6.4.</b> P??r??ile pot agrea ??ntr-un alt mod asupra penalit????ilor contractuale.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. VII. ??NCETAREA CONTRACTULUI</b></h3>
            <p style="text-indent: -20px;"><b>7.1.</b> Contractul ??nceteaz?? ??n urm??toarele cazuri:</p>
                a)	Prin acordul scris al p??r??ilor;
                c)	Prin reziliere, de c??tre partea ??ndrept????it??, la data comunic??rii unei notific??ri scrise c??tre partea aflat?? ??n culp?? contractual??.
                d)	Prin denun??are unilateral?? de c??tre oricare dintre p??r??i, ??n termen de 30 de zile de la data comunic??rii notific??rii scrise.
            <p style="text-indent: -20px;"><b>7.2.</b> ??ncetarea total?? sau par??ial?? a Contractului, din orice cauz??, nu va aduce atingerea obliga??iilor deja scadente ??ntre p??r??i, pe care acestea sunt ??inute s?? le ??ndeplineasc?? p??n?? la ??ncetarea efectiv?? a obliga??iilor.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. VIII. FOR??A MAJOR??</b></h3>
            <p style="text-indent: -20px;"><b>8.1.</b> For??a major??, a??a cum este definit?? de lege, exonereaz?? de r??spundere partea care o invoc?? ??n condi??iile legii, dac?? se men??ine pe o perioad?? mai mare de 30 de zile.</p>
            <p style="text-indent: -20px;"><b>8.2.</b> Starea de for???? major?? trebuie notificat?? ??n prealabil ??n scris, ??n termen de 2 zile de la apari??ia cazului de for???? major?? ??i dovedit?? pe baza certificatului eliberat de Camera de Comer?? ??i Industrie autorizat?? unde a ap??rut cazul de for???? major??.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. IX CONFIDEN??IALITATE</b></h3>
            <p style="text-indent: -20px;"><b>9.1.</b> P??r??ile au obliga??ia de a p??stra confiden??ialitatea tuturor  informa??iile tehnice sau comerciale, furnizate cu ocazia execut??rii prezentului contract, fiind obligate s?? se ab??in?? de la divulgarea lor c??tre ter??i, chiar ??i dup?? ??ncetarea rela??iilor contractuale, sub sanc??iunea pl????ii de daune interese ??n cuantum de 10.000 euro.</p>
            <p style="text-indent: -20px;"><b>9.2.</b> Pe toat?? perioada valabilit????ii Contractului, C??r??u??ul nu are dreptul de a lua direct leg??tura cu beneficiarii transporturilor, f??r?? acordul expres al Expeditorului, sub sanc??iunea pl????ii de daune interese ??n cuantum de 10.000 euro.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. X. PROTEC??IA DATELOR PERSONALE</b></h3>
            <p style="text-indent: -20px;"><b>10.1.</b> P??r??ile contractului, ??n calitate de operatori, prelucreaz?? datele cu caracter personal furnizate reciproc, cu ocazia ??ncheierii ??i pe parcursul execut??rii Contractului, cum ar fi, dar f??r?? a se limita la, identitatea reprezentan??ilor ??i persoanele de contact, identitatea ??i loca??ia GPRS a ??oferilor, etc. (???persoanele vizate???), ??n scopul execut??rii Contractului ??i altor interese legitime ale p??r??ilor, cum ar fi, dar f??r?? a se limita la, prevenirea fraudei, realizarea raport??rilor interne, etc., cu respectarea Regulamentul UE 679/2016 ??i  Legii nr. 190/2018.</p>
            <p style="text-indent: -20px;"><b>11.2.</b> P??r??ile se oblig?? s?? asigure securitatea, confiden??ialitatea ??i exactitatea informa??iilor colectate, scop ??n care vor aplica toate m??surile tehnice ??i organizatorice necesare. Datele cu caracter personal nu sunt transferate ??n alte state, fiind utilizate exclusiv de p??r??i, prin prepu??i ??i ??mputernici??i.</p>
            <p style="text-indent: -20px;"><b>11.3.</b> Informa??iile sunt p??strate pe o perioad?? determinat??, conform procedurilor interne ale p??r??ilor, p??n?? la ??ndeplinirea scopului prelucr??rii, fiind ulterior ??terse.</p>
            <p style="text-indent: -20px;"><b>11.4.</b> Persoanele vizate au dreptul la informare, acces, rectificare, ??tergere, opozi??ie ??i portare a datelor personale prelucrate, prin formularea unei cereri scrise la adresele de coresponden???? ale p??r??ilor prev??zute ??n Contract.</p>
            <p style="text-indent: -20px;"><b>11.5.</b> ??n conformitate cu art. 12 ??i 13 din Regulamentul UE nr. 2016/679, p??r??ile se oblig?? s?? aduc?? la cuno??tin???? propriilor salaria??i sau ??mputernici??i aceste drepturi, ??n m??sura ??n care ace??tia sunt sau devin persoane vizate.</p>
            <p style="text-indent: -20px;"><b>11.6.</b> ??n cazul nerespect??rii obliga??iilor privind prelucrarea datelor cu caracter personal de c??tre p??r??i, persoanele vizate se pot adresa cu o reclama??ie la ANSPDCP.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. XI.  LITIGII</b></h3>
            <p style="text-indent: -20px;"><b>11.1.</b> Eventualele litigii care s-ar ivi ??n leg??tur?? cu desf????urarea Contractului dintre p??r??i vor fi solu??ionate pe cale amiabil??, iar dac?? acest lucru este imposibil, litigiul va fi depus spre solu??ionare instan??elor competente de la sediul Expeditorului.</p>
            <br>

            <h3 style="margin:0; text-align:center"><b>CAP. XII. CLAUZE FINALE</b></h3>
            <p style="text-indent: -20px;"><b>12.1.</b> Prezentul Contract a fost semnat ast??zi, {{ $firma->contract_data ? \Carbon\Carbon::parse($firma->contract_data)->isoFormat('DD.MM.YYYY') : '' }} , data intr??rii ??n vigoare.</p>
            <p style="text-indent: -20px;"><b>12.2.</b> Modificarea datelor de identitate ??i/sau de coresponden???? trebuie notificate imediat celeilalte p??r??i, sub sanc??iunea nelu??rii ??n considerare a schimb??rilor.</p>
            <p style="text-indent: -20px;"><b>12.3.</b> ??n cazul ??n care o clauz?? sau o parte a Contractului va fi declarat?? nul??, clauzele r??mase valide vor continua s??-??i produc?? efectele.</p>
            <p style="text-indent: -20px;"><b>12.4.</b> Cu excep??ia unor cazuri particulare prev??zute ??n prezentul Contract, modificarea Contractului se face numai prin act adi??ional ??ncheiat de p??r??i.</p>
            <p style="text-indent: -20px;"><b>12.5.</b> Toate termenele precizate ??n prezentul Contract sunt considerate pe zile calendaristice, dac?? nu se precizeaz?? ??n mod expres altfel.</p>
            <p style="text-indent: -20px;"><b>12.6.</b> P??r??ile declar?? c?? au studiat, ??n??eles ??i acceptat toate obliga??iile contractuale.</p>
            <p style="text-indent: -20px;"><b>12.6.</b> Contractul con??ine 2 pagini ??i a fost ??ncheiat ??n 2 exemplare, unul pentru fiecare parte.</p>
            <br>


            Semn??turi:
            <br>

            <table>
                <tr valign="top">
                    <td style="padding:0px 2px; margin:0rem; width:50%;">
                        Reprezentant Expeditor,
                        <br>
                        <br>
                        _____________________________________
                    </td>
                    <td style="padding:0px 2px; margin:0rem; width:50%;">
                        Reprezentant C??r??u??,
                        <br>
                        <br>
                        _____________________________________
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
