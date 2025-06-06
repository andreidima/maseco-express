@csrf

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-0">
            <div class="col-lg-12 mb-4">
                <label for="observatii" class="mb-0 ps-3">Observații</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
                    name="observatii"
                    value="{{ old('observatii', $intermediere->observatii) }}"
                    required>
            </div>
            <div class="col-lg-12 mb-4">
                <label for="motis" class="mb-0 ps-3">Motis</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('motis') ? 'is-invalid' : '' }}"
                    name="motis"
                    value="{{ old('motis', $intermediere->motis) }}"
                    required>
            </div>
            <div class="col-lg-12 mb-4">
                <label for="dkv" class="mb-0 ps-3">Dkv</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('dkv') ? 'is-invalid' : '' }}"
                    name="dkv"
                    value="{{ old('dkv', $intermediere->dkv) }}"
                    required>
            </div>
            <div class="col-lg-12 mb-4">
                <label for="astra" class="mb-0 ps-3">Astra</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('astra') ? 'is-invalid' : '' }}"
                    name="astra"
                    value="{{ old('astra', $intermediere->astra) }}"
                    required>
            </div>
            {{-- <div class="col-lg-12 mb-4">
                <label for="plata_client" class="mb-0 ps-3">Plată client</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('plata_client') ? 'is-invalid' : '' }}"
                    name="plata_client"
                    value="{{ old('plata_client', $intermediere->plata_client) }}"
                    required>
            </div> --}}
        </div>
    </div>

    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('intermediereReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
