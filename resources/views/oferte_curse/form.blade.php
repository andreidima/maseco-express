@csrf

<div class="row mb-4 pt-2 rounded-3" style="border:1px solid #e9ecef; border-left:0.25rem #e66800 solid; background-color:#fff9f5">
    <div class="col-lg-3 mb-4">
        <label for="incarcare_cod_postal" class="mb-0 ps-3">Cod poștal încărcare</label>
        <input type="text" name="incarcare_cod_postal" id="incarcare_cod_postal"
               class="form-control bg-white rounded-3"
               value="{{ old('incarcare_cod_postal', $oferta->incarcare_cod_postal ?? '') }}">
    </div>
    <div class="col-lg-3 mb-4">
        <label for="incarcare_localitate" class="mb-0 ps-3">Localitate încărcare</label>
        <input type="text" name="incarcare_localitate" id="incarcare_localitate"
               class="form-control bg-white rounded-3"
               value="{{ old('incarcare_localitate', $oferta->incarcare_localitate ?? '') }}">
    </div>
    <div class="col-lg-6 mb-4">
        <label for="incarcare_data_ora" class="mb-0 ps-3">Data & ora încărcare</label>
        <input type="text" name="incarcare_data_ora" id="incarcare_data_ora"
               class="form-control bg-white rounded-3"
               value="{{ old('incarcare_data_ora', $oferta->incarcare_data_ora ?? '') }}">
    </div>
    <div class="col-lg-3 mb-4">
        <label for="descarcare_cod_postal" class="mb-0 ps-3">Cod poștal descărcare</label>
        <input type="text" name="descarcare_cod_postal" id="descarcare_cod_postal"
               class="form-control bg-white rounded-3"
               value="{{ old('descarcare_cod_postal', $oferta->descarcare_cod_postal ?? '') }}">
    </div>
    <div class="col-lg-3 mb-4">
        <label for="descarcare_localitate" class="mb-0 ps-3">Localitate descărcare</label>
        <input type="text" name="descarcare_localitate" id="descarcare_localitate"
               class="form-control bg-white rounded-3"
               value="{{ old('descarcare_localitate', $oferta->descarcare_localitate ?? '') }}">
    </div>

    <div class="col-lg-6 mb-4">
        <label for="descarcare_data_ora" class="mb-0 ps-3">Data & ora descărcare</label>
        <input type="text" name="descarcare_data_ora" id="descarcare_data_ora"
               class="form-control bg-white rounded-3"
               value="{{ old('descarcare_data_ora', $oferta->descarcare_data_ora ?? '') }}">
    </div>
    <div class="col-lg-12 mb-4">
        <label for="detalii_cursa" class="mb-0 ps-3">Detalii cursă</label>
        <textarea name="detalii_cursa" id="detalii_cursa" rows="7" class="form-control bg-white rounded-3">{{ old('detalii_cursa', $oferta->detalii_cursa ?? '') }}</textarea>
    </div>
</div>

<div class="row mb-4 pt-2 rounded-3" style="border:1px solid #e9ecef; border-left:0.25rem darkcyan solid; background-color:rgb(241, 250, 250)">
    <div class="col-lg-6 mb-4">
        <label for="email_subiect" class="mb-0 ps-3">Subiect email</label>
        <input type="text" name="email_subiect" id="email_subiect" required
               class="form-control bg-white rounded-3 @error('email_subiect') is-invalid @enderror"
               value="{{ old('email_subiect', $oferta->email_subiect ?? '') }}">
    </div>
    <div class="col-lg-6 mb-4">
        <label for="email_expeditor" class="mb-0 ps-3">Expeditor</label>
        <input type="email" name="email_expeditor" id="email_expeditor" required
               class="form-control bg-white rounded-3 @error('email_expeditor') is-invalid @enderror"
               value="{{ old('email_expeditor', $oferta->email_expeditor ?? '') }}">
    </div>
    <div class="col-lg-6 mb-4">
        <label for="data_primirii" class="mb-0 ps-3">Data primirii</label>
        <input type="datetime-local" name="data_primirii" id="data_primirii" required
               class="form-control bg-white rounded-3 @error('data_primirii') is-invalid @enderror"
               value="{{ old('data_primirii', \Carbon\Carbon::parse($oferta->data_primirii ?? now())->format('Y-m-d\TH:i')) }}">
    </div>
    <div class="col-lg-6 mb-4">
        <label for="gmail_link" class="mb-0 ps-3">Link Gmail</label>
        <input type="url" name="gmail_link" id="gmail_link" required
               class="form-control bg-white rounded-3 @error('gmail_link') is-invalid @enderror"
               value="{{ old('gmail_link', $oferta->gmail_link ?? '') }}">
    </div>
</div>

<div class="row">
    <div class="col-lg-12 mb-2 d-flex justify-content-center">
        <button type="submit" class="btn btn-primary text-white me-3 rounded-3">
            <i class="fa-solid fa-save me-1"></i> {{ $buttonText }}
        </button>
        <a class="btn btn-secondary rounded-3" href="{{ Session::get('returnUrl', route('oferte-curse.index')) }}">
            Renunță
        </a>
    </div>
</div>
