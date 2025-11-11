@php
    $currentFormType = $formType ?? old('form_type');
    $currentFormId = (int) ($formId ?? old('form_id'));
    $driverOptions = $soferi ?? [];
@endphp

@if (($includeCreate ?? false) === true)
    @php
        $isCreateActive = $currentFormType === 'create';
    @endphp
    <div class="modal fade text-dark" id="valabilitateCreateModal" tabindex="-1" role="dialog" aria-labelledby="valabilitateCreateModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="valabilitateCreateModalLabel">Adaugă valabilitate</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Închide"></button>
                </div>
                <form action="{{ route('valabilitati.store') }}" method="POST" class="valabilitati-modal-form" novalidate>
                    @csrf
                    <input type="hidden" name="form_type" value="create">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="valabilitate-create-denumire" class="form-label">Denumire<span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="denumire"
                                id="valabilitate-create-denumire"
                                class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('denumire') ? 'is-invalid' : '' }}"
                                value="{{ $isCreateActive ? old('denumire', '') : '' }}"
                                autocomplete="off"
                                required
                            >
                            <div class="invalid-feedback {{ $isCreateActive && $errors->has('denumire') ? 'd-block' : '' }}" data-error-for="denumire">
                                {{ $isCreateActive ? $errors->first('denumire') : '' }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="valabilitate-create-numar-auto" class="form-label">Număr auto<span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="numar_auto"
                                id="valabilitate-create-numar-auto"
                                class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('numar_auto') ? 'is-invalid' : '' }}"
                                value="{{ $isCreateActive ? old('numar_auto', '') : '' }}"
                                autocomplete="off"
                                required
                            >
                            <div class="invalid-feedback {{ $isCreateActive && $errors->has('numar_auto') ? 'd-block' : '' }}" data-error-for="numar_auto">
                                {{ $isCreateActive ? $errors->first('numar_auto') : '' }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="valabilitate-create-sofer" class="form-label">Șofer<span class="text-danger">*</span></label>
                            <select
                                name="sofer_id"
                                id="valabilitate-create-sofer"
                                class="form-select bg-white rounded-3 {{ $isCreateActive && $errors->has('sofer_id') ? 'is-invalid' : '' }}"
                                required
                            >
                                <option value="">Selectează șofer</option>
                                @foreach ($driverOptions as $driverId => $driverName)
                                    <option value="{{ $driverId }}" {{ $isCreateActive && (int) old('sofer_id') === (int) $driverId ? 'selected' : '' }}>
                                        {{ $driverName }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback {{ $isCreateActive && $errors->has('sofer_id') ? 'd-block' : '' }}" data-error-for="sofer_id">
                                {{ $isCreateActive ? $errors->first('sofer_id') : '' }}
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="valabilitate-create-data-inceput" class="form-label">Data început<span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    name="data_inceput"
                                    id="valabilitate-create-data-inceput"
                                    class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('data_inceput') ? 'is-invalid' : '' }}"
                                    value="{{ $isCreateActive ? old('data_inceput', '') : '' }}"
                                    required
                                >
                                <div class="invalid-feedback {{ $isCreateActive && $errors->has('data_inceput') ? 'd-block' : '' }}" data-error-for="data_inceput">
                                    {{ $isCreateActive ? $errors->first('data_inceput') : '' }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="valabilitate-create-data-sfarsit" class="form-label">Data sfârșit</label>
                                <input
                                    type="date"
                                    name="data_sfarsit"
                                    id="valabilitate-create-data-sfarsit"
                                    class="form-control bg-white rounded-3 {{ $isCreateActive && $errors->has('data_sfarsit') ? 'is-invalid' : '' }}"
                                    value="{{ $isCreateActive ? old('data_sfarsit', '') : '' }}"
                                >
                                <div class="invalid-feedback {{ $isCreateActive && $errors->has('data_sfarsit') ? 'd-block' : '' }}" data-error-for="data_sfarsit">
                                    {{ $isCreateActive ? $errors->first('data_sfarsit') : '' }}
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

@foreach ($valabilitati as $valabilitate)
    @php
        $isEditing = $currentFormType === 'edit' && $currentFormId === (int) $valabilitate->id;
        $editPrefix = 'valabilitate-edit-' . $valabilitate->id . '-';
    @endphp
    <div class="modal fade text-dark" id="valabilitateEditModal{{ $valabilitate->id }}" tabindex="-1" role="dialog" aria-labelledby="valabilitateEditModalLabel{{ $valabilitate->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="valabilitateEditModalLabel{{ $valabilitate->id }}">Modifică valabilitate</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Închide"></button>
                </div>
                <form action="{{ route('valabilitati.update', $valabilitate) }}" method="POST" class="valabilitati-modal-form" novalidate>
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="form_type" value="edit">
                    <input type="hidden" name="form_id" value="{{ $valabilitate->id }}">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="{{ $editPrefix }}denumire" class="form-label">Denumire<span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="denumire"
                                id="{{ $editPrefix }}denumire"
                                class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('denumire') ? 'is-invalid' : '' }}"
                                value="{{ $isEditing ? old('denumire', $valabilitate->denumire) : $valabilitate->denumire }}"
                                autocomplete="off"
                                required
                            >
                            <div class="invalid-feedback {{ $isEditing && $errors->has('denumire') ? 'd-block' : '' }}" data-error-for="denumire">
                                {{ $isEditing ? $errors->first('denumire') : '' }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="{{ $editPrefix }}numar-auto" class="form-label">Număr auto<span class="text-danger">*</span></label>
                            <input
                                type="text"
                                name="numar_auto"
                                id="{{ $editPrefix }}numar-auto"
                                class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('numar_auto') ? 'is-invalid' : '' }}"
                                value="{{ $isEditing ? old('numar_auto', $valabilitate->numar_auto) : $valabilitate->numar_auto }}"
                                autocomplete="off"
                                required
                            >
                            <div class="invalid-feedback {{ $isEditing && $errors->has('numar_auto') ? 'd-block' : '' }}" data-error-for="numar_auto">
                                {{ $isEditing ? $errors->first('numar_auto') : '' }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="{{ $editPrefix }}sofer" class="form-label">Șofer<span class="text-danger">*</span></label>
                            <select
                                name="sofer_id"
                                id="{{ $editPrefix }}sofer"
                                class="form-select bg-white rounded-3 {{ $isEditing && $errors->has('sofer_id') ? 'is-invalid' : '' }}"
                                required
                            >
                                <option value="">Selectează șofer</option>
                                @foreach ($driverOptions as $driverId => $driverName)
                                    <option value="{{ $driverId }}"
                                        @if (($isEditing ? (int) old('sofer_id', $valabilitate->sofer_id) : (int) $valabilitate->sofer_id) === (int) $driverId) selected @endif>
                                        {{ $driverName }}
                                    </option>
                                @endforeach
                                @if ($valabilitate->sofer && ! array_key_exists($valabilitate->sofer_id, $driverOptions))
                                    <option value="{{ $valabilitate->sofer_id }}" selected>
                                        {{ $valabilitate->sofer->name }}
                                    </option>
                                @endif
                            </select>
                            <div class="invalid-feedback {{ $isEditing && $errors->has('sofer_id') ? 'd-block' : '' }}" data-error-for="sofer_id">
                                {{ $isEditing ? $errors->first('sofer_id') : '' }}
                            </div>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="{{ $editPrefix }}data-inceput" class="form-label">Data început<span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    name="data_inceput"
                                    id="{{ $editPrefix }}data-inceput"
                                    class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('data_inceput') ? 'is-invalid' : '' }}"
                                    value="{{ $isEditing ? old('data_inceput', optional($valabilitate->data_inceput)->format('Y-m-d')) : optional($valabilitate->data_inceput)->format('Y-m-d') }}"
                                    required
                                >
                                <div class="invalid-feedback {{ $isEditing && $errors->has('data_inceput') ? 'd-block' : '' }}" data-error-for="data_inceput">
                                    {{ $isEditing ? $errors->first('data_inceput') : '' }}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="{{ $editPrefix }}data-sfarsit" class="form-label">Data sfârșit</label>
                                <input
                                    type="date"
                                    name="data_sfarsit"
                                    id="{{ $editPrefix }}data-sfarsit"
                                    class="form-control bg-white rounded-3 {{ $isEditing && $errors->has('data_sfarsit') ? 'is-invalid' : '' }}"
                                    value="{{ $isEditing ? old('data_sfarsit', optional($valabilitate->data_sfarsit)->format('Y-m-d')) : optional($valabilitate->data_sfarsit)->format('Y-m-d') }}"
                                >
                                <div class="invalid-feedback {{ $isEditing && $errors->has('data_sfarsit') ? 'd-block' : '' }}" data-error-for="data_sfarsit">
                                    {{ $isEditing ? $errors->first('data_sfarsit') : '' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                        <button type="submit" class="btn btn-primary text-white border border-dark rounded-3">
                            <i class="fa-solid fa-floppy-disk me-1"></i>Salvează modificările
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade text-dark" id="valabilitateDeleteModal{{ $valabilitate->id }}" tabindex="-1" role="dialog" aria-labelledby="valabilitateDeleteModalLabel{{ $valabilitate->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="valabilitateDeleteModalLabel{{ $valabilitate->id }}">Șterge valabilitate</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Închide"></button>
                </div>
                <div class="modal-body text-start">
                    Sigur ștergi valabilitatea <strong>{{ $valabilitate->denumire }}</strong> pentru <strong>{{ $valabilitate->numar_auto }}</strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                    <form action="{{ route('valabilitati.destroy', $valabilitate) }}" method="POST" class="valabilitati-modal-form" novalidate>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger text-white border border-dark rounded-3">
                            <i class="fa-solid fa-trash-can me-1"></i>Șterge
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach
