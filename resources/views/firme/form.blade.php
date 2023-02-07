@csrf

<div class="row mb-0 p-3 d-flex border-radius: 0px 0px 40px 40px" id="client">
    <div class="col-lg-12 mb-0">

        <div class="row mb-0">
            <div class="col-lg-4 mb-5 mx-auto">
                <label for="nume" class="mb-0 ps-3">Nume<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('nume') ? 'is-invalid' : '' }}"
                    name="nume"
                    placeholder=""
                    value="{{ old('nume', $client->nume) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-5 mx-auto">
                <label for="telefon" class="mb-0 ps-3">Telefon</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('telefon') ? 'is-invalid' : '' }}"
                    name="telefon"
                    placeholder=""
                    value="{{ old('telefon', $client->telefon) }}">
            </div>
            <div class="col-lg-4 mb-5 mx-auto">
                <label for="status" class="mb-0 ps-3">Status</label>
                <select name="status" class="form-select bg-white rounded-3 {{ $errors->has('status') ? 'is-invalid' : '' }}">
                    <option value='' selected></option>
                    <option value='Contractat' {{ (old('status', $client->status) == 'Contractat') ? 'selected' : '' }}> Contractat </option>
                    <option value='Pierdut' {{ (old('status', $client->status) == 'Pierdut') ? 'selected' : '' }}> Pierdut </option>
                    <option value='In derulare' {{ (old('status', $client->status) == 'In derulare') ? 'selected' : '' }}> In derulare </option>
                </select>
            </div>
            <div class="col-lg-12 mb-5 mx-auto">
                <label for="adresa" class="mb-0 ps-3">Adresa</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('adresa') ? 'is-invalid' : '' }}"
                    name="adresa"
                    placeholder=""
                    value="{{ old('adresa', $client->adresa) }}">
            </div>
            <div class="col-lg-3 mb-5 mx-auto">
                <label for="intrare" class="mb-0 ps-xxl-3">Intrare</label>
                <vue-datepicker-next
                    data-veche="{{ old('intrare', ($client->intrare ? $client->intrare : ((Route::currentRouteName() === "clienti.create") ? \Carbon\Carbon::today() : '' ))) }}"
                    nume-camp-db="intrare"
                    tip="date"
                    value-type="YYYY-MM-DD"
                    format="DD-MM-YYYY"
                    :latime="{ width: '125px' }"
                ></vue-datepicker-next>
            </div>
            <div class="col-lg-3 mb-5 mx-auto">
                <label for="lansare" class="mb-0 ps-xxl-3">Lansare</label>
                <vue-datepicker-next
                    data-veche="{{ old('lansare', ($client->lansare ?? '')) }}"
                    nume-camp-db="lansare"
                    tip="date"
                    value-type="YYYY-MM-DD"
                    format="DD-MM-YYYY"
                    :latime="{ width: '125px' }"
                ></vue-datepicker-next>
            </div>
            <div class="col-lg-3 mb-5 mx-auto">
                <label for="oferta_pret" class="mb-0 ps-3">Oferță preț</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('oferta_pret') ? 'is-invalid' : '' }}"
                    name="oferta_pret"
                    placeholder=""
                    value="{{ old('oferta_pret', $client->oferta_pret) }}">
            </div>
            <div class="col-lg-3 mb-5 mx-auto">
                <label for="avans" class="mb-0 ps-3">Avans</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('avans') ? 'is-invalid' : '' }}"
                    name="avans"
                    placeholder=""
                    value="{{ old('avans', $client->avans) }}">
            </div>
            <div class="col-lg-12 mb-5 mx-auto">
                <label for="observatii" class="form-label mb-0 ps-3">Observații</label>
                <textarea class="form-control bg-white {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
                    name="observatii" rows="3">{{ old('observatii', $client->observatii) }}</textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-secondary rounded-3" href="{{ Session::get('client_return_url') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
