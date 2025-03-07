@extends ('layouts.app')
<script type="application/javascript">
    facturi =  {!! json_encode(old('facturi', $facturi)) !!}
</script>


@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="shadow-lg" style="border-radius: 40px 40px 40px 40px;">
                <div class="border border-secondary p-2 culoare2" style="border-radius: 40px 40px 0px 0px;">
                    <span class="badge text-light fs-5">
                        <i class="fa-solid fa-file-invoice me-1"></i>Adăugare memento factură
                    </span>
                </div>

                @include ('errors')

                <div class="card-body py-2 border border-secondary"
                    style="border-radius: 0px 0px 40px 40px;"
                >
                    {{-- Removed on 14.01.2025 - to set more clients to a command, so more invoices, not just one --}}
                    {{-- <form  class="needs-validation" novalidate method="POST" action="/facturi-memento/salveaza/{{ $factura->id }}">
                        @csrf

                        <div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="datePicker">
                            <div class="col-lg-12 px-4 py-2 mb-0">
                                <div class="row justify-content-center">
                                    <div class="col-lg-6 mb-4">
                                        <label for="client_nume" class="mb-0 ps-3">Client<span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control rounded-3 {{ $errors->has('client_nume') ? 'is-invalid' : '' }}"
                                            name="client_nume"
                                            placeholder=""
                                            value="{{ old('client_nume', $factura->client_nume) }}"
                                            >
                                    </div>
                                    <div class="col-lg-6 mb-4">
                                        <label for="client_contract" class="mb-0 ps-3">Contract nr.<span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('client_contract') ? 'is-invalid' : '' }}"
                                            name="client_contract"
                                            placeholder=""
                                            value="{{ old('client_contract', $factura->client_contract) }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-2 mb-4">
                                        <label for="client_limba_id" class="mb-0 ps-3">Limba<span class="text-danger">*</span></label>
                                        <select name="client_limba_id" class="form-select bg-white rounded-3 {{ $errors->has('client_limba_id') ? 'is-invalid' : '' }}">
                                            <option selected></option>
                                            @foreach ($limbi as $limba)
                                                <option value="{{ $limba->id }}" {{ ($limba->id === intval(old('client_limba_id', $factura->client_limba_id ?? ''))) ? 'selected' : '' }}>{{ $limba->nume }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-2 mb-4">
                                        <label for="seria" class="mb-0 ps-3">Seria facturii</label>
                                        <select name="seria"
                                            class="form-select bg-white rounded-3 {{ $errors->has('seria') ? 'is-invalid' : '' }}">
                                            <option selected></option>
                                            <option value="MAS" {{ (old('seria', $factura->seria) === "MAS") ? 'selected' : '' }}>MAS</option>
                                            <option value="MSC" {{ (old('seria', $factura->seria) === "MSC") ? 'selected' : '' }}>MSC</option>
                                            <option value="MSO" {{ (old('seria', $factura->seria) === "MSO") ? 'selected' : '' }}>MSO</option>
                                            <option value="MSX" {{ (old('seria', $factura->seria) === "MSX") ? 'selected' : '' }}>MSX</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2 mb-4">
                                        <label for="numar" class="mb-0 ps-3">Numărul facturii<span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('numar') ? 'is-invalid' : '' }}"
                                            name="numar"
                                            placeholder=""
                                            value="{{ old('numar', $factura->numar) }}">
                                    </div>
                                    <div class="col-lg-2 mb-4 text-center">
                                        <label for="data" class="mb-0 ps-0">Data facturii<span class="text-danger">*</span></label>
                                        <vue-datepicker-next
                                            data-veche="{{ old('data', $factura->data) }}"
                                            nume-camp-db="data"
                                            tip="date"
                                            value-type="YYYY-MM-DD"
                                            format="DD.MM.YYYY"
                                            :latime="{ width: '125px' }"
                                        ></vue-datepicker-next>
                                    </div>
                                    <div class="col-lg-2 mb-4">
                                        <label for="zile_scadente" class="mb-0 ps-3">Zile scadente</label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('zile_scadente') ? 'is-invalid' : '' }}"
                                            name="zile_scadente"
                                            placeholder=""
                                            value="{{ old('zile_scadente', $factura->zile_scadente) }}">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4 mb-4">
                                        <label for="client_email" class="mb-0 ps-3">Email facturare</label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('client_email') ? 'is-invalid' : '' }}"
                                            name="client_email"
                                            placeholder=""
                                            value="{{ old('client_email', $factura->client_email) }}">
                                    </div>
                                    <div class="col-lg-8 mb-4">
                                        <label for="alerte_scadenta" class="mb-0 ps-3">
                                            Alerte scadență
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('alerte_scadenta') ? 'is-invalid' : '' }}"
                                            name="alerte_scadenta"
                                            placeholder="Ex: 1,3,7"
                                            value="{{ old('alerte_scadenta', $factura->alerte_scadenta) }}">
                                        <small class="ps-3">*Se setează cu câte zile înainte de scadență să se trimită mementouri.</small>
                                        <br>
                                        <small class="ps-3">**Se pot introduce mai multe mementouri, cu virgulă între ele.</small>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 mb-4">
                                        <hr class="rounded" style="border: 3px black solid;">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-2 mb-4">
                                        <label for="factura_transportator" class="mb-0 ps-3">Factură transportator</label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('factura_transportator') ? 'is-invalid' : '' }}"
                                            name="factura_transportator"
                                            placeholder=""
                                            value="{{ old('factura_transportator', $factura->factura_transportator) }}">
                                    </div>
                                    <div class="col-lg-3 mb-4 align-items-center">
                                        <label for="data_plata_transportator" class="mb-0 me-1">Dată plată transportator</label>
                                        <vue-datepicker-next
                                            data-veche="{{ old('data_plata_transportator', $factura->data_plata_transportator) }}"
                                            nume-camp-db="data_plata_transportator"
                                            tip="date"
                                            value-type="YYYY-MM-DD"
                                            format="DD.MM.YYYY"
                                            :latime="{ width: '125px' }"
                                        ></vue-datepicker-next>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 px-4 py-2 mb-0">
                                <div class="row">
                                    <div class="col-lg-12 mb-2 d-flex justify-content-center">
                                        <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">Salvează</button>
                                        <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('ComandaReturnUrl') }}">Renunță</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form> --}}

                    {{-- Added on 14.01.2025 - to set more clients to a command, so more invoices, not just one --}}
                    <form  class="needs-validation" novalidate method="POST" action="/facturi-memento/salveaza/comanda/{{ $comanda->id }}">
                        @csrf

                        <div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="facturaMemento">
                            <div v-for="(factura, index) in facturi" class="col-lg-12 px-4 py-2 mb-0">
                                <div class="row justify-content-center">
                                    <div class="col-lg-6 mb-4">
                                        <input
                                            type="hidden"
                                            :name="'facturi[' + index + '][id]'"
                                            v-model="facturi[index].id">

                                        <label for="client_nume" class="mb-0 ps-3">Client<span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control rounded-3 {{ $errors->has('client_nume') ? 'is-invalid' : '' }}"
                                            :name="'facturi[' + index + '][client_nume]'"
                                            v-model="facturi[index].client_nume">
                                    </div>
                                    <div class="col-lg-6 mb-4">
                                        <label for="client_contract" class="mb-0 ps-3">Contract nr.<span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('client_contract') ? 'is-invalid' : '' }}"
                                            :name="'facturi[' + index + '][client_contract]'"
                                            v-model="facturi[index].client_contract">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-2 mb-4">
                                        <label for="client_limba_id" class="mb-0 ps-3">Limba<span class="text-danger">*</span></label>
                                        <select class="form-select bg-white rounded-3 {{ $errors->has('client_limba_id') ? 'is-invalid' : '' }}"
                                            :name="'facturi[' + index + '][client_limba_id]'"
                                            v-model="facturi[index].client_limba_id"
                                        >
                                            <option value="" selected></option>
                                            @foreach ($limbi as $limba)
                                                <option value="{{ $limba->id }}">{{ $limba->nume }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-2 mb-4">
                                        <label for="seria" class="mb-0 ps-3">Seria facturii</label>
                                        <select class="form-select bg-white rounded-3 {{ $errors->has('seria') ? 'is-invalid' : '' }}"
                                            :name="'facturi[' + index + '][seria]'"
                                            v-model="facturi[index].seria"
                                        >
                                            <option value="" selected></option>
                                            <option value="MAS">MAS</option>
                                            <option value="MSC">MSC</option>
                                            <option value="MSO">MSO</option>
                                            <option value="MSX">MSX</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-2 mb-4">
                                        <label for="numar" class="mb-0 ps-3">Numărul facturii<span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('client_contract') ? 'is-invalid' : '' }}"
                                            :name="'facturi[' + index + '][numar]'"
                                            v-model="facturi[index].numar">
                                    </div>
                                    <div class="col-lg-2 mb-4 text-center">
                                        <label for="data" class="mb-0 ps-0">Data facturii<span class="text-danger">*</span></label>
                                        <vue-datepicker-next
                                            :data-veche='facturi[index].data'
                                            :nume-camp-db="'facturi[' + index + '][data]'"
                                            tip="date"
                                            value-type="YYYY-MM-DD"
                                            format="DD.MM.YYYY"
                                            :latime="{ width: '125px' }"
                                        ></vue-datepicker-next>
                                    </div>
                                    <div class="col-lg-2 mb-4">
                                        <label for="zile_scadente" class="mb-0 ps-3">Zile scadente</label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('client_contract') ? 'is-invalid' : '' }}"
                                            :name="'facturi[' + index + '][zile_scadente]'"
                                            v-model="facturi[index].zile_scadente">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-4 mb-4">
                                        <label for="client_email" class="mb-0 ps-3">Email facturare</label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('client_contract') ? 'is-invalid' : '' }}"
                                            :name="'facturi[' + index + '][client_email]'"
                                            v-model="facturi[index].client_email">
                                    </div>
                                    <div class="col-lg-8 mb-4">
                                        <label for="alerte_scadenta" class="mb-0 ps-3">
                                            Alerte scadență
                                        </label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('client_contract') ? 'is-invalid' : '' }}"
                                            :name="'facturi[' + index + '][alerte_scadenta]'"
                                            v-model="facturi[index].alerte_scadenta">
                                        <small class="ps-3">*Se setează cu câte zile înainte de scadență să se trimită mementouri.</small>
                                        <br>
                                        <small class="ps-3">**Se pot introduce mai multe mementouri, cu virgulă între ele.</small>
                                    </div>
                                    {{-- <div class="col-lg-2 mb-4 d-flex justify-content-end align-items-end">
                                        <button type="button" title="Șterge factura" class="btn btn-danger" @click="this.facturi.splice(index, 1);">
                                            <span class="badge bg-danger">Șterge factura</span>
                                        </button>
                                    </div> --}}
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 mb-4">
                                        <hr class="rounded" style="border: 3px black solid;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-12 px-4 py-2 mb-0">
                                {{-- <div class="row">
                                    <div class="col-lg-12 mb-4 text-end">
                                        <button type="button" title="Adaugă factură" class="btn btn-success" @click="this.facturi.push({ /* Add default properties here */ });">
                                            <span class="badge bg-success">Adaugă factură goală</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-12 mb-4">
                                        <hr class="rounded" style="border: 3px black solid;">
                                    </div>
                                </div> --}}

                                <div class="row">
                                    <div class="col-lg-2 mb-4">
                                        <label for="factura_transportator" class="mb-0 ps-3">Factură transportator</label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('factura_transportator') ? 'is-invalid' : '' }}"
                                            name="factura_transportator"
                                            placeholder=""
                                            value="{{ old('factura_transportator', $comanda->factura_transportator) }}">
                                    </div>
                                    <div class="col-lg-3 mb-4 align-items-center">
                                        <label for="data_plata_transportator" class="mb-0 me-1">Dată plată transportator</label>
                                        <vue-datepicker-next
                                            data-veche="{{ old('data_plata_transportator', $comanda->data_plata_transportator) }}"
                                            nume-camp-db="data_plata_transportator"
                                            tip="date"
                                            value-type="YYYY-MM-DD"
                                            format="DD.MM.YYYY"
                                            :latime="{ width: '125px' }"
                                        ></vue-datepicker-next>
                                    </div>
                                </div>

                            <div class="col-lg-12 px-4 py-2 mb-0">
                                <div class="row">
                                    <div class="col-lg-12 mb-2 d-flex justify-content-center">
                                        <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">Salvează</button>
                                        <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('ComandaReturnUrl') }}">Renunță</a>
                                    </div>
                                </div>
                            </div>

                            {{-- <div class="col-lg-12 px-4 py-2 mb-0">
                                <small class="ps-5">
                                    Sistemul generează automat toate facturile comenzii prima dată când se accesează acest formular.
                                </small>
                                <br>
                                <small class="ps-5">
                                    Dacă ulterior se adaugă, modifică sau șterge un client din comandă, factura pentru acel client trebuie gestionată manual aici.
                                </small>
                                <br>
                                <small class="ps-5">
                                    Pentru a regenera toate facturile de la zero, ștergeți toate facturile din formular, salvați modificările și apoi accesați din nou formularul. Sistemul va genera automat facturi noi, bazate pe clienții actuali.
                                </small>
                            </div> --}}
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
