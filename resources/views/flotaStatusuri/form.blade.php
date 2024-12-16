@csrf
<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-0">
            <div class="col-lg-4 mb-4">
                <label for="utilizator_id" class="mb-0 ps-3">Utilizator</label>
                <select name="utilizator_id" class="form-select bg-white rounded-3 {{ $errors->has('utilizator_id') ? 'is-invalid' : '' }}">
                    <option value="" selected></option>
                    @foreach ($utilizatori as $utilizator)
                        <option value="{{ $utilizator->id }}" {{ ($utilizator->id === intval(old('utilizator_id', $flotaStatus->utilizator_id ?? ''))) ? 'selected' : '' }}>{{ $utilizator->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 mb-4">
                <label for="nr_auto" class="mb-0 ps-3">Nr auto</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('nr_auto') ? 'is-invalid' : '' }}"
                    name="nr_auto"
                    value="{{ old('nr_auto', $flotaStatus->nr_auto) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-4">
                <label for="dimenssions" class="mb-0 ps-3">Dimenssions</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('dimenssions') ? 'is-invalid' : '' }}"
                    name="dimenssions"
                    value="{{ old('dimenssions', $flotaStatus->dimenssions) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-4">
                <label for="type" class="mb-0 ps-3">Type</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('type') ? 'is-invalid' : '' }}"
                    name="type"
                    value="{{ old('type', $flotaStatus->type) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-4">
                <label for="out_of_eu" class="mb-0 ps-3">Out of EU</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('out_of_eu') ? 'is-invalid' : '' }}"
                    name="out_of_eu"
                    value="{{ old('out_of_eu', $flotaStatus->out_of_eu) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-4">
                <label for="info" class="mb-0 ps-3">Info</label>
                <select name="info" class="form-select bg-white rounded-3 {{ $errors->has('info') ? 'is-invalid' : '' }}">
                    <option value="" selected></option>
                    <option value="1" {{ intval(old('info', $flotaStatus->info ?? '') === 1) ? 'selected' : '' }}>În tranzit, fără cursă după descărcare</option>
                    <option value="2" {{ intval(old('info', $flotaStatus->info ?? '') === 2) ? 'selected' : '' }}>De grupat</option>
                    <option value="3" {{ intval(old('info', $flotaStatus->info ?? '') === 3) ? 'selected' : '' }}>Liber</option>
                </select>
            </div>
            <div class="col-lg-4 mb-4">
                <label for="abilities" class="mb-0 ps-3">Abilities</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('abilities') ? 'is-invalid' : '' }}"
                    name="abilities"
                    value="{{ old('abilities', $flotaStatus->abilities) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-4">
                <label for="status_of_the_shipment" class="mb-0 ps-3">Status of the shipment</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('status_of_the_shipment') ? 'is-invalid' : '' }}"
                    name="status_of_the_shipment"
                    value="{{ old('status_of_the_shipment', $flotaStatus->status_of_the_shipment) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-4">
                <label for="info_ii" class="mb-0 ps-3">Info II</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('info_ii') ? 'is-invalid' : '' }}"
                    name="info_ii"
                    value="{{ old('info_ii', $flotaStatus->info_ii) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-4">
                <label for="info_iii" class="mb-0 ps-3">Info III</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('info_iii') ? 'is-invalid' : '' }}"
                    name="info_iii"
                    value="{{ old('info_iii', $flotaStatus->info_iii) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-4">
                <label for="special_info" class="mb-0 ps-3">Special info</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('special_info') ? 'is-invalid' : '' }}"
                    name="special_info"
                    value="{{ old('special_info', $flotaStatus->special_info) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-4">
                <label for="e_km" class="mb-0 ps-3">E/KM</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('e_km') ? 'is-invalid' : '' }}"
                    name="e_km"
                    value="{{ old('e_km', $flotaStatus->e_km) }}"
                    required>
            </div>
        </div>
    </div>

    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('flotaStatusReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
