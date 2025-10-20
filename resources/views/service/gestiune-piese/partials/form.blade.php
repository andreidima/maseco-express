@php
    $editing = isset($piesa);
@endphp

<div class="row g-3">
    <div class="col-lg-6">
        <label for="denumire" class="form-label small text-muted mb-1">Denumire piesă <span class="text-danger">*</span></label>
        <input type="text" name="denumire" id="denumire" class="form-control rounded-3"
            value="{{ old('denumire', $editing ? $piesa->denumire : '') }}" required>
        @error('denumire')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6">
        <label for="cod" class="form-label small text-muted mb-1">Cod piesă</label>
        <input type="text" name="cod" id="cod" class="form-control rounded-3"
            value="{{ old('cod', $editing ? $piesa->cod : '') }}">
        @error('cod')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4">
        <label for="cantitate_initiala" class="form-label small text-muted mb-1">Cantitate inițială</label>
        <input type="number" step="0.01" min="0" name="cantitate_initiala" id="cantitate_initiala"
            class="form-control rounded-3" value="{{ old('cantitate_initiala', $editing ? $piesa->cantitate_initiala : '') }}">
        <small class="text-muted">Dacă lipsește, valoarea va fi completată din stocul curent.</small>
        @error('cantitate_initiala')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4">
        <label for="nr_bucati" class="form-label small text-muted mb-1">Stoc curent</label>
        <input type="number" step="0.01" min="0" name="nr_bucati" id="nr_bucati"
            class="form-control rounded-3" value="{{ old('nr_bucati', $editing ? $piesa->nr_bucati : '') }}">
        @error('nr_bucati')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4">
        <label for="factura_id" class="form-label small text-muted mb-1">Factură furnizor (opțional)</label>
        <input type="number" min="1" name="factura_id" id="factura_id" class="form-control rounded-3"
            value="{{ old('factura_id', $editing ? $piesa->factura_id : '') }}">
        <small class="text-muted">Completează ID-ul facturii pentru a lega piesa de o achiziție.</small>
        @error('factura_id')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4">
        <label for="pret" class="form-label small text-muted mb-1">Preț net</label>
        <input type="number" step="0.01" min="0" name="pret" id="pret" class="form-control rounded-3"
            value="{{ old('pret', $editing ? $piesa->pret : '') }}">
        @error('pret')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4">
        <label for="tva_cota" class="form-label small text-muted mb-1">Cotă TVA (%)</label>
        <input type="number" step="0.01" min="0" max="100" name="tva_cota" id="tva_cota"
            class="form-control rounded-3" value="{{ old('tva_cota', $editing ? $piesa->tva_cota : '') }}">
        @error('tva_cota')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4">
        <label for="pret_brut" class="form-label small text-muted mb-1">Preț brut</label>
        <input type="number" step="0.01" min="0" name="pret_brut" id="pret_brut"
            class="form-control rounded-3" value="{{ old('pret_brut', $editing ? $piesa->pret_brut : '') }}">
        @error('pret_brut')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>
