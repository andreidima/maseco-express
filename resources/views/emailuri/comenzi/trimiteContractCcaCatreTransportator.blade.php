<div style="margin:0 auto;width:100%; background-color:#eff1f0;">
    <div style="margin:0 auto; max-width:800px!important; background-color: white;">

        @include ('emailuri.headerFooter.header')

        <div style="padding:20px 20px; max-width:760px!important;margin:0 auto; font-size:18px">
            Bună ziua, <b>{{ $firma->nume ?? '' }}</b>,
            <br><br>
            Vă trimitem atașat Contractul Cadru.
            {{-- <br><br>
            Acesta este un mesaj automat. Vă rugăm să nu răspundeți la acest e-mail. --}}
            <br><br>
            Mulțumim!
        </div>
    </div>

    @include ('emailuri.headerFooter.footer')
</div>

