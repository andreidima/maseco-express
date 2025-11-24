@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-1 mb-2">
            <span class="badge culoare1 fs-5">
                <span class="d-inline-flex flex-column align-items-start gap-1 lh-1">
                    <span><i class="fa-solid fa-calendar-check me-1"></i>Valabilități</span>
                </span>
            </span>
        </div>
        <div class="col-lg-10 mb-0" id="formularValabilitati">
            <form class="needs-validation mb-lg-0" novalidate method="GET" action="{{ url()->current() }}">
                <div class="row mb-1 custom-search-form justify-content-center align-items-end" id="datePicker">
                    <div class="col-lg-2 col-md-6 mb-2 mb-lg-0">
                        <input type="text" class="form-control rounded-3" id="filter-numar-auto" name="numar_auto" placeholder="Număr auto" value="{{ $filters['numar_auto'] }}">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <input type="text" class="form-control rounded-3" id="filter-sofer" name="sofer" placeholder="Șofer" value="{{ $filters['sofer'] }}">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2 mb-lg-0">
                        <input type="text" class="form-control rounded-3" id="filter-divizie" name="divizie" placeholder="Divizie" value="{{ $filters['divizie'] }}">
                    </div>
                    <div class="col-lg-2 col-md-6 mb-2 mb-lg-0">
                        <label for="filter-status" class="form-label mb-0 ps-3">Status</label>
                        <select id="filter-status" name="status" class="form-select rounded-3">
                            <option value="active" @selected($filters['status'] === 'active')>În lucru</option>
                            <option value="finished" @selected($filters['status'] === 'finished')>Finalizate</option>
                            <option value="all" @selected($filters['status'] === 'all')>Toate</option>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-4 mb-2 mb-md-0">
                        <button class="btn btn-sm w-100 btn-primary text-white border border-dark rounded-3" type="submit">
                            <i class="fas fa-search text-white me-1"></i>Caută
                        </button>
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <a class="btn btn-sm w-100 btn-secondary text-white border border-dark rounded-3" href="{{ route('valabilitati.index') }}" role="button">
                            <i class="far fa-trash-alt text-white me-1"></i>Resetează
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-lg-1 text-lg-end mt-3 mt-lg-0">
            <div class="d-flex flex-column align-items-stretch align-items-lg-end gap-2">
                <a
                    href="{{ route('valabilitati.create') }}"
                    class="btn btn-sm btn-success text-white border border-dark rounded-3"
                >
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă valabilitate
                </a>
            </div>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors', ['showSessionAlerts' => false])
        @if (session('status'))
            <div class="alert alert-success mx-3" role="alert">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mx-3" role="alert">
                {{ session('error') }}
            </div>
        @endif
        <div id="valabilitati-feedback" class="px-3"></div>

        <div class="table-responsive rounded">
            <table class="table table-sm table-striped table-hover rounded align-middle">
                <thead class="text-white rounded culoare2">
                    <tr>
                        <th>Divizie</th>
                        <th>Număr auto</th>
                        <th>Șofer</th>
                        <th>Început</th>
                        <th>Sfârșit</th>
                        <th>Status</th>
                        <th class="text-end">Acțiuni</th>
                    </tr>
                </thead>
                <tbody id="valabilitati-table-body">
                    @if ($valabilitati->count())
                        @include('valabilitati.partials.rows', ['valabilitati' => $valabilitati])
                    @else
                        <tr>
                            <td colspan="7" class="text-center py-4">Nu există valabilități care să respecte criteriile selectate.</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>

        @if ($valabilitati instanceof \Illuminate\Contracts\Pagination\Paginator || $valabilitati instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div id="valabilitati-infinite-scroll" class="mt-3 px-3">
                <div
                    id="valabilitati-load-more-trigger"
                    class="d-flex justify-content-center py-3"
                    data-next-url="{{ $nextPageUrl }}"
                >
                    @if ($valabilitati->hasMorePages())
                        <button type="button" class="btn btn-outline-primary" id="valabilitati-load-more">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="load-more-label">Încarcă mai multe</span>
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>

@include('valabilitati.partials.divizie-prices-modal')
@include('valabilitati.partials.valabilitate-prices-modal')

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableBody = document.getElementById('valabilitati-table-body');
            const feedbackContainer = document.getElementById('valabilitati-feedback');
            const loadMoreButton = document.getElementById('valabilitati-load-more');
            const loadMoreTrigger = document.getElementById('valabilitati-load-more-trigger');
            const spinner = loadMoreButton ? loadMoreButton.querySelector('.spinner-border') : null;
            const label = loadMoreButton ? loadMoreButton.querySelector('.load-more-label') : null;

            const supportsAjax = () => typeof window.fetch === 'function';
            let isLoading = false;
            let observer = null;

            const getNextUrl = () => (loadMoreTrigger ? loadMoreTrigger.dataset.nextUrl || '' : '');

            const setNextUrl = (url) => {
                if (loadMoreTrigger) {
                    loadMoreTrigger.dataset.nextUrl = url || '';
                }
            };

            const setLoadingState = (loading) => {
                if (!loadMoreButton) {
                    return;
                }

                loadMoreButton.disabled = loading;

                if (spinner) {
                    spinner.classList.toggle('d-none', !loading);
                }

                if (label) {
                    label.textContent = loading ? 'Se încarcă...' : 'Încarcă mai multe';
                }
            };

            const renderFeedback = (message, type = 'success') => {
                if (!feedbackContainer || !message) {
                    return;
                }

                const alert = document.createElement('div');
                alert.className = `alert alert-${type} alert-dismissible fade show mt-3`;
                alert.setAttribute('role', 'alert');
                alert.textContent = message;

                const closeButton = document.createElement('button');
                closeButton.type = 'button';
                closeButton.className = 'btn-close';
                closeButton.setAttribute('data-bs-dismiss', 'alert');
                closeButton.setAttribute('aria-label', 'Închide');
                alert.appendChild(closeButton);

                feedbackContainer.innerHTML = '';
                feedbackContainer.appendChild(alert);
            };

            const clearFeedback = () => {
                if (feedbackContainer) {
                    feedbackContainer.innerHTML = '';
                }
            };

            const showError = (message) => {
                renderFeedback(message, 'danger');
            };

            const appendRows = (html) => {
                if (!tableBody || !html) {
                    return;
                }

                const template = document.createElement('template');
                template.innerHTML = html.trim();
                tableBody.appendChild(template.content);
            };

            const disconnectObserver = () => {
                if (observer) {
                    observer.disconnect();
                    observer = null;
                }
            };

            const handleLoadMore = () => {
                if (!supportsAjax() || isLoading) {
                    return;
                }

                const nextUrl = getNextUrl();
                if (!nextUrl) {
                    return;
                }

                isLoading = true;
                setLoadingState(true);
                if (loadMoreButton) {
                    loadMoreButton.classList.remove('btn-danger');
                }

                fetch(nextUrl, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`Request failed with status ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        appendRows(data.rows_html || '');
                        setNextUrl(data.next_url || '');
                        clearFeedback();

                        if (!data.next_url && loadMoreButton) {
                            loadMoreButton.remove();
                            disconnectObserver();
                        }
                    })
                    .catch(error => {
                        console.error('Valabilități load more error', error);
                        showError('Nu s-au putut încărca mai multe valabilități. Reîncercați.');
                        if (loadMoreButton) {
                            loadMoreButton.classList.add('btn-danger');
                        }
                    })
                    .finally(() => {
                        isLoading = false;
                        setLoadingState(false);
                    });
            };

            if (loadMoreButton) {
                loadMoreButton.addEventListener('click', handleLoadMore);
            }

            if ('IntersectionObserver' in window && loadMoreTrigger) {
                observer = new IntersectionObserver(entries => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            handleLoadMore();
                        }
                    });
                });

                observer.observe(loadMoreTrigger);
            }

            const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const priceFields = [
                'flash_pret_km_gol',
                'flash_pret_km_plin',
                'flash_pret_km_cu_taxa',
                'flash_contributie_zilnica',
                'timestar_pret_km_bord',
                'timestar_pret_nr_zile_lucrate',
            ];

            const getVisibleFieldsForDivizie = (divizieId) => {
                if (divizieId === 1) {
                    return [
                        'flash_pret_km_gol',
                        'flash_pret_km_plin',
                        'flash_pret_km_cu_taxa',
                        'flash_contributie_zilnica',
                    ];
                }

                if (divizieId === 3) {
                    return ['timestar_pret_km_bord', 'timestar_pret_nr_zile_lucrate'];
                }

                return [];
            };

            const formatDecimalValue = (value) => {
                if (value === null || value === undefined || value === '') {
                    return '';
                }

                const numberValue = Number(value);
                if (Number.isNaN(numberValue)) {
                    return '';
                }

                return numberValue.toFixed(3);
            };

            const createPriceModalController = ({
                modalElement,
                formSelector,
                triggerSelector,
                fetchUrlAttr,
                updateUrlAttr,
                nameSelector,
                extractPayload,
            }) => {
                if (!modalElement || typeof bootstrap === 'undefined' || !bootstrap.Modal) {
                    return null;
                }

                const modalInstance = new bootstrap.Modal(modalElement);
                const form = modalElement.querySelector(formSelector);
                const modalLoading = modalElement.querySelector('[data-modal-loading]');
                const modalFormWrapper = modalElement.querySelector('[data-modal-form-wrapper]');
                const modalAlert = modalElement.querySelector('[data-modal-alert]');
                const modalName = modalElement.querySelector(nameSelector);
                const modalEmptyState = modalElement.querySelector('[data-modal-empty-state]');
                const submitButton = modalElement.querySelector('[data-modal-submit]');
                const submitSpinner = modalElement.querySelector('[data-modal-submit-spinner]');
                const submitLabel = modalElement.querySelector('[data-modal-submit-label]');

                const fieldInputs = {};
                const fieldErrors = {};
                const fieldWrappers = {};

                priceFields.forEach(field => {
                    fieldInputs[field] = form ? form.querySelector(`[name="${field}"]`) : null;
                    fieldErrors[field] = form ? form.querySelector(`[data-error-for="${field}"]`) : null;
                    fieldWrappers[field] = form ? form.querySelector(`[data-field-wrapper="${field}"]`) : null;
                });

                let visibleFields = new Set();

                const updateFieldVisibility = (divizieId) => {
                    const fields = getVisibleFieldsForDivizie(divizieId);
                    visibleFields = new Set(fields);

                    priceFields.forEach(field => {
                        const wrapper = fieldWrappers[field];
                        const input = fieldInputs[field];
                        const isVisible = visibleFields.has(field);

                        if (wrapper) {
                            wrapper.classList.toggle('d-none', !isVisible);
                        }

                        if (input) {
                            input.disabled = !isVisible;
                        }
                    });

                    if (modalEmptyState) {
                        modalEmptyState.classList.toggle('d-none', visibleFields.size > 0);
                    }
                };

                const resetModalState = () => {
                    priceFields.forEach(field => {
                        const input = fieldInputs[field];
                        const errorElement = fieldErrors[field];

                        if (input) {
                            input.value = '';
                            input.classList.remove('is-invalid');
                        }

                        if (errorElement) {
                            errorElement.textContent = '';
                        }
                    });

                    updateFieldVisibility(null);
                };

                const toggleModalLoading = (loading) => {
                    if (modalLoading) {
                        modalLoading.classList.toggle('d-none', !loading);
                    }

                    if (modalFormWrapper) {
                        modalFormWrapper.classList.toggle('d-none', loading);
                    }
                };

                const setModalAlert = (message = '', type = 'danger') => {
                    if (!modalAlert) {
                        return;
                    }

                    modalAlert.classList.remove('alert-danger', 'alert-success');
                    modalAlert.classList.add(`alert-${type}`);

                    if (!message) {
                        modalAlert.classList.add('d-none');
                        modalAlert.textContent = '';
                        return;
                    }

                    modalAlert.textContent = message;
                    modalAlert.classList.remove('d-none');
                };

                const setModalSavingState = (saving) => {
                    if (!submitButton) {
                        return;
                    }

                    submitButton.disabled = saving;

                    if (submitSpinner) {
                        submitSpinner.classList.toggle('d-none', !saving);
                    }

                    if (submitLabel) {
                        submitLabel.textContent = saving ? 'Se salvează...' : 'Salvează';
                    }
                };

                const fillFormValues = (values = {}) => {
                    priceFields.forEach(field => {
                        const input = fieldInputs[field];
                        const errorElement = fieldErrors[field];

                        if (input) {
                            input.value = formatDecimalValue(values[field]);
                            input.classList.remove('is-invalid');
                        }

                        if (errorElement) {
                            errorElement.textContent = '';
                        }
                    });
                };

                const handleValidationErrors = (errors = {}) => {
                    priceFields.forEach(field => {
                        const messages = Array.isArray(errors[field]) ? errors[field] : [];
                        const input = fieldInputs[field];
                        const errorElement = fieldErrors[field];

                        if (input) {
                            input.classList.toggle('is-invalid', messages.length > 0);
                        }

                        if (errorElement) {
                            errorElement.textContent = messages.length ? messages[0] : '';
                        }
                    });
                };

                const openModal = (trigger) => {
                    if (!form) {
                        return;
                    }

                    const fetchUrl = trigger.getAttribute(fetchUrlAttr) || '';
                    const updateUrl = trigger.getAttribute(updateUrlAttr) || '';

                    if (!fetchUrl || !updateUrl) {
                        return;
                    }

                    form.dataset.updateUrl = updateUrl;

                    resetModalState();
                    handleValidationErrors();
                    setModalAlert('');
                    toggleModalLoading(true);

                    const initialName = trigger.getAttribute('data-price-modal-name') || '';
                    if (modalName && initialName) {
                        modalName.textContent = initialName;
                    }

                    modalInstance.show();

                    fetch(fetchUrl, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        credentials: 'same-origin',
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error(`Request failed with status ${response.status}`);
                            }

                            return response.json();
                        })
                        .then(data => {
                            const payload = extractPayload(data, trigger) || {};
                            const divizieId = Number(payload.divizieId);

                            if (modalName && payload.name) {
                                modalName.textContent = payload.name;
                            }

                            updateFieldVisibility(Number.isNaN(divizieId) ? null : divizieId);
                            fillFormValues(payload.values || {});
                        })
                        .catch(error => {
                            console.error('Valabilități tarife fetch error', error);
                            setModalAlert('Nu am putut încărca tarifele. Reîncercați.');
                        })
                        .finally(() => {
                            toggleModalLoading(false);
                        });
                };

                if (form) {
                    priceFields.forEach(field => {
                        const input = fieldInputs[field];
                        if (!input) {
                            return;
                        }

                        input.addEventListener('blur', () => {
                            if (input.value === '') {
                                return;
                            }

                            const numericValue = Number(input.value);
                            if (Number.isNaN(numericValue)) {
                                return;
                            }

                            input.value = numericValue.toFixed(3);
                        });
                    });

                    form.addEventListener('submit', event => {
                        event.preventDefault();

                        const updateUrl = form.dataset.updateUrl || '';
                        if (!updateUrl) {
                            return;
                        }

                        handleValidationErrors();
                        setModalAlert('');
                        setModalSavingState(true);

                        const payload = {};
                        visibleFields.forEach(field => {
                            const input = fieldInputs[field];
                            const value = input ? input.value.trim() : '';
                            payload[field] = value === '' ? null : value;
                        });

                        fetch(updateUrl, {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify(payload),
                        })
                            .then(async response => {
                                const data = await response.json().catch(() => ({}));

                                if (!response.ok) {
                                    if (response.status === 422) {
                                        handleValidationErrors(data.errors || {});
                                        setModalAlert(data.message || 'Verifică datele introduse.');
                                        throw new Error('validation');
                                    }

                                    throw new Error(data.message || `Request failed with status ${response.status}`);
                                }

                                return data;
                            })
                            .then(data => {
                                modalInstance.hide();
                                renderFeedback(data.message || 'Tarifele au fost actualizate.', 'success');
                            })
                            .catch(error => {
                                if (error.message === 'validation') {
                                    return;
                                }

                                console.error('Valabilități tarife save error', error);
                                setModalAlert('Nu am putut salva tarifele. Reîncercați.');
                            })
                            .finally(() => {
                                setModalSavingState(false);
                            });
                    });
                }

                modalElement.addEventListener('hidden.bs.modal', () => {
                    resetModalState();
                    handleValidationErrors();
                    setModalAlert('');
                    toggleModalLoading(true);

                    if (form) {
                        delete form.dataset.updateUrl;
                    }
                });

                return {
                    attach(container) {
                        container?.addEventListener('click', event => {
                            const trigger = event.target.closest(triggerSelector);
                            if (!trigger) {
                                return;
                            }

                            event.preventDefault();
                            openModal(trigger);
                        });
                    },
                };
            };

            const divizieModalController = createPriceModalController({
                modalElement: document.getElementById('valabilitati-divizie-modal'),
                formSelector: '[data-divizie-form]',
                triggerSelector: '[data-divizie-prices-trigger]',
                fetchUrlAttr: 'data-fetch-url',
                updateUrlAttr: 'data-update-url',
                nameSelector: '[data-price-modal-name]',
                extractPayload: (data, trigger) => {
                    const divizie = data.divizie || {};

                    return {
                        values: divizie,
                        divizieId: Number(divizie.id),
                        name: trigger?.getAttribute('data-divizie-name') || divizie.nume || '',
                    };
                },
            });

            const valabilitateModalController = createPriceModalController({
                modalElement: document.getElementById('valabilitati-price-modal'),
                formSelector: '[data-valabilitate-form]',
                triggerSelector: '[data-valabilitate-prices-trigger]',
                fetchUrlAttr: 'data-fetch-url',
                updateUrlAttr: 'data-update-url',
                nameSelector: '[data-price-modal-name]',
                extractPayload: (data, trigger) => {
                    const valabilitate = data.valabilitate || {};
                    const divizieId = Number(valabilitate.divizie_id ?? trigger?.getAttribute('data-divizie-id'));

                    return {
                        values: valabilitate,
                        divizieId,
                        name: valabilitate.numar_auto || trigger?.getAttribute('data-valabilitate-numar-auto') || '',
                    };
                },
            });

            if (tableBody) {
                divizieModalController?.attach(tableBody);
                valabilitateModalController?.attach(tableBody);
            }
        });
    </script>
@endpush

@include('valabilitati.partials.delete-modal')
@endsection
