@php
    $currentFormType = $formType ?? old('form_type');
    $currentFormId = (int) ($formId ?? old('form_id'));
@endphp

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
        <div class="modal-dialog" role="document">
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
                        <div class="row g-3">
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <label for="cursa-create-data" class="form-label">Data și ora cursei</label>
                                <input
                                    type="datetime-local"
                                    name="data_cursa"
                                    id="cursa-create-data"
                                    class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('data_cursa') ? 'is-invalid' : '' }}"
                                    value="{{ $isCreateActive ? old('data_cursa', '') : '' }}"
                                >
                                <div
                                    class="invalid-feedback {{ $isCreateActive && $errors->has('data_cursa') ? 'd-block' : '' }}"
                                    data-error-for="data_cursa"
                                >
                                    {{ $isCreateActive ? $errors->first('data_cursa') : '' }}
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
        $editDataValue = $isEditing
            ? old('data_cursa', optional($cursa->data_cursa)->format('Y-m-d\TH:i'))
            : optional($cursa->data_cursa)->format('Y-m-d\TH:i');
    @endphp
    <div
        class="modal fade text-dark"
        id="cursaEditModal{{ $cursa->id }}"
        tabindex="-1"
        role="dialog"
        aria-labelledby="cursaEditModalLabel{{ $cursa->id }}"
        aria-hidden="true"
    >
        <div class="modal-dialog" role="document">
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
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <label for="{{ $editPrefix }}data" class="form-label">Data și ora cursei</label>
                                <input
                                    type="datetime-local"
                                    name="data_cursa"
                                    id="{{ $editPrefix }}data"
                                    class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('data_cursa') ? 'is-invalid' : '' }}"
                                    value="{{ $isEditing ? old('data_cursa', $editDataValue) : $editDataValue }}"
                                >
                                <div
                                    class="invalid-feedback {{ $isEditing && $errors->has('data_cursa') ? 'd-block' : '' }}"
                                    data-error-for="data_cursa"
                                >
                                    {{ $isEditing ? $errors->first('data_cursa') : '' }}
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
