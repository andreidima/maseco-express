@php
    $currentFormType = $formType ?? old('form_type');
    $currentFormId = (int) ($formId ?? old('form_id'));
    $tariCollection = collect($tari ?? [])->keyBy('id');
    $grupuri = collect($valabilitate->cursaGrupuri ?? []);
    $groupFormatOptions = $groupFormatOptions ?? [];
    $groupColorOptions = $groupColorOptions ?? [];
    $renderCurseModals = ($renderCurseModals ?? true) === true;
    $renderGroupModals = ($renderGroupModals ?? true) === true;
    $redirectTo = $redirectTo ?? '';
    $isFlashDivision = optional($valabilitate->divizie)->id === 1
        && strcasecmp((string) optional($valabilitate->divizie)->nume, 'FLASH') === 0;
    $normalizeColorHex = static function ($value): string {
        if (! is_string($value) || $value === '') {
            return '';
        }

        $value = strtoupper(ltrim($value, '#'));

        if (strlen($value) === 3) {
            $value = $value[0] . $value[0] . $value[1] . $value[1] . $value[2] . $value[2];
        }

        if (strlen($value) !== 6) {
            return '';
        }

        return '#' . $value;
    };
    $resolveTextColor = static function ($value) use ($normalizeColorHex): string {
        $hex = $normalizeColorHex($value);

        if ($hex === '') {
            return '#111111';
        }

        $r = $g = $b = 0;
        if (sscanf($hex, '#%02X%02X%02X', $r, $g, $b) !== 3) {
            return '#111111';
        }

        $luminance = ($r * 299 + $g * 587 + $b * 114) / 1000;

        return $luminance > 150 ? '#111111' : '#ffffff';
    };
    $resolveTaraName = static function ($id) use ($tariCollection) {
        if ($id === null || $id === '') {
            return '';
        }

        $id = (int) $id;

        return optional($tariCollection->get($id))->nume ?? '';
    };
    $formatDateTimeValue = static function ($value, string $format) {
        if ($value === null || $value === '') {
            return '';
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->format($format);
        } catch (\Throwable $exception) {
            return '';
        }
    };
@endphp

@if ($renderCurseModals && ($includeCreate ?? false) === true)
    <datalist id="valabilitati-curse-tari">
        @foreach ($tariCollection as $tara)
            <option value="{{ $tara->nume }}" data-id="{{ $tara->id }}"></option>
        @endforeach
    </datalist>
@endif

@php
    $hasBulkGrupuri = $grupuri->isNotEmpty();
@endphp

<div
    class="modal fade text-dark"
    id="curseBulkAssignModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="curseBulkAssignModalLabel"
    aria-hidden="true"
    data-action="{{ $bulkAssignRoute ?? route('valabilitati.curse.bulk-assign', $valabilitate) }}"
    data-has-groups="{{ $hasBulkGrupuri ? 'true' : 'false' }}"
