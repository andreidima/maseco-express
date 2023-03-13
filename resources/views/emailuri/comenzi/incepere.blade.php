<div style="margin:0 auto;width:100%; background-color:#eff1f0;">
    <div style="margin:0 auto; max-width:800px!important; background-color: white;">

        @include ('emailuri.headerFooter.header')

        @php
            $data_ora_prima_incarcare = $comanda->locuriOperareIncarcari()->where('ordine', 1)->data_ora;
        @endphp


        <div style="padding:20px 20px; max-width:760px!important;margin:0 auto; font-size:18px">
            Bună Maseco Expres,
            <br><br>
            Comanda {{ $comanda->transportator_contract }} începe prima încărcare
            la ora <b>{{ $data_ora_prima_incarcare ? \Carbon\Carbon::parse($data_ora_prima_incarcare)->isoFormat('HH:mm') : '' }}</b>,
            în data de <b>{{ $data_ora_prima_incarcare ? \Carbon\Carbon::parse($data_ora_prima_incarcare)->isoFormat('DD.MM.YYYY'): '' }}</b>
            <br><br>
            Acesta este un mesaj automat. Vă rugăm să nu răspundeți la acest e-mail.
            <br><br>
            Mulțumim!
        </div>
    </div>

    @include ('emailuri.headerFooter.footer')
</div>

