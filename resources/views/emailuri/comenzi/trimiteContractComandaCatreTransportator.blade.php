<div style="margin:0 auto;width:100%; background-color:#eff1f0;">
    <div style="margin:0 auto; max-width:800px!important; background-color: white;">

        @include ('emailuri.headerFooter.header')

        <div style="padding:20px 20px; max-width:760px!important;margin:0 auto; font-size:18px">
            Bună ziua, <b>{{ $comanda->transportator->nume ?? '' }}</b>,
            <br><br>
            Vă trimitem atașat comanda {{ $comanda->transportator_contract }}

            {{-- format documente = 2 means digital --}}
            {{-- @if ($comanda->transportator_format_documente == "2")
                <br><br>
                Vă rugăm ca după livrarea acestui transport, să încărcați toate documentele aferente în aplicația noastră, în format digital PDF, la adresa
                <a href="{{ url('/comanda-incarcare-documente-de-catre-transportator/' . $comanda->cheie_unica) }}">
                    {{ url('/comanda-incarcare-documente-de-catre-transportator/' . $comanda->cheie_unica) }}</a>
            @endif --}}

            {{-- <br><br>
            Acesta este un mesaj automat. Vă rugăm să nu răspundeți la acest e-mail. --}}
            <br><br>
            Mulțumim!
        </div>
    </div>

    @include ('emailuri.headerFooter.footer')
</div>

