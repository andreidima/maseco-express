@csrf
<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-0">
            <div class="col-lg-6 mb-4">
                <label for="nume" class="mb-0 ps-3">Nume</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('nume') ? 'is-invalid' : '' }}"
                    name="nume"
                    value="{{ old('nume', $flotaStatusUtilizator->nume) }}"
                    required>
            </div>
            @php
                $colorOptions = [
                    '#4287f5' => '#4287f5',
                    'red' => 'red',
                    'orange' => 'orange',
                    'yellow' => 'yellow',
                    'green' => 'green',
                    'blue' => 'blue',
                    'teal' => 'teal',
                    'purple' => 'purple',
                    'pink' => 'pink',
                    'brown' => 'brown',
                    'gray' => 'gray',
                    'white' => 'white',
                    'black' => 'black',
                ];
                $lightColors = ['white', 'yellow', 'orange', 'pink', 'gray'];
                $selectedBackground = old('culoare_background', $flotaStatusUtilizator->culoare_background);
                $selectedText = old('culoare_text', $flotaStatusUtilizator->culoare_text);
            @endphp
            <div class="col-lg-6 mb-4">
                <label for="culoare_background" class="mb-0 ps-3">Culoare background</label>
                <select
                    name="culoare_background"
                    class="form-select bg-white rounded-3 {{ $errors->has('culoare_background') ? 'is-invalid' : '' }}"
                >
                    <option value="" {{ $selectedBackground === null || $selectedBackground === '' ? 'selected' : '' }}></option>
                    @if($selectedBackground !== null && $selectedBackground !== '' && !array_key_exists($selectedBackground, $colorOptions))
                        <option value="{{ $selectedBackground }}" selected>Custom: {{ $selectedBackground }}</option>
                    @endif
                    @foreach ($colorOptions as $value => $label)
                        <option
                            value="{{ $value }}"
                            {{ $selectedBackground === $value ? 'selected' : '' }}
                            style="background-color: {{ $value }}; color: {{ in_array($value, $lightColors, true) ? '#000' : '#fff' }};"
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="culoare_text" class="mb-0 ps-3">Culoare text</label>
                <select
                    name="culoare_text"
                    class="form-select bg-white rounded-3 {{ $errors->has('culoare_text') ? 'is-invalid' : '' }}"
                >
                    <option value="" {{ $selectedText === null || $selectedText === '' ? 'selected' : '' }}></option>
                    @if($selectedText !== null && $selectedText !== '' && !array_key_exists($selectedText, $colorOptions))
                        <option value="{{ $selectedText }}" selected>Custom: {{ $selectedText }}</option>
                    @endif
                    @foreach ($colorOptions as $value => $label)
                        <option
                            value="{{ $value }}"
                            {{ $selectedText === $value ? 'selected' : '' }}
                            style="background-color: {{ $value }}; color: {{ in_array($value, $lightColors, true) ? '#000' : '#fff' }};"
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="ordine_afisare" class="mb-0 ps-3">Ordine afisare</label>
                <input
                    type="number"
                    class="form-control bg-white rounded-3 {{ $errors->has('ordine_afisare') ? 'is-invalid' : '' }}"
                    name="ordine_afisare"
                    value="{{ old('ordine_afisare', $flotaStatusUtilizator->ordine_afisare) }}"
                    min="0">
            </div>
        </div>
    </div>

    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('flotaStatusUtilizatorReturnUrl') }}">Renunta</a>
            </div>
        </div>
    </div>
</div>
