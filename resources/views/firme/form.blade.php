@csrf
<input type="hidden" name="tip_partener" value="{{ $tipPartener }}">

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
                    value="{{ old('nume', $firma->nume) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-5 mx-auto">
                <label for="tara" class="mb-0 ps-3">Țara<span class="text-danger">*</span></label>
                <select name="tara_id" class="form-select bg-white rounded-3 {{ $errors->has('tara_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($tari as $tara)
                        <option value="{{ $tara->id }}" {{ ($tara->id === intval(old('tara_id', $firma->tara_id ?? ''))) ? 'selected' : '' }}>{{ $tara->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 mb-5 mx-auto">
                <label for="telefon" class="mb-0 ps-3">Telefon</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('telefon') ? 'is-invalid' : '' }}"
                    name="telefon"
                    placeholder=""
                    value="{{ old('telefon', $firma->telefon) }}">
            </div>
            {{-- <div class="col-lg-12 mb-5 mx-auto">
                <label for="observatii" class="form-label mb-0 ps-3">Observații</label>
                <textarea class="form-control bg-white {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
                    name="observatii" rows="3">{{ old('observatii', $client->observatii) }}</textarea>
            </div> --}}
        </div>
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-secondary rounded-3" href="{{ Session::get('client_return_url') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
