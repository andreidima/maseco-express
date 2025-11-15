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
    @php
        $summaryData = $summary ?? [];

        $formatNullable = static function ($value) {
            return $value === null || $value === '' ? '—' : $value;
        };

        $formatNumeric = static function ($value) {
            if ($value === null || $value === '') {
                return '—';
            }

            if (is_numeric($value)) {
                return number_format((float) $value, 0, ',', '.');
            }

            return $value;
        };
    @endphp

    <div class="card border-0 shadow-sm mb-4 sofer-summary-card">
        <div class="card-body">
            <div class="sofer-summary-grid">
                <div class="sofer-summary-item">
                    <span class="sofer-summary-label">Număr auto</span>
                    <span class="sofer-summary-value">{{ $formatNullable($summaryData['vehicle'] ?? null) }}</span>
                </div>
                <div class="sofer-summary-item">
                    <span class="sofer-summary-label">Șofer</span>
                    <span class="sofer-summary-value">{{ $formatNullable($summaryData['driver'] ?? null) }}</span>
                </div>
                <div class="sofer-summary-item">
                    <span class="sofer-summary-label">Data plecare</span>
                    <span class="sofer-summary-value">{{ $formatNullable($summaryData['period_start'] ?? null) }}</span>
                </div>
                <div class="sofer-summary-item">
                    <span class="sofer-summary-label">Data sosire</span>
                    <span class="sofer-summary-value">{{ $formatNullable($summaryData['period_end'] ?? null) }}</span>
                </div>
                <div class="sofer-summary-item">
                    <span class="sofer-summary-label">Total zile</span>
                    <span class="sofer-summary-value">{{ $formatNumeric($summaryData['total_days'] ?? null) }}</span>
                </div>
                <div class="sofer-summary-item">
                    <span class="sofer-summary-label">Total curse</span>
                    <span class="sofer-summary-value">{{ $formatNumeric($summaryData['total_courses'] ?? null) }}</span>
                </div>
                <div class="sofer-summary-item">
                    <span class="sofer-summary-label">Total KM MAPS</span>
                    <span class="sofer-summary-value">{{ $formatNumeric($summaryData['km_maps'] ?? null) }}</span>
                </div>
                <div class="sofer-summary-item">
                    <span class="sofer-summary-label">Total KM BORD 2</span>
                    <span class="sofer-summary-value">{{ $formatNumeric($summaryData['km_bord_2'] ?? null) }}</span>
                </div>
                <div class="sofer-summary-item">
                    <span class="sofer-summary-label">Diferența totală KM (Bord - Maps)</span>
                    <span class="sofer-summary-value">{{ $formatNumeric($summaryData['km_difference'] ?? null) }}</span>
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
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 sofer-curse-table">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-uppercase small text-muted">#</th>
                                <th scope="col" class="text-uppercase small text-muted">Nr cursă</th>
                                <th scope="col" class="text-uppercase small text-muted">Cursa</th>
                                <th scope="col" class="text-uppercase small text-muted">Data Transport</th>
                                <th scope="col" class="text-uppercase small text-muted text-end">KM MAPS</th>
                                <th scope="col" class="text-uppercase small text-muted text-center">KM PLECARE / KM SOSIRE</th>
                                <th scope="col" class="text-uppercase small text-muted text-end">KM BORD 2</th>
                                <th scope="col" class="text-uppercase small text-muted text-center">Sumă încasată</th>
                                <th scope="col" class="text-uppercase small text-muted text-end">Diferența KM (Bord-Maps)</th>
                                <th scope="col" class="text-uppercase small text-muted text-end">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($curse as $cursa)
                                @php
                                    $incarcare = collect([
                                        $cursa->incarcare_localitate,
                                        $cursa->incarcare_cod_postal,
                                        $cursa->incarcareTara?->nume,
                                    ])->filter()->implode(', ');

                                    $descarcare = collect([
                                        $cursa->descarcare_localitate,
                                        $cursa->descarcare_cod_postal,
                                        $cursa->descarcareTara?->nume,
                                    ])->filter()->implode(', ');

                                    $transportDate = optional($cursa->data_cursa)->format('d.m.Y');
                                    $transportTime = optional($cursa->data_cursa)->format('H:i');

                                    $kmMaps = $cursa->km_maps;
                                    $kmPlecare = $cursa->km_bord_incarcare;
                                    $kmSosire = $cursa->km_bord_descarcare;
                                    $kmBord2 = ($kmPlecare !== null && $kmSosire !== null)
                                        ? (int) $kmSosire - (int) $kmPlecare
                                        : null;
                                    $kmDifference = ($kmBord2 !== null && $kmMaps !== null)
                                        ? $kmBord2 - (int) $kmMaps
                                        : null;

                                    $canMoveUp = ! $loop->first;
                                    $canMoveDown = ! $loop->last;
                                    $hasMultipleCurse = $loop->count > 1;
                                @endphp
                                <tr>
                                    <td class="text-muted fw-semibold">#{{ $cursa->nr_ordine }}</td>
                                    <td class="fw-semibold">{{ $cursa->nr_cursa ?? '—' }}</td>
                                    <td>
                                        <div class="sofer-curse-route">
                                            <span class="fw-semibold text-body">{{ $incarcare ?: '—' }}</span>
                                            <span class="sofer-curse-route__arrow">→</span>
                                            <span class="fw-semibold text-body">{{ $descarcare ?: '—' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $transportDate ?? '—' }}</div>
                                        @if ($transportTime)
                                            <div class="text-muted small">{{ $transportTime }}</div>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        {{ $kmMaps !== null ? number_format((float) $kmMaps, 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="text-center">
                                        <div class="sofer-curse-table__stack">
                                            <span>{{ $kmPlecare !== null ? number_format((float) $kmPlecare, 0, ',', '.') : '—' }}</span>
                                            <span class="text-muted">{{ $kmSosire !== null ? number_format((float) $kmSosire, 0, ',', '.') : '—' }}</span>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        {{ $kmBord2 !== null ? number_format((float) $kmBord2, 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="text-center">&nbsp;</td>
                                    <td class="text-end">
                                        {{ $kmDifference !== null ? number_format((float) $kmDifference, 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="text-end">
                                        <div class="sofer-curse-actions d-flex flex-wrap justify-content-end gap-2">
                                            @if ($hasMultipleCurse)
                                                <form
                                                    method="POST"
                                                    action="{{ route('sofer.valabilitati.curse.reorder', [$valabilitate, $cursa]) }}"
                                                    class="sofer-curse-action-form"
                                                >
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="direction" value="up">
                                                    <button
                                                        type="submit"
                                                        class="btn btn-outline-secondary btn-sm"
                                                        title="Mută cursa mai sus"
                                                        @disabled(! $canMoveUp)
                                                    >
                                                        <i class="fa-solid fa-arrow-up"></i>
                                                    </button>
                                                </form>

                                                <form
                                                    method="POST"
                                                    action="{{ route('sofer.valabilitati.curse.reorder', [$valabilitate, $cursa]) }}"
                                                    class="sofer-curse-action-form"
                                                >
                                                    @csrf
                                                    @method('PATCH')
                                                    <input type="hidden" name="direction" value="down">
                                                    <button
                                                        type="submit"
                                                        class="btn btn-outline-secondary btn-sm"
                                                        title="Mută cursa mai jos"
                                                        @disabled(! $canMoveDown)
                                                    >
                                                        <i class="fa-solid fa-arrow-down"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            <a
                                                href="{{ route('sofer.valabilitati.curse.edit', [$valabilitate, $cursa]) }}"
                                                class="btn btn-outline-primary btn-sm"
                                                title="Editează cursa"
                                            >
                                                <i class="fa-solid fa-pen"></i>
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
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
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

    /* --- SUMMARY --- */
    .sofer-summary-card .card-body {
        padding: 1.5rem;
    }

    .sofer-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1rem;
    }

    .sofer-summary-item {
        background-color: #f8fafc;
        border-radius: 0.75rem;
        padding: 0.9rem 1.1rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .sofer-summary-label {
        font-size: 0.72rem;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.35rem;
    }

    .sofer-summary-value {
        font-size: 1.05rem;
        font-weight: 600;
        color: #1f2937;
    }

    /* --- TABLE --- */
    .sofer-curse-table thead th {
        font-size: 0.7rem;
        letter-spacing: 0.06em;
        border-bottom: 0;
        background-color: #f8fafc;
        color: #64748b;
    }

    .sofer-curse-table tbody td {
        padding: 1rem 1.25rem;
        font-size: 0.92rem;
        color: #1f2937;
        vertical-align: middle;
    }

    .sofer-curse-table tbody td:first-child {
        color: #64748b;
        font-size: 0.85rem;
    }

    .sofer-curse-route {
        display: flex;
        flex-direction: column;
        gap: 0.15rem;
    }

    .sofer-curse-route__arrow {
        color: #9ca3af;
        font-size: 0.8rem;
        text-align: center;
    }

    .sofer-curse-table__stack span {
        display: block;
    }

    .sofer-curse-table__stack span + span {
        margin-top: 0.35rem;
    }

    .sofer-curse-table__stack span:first-child {
        font-weight: 600;
    }

    .sofer-curse-actions {
        gap: 0.4rem !important;
    }

    .sofer-curse-actions .btn {
        min-width: 0;
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }

    .sofer-curse-actions .btn i {
        font-size: 0.85rem;
    }

    .sofer-curse-action-form {
        margin: 0;
    }

    /* --- MOBILE TWEAKS --- */
    @media (max-width: 991.98px) {
        .sofer-summary-card .card-body {
            padding: 1.25rem;
        }

        .sofer-curse-table tbody td {
            padding: 0.85rem 1rem;
        }
    }

    @media (max-width: 575.98px) {
        .sofer-header__title {
            font-size: 1rem;
        }

        .sofer-header__meta {
            font-size: 0.78rem;
        }

        .sofer-summary-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 0.75rem;
        }

        .sofer-summary-item {
            padding: 0.75rem 0.9rem;
        }

        .sofer-summary-value {
            font-size: 0.95rem;
        }

        .sofer-curse-table tbody td {
            font-size: 0.85rem;
        }

        .sofer-curse-actions {
            justify-content: flex-start;
        }

        .sofer-curse-actions .btn {
            width: 32px;
            height: 32px;
        }
    }

    @media (max-width: 400px) {
        .sofer-summary-grid {
            grid-template-columns: 1fr;
        }

        .sofer-curse-table tbody td {
            padding: 0.75rem 0.8rem;
        }
    }
</style>
@endpush




@push('page-scripts')
<script>
    (function () {
        var RELOAD_STORAGE_KEY = 'soferValabilitati:forcedReload';

        var resolveNavigationType = function () {
            var perf = window.performance;

            if (!perf) {
                return null;
            }

            if (typeof perf.getEntriesByType === 'function') {
                var entries = perf.getEntriesByType('navigation');

                if (entries && entries.length > 0) {
                    return entries[0].type || null;
                }
            }

            if (perf.navigation && typeof perf.navigation.type === 'number') {
                var navigation = perf.navigation;

                if (
                    typeof navigation.TYPE_BACK_FORWARD === 'number' &&
                    navigation.type === navigation.TYPE_BACK_FORWARD
                ) {
                    return 'back_forward';
                }
            }

            return null;
        };

        var performHardReload = function () {
            try {
                sessionStorage.setItem(RELOAD_STORAGE_KEY, '1');
            } catch (error) {
                // sessionStorage might be unavailable (Safari private mode, etc.).
            }

            var currentUrl = window.location.href;
            var withoutHash = currentUrl.split('#')[0];

            window.location.replace(withoutHash);
        };

        var alreadyForcedReload = function () {
            try {
                return sessionStorage.getItem(RELOAD_STORAGE_KEY) === '1';
            } catch (error) {
                return false;
            }
        };

        var clearForcedReloadFlag = function () {
            try {
                sessionStorage.removeItem(RELOAD_STORAGE_KEY);
            } catch (error) {
                // no-op
            }
        };

        window.addEventListener('pageshow', function (event) {
            var isPersisted = event.persisted === true;
            var navigationType = resolveNavigationType();
            var cameFromHistory = navigationType === 'back_forward';

            if (isPersisted || cameFromHistory) {
                if (!alreadyForcedReload()) {
                    performHardReload();
                } else {
                    clearForcedReloadFlag();
                }

                return;
            }

            clearForcedReloadFlag();
        });

        window.addEventListener('pagehide', function () {
            clearForcedReloadFlag();
        });
    })();

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