>
    <div class="modal-dialog" role="document" style="--bs-modal-width: 500px;">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="curseBulkAssignModalLabel">Adaugă cursele selectate în grup</h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Închide"></button>
            </div>
            <div class="modal-body">
                @unless ($hasBulkGrupuri)
                    <div class="alert alert-warning" data-bulk-empty-warning>
                        Creează mai întâi un grup pentru a putea adăuga cursele selectate.
                    </div>
                @endunless
                <div class="mb-3">
                    <label for="curse-bulk-group" class="form-label">Alege grupul</label>
                    <select
                        id="curse-bulk-group"
                        class="form-select"
                        @disabled(! $hasBulkGrupuri)
                    >
                        <option value="">Selectează grupul</option>
                        @foreach ($grupuri as $grup)
                            <option value="{{ $grup->id }}">{{ $grup->nume }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-muted small mb-3">
                    Curse selectate: <span id="curse-bulk-selected-count">0</span>
                </div>
                <div id="curse-bulk-feedback" class="text-danger small d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                <button
                    type="button"
                    id="curse-bulk-submit"
                    class="btn btn-primary text-white"
                    @disabled(! $hasBulkGrupuri)
                >
                    <span data-bulk-default-label>Adaugă cursele selectate în grup</span>
                    <span class="d-none" data-bulk-loading-label>
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Se procesează...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

@if ($renderCurseModals && ($includeCreate ?? false) === true)
    @php
        $isCreateActive = $currentFormType === 'create';
    @endphp
    <div
        class="modal fade text-dark"
        id="cursaCreateModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="cursaCreateModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog" role="document" style="--bs-modal-width: 750px;">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="cursaCreateModalLabel">Adaugă cursă</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Închide"></button>
                </div>
                <form
                    action="{{ route('valabilitati.curse.store', $valabilitate) }}"
                    method="POST"
                    class="curse-modal-form"
                    novalidate
                >
                    @csrf
                    <input type="hidden" name="form_type" value="create">
                    <div class="modal-body">
                        @php
                            $createNrCursa = $isCreateActive ? old('nr_cursa', '') : '';
                            $createCursaGrupId = $isCreateActive ? old('cursa_grup_id', '') : '';
                            $createIncarcareTaraId = $isCreateActive ? old('incarcare_tara_id', '') : '';
                            $createIncarcareTaraText = $isCreateActive ? old('incarcare_tara_text', '') : '';
                            if ($createIncarcareTaraText === '' && $createIncarcareTaraId !== '') {
                                $createIncarcareTaraText = $resolveTaraName($createIncarcareTaraId);
                            }

                            $createDescarcareTaraId = $isCreateActive ? old('descarcare_tara_id', '') : '';
                            $createDescarcareTaraText = $isCreateActive ? old('descarcare_tara_text', '') : '';
                            if ($createDescarcareTaraText === '' && $createDescarcareTaraId !== '') {
                                $createDescarcareTaraText = $resolveTaraName($createDescarcareTaraId);
                            }

                            $createKmBordIncarcare = $isCreateActive ? old('km_bord_incarcare', '') : '';
                            $createKmBordDescarcare = $isCreateActive ? old('km_bord_descarcare', '') : '';
                            $createKmMaps = $isCreateActive ? old('km_maps', '') : '';
                            $createKmMapsGol = $isCreateActive ? old('km_maps_gol', '') : '';
                            $createKmMapsPlin = $isCreateActive ? old('km_maps_plin', '') : '';
                            $createKmCuTaxa = $isCreateActive ? old('km_cu_taxa', '') : '';
                            $createKmFlashGol = $isCreateActive ? old('km_flash_gol', '') : '';
                            $createKmFlashPlin = $isCreateActive ? old('km_flash_plin', '') : '';
                            $createAlteTaxe = $isCreateActive ? old('alte_taxe', '') : '';
                            $createFuelTax = $isCreateActive ? old('fuel_tax', '') : '';
                            $createSumaIncasata = $isCreateActive ? old('suma_incasata', '') : '';
                            $createDailyContributionIncasata = $isCreateActive
                                ? old('daily_contribution_incasata', '')
                                : '';
                            $createDataDateValue = $isCreateActive ? old('data_cursa_date', '') : '';
                            $createDataTimeValue = $isCreateActive ? old('data_cursa_time', '') : '';
                            if ($isCreateActive) {
                                $oldCombinedValue = old('data_cursa', '');
                                if ($createDataDateValue === '' && $oldCombinedValue !== '') {
                                    $createDataDateValue = $formatDateTimeValue($oldCombinedValue, 'Y-m-d');
                                }
                                if ($createDataTimeValue === '' && $oldCombinedValue !== '') {
                                    $createDataTimeValue = $formatDateTimeValue($oldCombinedValue, 'H:i');
                                }
                            }
                        @endphp
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="cursa-create-nr" class="form-label">Număr cursă</label>
                                <input
                                    type="text"
                                    name="nr_cursa"
                                    id="cursa-create-nr"
                                    class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('nr_cursa') ? 'is-invalid' : '' }}"
                                    value="{{ $createNrCursa }}"
                                    maxlength="255"
                                >
                                <div
                                    class="invalid-feedback {{ $isCreateActive && $errors->has('nr_cursa') ? 'd-block' : '' }}"
                                    data-error-for="nr_cursa"
                                >
                                    {{ $isCreateActive ? $errors->first('nr_cursa') : '' }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="cursa-create-incarcare-localitate" class="form-label">Localitate încărcare</label>
                                <input
                                    type="text"
                                    name="incarcare_localitate"
                                    id="cursa-create-incarcare-localitate"
                                    class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('incarcare_localitate') ? 'is-invalid' : '' }}"
                                    value="{{ $isCreateActive ? old('incarcare_localitate', '') : '' }}"
                                    maxlength="255"
                                >
                                <div
                                    class="invalid-feedback {{ $isCreateActive && $errors->has('incarcare_localitate') ? 'd-block' : '' }}"
                                    data-error-for="incarcare_localitate"
                                >
                                    {{ $isCreateActive ? $errors->first('incarcare_localitate') : '' }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="cursa-create-incarcare-cod-postal" class="form-label">Cod poștal încărcare</label>
                                <input
                                    type="text"
                                    name="incarcare_cod_postal"
                                    id="cursa-create-incarcare-cod-postal"
                                    class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('incarcare_cod_postal') ? 'is-invalid' : '' }}"
                                    value="{{ $isCreateActive ? old('incarcare_cod_postal', '') : '' }}"
                                    maxlength="255"
                                >
                                <div
                                    class="invalid-feedback {{ $isCreateActive && $errors->has('incarcare_cod_postal') ? 'd-block' : '' }}"
                                    data-error-for="incarcare_cod_postal"
                                >
                                    {{ $isCreateActive ? $errors->first('incarcare_cod_postal') : '' }}
                                </div>
                            </div>
                            <div class="col-md-4" data-country-field>
                                <label for="cursa-create-incarcare-tara" class="form-label">Țară încărcare</label>
                                <input type="hidden" name="incarcare_tara_id" value="{{ $createIncarcareTaraId }}" data-country-hidden="true">
                                <input
                                    type="text"
                                    name="incarcare_tara_text"
                                    id="cursa-create-incarcare-tara"
                                    class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('incarcare_tara_id') ? 'is-invalid' : '' }}"
                                    value="{{ $createIncarcareTaraText }}"
                                    maxlength="255"
                                    autocomplete="off"
                                    list="valabilitati-curse-tari"
                                    data-country-input
                                >
                                <div
                                    class="invalid-feedback {{ $isCreateActive && $errors->has('incarcare_tara_id') ? 'd-block' : '' }}"
                                    data-error-for="incarcare_tara_id"
                                >
                                    {{ $isCreateActive ? $errors->first('incarcare_tara_id') : '' }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="cursa-create-descarcare-localitate" class="form-label">Localitate descărcare</label>
                                <input
                                    type="text"
                                    name="descarcare_localitate"
                                    id="cursa-create-descarcare-localitate"
                                    class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('descarcare_localitate') ? 'is-invalid' : '' }}"
                                    value="{{ $isCreateActive ? old('descarcare_localitate', '') : '' }}"
                                    maxlength="255"
                                >
                                <div
                                    class="invalid-feedback {{ $isCreateActive && $errors->has('descarcare_localitate') ? 'd-block' : '' }}"
                                    data-error-for="descarcare_localitate"
                                >
                                    {{ $isCreateActive ? $errors->first('descarcare_localitate') : '' }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="cursa-create-descarcare-cod-postal" class="form-label">Cod poștal descărcare</label>
                                <input
                                    type="text"
                                    name="descarcare_cod_postal"
                                    id="cursa-create-descarcare-cod-postal"
                                    class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('descarcare_cod_postal') ? 'is-invalid' : '' }}"
                                    value="{{ $isCreateActive ? old('descarcare_cod_postal', '') : '' }}"
                                    maxlength="255"
                                >
                                <div
                                    class="invalid-feedback {{ $isCreateActive && $errors->has('descarcare_cod_postal') ? 'd-block' : '' }}"
                                    data-error-for="descarcare_cod_postal"
                                >
                                    {{ $isCreateActive ? $errors->first('descarcare_cod_postal') : '' }}
                                </div>
                            </div>
                            <div class="col-md-4" data-country-field>
                                <label for="cursa-create-descarcare-tara" class="form-label">Țară descărcare</label>
                                <input type="hidden" name="descarcare_tara_id" value="{{ $createDescarcareTaraId }}" data-country-hidden="true">
                                <input
                                    type="text"
                                    name="descarcare_tara_text"
                                    id="cursa-create-descarcare-tara"
                                    class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('descarcare_tara_id') ? 'is-invalid' : '' }}"
                                    value="{{ $createDescarcareTaraText }}"
                                    maxlength="255"
                                    autocomplete="off"
                                    list="valabilitati-curse-tari"
                                    data-country-input
                                >
                                <div
                                    class="invalid-feedback {{ $isCreateActive && $errors->has('descarcare_tara_id') ? 'd-block' : '' }}"
                                    data-error-for="descarcare_tara_id"
                                >
                                    {{ $isCreateActive ? $errors->first('descarcare_tara_id') : '' }}
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label for="cursa-create-data-date" class="form-label">Data și ora cursei</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input
                                            type="date"
                                            name="data_cursa_date"
                                            id="cursa-create-data-date"
                                            class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('data_cursa') ? 'is-invalid' : '' }}"
                                            value="{{ $createDataDateValue }}"
                                            data-error-proxy="data_cursa"
                                        >
                                    </div>
                                    <div class="col-6">
                                        <input
                                            type="time"
                                            name="data_cursa_time"
                                            id="cursa-create-data-time"
                                            class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('data_cursa') ? 'is-invalid' : '' }}"
                                            value="{{ $createDataTimeValue }}"
                                            step="60"
                                            data-error-proxy="data_cursa"
                                        >
                                    </div>
                                </div>
                                <div
                                    class="invalid-feedback {{ $isCreateActive && $errors->has('data_cursa') ? 'd-block' : '' }}"
                                    data-error-for="data_cursa"
                                >
                                    {{ $isCreateActive ? $errors->first('data_cursa') : '' }}
                                </div>
                            </div>
                            @unless ($isFlashDivision)
                                <div class="col-md-4">
                                    <label for="cursa-create-km-bord-incarcare" class="form-label">Km bord încărcare</label>
                                    <input
                                        type="number"
                                        name="km_bord_incarcare"
                                        id="cursa-create-km-bord-incarcare"
                                        class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('km_bord_incarcare') ? 'is-invalid' : '' }}"
                                        value="{{ $createKmBordIncarcare }}"
                                        min="0"
                                        step="1"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isCreateActive && $errors->has('km_bord_incarcare') ? 'd-block' : '' }}"
                                        data-error-for="km_bord_incarcare"
                                    >
                                        {{ $isCreateActive ? $errors->first('km_bord_incarcare') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="cursa-create-km-bord-descarcare" class="form-label">Km bord descărcare</label>
                                    <input
                                        type="number"
                                        name="km_bord_descarcare"
                                        id="cursa-create-km-bord-descarcare"
                                        class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('km_bord_descarcare') ? 'is-invalid' : '' }}"
                                        value="{{ $createKmBordDescarcare }}"
                                        min="0"
                                        step="1"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isCreateActive && $errors->has('km_bord_descarcare') ? 'd-block' : '' }}"
                                        data-error-for="km_bord_descarcare"
                                    >
                                        {{ $isCreateActive ? $errors->first('km_bord_descarcare') : '' }}
                                    </div>
                                </div>
                            @endunless
                            @unless ($isFlashDivision)
                                <div class="col-md-4">
                                    <label for="cursa-create-km-maps" class="form-label">Km Maps</label>
                                    <input
                                        type="text"
                                        name="km_maps"
                                        id="cursa-create-km-maps"
                                        class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('km_maps') ? 'is-invalid' : '' }}"
                                        value="{{ $createKmMaps }}"
                                        maxlength="255"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isCreateActive && $errors->has('km_maps') ? 'd-block' : '' }}"
                                        data-error-for="km_maps"
                                    >
                                        {{ $isCreateActive ? $errors->first('km_maps') : '' }}
                                    </div>
                                </div>
                            @endunless
                            @if ($isFlashDivision)
                                <div class="col-md-4">
                                    <label for="cursa-create-km-maps-gol" class="form-label">Km Maps gol</label>
                                    <input
                                        type="number"
                                        name="km_maps_gol"
                                        id="cursa-create-km-maps-gol"
                                        class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('km_maps_gol') ? 'is-invalid' : '' }}"
                                        value="{{ $createKmMapsGol }}"
                                        min="0"
                                        step="1"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isCreateActive && $errors->has('km_maps_gol') ? 'd-block' : '' }}"
                                        data-error-for="km_maps_gol"
                                    >
                                        {{ $isCreateActive ? $errors->first('km_maps_gol') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="cursa-create-km-maps-plin" class="form-label">Km Maps plin</label>
                                    <input
                                        type="number"
                                        name="km_maps_plin"
                                        id="cursa-create-km-maps-plin"
                                        class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('km_maps_plin') ? 'is-invalid' : '' }}"
                                        value="{{ $createKmMapsPlin }}"
                                        min="0"
                                        step="1"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isCreateActive && $errors->has('km_maps_plin') ? 'd-block' : '' }}"
                                        data-error-for="km_maps_plin"
                                    >
                                        {{ $isCreateActive ? $errors->first('km_maps_plin') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="cursa-create-km-cu-taxa" class="form-label">Km cu taxă</label>
                                    <input
                                        type="number"
                                        name="km_cu_taxa"
                                        id="cursa-create-km-cu-taxa"
                                        class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('km_cu_taxa') ? 'is-invalid' : '' }}"
                                        value="{{ $createKmCuTaxa }}"
                                        min="0"
                                        step="1"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isCreateActive && $errors->has('km_cu_taxa') ? 'd-block' : '' }}"
                                        data-error-for="km_cu_taxa"
                                    >
                                        {{ $isCreateActive ? $errors->first('km_cu_taxa') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="cursa-create-km-flash-gol" class="form-label">Km Flash gol</label>
                                    <input
                                        type="number"
                                        name="km_flash_gol"
                                        id="cursa-create-km-flash-gol"
                                        class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('km_flash_gol') ? 'is-invalid' : '' }}"
                                        value="{{ $createKmFlashGol }}"
                                        min="0"
                                        step="1"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isCreateActive && $errors->has('km_flash_gol') ? 'd-block' : '' }}"
                                        data-error-for="km_flash_gol"
                                    >
                                        {{ $isCreateActive ? $errors->first('km_flash_gol') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="cursa-create-km-flash-plin" class="form-label">Km Flash plin</label>
                                    <input
                                        type="number"
                                        name="km_flash_plin"
                                        id="cursa-create-km-flash-plin"
                                        class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('km_flash_plin') ? 'is-invalid' : '' }}"
                                        value="{{ $createKmFlashPlin }}"
                                        min="0"
                                        step="1"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isCreateActive && $errors->has('km_flash_plin') ? 'd-block' : '' }}"
                                        data-error-for="km_flash_plin"
                                    >
                                        {{ $isCreateActive ? $errors->first('km_flash_plin') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="cursa-create-alte-taxe" class="form-label">Alte taxe</label>
                                    <input
                                        type="number"
                                        name="alte_taxe"
                                        id="cursa-create-alte-taxe"
                                        class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('alte_taxe') ? 'is-invalid' : '' }}"
                                        value="{{ $createAlteTaxe }}"
                                        min="0"
                                        step="0.01"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isCreateActive && $errors->has('alte_taxe') ? 'd-block' : '' }}"
                                        data-error-for="alte_taxe"
                                    >
                                        {{ $isCreateActive ? $errors->first('alte_taxe') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="cursa-create-fuel-tax" class="form-label">Fuel tax</label>
                                    <input
                                        type="number"
                                        name="fuel_tax"
                                        id="cursa-create-fuel-tax"
                                        class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('fuel_tax') ? 'is-invalid' : '' }}"
                                        value="{{ $createFuelTax }}"
                                        min="0"
                                        step="0.01"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isCreateActive && $errors->has('fuel_tax') ? 'd-block' : '' }}"
                                        data-error-for="fuel_tax"
                                    >
                                        {{ $isCreateActive ? $errors->first('fuel_tax') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="cursa-create-suma-incasata" class="form-label">Sumă încasată</label>
                                    <input
                                        type="number"
                                        name="suma_incasata"
                                        id="cursa-create-suma-incasata"
                                        class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('suma_incasata') ? 'is-invalid' : '' }}"
                                        value="{{ $createSumaIncasata }}"
                                        min="0"
                                        step="0.01"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isCreateActive && $errors->has('suma_incasata') ? 'd-block' : '' }}"
                                        data-error-for="suma_incasata"
                                    >
                                        {{ $isCreateActive ? $errors->first('suma_incasata') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="cursa-create-daily-contribution" class="form-label">Daily contribution (încasat)</label>
                                    <input
                                        type="number"
                                        name="daily_contribution_incasata"
                                        id="cursa-create-daily-contribution"
                                        class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('daily_contribution_incasata') ? 'is-invalid' : '' }}"
                                        value="{{ $createDailyContributionIncasata }}"
                                        min="0"
                                        step="0.01"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isCreateActive && $errors->has('daily_contribution_incasata') ? 'd-block' : '' }}"
                                        data-error-for="daily_contribution_incasata"
                                    >
                                        {{ $isCreateActive ? $errors->first('daily_contribution_incasata') : '' }}
                                    </div>
                                </div>
                            @endif
                            <div class="col-12">
                                <label for="cursa-create-grup" class="form-label">Grup cursă</label>
                                <select
                                    name="cursa_grup_id"
                                    id="cursa-create-grup"
                                    class="form-select bg-white rounded-3 {{ $isCreateActive && $errors->has('cursa_grup_id') ? 'is-invalid' : '' }}"
                                >
                                    <option value="">Fără grup</option>
                                    @foreach ($grupuri as $grup)
                                        <option value="{{ $grup->id }}" @selected((string) $createCursaGrupId === (string) $grup->id)>
                                            {{ $grup->nume ?? 'Fără nume' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div
                                    class="invalid-feedback {{ $isCreateActive && $errors->has('cursa_grup_id') ? 'd-block' : '' }}"
                                    data-error-for="cursa_grup_id"
                                >
                                    {{ $isCreateActive ? $errors->first('cursa_grup_id') : '' }}
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="cursa-create-observatii" class="form-label">Observații</label>
                                <textarea
                                    class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('observatii') ? 'is-invalid' : '' }}"
                                    id="cursa-create-observatii"
                                    name="observatii"
                                    rows="3"
                                >{{ $isCreateActive ? old('observatii', '') : '' }}</textarea>
                                <div
                                    class="invalid-feedback {{ $isCreateActive && $errors->has('observatii') ? 'd-block' : '' }}"
                                    data-error-for="observatii"
                                >
                                    {{ $isCreateActive ? $errors->first('observatii') : '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                        <button type="submit" class="btn btn-success text-white border border-dark rounded-3">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Salvează
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if ($renderCurseModals)
@foreach ($curse as $cursa)
    @php
        $isEditing = $currentFormType === 'edit' && $currentFormId === (int) $cursa->id;
        $editPrefix = 'cursa-edit-' . $cursa->id . '-';
        $baseDataDate = optional($cursa->data_cursa)->format('Y-m-d');
        $baseDataTime = optional($cursa->data_cursa)->format('H:i');
        $editDataDateValue = $isEditing ? old('data_cursa_date', $baseDataDate) : $baseDataDate;
        $editDataTimeValue = $isEditing ? old('data_cursa_time', $baseDataTime) : $baseDataTime;
        if ($isEditing) {
            $oldCombinedValue = old('data_cursa', '');
            if ($editDataDateValue === '' && $oldCombinedValue !== '') {
                $editDataDateValue = $formatDateTimeValue($oldCombinedValue, 'Y-m-d');
            }
            if ($editDataTimeValue === '' && $oldCombinedValue !== '') {
                $editDataTimeValue = $formatDateTimeValue($oldCombinedValue, 'H:i');
            }
        }
        $baseIncarcareTaraId = $cursa->incarcare_tara_id;
        $editIncarcareTaraId = $isEditing ? old('incarcare_tara_id', $baseIncarcareTaraId) : $baseIncarcareTaraId;
        $editIncarcareTaraText = $isEditing ? old('incarcare_tara_text', '') : optional($cursa->incarcareTara)->nume;
        if ($editIncarcareTaraText === '' && $editIncarcareTaraId !== null && $editIncarcareTaraId !== '') {
            $editIncarcareTaraText = $resolveTaraName($editIncarcareTaraId) ?: optional($cursa->incarcareTara)->nume;
        }

        $baseDescarcareTaraId = $cursa->descarcare_tara_id;
        $editDescarcareTaraId = $isEditing ? old('descarcare_tara_id', $baseDescarcareTaraId) : $baseDescarcareTaraId;
        $editDescarcareTaraText = $isEditing ? old('descarcare_tara_text', '') : optional($cursa->descarcareTara)->nume;
        if ($editDescarcareTaraText === '' && $editDescarcareTaraId !== null && $editDescarcareTaraId !== '') {
            $editDescarcareTaraText = $resolveTaraName($editDescarcareTaraId) ?: optional($cursa->descarcareTara)->nume;
        }

        $editKmBordIncarcare = $isEditing ? old('km_bord_incarcare', $cursa->km_bord_incarcare) : $cursa->km_bord_incarcare;
        if ($editKmBordIncarcare === null) {
            $editKmBordIncarcare = '';
        }
        $editKmBordDescarcare = $isEditing ? old('km_bord_descarcare', $cursa->km_bord_descarcare) : $cursa->km_bord_descarcare;
        if ($editKmBordDescarcare === null) {
            $editKmBordDescarcare = '';
        }
        $editKmMaps = $isEditing ? old('km_maps', $cursa->km_maps) : $cursa->km_maps;
        if ($editKmMaps === null) {
            $editKmMaps = '';
        }
        $editKmMapsGol = $isEditing ? old('km_maps_gol', $cursa->km_maps_gol) : $cursa->km_maps_gol;
        if ($editKmMapsGol === null) {
            $editKmMapsGol = '';
        }
        $editKmMapsPlin = $isEditing ? old('km_maps_plin', $cursa->km_maps_plin) : $cursa->km_maps_plin;
        if ($editKmMapsPlin === null) {
            $editKmMapsPlin = '';
        }
        $editKmCuTaxa = $isEditing ? old('km_cu_taxa', $cursa->km_cu_taxa) : $cursa->km_cu_taxa;
        if ($editKmCuTaxa === null) {
            $editKmCuTaxa = '';
        }
        $editKmFlashGol = $isEditing ? old('km_flash_gol', $cursa->km_flash_gol) : $cursa->km_flash_gol;
        if ($editKmFlashGol === null) {
            $editKmFlashGol = '';
        }
        $editKmFlashPlin = $isEditing ? old('km_flash_plin', $cursa->km_flash_plin) : $cursa->km_flash_plin;
        if ($editKmFlashPlin === null) {
            $editKmFlashPlin = '';
        }
        $editAlteTaxe = $isEditing ? old('alte_taxe', $cursa->alte_taxe) : $cursa->alte_taxe;
        if ($editAlteTaxe === null) {
            $editAlteTaxe = '';
        }
        $editFuelTax = $isEditing ? old('fuel_tax', $cursa->fuel_tax) : $cursa->fuel_tax;
        if ($editFuelTax === null) {
            $editFuelTax = '';
        }
        $editSumaIncasata = $isEditing ? old('suma_incasata', $cursa->suma_incasata) : $cursa->suma_incasata;
        if ($editSumaIncasata === null) {
            $editSumaIncasata = '';
        }
        $editDailyContributionIncasata = $isEditing
            ? old('daily_contribution_incasata', $cursa->daily_contribution_incasata)
            : $cursa->daily_contribution_incasata;
        if ($editDailyContributionIncasata === null) {
            $editDailyContributionIncasata = '';
        }
        $editNrCursa = $isEditing ? old('nr_cursa', $cursa->nr_cursa) : $cursa->nr_cursa;
        if ($editNrCursa === null) {
            $editNrCursa = '';
        }
        $baseCursaGrupId = $cursa->cursa_grup_id;
        $editCursaGrupId = $isEditing ? old('cursa_grup_id', $baseCursaGrupId) : $baseCursaGrupId;
        if ($editCursaGrupId === null) {
            $editCursaGrupId = '';
        }
    @endphp
    <div
        class="modal fade text-dark"
        id="cursaEditModal{{ $cursa->id }}"
        tabindex="-1"
        role="dialog"
        aria-labelledby="cursaEditModalLabel{{ $cursa->id }}"
        aria-hidden="true"
    >
        <div class="modal-dialog" role="document" style="--bs-modal-width: 750px;">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="cursaEditModalLabel{{ $cursa->id }}">Modifică cursa</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Închide"></button>
                </div>
                <form
                    action="{{ route('valabilitati.curse.update', [$valabilitate, $cursa]) }}"
                    method="POST"
                    class="curse-modal-form"
                    novalidate
                >
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_type" value="edit">
                    <input type="hidden" name="form_id" value="{{ $cursa->id }}">
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="{{ $editPrefix }}nr" class="form-label">Număr cursă</label>
                                <input
                                    type="text"
                                    name="nr_cursa"
                                    id="{{ $editPrefix }}nr"
                                    class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('nr_cursa') ? 'is-invalid' : '' }}"
                                    value="{{ $editNrCursa }}"
                                    maxlength="255"
                                >
                                <div
                                    class="invalid-feedback {{ $isEditing && $errors->has('nr_cursa') ? 'd-block' : '' }}"
                                    data-error-for="nr_cursa"
                                >
                                    {{ $isEditing ? $errors->first('nr_cursa') : '' }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="{{ $editPrefix }}incarcare-localitate" class="form-label">Localitate încărcare</label>
                                <input
                                    type="text"
                                    name="incarcare_localitate"
                                    id="{{ $editPrefix }}incarcare-localitate"
                                    class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('incarcare_localitate') ? 'is-invalid' : '' }}"
                                    value="{{ $isEditing ? old('incarcare_localitate', $cursa->incarcare_localitate) : $cursa->incarcare_localitate }}"
                                    maxlength="255"
                                >
                                <div
                                    class="invalid-feedback {{ $isEditing && $errors->has('incarcare_localitate') ? 'd-block' : '' }}"
                                    data-error-for="incarcare_localitate"
                                >
                                    {{ $isEditing ? $errors->first('incarcare_localitate') : '' }}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label for="{{ $editPrefix }}incarcare-cod-postal" class="form-label">Cod poștal încărcare</label>
                                <input
                                    type="text"
                                    name="incarcare_cod_postal"
                                    id="{{ $editPrefix }}incarcare-cod-postal"
                                    class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('incarcare_cod_postal') ? 'is-invalid' : '' }}"
                                    value="{{ $isEditing ? old('incarcare_cod_postal', $cursa->incarcare_cod_postal) : $cursa->incarcare_cod_postal }}"
                                    maxlength="255"
                                >
                                <div
                                    class="invalid-feedback {{ $isEditing && $errors->has('incarcare_cod_postal') ? 'd-block' : '' }}"
                                    data-error-for="incarcare_cod_postal"
                                >
                                    {{ $isEditing ? $errors->first('incarcare_cod_postal') : '' }}
                                </div>
                            </div>
                            <div class="col-md-4" data-country-field>
                                <label for="{{ $editPrefix }}incarcare-tara" class="form-label">Țară încărcare</label>
                                <input type="hidden" name="incarcare_tara_id" value="{{ $editIncarcareTaraId }}" data-country-hidden="true">
                                <input
                                    type="text"
                                    name="incarcare_tara_text"
                                    id="{{ $editPrefix }}incarcare-tara"
                                    class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('incarcare_tara_id') ? 'is-invalid' : '' }}"
                                    value="{{ $editIncarcareTaraText }}"
                                    maxlength="255"
                                    autocomplete="off"
                                    list="valabilitati-curse-tari"
                                    data-country-input
                                >
                                <div
                                    class="invalid-feedback {{ $isEditing && $errors->has('incarcare_tara_id') ? 'd-block' : '' }}"
                                    data-error-for="incarcare_tara_id"
                                >
                                    {{ $isEditing ? $errors->first('incarcare_tara_id') : '' }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="{{ $editPrefix }}descarcare-localitate" class="form-label">Localitate descărcare</label>
                                <input
                                    type="text"
                                    name="descarcare_localitate"
                                    id="{{ $editPrefix }}descarcare-localitate"
                                    class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('descarcare_localitate') ? 'is-invalid' : '' }}"
                                    value="{{ $isEditing ? old('descarcare_localitate', $cursa->descarcare_localitate) : $cursa->descarcare_localitate }}"
                                    maxlength="255"
                                >
                                <div
                                    class="invalid-feedback {{ $isEditing && $errors->has('descarcare_localitate') ? 'd-block' : '' }}"
                                    data-error-for="descarcare_localitate"
                                >
                                    {{ $isEditing ? $errors->first('descarcare_localitate') : '' }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label for="{{ $editPrefix }}descarcare-cod-postal" class="form-label">Cod poștal descărcare</label>
                                <input
                                    type="text"
                                    name="descarcare_cod_postal"
                                    id="{{ $editPrefix }}descarcare-cod-postal"
                                    class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('descarcare_cod_postal') ? 'is-invalid' : '' }}"
                                    value="{{ $isEditing ? old('descarcare_cod_postal', $cursa->descarcare_cod_postal) : $cursa->descarcare_cod_postal }}"
                                    maxlength="255"
                                >
                                <div
                                    class="invalid-feedback {{ $isEditing && $errors->has('descarcare_cod_postal') ? 'd-block' : '' }}"
                                    data-error-for="descarcare_cod_postal"
                                >
                                    {{ $isEditing ? $errors->first('descarcare_cod_postal') : '' }}
                                </div>
                            </div>
                            <div class="col-md-4" data-country-field>
                                <label for="{{ $editPrefix }}descarcare-tara" class="form-label">Țară descărcare</label>
                                <input type="hidden" name="descarcare_tara_id" value="{{ $editDescarcareTaraId }}" data-country-hidden="true">
                                <input
                                    type="text"
                                    name="descarcare_tara_text"
                                    id="{{ $editPrefix }}descarcare-tara"
                                    class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('descarcare_tara_id') ? 'is-invalid' : '' }}"
                                    value="{{ $editDescarcareTaraText }}"
                                    maxlength="255"
                                    autocomplete="off"
                                    list="valabilitati-curse-tari"
                                    data-country-input
                                >
                                <div
                                    class="invalid-feedback {{ $isEditing && $errors->has('descarcare_tara_id') ? 'd-block' : '' }}"
                                    data-error-for="descarcare_tara_id"
                                >
                                    {{ $isEditing ? $errors->first('descarcare_tara_id') : '' }}
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label for="{{ $editPrefix }}data-date" class="form-label">Data și ora cursei</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input
                                            type="date"
                                            name="data_cursa_date"
                                            id="{{ $editPrefix }}data-date"
                                            class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('data_cursa') ? 'is-invalid' : '' }}"
                                            value="{{ $editDataDateValue }}"
                                            data-error-proxy="data_cursa"
                                        >
                                    </div>
                                    <div class="col-6">
                                        <input
                                            type="time"
                                            name="data_cursa_time"
                                            id="{{ $editPrefix }}data-time"
                                            class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('data_cursa') ? 'is-invalid' : '' }}"
                                            value="{{ $editDataTimeValue }}"
                                            step="60"
                                            data-error-proxy="data_cursa"
                                        >
                                    </div>
                                </div>
                                <div
                                    class="invalid-feedback {{ $isEditing && $errors->has('data_cursa') ? 'd-block' : '' }}"
                                    data-error-for="data_cursa"
                                >
                                    {{ $isEditing ? $errors->first('data_cursa') : '' }}
                                </div>
                            </div>
                            @unless ($isFlashDivision)
                                <div class="col-md-4">
                                    <label for="{{ $editPrefix }}km-bord-incarcare" class="form-label">Km bord încărcare</label>
                                    <input
                                        type="number"
                                        name="km_bord_incarcare"
                                        id="{{ $editPrefix }}km-bord-incarcare"
                                        class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('km_bord_incarcare') ? 'is-invalid' : '' }}"
                                        value="{{ $editKmBordIncarcare }}"
                                        min="0"
                                        step="1"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isEditing && $errors->has('km_bord_incarcare') ? 'd-block' : '' }}"
                                        data-error-for="km_bord_incarcare"
                                    >
                                        {{ $isEditing ? $errors->first('km_bord_incarcare') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="{{ $editPrefix }}km-bord-descarcare" class="form-label">Km bord descărcare</label>
                                    <input
                                        type="number"
                                        name="km_bord_descarcare"
                                        id="{{ $editPrefix }}km-bord-descarcare"
                                        class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('km_bord_descarcare') ? 'is-invalid' : '' }}"
                                        value="{{ $editKmBordDescarcare }}"
                                        min="0"
                                        step="1"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isEditing && $errors->has('km_bord_descarcare') ? 'd-block' : '' }}"
                                        data-error-for="km_bord_descarcare"
                                    >
                                        {{ $isEditing ? $errors->first('km_bord_descarcare') : '' }}
                                    </div>
                                </div>
                            @endunless
                            @unless ($isFlashDivision)
                                <div class="col-md-4">
                                    <label for="{{ $editPrefix }}km-maps" class="form-label">Km Maps</label>
                                    <input
                                        type="text"
                                        name="km_maps"
                                        id="{{ $editPrefix }}km-maps"
                                        class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('km_maps') ? 'is-invalid' : '' }}"
                                        value="{{ $editKmMaps }}"
                                        maxlength="255"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isEditing && $errors->has('km_maps') ? 'd-block' : '' }}"
                                        data-error-for="km_maps"
                                    >
                                        {{ $isEditing ? $errors->first('km_maps') : '' }}
                                    </div>
                                </div>
                            @endunless
                            @if ($isFlashDivision)
                                <div class="col-md-4">
                                    <label for="{{ $editPrefix }}km-maps-gol" class="form-label">Km Maps gol</label>
                                    <input
                                        type="number"
                                        name="km_maps_gol"
                                        id="{{ $editPrefix }}km-maps-gol"
                                        class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('km_maps_gol') ? 'is-invalid' : '' }}"
                                        value="{{ $editKmMapsGol }}"
                                        min="0"
                                        step="1"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isEditing && $errors->has('km_maps_gol') ? 'd-block' : '' }}"
                                        data-error-for="km_maps_gol"
                                    >
                                        {{ $isEditing ? $errors->first('km_maps_gol') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="{{ $editPrefix }}km-maps-plin" class="form-label">Km Maps plin</label>
                                    <input
                                        type="number"
                                        name="km_maps_plin"
                                        id="{{ $editPrefix }}km-maps-plin"
                                        class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('km_maps_plin') ? 'is-invalid' : '' }}"
                                        value="{{ $editKmMapsPlin }}"
                                        min="0"
                                        step="1"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isEditing && $errors->has('km_maps_plin') ? 'd-block' : '' }}"
                                        data-error-for="km_maps_plin"
                                    >
                                        {{ $isEditing ? $errors->first('km_maps_plin') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="{{ $editPrefix }}km-cu-taxa" class="form-label">Km cu taxă</label>
                                    <input
                                        type="number"
                                        name="km_cu_taxa"
                                        id="{{ $editPrefix }}km-cu-taxa"
                                        class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('km_cu_taxa') ? 'is-invalid' : '' }}"
                                        value="{{ $editKmCuTaxa }}"
                                        min="0"
                                        step="1"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isEditing && $errors->has('km_cu_taxa') ? 'd-block' : '' }}"
                                        data-error-for="km_cu_taxa"
                                    >
                                        {{ $isEditing ? $errors->first('km_cu_taxa') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="{{ $editPrefix }}km-flash-gol" class="form-label">Km Flash gol</label>
                                    <input
                                        type="number"
                                        name="km_flash_gol"
                                        id="{{ $editPrefix }}km-flash-gol"
                                        class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('km_flash_gol') ? 'is-invalid' : '' }}"
                                        value="{{ $editKmFlashGol }}"
                                        min="0"
                                        step="1"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isEditing && $errors->has('km_flash_gol') ? 'd-block' : '' }}"
                                        data-error-for="km_flash_gol"
                                    >
                                        {{ $isEditing ? $errors->first('km_flash_gol') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="{{ $editPrefix }}km-flash-plin" class="form-label">Km Flash plin</label>
                                    <input
                                        type="number"
                                        name="km_flash_plin"
                                        id="{{ $editPrefix }}km-flash-plin"
                                        class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('km_flash_plin') ? 'is-invalid' : '' }}"
                                        value="{{ $editKmFlashPlin }}"
                                        min="0"
                                        step="1"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isEditing && $errors->has('km_flash_plin') ? 'd-block' : '' }}"
                                        data-error-for="km_flash_plin"
                                    >
                                        {{ $isEditing ? $errors->first('km_flash_plin') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="{{ $editPrefix }}alte-taxe" class="form-label">Alte taxe</label>
                                    <input
                                        type="number"
                                        name="alte_taxe"
                                        id="{{ $editPrefix }}alte-taxe"
                                        class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('alte_taxe') ? 'is-invalid' : '' }}"
                                        value="{{ $editAlteTaxe }}"
                                        min="0"
                                        step="0.01"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isEditing && $errors->has('alte_taxe') ? 'd-block' : '' }}"
                                        data-error-for="alte_taxe"
                                    >
                                        {{ $isEditing ? $errors->first('alte_taxe') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="{{ $editPrefix }}fuel-tax" class="form-label">Fuel tax</label>
                                    <input
                                        type="number"
                                        name="fuel_tax"
                                        id="{{ $editPrefix }}fuel-tax"
                                        class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('fuel_tax') ? 'is-invalid' : '' }}"
                                        value="{{ $editFuelTax }}"
                                        min="0"
                                        step="0.01"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isEditing && $errors->has('fuel_tax') ? 'd-block' : '' }}"
                                        data-error-for="fuel_tax"
                                    >
                                        {{ $isEditing ? $errors->first('fuel_tax') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="{{ $editPrefix }}suma-incasata" class="form-label">Sumă încasată</label>
                                    <input
                                        type="number"
                                        name="suma_incasata"
                                        id="{{ $editPrefix }}suma-incasata"
                                        class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('suma_incasata') ? 'is-invalid' : '' }}"
                                        value="{{ $editSumaIncasata }}"
                                        min="0"
                                        step="0.01"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isEditing && $errors->has('suma_incasata') ? 'd-block' : '' }}"
                                        data-error-for="suma_incasata"
                                    >
                                        {{ $isEditing ? $errors->first('suma_incasata') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="{{ $editPrefix }}daily-contribution" class="form-label">Daily contribution (încasat)</label>
                                    <input
                                        type="number"
                                        name="daily_contribution_incasata"
                                        id="{{ $editPrefix }}daily-contribution"
                                        class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('daily_contribution_incasata') ? 'is-invalid' : '' }}"
                                        value="{{ $editDailyContributionIncasata }}"
                                        min="0"
                                        step="0.01"
                                    >
                                    <div
                                        class="invalid-feedback {{ $isEditing && $errors->has('daily_contribution_incasata') ? 'd-block' : '' }}"
                                        data-error-for="daily_contribution_incasata"
                                    >
                                        {{ $isEditing ? $errors->first('daily_contribution_incasata') : '' }}
                                    </div>
                                </div>
                            @endif
                            <div class="col-12">
                                <label for="{{ $editPrefix }}grup" class="form-label">Grup cursă</label>
                                <select
                                    name="cursa_grup_id"
                                    id="{{ $editPrefix }}grup"
                                    class="form-select bg-white rounded-3 {{ $isEditing && $errors->has('cursa_grup_id') ? 'is-invalid' : '' }}"
                                >
                                    <option value="">Fără grup</option>
                                    @foreach ($grupuri as $grup)
                                        <option value="{{ $grup->id }}" @selected((string) $editCursaGrupId === (string) $grup->id)>
                                            {{ $grup->nume ?? 'Fără nume' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div
                                    class="invalid-feedback {{ $isEditing && $errors->has('cursa_grup_id') ? 'd-block' : '' }}"
                                    data-error-for="cursa_grup_id"
                                >
                                    {{ $isEditing ? $errors->first('cursa_grup_id') : '' }}
                                </div>
                            </div>
                            <div class="col-12">
                                <label for="{{ $editPrefix }}observatii" class="form-label">Observații</label>
                                <textarea
                                    class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('observatii') ? 'is-invalid' : '' }}"
                                    id="{{ $editPrefix }}observatii"
                                    name="observatii"
                                    rows="3"
                                >{{ $isEditing ? old('observatii', $cursa->observatii) : $cursa->observatii }}</textarea>
                                <div
                                    class="invalid-feedback {{ $isEditing && $errors->has('observatii') ? 'd-block' : '' }}"
                                    data-error-for="observatii"
                                >
                                    {{ $isEditing ? $errors->first('observatii') : '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                        <button type="submit" class="btn btn-primary text-white border border-dark rounded-3">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Actualizează
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div
        class="modal fade text-dark"
        id="cursaDeleteModal{{ $cursa->id }}"
        tabindex="-1"
        role="dialog"
        aria-labelledby="cursaDeleteModalLabel{{ $cursa->id }}"
        aria-hidden="true"
    >
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="cursaDeleteModalLabel{{ $cursa->id }}">Șterge cursă</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Închide"></button>
                </div>
                <form
                    action="{{ route('valabilitati.curse.destroy', [$valabilitate, $cursa]) }}"
                    method="POST"
                    class="curse-modal-form"
                >
                    @csrf
                    @method('DELETE')
                    <div class="modal-body">
                        Ești sigur că dorești să ștergi această cursă?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                        <button type="submit" class="btn btn-danger text-white border border-dark rounded-3">
                            <i class="fa-solid fa-trash me-1"></i>Șterge
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endif

@if ($renderGroupModals && ($includeCreate ?? false) === true)
    @php
        $isGroupCreateActive = $currentFormType === 'group-create';
        $groupCreateName = $isGroupCreateActive ? old('nume', '') : '';
        $groupCreateRr = $isGroupCreateActive ? old('rr', '') : '';
        $groupCreateFormat = $isGroupCreateActive ? old('format_documente', '') : '';
        $groupCreateZile = $isGroupCreateActive ? old('zile_calculate', '') : '';
        $groupCreateSumaIncasata = $isGroupCreateActive ? old('suma_incasata', '') : '';
        $groupCreateSumaCalculata = $isGroupCreateActive ? old('suma_calculata', '') : '';
        $groupCreateDataFactura = $isGroupCreateActive ? old('data_factura', '') : '';
        $groupCreateNumarFactura = $isGroupCreateActive ? old('numar_factura', '') : '';
        $groupCreateColor = $isGroupCreateActive ? old('culoare_hex', '') : '';
    @endphp
    <div
        class="modal fade text-dark"
        id="cursaGroupCreateModal"
        tabindex="-1"
        role="dialog"
        aria-labelledby="cursaGroupCreateModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog" role="document" style="--bs-modal-width: 650px;">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="cursaGroupCreateModalLabel">Crează grup cursă</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Închide"></button>
                </div>
                <form
                    action="{{ route('valabilitati.curse.grupuri.store', $valabilitate) }}"
                    method="POST"
                    class="curse-modal-form"
                    novalidate
                >
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                    <input type="hidden" name="form_type" value="group-create">
                    <div class="modal-body">
                        <div class="row g-3">
                            @if ($isFlashDivision)
                                <div class="col-md-6">
                                    <label for="group-create-rr" class="form-label">RR</label>
                                    <input
                                        type="text"
                                        class="form-control rounded-3 {{ $isGroupCreateActive && $errors->has('rr') ? 'is-invalid' : '' }}"
                                        id="group-create-rr"
                                        name="rr"
                                        value="{{ $groupCreateRr }}"
                                        maxlength="255"
                                    >
                                    <div class="invalid-feedback {{ $isGroupCreateActive && $errors->has('rr') ? 'd-block' : '' }}" data-error-for="rr">
                                        {{ $isGroupCreateActive ? $errors->first('rr') : '' }}
                                    </div>
                                </div>
                            @else
                                <div class="col-md-6">
                                    <label for="group-create-name" class="form-label">Nume grup</label>
                                    <input
                                        type="text"
                                        class="form-control rounded-3 {{ $isGroupCreateActive && $errors->has('nume') ? 'is-invalid' : '' }}"
                                        id="group-create-name"
                                        name="nume"
                                        value="{{ $groupCreateName }}"
                                        maxlength="255"
                                    >
                                    <div class="invalid-feedback {{ $isGroupCreateActive && $errors->has('nume') ? 'd-block' : '' }}" data-error-for="nume">
                                        {{ $isGroupCreateActive ? $errors->first('nume') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="group-create-format" class="form-label">Format documente</label>
                                    <select
                                        class="form-select rounded-3 {{ $isGroupCreateActive && $errors->has('format_documente') ? 'is-invalid' : '' }}"
                                        id="group-create-format"
                                        name="format_documente"
                                    >
                                        <option value="" @selected($groupCreateFormat === '')>Fără format</option>
                                        @foreach ($groupFormatOptions as $value => $label)
                                            <option value="{{ $value }}" @selected($groupCreateFormat === (string) $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback {{ $isGroupCreateActive && $errors->has('format_documente') ? 'd-block' : '' }}" data-error-for="format_documente">
                                        {{ $isGroupCreateActive ? $errors->first('format_documente') : '' }}
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <label for="group-create-zile" class="form-label">Zile calculate</label>
                                <input
                                    type="number"
                                    min="0"
                                    id="group-create-zile"
                                    name="zile_calculate"
                                    class="form-control rounded-3 {{ $isGroupCreateActive && $errors->has('zile_calculate') ? 'is-invalid' : '' }}"
                                    value="{{ $groupCreateZile }}"
                                >
                                <div class="invalid-feedback {{ $isGroupCreateActive && $errors->has('zile_calculate') ? 'd-block' : '' }}" data-error-for="zile_calculate">
                                    {{ $isGroupCreateActive ? $errors->first('zile_calculate') : '' }}
                                </div>
                            </div>
                            @unless ($isFlashDivision)
                                <div class="col-md-6">
                                    <label for="group-create-suma-incasata" class="form-label">Sumă încasată</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        id="group-create-suma-incasata"
                                        name="suma_incasata"
                                        class="form-control rounded-3 {{ $isGroupCreateActive && $errors->has('suma_incasata') ? 'is-invalid' : '' }}"
                                        value="{{ $groupCreateSumaIncasata }}"
                                    >
                                    <div class="invalid-feedback {{ $isGroupCreateActive && $errors->has('suma_incasata') ? 'd-block' : '' }}" data-error-for="suma_incasata">
                                        {{ $isGroupCreateActive ? $errors->first('suma_incasata') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="group-create-suma-calculata" class="form-label">Sumă calculată</label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        id="group-create-suma-calculata"
                                        name="suma_calculata"
                                        class="form-control rounded-3 {{ $isGroupCreateActive && $errors->has('suma_calculata') ? 'is-invalid' : '' }}"
                                        value="{{ $groupCreateSumaCalculata }}"
                                    >
                                    <div class="invalid-feedback {{ $isGroupCreateActive && $errors->has('suma_calculata') ? 'd-block' : '' }}" data-error-for="suma_calculata">
                                        {{ $isGroupCreateActive ? $errors->first('suma_calculata') : '' }}
                                    </div>
                                </div>
                            @endunless
                            <div class="col-md-6">
                                <label for="group-create-data-factura" class="form-label">Data facturii</label>
                                <input
                                    type="date"
                                    id="group-create-data-factura"
                                    name="data_factura"
                                    class="form-control rounded-3 {{ $isGroupCreateActive && $errors->has('data_factura') ? 'is-invalid' : '' }}"
                                    value="{{ $groupCreateDataFactura }}"
                                >
                                <div class="invalid-feedback {{ $isGroupCreateActive && $errors->has('data_factura') ? 'd-block' : '' }}" data-error-for="data_factura">
                                    {{ $isGroupCreateActive ? $errors->first('data_factura') : '' }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="group-create-numar-factura" class="form-label">Număr factură</label>
                                <input
                                    type="text"
                                    id="group-create-numar-factura"
                                    name="numar_factura"
                                    class="form-control rounded-3 {{ $isGroupCreateActive && $errors->has('numar_factura') ? 'is-invalid' : '' }}"
                                    value="{{ $groupCreateNumarFactura }}"
                                    maxlength="255"
                                >
                                <div class="invalid-feedback {{ $isGroupCreateActive && $errors->has('numar_factura') ? 'd-block' : '' }}" data-error-for="numar_factura">
                                    {{ $isGroupCreateActive ? $errors->first('numar_factura') : '' }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="group-create-color" class="form-label">Culoare</label>
                                @php
                                    $currentCreateColorKey = strtoupper(ltrim((string) $groupCreateColor, '#'));
                                @endphp
                                <select
                                    class="form-select rounded-3 {{ $isGroupCreateActive && $errors->has('culoare_hex') ? 'is-invalid' : '' }}"
                                    id="group-create-color"
                                    name="culoare_hex"
                                >
                                    <option value="">Fără culoare</option>
                                    @foreach ($groupColorOptions as $hex => $label)
                                        @php
                                            $normalizedHex = $normalizeColorHex($hex);
                                            $optionKey = strtoupper(ltrim($normalizedHex, '#'));
                                            $textColor = $resolveTextColor($normalizedHex);
                                        @endphp
                                        <option
                                            value="{{ $normalizedHex }}"
                                            style="background-color: {{ $normalizedHex ?: '#ffffff' }}; color: {{ $textColor }};"
                                            @selected($currentCreateColorKey !== '' && $currentCreateColorKey === $optionKey)
                                        >
                                            {{ $label }} ({{ $normalizedHex ?: strtoupper($hex) }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback {{ $isGroupCreateActive && $errors->has('culoare_hex') ? 'd-block' : '' }}" data-error-for="culoare_hex">
                                    {{ $isGroupCreateActive ? $errors->first('culoare_hex') : '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                        <button type="submit" class="btn btn-info text-white border border-dark rounded-3">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Salvează grupul
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif

@if ($renderGroupModals)
@foreach ($grupuri as $grup)
        @php
            $isGroupEditActive = $currentFormType === 'group-edit' && $currentFormId === (int) $grup->id;
            $groupEditName = $isGroupEditActive ? old('nume', $grup->nume) : $grup->nume;
            $groupEditRr = $isGroupEditActive ? old('rr', $grup->rr) : $grup->rr;
            $groupEditFormat = $isGroupEditActive ? old('format_documente', $grup->format_documente) : $grup->format_documente;
            $groupEditZile = $isGroupEditActive ? old('zile_calculate', $grup->zile_calculate) : $grup->zile_calculate;
            $groupEditSumaIncasata = $isGroupEditActive ? old('suma_incasata', $grup->suma_incasata) : $grup->suma_incasata;
            $groupEditSumaCalculata = $isGroupEditActive ? old('suma_calculata', $grup->suma_calculata) : $grup->suma_calculata;
            $groupEditDataFactura = $isGroupEditActive
                ? old('data_factura', optional($grup->data_factura)->format('Y-m-d'))
                : optional($grup->data_factura)->format('Y-m-d');
            $groupEditNumarFactura = $isGroupEditActive ? old('numar_factura', $grup->numar_factura) : $grup->numar_factura;
            $groupEditColor = $isGroupEditActive ? old('culoare_hex', $grup->culoare_hex) : $grup->culoare_hex;
        @endphp
        <div
            class="modal fade text-dark"
            id="cursaGroupEditModal{{ $grup->id }}"
            tabindex="-1"
            role="dialog"
            aria-labelledby="cursaGroupEditModalLabel{{ $grup->id }}"
            aria-hidden="true"
        >
            <div class="modal-dialog" role="document" style="--bs-modal-width: 650px;">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="cursaGroupEditModalLabel{{ $grup->id }}">Editează grupul {{ $grup->nume }}</h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Închide"></button>
                    </div>
                    <form
                        action="{{ route('valabilitati.curse.grupuri.update', [$valabilitate, $grup]) }}"
                        method="POST"
                        class="curse-modal-form"
                        novalidate
                    >
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                        <input type="hidden" name="form_type" value="group-edit">
                        <input type="hidden" name="form_id" value="{{ $grup->id }}">
                        <div class="modal-body">
                            <div class="row g-3">
                                @if ($isFlashDivision)
                                    <div class="col-md-6">
                                        <label for="group-edit-rr-{{ $grup->id }}" class="form-label">RR</label>
                                        <input
                                            type="text"
                                            class="form-control rounded-3 {{ $isGroupEditActive && $errors->has('rr') ? 'is-invalid' : '' }}"
                                            id="group-edit-rr-{{ $grup->id }}"
                                            name="rr"
                                            value="{{ $groupEditRr }}"
                                            maxlength="255"
                                        >
                                        <div class="invalid-feedback {{ $isGroupEditActive && $errors->has('rr') ? 'd-block' : '' }}" data-error-for="rr">
                                            {{ $isGroupEditActive ? $errors->first('rr') : '' }}
                                        </div>
                                    </div>
                                @else
                                    <div class="col-md-6">
                                        <label for="group-edit-name-{{ $grup->id }}" class="form-label">Nume grup</label>
                                        <input
                                            type="text"
                                            class="form-control rounded-3 {{ $isGroupEditActive && $errors->has('nume') ? 'is-invalid' : '' }}"
                                            id="group-edit-name-{{ $grup->id }}"
                                            name="nume"
                                            value="{{ $groupEditName }}"
                                            maxlength="255"
                                        >
                                        <div class="invalid-feedback {{ $isGroupEditActive && $errors->has('nume') ? 'd-block' : '' }}" data-error-for="nume">
                                            {{ $isGroupEditActive ? $errors->first('nume') : '' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="group-edit-format-{{ $grup->id }}" class="form-label">Format documente</label>
                                        <select
                                            class="form-select rounded-3 {{ $isGroupEditActive && $errors->has('format_documente') ? 'is-invalid' : '' }}"
                                            id="group-edit-format-{{ $grup->id }}"
                                            name="format_documente"
                                        >
                                            <option value="" @selected($groupEditFormat === null || $groupEditFormat === '')>Fără format</option>
                                            @foreach ($groupFormatOptions as $value => $label)
                                                <option value="{{ $value }}" @selected((string) $groupEditFormat === (string) $value)>
                                                    {{ $label }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback {{ $isGroupEditActive && $errors->has('format_documente') ? 'd-block' : '' }}" data-error-for="format_documente">
                                            {{ $isGroupEditActive ? $errors->first('format_documente') : '' }}
                                        </div>
                                    </div>
                                @endif
                                <div class="col-md-6">
                                    <label for="group-edit-zile-{{ $grup->id }}" class="form-label">Zile calculate</label>
                                    <input
                                        type="number"
                                        min="0"
                                        id="group-edit-zile-{{ $grup->id }}"
                                        name="zile_calculate"
                                        class="form-control rounded-3 {{ $isGroupEditActive && $errors->has('zile_calculate') ? 'is-invalid' : '' }}"
                                        value="{{ $groupEditZile }}"
                                    >
                                <div class="invalid-feedback {{ $isGroupEditActive && $errors->has('zile_calculate') ? 'd-block' : '' }}" data-error-for="zile_calculate">
                                    {{ $isGroupEditActive ? $errors->first('zile_calculate') : '' }}
                                </div>
                            </div>
                                @unless ($isFlashDivision)
                                    <div class="col-md-6">
                                        <label for="group-edit-suma-incasata-{{ $grup->id }}" class="form-label">Sumă încasată</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            id="group-edit-suma-incasata-{{ $grup->id }}"
                                            name="suma_incasata"
                                            class="form-control rounded-3 {{ $isGroupEditActive && $errors->has('suma_incasata') ? 'is-invalid' : '' }}"
                                            value="{{ $groupEditSumaIncasata }}"
                                        >
                                        <div class="invalid-feedback {{ $isGroupEditActive && $errors->has('suma_incasata') ? 'd-block' : '' }}" data-error-for="suma_incasata">
                                            {{ $isGroupEditActive ? $errors->first('suma_incasata') : '' }}
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="group-edit-suma-calculata-{{ $grup->id }}" class="form-label">Sumă calculată</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            id="group-edit-suma-calculata-{{ $grup->id }}"
                                            name="suma_calculata"
                                            class="form-control rounded-3 {{ $isGroupEditActive && $errors->has('suma_calculata') ? 'is-invalid' : '' }}"
                                            value="{{ $groupEditSumaCalculata }}"
                                        >
                                        <div class="invalid-feedback {{ $isGroupEditActive && $errors->has('suma_calculata') ? 'd-block' : '' }}" data-error-for="suma_calculata">
                                            {{ $isGroupEditActive ? $errors->first('suma_calculata') : '' }}
                                        </div>
                                    </div>
                                @endunless
                                <div class="col-md-6">
                                    <label for="group-edit-data-factura-{{ $grup->id }}" class="form-label">Data facturii</label>
                                    <input
                                        type="date"
                                        id="group-edit-data-factura-{{ $grup->id }}"
                                        name="data_factura"
                                        class="form-control rounded-3 {{ $isGroupEditActive && $errors->has('data_factura') ? 'is-invalid' : '' }}"
                                        value="{{ $groupEditDataFactura }}"
                                    >
                                    <div class="invalid-feedback {{ $isGroupEditActive && $errors->has('data_factura') ? 'd-block' : '' }}" data-error-for="data_factura">
                                        {{ $isGroupEditActive ? $errors->first('data_factura') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="group-edit-numar-factura-{{ $grup->id }}" class="form-label">Număr factură</label>
                                    <input
                                        type="text"
                                        id="group-edit-numar-factura-{{ $grup->id }}"
                                        name="numar_factura"
                                        class="form-control rounded-3 {{ $isGroupEditActive && $errors->has('numar_factura') ? 'is-invalid' : '' }}"
                                        value="{{ $groupEditNumarFactura }}"
                                        maxlength="255"
                                    >
                                    <div class="invalid-feedback {{ $isGroupEditActive && $errors->has('numar_factura') ? 'd-block' : '' }}" data-error-for="numar_factura">
                                        {{ $isGroupEditActive ? $errors->first('numar_factura') : '' }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="group-edit-color-{{ $grup->id }}" class="form-label">Culoare</label>
                                    @php
                                        $currentEditColorKey = strtoupper(ltrim((string) $groupEditColor, '#'));
                                    @endphp
                                    <select
                                        class="form-select rounded-3 {{ $isGroupEditActive && $errors->has('culoare_hex') ? 'is-invalid' : '' }}"
                                        id="group-edit-color-{{ $grup->id }}"
                                        name="culoare_hex"
                                    >
                                        <option value="" @selected($groupEditColor === null || $groupEditColor === '')>Fără culoare</option>
                                        @foreach ($groupColorOptions as $hex => $label)
                                            @php
                                                $normalizedHex = $normalizeColorHex($hex);
                                                $optionKey = strtoupper(ltrim($normalizedHex, '#'));
                                                $textColor = $resolveTextColor($normalizedHex);
                                            @endphp
                                            <option
                                                value="{{ $normalizedHex }}"
                                                style="background-color: {{ $normalizedHex ?: '#ffffff' }}; color: {{ $textColor }};"
                                                @selected($currentEditColorKey !== '' && $currentEditColorKey === $optionKey)
                                            >
                                                {{ $label }} ({{ $normalizedHex ?: strtoupper($hex) }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback {{ $isGroupEditActive && $errors->has('culoare_hex') ? 'd-block' : '' }}" data-error-for="culoare_hex">
                                        {{ $isGroupEditActive ? $errors->first('culoare_hex') : '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                            <button type="submit" class="btn btn-primary text-white border border-dark rounded-3">
                                <i class="fa-solid fa-floppy-disk me-1"></i>Actualizează grupul
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div
            class="modal fade text-dark"
            id="cursaGroupDeleteModal{{ $grup->id }}"
            tabindex="-1"
            role="dialog"
            aria-labelledby="cursaGroupDeleteModalLabel{{ $grup->id }}"
            aria-hidden="true"
        >
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="cursaGroupDeleteModalLabel{{ $grup->id }}">Șterge grupul {{ $grup->nume }}</h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Închide"></button>
                    </div>
                    <form
                        action="{{ route('valabilitati.curse.grupuri.destroy', [$valabilitate, $grup]) }}"
                        method="POST"
                        class="curse-modal-form"
                    >
                        @csrf
                        @method('DELETE')
                        <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                        <div class="modal-body">
                            Ești sigur că dorești să ștergi acest grup? Curse asociate vor pierde culoarea și metadatele de grup.
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                            <button type="submit" class="btn btn-danger text-white border border-dark rounded-3">
                                <i class="fa-solid fa-trash me-1"></i>Șterge grupul
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endif
