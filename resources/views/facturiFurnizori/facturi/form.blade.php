@php
    $factura ??= null;
    $buttonText ??= __('Salvează factura');
    $buttonClass ??= 'btn-primary';
    $cancelUrl ??= \App\Support\FacturiFurnizori\FacturiIndexFilterState::route();
@endphp

<div class="row">
    <div class="col-lg-4 mb-3">
        <label for="denumire_furnizor" class="mb-0 ps-2">Denumire furnizor<span class="text-danger">*</span></label>
        <input
            type="text"
            name="denumire_furnizor"
            id="denumire_furnizor"
            class="form-control bg-white rounded-3 {{ $errors->has('denumire_furnizor') ? 'is-invalid' : '' }}"
            value="{{ old('denumire_furnizor', $factura->denumire_furnizor ?? '') }}"
            list="furnizor-suggestions"
            autocomplete="off"
            data-typeahead-tip="furnizor"
            data-typeahead-minlength="1"
            required
        >
        <datalist id="furnizor-suggestions"></datalist>
        <small class="form-text text-muted ps-2">
            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Autocomplete disponibil
        </small>
        @error('denumire_furnizor')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4 mb-3">
        <label for="cont_iban" class="mb-0 ps-2">Cont IBAN</label>
        <input
            type="text"
            name="cont_iban"
            id="cont_iban"
            class="form-control bg-white rounded-3 text-uppercase {{ $errors->has('cont_iban') ? 'is-invalid' : '' }}"
            maxlength="255"
            value="{{ old('cont_iban', $factura->cont_iban ?? '') }}"
            autocomplete="off"
        >
        @error('cont_iban')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4 mb-3">
        <label for="numar_factura" class="mb-0 ps-2">Număr factură<span class="text-danger">*</span></label>
        <input
            type="text"
            name="numar_factura"
            id="numar_factura"
            class="form-control bg-white rounded-3 {{ $errors->has('numar_factura') ? 'is-invalid' : '' }}"
            value="{{ old('numar_factura', $factura->numar_factura ?? '') }}"
            autocomplete="off"
            required
        >
        @error('numar_factura')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
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
        @error('data_factura')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
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
        @error('data_scadenta')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
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
        @error('suma')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
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
        @error('moneda')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row mb-0">
    <div class="col-lg-6 mb-4">
        <label for="departament_vehicul" class="mb-0 ps-3">Nr auto / departament</label>
        <input
            type="text"
            name="departament_vehicul"
            id="departament_vehicul"
            class="form-control bg-white rounded-3 {{ $errors->has('departament_vehicul') ? 'is-invalid' : '' }}"
            value="{{ old('departament_vehicul', $factura->departament_vehicul ?? '') }}"
            list="departament-suggestions"
            autocomplete="off"
            data-typeahead-tip="departament"
            data-typeahead-minlength="1"
        >
        <datalist id="departament-suggestions"></datalist>
        <small class="form-text text-muted ps-3">
            <i class="fa-solid fa-wand-magic-sparkles me-1"></i>Autocomplete disponibil
        </small>
        @error('departament_vehicul')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-6 mb-4">
        <label for="observatii" class="mb-0 ps-3">Observații</label>
        <textarea
            name="observatii"
            id="observatii"
            rows="2"
            class="form-control bg-white rounded-3 {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
        >{{ old('observatii', $factura->observatii ?? '') }}</textarea>
        @error('observatii')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

<div class="row mb-0 px-3">
    <div class="col-lg-12 mb-2 d-flex justify-content-center">
        <button type="submit" class="btn btn-lg {{ $buttonClass }} text-white me-3 rounded-3">
            {{ $buttonText }}
        </button>
        <a class="btn btn-lg btn-secondary rounded-3" href="{{ $cancelUrl }}">
            Renunță
        </a>
    </div>
</div>
