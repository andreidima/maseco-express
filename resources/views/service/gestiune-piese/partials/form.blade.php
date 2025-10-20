@php
    $editing = isset($piesa);

    $normalizeDecimal = static function ($value) {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $value = str_replace([' ', ','], ['', '.'], $value);
        }

        return is_numeric($value) ? (float) $value : null;
    };

    $oldInitialRaw = old('cantitate_initiala');
    $oldRemainingRaw = old('nr_bucati');

    $initialInputSource = $oldInitialRaw;

    if ($initialInputSource === null || $initialInputSource === '') {
        $initialInputSource = $editing
            ? ($piesa->cantitate_initiala ?? $piesa->nr_bucati ?? '')
            : '';
    }

    $initialInputNumeric = $normalizeDecimal($initialInputSource);
    $initialFieldValue = $initialInputNumeric === null
        ? ($initialInputSource ?? '')
        : number_format($initialInputNumeric, 2, '.', '');

    $remainingInputSource = $oldRemainingRaw;

    if ($remainingInputSource === null || $remainingInputSource === '') {
        $remainingInputSource = $editing ? ($piesa->nr_bucati ?? '') : '';
    }

    $remainingInputNumeric = $normalizeDecimal($remainingInputSource);
    $remainingFieldValue = $remainingInputNumeric === null
        ? ($remainingInputSource ?? '')
        : number_format($remainingInputNumeric, 2, '.', '');

    $initialFromDb = $editing ? $normalizeDecimal($piesa->cantitate_initiala ?? $piesa->nr_bucati ?? null) : null;
    $remainingFromDb = $editing ? $normalizeDecimal($piesa->nr_bucati ?? null) : null;

    $oldInitialNumeric = $normalizeDecimal($oldInitialRaw);
    $oldRemainingNumeric = $normalizeDecimal($oldRemainingRaw);

    $consumedBaseline = null;

    if ($oldInitialNumeric !== null && $oldRemainingNumeric !== null) {
        $consumedBaseline = max($oldInitialNumeric - $oldRemainingNumeric, 0);
    } elseif ($initialFromDb !== null && $remainingFromDb !== null) {
        $consumedBaseline = max($initialFromDb - $remainingFromDb, 0);
    }

    $consumedBaselineValue = $consumedBaseline === null
        ? ''
        : number_format($consumedBaseline, 2, '.', '');
    $initialBenchmarkValue = $initialFromDb === null
        ? ''
        : number_format($initialFromDb, 2, '.', '');
    $remainingBenchmarkValue = $remainingFromDb === null
        ? ''
        : number_format($remainingFromDb, 2, '.', '');
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
            class="form-control rounded-3" value="{{ $initialFieldValue }}" data-piece-quantity="initial"
            data-piece-used="{{ $consumedBaselineValue }}" data-piece-initial-benchmark="{{ $initialBenchmarkValue }}"
            data-piece-remaining-benchmark="{{ $remainingBenchmarkValue }}">
        <small class="text-muted">Dacă lipsește, valoarea va fi completată din stocul curent.</small>
        <input type="hidden" name="nr_bucati" id="nr_bucati" value="{{ $remainingFieldValue }}"
            data-piece-quantity="remaining">
        <input type="hidden" data-piece-quantity="consumed" value="{{ $consumedBaselineValue }}">
            class="form-control rounded-3" value="{{ old('cantitate_initiala', $editing ? $piesa->cantitate_initiala : '') }}">
        @error('cantitate_initiala')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        @error('nr_bucati')
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
                const initialQtyInput = document.querySelector('[data-piece-quantity="initial"]');
                const remainingQtyInput = document.querySelector('[data-piece-quantity="remaining"]');

                const roundTwo = (value) => Math.round(value * 100) / 100;

                const formatNumber = (value) => {
                    if (!Number.isFinite(value)) {
                        return '';
                    }

                    return value.toFixed(2);
                };

                const parseNullableFloat = (raw) => {
                    if (raw === null || raw === undefined) {
                        return null;
                    }

                    if (typeof raw === 'number') {
                        return Number.isFinite(raw) ? raw : null;
                    }

                    if (typeof raw !== 'string') {
                        return null;
                    }

                    const normalized = raw.trim().replace(/\s+/g, '').replace(/,/g, '.');

                    if (normalized === '') {
                        return null;
                    }

                    const parsed = Number.parseFloat(normalized);

                    return Number.isFinite(parsed) ? parsed : null;
                };

                if (netInput && vatInput && grossInput) {
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
                }

                if (initialQtyInput && remainingQtyInput) {
                    const consumedPieces = (() => {
                        const consumedHolder = document.querySelector('[data-piece-quantity="consumed"]');
                        const consumedFromHolder = consumedHolder ? parseNullableFloat(consumedHolder.value) : null;

                        if (consumedFromHolder !== null) {
                            return consumedFromHolder;
                        }

                        const consumedFromDataset = parseNullableFloat(initialQtyInput.dataset.pieceUsed ?? '');

                        if (consumedFromDataset !== null) {
                            return consumedFromDataset;
                        }

                        const initialBenchmark = parseNullableFloat(initialQtyInput.dataset.pieceInitialBenchmark ?? '');
                        const remainingBenchmark = parseNullableFloat(initialQtyInput.dataset.pieceRemainingBenchmark ?? '');

                        if (initialBenchmark !== null && remainingBenchmark !== null) {
                            return Math.max(roundTwo(initialBenchmark - remainingBenchmark), 0);
                        }

                        const initialAtLoad = parseNullableFloat(initialQtyInput.value);
                        const remainingAtLoad = parseNullableFloat(remainingQtyInput.value);

                        if (initialAtLoad !== null && remainingAtLoad !== null) {
                            return Math.max(roundTwo(initialAtLoad - remainingAtLoad), 0);
                        }

                        return null;
                    })();

                    const recalculateRemaining = () => {
                        const initialValue = parseNullableFloat(initialQtyInput.value);

                        if (initialValue === null) {
                            remainingQtyInput.value = '';
                            return;
                        }

                        if (consumedPieces === null) {
                            remainingQtyInput.value = formatNumber(initialValue);
                            return;
                        }

                        const remainingValue = Math.max(roundTwo(initialValue - consumedPieces), 0);
                        remainingQtyInput.value = formatNumber(remainingValue);
                    };

                    ['input', 'change'].forEach((eventName) => {
                        initialQtyInput.addEventListener(eventName, recalculateRemaining);
                    });

                    recalculateRemaining();
                }
            });
        </script>
    @endpush
@endonce
