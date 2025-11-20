@extends('layouts.app')

@section('content')
    {{-- Page-specific styling – stays only in this file --}}
    <style>
        /* Excel-style summary + main table */

        .curse-summary-table,
        .curse-data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .curse-summary-table th,
        .curse-summary-table td,
        .curse-data-table th,
        .curse-data-table td {
            border: 1px solid #000000ff;
            padding: 0.4rem 0.6rem;
            line-height: 1.1;
            vertical-align: middle;
        }

        .curse-summary-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .curse-summary-title {
            background-color: #fff3cd;
            font-weight: 600;
            white-space: nowrap;
        }

        .curse-summary-driver {
            background-color: #fde2e4;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
        }

        .curse-summary-label {
            white-space: nowrap;
        }

        .curse-data-header-top th,
        .curse-data-header-bottom th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        .curse-data-header-bottom th {
            border-top: none;
        }

        .curse-data-table tbody tr:not(.curse-group-heading):not(.curse-group-row):nth-child(even) {
            background-color: #fcfcfc;
        }

        .curse-data-table tbody tr:not(.curse-group-heading):not(.curse-group-row):hover {
            background-color: #f1f3f5;
        }

        .curse-group-heading th {
            font-size: 0.85rem;
            text-transform: uppercase;
            background-color: rgba(0, 0, 0, 0.05);
            color: inherit;
        }

        .curse-group-heading--emphasis th,
        .curse-group-heading--emphasis td {
            border-top: 2px solid rgba(0, 0, 0, 0.25);
            border-bottom: 2px solid rgba(0, 0, 0, 0.25);
            box-shadow: inset 0 0 0 999px rgba(255, 255, 255, 0.08);
        }

        .curse-group-heading__meta {
            font-size: 0.8rem;
        }

        .curse-group-row {
            transition: background-color 0.2s ease-in-out;
        }

        .curse-nowrap {
            white-space: nowrap;
        }
    </style>

    @php
        $grupuriRoute = route('valabilitati.grupuri.index', $valabilitate);
        $curseRoute = route('valabilitati.curse.index', $valabilitate);
        $isGroupsContext = request()->routeIs('valabilitati.grupuri.*');
        $hasGrupuri = $valabilitate->cursaGrupuri->count() > 0;
        $isFlashDivision = optional($valabilitate->divizie)->id === 1
            && strcasecmp((string) optional($valabilitate->divizie)->nume, 'FLASH') === 0;
        $tableColumnCount = $isFlashDivision ? 23 : 12;
    @endphp
    <div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center text-center text-lg-start" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                <span class="badge culoare1 fs-5">
                    <span class="d-inline-flex flex-column align-items-start gap-1 lh-1">
                        <span>
                            <i class="fa-solid fa-route me-1"></i>Curse
                            /
                            {{ $valabilitate->divizie->nume ?? 'Fără divizie' }}
                        </span>
                    </span>
                </span>
            </div>
            <div class="col-12 col-lg-4 my-2 my-lg-0">
                <div class="d-flex justify-content-center gap-2">
                    <a
                        href="{{ $curseRoute }}"
                        class="btn btn-sm {{ $isGroupsContext ? 'btn-outline-primary' : 'btn-primary text-white' }} border border-dark rounded-3"
                    >
                        <i class="fa-solid fa-truck-fast me-1"></i>Curse
                    </a>
                    <a
                        href="{{ $grupuriRoute }}"
                        class="btn btn-sm {{ $isGroupsContext ? 'btn-primary text-white' : 'btn-outline-primary' }} border border-dark rounded-3"
                    >
                        <i class="fa-solid fa-layer-group me-1"></i>Grupuri
                    </a>
                </div>
            </div>
            <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="d-flex align-items-stretch align-items-lg-end gap-2 flex-wrap justify-content-center justify-content-lg-end">
                    {{-- <button
                        type="button"
                        class="btn btn-sm btn-outline-primary border border-dark rounded-3"
                        data-bs-toggle="modal"
                        data-bs-target="#cursaGroupCreateModal"
                    >
                        <i class="fa-solid fa-layer-group me-1"></i>Crează grup
                    </button> --}}
                    <button
                        type="button"
                        class="btn btn-sm btn-success text-white border border-dark rounded-3"
                        data-bs-toggle="modal"
                        data-bs-target="#cursaCreateModal"
                    >
                        <i class="fas fa-plus-square text-white me-1"></i>Adaugă cursă
                    </button>
                    <button
                        type="button"
                        id="curse-bulk-trigger"
                        class="btn btn-sm btn-outline-primary border border-dark rounded-3"
                        data-bs-toggle="modal"
                        data-bs-target="#curseBulkAssignModal"
                        disabled
                    >
                        <i class="fa-solid fa-layer-group me-1"></i>Adaugă cursele selectate în grup
                    </button>
                    <a
                        href="{{ $backUrl }}"
                        class="btn btn-sm btn-outline-secondary border border-dark rounded-3"
                    >
                        <i class="fa-solid fa-list me-1"></i>Înapoi la valabilități
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body px-0 py-3">
            @include('errors')

            {{-- Excel-style summary band --}}
            <div id="curse-summary" class="px-3 mb-3">
                @include('valabilitati.curse.partials.summary', [
                    'valabilitate' => $valabilitate,
                    'summary' => $summary,
                    'showGroupSummary' => false,
                    'isFlashDivision' => $isFlashDivision,
                ])
            </div>

            <div id="curse-feedback" class="px-3"></div>

            <div class="px-3">
                <div class="table-responsive">
                    <table class="table table-sm curse-data-table align-middle">
                        <thead>
                            <tr class="curse-data-header-top">
                                <th rowspan="2" class="text-center curse-nowrap align-middle">
                                    <div class="form-check mb-0">
                                        <input
                                            type="checkbox"
                                            class="form-check-input"
                                            id="curse-table-select-all"
                                            @disabled($curse->count() === 0)
                                        >
                                        <label class="visually-hidden" for="curse-table-select-all">
                                            Selectează toate cursele afișate
                                        </label>
                                    </div>
                                </th>
                                <th rowspan="2" class="text-center curse-nowrap">#</th>
                                <th rowspan="2" class="text-center curse-nowrap">Nr. cursă</th>
                                <th rowspan="2">Cursa</th>
                                <th rowspan="2" class="text-center curse-nowrap">Dată<br>transport</th>
                                @if ($isFlashDivision)
                                    <th colspan="2" class="text-center curse-nowrap">KM Maps</th>
                                    <th rowspan="2" class="text-center curse-nowrap">KM cu<br>taxă</th>
                                    <th colspan="2" class="text-center curse-nowrap">KM Flash</th>
                                    <th colspan="2" class="text-center curse-nowrap">Diferența KM<br>(Maps – Flash)</th>
                                    <th colspan="4" class="text-center curse-nowrap">Sumă calculată</th>
                                    <th rowspan="2" class="text-end curse-nowrap">Alte taxe</th>
                                    <th rowspan="2" class="text-end curse-nowrap">Fuel tax</th>
                                    <th rowspan="2" class="text-center curse-nowrap">Sumă<br>încasată</th>
                                    <th rowspan="2" class="text-center curse-nowrap">Diferența preț<br>(încasat –<br> calculat)</th>
                                    <th colspan="2" class="text-center curse-nowrap">Daily contribution</th>
                                @else
                                    <th rowspan="2" class="text-end curse-nowrap">KM Maps</th>
                                    <th colspan="2" class="text-center curse-nowrap">
                                        KM Bord (plecare / sosire)
                                    </th>
                                    <th colspan="2" class="text-center curse-nowrap">KM Bord 2</th>
                                    <th rowspan="2" class="text-end curse-nowrap">
                                        Diferența KM<br>(Bord – Maps)
                                    </th>
                                @endif
                                <th rowspan="2" class="text-end curse-nowrap">Acțiuni</th>
                            </tr>
                            <tr class="curse-data-header-bottom">
                                @if ($isFlashDivision)
                                    <th class="text-end curse-nowrap">Km gol</th>
                                    <th class="text-end curse-nowrap">Km plin</th>
                                    <th class="text-end curse-nowrap">Km gol</th>
                                    <th class="text-end curse-nowrap">Km plin</th>
                                    <th class="text-end curse-nowrap">Km gol</th>
                                    <th class="text-end curse-nowrap">Km plin</th>
                                    <th class="text-end curse-nowrap">Km gol</th>
                                    <th class="text-end curse-nowrap">Km plin</th>
                                    <th class="text-end curse-nowrap">Km cu taxă</th>
                                    <th class="text-end curse-nowrap">Total km</th>
                                    <th class="text-end curse-nowrap">Calculat</th>
                                    <th class="text-end curse-nowrap">Încasat</th>
                                @else
                                    <th class="text-end curse-nowrap">Plecare</th>
                                    <th class="text-end curse-nowrap">Sosire</th>
                                    <th class="text-end curse-nowrap">Km gol</th>
                                    <th class="text-end curse-nowrap">Km plin</th>
                                @endif
                            </tr>
                        </thead>

                        <tbody id="curse-table-body">
                            @if ($curse->count())
                                @include('valabilitati.curse.partials.rows', ['curse' => $curse, 'valabilitate' => $valabilitate])
                            @else
                                <tr>
                                    <td colspan="{{ $tableColumnCount }}" class="text-center py-4">
                                        Nu există curse care să respecte criteriile selectate.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($curse instanceof \Illuminate\Contracts\Pagination\Paginator || $curse instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
                <div id="curse-infinite-scroll" class="mt-3 px-3">
                    <div
                        id="curse-load-more-trigger"
                        class="d-flex justify-content-center py-3"
                        data-next-url="{{ $nextPageUrl }}"
                    >
                        @if ($curse->hasMorePages())
                            <button type="button" class="btn btn-outline-primary" id="curse-load-more">
                                <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                <span class="load-more-label">Încarcă mai multe</span>
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div
        id="curse-modals"
        data-active-modal="{{ session('curse.modal') }}"
    >
        @include('valabilitati.curse.partials.modals', $modalViewData)
    </div>

    @push('page-scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const bootstrap = window.bootstrap;
                const bootstrapModal = bootstrap && bootstrap.Modal ? bootstrap.Modal : null;
                const modalsContainer = document.getElementById('curse-modals');
                const tableBody = document.getElementById('curse-table-body');
                const feedbackContainer = document.getElementById('curse-feedback');
                const summaryContainer = document.getElementById('curse-summary');
                const bulkTriggerButton = document.getElementById('curse-bulk-trigger');
                const bulkSelectAllCheckbox = document.getElementById('curse-table-select-all');
                let bulkAssignModalElement = document.getElementById('curseBulkAssignModal');
                let bulkGroupSelect = bulkAssignModalElement
                    ? bulkAssignModalElement.querySelector('#curse-bulk-group')
                    : null;
                let bulkSubmitButton = bulkAssignModalElement
                    ? bulkAssignModalElement.querySelector('#curse-bulk-submit')
                    : null;
                let bulkSelectedCount = bulkAssignModalElement
                    ? bulkAssignModalElement.querySelector('#curse-bulk-selected-count')
                    : null;
                let bulkFeedback = bulkAssignModalElement
                    ? bulkAssignModalElement.querySelector('#curse-bulk-feedback')
                    : null;
                let bulkEmptyWarning = bulkAssignModalElement
                    ? bulkAssignModalElement.querySelector('[data-bulk-empty-warning]')
                    : null;
                const bulkAssignState = {
                    selected: new Set(),
                    loading: false,
                    hasGroups: bulkAssignModalElement ? bulkAssignModalElement.dataset.hasGroups === 'true' : false,
                };
                const resolveBulkModalInstance = () => {
                    if (!bulkAssignModalElement || !bootstrapModal) {
                        return null;
                    }

                    return bootstrapModal.getOrCreateInstance(bulkAssignModalElement);
                };
                let bulkAssignModalInstance = resolveBulkModalInstance();
                const COUNTRY_DATALIST_ID = 'valabilitati-curse-tari';
                const countryState = {
                    mapByName: new Map(),
                    mapById: new Map(),
                };

                const supportsAjax = () => typeof window.fetch === 'function' && typeof window.FormData === 'function';

                const resolveCsrfToken = (() => {
                    let cachedToken = null;
                    return () => {
                        if (cachedToken !== null) {
                            return cachedToken;
                        }

                        const meta = document.querySelector('meta[name="csrf-token"]');
                        cachedToken = meta ? meta.getAttribute('content') : '';
                        return cachedToken;
                    };
                })();

                const loadState = {
                    button: null,
                    trigger: null,
                    spinner: null,
                    label: null,
                    observer: null,
                    nextUrl: null,
                    loading: false,
                };

                const updateBulkAssignUi = () => {
                    if (bulkSelectedCount) {
                        bulkSelectedCount.textContent = String(bulkAssignState.selected.size);
                    }

                    if (bulkSubmitButton) {
                        const hasGroupSelection = bulkGroupSelect && bulkGroupSelect.value !== '';
                        const canSubmit =
                            bulkAssignState.hasGroups &&
                            hasGroupSelection &&
                            bulkAssignState.selected.size > 0 &&
                            !bulkAssignState.loading;
                        bulkSubmitButton.disabled = !canSubmit;
                    }

                    if (bulkTriggerButton) {
                        const canOpenModal =
                            bulkAssignState.hasGroups &&
                            bulkAssignState.selected.size > 0 &&
                            !bulkAssignState.loading;
                        bulkTriggerButton.disabled = !canOpenModal;
                    }

                    if (bulkEmptyWarning) {
                        bulkEmptyWarning.classList.toggle('d-none', bulkAssignState.hasGroups);
                    }

                    if (bulkSelectAllCheckbox && tableBody) {
                        const totalCheckboxes = tableBody.querySelectorAll('.curse-row-checkbox').length;
                        bulkSelectAllCheckbox.disabled = totalCheckboxes === 0;
                    }
                };

                const clearBulkAssignError = () => {
                    if (!bulkFeedback) {
                        return;
                    }

                    bulkFeedback.textContent = '';
                    bulkFeedback.classList.add('d-none');
                };

                const showBulkAssignError = (message) => {
                    if (!bulkFeedback) {
                        return;
                    }

                    bulkFeedback.textContent = message;
                    bulkFeedback.classList.remove('d-none');
                };

                const setBulkAssignLoading = (isLoading) => {
                    bulkAssignState.loading = isLoading;

                    if (bulkSubmitButton) {
                        const defaultLabel = bulkSubmitButton.querySelector('[data-bulk-default-label]');
                        const loadingLabel = bulkSubmitButton.querySelector('[data-bulk-loading-label]');
                        if (defaultLabel && loadingLabel) {
                            defaultLabel.classList.toggle('d-none', isLoading);
                            loadingLabel.classList.toggle('d-none', !isLoading);
                        }
                    }

                    updateBulkAssignUi();
                };

                const resetBulkSelection = (clearSet = false) => {
                    if (clearSet) {
                        bulkAssignState.selected.clear();
                    }

                    if (tableBody) {
                        const checkboxes = tableBody.querySelectorAll('.curse-row-checkbox');
                        checkboxes.forEach(checkbox => {
                            checkbox.checked = false;
                        });
                    }

                    if (bulkSelectAllCheckbox) {
                        bulkSelectAllCheckbox.checked = false;
                        bulkSelectAllCheckbox.indeterminate = false;
                    }

                    updateBulkAssignUi();
                };

                const refreshBulkSelectAllState = () => {
                    if (!bulkSelectAllCheckbox || !tableBody) {
                        return;
                    }

                    const checkboxes = Array.from(tableBody.querySelectorAll('.curse-row-checkbox'));
                    const total = checkboxes.length;
                    const checked = checkboxes.filter(cb => cb.checked).length;

                    bulkSelectAllCheckbox.indeterminate = checked > 0 && checked < total;
                    bulkSelectAllCheckbox.checked = total > 0 && checked === total;
                    bulkSelectAllCheckbox.disabled = total === 0;
                };

                const refreshBulkModalReferences = () => {
                    bulkAssignModalElement = document.getElementById('curseBulkAssignModal');
                    bulkGroupSelect = bulkAssignModalElement
                        ? bulkAssignModalElement.querySelector('#curse-bulk-group')
                        : null;
                    bulkSubmitButton = bulkAssignModalElement
                        ? bulkAssignModalElement.querySelector('#curse-bulk-submit')
                        : null;
                    bulkSelectedCount = bulkAssignModalElement
                        ? bulkAssignModalElement.querySelector('#curse-bulk-selected-count')
                        : null;
                    bulkFeedback = bulkAssignModalElement
                        ? bulkAssignModalElement.querySelector('#curse-bulk-feedback')
                        : null;
                    bulkEmptyWarning = bulkAssignModalElement
                        ? bulkAssignModalElement.querySelector('[data-bulk-empty-warning]')
                        : null;
                    bulkAssignState.hasGroups = bulkAssignModalElement
                        ? bulkAssignModalElement.dataset.hasGroups === 'true'
                        : false;
                    bulkAssignModalInstance = resolveBulkModalInstance();
                };

                const bindBulkCheckboxes = () => {
                    if (!tableBody) {
                        return;
                    }

                    const checkboxes = tableBody.querySelectorAll('.curse-row-checkbox');
                    checkboxes.forEach(checkbox => {
                        if (checkbox.dataset.bulkBound === 'true') {
                            return;
                        }

                        checkbox.addEventListener('change', () => {
                            const id = checkbox.dataset.cursaId;
                            if (!id) {
                                return;
                            }

                            if (checkbox.checked) {
                                bulkAssignState.selected.add(id);
                            } else {
                                bulkAssignState.selected.delete(id);
                            }

                            refreshBulkSelectAllState();
                            updateBulkAssignUi();
                        });

                        checkbox.dataset.bulkBound = 'true';
                    });

                    refreshBulkSelectAllState();
                };

                const handleBulkSelectAllChange = (event) => {
                    if (!tableBody) {
                        return;
                    }

                    const shouldCheck = event.target.checked;

                    if (bulkSelectAllCheckbox) {
                        bulkSelectAllCheckbox.indeterminate = false;
                    }

                    const checkboxes = tableBody.querySelectorAll('.curse-row-checkbox');
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = shouldCheck;
                        const id = checkbox.dataset.cursaId;

                        if (!id) {
                            return;
                        }

                        if (shouldCheck) {
                            bulkAssignState.selected.add(id);
                        } else {
                            bulkAssignState.selected.delete(id);
                        }
                    });

                    updateBulkAssignUi();
                };

                const syncBulkGroupOptions = () => {
                    if (!bulkAssignModalElement || !bulkGroupSelect) {
                        bulkAssignState.hasGroups = false;
                        updateBulkAssignUi();
                        return;
                    }

                    const sourceSelect = modalsContainer
                        ? modalsContainer.querySelector('#cursa-create-grup')
                        : null;

                    if (!sourceSelect) {
                        const existingOptions = Array.from(bulkGroupSelect.options).filter(
                            option => option.value && option.value !== ''
                        );
                        bulkAssignState.hasGroups = existingOptions.length > 0;

                        bulkAssignModalElement.dataset.hasGroups = bulkAssignState.hasGroups ? 'true' : 'false';
                        updateBulkAssignUi();
                        return;
                    }

                    const previousValue = bulkGroupSelect.value;
                    const newOptions = Array.from(sourceSelect.options)
                        .filter(option => option.value && option.value !== '')
                        .map(option => ({
                            value: option.value,
                            label: (option.textContent || '').trim(),
                        }));

                    bulkGroupSelect.innerHTML = '';

                    const placeholder = document.createElement('option');
                    placeholder.value = '';
                    placeholder.textContent = 'Selectează grupul';
                    bulkGroupSelect.appendChild(placeholder);

                    newOptions.forEach(option => {
                        const opt = document.createElement('option');
                        opt.value = option.value;
                        opt.textContent = option.label || '—';
                        if (previousValue && previousValue === option.value) {
                            opt.selected = true;
                        }
                        bulkGroupSelect.appendChild(opt);
                    });

                    bulkAssignState.hasGroups = newOptions.length > 0;

                    if (!bulkAssignState.hasGroups) {
                        bulkGroupSelect.value = '';
                    }

                    bulkAssignModalElement.dataset.hasGroups = bulkAssignState.hasGroups ? 'true' : 'false';

                    updateBulkAssignUi();
                };

                const extractFirstValidationMessage = (errors) => {
                    if (!errors || typeof errors !== 'object') {
                        return null;
                    }

                    for (const key of Object.keys(errors)) {
                        const messages = errors[key];
                        if (Array.isArray(messages) && messages.length > 0) {
                            return messages[0];
                        }
                    }

                    return null;
                };

                const handleBulkAssignSubmit = (event) => {
                    event.preventDefault();

                    if (!bulkAssignModalElement || !bulkAssignState.hasGroups || bulkAssignState.loading) {
                        return;
                    }

                    clearBulkAssignError();

                    const action = bulkAssignModalElement.dataset.action || '';
                    const groupId = bulkGroupSelect ? bulkGroupSelect.value : '';
                    const curseIds = Array.from(bulkAssignState.selected);

                    if (!action) {
                        return;
                    }

                    if (curseIds.length === 0) {
                        showBulkAssignError('Selectează cel puțin o cursă.');
                        updateBulkAssignUi();
                        return;
                    }

                    if (!groupId) {
                        showBulkAssignError('Alege grupul în care dorești să muți cursele.');
                        updateBulkAssignUi();
                        return;
                    }

                    const formData = new FormData();
                    curseIds.forEach(id => formData.append('curse_ids[]', id));
                    formData.append('cursa_grup_id', groupId);

                    setBulkAssignLoading(true);

                    const fetchOptions = {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                        body: formData,
                    };

                    const csrfToken = resolveCsrfToken();
                    if (csrfToken) {
                        fetchOptions.headers['X-CSRF-TOKEN'] = csrfToken;
                    }

                    fetch(action, fetchOptions)
                        .then(response => {
                            if (response.status === 422) {
                                return response.json().then(data => {
                                    const message = extractFirstValidationMessage(data.errors || {});
                                    if (message) {
                                        showBulkAssignError(message);
                                    }
                                    throw new Error('validation');
                                });
                            }

                            if (!response.ok) {
                                return response.text().then(body => {
                                    const error = new Error('request_failed');
                                    error.status = response.status;
                                    error.statusText = response.statusText;
                                    error.body = body;
                                    error.url = response.url;
                                    throw error;
                                });
                            }

                            return response.json();
                        })
                        .then(data => {
                            if (bulkAssignModalInstance) {
                                bulkAssignModalInstance.hide();
                            }

                            processMutationResponse(data);
                            clearBulkAssignError();

                            if (data.message) {
                                const alertMap = {
                                    error: 'danger',
                                    warning: 'warning',
                                    success: 'success',
                                };
                                const alertType = alertMap[data.message_type] || 'success';
                                showFeedback(data.message, alertType);
                            }
                        })
                        .catch(error => {
                            if (error && error.message === 'validation') {
                                return;
                            }

                            console.error('Valabilități curse bulk assign error', error);

                            const detailText = buildErrorDetails(error);
                            const baseMessage = 'A apărut o eroare neașteptată. Reîncercați.';
                            showFeedback(detailText ? `${baseMessage}${detailText}` : baseMessage, 'danger');
                        })
                        .finally(() => {
                            setBulkAssignLoading(false);
                            updateBulkAssignUi();
                        });
                };

                const bindBulkModalEvents = () => {
                    if (bulkGroupSelect && bulkGroupSelect.dataset.bulkBound !== 'true') {
                        bulkGroupSelect.addEventListener('change', () => {
                            clearBulkAssignError();
                            updateBulkAssignUi();
                        });
                        bulkGroupSelect.dataset.bulkBound = 'true';
                    }

                    if (bulkSubmitButton && bulkSubmitButton.dataset.bulkBound !== 'true') {
                        bulkSubmitButton.addEventListener('click', handleBulkAssignSubmit);
                        bulkSubmitButton.dataset.bulkBound = 'true';
                    }

                    if (bulkAssignModalElement && bulkAssignModalElement.dataset.bulkBound !== 'true') {
                        bulkAssignModalElement.addEventListener('hidden.bs.modal', () => {
                            if (!bulkAssignState.loading) {
                                clearBulkAssignError();
                            }
                        });
                        bulkAssignModalElement.dataset.bulkBound = 'true';
                    }
                };

                const initBulkAssignControls = () => {
                    refreshBulkModalReferences();
                    bindBulkModalEvents();

                    if (!bulkAssignModalElement) {
                        updateBulkAssignUi();
                        return;
                    }

                    if (bulkSelectAllCheckbox && bulkSelectAllCheckbox.dataset.bulkBound !== 'true') {
                        bulkSelectAllCheckbox.addEventListener('change', handleBulkSelectAllChange);
                        bulkSelectAllCheckbox.dataset.bulkBound = 'true';
                    }

                    bindBulkCheckboxes();
                    syncBulkGroupOptions();
                    updateBulkAssignUi();
                };

                const appendHtml = (container, html) => {
                    if (!container || !html) {
                        return;
                    }

                    const template = document.createElement('template');
                    template.innerHTML = html.trim();
                    container.appendChild(template.content);
                };

                const refreshCountryMaps = () => {
                    countryState.mapByName.clear();
                    countryState.mapById.clear();

                    if (!modalsContainer) {
                        return;
                    }

                    const datalist = modalsContainer.querySelector(`#${COUNTRY_DATALIST_ID}`);

                    if (!datalist) {
                        return;
                    }

                    const options = datalist.querySelectorAll('option');

                    options.forEach(option => {
                        const name = (option.value || '').trim();
                        const id = (option.dataset.id || '').trim();

                        if (name) {
                            countryState.mapByName.set(name.toLowerCase(), id);
                        }

                        if (id) {
                            countryState.mapById.set(id, name);
                        }
                    });
                };

                const syncHiddenToText = (textInput, hiddenInput) => {
                    const hiddenValue = (hiddenInput.value || '').trim();

                    if (!hiddenValue) {
                        return;
                    }

                    const resolved = countryState.mapById.get(hiddenValue);

                    if (resolved && (textInput.value || '').trim() === '') {
                        textInput.value = resolved;
                    }
                };

                const syncTextToHidden = (textInput, hiddenInput) => {
                    const textValue = (textInput.value || '').trim().toLowerCase();
                    const matchId = countryState.mapByName.get(textValue);

                    hiddenInput.value = matchId || '';
                };

                const bindCountryField = (field) => {
                    if (!field) {
                        return;
                    }

                    const textInput = field.querySelector('[data-country-input]');
                    const hiddenInput = field.querySelector('[data-country-hidden]');

                    if (!textInput || !hiddenInput) {
                        return;
                    }

                    const hasOptions = countryState.mapByName.size > 0;

                    if (textInput.dataset.countryBound === 'true') {
                        syncHiddenToText(textInput, hiddenInput);
                        if (hasOptions) {
                            syncTextToHidden(textInput, hiddenInput);
                        }
                        return;
                    }

                    const clearValidationState = () => {
                        textInput.classList.remove('is-invalid');
                        hiddenInput.classList.remove('is-invalid');
                    };

                    syncHiddenToText(textInput, hiddenInput);
                    if (hasOptions) {
                        syncTextToHidden(textInput, hiddenInput);
                    }

                    textInput.addEventListener('input', () => {
                        if (hasOptions) {
                            syncTextToHidden(textInput, hiddenInput);
                        }
                        clearValidationState();
                    });

                    textInput.addEventListener('change', () => {
                        if (hasOptions) {
                            syncTextToHidden(textInput, hiddenInput);
                        }
                    });

                    textInput.addEventListener('blur', () => {
                        if ((textInput.value || '').trim() === '') {
                            hiddenInput.value = '';
                        }
                    });

                    textInput.dataset.countryBound = 'true';
                };

                const initCountryInputs = () => {
                    refreshCountryMaps();

                    if (!modalsContainer) {
                        return;
                    }

                    const fields = modalsContainer.querySelectorAll('[data-country-field]');
                    fields.forEach(field => bindCountryField(field));
                };

                const showModalElement = (element, fallbackMessage = null) => {
                    if (!element) {
                        if (fallbackMessage && typeof window !== 'undefined' && typeof window.alert === 'function') {
                            window.alert(fallbackMessage);
                        }
                        return;
                    }

                    if (bootstrapModal) {
                        const modalInstance =
                            typeof bootstrapModal.getOrCreateInstance === 'function'
                                ? bootstrapModal.getOrCreateInstance(element)
                                : new bootstrapModal(element);
                        modalInstance.show();
                        return;
                    }

                    const $ = window.jQuery || window.$;

                    if (typeof $ === 'function' && typeof $(element).modal === 'function') {
                        $(element).modal('show');
                        return;
                    }

                    if (fallbackMessage && typeof window !== 'undefined' && typeof window.alert === 'function') {
                        window.alert(fallbackMessage);
                    }
                };

                const closeModal = (modalElement) => {
                    if (!modalElement) {
                        return;
                    }

                    if (bootstrapModal) {
                        const existingInstance =
                            typeof bootstrapModal.getInstance === 'function'
                                ? bootstrapModal.getInstance(modalElement)
                                : null;

                        if (existingInstance) {
                            existingInstance.hide();
                            return;
                        }

                        const fallbackInstance =
                            typeof bootstrapModal.getOrCreateInstance === 'function'
                                ? bootstrapModal.getOrCreateInstance(modalElement)
                                : new bootstrapModal(modalElement);
                        fallbackInstance.hide();
                        return;
                    }

                    const $ = window.jQuery || window.$;

                    if (typeof $ === 'function' && typeof $(modalElement).modal === 'function') {
                        $(modalElement).modal('hide');
                        return;
                    }

                    modalElement.classList.remove('show');
                    modalElement.setAttribute('aria-hidden', 'true');
                };

                const showFeedback = (message, type = 'success') => {
                    if (!feedbackContainer) {
                        return;
                    }

                    feedbackContainer.innerHTML = '';

                    if (!message) {
                        return;
                    }

                    const alert = document.createElement('div');
                    alert.className = `alert alert-${type} alert-dismissible fade show mt-3`;
                    alert.setAttribute('role', 'alert');

                    const messageSpan = document.createElement('span');
                    messageSpan.textContent = message;
                    alert.appendChild(messageSpan);

                    const closeButton = document.createElement('button');
                    closeButton.type = 'button';
                    closeButton.className = 'btn-close';
                    closeButton.setAttribute('data-bs-dismiss', 'alert');
                    closeButton.setAttribute('aria-label', 'Închide');
                    alert.appendChild(closeButton);

                    feedbackContainer.appendChild(alert);
                };

                const buildErrorDetails = (error) => {
                    if (!error || typeof error !== 'object') {
                        return '';
                    }

                    const details = [];
                    const status = typeof error.status === 'number' ? error.status : error.statusCode;

                    if (typeof status === 'number') {
                        details.push(`cod ${status}`);
                    }

                    if (error.statusText) {
                        details.push(error.statusText);
                    }

                    if (error.message && !['request_failed', 'validation'].includes(error.message)) {
                        details.push(error.message);
                    }

                    if (error.body) {
                        const snippet = String(error.body).trim().replace(/\s+/g, ' ');
                        if (snippet) {
                            details.push(snippet.length > 200 ? `${snippet.slice(0, 200)}…` : snippet);
                        }
                    }

                    return details.length ? ` Detalii: ${details.join(' | ')}` : '';
                };

                const clearFormErrors = (form) => {
                    form.querySelectorAll('.is-invalid').forEach(element => {
                        element.classList.remove('is-invalid');
                    });

                    form.querySelectorAll('[data-error-for]').forEach(element => {
                        element.textContent = '';
                        element.classList.remove('d-block');
                    });
                };

                const displayValidationErrors = (form, errors) => {
                    if (!errors) {
                        return;
                    }

                    Object.entries(errors).forEach(([field, messages]) => {
                        const inputs = form.querySelectorAll(`[name="${field}"]`);
                        const proxyInputs = form.querySelectorAll(`[data-error-proxy="${field}"]`);

                        inputs.forEach(input => {
                            input.classList.add('is-invalid');

                            if (input.dataset.countryHidden === 'true') {
                                const fieldWrapper = input.closest('[data-country-field]');
                                const textInput = fieldWrapper ? fieldWrapper.querySelector('[data-country-input]') : null;

                                if (textInput) {
                                    textInput.classList.add('is-invalid');
                                }
                            }
                        });

                        proxyInputs.forEach(proxy => {
                            proxy.classList.add('is-invalid');
                        });

                        const feedback = form.querySelector(`[data-error-for="${field}"]`);
                        if (feedback) {
                            const messageText = Array.isArray(messages) ? messages.join(' ') : messages;
                            feedback.textContent = messageText;
                            feedback.classList.add('d-block');
                        }
                    });
                };

                function setLoadingState(isLoading) {
                    loadState.loading = isLoading;

                    if (!loadState.button) {
                        return;
                    }

                    loadState.button.disabled = isLoading;

                    if (loadState.spinner) {
                        loadState.spinner.classList.toggle('d-none', !isLoading);
                    }

                    if (loadState.label) {
                        loadState.label.textContent = isLoading ? 'Se încarcă...' : 'Încarcă mai multe';
                    }
                }

                function disconnectObserver() {
                    if (loadState.observer) {
                        loadState.observer.disconnect();
                        loadState.observer = null;
                    }
                }

                function processMutationResponse(data) {
                    resetBulkSelection(true);

                    if (data.table_html && tableBody) {
                        tableBody.innerHTML = data.table_html;
                    }

                    if (summaryContainer && data.summary_html) {
                        summaryContainer.innerHTML = data.summary_html;
                    }

                    if (modalsContainer && data.modals_html) {
                        modalsContainer.innerHTML = data.modals_html;
                        modalsContainer.dataset.activeModal = '';
                        refreshBulkModalReferences();
                        bindBulkModalEvents();
                        syncBulkGroupOptions();
                    }

                    const loadMoreTrigger = document.getElementById('curse-load-more-trigger');
                    if (loadMoreTrigger) {
                        loadMoreTrigger.dataset.nextUrl = data.next_url || '';
                    }

                    initCountryInputs();
                    initLoadMore();
                    bindBulkCheckboxes();
                    updateBulkAssignUi();
                    clearBulkAssignError();

                    if (supportsAjax()) {
                        attachFormHandlers();
                    }
                }

                function handleFormSubmit(event) {
                    if (!supportsAjax()) {
                        return;
                    }

                    event.preventDefault();

                    const form = event.target;
                    const submitButton = form.querySelector('[type="submit"]');
                    const originalHtml = submitButton ? submitButton.innerHTML : null;

                    clearFormErrors(form);

                    if (submitButton) {
                        submitButton.dataset.originalHtml = originalHtml || '';
                        submitButton.disabled = true;
                        submitButton.innerHTML = `
                        <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Se procesează...
                    `;
                    }

                    const methodAttribute = (form.getAttribute('method') || 'POST').toUpperCase();
                    const spoofedMethodInput = form.querySelector('input[name="_method"]');
                    const spoofedMethod =
                        methodAttribute === 'POST' && spoofedMethodInput && spoofedMethodInput.value
                            ? spoofedMethodInput.value.toUpperCase()
                            : null;
                    const fetchMethod = methodAttribute === 'GET' ? 'GET' : 'POST';

                    const fetchOptions = {
                        method: fetchMethod,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    };

                    const csrfToken = resolveCsrfToken();
                    if (csrfToken) {
                        fetchOptions.headers['X-CSRF-TOKEN'] = csrfToken;
                    }

                    if (fetchMethod !== 'GET') {
                        fetchOptions.body = new FormData(form);

                        if (spoofedMethod) {
                            fetchOptions.headers['X-HTTP-Method-Override'] = spoofedMethod;
                        }
                    }

                    fetch(form.action, fetchOptions)
                        .then(response => {
                            if (response.status === 422) {
                                return response.json().then(data => {
                                    displayValidationErrors(form, data.errors || {});
                                    throw new Error('validation');
                                });
                            }

                            if (!response.ok) {
                                return response.text().then(body => {
                                    const error = new Error('request_failed');
                                    error.status = response.status;
                                    error.statusText = response.statusText;
                                    error.body = body;
                                    error.url = response.url;
                                    throw error;
                                });
                            }

                            return response.json();
                        })
                        .then(data => {
                            processMutationResponse(data);
                            const modalElement = form.closest('.modal');
                            closeModal(modalElement);

                            if (data.message) {
                                const alertMap = {
                                    error: 'danger',
                                    warning: 'warning',
                                    success: 'success',
                                };
                                const alertType = alertMap[data.message_type] || 'success';
                                showFeedback(data.message, alertType);
                            }
                        })
                        .catch(error => {
                            if (error && error.message === 'validation') {
                                return;
                            }

                            console.error('Valabilități curse AJAX error', error);

                            const detailText = buildErrorDetails(error);
                            const baseMessage = 'A apărut o eroare neașteptată. Reîncercați.';
                            showFeedback(detailText ? `${baseMessage}${detailText}` : baseMessage, 'danger');
                        })
                        .finally(() => {
                            if (submitButton) {
                                submitButton.disabled = false;
                                if (submitButton.dataset.originalHtml !== undefined) {
                                    submitButton.innerHTML = submitButton.dataset.originalHtml || originalHtml || submitButton.innerHTML;
                                    delete submitButton.dataset.originalHtml;
                                }
                            }
                        });
                }

                function attachFormHandlers() {
                    if (!supportsAjax()) {
                        return;
                    }

                    const forms = document.querySelectorAll('.curse-modal-form');
                    forms.forEach(form => {
                        if (form.dataset.ajaxBound === 'true') {
                            return;
                        }

                        form.addEventListener('submit', handleFormSubmit);
                        form.dataset.ajaxBound = 'true';
                    });
                }

                function handleLoadMore(event) {
                    if (event) {
                        event.preventDefault();
                    }

                    if (!loadState.nextUrl || loadState.loading) {
                        return;
                    }

                    setLoadingState(true);

                    fetch(loadState.nextUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('request_failed');
                            }

                            return response.json();
                        })
                        .then(data => {
                            if (data.rows_html && tableBody) {
                                appendHtml(tableBody, data.rows_html);
                                bindBulkCheckboxes();
                                updateBulkAssignUi();
                            }

                            if (data.modals_html && modalsContainer) {
                                appendHtml(modalsContainer, data.modals_html);
                                refreshBulkModalReferences();
                                bindBulkModalEvents();
                                syncBulkGroupOptions();
                                initCountryInputs();
                                if (supportsAjax()) {
                                    attachFormHandlers();
                                }
                            }

                            loadState.nextUrl = data.next_url || null;

                            if (loadState.trigger) {
                                loadState.trigger.dataset.nextUrl = loadState.nextUrl || '';
                            }

                            if (!loadState.nextUrl) {
                                if (loadState.button) {
                                    loadState.button.remove();
                                }
                                loadState.button = null;
                                loadState.spinner = null;
                                loadState.label = null;
                                disconnectObserver();
                            }

                            if (loadState.button) {
                                loadState.button.classList.remove('btn-danger');
                            }

                            if (supportsAjax()) {
                                attachFormHandlers();
                            }
                        })
                        .catch(() => {
                            if (loadState.button) {
                                loadState.button.classList.add('btn-danger');
                                loadState.button.disabled = false;
                            }

                            if (loadState.label) {
                                loadState.label.textContent = 'Reîncearcă';
                            }
                        })
                        .finally(() => {
                            setLoadingState(false);
                        });
                }

                function initLoadMore() {
                    disconnectObserver();

                    loadState.button = document.getElementById('curse-load-more');
                    loadState.trigger = document.getElementById('curse-load-more-trigger');
                    loadState.spinner = loadState.button ? loadState.button.querySelector('.spinner-border') : null;
                    loadState.label = loadState.button ? loadState.button.querySelector('.load-more-label') : null;
                    loadState.nextUrl = loadState.trigger ? loadState.trigger.dataset.nextUrl || null : null;
                    loadState.loading = false;

                    if (loadState.button) {
                        loadState.button.addEventListener('click', handleLoadMore);
                        loadState.button.classList.remove('btn-danger');
                    }

                    if ('IntersectionObserver' in window && loadState.trigger) {
                        loadState.observer = new IntersectionObserver(entries => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    handleLoadMore();
                                }
                            });
                        });

                        loadState.observer.observe(loadState.trigger);
                    }
                }

                const showActiveModal = () => {
                    if (!modalsContainer) {
                        return;
                    }

                    const activeModal = modalsContainer.dataset.activeModal;
                    if (!activeModal) {
                        return;
                    }

                    let modalId = null;

                    if (activeModal === 'create') {
                        modalId = 'cursaCreateModal';
                    } else if (activeModal.startsWith('edit:')) {
                        const parts = activeModal.split(':');
                        if (parts.length === 2 && parts[1]) {
                            modalId = `cursaEditModal${parts[1]}`;
                        }
                    } else if (activeModal === 'group-create') {
                        modalId = 'cursaGroupCreateModal';
                    } else if (activeModal.startsWith('group-edit:')) {
                        const parts = activeModal.split(':');
                        if (parts.length === 2 && parts[1]) {
                            modalId = `cursaGroupEditModal${parts[1]}`;
                        }
                    }

                    if (modalId) {
                        const modalElement = document.getElementById(modalId);
                        showModalElement(modalElement);
                    }

                    modalsContainer.dataset.activeModal = '';
                };

                initCountryInputs();
                initLoadMore();
                initBulkAssignControls();

                if (supportsAjax()) {
                    attachFormHandlers();
                }

                showActiveModal();
            });
        </script>
    @endpush
@endsection
