<div style="margin:0 auto;width:100%; background-color:#eff1f0;">
    <div style="margin:0 auto; max-width:800px!important; background-color: white;">

        @include ('emailuri.headerFooter.header')

        <div style="padding:20px 20px; max-width:760px!important;margin:0 auto; font-size:18px; background-color:rgb(207, 83, 83)">
            Bună Maseco Expres,
            <br><br>
            {{-- Transportatorul a adăugat un status la Comanda {{ $comanda->transportator_contract }} --}}
            Transportatorul a adăugat un status pentru comanda {{ $comanda->primaIncarcare()->tara->nume ?? '' }} - {{ $comanda->ultimaDescarcare()->tara->nume ?? '' }} / nr. auto {{ $comanda->camion->numar_inmatriculare ?? '' }}
            <br><br>
            Data: {{ \Carbon\Carbon::parse($comanda->ultimulStatus()->created_at)->isoFormat('DD.MM.YYYY') }}
            <br>
            Mod transmitere: {{ $comanda->ultimulStatus()->mod_transmitere }}
            <br>
            Status: {{ $comanda->ultimulStatus()->status }}
            <br><br>
            Acesta este un mesaj automat. Vă rugăm să nu răspundeți la acest e-mail.
            <br><br>
            Mulțumim!
        </div>
    </div>

    @include ('emailuri.headerFooter.footer')
</div>

