@csrf
<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-0">
            <div class="col-lg-4 mb-4">
                <label for="modalitate_de_plata" class="mb-0 ps-3">Modalitate de plată</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('modalitate_de_plata') ? 'is-invalid' : '' }}"
                    name="modalitate_de_plata"
                    value="{{ old('modalitate_de_plata', $flotaStatusInformatie->modalitate_de_plata) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-4">
                <label for="spot" class="mb-0 ps-3">Spot</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('spot') ? 'is-invalid' : '' }}"
                    name="spot"
                    value="{{ old('spot', $flotaStatusInformatie->spot) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-4">
                <label for="termen" class="mb-0 ps-3">Termen</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('termen') ? 'is-invalid' : '' }}"
                    name="termen"
                    value="{{ old('termen', $flotaStatusInformatie->termen) }}"
                    required>
            </div>
            <div class="col-lg-12 mb-4">
                <label for="info" class="mb-0 ps-3">Info</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('info') ? 'is-invalid' : '' }}"
                    name="info"
                    value="{{ old('info', $flotaStatusInformatie->info) }}"
                    required>
            </div>
            <div class="col-lg-12 mb-4">
                <label for="info_2" class="mb-0 ps-3">Info 2</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('info_2') ? 'is-invalid' : '' }}"
                    name="info_2"
                    value="{{ old('info_2', $flotaStatusInformatie->info_2) }}"
                    required>
            </div>
        </div>
    </div>

    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('flotaStatusInformatieReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
