@csrf

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="formularComanda">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row px-2 py-4 mb-0" style="background-color:lightyellow; border-left:6px solid; border-color:goldenrod">
            <div class="col-lg-3 mb-2">
                <label for="data_creare" class="mb-0 ps-3">Dată creare{{ (Route::currentRouteName() === "comenzi.create") ? \Carbon\Carbon::today() : ''  }}<span class="text-danger">*</span></label>
                <vue-datepicker-next
                    data-veche="{{ old('data_creare', $comanda->data_creare) }}"
                    nume-camp-db="data_creare"
                    tip="date"
                    value-type="YYYY-MM-DD"
                    format="DD-MM-YYYY"
                    :latime="{ width: '125px' }"
                ></vue-datepicker-next>
            </div>
        </div>
        <div class="row px-2 py-4" style="background-color:#ddffff; border-left:6px solid; border-color:#2196F3; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-3 mb-2">
                <label for="transportator_contract" class="mb-0 ps-3">Contract</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('transportator_contract') ? 'is-invalid' : '' }}"
                    name="transportator_contract"
                    placeholder=""
                    value="{{ old('transportator_contract', $comanda->transportator_contract) }}"
                    disabled>
            </div>
            <div class="col-lg-3 mb-2">
                <label for="transportator_limba_id" class="mb-0 ps-3">Limba</label>
                <select name="transportator_limba_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_limba_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($limbi as $limba)
                        <option value="{{ $limba->id }}" {{ ($limba->id === intval(old('transportator_limba_id', $comanda->transportator_limba_id ?? ''))) ? 'selected' : '' }}>{{ $limba->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6 mb-2">
                <label for="transportator_transportator_id" class="mb-0 ps-3">Transportator</label>
                <select name="transportator_transportator_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_transportator_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($firmeTransportatori as $firmaTransportator)
                        <option value="{{ $firmaTransportator->id }}" {{ ($firmaTransportator->id === intval(old('transportator_transportator_id', $comanda->transportator_transportator_id ?? ''))) ? 'selected' : '' }}>{{ $firmaTransportator->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 mb-2">
                <label for="transportator_valoare_contract" class="mb-0 ps-3">Valoare contract</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('transportator_valoare_contract') ? 'is-invalid' : '' }}"
                    name="transportator_valoare_contract"
                    placeholder=""
                    value="{{ old('transportator_valoare_contract', $comanda->transportator_valoare_contract) }}">
            </div>
            <div class="col-lg-3 mb-2">
                <label for="transportator_moneda_id" class="mb-0 ps-3">Monedă</label>
                <select name="transportator_moneda_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_moneda_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($monede as $moneda)
                        <option value="{{ $moneda->id }}" {{ ($moneda->id === intval(old('transportator_moneda_id', $comanda->transportator_moneda_id ?? ''))) ? 'selected' : '' }}>{{ $limba->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 mb-2">
                <label for="transportator_procent_tva_id" class="mb-0 ps-3">Procent TVA</label>
                <select name="transportator_procent_tva_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_procent_tva_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($procenteTVA as $procentTVA)
                        <option value="{{ $procentTVA->id }}" {{ ($procentTVA->id === intval(old('transportator_procent_tva_id', $comanda->transportator_moneda_id ?? ''))) ? 'selected' : '' }}>{{ $limba->nume }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row px-2 pt-4 pb-2 mb-0" style="background-color:#B8FFB8; border-left:6px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-2 mb-2">
                <label for="persoana_contact" class="mb-0 ps-3">Transportator</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('persoana_contact') ? 'is-invalid' : '' }}"
                    name="persoana_contact"
                    placeholder=""
                    value="{{ old('persoana_contact', $comanda->persoana_contact) }}">
            </div>
        </div>
        <div class="row px-2 py-2 mb-4" style="background-color:#B8FFB8; border-left:6px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-6 mb-2">
                <label for="observatii" class="form-label mb-0 ps-3">Observații</label>
                <textarea class="form-control bg-white {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
                    name="observatii" rows="3">{{ old('observatii', $comanda->observatii) }}</textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('ComandaReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
