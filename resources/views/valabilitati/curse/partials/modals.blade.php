@php
    $currentFormType = $formType ?? old('form_type');
    $currentFormId = (int) ($formId ?? old('form_id'));
    $tariCollection = collect($tari ?? [])->keyBy('id');
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

@if (($includeCreate ?? false) === true)
    <datalist id="valabilitati-curse-tari">
        @foreach ($tariCollection as $tara)
            <option value="{{ $tara->nume }}" data-id="{{ $tara->id }}"></option>
        @endforeach
    </datalist>
@endif

@if (($includeCreate ?? false) === true)
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

                            $createKmBord = $isCreateActive ? old('km_bord', '') : '';
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
                            <div class="col-md-4">
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
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <label for="cursa-create-km-bord" class="form-label">Km bord</label>
                                <input
                                    type="number"
                                    name="km_bord"
                                    id="cursa-create-km-bord"
                                    class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('km_bord') ? 'is-invalid' : '' }}"
                                    value="{{ $createKmBord }}"
                                    min="0"
                                    step="1"
                                >
                                <div
                                    class="invalid-feedback {{ $isCreateActive && $errors->has('km_bord') ? 'd-block' : '' }}"
                                    data-error-for="km_bord"
                                >
                                    {{ $isCreateActive ? $errors->first('km_bord') : '' }}
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

        $editKmBordValue = $isEditing ? old('km_bord', $cursa->km_bord) : $cursa->km_bord;
        if ($editKmBordValue === null) {
            $editKmBordValue = '';
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
                            <div class="col-md-4">
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
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <label for="{{ $editPrefix }}km-bord" class="form-label">Km bord</label>
                                <input
                                    type="number"
                                    name="km_bord"
                                    id="{{ $editPrefix }}km-bord"
                                    class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('km_bord') ? 'is-invalid' : '' }}"
                                    value="{{ $editKmBordValue }}"
                                    min="0"
                                    step="1"
                                >
                                <div
                                    class="invalid-feedback {{ $isEditing && $errors->has('km_bord') ? 'd-block' : '' }}"
                                    data-error-for="km_bord"
                                >
                                    {{ $isEditing ? $errors->first('km_bord') : '' }}
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
