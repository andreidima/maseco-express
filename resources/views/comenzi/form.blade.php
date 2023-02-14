@csrf

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="formularComanda">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row px-2 py-2 mb-0" style="background-color:lightyellow; border-left:6px solid; border-color:goldenrod">
            <div class="col-lg-3 mb-2">
                <label for="data_creare" class="mb-0 ps-3">Dată creare<span class="text-danger">*</span></label>
                <vue-datepicker-next
                    data-veche="{{ old('data_creare', ($comanda->data_creare ? $comanda->data_creare : ((Route::currentRouteName() === "comenzi.create") ? \Carbon\Carbon::today() : '' ))) }}"
                    nume-camp-db="data_creare"
                    tip="date"
                    value-type="YYYY-MM-DD"
                    format="DD-MM-YYYY"
                    :latime="{ width: '125px' }"
                ></vue-datepicker-next>
            </div>
        </div>
        <div class="row px-2 py-4" style="background-color:#ddffff; border-left:6px solid; border-color:#2196F3; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-2 mb-2">
                <label for="zile_scadente" class="mb-0 ps-3">Zile scadențe</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('zile_scadente') ? 'is-invalid' : '' }}"
                    name="zile_scadente"
                    placeholder=""
                    value="{{ old('zile_scadente', $comanda->zile_scadente) }}">
            </div>
        </div>
        <div class="row px-2 pt-4 pb-2 mb-0" style="background-color:#B8FFB8; border-left:6px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-2 mb-2">
                <label for="persoana_contact" class="mb-0 ps-3">Persoană de contact</label>
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
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('firma_return_url') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
