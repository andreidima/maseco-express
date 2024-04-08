@extends ('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
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
                    <form  class="needs-validation" novalidate method="POST" action="/facturi-memento/salveaza/{{ $factura->id }}">
                        @csrf

                        <div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="datePicker">
                            <div class="col-lg-12 px-4 py-2 mb-0">
                                <div class="row mb-4 justify-content-center">
                                    <div class="col-lg-4 mb-1">
                                        <label for="client_nume" class="mb-0 ps-3">Client</label>
                                        <input
                                            type="text"
                                            class="form-control rounded-3 {{ $errors->has('client_nume') ? 'is-invalid' : '' }}"
                                            name="client_nume"
                                            placeholder=""
                                            value="{{ old('client_nume', $factura->client_nume) }}"
                                            disabled>
                                    </div>
                                    <div class="col-lg-4 mb-1">
                                        <label for="client_email" class="mb-0 ps-3">Email facturare<span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('client_email') ? 'is-invalid' : '' }}"
                                            name="client_email"
                                            placeholder=""
                                            value="{{ old('client_email', $factura->client_email) }}">
                                    </div>
                                    <div class="col-lg-2 mb-1">
                                        <label for="client_contract" class="mb-0 ps-3">Contract nr.<span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('client_contract') ? 'is-invalid' : '' }}"
                                            name="client_contract"
                                            placeholder=""
                                            value="{{ old('client_contract', $factura->client_contract) }}">
                                    </div>
                                    <div class="col-lg-2 mb-1">
                                        <label for="client_limba_id" class="mb-0 ps-3">Limba<span class="text-danger">*</span></label>
                                        <select name="client_limba_id" class="form-select bg-white rounded-3 {{ $errors->has('client_limba_id') ? 'is-invalid' : '' }}">
                                            <option selected></option>
                                            @foreach ($limbi as $limba)
                                                <option value="{{ $limba->id }}" {{ ($limba->id === intval(old('client_limba_id', $factura->client_limba_id ?? ''))) ? 'selected' : '' }}>{{ $limba->nume }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="row mb-4 justify-content-center">
                                    <div class="col-lg-3 mb-1">
                                        <label for="seria" class="mb-0 ps-3">Seria facturii</label>
                                        <select name="seria"
                                            class="form-select bg-white rounded-3 {{ $errors->has('seria') ? 'is-invalid' : '' }}">
                                            <option selected></option>
                                            <option value="MAS" {{ (old('seria', $factura->seria) === "MAS") ? 'selected' : '' }}>MAS</option>
                                            <option value="MSC" {{ (old('seria', $factura->seria) === "MSC") ? 'selected' : '' }}>MSC</option>
                                            <option value="MSX" {{ (old('seria', $factura->seria) === "MSX") ? 'selected' : '' }}>MSX</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-3 mb-1">
                                        <label for="numar" class="mb-0 ps-3">Numărul facturii<span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('numar') ? 'is-invalid' : '' }}"
                                            name="numar"
                                            placeholder=""
                                            value="{{ old('numar', $factura->numar) }}">
                                    </div>
                                    <div class="col-lg-3 mb-0 text-center">
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
                                    <div class="col-lg-3 mb-1">
                                        <label for="zile_scadente" class="mb-0 ps-3">Zile scadente<span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('zile_scadente') ? 'is-invalid' : '' }}"
                                            name="zile_scadente"
                                            placeholder=""
                                            value="{{ old('zile_scadente', $factura->zile_scadente) }}">
                                    </div>
                                </div>
                                <div class="row mb-4 justify-content-center">
                                    <div class="col-lg-8 mb-1">
                                        <label for="alerte_scadenta" class="mb-0 ps-3">
                                            Alerte scadență<span class="text-danger">*</span>
                                            {{-- <small class="">(se poate seta cu câte zile înainte de scadență să se trimită mementouri. Se pot introduce mai multe mementouri, cu virgulă între ele)</small> --}}
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
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
