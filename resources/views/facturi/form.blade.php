@csrf

@php
    use \Carbon\Carbon;
@endphp

<script type="application/javascript">
    comandaId = {!! json_encode(old('comandaId', "")) !!}
    client = {!! json_encode(old('client', "")) !!}
    cif = {!! json_encode(old('cif', "")) !!}
    adresa = {!! json_encode(old('adresa', "")) !!}
    tara = {!! json_encode(old('tara', "")) !!}
    email = {!! json_encode(old('email', "")) !!}
    produse = {!! json_encode(old('produse', "")) !!}
    valoare_contract = {!! json_encode(old('valoare_contract', "")) !!}
    moneda = {!! json_encode(old('moneda', "")) !!}
    procent_tva = {!! json_encode(old('procent_tva', "")) !!}
    zile_scadente = {!! json_encode(old('zile_scadente', "")) !!}
</script>

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="creareFactura">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-4 justify-content-center">
            <div class="col-lg-2 mb-4">
                <label for="seria" class="mb-0 ps-3">Seria facturii<span class="text-danger">*</span></label>
                <select name="seria"
                    class="form-select bg-white rounded-3 {{ $errors->has('seria') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    <option value="MAS" {{ (old('seria') === "MAS") ? 'selected' : '' }}>MAS</option>
                    <option value="MSC" {{ (old('seria') === "MSC") ? 'selected' : '' }}>MSC</option>
                    <option value="MSX" {{ (old('seria') === "MSX") ? 'selected' : '' }}>MSX</option>
                </select>
            </div>
            <div class="col-lg-2 mb-4 text-center">
                <label for="data" class="mb-0 ps-3">Data facturii<span class="text-danger">*</span></label>
                <vue-datepicker-next
                    data-veche="{{ old('data', Carbon::now()) }}"
                    nume-camp-db="data"
                    tip="date"
                    value-type="YYYY-MM-DD"
                    format="DD.MM.YYYY"
                    :latime="{ width: '125px' }"
                ></vue-datepicker-next>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="intocmit_de" class="mb-0 ps-3">Întocmit de<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('intocmit_de') ? 'is-invalid' : '' }}"
                    name="intocmit_de"
                    value="{{ old('intocmit_de', auth()->user()->name) }}">
            </div>
        </div>
        <div class="row mb-4 rounded-3 pt-4 d-flex justify-content-center"  style="background-color:#ddffff; border-left:6px solid; border-color:#2196F3; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-12 mb-4 d-flex align-items-center justify-content-center">
                <label for="comanda" class="mb-0 pe-2">Caută comanda</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3"
                    style="width:150px"
                    v-model="serieSiNumarDeCautat"
                    placeholder="Ex: MSX-1000"
                    v-on:keydown.enter.prevent=''
                    v-on:keyup.enter="axiosCautaComanda()"
                    >
                <button type="button" class="btn btn-primary text-white" @click="axiosCautaComanda()">Caută</button>
            </div>
            <div v-if="afisareMesajAtentionareNegasireComanda" class="col-lg-12 mb-4 d-flex align-items-center justify-content-center">
                <p class="px-2 rounded-3 bg-warning">Nu a fost gasită comanda!</p>
            </div>
            <div class="col-lg-6 mb-4">
                <input
                    type="hidden"
                    class="form-control bg-white rounded-3 {{ $errors->has('comandaId') ? 'is-invalid' : '' }}"
                    name="comandaId"
                    v-model="comandaId">

                <label for="client" class="mb-0 ps-3">Client<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('client') ? 'is-invalid' : '' }}"
                    name="client"
                    v-model="client">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="cif" class="mb-0 ps-3">CIF</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('cif') ? 'is-invalid' : '' }}"
                    name="cif"
                    v-model="cif">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="tara" class="mb-0 ps-3">Țara</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('tara') ? 'is-invalid' : '' }}"
                    name="tara"
                    v-model="tara">
            </div>
            <div class="col-lg-9 mb-4">
                <label for="adresa" class="mb-0 ps-3">Adresa</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('adresa') ? 'is-invalid' : '' }}"
                    name="adresa"
                    v-model="adresa">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="email" class="mb-0 ps-3">Email</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    name="email"
                    v-model="email">
            </div>
            <div class="col-lg-12 mb-4">
                <label for="produse" class="mb-0 ps-3">Produse<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('produse') ? 'is-invalid' : '' }}"
                    name="produse"
                    v-model="produse">
            </div>
            <div class="col-lg-2 mb-4">
                <label for="valoare_contract" class="mb-0 ps-3">Valoare contract<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('valoare_contract') ? 'is-invalid' : '' }}"
                    name="valoare_contract"
                    v-model="valoare_contract">
            </div>
            <div class="col-lg-2 mb-4">
                <label for="moneda" class="mb-0 ps-3">Monedă<span class="text-danger">*</span></label>
                <select name="moneda"
                    v-model="moneda"
                class="form-select bg-white rounded-3 {{ $errors->has('moneda') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($monede as $moneda)
                        <option value="{{ $moneda->id }}">{{ $moneda->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="procent_tva" class="mb-0 ps-3">Procent TVA<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('procent_tva') ? 'is-invalid' : '' }}"
                    name="procent_tva"
                    v-model="procent_tva">
            </div>
            <div class="col-lg-2 mb-4">
                <label for="zile_scadente" class="mb-0 ps-3">Zile scadente</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('zile_scadente') ? 'is-invalid' : '' }}"
                    name="zile_scadente"
                    v-model="zile_scadente">
            </div>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-4 mb-4">
                <label for="alerte_scadenta" class="mb-0 ps-3"><small>Cu câte zile înainte de scadență să se trimită memento</small></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('alerte_scadenta') ? 'is-invalid' : '' }}"
                    name="alerte_scadenta"
                    placeholder=""
                    value="{{ old('alerte_scadenta') }}">
                <small class="ps-3">
                    *Se pot introduce mai multe cu virgulă între ele (Ex: 1,3,7)
                </small>
            </div>
        </div>
    </div>
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('mementoReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
