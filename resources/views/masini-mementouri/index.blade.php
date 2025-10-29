@extends('layouts.app')

@push('page-styles')
    <style>
        .expanded-row-wrapper {
            background-color: #f8fafc;
            border-radius: 1.25rem;
            border: 1px solid rgba(0, 0, 0, 0.075);
            padding: 1.5rem;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.6);
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: max-height 0.3s ease, opacity 0.25s ease;
        }

        .expanded-row-wrapper.is-visible {
            max-height: 1200px;
            opacity: 1;
        }

        .expanded-row-document {
            border: 1px solid rgba(15, 23, 42, 0.12);
            border-radius: 1rem;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .expanded-row-document__header {
            padding: 0.85rem 1.1rem;
            border-bottom: 1px solid rgba(15, 23, 42, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 0.75rem;
        }

        .expanded-row-document__body {
            padding: 1rem 1.1rem 1.25rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .expanded-row-document__files {
            border-top: 1px dashed rgba(15, 23, 42, 0.12);
            padding-top: 0.75rem;
            margin-top: 0.25rem;
        }
    </style>
@endpush

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-4">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-car me-1"></i>Mementouri mașini
            </span>
        </div>
        <div class="col-lg-8 text-end">
            <button type="button" class="btn btn-primary border border-dark rounded-3" data-action="add-masina">
                <i class="fa-solid fa-plus me-1"></i>Adaugă mașină
            </button>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        @php
            $columnCount = 5 + count($gridDocumentTypes) + count($vignetteCountries);
            $today = now()->format('Y-m-d');
        @endphp
        <div class="table-responsive rounded">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="text-white rounded culoare2">
                    <tr>
                        <th>#</th>
                        <th>Număr auto</th>
                        <th>Descriere</th>
                        @foreach ($gridDocumentTypes as $label)
                            <th class="text-center">{{ $label }}</th>
                        @endforeach
                        @foreach ($vignetteCountries as $code => $label)
                            <th class="text-center">Vignetă {{ $label }}</th>
                        @endforeach
                        <th class="text-center">Documente</th>
                        <th class="text-end">Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($masini as $masina)
                        @php
                            $documents = $masina->documente->keyBy(fn($document) => $document->document_type . ($document->tara ? ':' . $document->tara : ''));
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-semibold">
                                <button type="button" class="btn btn-link p-0 align-baseline text-decoration-none fw-semibold" data-action="edit-masina" data-masina-id="{{ $masina->id }}">
                                    {{ $masina->numar_inmatriculare }}
                                </button>
                            </td>
                            <td>{{ $masina->descriere }}</td>
                            @foreach ($gridDocumentTypes as $type => $label)
                                @php
                                    $document = $documents->get($type);
                                    $displayDate = optional($document?->data_expirare)->format('d.m.Y') ?? 'N/A';
                                    $colorClass = $document?->colorClass() ?? 'bg-secondary-subtle text-body-secondary';
                                    $ariaLabel = "Editează {$label} pentru {$masina->numar_inmatriculare}";
                                @endphp
                                <td class="text-center">
                                    <a href="{{ route('masini-mementouri.edit', $masina) }}"
                                       class="d-inline-flex w-100 justify-content-center text-decoration-none text-reset"
                                       aria-label="{{ $ariaLabel }}">
                                        <span class="document-cell rounded-3 px-2 py-2 text-center d-inline-flex justify-content-center align-items-center w-100 {{ $colorClass }}">
                                            {{ $displayDate }}
                                        </span>
                                    </a>
                                </td>
                            @endforeach
                            @foreach ($vignetteCountries as $code => $label)
                                @php
                                    $document = $documents->get(\App\Models\Masini\MasinaDocument::TYPE_VIGNETA . ':' . $code);
                                    $displayDate = optional($document?->data_expirare)->format('d.m.Y') ?? 'N/A';
                                    $colorClass = $document?->colorClass() ?? 'bg-secondary-subtle text-body-secondary';
                                    $ariaLabel = "Editează Vignetă {$label} pentru {$masina->numar_inmatriculare}";
                                @endphp
                                <td class="text-center">
                                    <a href="{{ route('masini-mementouri.edit', $masina) }}"
                                       class="d-inline-flex w-100 justify-content-center text-decoration-none text-reset"
                                       aria-label="{{ $ariaLabel }}">
                                        <span class="document-cell rounded-3 px-2 py-2 text-center d-inline-flex justify-content-center align-items-center w-100 {{ $colorClass }}">
                                            {{ $displayDate }}
                                        </span>
                                    </a>
                                </td>
                            @endforeach
                            <td class="text-center">
                                <a href="{{ route('masini-mementouri.show', $masina) }}" class="btn btn-sm btn-outline-primary border border-dark rounded-3">
                                    <i class="fa-solid fa-file-lines me-1"></i>Documente
                                </a>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2 justify-content-end">
                                    <button type="button" class="badge bg-primary border-0" data-toggle-row data-masina-id="{{ $masina->id }}" aria-expanded="false">
                                        Editează
                                    </button>
                                    <form method="POST" action="{{ route('masini-mementouri.destroy', $masina) }}" onsubmit="return confirm('Sigur dorești să ștergi această mașină?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="badge bg-danger border-0">Șterge</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <tr class="expanded-row" data-expanded-row="{{ $masina->id }}" style="display: none;">
                            <td colspan="{{ $columnCount }}" class="border-top-0">
                                <div class="expanded-row-wrapper" data-expanded-row-content>
                                    <div class="d-flex flex-column flex-lg-row gap-3 align-items-lg-center mb-3">
                                        <div class="flex-grow-1">
                                            <label class="form-label mb-1" for="expanded_reference_date_{{ $masina->id }}">Dată de referință</label>
                                            <input type="date" id="expanded_reference_date_{{ $masina->id }}" class="form-control form-control-sm rounded-3"
                                                   value="{{ $today }}" data-master-date data-masina-id="{{ $masina->id }}">
                                            <div class="form-text">Folosește această dată pentru a completa rapid câmpurile goale.</div>
                                        </div>
                                        <div class="text-lg-end">
                                            <a href="{{ route('masini-mementouri.show', $masina) }}" class="btn btn-outline-secondary border border-dark rounded-3">
                                                <i class="fa-solid fa-up-right-from-square me-1"></i>Vezi pagina completă
                                            </a>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        @foreach ($masina->documente as $document)
                                            @php
                                                $documentKey = $document->document_type . ($document->tara ? ':' . $document->tara : '');
                                                $documentLabel = $uploadDocumentLabels[$documentKey] ?? ($gridDocumentTypes[$document->document_type] ?? $document->document_type);
                                                $documentDateValue = optional($document->data_expirare)->format('Y-m-d');
                                            @endphp
                                            <div class="col-xl-4 col-lg-6">
                                                <div class="expanded-row-document" data-document-wrapper data-document-id="{{ $document->id }}" data-empty-label="Fără dată">
                                                    <div class="expanded-row-document__header">
                                                        <span class="fw-semibold">{{ $documentLabel }}</span>
                                                        <span class="badge text-bg-light text-dark border" data-document-badge data-empty-label="Fără dată">{{ optional($document->data_expirare)->isoFormat('DD.MM.YYYY') ?? 'Fără dată' }}</span>
                                                    </div>
                                                    <div class="expanded-row-document__body">
                                                        <div class="document-feedback small" data-feedback-target hidden></div>

                                                        <form method="POST" action="{{ route('masini-mementouri.documente.update', [$masina, $document]) }}" class="row g-2 align-items-end" data-document-update>
                                                            @csrf
                                                            @method('PATCH')
                                                            <div class="col-12">
                                                                <label class="form-label" for="document_expiration_{{ $document->id }}">Dată expirare</label>
                                                                <input type="date" class="form-control form-control-sm" id="document_expiration_{{ $document->id }}" name="data_expirare" data-date-input
                                                                       value="{{ $documentDateValue ?? $today }}" data-sync-target="{{ $masina->id }}">
                                                            </div>
                                                            <div class="col-12">
                                                                <label class="form-label" for="document_email_{{ $document->id }}">Email alertă</label>
                                                                <input type="email" class="form-control form-control-sm" id="document_email_{{ $document->id }}" name="email_notificare"
                                                                       value="{{ $document->email_notificare }}">
                                                            </div>
                                                            <div class="col-12 text-end">
                                                                <button type="submit" class="btn btn-sm btn-outline-primary border border-dark rounded-3">
                                                                    <i class="fa-solid fa-floppy-disk me-1"></i>Salvează
                                                                </button>
                                                            </div>
                                                        </form>

                                                        <form method="POST" action="{{ route('masini-mementouri.documente.fisiere.store', [$masina, $document]) }}" enctype="multipart/form-data" class="row g-2 align-items-end" data-document-upload>
                                                            @csrf
                                                            <div class="col-12">
                                                                <label class="form-label" for="document_file_{{ $document->id }}">Adaugă document (PDF)</label>
                                                                <input type="file" class="form-control form-control-sm" id="document_file_{{ $document->id }}" name="fisier" accept="application/pdf" required>
                                                            </div>
                                                            <div class="col-12 text-end">
                                                                <button type="submit" class="btn btn-sm btn-success text-white border border-dark rounded-3">
                                                                    <i class="fa-solid fa-upload me-1"></i>Încarcă
                                                                </button>
                                                            </div>
                                                        </form>

                                                        <div class="expanded-row-document__files" data-document-files>
                                                            <p class="fw-semibold small mb-2">Fișiere existente</p>
                                                            @include('masini-mementouri.partials.document-files-list', ['masina' => $masina, 'document' => $document])
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $columnCount }}" class="text-center py-4">Nu există mașini înregistrate momentan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<div class="modal fade" id="masinaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" class="modal-content rounded-4" data-store-url="{{ route('masini-mementouri.store') }}">
            @csrf
            <input type="hidden" data-method-input>
            <input type="hidden" name="redirect" value="index">
            <input type="hidden" name="modal_origin" data-modal-origin>
            <input type="hidden" name="modal_masina_id" data-modal-masina-id>
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title" data-modal-title>Adaugă mașină</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" for="modal_numar_inmatriculare">Număr înmatriculare</label>
                        <input type="text" class="form-control rounded-3" id="modal_numar_inmatriculare" name="numar_inmatriculare" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="modal_descriere">Descriere</label>
                        <input type="text" class="form-control rounded-3" id="modal_descriere" name="descriere">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="modal_email_notificari">Email notificări</label>
                        <input type="email" class="form-control rounded-3" id="modal_email_notificari" name="email_notificari">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="modal_observatii">Observații</label>
                        <textarea class="form-control rounded-3" id="modal_observatii" name="observatii" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary border border-dark" data-bs-dismiss="modal">Renunță</button>
                <button type="submit" class="btn btn-primary border border-dark" data-submit-button>
                    <i class="fa-solid fa-plus me-1" data-submit-icon></i>
                    <span data-submit-text>Adaugă mașina</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalData = @json($masiniModalData);
        const defaultEmail = 'masecoexpres@gmail.com';

        const modalElement = document.getElementById('masinaModal');
        const bootstrapModal = modalElement && typeof bootstrap !== 'undefined'
            ? new bootstrap.Modal(modalElement)
            : null;

        const expandedState = {
            id: null,
            button: null,
        };

        const hideExpandedRow = (row) => {
            if (!row) {
                return;
            }

            const content = row.querySelector('[data-expanded-row-content]');
            if (content) {
                content.classList.remove('is-visible');
                const handleTransitionEnd = () => {
                    row.style.display = 'none';
                    content.removeEventListener('transitionend', handleTransitionEnd);
                };
                content.addEventListener('transitionend', handleTransitionEnd);
            } else {
                row.style.display = 'none';
            }
        };

        const showExpandedRow = (row) => {
            if (!row) {
                return;
            }

            const content = row.querySelector('[data-expanded-row-content]');
            row.style.display = 'table-row';

            requestAnimationFrame(() => {
                if (content) {
                    content.classList.add('is-visible');
                }
            });
        };

        document.querySelectorAll('[data-toggle-row]').forEach((button) => {
            button.addEventListener('click', () => {
                const id = button.dataset.masinaId;
                const row = document.querySelector(`[data-expanded-row="${id}"]`);

                if (!row) {
                    return;
                }

                const isCurrent = expandedState.id === id;

                if (expandedState.button) {
                    expandedState.button.setAttribute('aria-expanded', 'false');
                }

                if (expandedState.id && expandedState.id !== id) {
                    const previousRow = document.querySelector(`[data-expanded-row="${expandedState.id}"]`);
                    hideExpandedRow(previousRow);
                }

                if (isCurrent) {
                    hideExpandedRow(row);
                    expandedState.id = null;
                    expandedState.button = null;
                    button.setAttribute('aria-expanded', 'false');
                } else {
                    showExpandedRow(row);
                    expandedState.id = id;
                    expandedState.button = button;
                    button.setAttribute('aria-expanded', 'true');
                }
            });
        });

        document.querySelectorAll('[data-master-date]').forEach((input) => {
            const masinaId = input.dataset.masinaId;
            input.addEventListener('change', () => {
                document.querySelectorAll(`[data-sync-target="${masinaId}"]`).forEach((target) => {
                    if (target.dataset.syncEmpty === 'true') {
                        target.value = input.value;
                        target.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                });
            });
        });

        document.querySelectorAll('[data-sync-target]').forEach((input) => {
            input.dataset.syncEmpty = input.value ? 'false' : 'true';

            input.addEventListener('input', () => {
                input.dataset.syncEmpty = input.value ? 'false' : 'true';
            });
        });

        if (modalElement && bootstrapModal) {
            const form = modalElement.querySelector('form');
            const methodInput = form.querySelector('[data-method-input]');
            const modalOriginInput = form.querySelector('[data-modal-origin]');
            const modalMasinaIdInput = form.querySelector('[data-modal-masina-id]');
            const titleElement = modalElement.querySelector('[data-modal-title]');
            const submitButton = form.querySelector('[data-submit-button]');
            const submitIcon = submitButton.querySelector('[data-submit-icon]');
            const submitText = submitButton.querySelector('[data-submit-text]');
            const storeUrl = form.dataset.storeUrl;

            const inputs = {
                numar_inmatriculare: form.querySelector('input[name="numar_inmatriculare"]'),
                descriere: form.querySelector('input[name="descriere"]'),
                email_notificari: form.querySelector('input[name="email_notificari"]'),
                observatii: form.querySelector('textarea[name="observatii"]'),
            };

            const setMethod = (method = null) => {
                if (method) {
                    methodInput.name = '_method';
                    methodInput.value = method;
                } else {
                    methodInput.removeAttribute('name');
                    methodInput.value = '';
                }
            };

            const fillForm = (values = {}) => {
                inputs.numar_inmatriculare.value = values.numar_inmatriculare ?? '';
                inputs.descriere.value = values.descriere ?? '';
                inputs.email_notificari.value = values.email_notificari ?? defaultEmail;
                inputs.observatii.value = values.observatii ?? '';
            };

            const openCreateModal = () => {
                form.action = storeUrl;
                setMethod(null);
                modalOriginInput.value = 'create';
                modalMasinaIdInput.value = '';
                titleElement.textContent = 'Adaugă mașină';
                submitIcon.className = 'fa-solid fa-plus me-1';
                submitText.textContent = 'Adaugă mașina';
                fillForm({ email_notificari: defaultEmail });
                bootstrapModal.show();
            };

            const openEditModal = (id) => {
                const data = modalData[id];
                if (!data) {
                    return;
                }

                form.action = data.update_url;
                setMethod('PUT');
                modalOriginInput.value = 'edit';
                modalMasinaIdInput.value = id;
                titleElement.textContent = 'Editează mașina';
                submitIcon.className = 'fa-solid fa-floppy-disk me-1';
                submitText.textContent = 'Salvează modificările';
                fillForm(data);
                bootstrapModal.show();
            };

            document.querySelectorAll('[data-action="add-masina"]').forEach((button) => {
                button.addEventListener('click', () => {
                    openCreateModal();
                });
            });

            document.querySelectorAll('[data-action="edit-masina"]').forEach((button) => {
                button.addEventListener('click', () => {
                    const id = button.dataset.masinaId;
                    openEditModal(id);
                });
            });

            const oldOrigin = @json(old('modal_origin'));
            const oldMasinaId = @json(old('modal_masina_id'));
            const oldValues = {
                numar_inmatriculare: @json(old('numar_inmatriculare')),
                descriere: @json(old('descriere')),
                email_notificari: @json(old('email_notificari')),
                observatii: @json(old('observatii')),
            };

            if (oldOrigin) {
                if (oldOrigin === 'create') {
                    openCreateModal();
                    fillForm(oldValues);
                } else if (oldOrigin === 'edit' && oldMasinaId) {
                    openEditModal(oldMasinaId);
                    fillForm(oldValues);
                }
            }
        }

        const feedbackClasses = ['text-success', 'text-danger', 'text-muted'];

        const findFeedbackElement = (form) => {
            if (!form) {
                return null;
            }

            const direct = form.querySelector('[data-feedback-target]');
            if (direct) {
                return direct;
            }

            const wrapper = form.closest('[data-document-wrapper]');
            if (wrapper) {
                return wrapper.querySelector('[data-feedback-target]') ?? null;
            }

            return null;
        };

        const clearFeedback = (element) => {
            if (!element) {
                return;
            }

            feedbackClasses.forEach((className) => element.classList.remove(className));
            element.textContent = '';
            element.hidden = true;
        };

        const setFeedback = (element, type, message) => {
            if (!element) {
                return;
            }

            clearFeedback(element);

            const className = type === 'success'
                ? 'text-success'
                : type === 'error'
                    ? 'text-danger'
                    : 'text-muted';

            element.classList.add(className);
            element.textContent = message;
            element.hidden = !message;
        };

        const extractErrorMessage = (data, fallback = 'A apărut o eroare. Te rugăm să încerci din nou.') => {
            if (!data) {
                return fallback;
            }

            if (typeof data.message === 'string' && data.message.trim() !== '') {
                return data.message;
            }

            if (data.errors && typeof data.errors === 'object') {
                for (const key of Object.keys(data.errors)) {
                    const value = data.errors[key];
                    if (Array.isArray(value) && value.length > 0) {
                        return value[0];
                    }
                }
            }

            return fallback;
        };

        const isValidDateValue = (value) => {
            if (!value) {
                return true;
            }

            const match = /^([0-9]{4})-([0-9]{2})-([0-9]{2})$/.exec(value);

            if (!match) {
                return false;
            }

            const year = Number.parseInt(match[1], 10);
            const month = Number.parseInt(match[2], 10);
            const day = Number.parseInt(match[3], 10);
            const date = new Date(value);

            if (Number.isNaN(date.getTime())) {
                return false;
            }

            return date.getUTCFullYear() === year
                && date.getUTCMonth() + 1 === month
                && date.getUTCDate() === day;
        };

        const updateDocumentDisplays = (documentId, data, fallbackLabel = '—') => {
            if (!documentId) {
                return;
            }

            const wrappers = document.querySelectorAll(`[data-document-id="${documentId}"]`);
            const hasFormatted = Object.prototype.hasOwnProperty.call(data, 'formatted_date');
            const hasReadable = Object.prototype.hasOwnProperty.call(data, 'readable_date');

            wrappers.forEach((wrapper) => {
                const localFallback = wrapper.dataset.emptyLabel
                    || wrapper.querySelector('[data-document-badge]')?.dataset.emptyLabel
                    || fallbackLabel;

                const dateInput = wrapper.querySelector('input[name="data_expirare"]');
                if (dateInput && hasFormatted) {
                    dateInput.value = data.formatted_date ?? '';
                }

                const inlineDisplay = wrapper.querySelector('[data-date-text]');
                if (inlineDisplay) {
                    if (hasReadable) {
                        inlineDisplay.textContent = data.readable_date ?? localFallback;
                    } else if (hasFormatted) {
                        inlineDisplay.textContent = data.formatted_date ?? localFallback;
                    }
                }

                const badge = wrapper.querySelector('[data-document-badge]');
                if (badge && (hasReadable || hasFormatted)) {
                    const badgeFallback = badge.dataset.emptyLabel || localFallback;
                    if (hasReadable) {
                        badge.textContent = data.readable_date ?? badgeFallback;
                    } else {
                        badge.textContent = data.formatted_date ?? badgeFallback;
                    }
                }

                const holder = wrapper.dataset.baseClass ? wrapper : wrapper.querySelector('[data-color-holder]');

                if (holder && holder.dataset.baseClass && Object.prototype.hasOwnProperty.call(data, 'color_class')) {
                    const baseClass = holder.dataset.baseClass;
                    const colorClass = data.color_class ? ` ${data.color_class}` : '';
                    holder.className = baseClass + colorClass;
                }
            });
        };

        const refreshFileList = (wrapper, html) => {
            if (!wrapper) {
                return;
            }

            const container = wrapper.querySelector('[data-document-files]');

            if (!container) {
                return;
            }

            const existingList = container.querySelector('[data-document-files-list]');
            if (existingList) {
                existingList.remove();
            }

            if (html) {
                container.insertAdjacentHTML('beforeend', html);
            }

            initializeDocumentForms(container);
        };

        const initializeDocumentUpdateForm = (form) => {
            if (!form || form.dataset.initialized === 'true') {
                return;
            }

            form.dataset.initialized = 'true';

            const wrapper = form.closest('[data-document-wrapper]');
            const documentId = wrapper?.dataset.documentId;
            const tokenField = form.querySelector('input[name="_token"]');
            const dateInput = form.querySelector('[data-date-input], input[name="data_expirare"]');
            const trigger = form.querySelector('[data-edit-trigger]');
            const submitButton = form.querySelector('button[type="submit"]');
            const feedbackElement = findFeedbackElement(form);
            const emptyLabel = wrapper?.dataset.emptyLabel || '—';

            const setLoading = (loading) => {
                form.dataset.loading = loading ? 'true' : 'false';

                const interactiveElements = Array.from(form.elements).filter((element) => {
                    if (!element || !element.tagName) {
                        return false;
                    }

                    const tag = element.tagName.toLowerCase();
                    if (!['input', 'button', 'select', 'textarea'].includes(tag)) {
                        return false;
                    }

                    if (tag === 'input' && element.type === 'hidden') {
                        return false;
                    }

                    return true;
                });

                interactiveElements.forEach((element) => {
                    element.disabled = loading;
                });

                if (submitButton) {
                    submitButton.disabled = loading;
                }

                if (trigger) {
                    trigger.disabled = loading;
                }
            };

            const handleSubmit = async (event) => {
                event.preventDefault();

                if (form.dataset.loading === 'true') {
                    return;
                }

                clearFeedback(feedbackElement);

                if (form.reportValidity && !form.reportValidity()) {
                    return;
                }

                if (dateInput && !isValidDateValue(dateInput.value)) {
                    setFeedback(feedbackElement, 'error', 'Data introdusă nu este validă.');
                    return;
                }

                const formData = new FormData(form);

                setLoading(true);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': tokenField ? tokenField.value : '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    let data = null;

                    try {
                        data = await response.json();
                    } catch (error) {
                        data = null;
                    }

                    if (!response.ok || !data || data.status !== 'ok') {
                        setFeedback(feedbackElement, 'error', extractErrorMessage(data));
                        return;
                    }

                    setFeedback(feedbackElement, 'success', data.message || 'Modificarea a fost salvată.');
                    updateDocumentDisplays(documentId, data, emptyLabel);
                } catch (error) {
                    setFeedback(feedbackElement, 'error', 'A apărut o eroare neașteptată. Te rugăm să încerci din nou.');
                } finally {
                    setLoading(false);
                }
            };

            form.addEventListener('submit', handleSubmit);

            if (dateInput && form.dataset.autoSubmit === 'true') {
                dateInput.addEventListener('change', () => {
                    if (form.dataset.loading === 'true') {
                        return;
                    }

                    if (!isValidDateValue(dateInput.value)) {
                        setFeedback(feedbackElement, 'error', 'Data introdusă nu este validă.');
                        return;
                    }

                    if (typeof form.requestSubmit === 'function') {
                        form.requestSubmit();
                    } else {
                        form.submit();
                    }
                });
            }

            if (trigger && dateInput) {
                trigger.addEventListener('click', () => {
                    if (typeof dateInput.showPicker === 'function') {
                        dateInput.showPicker();
                    } else {
                        dateInput.focus();
                        dateInput.click();
                    }
                });
            }
        };

        const initializeDocumentUploadForm = (form) => {
            if (!form || form.dataset.initialized === 'true') {
                return;
            }

            form.dataset.initialized = 'true';

            const wrapper = form.closest('[data-document-wrapper]');
            const tokenField = form.querySelector('input[name="_token"]');
            const fileInput = form.querySelector('input[type="file"]');
            const submitButton = form.querySelector('button[type="submit"]');
            const feedbackElement = findFeedbackElement(form);

            const setLoading = (loading) => {
                form.dataset.loading = loading ? 'true' : 'false';

                if (submitButton) {
                    submitButton.disabled = loading;
                }

                if (fileInput) {
                    fileInput.disabled = loading;
                }
            };

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                if (form.dataset.loading === 'true') {
                    return;
                }

                clearFeedback(feedbackElement);

                if (!fileInput || fileInput.files.length === 0) {
                    setFeedback(feedbackElement, 'error', 'Te rugăm să selectezi un fișier PDF.');
                    return;
                }

                const formData = new FormData(form);

                setLoading(true);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': tokenField ? tokenField.value : '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    let data = null;

                    try {
                        data = await response.json();
                    } catch (error) {
                        data = null;
                    }

                    if (!response.ok || !data || data.status !== 'ok') {
                        setFeedback(feedbackElement, 'error', extractErrorMessage(data, 'Încărcarea a eșuat.'));
                        return;
                    }

                    setFeedback(feedbackElement, 'success', data.message || 'Fișierul a fost încărcat.');
                    if (data.files_html) {
                        refreshFileList(wrapper, data.files_html);
                    }

                    if (fileInput) {
                        fileInput.value = '';
                    }
                } catch (error) {
                    setFeedback(feedbackElement, 'error', 'A apărut o eroare neașteptată la încărcare.');
                } finally {
                    setLoading(false);
                }
            });
        };

        const initializeDocumentDeleteForm = (form) => {
            if (!form || form.dataset.initialized === 'true') {
                return;
            }

            form.dataset.initialized = 'true';

            const wrapper = form.closest('[data-document-wrapper]');
            const tokenField = form.querySelector('input[name="_token"]');
            const submitButton = form.querySelector('button[type="submit"]');
            const feedbackElement = findFeedbackElement(form);

            const setLoading = (loading) => {
                form.dataset.loading = loading ? 'true' : 'false';

                if (submitButton) {
                    submitButton.disabled = loading;
                }
            };

            form.addEventListener('submit', async (event) => {
                event.preventDefault();

                if (form.dataset.loading === 'true') {
                    return;
                }

                if (!window.confirm('Ștergi fișierul?')) {
                    return;
                }

                clearFeedback(feedbackElement);

                const formData = new FormData(form);

                setLoading(true);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': tokenField ? tokenField.value : '',
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    let data = null;

                    try {
                        data = await response.json();
                    } catch (error) {
                        data = null;
                    }

                    if (!response.ok || !data || data.status !== 'ok') {
                        setFeedback(feedbackElement, 'error', extractErrorMessage(data, 'Fișierul nu a putut fi șters.'));
                        return;
                    }

                    setFeedback(feedbackElement, 'success', data.message || 'Fișierul a fost șters.');
                    if (data.files_html) {
                        refreshFileList(wrapper, data.files_html);
                    }
                } catch (error) {
                    setFeedback(feedbackElement, 'error', 'A apărut o eroare neașteptată la ștergere.');
                } finally {
                    setLoading(false);
                }
            });
        };

        const initializeDocumentForms = (root = document) => {
            root.querySelectorAll('form[data-document-update]').forEach(initializeDocumentUpdateForm);
            root.querySelectorAll('form[data-document-upload]').forEach(initializeDocumentUploadForm);
            root.querySelectorAll('form[data-document-delete]').forEach(initializeDocumentDeleteForm);
        };

        initializeDocumentForms();
    });
</script>
@endpush
