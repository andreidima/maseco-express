<div class="row g-3">
    <div class="col-md-6">
        <label for="masina_id" class="form-label">Mașină</label>
        <select id="masina_id" name="masina_id" class="form-select">
            <option value="">— Selectează mașina —</option>
            @foreach ($masini as $id => $label)
                <option value="{{ $id }}" @selected((string) old('masina_id', $valabilitate->masina_id) === (string) $id)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6">
        <label for="referinta" class="form-label">Referință</label>
        <input type="text" class="form-control" id="referinta" name="referinta"
            value="{{ old('referinta', $valabilitate->referinta) }}" maxlength="255">
    </div>

    <div class="col-md-6">
        <label for="prima_cursa" class="form-label">Prima cursă</label>
        <input type="datetime-local" class="form-control" id="prima_cursa" name="prima_cursa"
            value="{{ old('prima_cursa', optional($valabilitate->prima_cursa)->format('Y-m-d\TH:i')) }}">
    </div>

    <div class="col-md-6">
        <label for="ultima_cursa" class="form-label">Ultima cursă</label>
        <input type="datetime-local" class="form-control" id="ultima_cursa" name="ultima_cursa"
            value="{{ old('ultima_cursa', optional($valabilitate->ultima_cursa)->format('Y-m-d\TH:i')) }}">
    </div>
</div>

<div class="d-flex justify-content-end mt-4">
    <a href="{{ route('valabilitati.index') }}" class="btn btn-outline-secondary me-2">
        Renunță
    </a>
    <button type="submit" class="btn btn-primary">
        {{ $submitLabel }}
    </button>
</div>
