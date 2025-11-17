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

        .curse-data-table tbody tr:nth-child(even) {
            background-color: #fcfcfc;
        }

        .curse-data-table tbody tr:hover {
            background-color: #f1f3f5;
        }

        .curse-nowrap {
            white-space: nowrap;
        }
    </style>

    <div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-10 col-xl-10 mb-2 mb-lg-0">
                <span class="badge culoare1 fs-5">
                    <span class="d-inline-flex flex-column align-items-start gap-1 lh-1">
                        <span><i class="fa-solid fa-route me-1"></i>Curse</span>
                        <small class="text-white-50">{{ $valabilitate->denumire }}</small>
                    </span>
                </span>
            </div>
            <div class="col-lg-2 col-xl-2 text-lg-end mt-3 mt-lg-0">
                <div class="d-flex flex-column align-items-stretch align-items-lg-end gap-2">
                    <a
                        href="{{ $backUrl }}"
                        class="btn btn-sm btn-outline-secondary border border-dark rounded-3"
                    >
                        <i class="fa-solid fa-list me-1"></i>Înapoi la valabilități
                    </a>
                    <button
                        type="button"
                        class="btn btn-sm btn-success text-white border border-dark rounded-3"
                        data-bs-toggle="modal"
                        data-bs-target="#cursaCreateModal"
                    >
                        <i class="fas fa-plus-square text-white me-1"></i>Adaugă cursă
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body px-0 py-3">
            @include('errors')

            @php
                $curseCollection = $curse instanceof \Illuminate\Contracts\Pagination\Paginator
                    || $curse instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator
                    || $curse instanceof \Illuminate\Contracts\Pagination\CursorPaginator
                    ? collect($curse->items())
                    : collect($curse);

                $kmPlecare = $curseCollection
                    ->pluck('km_bord_incarcare')
                    ->filter(static fn ($value) => $value !== null && $value !== '')
                    ->min();

                $kmSosire = $curseCollection
                    ->pluck('km_bord_descarcare')
                    ->filter(static fn ($value) => $value !== null && $value !== '')
                    ->max();

                $kmTotal = $kmPlecare !== null && $kmSosire !== null
                    ? (float) $kmSosire - (float) $kmPlecare
                    : null;

                $dataInceput = $valabilitate->data_inceput;
                $dataSfarsit = $valabilitate->data_sfarsit;
                $totalZile = $dataInceput && $dataSfarsit
                    ? $dataInceput->diffInDays($dataSfarsit) + 1
                    : null;

                // Extra totals for the summary band
                $totalKmMaps = $curseCollection
                    ->pluck('km_maps')
                    ->filter(static fn ($v) => $v !== null && $v !== '' && is_numeric($v))
                    ->map(static fn ($v) => (float) $v)
                    ->sum();

                $totalKmBord2 = $curseCollection
                    ->map(static function ($cursa) {
                        $start = $cursa->km_bord_incarcare !== null && $cursa->km_bord_incarcare !== ''
                            ? (float) $cursa->km_bord_incarcare
                            : null;
                        $end = $cursa->km_bord_descarcare !== null && $cursa->km_bord_descarcare !== ''
                            ? (float) $cursa->km_bord_descarcare
                            : null;

                        return $start !== null && $end !== null ? $end - $start : null;
                    })
                    ->filter(static fn ($v) => $v !== null)
                    ->sum();

                $totalKmDiff = $curseCollection
                    ->map(static function ($cursa) {
                        $start = $cursa->km_bord_incarcare !== null && $cursa->km_bord_incarcare !== ''
                            ? (float) $cursa->km_bord_incarcare
                            : null;
                        $end = $cursa->km_bord_descarcare !== null && $cursa->km_bord_descarcare !== ''
                            ? (float) $cursa->km_bord_descarcare
                            : null;
                        $maps = is_numeric($cursa->km_maps) ? (float) $cursa->km_maps : null;

                        $bord2 = $start !== null && $end !== null ? $end - $start : null;

                        return $bord2 !== null && $maps !== null ? $bord2 - $maps : null;
                    })
                    ->filter(static fn ($v) => $v !== null)
                    ->sum();
            @endphp

            {{-- Excel-style summary band --}}
            <div class="px-3 mb-3">
                <table class="curse-summary-table">
                    <tr>
                        <th class="curse-summary-title">
                            {{ $valabilitate->numar_auto ?? '—' }}
                        </th>
                        <th colspan="2" class="curse-summary-driver">
                            {{ $valabilitate->sofer->name ?? '—' }}
                        </th>
                    </tr>
                    <tr>
                        <th class="curse-summary-label">Dată plecare</th>
                        <td class="curse-nowrap">
                            {{ optional($dataInceput)->format('d.m.Y') ?? '—' }}
                        </td>

                        <th class="text-end curse-summary-label">KM plecare</th>
                        <td class="text-end curse-nowrap">
                            {{ $kmPlecare !== null ? $kmPlecare : '—' }}
                        </td>

                        <th class="text-end curse-summary-label">KM Maps total</th>
                        <td class="text-end curse-nowrap">
                            {{ $totalKmMaps ? $totalKmMaps : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <th class="curse-summary-label">Dată sosire</th>
                        <td class="curse-nowrap">
                            {{ optional($dataSfarsit)->format('d.m.Y') ?? '—' }}
                        </td>

                        <th class="text-end curse-summary-label">KM sosire</th>
                        <td class="text-end curse-nowrap">
                            {{ $kmSosire !== null ? $kmSosire : '—' }}
                        </td>

                        <th class="text-end curse-summary-label">KM Bord 2 total</th>
                        <td class="text-end curse-nowrap">
                            {{ $totalKmBord2 ? $totalKmBord2 : '—' }}
                        </td>
                    </tr>
                    <tr>
                        <th class="curse-summary-label">Total zile</th>
                        <td class="curse-nowrap">
                            {{ $totalZile !== null ? $totalZile : '—' }}
                        </td>

                        <th class="text-end curse-summary-label">KM total (plecare → sosire)</th>
                        <td class="text-end curse-nowrap">
                            {{ $kmTotal !== null ? $kmTotal : '—' }}
                        </td>

                        <th class="text-end curse-summary-label">Diferență totală (Bord–Maps)</th>
                        <td class="text-end curse-nowrap">
                            {{ $totalKmDiff ? $totalKmDiff : '—' }}
                        </td>
                    </tr>
                </table>
            </div>

            <div id="curse-feedback" class="px-3"></div>

            <div class="px-3">
                <div class="table-responsive">
                    <table class="table table-sm curse-data-table align-middle">
                        <thead>
                            <tr class="curse-data-header-top">
                                <th rowspan="2" class="text-center curse-nowrap">#</th>
                                <th rowspan="2" class="curse-nowrap">Nr. cursă</th>
                                <th rowspan="2">Cursa</th>
                                <th rowspan="2" class="curse-nowrap">Dată transport</th>
                                <th rowspan="2" class="text-end curse-nowrap">KM Maps</th>
                                <th colspan="2" class="text-center curse-nowrap">
                                    KM Bord (plecare / sosire)
                                </th>
                                <th rowspan="2" class="text-end curse-nowrap">KM Bord 2</th>
                                <th rowspan="2" class="text-end curse-nowrap">Sumă încasată</th>
                                <th rowspan="2" class="text-end curse-nowrap">
                                    Diferența KM<br>(Bord – Maps)
                                </th>
                                <th rowspan="2" class="text-end curse-nowrap">Acțiuni</th>
                            </tr>
                            <tr class="curse-data-header-bottom">
                                <th class="text-end curse-nowrap">Plecare</th>
                                <th class="text-end curse-nowrap">Sosire</th>
                            </tr>
                        </thead>

                        <tbody id="curse-table-body">
                            @if ($curse->count())
                                @include('valabilitati.curse.partials.rows', ['curse' => $curse, 'valabilitate' => $valabilitate])
                            @else
                                <tr>
                                    <td colspan="11" class="text-center py-4">
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
        @include('valabilitati.curse.partials.modals', [
            'valabilitate' => $valabilitate,
            'curse' => $curse,
            'includeCreate' => true,
            'formType' => old('form_type'),
            'formId' => old('form_id'),
            'tari' => $tari,
        ])
    </div>

    @push('page-scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const bootstrap = window.bootstrap;
                const bootstrapModal = bootstrap && bootstrap.Modal ? bootstrap.Modal : null;
                const modalsContainer = document.getElementById('curse-modals');
                const tableBody = document.getElementById('curse-table-body');
                const feedbackContainer = document.getElementById('curse-feedback');
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
                    if (data.table_html && tableBody) {
                        tableBody.innerHTML = data.table_html;
                    }

                    if (modalsContainer && data.modals_html) {
                        modalsContainer.innerHTML = data.modals_html;
                        modalsContainer.dataset.activeModal = '';
                    }

                    const loadMoreTrigger = document.getElementById('curse-load-more-trigger');
                    if (loadMoreTrigger) {
                        loadMoreTrigger.dataset.nextUrl = data.next_url || '';
                    }

                    initCountryInputs();
                    initLoadMore();

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
                                showFeedback(data.message, 'success');
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
                            }

                            if (data.modals_html && modalsContainer) {
                                appendHtml(modalsContainer, data.modals_html);
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
                    }

                    if (modalId) {
                        const modalElement = document.getElementById(modalId);
                        showModalElement(modalElement);
                    }

                    modalsContainer.dataset.activeModal = '';
                };

                initCountryInputs();
                initLoadMore();

                if (supportsAjax()) {
                    attachFormHandlers();
                }

                showActiveModal();
            });
        </script>
    @endpush
@endsection
