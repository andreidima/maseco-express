@php
    $curse = $curse ?? collect();
    $modalKey = $modalKey ?? null;
@endphp

@include('sofer.valabilitati.partials.cursa-form', [
    'formType' => 'create',
    'modalId' => 'cursaCreateModal',
    'modalTitle' => 'Adaugă cursă',
    'submitLabel' => 'Salvează cursa',
    'formAction' => route('sofer.valabilitati.curse.store', $valabilitate),
    'method' => 'POST',
    'tari' => $tari,
    'requiresTimeByDefault' => $curse->isEmpty(),
    'lockTime' => $curse->isEmpty(),
])

@foreach ($curse as $cursa)
    @include('sofer.valabilitati.partials.cursa-form', [
        'formType' => 'edit',
        'formId' => $cursa->id,
        'modalId' => 'cursaEditModal-' . $cursa->id,
        'modalTitle' => 'Editează cursa',
        'submitLabel' => 'Actualizează cursa',
        'formAction' => route('sofer.valabilitati.curse.update', [$valabilitate, $cursa]),
        'method' => 'PUT',
        'tari' => $tari,
        'cursa' => $cursa,
        'requiresTimeByDefault' => false,
        'lockTime' => false,
    ])
@endforeach

<div class="modal fade" id="finalReturnConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header border-0 pb-0">
                <h2 class="h5 modal-title fw-bold">Ultima cursă către România?</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">Confirmarea marchează această cursă ca fiind întoarcerea acasă. Vom solicita și ora sosirii.</p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-final-return-cancel>Nu, continuă fără ora</button>
                <button type="button" class="btn btn-primary" data-final-return-confirm>Da, adaug ora sosirii</button>
            </div>
        </div>
    </div>
</div>

@push('page-styles')
<style>
    .sofer-valabilitati .list-group-item {
        border-left: 0;
        border-right: 0;
    }

    .sofer-valabilitati .list-group-item:first-child {
        border-top: 0;
    }

    .sofer-valabilitati .list-group-item:last-child {
        border-bottom: 0;
    }

    .sofer-valabilitati .cursa-time-field label::after {
        content: '*';
        color: #dc3545;
        margin-left: 0.25rem;
        font-weight: 600;
        display: none;
    }

    .sofer-valabilitati .cursa-time-field.is-required label::after {
        display: inline;
    }

    @media (max-width: 575.98px) {
        .sofer-valabilitati .list-group-item {
            padding-left: 1rem;
            padding-right: 1rem;
        }

        .sofer-valabilitati .list-group-item .btn {
            width: 100%;
        }

        .sofer-valabilitati .modal-dialog {
            margin: 1rem;
        }
    }
</style>
@endpush

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modalKey = @json($modalKey);
        const confirmModalEl = document.getElementById('finalReturnConfirmModal');

        let pendingForm = null;

        function toggleTimeField(form, visible, requireTime = null) {
            const container = form.querySelector('[data-time-container]');
            if (!container) {
                return;
            }

            const locked = form.dataset.lockTimeVisible === 'true';
            const defaultRequired = form.dataset.requireTimeDefault === 'true';
            const shouldRequire = requireTime !== null ? requireTime : (locked || defaultRequired);

            if (visible) {
                container.classList.remove('d-none');
                if (shouldRequire) {
                    container.classList.add('is-required');
                } else {
                    container.classList.remove('is-required');
                }
            } else if (!locked) {
                container.classList.add('d-none');
                container.classList.remove('is-required');
                const timeInput = container.querySelector('[data-time-input]');
                if (timeInput && !timeInput.dataset.preserveValue) {
                    timeInput.value = '';
                }
            }
        }

        function setFinalReturnFlag(form, value) {
            const input = form.querySelector('[data-final-return-input]');
            if (input) {
                input.value = value ? '1' : '0';
            }
        }

        function resetToInitialState(form) {
            const initialVisible = form.dataset.initialTimeVisible === 'true';
            const locked = form.dataset.lockTimeVisible === 'true';
            const initialFinal = form.dataset.initialFinalReturn === '1';
            toggleTimeField(form, initialVisible || locked, initialFinal || locked);
            setFinalReturnFlag(form, initialFinal);
        }

        document.querySelectorAll('[data-cursa-form]').forEach((form) => {
            const initialVisible = form.dataset.initialTimeVisible === 'true';
            const locked = form.dataset.lockTimeVisible === 'true';

            if (initialVisible || locked) {
                toggleTimeField(form, true);
            }

            const finalFlag = form.querySelector('[data-final-return-input]');
            if (finalFlag && finalFlag.value === '1') {
                toggleTimeField(form, true);
            }

            const select = form.querySelector('[data-descarcare-select]');
            if (!select) {
                return;
            }

            select.addEventListener('change', () => {
                const selectedText = select.options[select.selectedIndex]?.text?.toLowerCase().trim();
                const isRomania = selectedText === 'romania' || selectedText === 'românia';

                if (!isRomania) {
                    resetToInitialState(form);
                    return;
                }

                pendingForm = form;
                bootstrap.Modal.getOrCreateInstance(confirmModalEl).show();
            });
        });

        if (confirmModalEl) {
            const confirmModal = bootstrap.Modal.getOrCreateInstance(confirmModalEl);
            const confirmButton = confirmModalEl.querySelector('[data-final-return-confirm]');
            const cancelButton = confirmModalEl.querySelector('[data-final-return-cancel]');

            confirmButton?.addEventListener('click', () => {
                if (!pendingForm) {
                    return;
                }

                toggleTimeField(pendingForm, true, true);
                setFinalReturnFlag(pendingForm, 1);
                confirmModal.hide();
                pendingForm = null;
            });

            cancelButton?.addEventListener('click', () => {
                if (!pendingForm) {
                    return;
                }

                resetToInitialState(pendingForm);
                confirmModal.hide();
                pendingForm = null;
            });

            confirmModalEl.addEventListener('hidden.bs.modal', () => {
                if (!pendingForm) {
                    return;
                }

                resetToInitialState(pendingForm);
                pendingForm = null;
            });
        }

        if (modalKey) {
            const modal = document.querySelector(`[data-modal-key="${modalKey}"]`);
            if (modal) {
                bootstrap.Modal.getOrCreateInstance(modal).show();
            }
        }
    });
</script>
@endpush
