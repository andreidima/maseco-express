@php
    $fieldName = $fieldName ?? 'taxe_drum';
    $formPrefix = $formPrefix ?? '';
    $isActive = $isActive ?? false;

    $normalizedEntries = collect($entries ?? [])->map(function ($entry) {
        if ($entry instanceof \App\Models\ValabilitateTaxaDrum) {
            return [
                'nume' => $entry->nume ?? '',
                'tara' => $entry->tara ?? '',
                'suma' => $entry->suma !== null ? number_format((float) $entry->suma, 2, '.', '') : '',
                'moneda' => $entry->moneda ?? '',
                'data' => optional($entry->data)->format('Y-m-d') ?? '',
                'observatii' => $entry->observatii ?? '',
            ];
        }

        return [
            'nume' => $entry['nume'] ?? '',
            'tara' => $entry['tara'] ?? '',
            'suma' => isset($entry['suma']) && $entry['suma'] !== null ? (string) $entry['suma'] : '',
            'moneda' => $entry['moneda'] ?? '',
            'data' => $entry['data'] ?? '',
            'observatii' => $entry['observatii'] ?? '',
        ];
    })->values();

    $entriesCount = $normalizedEntries->count();
    $generalErrorKey = $fieldName;
    $generalHasError = $isActive && $errors->has($generalErrorKey);
    $idPrefix = $formPrefix . 'taxe-drum-';
@endphp

<div
    class="mb-4 p-3 p-lg-4 rounded-4 border shadow-sm"
    style="border-color: rgba(13, 110, 253, 0.35) !important; background-color: rgba(13, 110, 253, 0.05);"
    data-road-tax-wrapper
