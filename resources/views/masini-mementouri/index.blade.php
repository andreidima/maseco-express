@extends('layouts.app')

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
                                @endphp
                                <td class="text-center">
                                    @if ($document)
                                        @include('masini-mementouri.partials.document-cell', ['masina' => $masina, 'document' => $document])
                                    @endif
                                </td>
                            @endforeach
                            @foreach ($vignetteCountries as $code => $label)
                                @php
                                    $document = $documents->get(\App\Models\Masini\MasinaDocument::TYPE_VIGNETA . ':' . $code);
                                @endphp
                                <td class="text-center">
                                    @if ($document)
                                        @include('masini-mementouri.partials.document-cell', ['masina' => $masina, 'document' => $document])
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center">
                                <a href="{{ route('masini-mementouri.show', $masina) }}" class="btn btn-sm btn-outline-primary border border-dark rounded-3">
                                    <i class="fa-solid fa-file-lines me-1"></i>Documente
                                </a>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2 justify-content-end">
                                    <a href="{{ route('masini-mementouri.show', $masina) }}" class="badge bg-primary text-decoration-none">Editează</a>
                                    <form method="POST" action="{{ route('masini-mementouri.destroy', $masina) }}" onsubmit="return confirm('Sigur dorești să ștergi această mașină?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="badge bg-danger border-0">Șterge</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 5 + count($gridDocumentTypes) + count($vignetteCountries) }}" class="text-center py-4">Nu există mașini înregistrate momentan.</td>
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

        const forms = document.querySelectorAll('[data-document-update]');

        forms.forEach((form) => {
            const input = form.querySelector('input[type="date"]');
            if (!input) {
                return;
            }

            input.addEventListener('change', () => {
                const formData = new FormData(form);
                const token = form.querySelector('input[name="_token"]').value;

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (!data || data.status !== 'ok') {
                            return;
                        }

                        const holder = form.closest('[data-color-holder]');
                        if (holder) {
                            holder.className = holder.dataset.baseClass + (data.color_class ? ' ' + data.color_class : '');
                        }

                        const daysLabel = form.querySelector('[data-days-label]');
                        if (daysLabel) {
                            if (data.days_until_expiry === null || typeof data.days_until_expiry === 'undefined') {
                                daysLabel.textContent = 'Fără dată';
                            } else if (data.days_until_expiry < 0) {
                                const days = Math.abs(data.days_until_expiry);
                                daysLabel.textContent = `Expirat de ${days} ${days === 1 ? 'zi' : 'zile'}`;
                            } else {
                                daysLabel.textContent = `Expiră în ${data.days_until_expiry} ${data.days_until_expiry === 1 ? 'zi' : 'zile'}`;
                            }
                        }
                    })
                    .catch(() => {
                        // Silent failure; inline feedback is optional.
                    });
            });
        });
    });
</script>
@endpush
