@csrf

@if ($tipPartener === 'clienti')
    <input type="hidden" name="tip_partener" value="1">
@elseif ($tipPartener === 'transportatori')
    <input type="hidden" name="tip_partener" value="2">
@endif

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="client">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row px-2 py-2 mb-0" style="background-color:lightyellow; border-left:6px solid; border-color:goldenrod">
            <div class="col-lg-3 mb-2">
                <label for="nume" class="mb-0 ps-3">Nume<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('nume') ? 'is-invalid' : '' }}"
                    name="nume"
                    placeholder=""
                    value="{{ old('nume', $firma->nume) }}"
                    required>
            </div>
            <div class="col-lg-2 mb-2">
                <label for="tip_partener" class="mb-0 ps-3">Tip partener<span class="text-danger">*{{ old('tip_partener') }}</span></label>
                <select name="tip_partener" class="form-select bg-white rounded-3 {{ $errors->has('tip_partener') ? 'is-invalid' : '' }}" disabled>
                    <option selected></option>
                    <option value="1"
                        {{
                            (intval(old('tip_partener', $firma->tip_partener ?? '')) === 1) ?
                                'selected' : ( ( !old('tip_partener', $firma->tip_partener ?? '') && ($tipPartener === 'clienti') ) ? 'selected' : '' )
                        }}>
                        Client
                    </option>
                    <option value="2"
                        {{
                            (intval(old('tip_partener', $firma->tip_partener ?? '')) === 2) ?
                                'selected' : ( ( !old('tip_partener', $firma->tip_partener ?? '') && ($tipPartener === 'transportatori') ) ? 'selected' : '' )
                        }}>
                        Transportator
                    </option>
                </select>
            </div>
            <div class="col-lg-2 mb-2">
                <label for="tara" class="mb-0 ps-3">Țara<span class="text-danger">*</span></label>
                <select name="tara_id" class="form-select bg-white rounded-3 {{ $errors->has('tara_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($tari as $tara)
                        <option value="{{ $tara->id }}" {{ ($tara->id === intval(old('tara_id', $firma->tara_id ?? ''))) ? 'selected' : '' }}>{{ $tara->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-2">
                <label for="cui" class="mb-0 ps-3">CUI</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('cui') ? 'is-invalid' : '' }}"
                    name="cui"
                    placeholder=""
                    value="{{ old('cui', $firma->cui) }}">
            </div>
            <div class="col-lg-2 mb-2">
                <label for="reg_com" class="mb-0 ps-3">Reg. Com.</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('reg_com') ? 'is-invalid' : '' }}"
                    name="reg_com"
                    placeholder=""
                    value="{{ old('reg_com', $firma->reg_com) }}">
            </div>
        </div>
        <div class="row px-2 py-2 pb-4 mb-0" style="background-color:lightyellow; border-left:6px solid; border-color:goldenrod">
            <div class="col-lg-2 mb-2">
                <label for="oras" class="mb-0 ps-3">Oraș</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('oras') ? 'is-invalid' : '' }}"
                    name="oras"
                    placeholder=""
                    value="{{ old('oras', $firma->oras) }}">
            </div>
            <div class="col-lg-2 mb-2">
                <label for="judet" class="mb-0 ps-3">Județ</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('judet') ? 'is-invalid' : '' }}"
                    name="judet"
                    placeholder=""
                    value="{{ old('judet', $firma->judet) }}">
            </div>
            <div class="col-lg-6 mb-2">
                <label for="adresa" class="mb-0 ps-3">Adresa</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('adresa') ? 'is-invalid' : '' }}"
                    name="adresa"
                    placeholder=""
                    value="{{ old('adresa', $firma->adresa) }}">
            </div>
            <div class="col-lg-2 mb-2">
                <label for="cod_postal" class="mb-0 ps-3">Cod poștal</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('cod_postal') ? 'is-invalid' : '' }}"
                    name="cod_postal"
                    placeholder=""
                    value="{{ old('cod_postal', $firma->cod_postal) }}">
            </div>
        </div>
        <div class="row px-2 py-4" style="background-color:#ddffff; border-left:6px solid; border-color:#2196F3; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-2 mb-2">
                <label for="banca" class="mb-0 ps-3">Banca</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('banca') ? 'is-invalid' : '' }}"
                    name="banca"
                    placeholder=""
                    value="{{ old('banca', $firma->banca) }}">
            </div>
            <div class="col-lg-3 mb-2">
                <label for="cont_iban" class="mb-0 ps-3">Cont IBAN</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('cont_iban') ? 'is-invalid' : '' }}"
                    name="cont_iban"
                    placeholder=""
                    value="{{ old('cont_iban', $firma->cont_iban) }}">
            </div>
            <div class="col-lg-2 mb-2">
                <label for="banca_eur" class="mb-0 ps-3">Banca(EUR)</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('banca_eur') ? 'is-invalid' : '' }}"
                    name="banca_eur"
                    placeholder=""
                    value="{{ old('banca_eur', $firma->banca_eur) }}">
            </div>
            <div class="col-lg-3 mb-2">
                <label for="cont_iban_eur" class="mb-0 ps-3">Iban(EUR)</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('cont_iban_eur') ? 'is-invalid' : '' }}"
                    name="cont_iban_eur"
                    placeholder=""
                    value="{{ old('cont_iban_eur', $firma->cont_iban_eur) }}">
            </div>
            <div class="col-lg-2 mb-2">
                <label for="zile_scadente" class="mb-0 ps-3">Zile scadențe</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('zile_scadente') ? 'is-invalid' : '' }}"
                    name="zile_scadente"
                    placeholder=""
                    value="{{ old('zile_scadente', $firma->zile_scadente) }}">
            </div>
        </div>
        <div class="row px-2 pt-4 pb-2 mb-0" style="background-color:#B8FFB8; border-left:6px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-2 mb-2">
                <label for="persoana_contact" class="mb-0 ps-3">Persoană contact</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('persoana_contact') ? 'is-invalid' : '' }}"
                    name="persoana_contact"
                    placeholder=""
                    value="{{ old('persoana_contact', $firma->persoana_contact) }}">
            </div>
            <div class="col-lg-2 mb-2">
                <label for="email" class="mb-0 ps-3">Email</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    name="email"
                    placeholder=""
                    value="{{ old('email', $firma->email) }}">
            </div>
            <div class="col-lg-2 mb-2">
                <label for="telefon" class="mb-0 ps-3">Telefon</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('telefon') ? 'is-invalid' : '' }}"
                    name="telefon"
                    placeholder=""
                    value="{{ old('telefon', $firma->telefon) }}">
            </div>
            <div class="col-lg-2 mb-2">
                <label for="skype" class="mb-0 ps-3">Skype</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('skype') ? 'is-invalid' : '' }}"
                    name="skype"
                    placeholder=""
                    value="{{ old('skype', $firma->skype) }}">
            </div>
            <div class="col-lg-2 mb-2">
                <label for="fax" class="mb-0 ps-3">Fax</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('fax') ? 'is-invalid' : '' }}"
                    name="fax"
                    placeholder=""
                    value="{{ old('fax', $firma->fax) }}">
            </div>
            <div class="col-lg-2 mb-2">
                <label for="website" class="mb-0 ps-3">Website</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('website') ? 'is-invalid' : '' }}"
                    name="website"
                    placeholder=""
                    value="{{ old('website', $firma->website) }}">
            </div>
        </div>
        <div class="row px-2 py-2 mb-4" style="background-color:#B8FFB8; border-left:6px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-6 mb-2">
                <label for="observatii" class="form-label mb-0 ps-3">Observații</label>
                <textarea class="form-control bg-white {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
                    name="observatii" rows="3">{{ old('observatii', $firma->observatii) }}</textarea>
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
