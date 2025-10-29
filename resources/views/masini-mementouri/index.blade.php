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

@include('masini-mementouri.partials.document-form-scripts')

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

        const documentUtilities = window.MasiniMementouriDocuments;

        if (!documentUtilities) {
            console.error('Modulele pentru formularele documentelor nu au fost încărcate.');
            return;
        }

        const {
            initializeDocumentForms,
        } = documentUtilities;

        initializeDocumentForms();
    });
</script>
@endpush