>
    <div class="d-flex flex-column flex-lg-row justify-content-lg-between align-items-lg-center gap-3">
        <div class="d-flex flex-column gap-2">
            <span
                class="badge text-primary fw-semibold text-uppercase px-3 py-2"
                style="background-color: rgba(13, 110, 253, 0.1);"
            >
                <i class="fa-solid fa-road me-2"></i>Taxe de drum
            </span>
            <span class="small text-muted">Secțiune dedicată taxelor speciale ale cursei.</span>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" data-road-tax-add>
            <i class="fa-solid fa-plus me-1"></i>Adaugă taxă de drum
        </button>
    </div>
    <div class="invalid-feedback {{ $generalHasError ? 'd-block' : '' }}" data-error-for="{{ $generalErrorKey }}">
        {{ $generalHasError ? $errors->first($generalErrorKey) : '' }}
    </div>

    <div
        class="road-tax-collection mt-3"
        data-next-index="{{ $entriesCount }}"
        data-field-name="{{ $fieldName }}"
        data-id-prefix="{{ $idPrefix }}"
    >
        <p class="text-muted fst-italic road-tax-empty {{ $entriesCount ? 'd-none' : '' }}">
            Nu există taxe de drum adăugate.
        </p>
        <div class="road-tax-items">
            @foreach ($normalizedEntries as $index => $taxa)
                @php
                    $fieldPrefix = $fieldName . '[' . $index . ']';
                    $errorPrefix = $fieldName . '.' . $index . '.';
                    $rowIdPrefix = $idPrefix . $index . '-';

                    $numeErrorKey = $errorPrefix . 'nume';
                    $taraErrorKey = $errorPrefix . 'tara';
                    $sumaErrorKey = $errorPrefix . 'suma';
                    $monedaErrorKey = $errorPrefix . 'moneda';
                    $dataErrorKey = $errorPrefix . 'data';
                    $observatiiErrorKey = $errorPrefix . 'observatii';

                    $numeHasError = $isActive && $errors->has($numeErrorKey);
                    $taraHasError = $isActive && $errors->has($taraErrorKey);
                    $sumaHasError = $isActive && $errors->has($sumaErrorKey);
                    $monedaHasError = $isActive && $errors->has($monedaErrorKey);
                    $dataHasError = $isActive && $errors->has($dataErrorKey);
                    $observatiiHasError = $isActive && $errors->has($observatiiErrorKey);
                @endphp
                <div class="card mb-3 road-tax-entry" data-index="{{ $index }}">
                    <div class="card-body pb-0">
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label for="{{ $rowIdPrefix }}nume" class="form-label">Nume<span class="text-danger">*</span></label>
                                <input
                                    type="text"
                                    name="{{ $fieldPrefix }}[nume]"
                                    id="{{ $rowIdPrefix }}nume"
                                    class="form-control bg-white rounded-3 {{ $numeHasError ? 'is-invalid' : '' }}"
                                    value="{{ $taxa['nume'] }}"
                                    autocomplete="off"
                                    required
                                >
                                <div class="invalid-feedback {{ $numeHasError ? 'd-block' : '' }}" data-error-for="{{ $numeErrorKey }}">
                                    {{ $numeHasError ? $errors->first($numeErrorKey) : '' }}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="{{ $rowIdPrefix }}tara" class="form-label">Țară</label>
                                <input
                                    type="text"
                                    name="{{ $fieldPrefix }}[tara]"
                                    id="{{ $rowIdPrefix }}tara"
                                    class="form-control bg-white rounded-3 {{ $taraHasError ? 'is-invalid' : '' }}"
                                    value="{{ $taxa['tara'] }}"
                                    autocomplete="off"
                                >
                                <div class="invalid-feedback {{ $taraHasError ? 'd-block' : '' }}" data-error-for="{{ $taraErrorKey }}">
                                    {{ $taraHasError ? $errors->first($taraErrorKey) : '' }}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="{{ $rowIdPrefix }}suma" class="form-label">Sumă</label>
                                <input
                                    type="text"
                                    name="{{ $fieldPrefix }}[suma]"
                                    id="{{ $rowIdPrefix }}suma"
                                    class="form-control bg-white rounded-3 {{ $sumaHasError ? 'is-invalid' : '' }}"
                                    value="{{ $taxa['suma'] }}"
                                    autocomplete="off"
                                    inputmode="decimal"
                                >
                                <div class="invalid-feedback {{ $sumaHasError ? 'd-block' : '' }}" data-error-for="{{ $sumaErrorKey }}">
                                    {{ $sumaHasError ? $errors->first($sumaErrorKey) : '' }}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="{{ $rowIdPrefix }}moneda" class="form-label">Monedă</label>
                                <input
                                    type="text"
                                    name="{{ $fieldPrefix }}[moneda]"
                                    id="{{ $rowIdPrefix }}moneda"
                                    class="form-control bg-white rounded-3 text-uppercase {{ $monedaHasError ? 'is-invalid' : '' }}"
                                    value="{{ $taxa['moneda'] }}"
                                    autocomplete="off"
                                >
                                <div class="invalid-feedback {{ $monedaHasError ? 'd-block' : '' }}" data-error-for="{{ $monedaErrorKey }}">
                                    {{ $monedaHasError ? $errors->first($monedaErrorKey) : '' }}
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label for="{{ $rowIdPrefix }}data" class="form-label">Dată</label>
                                <input
                                    type="date"
                                    name="{{ $fieldPrefix }}[data]"
                                    id="{{ $rowIdPrefix }}data"
                                    class="form-control bg-white rounded-3 {{ $dataHasError ? 'is-invalid' : '' }}"
                                    value="{{ $taxa['data'] }}"
                                >
                                <div class="invalid-feedback {{ $dataHasError ? 'd-block' : '' }}" data-error-for="{{ $dataErrorKey }}">
                                    {{ $dataHasError ? $errors->first($dataErrorKey) : '' }}
                                </div>
                            </div>
                            <div class="col-12 col-md-1 text-md-end">
                                <button type="button" class="btn btn-outline-danger btn-sm" data-road-tax-remove>
                                    <i class="fa-solid fa-trash-can me-1"></i>Șterge
                                </button>
                            </div>
                            <div class="col-12">
                                <label for="{{ $rowIdPrefix }}observatii" class="form-label">Observații</label>
                                <textarea
                                    name="{{ $fieldPrefix }}[observatii]"
                                    id="{{ $rowIdPrefix }}observatii"
                                    class="form-control bg-white rounded-3 {{ $observatiiHasError ? 'is-invalid' : '' }}"
                                    rows="2"
                                >{{ $taxa['observatii'] }}</textarea>
                                <div class="invalid-feedback {{ $observatiiHasError ? 'd-block' : '' }}" data-error-for="{{ $observatiiErrorKey }}">
                                    {{ $observatiiHasError ? $errors->first($observatiiErrorKey) : '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <template class="road-tax-template">
            <div class="card mb-3 road-tax-entry" data-index="__INDEX__">
                <div class="card-body pb-0">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-3">
                            <label for="{{ $idPrefix }}__INDEX__-nume" class="form-label">Nume<span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="{{ $fieldName }}[__INDEX__][nume]"
                                id="{{ $idPrefix }}__INDEX__-nume"
                                class="form-control bg-white rounded-3"
                                autocomplete="off"
                                required
                            >
                            <div class="invalid-feedback" data-error-for="{{ $fieldName }}.__INDEX__.nume"></div>
                        </div>
                        <div class="col-md-2">
                            <label for="{{ $idPrefix }}__INDEX__-tara" class="form-label">Țară</label>
                            <input
                                type="text"
                                name="{{ $fieldName }}[__INDEX__][tara]"
                                id="{{ $idPrefix }}__INDEX__-tara"
                                class="form-control bg-white rounded-3"
                                autocomplete="off"
                            >
                            <div class="invalid-feedback" data-error-for="{{ $fieldName }}.__INDEX__.tara"></div>
                        </div>
                        <div class="col-md-2">
                            <label for="{{ $idPrefix }}__INDEX__-suma" class="form-label">Sumă</label>
                            <input
                                type="text"
                                name="{{ $fieldName }}[__INDEX__][suma]"
                                id="{{ $idPrefix }}__INDEX__-suma"
                                class="form-control bg-white rounded-3"
                                autocomplete="off"
                                inputmode="decimal"
                            >
                            <div class="invalid-feedback" data-error-for="{{ $fieldName }}.__INDEX__.suma"></div>
                        </div>
                        <div class="col-md-2">
                            <label for="{{ $idPrefix }}__INDEX__-moneda" class="form-label">Monedă</label>
                            <input
                                type="text"
                                name="{{ $fieldName }}[__INDEX__][moneda]"
                                id="{{ $idPrefix }}__INDEX__-moneda"
                                class="form-control bg-white rounded-3 text-uppercase"
                                autocomplete="off"
                            >
                            <div class="invalid-feedback" data-error-for="{{ $fieldName }}.__INDEX__.moneda"></div>
                        </div>
                        <div class="col-md-2">
                            <label for="{{ $idPrefix }}__INDEX__-data" class="form-label">Dată</label>
                            <input
                                type="date"
                                name="{{ $fieldName }}[__INDEX__][data]"
                                id="{{ $idPrefix }}__INDEX__-data"
                                class="form-control bg-white rounded-3"
                            >
                            <div class="invalid-feedback" data-error-for="{{ $fieldName }}.__INDEX__.data"></div>
                        </div>
                        <div class="col-12 col-md-1 text-md-end">
                            <button type="button" class="btn btn-outline-danger btn-sm" data-road-tax-remove>
                                <i class="fa-solid fa-trash-can me-1"></i>Șterge
                            </button>
                        </div>
                        <div class="col-12">
                            <label for="{{ $idPrefix }}__INDEX__-observatii" class="form-label">Observații</label>
                            <textarea
                                name="{{ $fieldName }}[__INDEX__][observatii]"
                                id="{{ $idPrefix }}__INDEX__-observatii"
                                class="form-control bg-white rounded-3"
                                rows="2"
                            ></textarea>
                            <div class="invalid-feedback" data-error-for="{{ $fieldName }}.__INDEX__.observatii"></div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>
