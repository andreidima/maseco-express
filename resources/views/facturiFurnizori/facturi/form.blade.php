@php
    $factura ??= null;
@endphp

<div class="row">
    <div class="col-lg-6 mb-3">
        <label for="denumire_furnizor" class="mb-0 ps-2">Denumire furnizor<span class="text-danger">*</span></label>
        <input
            type="text"
            name="denumire_furnizor"
            id="denumire_furnizor"
            class="form-control bg-white rounded-3 {{ $errors->has('denumire_furnizor') ? 'is-invalid' : '' }}"
            value="{{ old('denumire_furnizor', $factura->denumire_furnizor ?? '') }}"
            list="furnizor-suggestions"
            autocomplete="off"
            required
        >
        <datalist id="furnizor-suggestions"></datalist>
    </div>
    <div class="col-lg-6 mb-3">
        <label for="numar_factura" class="mb-0 ps-2">Număr factură<span class="text-danger">*</span></label>
        <input
            type="text"
            name="numar_factura"
            id="numar_factura"
            class="form-control bg-white rounded-3 {{ $errors->has('numar_factura') ? 'is-invalid' : '' }}"
            value="{{ old('numar_factura', $factura->numar_factura ?? '') }}"
            required
        >
    </div>
</div>

<div class="row">
    <div class="col-lg-3 mb-3">
        <label for="data_factura" class="mb-0 ps-2">Data factură<span class="text-danger">*</span></label>
        <input
            type="date"
            name="data_factura"
            id="data_factura"
            class="form-control bg-white rounded-3 {{ $errors->has('data_factura') ? 'is-invalid' : '' }}"
            value="{{ old('data_factura', optional($factura?->data_factura)->format('Y-m-d')) }}"
            required
        >
    </div>
    <div class="col-lg-3 mb-3">
        <label for="data_scadenta" class="mb-0 ps-2">Data scadență<span class="text-danger">*</span></label>
        <input
            type="date"
            name="data_scadenta"
            id="data_scadenta"
            class="form-control bg-white rounded-3 {{ $errors->has('data_scadenta') ? 'is-invalid' : '' }}"
            value="{{ old('data_scadenta', optional($factura?->data_scadenta)->format('Y-m-d')) }}"
            required
        >
    </div>
    <div class="col-lg-3 mb-3">
        <label for="suma" class="mb-0 ps-2">Sumă<span class="text-danger">*</span></label>
        <input
            type="number"
            name="suma"
            id="suma"
            class="form-control bg-white rounded-3 {{ $errors->has('suma') ? 'is-invalid' : '' }}"
            step="0.01"
            min="0"
            value="{{ old('suma', $factura->suma ?? '') }}"
            required
        >
    </div>
    <div class="col-lg-3 mb-3">
        <label for="moneda" class="mb-0 ps-2">Monedă<span class="text-danger">*</span></label>
        <input
            type="text"
            name="moneda"
            id="moneda"
            class="form-control bg-white rounded-3 text-uppercase {{ $errors->has('moneda') ? 'is-invalid' : '' }}"
            maxlength="3"
            value="{{ old('moneda', $factura->moneda ?? 'RON') }}"
            required
        >
    </div>
</div>

<div class="row">
    <div class="col-lg-6 mb-3">
        <label for="departament_vehicul" class="mb-0 ps-2">Nr auto / departament</label>
        <input
            type="text"
            name="departament_vehicul"
            id="departament_vehicul"
            class="form-control bg-white rounded-3 {{ $errors->has('departament_vehicul') ? 'is-invalid' : '' }}"
            value="{{ old('departament_vehicul', $factura->departament_vehicul ?? '') }}"
            list="departament-suggestions"
            autocomplete="off"
        >
        <datalist id="departament-suggestions"></datalist>
    </div>
    <div class="col-lg-6 mb-3">
        <label for="observatii" class="mb-0 ps-2">Observații</label>
        <textarea
            name="observatii"
            id="observatii"
            rows="2"
            class="form-control bg-white rounded-3 {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
        >{{ old('observatii', $factura->observatii ?? '') }}</textarea>
    </div>
</div>
