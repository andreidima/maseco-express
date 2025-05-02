<div style="margin:0 auto;width:100%; background-color:#eff1f0;">
    <div style="margin:0 auto; max-width:800px!important; background-color: white;">

        @include ('emailuri.headerFooter.header')

        <div style="padding:20px 20px; max-width:760px!important;margin:0 auto; font-size:18px">
            Bună Maseco Expres,
            <br><br>

            Asigură-te că noul client introdus este corect:
            <ul>
                <li>
                    Nume: {{ $firma->nume }}
                </li>
                <li>
                    CUI: {{ $firma->cui }}
                </li>
                <li>
                    Link direct aplicație: <a href="{{ url($firma->path($firma->tip_partener)) }}/modifica" target="_blank">{{ url($firma->path($firma->tip_partener)) }}/modifica</a>
                </li>
            </ul>

            <br>
            Acesta este un mesaj automat. Vă rugăm să nu răspundeți la acest e-mail.
            <br><br>
            Mulțumim!
        </div>
    </div>

    @include ('emailuri.headerFooter.footer')
</div>

