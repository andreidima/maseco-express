<div class="modal fade text-dark" id="valabilitate-delete-modal" tabindex="-1" aria-labelledby="valabilitate-delete-modal-label" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="valabilitate-delete-modal-label">Șterge valabilitate</h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Închide"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0" id="valabilitate-delete-message">Sigur ștergi această valabilitate?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                <button type="button" class="btn btn-danger text-white border border-dark rounded-3" id="valabilitate-delete-confirm">
                    <i class="fa-solid fa-trash-can me-1"></i>Șterge
                </button>
            </div>
        </div>
    </div>
</div>

<form method="POST" class="d-none" id="valabilitate-delete-form">
    @csrf
    @method('DELETE')
</form>

@once
    @push('page-scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const modalElement = document.getElementById('valabilitate-delete-modal');
                const messageElement = document.getElementById('valabilitate-delete-message');
                const confirmButton = document.getElementById('valabilitate-delete-confirm');
                const deleteForm = document.getElementById('valabilitate-delete-form');
                const bootstrap = window.bootstrap;
                let modalInstance = null;

                const showModal = trigger => {
                    if (!trigger || !deleteForm) {
                        return;
                    }

                    const action = trigger.getAttribute('data-delete-url');
                    if (!action) {
                        return;
                    }

                    deleteForm.setAttribute('action', action);

                    const denumire = trigger.getAttribute('data-delete-denumire') || '';
                    const numarAuto = trigger.getAttribute('data-delete-numar-auto') || '';

                    if (messageElement) {
                        let message = 'Sigur ștergi valabilitatea?';
                        if (denumire && numarAuto) {
                            message = `Sigur ștergi valabilitatea „${denumire}” pentru „${numarAuto}”?`;
                        } else if (denumire) {
                            message = `Sigur ștergi valabilitatea „${denumire}”?`;
                        } else if (numarAuto) {
                            message = `Sigur ștergi valabilitatea pentru „${numarAuto}”?`;
                        }
                        messageElement.textContent = message;
                    }

                    if (bootstrap && bootstrap.Modal) {
                        modalInstance = bootstrap.Modal.getOrCreateInstance(modalElement);
                        modalInstance.show();
                    } else if (modalElement) {
                        modalElement.classList.add('show');
                        modalElement.style.display = 'block';
                    }
                };

                document.addEventListener('click', event => {
                    const trigger = event.target.closest('[data-valabilitate-delete]');
                    if (!trigger) {
                        return;
                    }

                    event.preventDefault();
                    showModal(trigger);
                });

                if (confirmButton) {
                    confirmButton.addEventListener('click', () => {
                        if (!deleteForm) {
                            return;
                        }

                        confirmButton.disabled = true;
                        deleteForm.submit();
                    });
                }

                if (modalElement) {
                    modalElement.addEventListener('hidden.bs.modal', () => {
                        if (confirmButton) {
                            confirmButton.disabled = false;
                        }
                    });
                }
            });
        </script>
    @endpush
@endonce
