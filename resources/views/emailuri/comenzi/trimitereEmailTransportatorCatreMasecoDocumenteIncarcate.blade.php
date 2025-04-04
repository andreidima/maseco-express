<div style="margin:0 auto;width:100%; background-color:#eff1f0;">
    <div style="margin:0 auto; max-width:800px!important; background-color: white;">

        @include ('emailuri.headerFooter.header')

        <div style="padding:20px 20px; max-width:760px!important;margin:0 auto; font-size:18px">
            @if ($tipEmail == 'transportatorCatreMaseco')
                Bună ziua,
                <br><br>

                {{ $comanda->transportator->nume ?? 'Transportatorul' }} a încărcat
                    @if ($categorieEmail == 'documenteTransport')
                        documentele
                    @elseif ($categorieEmail == 'facturaTransport')
                        factura
                    @endif
                la comanda {{ $comanda->transportator_contract }}.
                <br><br>
                Acest mesaj a fost trimis în același timp către <b>Maseco Expres</b>, dar și către <b>{{ $comanda->transportator->nume ?? 'transportator' }}</b>, pentru a avea confirmarea trimiterii.
            @elseif ($tipEmail == 'MasecoCatreTransportatorGoodDocuments')
                Bună ziua, <b>{{ $comanda->transportator->nume ?? '' }}</b>,
                <br><br>
                Documentele încărcate au fost verificate și validate.
                <br>
                În caz că nu ați încărcat și factura aferentă acestui transport, vă rugăm să o încărcați tot în link-ul primit, în cel mai scurt timp!
                {{-- Te rugăm sa ne trimiți în cel mai scurt timp factura aferentă acestui transport la adresa de email <a href="mailto:pod@masecoexpres.net">pod@masecoexpres.net</a> --}}
            @elseif ($tipEmail == 'MasecoCatreTransportatorBadDocuments')
                Bună ziua, <b>{{ $comanda->transportator->nume ?? '' }}</b>,
                <br><br>
                {!! nl2br($mesaj) !!}
            @endif
            {{-- <br><br> --}}
            {{-- Poți accesa direct comanda prin linkul următor <a href="{{ url('/comanda-incarcare-documente-de-catre-transportator/' . $comanda->cheie_unica) }}" target="_blank">{{ url('/comanda-incarcare-documente-de-catre-transportator/' . $comanda->cheie_unica) }}</a>. --}}
            <br><br>
            Acesta este un mesaj automat. Vă rugăm să nu răspundeți la acest e-mail.
            <br><br>
            Mulțumim!
        </div>
    </div>

    @include ('emailuri.headerFooter.footer')
</div>

