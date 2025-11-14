@extends('layouts.app')

@section('content')
<div class="container py-4 py-md-5 sofer-valabilitati">
    <div class="mb-4">
        <a href="{{ route('sofer.dashboard') }}" class="btn btn-link text-decoration-none px-0">
            <i class="fa-solid fa-arrow-left-long me-1"></i>
            Înapoi la panou
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Închide"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-4">
                <div>
                    <p class="text-uppercase text-muted small fw-semibold mb-1">Valabilitate activă</p>
                    <h1 class="h4 fw-bold mb-1">{{ $valabilitate->denumire }}</h1>
                    <p class="text-muted mb-0">
                        {{ optional($valabilitate->data_inceput)->format('d.m.Y') ?? '—' }}
                        <span class="mx-1">–</span>
                        {{ optional($valabilitate->data_sfarsit)->format('d.m.Y') ?? 'Prezent' }}
                    </p>
                </div>
                <div class="text-lg-end">
                    <p class="text-uppercase text-muted small fw-semibold mb-1">Număr auto</p>
                    <p class="h5 fw-bold mb-3 mb-lg-2">{{ $valabilitate->numar_auto ?? 'Fără număr' }}</p>
                    <a
                        href="{{ route('sofer.valabilitati.curse.create', $valabilitate) }}"
                        class="btn btn-primary btn-sm px-4"
                    >
                        <i class="fa-solid fa-plus me-1"></i>
                        Adaugă cursă
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if ($curse->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-5">
                <i class="fa-solid fa-route fa-2x text-primary mb-3"></i>
                <p class="fw-semibold mb-1">Nu există curse înregistrate</p>
                <p class="text-muted small mb-0">Începeți prin adăugarea primei curse pentru această valabilitate.</p>
            </div>
        </div>
    @else
        <div class="d-flex flex-column gap-3">
            @foreach ($curse as $cursa)
                <article class="cursa-card card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-start gap-3">
                            <div class="flex-grow-1">
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                    <span class="badge bg-light text-dark fw-semibold">
                                        {{ optional($cursa->data_cursa)->format('d.m.Y H:i') ?? 'Fără dată' }}
                                    </span>
                                    @if ($cursa->km_bord)
                                        <span class="badge bg-body-secondary text-secondary fw-semibold">
                                            {{ number_format($cursa->km_bord, 0, '.', ' ') }} km
                                        </span>
                                    @endif
                                </div>
                                <div class="row g-3 small text-muted">
                                    <div class="col-12 col-md-6">
                                        <p class="fw-semibold text-uppercase text-dark small mb-2">Încărcare</p>
                                        <ul class="list-unstyled mb-0">
                                            <li>{{ $cursa->incarcare_localitate ?? '—' }}</li>
                                            <li>{{ $cursa->incarcare_cod_postal ?? '—' }}</li>
                                            <li>{{ $cursa->incarcareTara?->nume ?? '—' }}</li>
                                        </ul>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <p class="fw-semibold text-uppercase text-dark small mb-2">Descărcare</p>
                                        <ul class="list-unstyled mb-0">
                                            <li>{{ $cursa->descarcare_localitate ?? '—' }}</li>
                                            <li>{{ $cursa->descarcare_cod_postal ?? '—' }}</li>
                                            <li>{{ $cursa->descarcareTara?->nume ?? '—' }}</li>
                                        </ul>
                                    </div>
                                </div>
                                @if ($cursa->observatii)
                                    <p class="text-muted small mt-3 mb-0">{{ $cursa->observatii }}</p>
                                @endif
                            </div>
                            <div class="cursa-card__actions d-flex flex-column flex-sm-row align-items-stretch gap-2 w-100 w-lg-auto">
                                <a
                                    href="{{ route('sofer.valabilitati.curse.edit', [$valabilitate, $cursa]) }}"
                                    class="btn btn-outline-primary btn-sm"
                                >
                                    <i class="fa-solid fa-pen me-1"></i>
                                    Editează
                                </a>
                                <button
                                    type="button"
                                    class="btn btn-outline-danger btn-sm"
                                    data-delete-trigger
                                    data-delete-url="{{ route('sofer.valabilitati.curse.destroy', [$valabilitate, $cursa]) }}"
                                    data-delete-date="{{ optional($cursa->data_cursa)->format('d.m.Y H:i') ?? 'Fără dată' }}"
                                    data-delete-summary="{{ ($cursa->incarcare_localitate ?? '—') . ' → ' . ($cursa->descarcare_localitate ?? '—') }}"
                                >
                                    <i class="fa-solid fa-trash-can me-1"></i>
                                    Șterge
                                </button>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection

<form method="POST" id="deleteCursaForm" class="d-none">
    @csrf
    @method('DELETE')
</form>

<div class="modal fade" id="deleteCursaModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm">
            <div class="modal-header border-0 pb-0">
                <h2 class="h5 modal-title fw-bold">Șterge cursa</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
            </div>
            <div class="modal-body">
                <p class="mb-1">Confirmați că doriți să ștergeți această cursă?</p>
                <p class="text-muted small mb-0">
                    <span data-delete-date></span>
                    <span class="mx-1">•</span>
                    <span data-delete-summary></span>
                </p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Renunță</button>
                <button type="button" class="btn btn-danger" data-delete-confirm>Șterge cursa</button>
            </div>
        </div>
    </div>
</div>

@push('page-styles')
<style>
    .cursa-card {
        border-radius: 1rem;
    }

    .cursa-card__actions .btn {
        min-width: 150px;
    }

    .cursa-card__actions .btn + .btn {
        margin-left: 0;
    }

    @media (max-width: 575.98px) {
        .cursa-card__actions {
            width: 100%;
        }

        .cursa-card__actions .btn {
            width: 100%;
        }
    }
</style>
@endpush

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const deleteModalEl = document.getElementById('deleteCursaModal');
        const deleteForm = document.getElementById('deleteCursaForm');
        const bootstrapModal = window.bootstrap?.Modal ?? window.bootstrapModal ?? null;

        if (!deleteModalEl || !deleteForm) {
            return;
        }

        const dateHolder = deleteModalEl.querySelector('[data-delete-date]');
        const summaryHolder = deleteModalEl.querySelector('[data-delete-summary]');
        const confirmButton = deleteModalEl.querySelector('[data-delete-confirm]');

        let pendingUrl = null;

        const showModal = () => {
            if (bootstrapModal && typeof bootstrapModal.getOrCreateInstance === 'function') {
                bootstrapModal.getOrCreateInstance(deleteModalEl).show();

                return;
            }

            const $ = window.jQuery || window.$;
            if (typeof $ === 'function' && typeof $(deleteModalEl).modal === 'function') {
                $(deleteModalEl).modal('show');

                return;
            }

            deleteModalEl.classList.add('show');
            deleteModalEl.removeAttribute('aria-hidden');
        };

        const hideModal = () => {
            if (bootstrapModal && typeof bootstrapModal.getInstance === 'function') {
                const instance = bootstrapModal.getInstance(deleteModalEl);
                if (instance) {
                    instance.hide();
                    return;
                }
            }

            const $ = window.jQuery || window.$;
            if (typeof $ === 'function' && typeof $(deleteModalEl).modal === 'function') {
                $(deleteModalEl).modal('hide');
                return;
            }

            deleteModalEl.classList.remove('show');
            deleteModalEl.setAttribute('aria-hidden', 'true');
        };

        deleteModalEl.addEventListener('hidden.bs.modal', () => {
            pendingUrl = null;
        });

        document.querySelectorAll('[data-delete-trigger]').forEach((button) => {
            button.addEventListener('click', () => {
                pendingUrl = button.dataset.deleteUrl || null;

                if (dateHolder) {
                    dateHolder.textContent = button.dataset.deleteDate || '';
                }

                if (summaryHolder) {
                    summaryHolder.textContent = button.dataset.deleteSummary || '';
                }

                showModal();
            });
        });

        confirmButton?.addEventListener('click', () => {
            if (!pendingUrl) {
                return;
            }

            deleteForm.setAttribute('action', pendingUrl);
            hideModal();
            deleteForm.submit();
        });
    });
</script>
@endpush
