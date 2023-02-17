@csrf

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="client">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row px-2 py-4 mb-0" style="background-color:lightyellow; border-left:6px solid; border-color:goldenrod">
            <div class="col-lg-5 mb-2">
                <label for="nume" class="mb-0 ps-3">Nume<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('nume') ? 'is-invalid' : '' }}"
                    name="nume"
                    placeholder=""
                    value="{{ old('nume', $locOperare->nume) }}"
                    required>
            </div>
            <div class="col-lg-7 mb-2">
                <label for="adresa" class="mb-0 ps-3">Adresa</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('adresa') ? 'is-invalid' : '' }}"
                    name="adresa"
                    placeholder=""
                    value="{{ old('adresa', $locOperare->adresa) }}">
            </div>
            <div class="col-lg-3 mb-2">
                <label for="tara" class="mb-0 ps-3">Țara<span class="text-danger">*</span></label>
                <select name="tara_id" class="form-select bg-white rounded-3 {{ $errors->has('tara_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($tari as $tara)
                        <option value="{{ $tara->id }}" {{ ($tara->id === intval(old('tara_id', $locOperare->tara_id ?? ''))) ? 'selected' : '' }}>{{ $tara->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 mb-2">
                <label for="judet" class="mb-0 ps-3">Județ</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('judet') ? 'is-invalid' : '' }}"
                    name="judet"
                    placeholder=""
                    value="{{ old('judet', $locOperare->judet) }}">
            </div>
            <div class="col-lg-3 mb-2">
                <label for="oras" class="mb-0 ps-3">Oraș</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('oras') ? 'is-invalid' : '' }}"
                    name="oras"
                    placeholder=""
                    value="{{ old('oras', $locOperare->oras) }}">
            </div>
            <div class="col-lg-3 mb-2">
                <label for="cod_postal" class="mb-0 ps-3">Cod poștal</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('cod_postal') ? 'is-invalid' : '' }}"
                    name="cod_postal"
                    placeholder=""
                    value="{{ old('cod_postal', $locOperare->cod_postal) }}">
            </div>
        </div>
        <div class="row px-2 py-4" style="background-color:#ddffff; border-left:6px solid; border-color:#2196F3; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-4 mb-2">
                <label for="persoana_contact" class="mb-0 ps-3">Persoană de contact</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('persoana_contact') ? 'is-invalid' : '' }}"
                    name="persoana_contact"
                    placeholder=""
                    value="{{ old('persoana_contact', $locOperare->persoana_contact) }}">
            </div>
            <div class="col-lg-4 mb-2">
                <label for="telefon" class="mb-0 ps-3">Telefon</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('telefon') ? 'is-invalid' : '' }}"
                    name="telefon"
                    placeholder=""
                    value="{{ old('telefon', $locOperare->telefon) }}">
            </div>
            <div class="col-lg-4 mb-2">
                <label for="skype" class="mb-0 ps-3">Skype</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('skype') ? 'is-invalid' : '' }}"
                    name="skype"
                    placeholder=""
                    value="{{ old('skype', $locOperare->skype) }}">
            </div>
        </div>
        <div class="row px-2 py-4 mb-4" style="background-color:#B8FFB8; border-left:6px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-8 mb-2">
                <label for="observatii" class="form-label mb-0 ps-3">Observații</label>
                <textarea class="form-control bg-white {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
                    name="observatii" rows="3">{{ old('observatii', $locOperare->observatii) }}</textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('locOperareReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
