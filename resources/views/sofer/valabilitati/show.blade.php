@extends('layouts.app')

@section('content')
<div class="container py-4 py-md-5 sofer-valabilitati">
   <div class="sofer-header mb-3 mb-md-4">
        <div class="d-flex align-items-center justify-content-between gap-2">
            {{-- Back link (text hidden on very small screens) --}}
            <a
                href="{{ route('sofer.dashboard') }}"
                class="btn btn-link btn-sm text-decoration-none px-0 sofer-header__back"
            >
                <i class="fa-solid fa-arrow-left-long me-1"></i>
                <span class="d-none d-sm-inline">Înapoi la panou</span>
            </a>

            {{-- Title + label in the middle --}}
            <div class="flex-grow-1 mx-1 text-center">
                <h1 class="sofer-header__title fw-bold mb-0 text-truncate">
                    {{ $valabilitate->denumire }}
                </h1>
            </div>

            {{-- Primary CTA on the right --}}
            <a
                href="{{ route('sofer.valabilitati.curse.create', $valabilitate) }}"
                class="btn btn-success btn-sm flex-shrink-0 sofer-header__cta p-1"
            >
                <i class="fa-solid fa-plus"></i>
                <span class="d-none d-sm-inline">Adaugă cursă</span>
            </a>
        </div>

        {{-- Meta line: dates + număr auto --}}
        <div class="sofer-header__meta small text-muted mt-2 text-center gap-2">
            <span class="">
                <i class="fa-solid fa-car-side me-1"></i>
                {{ $valabilitate->numar_auto ?? 'Fără număr' }}
            </span>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Închide"></button>
        </div>
    @endif


    @if ($curse->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-5">
                <i class="fa-solid fa-route fa-2x text-primary mb-3"></i>
                <p class="fw-semibold mb-1">Nu există curse înregistrate</p>
                <p class="text-muted small mb-0">Începeți prin adăugarea primei curse pentru această valabilitate.</p>
            </div>
        </div>
    @else
        <div class="accordion cursa-accordion" id="curseAccordion">
            @foreach ($curse as $cursa)
                @php
                    $cursaId = 'cursa-' . $cursa->id;
                @endphp

                <article class="accordion-item cursa-card">
                    <h2 class="accordion-header" id="heading-{{ $cursaId }}">
                        <button
                            class="accordion-button collapsed py-3"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapse-{{ $cursaId }}"
                            aria-expanded="false"
                            aria-controls="collapse-{{ $cursaId }}"
                        >
                            <div class="w-100 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="fw-semibold text-dark small text-uppercase">
                                        Nr. cursă:
                                        <span class="text-body-secondary">{{ $cursa->nr_cursa ?? '—' }}</span>
                                    </span>
                                    <span class="text-primary">
                                        {{ $cursa->incarcare_localitate ?? '—' }}
                                        <span class="mx-1">→</span>
                                        {{ $cursa->descarcare_localitate ?? '—' }}
                                        <br>
                                        <small class="text-muted">
                                            {{ optional($cursa->data_cursa)->format('d.m.Y') ?? 'Fără dată' }}
                                        </small>

                                    </span>
                                </div>
                            </div>
                        </button>
                    </h2>

                    <div
                        id="collapse-{{ $cursaId }}"
                        class="accordion-collapse collapse"
                        aria-labelledby="heading-{{ $cursaId }}"
                        data-bs-parent="#curseAccordion"
                    >
                        <div class="accordion-body bg-white">
                            <div class="cursa-card__inner">

                                <div class="row g-3 align-items-start">
                                    <div class="col-12 col-lg-12">
                                        <div class="row g-2 small text-muted mb-2">
                                            <div class="col-12">
                                                <p class="fw-semibold text-uppercase text-dark small mb-1">Număr cursă</p>
                                                <p class="mb-0">
                                                    <span class="text-body-secondary">{{ $cursa->nr_cursa ?? '—' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                        {{-- Încărcare left, Descărcare right (always 50% / 50%) --}}
                                        <div class="row g-2 small text-muted cursa-card__locations">
                                            <div class="col-6">
                                                <p class="fw-semibold text-uppercase text-dark small mb-1">Încărcare</p>
                                                <p class="mb-0">
                                                    <span class="text-body-secondary">
                                                        {{ $cursa->incarcare_localitate ?? '—' }}<br>
                                                        {{ $cursa->incarcare_cod_postal ?? '—' }}<br>
                                                        {{ $cursa->incarcareTara?->nume ?? '—' }}
                                                    </span>
                                                </p>
                                            </div>

                                            <div class="col-6 text-end">
                                                <p class="fw-semibold text-uppercase text-dark small mb-1">Descărcare</p>
                                                <p class="mb-0">
                                                    <span class="text-body-secondary">
                                                        {{ $cursa->descarcare_localitate ?? '—' }}<br>
                                                        {{ $cursa->descarcare_cod_postal ?? '—' }}<br>
                                                        {{ $cursa->descarcareTara?->nume ?? '—' }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="row g-2 small text-muted mt-2">
                                            <div class="col-6">
                                                <p class="fw-semibold text-uppercase text-dark small mb-1">Km bord încărcare</p>
                                                <p class="mb-0">
                                                    <span class="text-body-secondary">
                                                        {{ $cursa->km_bord_incarcare ?? '—' }}
                                                    </span>
                                                </p>
                                            </div>

                                            <div class="col-6 text-end">
                                                <p class="fw-semibold text-uppercase text-dark small mb-1">Km bord descărcare</p>
                                                <p class="mb-0">
                                                    <span class="text-body-secondary">
                                                        {{ $cursa->km_bord_descarcare ?? '—' }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>

                                        @if ($cursa->observatii)
                                            <p class="text-muted small mt-3 mb-0">{{ $cursa->observatii }}</p>
                                        @endif
                                    </div>

                                    <div class="col-12 col-lg-12 mt-3 mt-lg-0">
                                        {{-- Edit left, Delete right --}}
                                        <div class="cursa-card__actions d-flex justify-content-between align-items-stretch gap-2">
                                            <a
                                                href="{{ route('sofer.valabilitati.curse.edit', [$valabilitate, $cursa]) }}"
                                                class="btn btn-outline-primary btn-sm"
                                            >
                                                <i class="fa-solid fa-pen"></i>
                                                <span class="d-none d-sm-inline ms-1">Editează</span>
                                            </a>

                                            <button
                                                type="button"
                                                class="btn btn-outline-danger btn-sm"
                                                data-delete-trigger
                                                data-delete-url="{{ route('sofer.valabilitati.curse.destroy', [$valabilitate, $cursa]) }}"
                                                data-delete-date="{{ optional($cursa->data_cursa)->format('d.m.Y H:i') ?? 'Fără dată' }}"
                                                data-delete-summary="{{ ($cursa->incarcare_localitate ?? '—') . ' → ' . ($cursa->descarcare_localitate ?? '—') }}"
                                            >
                                                <i class="fa-solid fa-trash-can"></i>
                                                <span class="d-none d-sm-inline ms-1">Șterge</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
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
    /* --- HEADER --- */
    .sofer-header__back,
    .sofer-header__cta {
        white-space: nowrap;
    }

    .sofer-header__title {
        font-size: 1.1rem;
    }

    .sofer-header__meta {
        font-size: 0.82rem;
    }

    /* --- ACCORDION / CURSE --- */
    .cursa-accordion .accordion-item.cursa-card {
        border-radius: 1rem;
        overflow: hidden;
        border: 0;
        box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .04);
    }

    .cursa-accordion .accordion-item + .accordion-item {
        margin-top: 0.75rem;
    }

    .cursa-accordion .accordion-button {
        background-color: #fff;
        font-size: 0.9rem;
    }

    .cursa-accordion .accordion-button:not(.collapsed) {
        box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .03);
    }

    .cursa-card__summary {
        border-radius: 0.5rem;
        background-color: #f8fafc;
        padding: 0.35rem 0.6rem;
    }

    .cursa-card__actions .btn {
        min-width: 120px;
    }

    /* --- MOBILE TWEAKS --- */
    @media (max-width: 575.98px) {
        .sofer-valabilitati .card-body {
            padding: 0.85rem 0.9rem;
        }

        .sofer-header__title {
            font-size: 1rem;
        }

        .sofer-header__meta {
            font-size: 0.78rem;
        }

        .sofer-valabilitati .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.4rem;
        }

        .cursa-card__locations p {
            font-size: 0.78rem;
        }

        .cursa-card__actions {
            width: 100%;
        }

        .cursa-card__actions .btn {
            width: 48%;
            min-width: 0;
            padding: 0.4rem 0.2rem;
            font-size: 0.78rem;
        }
    }

    @media (max-width: 400px) {
        .sofer-header__title {
            font-size: 0.95rem;
        }

        .sofer-valabilitati .card-body {
            padding: 0.7rem 0.7rem;
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
