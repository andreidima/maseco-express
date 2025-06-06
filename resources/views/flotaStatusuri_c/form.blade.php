{{-- resources/views/flotaStatusuri_c/form.blade.php --}}
<div class="row mb-0 px-3">
    {{-- Field: Nr auto --}}
    <div class="col-lg-4 mb-4">
        <label for="nr_auto" class="mb-0 ps-3">Nr auto</label>
        <input
            type="text"
            name="nr_auto"
            id="nr_auto"
            class="form-control bg-white rounded-3 {{ $errors->has('nr_auto') ? 'is-invalid' : '' }}"
            value="{{ old('nr_auto', $flotaStatusC->nr_auto ?? '') }}"
        >
        @if($errors->has('nr_auto'))
            <div class="invalid-feedback">
                {{ $errors->first('nr_auto') }}
            </div>
        @endif
    </div>

    {{-- Field: Dimenssions --}}
    <div class="col-lg-4 mb-4">
        <label for="dimenssions" class="mb-0 ps-3">Dimenssions</label>
        <input
            type="text"
            name="dimenssions"
            id="dimenssions"
            class="form-control bg-white rounded-3 {{ $errors->has('dimenssions') ? 'is-invalid' : '' }}"
            value="{{ old('dimenssions', $flotaStatusC->dimenssions ?? '') }}"
        >
        @if($errors->has('dimenssions'))
            <div class="invalid-feedback">
                {{ $errors->first('dimenssions') }}
            </div>
        @endif
    </div>

    {{-- Field: Type --}}
    <div class="col-lg-4 mb-4">
        <label for="type" class="mb-0 ps-3">Type</label>
        <input
            type="text"
            name="type"
            id="type"
            class="form-control bg-white rounded-3 {{ $errors->has('type') ? 'is-invalid' : '' }}"
            value="{{ old('type', $flotaStatusC->type ?? '') }}"
        >
        @if($errors->has('type'))
            <div class="invalid-feedback">
                {{ $errors->first('type') }}
            </div>
        @endif
    </div>

    {{-- Field: Out of EU --}}
    <div class="col-lg-4 mb-4">
        <label for="out_of_eu" class="mb-0 ps-3">Out of EU</label>
        <input
            type="text"
            name="out_of_eu"
            id="out_of_eu"
            class="form-control bg-white rounded-3 {{ $errors->has('out_of_eu') ? 'is-invalid' : '' }}"
            value="{{ old('out_of_eu', $flotaStatusC->out_of_eu ?? '') }}"
        >
        @if($errors->has('out_of_eu'))
            <div class="invalid-feedback">
                {{ $errors->first('out_of_eu') }}
            </div>
        @endif
    </div>

    {{-- Field: Info I --}}
    <div class="col-lg-4 mb-4">
        <label for="info_i" class="mb-0 ps-3">Info I</label>
        <input
            type="text"
            name="info_i"
            id="info_i"
            class="form-control bg-white rounded-3 {{ $errors->has('info_i') ? 'is-invalid' : '' }}"
            value="{{ old('info_i', $flotaStatusC->info_i ?? '') }}"
        >
        @if($errors->has('info_i'))
            <div class="invalid-feedback">
                {{ $errors->first('info_i') }}
            </div>
        @endif
    </div>

    {{-- Field: Info II --}}
    <div class="col-lg-4 mb-4">
        <label for="info_ii" class="mb-0 ps-3">Info II</label>
        <input
            type="text"
            name="info_ii"
            id="info_ii"
            class="form-control bg-white rounded-3 {{ $errors->has('info_ii') ? 'is-invalid' : '' }}"
            value="{{ old('info_ii', $flotaStatusC->info_ii ?? '') }}"
        >
        @if($errors->has('info_ii'))
            <div class="invalid-feedback">
                {{ $errors->first('info_ii') }}
            </div>
        @endif
    </div>

    {{-- Color selection as a dropdown (10 predefined choices) --}}
    <div class="col-lg-4 mb-4">
        <label for="color" class="mb-0 ps-3">Color</label>
        <select
            name="color"
            id="color"
            class="form-select bg-white rounded-3 {{ $errors->has('color') ? 'is-invalid' : '' }}"
        >
            <option value="" {{ old('color', $flotaStatusC->color ?? '') === '' ? 'selected' : '' }}>
                – Alege o culoare –
            </option>

            @php
                // Same palette of 10 colors
                $colors = [
                    '#FF0000' => 'Red',
                    '#00FF00' => 'Green',
                    '#0000FF' => 'Blue',
                    '#FFFF00' => 'Yellow',
                    '#FFA500' => 'Orange',
                    '#800080' => 'Purple',
                    '#008080' => 'Teal',
                    '#FFC0CB' => 'Pink',
                    '#A52A2A' => 'Brown',
                    '#808080' => 'Gray',
                ];
            @endphp

            @foreach($colors as $hex => $name)
                <option
                    value="{{ $hex }}"
                    {{-- Preselect if old() or existing model matches --}}
                    {{ old('color', $flotaStatusC->color ?? '') === $hex ? 'selected' : '' }}
                    {{-- Inline‐style so the background of each option is the actual color --}}
                    style="background-color: {{ $hex }}; color: {{ in_array($hex, ['#FFFF00', '#FFC0CB', '#00FF00']) ? '#000' : '#fff' }};"
                >
                    {{ $name }}
                </option>
            @endforeach
        </select>

        @if($errors->has('color'))
            <div class="invalid-feedback">
                {{ $errors->first('color') }}
            </div>
        @endif
    </div>

    {{-- ===== “Ordine” field ===== --}}
    <div class="col-lg-4 mb-4">
        <label for="ordine" class="mb-0 ps-3">Ordine afișare</label>
        <input
            type="number"
            name="ordine"
            id="ordine"
            class="form-control bg-white rounded-3 {{ $errors->has('ordine') ? 'is-invalid' : '' }}"
            value="{{ old('ordine', $flotaStatusC->ordine ?? '') }}"
            min="0"
        >
        @if($errors->has('ordine'))
            <div class="invalid-feedback">
                {{ $errors->first('ordine') }}
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
           href="{{ Session::get('flotaStatusCReturnUrl') }}">
            Renunță
        </a>
    </div>
</div>
