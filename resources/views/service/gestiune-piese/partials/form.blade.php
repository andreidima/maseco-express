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
    <div class="col-lg-6">
        <label for="cantitate_initiala" class="form-label small text-muted mb-1">Cantitate inițială</label>
        <input type="number" step="0.01" min="0" name="cantitate_initiala" id="cantitate_initiala"
            class="form-control rounded-3" value="{{ old('cantitate_initiala', $editing ? $piesa->cantitate_initiala : '') }}">
        @error('cantitate_initiala')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    @php
        $rawVatRate = old('tva_cota', $editing ? $piesa->tva_cota : null);
        $selectedVatRate = is_numeric($rawVatRate) ? (float) $rawVatRate : null;
    @endphp
    <div class="col-lg-6">
        <label for="factura_id" class="form-label small text-muted mb-1">Factură furnizor (opțional)</label>
        <input type="number" min="1" name="factura_id" id="factura_id" class="form-control rounded-3"
            value="{{ old('factura_id', $editing ? $piesa->factura_id : '') }}">
        <small class="text-muted">Completează ID-ul facturii pentru a lega piesa de o achiziție.</small>
        @error('factura_id')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4">
        <label for="pret" class="form-label small text-muted mb-1">Preț NET/buc</label>
        <input type="number" step="0.01" min="0" name="pret" id="pret" class="form-control rounded-3"
            value="{{ old('pret', $editing ? $piesa->pret : '') }}" data-piece-price="net">
        @error('pret')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4">
        <label for="tva_cota" class="form-label small text-muted mb-1">Cotă TVA</label>
        <select name="tva_cota" id="tva_cota" class="form-select rounded-3" data-piece-price="vat">
            <option value="" @selected(!is_numeric($rawVatRate))>Cotă TVA</option>
            <option value="11" @selected($selectedVatRate === 11.0)>11%</option>
            <option value="21" @selected($selectedVatRate === 21.0)>21%</option>
        </select>
        @error('tva_cota')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-lg-4">
        <label for="pret_brut" class="form-label small text-muted mb-1">Preț BRUT/buc</label>
        <input type="number" step="0.01" min="0" name="pret_brut" id="pret_brut"
            class="form-control rounded-3 bg-light" value="{{ old('pret_brut', $editing ? $piesa->pret_brut : '') }}"
            readonly tabindex="-1" data-piece-price="gross">
        @error('pret_brut')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>
</div>

@once
    @push('page-scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const netInput = document.querySelector('[data-piece-price="net"]');
                const vatInput = document.querySelector('[data-piece-price="vat"]');
                const grossInput = document.querySelector('[data-piece-price="gross"]');

                if (!netInput || !vatInput || !grossInput) {
                    return;
                }

                const roundTwo = (value) => Math.round(value * 100) / 100;

                const formatNumber = (value) => {
                    if (!Number.isFinite(value)) {
                        return '';
                    }

                    return value.toFixed(2);
                };

                const recalculateGross = () => {
                    const netRaw = netInput.value;
                    const vatRaw = vatInput.value;

                    if (netRaw.trim() === '' || vatRaw.trim() === '') {
                        grossInput.value = '';
                        return;
                    }

                    const netValue = Number.parseFloat(netRaw);
                    const vatValue = Number.parseFloat(vatRaw);

                    if (!Number.isFinite(netValue) || !Number.isFinite(vatValue)) {
                        grossInput.value = '';
                        return;
                    }

                    const grossValue = roundTwo(netValue * (1 + vatValue / 100));
                    grossInput.value = formatNumber(grossValue);
                };

                const bindRecalculation = (element) => {
                    ['input', 'change'].forEach((eventName) => {
                        element.addEventListener(eventName, recalculateGross);
                    });
                };

                bindRecalculation(netInput);
                bindRecalculation(vatInput);

                recalculateGross();
            });
        </script>
    @endpush
@endonce
