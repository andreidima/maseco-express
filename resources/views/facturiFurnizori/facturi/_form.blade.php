@php
    $factura ??= null;
@endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="denumire_furnizor" class="form-label">Denumire furnizor</label>
        <input type="text" name="denumire_furnizor" id="denumire_furnizor" class="form-control" value="{{ old('denumire_furnizor', $factura->denumire_furnizor ?? '') }}" list="furnizor-suggestions" autocomplete="off" required>
        <datalist id="furnizor-suggestions"></datalist>
    </div>
    <div class="col-md-6 mb-3">
        <label for="numar_factura" class="form-label">Numar factura</label>
        <input type="text" name="numar_factura" id="numar_factura" class="form-control" value="{{ old('numar_factura', $factura->numar_factura ?? '') }}" required>
    </div>
</div>
<div class="row">
    <div class="col-md-3 mb-3">
        <label for="data_factura" class="form-label">Data factura</label>
        <input type="date" name="data_factura" id="data_factura" class="form-control" value="{{ old('data_factura', optional($factura?->data_factura)->format('Y-m-d')) }}" required>
    </div>
    <div class="col-md-3 mb-3">
        <label for="data_scadenta" class="form-label">Data scadenta</label>
        <input type="date" name="data_scadenta" id="data_scadenta" class="form-control" value="{{ old('data_scadenta', optional($factura?->data_scadenta)->format('Y-m-d')) }}" required>
    </div>
    <div class="col-md-3 mb-3">
        <label for="suma" class="form-label">Suma</label>
        <input type="number" name="suma" id="suma" class="form-control" step="0.01" min="0" value="{{ old('suma', $factura->suma ?? '') }}" required>
    </div>
    <div class="col-md-3 mb-3">
        <label for="moneda" class="form-label">Moneda</label>
        <input type="text" name="moneda" id="moneda" class="form-control text-uppercase" maxlength="3" value="{{ old('moneda', $factura->moneda ?? 'RON') }}" required>
    </div>
</div>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="departament_vehicul" class="form-label">Nr auto / departament</label>
        <input type="text" name="departament_vehicul" id="departament_vehicul" class="form-control" value="{{ old('departament_vehicul', $factura->departament_vehicul ?? '') }}" list="departament-suggestions" autocomplete="off">
        <datalist id="departament-suggestions"></datalist>
    </div>
    <div class="col-md-6 mb-3">
        <label for="observatii" class="form-label">Observatii</label>
        <textarea name="observatii" id="observatii" class="form-control" rows="2">{{ old('observatii', $factura->observatii ?? '') }}</textarea>
    </div>
</div>
