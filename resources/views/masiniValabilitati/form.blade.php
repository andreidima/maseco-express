<div class="row mb-0 px-3">
    {{-- Field: Nr auto --}}
    <div class="col-lg-4 mb-4">
        <label for="nr_auto" class="mb-0 ps-3">Nr auto</label>
        <input
            type="text"
            name="nr_auto"
            id="nr_auto"
            class="form-control bg-white rounded-3 {{ $errors->has('nr_auto') ? 'is-invalid' : '' }}"
            value="{{ old('nr_auto', $masinaValabilitati->nr_auto ?? '') }}"
        >
        @if($errors->has('nr_auto'))
            <div class="invalid-feedback">
                {{ $errors->first('nr_auto') }}
            </div>
        @endif
    </div>

    {{-- Field: Nume șofer --}}
    <div class="col-lg-4 mb-4">
        <label for="nume_sofer" class="mb-0 ps-3">Nume șofer</label>
        <input
            type="text"
            name="nume_sofer"
            id="nume_sofer"
            class="form-control bg-white rounded-3 {{ $errors->has('nume_sofer') ? 'is-invalid' : '' }}"
            value="{{ old('nume_sofer', $masinaValabilitati->nume_sofer ?? '') }}"
        >
        @if($errors->has('nume_sofer'))
            <div class="invalid-feedback">
                {{ $errors->first('nume_sofer') }}
            </div>
        @endif
    </div>

    {{-- Field: Divizie selection as a dropdown (4 predefined choices) --}}
    <div class="col-lg-4 mb-4">
        <label for="divizie" class="mb-0 ps-3">Divizie</label>
        <select
            name="divizie"
            id="divizie"
            class="form-select bg-white rounded-3 {{ $errors->has('divizie') ? 'is-invalid' : '' }}"
        >
            <option value="" {{ old('divizie', $masinaValabilitati->divizie ?? '') === '' ? 'selected' : '' }}>
                – Alege o divizie –
            </option>

            @php
                $divizii = [
                    'Regim propriu',
                    'Flash',
                    'Timestar',
                    'Ml Express',
                ];
            @endphp

            @foreach($divizii as $divizie)
                <option
                    value="{{ $divizie }}"
                    {{-- Preselect if old() or existing model matches --}}
                    {{ old('divizie', $masinaValabilitati->divizie ?? '') === $divizie ? 'selected' : '' }}
                >
                    {{ $divizie }}
                </option>
            @endforeach
        </select>

        @if($errors->has('divizie'))
            <div class="invalid-feedback">
                {{ $errors->first('divizie') }}
            </div>
        @endif
    </div>

    {{-- Field: Valabilitate 1 --}}
    <div class="col-lg-4 mb-4">
        <label for="valabilitate_1" class="mb-0 ps-3">Valabilitate 1</label>
        <input
            type="text"
            name="valabilitate_1"
            id="valabilitate_1"
            class="form-control bg-white rounded-3 {{ $errors->has('valabilitate_1') ? 'is-invalid' : '' }}"
            value="{{ old('valabilitate_1', $masinaValabilitati->valabilitate_1 ?? '') }}"
        >
        @if($errors->has('valabilitate_1'))
            <div class="invalid-feedback">
                {{ $errors->first('valabilitate_1') }}
            </div>
        @endif
    </div>

    {{-- Field: Observații 1 --}}
    <div class="col-lg-8 mb-4">
        <label for="observatii_1" class="mb-0 ps-3">Observații 1</label>
        <input
            type="text"
            name="observatii_1"
            id="observatii_1"
            class="form-control bg-white rounded-3 {{ $errors->has('observatii_1') ? 'is-invalid' : '' }}"
            value="{{ old('observatii_1', $masinaValabilitati->observatii_1 ?? '') }}"
        >
        @if($errors->has('observatii_1'))
            <div class="invalid-feedback">
                {{ $errors->first('observatii_1') }}
            </div>
        @endif
    </div>

    {{-- Field: Valabilitate 2 --}}
    <div class="col-lg-4 mb-4">
        <label for="valabilitate_2" class="mb-0 ps-3">Valabilitate 2</label>
        <input
            type="text"
            name="valabilitate_2"
            id="valabilitate_2"
            class="form-control bg-white rounded-3 {{ $errors->has('valabilitate_2') ? 'is-invalid' : '' }}"
            value="{{ old('valabilitate_2', $masinaValabilitati->valabilitate_2 ?? '') }}"
        >
        @if($errors->has('valabilitate_2'))
            <div class="invalid-feedback">
                {{ $errors->first('valabilitate_2') }}
            </div>
        @endif
    </div>

    {{-- Field: Observații 2 --}}
    <div class="col-lg-8 mb-4">
        <label for="observatii_2" class="mb-0 ps-3">Observații 2</label>
        <input
            type="text"
            name="observatii_2"
            id="observatii_2"
            class="form-control bg-white rounded-3 {{ $errors->has('observatii_2') ? 'is-invalid' : '' }}"
            value="{{ old('observatii_2', $masinaValabilitati->observatii_2 ?? '') }}"
        >
        @if($errors->has('observatii_2'))
            <div class="invalid-feedback">
                {{ $errors->first('observatii_2') }}
            </div>
        @endif
    </div>
</div>

<div class="row mb-0 px-3">
    {{-- Submit and Cancel buttons --}}
    <div class="col-lg-12 mb-2 d-flex justify-content-center">
        <button type="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">
            {{ $buttonText }}
        </button>
        <a class="btn btn-lg btn-secondary rounded-3"
           href="{{ Session::get('masinaValabilitatiReturnUrl') }}">
            Renunță
        </a>
    </div>
</div>
