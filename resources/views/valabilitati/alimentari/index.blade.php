@extends('layouts.app')

@section('content')
    @php
        $formatNumber = function ($value, int $decimals = 2): string {
            if ($value === null || $value === '') {
                return '';
            }

            $trimmed = rtrim(rtrim(number_format((float) $value, $decimals, '.', ''), '0'), '.');

            return $trimmed === '-0' ? '0' : $trimmed;
        };
    @endphp

    <style>
        .curse-summary-table,
        .alimentari-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }

        .alimentari-table th,
        .alimentari-table td,
        .curse-summary-table th,
        .curse-summary-table td {
            border: 1px solid #000000ff;
            padding: 0.45rem 0.5rem;
            vertical-align: middle;
        }

        .curse-summary-table th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        .alimentari-table thead th {
            background-color: #6a6ba0;
            color: #ffffff;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.02em;
        }

        .alimentari-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .alimentari-table .actions-column {
            width: 160px;
            white-space: nowrap;
        }

        .alimentari-actions {
            gap: 0.5rem;
        }

        .alimentari-form-label {
            font-weight: 600;
        }

        .alimentare-edit {
            display: none;
        }

        tr[data-editing="true"] .alimentare-edit {
            display: block;
        }

        tr[data-editing="true"] .alimentare-display {
            display: none;
        }

        tr[data-editing="true"] {
            background-color: #fff9e6;
        }
    </style>

    @php
        $curseRoute = route('valabilitati.curse.index', $valabilitate);
        $grupuriRoute = route('valabilitati.grupuri.index', $valabilitate);
        $alimentariRoute = route('valabilitati.alimentari.index', $valabilitate);
    @endphp

    <div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center text-center text-lg-start" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                <span class="badge culoare1 fs-5">
                    <span class="d-inline-flex flex-column align-items-start gap-1 lh-1">
                        <span>
                            <i class="fa-solid fa-route me-1"></i>Valabilitate
                            /
                            {{ $valabilitate->divizie->nume ?? 'Fără divizie' }}
                        </span>
                    </span>
                </span>
            </div>
            <div class="col-12 col-lg-4 my-2 my-lg-0">
                <div class="d-flex justify-content-center gap-2">
                    <a
                        href="{{ $curseRoute }}"
                        class="btn btn-sm btn-outline-primary border border-dark rounded-3"
                    >
                        <i class="fa-solid fa-truck-fast me-1"></i>Curse
                    </a>
                    <a
                        href="{{ $grupuriRoute }}"
                        class="btn btn-sm btn-outline-primary border border-dark rounded-3"
                    >
                        <i class="fa-solid fa-layer-group me-1"></i>Grupuri
                    </a>
                    <a
                        href="{{ $alimentariRoute }}"
                        class="btn btn-sm btn-primary text-white border border-dark rounded-3"
                    >
                        <i class="fa-solid fa-gas-pump me-1"></i>Alimentari
                    </a>
                </div>
            </div>
            <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="d-flex align-items-stretch align-items-lg-end gap-2 flex-wrap justify-content-center justify-content-lg-end">
                    <button
                        type="button"
                        class="btn btn-sm btn-success text-white border border-dark rounded-3"
                        data-bs-toggle="modal"
                        data-bs-target="#alimentareCreateModal"
                    >
                        <i class="fas fa-plus-square text-white me-1"></i>Adaugă alimentare
                    </button>
                    <a
                        href="{{ $backUrl }}"
                        class="btn btn-sm btn-outline-secondary border border-dark rounded-3"
                    >
                        <i class="fa-solid fa-list me-1"></i>Înapoi la valabilități
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body px-0 py-3">
            @include('errors')

            <div id="alimentari-inline-feedback" class="px-3 mb-2"></div>
            <div id="alimentari-summary" class="px-3 mb-3">
                @include('valabilitati.curse.partials.summary', [
                    'valabilitate' => $valabilitate,
                    'summary' => $summary,
                    'showGroupSummary' => false,
                    'isFlashDivision' => optional($valabilitate->divizie)->id === 1,
                ])
            </div>

            <div class="px-3 col-12 col-lg-8 mx-auto">
                <div class="table-responsive">
                    <table class="table table-sm align-middle culoare1">
                        <thead>
                            @php
                                $totalLitri = $alimentariMetrics['totalLitri'];
                                $averagePret = $alimentariMetrics['averagePret'];
                                $totalPret = $alimentariMetrics['totalPret'];
                                $consum = $alimentariMetrics['consum'];
                            @endphp
                            <tr class="bg-secondary bg-opacity-25">
                                <th class="text-center">TOTAL LITRI</th>
                                <th class="text-center">MEDIE PREȚ</th>
                                <th class="text-center">TOTAL PREȚ</th>
                                <th class="text-center">CONSUM</th>
                            </tr>
                            <tr class="bg-secondary bg-opacity-25">
                                <th class="text-center" id="alimentari-total-litri">{{ $formatNumber($totalLitri, 2) }}</th>
                                <th class="text-center">
                                    <span id="alimentari-average-pret">
                                        {{ $averagePret !== null ? $formatNumber($averagePret, 4) : '—' }}
                                    </span>
                                </th>
                                <th class="text-center" id="alimentari-total-pret">{{ $formatNumber($totalPret, 4) }}</th>
                                <th class="text-center">
                                    <span id="alimentari-consum">
                                        {{ $consum !== null ? $formatNumber($consum, 2) : '—' }}
                                    </span>
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm alimentari-table align-middle">
                        <thead>
                            <tr>
                                <th>Dată / oră alimentare</th>
                                <th class="text-end">Litrii</th>
                                <th class="text-end">Preț / litru</th>
                                <th class="text-end">Total preț</th>
                                <th class="actions-column text-center">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody
                            data-alimentari-body
                            data-store-url="{{ route('valabilitati.alimentari.store', $valabilitate) }}"
                        >
                            <tr data-new-alimentare-row>
                                <td>
                                    <input
                                        type="datetime-local"
                                        name="data_ora_alimentare"
                                        class="form-control form-control-sm"
                                    >
                                    <div class="invalid-feedback" data-error-for="data_ora_alimentare"></div>
                                </td>
                                <td class="text-end">
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        name="litrii"
                                        class="form-control form-control-sm text-end"
                                        data-decimals="2"
                                    >
                                    <div class="invalid-feedback" data-error-for="litrii"></div>
                                </td>
                                <td class="text-end">
                                    <input
                                        type="number"
                                        step="0.0001"
                                        min="0"
                                        name="pret_pe_litru"
                                        class="form-control form-control-sm text-end"
                                        data-decimals="4"
                                    >
                                    <div class="invalid-feedback" data-error-for="pret_pe_litru"></div>
                                </td>
                                <td class="text-end">
                                    <input
                                        type="number"
                                        step="0.0001"
                                        min="0"
                                        name="total_pret"
                                        class="form-control form-control-sm text-end"
                                        data-decimals="4"
                                    >
                                    <div class="invalid-feedback" data-error-for="total_pret"></div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">
                                        <button class="btn btn-sm btn-success border border-dark" type="button" data-new-save>
                                            <i class="fa-solid fa-plus me-1"></i>Adaugă
                                        </button>
                                        <button class="btn btn-sm btn-outline-secondary border border-dark" type="button" data-new-reset>
                                            <i class="fa-solid fa-rotate-left me-1"></i>Reset
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @forelse ($alimentari as $alimentare)
                                <tr
                                    data-alimentare-row
                                    data-alimentare-id="{{ $alimentare->id }}"
                                    data-update-url="{{ route('valabilitati.alimentari.update', [$valabilitate, $alimentare]) }}"
                                    data-data-ora="{{ optional($alimentare->data_ora_alimentare)->format('Y-m-d\\TH:i') }}"
                                    data-litrii="{{ $alimentare->litrii }}"
                                    data-pret-pe-litru="{{ $alimentare->pret_pe_litru }}"
                                    data-total-pret="{{ $alimentare->total_pret }}"
                                    data-observatii="{{ e($alimentare->observatii ?? '') }}"
                                    data-editing="false"
                                >
                                    <td class="fw-semibold">
                                        <div class="alimentare-display" data-display="data_ora_alimentare">
                                            {{ optional($alimentare->data_ora_alimentare)->format('d.m.Y H:i') }}
                                        </div>
                                        <div class="alimentare-edit">
                                            <input
                                                type="datetime-local"
                                                name="data_ora_alimentare"
                                                class="form-control form-control-sm"
                                                value="{{ optional($alimentare->data_ora_alimentare)->format('Y-m-d\\TH:i') }}"
                                                required
                                            >
                                            <div class="invalid-feedback" data-error-for="data_ora_alimentare"></div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="alimentare-display" data-display="litrii">
                                            {{ $formatNumber($alimentare->litrii, 2) }}
                                        </div>
                                        <div class="alimentare-edit">
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                name="litrii"
                                                class="form-control form-control-sm text-end"
                                                value="{{ $formatNumber($alimentare->litrii, 2) }}"
                                                data-decimals="2"
                                                required
                                            >
                                            <div class="invalid-feedback" data-error-for="litrii"></div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="alimentare-display" data-display="pret_pe_litru">
                                            {{ $formatNumber($alimentare->pret_pe_litru, 4) }}
                                        </div>
                                        <div class="alimentare-edit">
                                            <input
                                                type="number"
                                                step="0.0001"
                                                min="0"
                                                name="pret_pe_litru"
                                                class="form-control form-control-sm text-end"
                                                value="{{ $formatNumber($alimentare->pret_pe_litru, 4) }}"
                                                data-decimals="4"
                                                required
                                            >
                                            <div class="invalid-feedback" data-error-for="pret_pe_litru"></div>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <div class="alimentare-display" data-display="total_pret">
                                            {{ $formatNumber($alimentare->total_pret, 4) }}
                                        </div>
                                        <div class="alimentare-edit">
                                            <input
                                                type="number"
                                                step="0.0001"
                                                min="0"
                                                name="total_pret"
                                                class="form-control form-control-sm text-end"
                                                value="{{ $formatNumber($alimentare->total_pret, 4) }}"
                                                data-decimals="4"
                                                required
                                            >
                                            <div class="invalid-feedback" data-error-for="total_pret"></div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center flex-wrap alimentari-actions">
                                            <div data-view-actions class="d-flex gap-2">
                                                <button
                                                    class="btn btn-sm btn-outline-primary border border-dark"
                                                    type="button"
                                                    data-inline-edit
                                                >
                                                    <i class="fa-solid fa-pen-to-square me-1"></i>
                                                </button>
                                                <form
                                                    method="POST"
                                                    action="{{ route('valabilitati.alimentari.destroy', [$valabilitate, $alimentare]) }}"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Sigur ștergi această alimentare?');"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border border-dark">
                                                        <i class="fa-solid fa-trash me-1"></i>
                                                    </button>
                                                </form>
                                            </div>
                                            <div data-edit-actions class="d-none d-flex gap-2">
                                                <button class="btn btn-sm btn-primary border border-dark" type="button" data-inline-save>
                                                    <i class="fa-solid fa-floppy-disk me-1"></i>Salvează
                                                </button>
                                                <button class="btn btn-sm btn-secondary border border-dark" type="button" data-inline-cancel>
                                                    <i class="fa-solid fa-xmark me-1"></i>Anulează
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr data-empty-row>
                                    <td colspan="5" class="text-center py-4">Nu există alimentări salvate.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $alimentari->links() }}
                </div>
            </div>

            {{-- Create modal --}}
            <div
                class="modal fade"
                id="alimentareCreateModal"
                tabindex="-1"
                aria-labelledby="alimentareCreateModalLabel"
                aria-hidden="true"
            >
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="alimentareCreateModalLabel">Adaugă alimentare</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="{{ route('valabilitati.alimentari.store', $valabilitate) }}">
                            @csrf
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-12 col-lg-3">
                                        <label class="form-label alimentari-form-label" for="data_ora_alimentare">Dată / oră alimentare</label>
                                        <input
                                            type="datetime-local"
                                            name="data_ora_alimentare"
                                            id="data_ora_alimentare"
                                            class="form-control"
                                            value="{{ old('data_ora_alimentare') }}"
                                            required
                                        >
                                    </div>
                                    <div class="col-12 col-lg-3">
                                        <label class="form-label alimentari-form-label" for="litrii">Litrii</label>
                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name="litrii"
                                            id="litrii"
                                            class="form-control"
                                            value="{{ $formatNumber(old('litrii'), 2) }}"
                                            required
                                        >
                                    </div>
                                    <div class="col-12 col-lg-3">
                                        <label class="form-label alimentari-form-label" for="pret_pe_litru">Preț / litru</label>
                                        <input
                                            type="number"
                                            step="0.0001"
                                            min="0"
                                            name="pret_pe_litru"
                                            id="pret_pe_litru"
                                            class="form-control"
                                            value="{{ $formatNumber(old('pret_pe_litru'), 4) }}"
                                            required
                                        >
                                    </div>
                                    <div class="col-12 col-lg-3">
                                        <label class="form-label alimentari-form-label" for="total_pret">Total preț</label>
                                        <input
                                            type="number"
                                            step="0.0001"
                                            min="0"
                                            name="total_pret"
                                            id="total_pret"
                                            class="form-control"
                                            value="{{ $formatNumber(old('total_pret'), 4) }}"
                                            required
                                        >
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label alimentari-form-label" for="observatii">Observații</label>
                                        <textarea
                                            name="observatii"
                                            id="observatii"
                                            class="form-control"
                                            rows="2"
                                            placeholder="Opțional"
                                        >{{ old('observatii') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Renunță</button>
                                <button type="submit" class="btn btn-success text-white border border-dark">
                                    <i class="fas fa-plus-square text-white me-1"></i>Salvează alimentarea
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Edit modals --}}
            @foreach ($alimentari as $alimentare)
                @php
                    $shouldPrefillFromOld = (int) old('alimentare_id') === $alimentare->id;
                @endphp
                <div
                    class="modal fade"
                    id="alimentareEditModal-{{ $alimentare->id }}"
                    tabindex="-1"
                    aria-labelledby="alimentareEditModalLabel-{{ $alimentare->id }}"
                    aria-hidden="true"
                >
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="alimentareEditModalLabel-{{ $alimentare->id }}">Editează alimentarea</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form method="POST" action="{{ route('valabilitati.alimentari.update', [$valabilitate, $alimentare]) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="alimentare_id" value="{{ $alimentare->id }}">
                                <div class="modal-body">
                                    <div class="row g-3">
                                        <div class="col-12 col-lg-3">
                                            <label class="form-label alimentari-form-label" for="data_ora_alimentare_{{ $alimentare->id }}">Dată / oră alimentare</label>
                                            <input
                                                type="datetime-local"
                                                name="data_ora_alimentare"
                                                id="data_ora_alimentare_{{ $alimentare->id }}"
                                                class="form-control"
                                                value="{{ $shouldPrefillFromOld ? old('data_ora_alimentare') : optional($alimentare->data_ora_alimentare)->format('Y-m-d\\TH:i') }}"
                                                required
                                            >
                                        </div>
                                        <div class="col-12 col-lg-3">
                                            <label class="form-label alimentari-form-label" for="litrii_{{ $alimentare->id }}">Litrii</label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                name="litrii"
                                                id="litrii_{{ $alimentare->id }}"
                                                class="form-control"
                                                value="{{ $shouldPrefillFromOld ? $formatNumber(old('litrii'), 2) : $formatNumber($alimentare->litrii, 2) }}"
                                                required
                                            >
                                        </div>
                                        <div class="col-12 col-lg-3">
                                            <label class="form-label alimentari-form-label" for="pret_pe_litru_{{ $alimentare->id }}">Preț / litru</label>
                                            <input
                                                type="number"
                                                step="0.0001"
                                                min="0"
                                                name="pret_pe_litru"
                                                id="pret_pe_litru_{{ $alimentare->id }}"
                                                class="form-control"
                                                value="{{ $shouldPrefillFromOld ? $formatNumber(old('pret_pe_litru'), 4) : $formatNumber($alimentare->pret_pe_litru, 4) }}"
                                                required
                                            >
                                        </div>
                                        <div class="col-12 col-lg-3">
                                            <label class="form-label alimentari-form-label" for="total_pret_{{ $alimentare->id }}">Total preț</label>
                                            <input
                                                type="number"
                                                step="0.0001"
                                                min="0"
                                                name="total_pret"
                                                id="total_pret_{{ $alimentare->id }}"
                                                class="form-control"
                                                value="{{ $shouldPrefillFromOld ? $formatNumber(old('total_pret'), 4) : $formatNumber($alimentare->total_pret, 4) }}"
                                                required
                                            >
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label alimentari-form-label" for="observatii_{{ $alimentare->id }}">Observații</label>
                                            <textarea
                                                name="observatii"
                                                id="observatii_{{ $alimentare->id }}"
                                                class="form-control"
                                                rows="2"
                                                placeholder="Opțional"
                                            >{{ $shouldPrefillFromOld ? old('observatii') : $alimentare->observatii }}</textarea>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Renunță</button>
                                    <button type="submit" class="btn btn-primary border border-dark">
                                        <i class="fa-solid fa-floppy-disk me-1"></i>Actualizează
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

@push('page-scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableBody = document.querySelector('[data-alimentari-body]');
            const rows = Array.from(document.querySelectorAll('[data-alimentare-row]'));
            const newRow = tableBody ? tableBody.querySelector('[data-new-alimentare-row]') : null;
            const storeUrl = tableBody ? tableBody.dataset.storeUrl || '' : '';

            const csrfToken = document.head.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const feedbackContainer = document.getElementById('alimentari-inline-feedback');
            const summaryEls = {
                totalLitri: document.getElementById('alimentari-total-litri'),
                averagePret: document.getElementById('alimentari-average-pret'),
                totalPret: document.getElementById('alimentari-total-pret'),
                consum: document.getElementById('alimentari-consum'),
            };

            const formatNumber = (value, decimals = 2) => {
                if (value === null || value === undefined || value === '') {
                    return '';
                }

                const numberValue = Number(value);
                if (Number.isNaN(numberValue)) {
                    return '';
                }

                const formatted = numberValue.toFixed(decimals);
                return formatted.replace(/\.0+$/, '').replace(/\.$/, '') || '0';
            };

            const renderFeedback = (message, type = 'success') => {
                if (!feedbackContainer || !message) {
                    return;
                }

                const alert = document.createElement('div');
                alert.className = `alert alert-${type} alert-dismissible fade show`;
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

            const clearErrors = (row) => {
                row.querySelectorAll('[data-error-for]').forEach(el => {
                    el.textContent = '';
                });
                row.querySelectorAll('input').forEach(input => input.classList.remove('is-invalid'));
            };

            const fieldDecimals = {
                litrii: 2,
                pret_pe_litru: 4,
                total_pret: 4,
            };

            const setEditing = (row, editing) => {
                row.dataset.editing = editing ? 'true' : 'false';
                const viewActions = row.querySelector('[data-view-actions]');
                const editActions = row.querySelector('[data-edit-actions]');
                if (viewActions) {
                    viewActions.classList.toggle('d-none', editing);
                }
                if (editActions) {
                    editActions.classList.toggle('d-none', !editing);
                }
            };

            const fillInputsFromDataset = (row) => {
                const data = row.dataset;
                const assignments = {
                    data_ora_alimentare: data.dataOra || '',
                    litrii: data.litrii ?? '',
                    pret_pe_litru: data.pretPeLitru ?? '',
                    total_pret: data.totalPret ?? '',
                };

                Object.entries(assignments).forEach(([name, value]) => {
                    const input = row.querySelector(`input[name="${name}"]`);
                    if (input) {
                        if (input.type === 'datetime-local') {
                            input.value = value;
                            return;
                        }

                        const decimals = Number(input.dataset.decimals ?? fieldDecimals[name] ?? 2);
                        input.value = formatNumber(value, decimals);
                    }
                });

                clearErrors(row);
            };

            const updateRowDataset = (row, payload) => {
                row.dataset.dataOra = payload.data_ora_alimentare ?? '';
                row.dataset.litrii = payload.litrii !== undefined
                    ? formatNumber(payload.litrii, fieldDecimals.litrii)
                    : '';
                row.dataset.pretPeLitru = payload.pret_pe_litru !== undefined
                    ? formatNumber(payload.pret_pe_litru, fieldDecimals.pret_pe_litru)
                    : '';
                row.dataset.totalPret = payload.total_pret !== undefined
                    ? formatNumber(payload.total_pret, fieldDecimals.total_pret)
                    : '';
                row.dataset.observatii = payload.observatii ?? '';
            };

            const updateDisplay = (row, data) => {
                const mappings = [
                    { selector: '[data-display="data_ora_alimentare"]', value: data.data_ora_display || '' },
                    { selector: '[data-display="litrii"]', value: formatNumber(data.litrii, 2) },
                    { selector: '[data-display="pret_pe_litru"]', value: formatNumber(data.pret_pe_litru, 4) },
                    { selector: '[data-display="total_pret"]', value: formatNumber(data.total_pret, 4) },
                ];

                mappings.forEach(({ selector, value }) => {
                    const el = row.querySelector(selector);
                    if (el) {
                        el.textContent = value || '—';
                    }
                });
            };

            const applyMetrics = (metrics = {}) => {
                const { totalLitri = null, averagePret = null, totalPret = null, consum = null } = metrics;

                if (summaryEls.totalLitri) {
                    summaryEls.totalLitri.textContent = formatNumber(totalLitri, 2) || '—';
                }
                if (summaryEls.averagePret) {
                    summaryEls.averagePret.textContent = averagePret !== null ? formatNumber(averagePret, 4) : '—';
                }
                if (summaryEls.totalPret) {
                    summaryEls.totalPret.textContent = formatNumber(totalPret, 4) || '—';
                }
                if (summaryEls.consum) {
                    summaryEls.consum.textContent = consum !== null ? formatNumber(consum, 2) : '—';
                }
            };

            const gatherPayload = (row) => {
                const dataOraInput = row.querySelector('input[name="data_ora_alimentare"]');
                const litriiInput = row.querySelector('input[name="litrii"]');
                const pretInput = row.querySelector('input[name="pret_pe_litru"]');
                const totalInput = row.querySelector('input[name="total_pret"]');

                const normalize = (input, fallbackDecimals = 2) => {
                    if (!input) {
                        return '';
                    }
                    const decimals = Number(input.dataset.decimals ?? fallbackDecimals);
                    return formatNumber(input.value, decimals);
                };

                return {
                    data_ora_alimentare: dataOraInput?.value || '',
                    litrii: normalize(litriiInput, fieldDecimals.litrii),
                    pret_pe_litru: normalize(pretInput, fieldDecimals.pret_pe_litru),
                    total_pret: normalize(totalInput, fieldDecimals.total_pret),
                    observatii: row.dataset.observatii || null,
                    alimentare_id: row.dataset.alimentareId,
                };
            };

            const showFieldErrors = (row, errors = {}) => {
                Object.entries(errors).forEach(([name, messages]) => {
                    const message = Array.isArray(messages) ? messages[0] : messages;
                    const feedback = row.querySelector(`[data-error-for="${name}"]`);
                    const input = row.querySelector(`[name="${name}"]`);
                    if (feedback) {
                        feedback.textContent = message || '';
                    }
                    if (input && message) {
                        input.classList.add('is-invalid');
                    }
                });
            };

            const saveRow = (row) => {
                const updateUrl = row.dataset.updateUrl || '';
                if (!updateUrl) {
                    return;
                }

                clearErrors(row);
                const payload = gatherPayload(row);

                row.classList.add('opacity-75');
                row.querySelectorAll('button').forEach(btn => btn.disabled = true);

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
                                showFieldErrors(row, data.errors || {});
                                throw new Error('validation');
                            }

                            throw new Error(data.message || `Eroare (${response.status})`);
                        }

                        return data;
                    })
                    .then(data => {
                        if (data.alimentare) {
                            updateRowDataset(row, data.alimentare);
                            updateDisplay(row, data.alimentare);
                        }

                        if (data.metrics) {
                            applyMetrics(data.metrics);
                        }

                        setEditing(row, false);
                        renderFeedback(data.message || 'Alimentarea a fost actualizată.');
                    })
                    .catch(error => {
                        if (error.message === 'validation') {
                            renderFeedback('Verifică valorile introduse.', 'danger');
                            return;
                        }

                        console.error('Alimentare inline save error', error);
                        renderFeedback('Nu am putut salva modificarea. Reîncearcă.', 'danger');
                    })
                    .finally(() => {
                        row.classList.remove('opacity-75');
                        row.querySelectorAll('button').forEach(btn => btn.disabled = false);
                    });
            };

            const attachRowListeners = (row) => {
                const editBtn = row.querySelector('[data-inline-edit]');
                const saveBtn = row.querySelector('[data-inline-save]');
                const cancelBtn = row.querySelector('[data-inline-cancel]');

                if (editBtn) {
                    editBtn.addEventListener('click', () => {
                        fillInputsFromDataset(row);
                        setEditing(row, true);
                        const firstInput = row.querySelector('input');
                        if (firstInput) {
                            firstInput.focus();
                        }
                    });
                }

                if (cancelBtn) {
                    cancelBtn.addEventListener('click', () => {
                        fillInputsFromDataset(row);
                        setEditing(row, false);
                    });
                }

                if (saveBtn) {
                    saveBtn.addEventListener('click', () => saveRow(row));
                }

                row.querySelectorAll('input').forEach(input => {
                    input.addEventListener('keydown', event => {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            saveRow(row);
                        }
                        if (event.key === 'Escape') {
                            event.preventDefault();
                            fillInputsFromDataset(row);
                            setEditing(row, false);
                        }
                    });

                    input.addEventListener('blur', () => {
                        if (input.type !== 'number') {
                            return;
                        }
                        const decimals = Number(input.dataset.decimals ?? 2);
                        input.value = formatNumber(input.value, decimals);
                    });
                });
            };

            rows.forEach(attachRowListeners);

            const removeEmptyRow = () => {
                if (!tableBody) {
                    return;
                }
                const emptyRow = tableBody.querySelector('[data-empty-row]');
                if (emptyRow) {
                    emptyRow.remove();
                }
            };

            const resetNewRow = () => {
                if (!newRow) {
                    return;
                }
                newRow.querySelectorAll('input').forEach(input => {
                    input.value = '';
                    input.classList.remove('is-invalid');
                });
                newRow.querySelectorAll('[data-error-for]').forEach(el => (el.textContent = ''));
            };

            const buildRowElement = (item) => {
                const row = document.createElement('tr');
                row.dataset.alimentareRow = '';
                row.dataset.alimentareId = item.id;
                row.dataset.updateUrl = item.update_url || '';
                row.dataset.dataOra = item.data_ora_alimentare || '';
                row.dataset.litrii = item.litrii ?? '';
                row.dataset.pretPeLitru = item.pret_pe_litru ?? '';
                row.dataset.totalPret = item.total_pret ?? '';
                row.dataset.observatii = item.observatii ?? '';
                row.dataset.editing = 'false';

                const cells = [
                    {
                        name: 'data_ora_alimentare',
                        display: item.data_ora_display || '',
                        inputType: 'datetime-local',
                    },
                    {
                        name: 'litrii',
                        display: formatNumber(item.litrii, fieldDecimals.litrii),
                        inputType: 'number',
                        step: '0.01',
                        min: '0',
                        decimals: fieldDecimals.litrii,
                    },
                    {
                        name: 'pret_pe_litru',
                        display: formatNumber(item.pret_pe_litru, fieldDecimals.pret_pe_litru),
                        inputType: 'number',
                        step: '0.0001',
                        min: '0',
                        decimals: fieldDecimals.pret_pe_litru,
                    },
                    {
                        name: 'total_pret',
                        display: formatNumber(item.total_pret, fieldDecimals.total_pret),
                        inputType: 'number',
                        step: '0.0001',
                        min: '0',
                        decimals: fieldDecimals.total_pret,
                    },
                ];

                cells.forEach(({ name, display, inputType, step, min, decimals }) => {
                    const td = document.createElement('td');
                    if (name !== 'data_ora_alimentare') {
                        td.classList.add('text-end');
                    }

                    const displayDiv = document.createElement('div');
                    displayDiv.className = 'alimentare-display';
                    displayDiv.dataset.display = name;
                    displayDiv.textContent = display || '—';

                    const editDiv = document.createElement('div');
                    editDiv.className = 'alimentare-edit';
                    const input = document.createElement('input');
                    input.type = inputType;
                    input.name = name;
                    input.className = 'form-control form-control-sm';
                    if (name !== 'data_ora_alimentare') {
                        input.classList.add('text-end');
                    }
                    if (step) input.step = step;
                    if (min) input.min = min;
                    if (decimals !== undefined) {
                        input.dataset.decimals = String(decimals);
                        input.value = formatNumber(item[name] ?? '', decimals);
                    } else {
                        input.value = item.data_ora_alimentare || '';
                    }

                    editDiv.appendChild(input);
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    feedback.dataset.errorFor = name;
                    editDiv.appendChild(feedback);

                    td.appendChild(displayDiv);
                    td.appendChild(editDiv);
                    row.appendChild(td);
                });

                const actionsTd = document.createElement('td');
                actionsTd.className = 'text-center';
                const wrapper = document.createElement('div');
                wrapper.className = 'd-flex justify-content-center flex-wrap alimentari-actions';

                const viewActions = document.createElement('div');
                viewActions.dataset.viewActions = '';
                viewActions.className = 'd-flex gap-2';
                const editButton = document.createElement('button');
                editButton.className = 'btn btn-sm btn-outline-primary border border-dark';
                editButton.type = 'button';
                editButton.dataset.inlineEdit = '';
                editButton.innerHTML = '<i class="fa-solid fa-pen-to-square me-1"></i>';
                viewActions.appendChild(editButton);

                const deleteForm = document.createElement('form');
                deleteForm.method = 'POST';
                deleteForm.action = item.delete_url || '';
                deleteForm.className = 'd-inline';
                deleteForm.onsubmit = () => confirm('Sigur ștergi această alimentare?');
                deleteForm.innerHTML = `
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-sm btn-outline-danger border border-dark">
                        <i class="fa-solid fa-trash me-1"></i>
                    </button>
                `;
                viewActions.appendChild(deleteForm);

                const editActions = document.createElement('div');
                editActions.dataset.editActions = '';
                editActions.className = 'd-none d-flex gap-2';
                const saveButton = document.createElement('button');
                saveButton.className = 'btn btn-sm btn-primary border border-dark';
                saveButton.type = 'button';
                saveButton.dataset.inlineSave = '';
                saveButton.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i>Salvează';
                const cancelButton = document.createElement('button');
                cancelButton.className = 'btn btn-sm btn-secondary border border-dark';
                cancelButton.type = 'button';
                cancelButton.dataset.inlineCancel = '';
                cancelButton.innerHTML = '<i class="fa-solid fa-xmark me-1"></i>Anulează';
                editActions.appendChild(saveButton);
                editActions.appendChild(cancelButton);

                wrapper.appendChild(viewActions);
                wrapper.appendChild(editActions);
                actionsTd.appendChild(wrapper);
                row.appendChild(actionsTd);

                return row;
            };

            const saveNewRow = () => {
                if (!newRow || !storeUrl) {
                    return;
                }

                clearErrors(newRow);
                const payload = gatherPayload(newRow);

                newRow.classList.add('opacity-75');
                newRow.querySelectorAll('button').forEach(btn => btn.disabled = true);

                fetch(storeUrl, {
                    method: 'POST',
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
                                showFieldErrors(newRow, data.errors || {});
                                throw new Error('validation');
                            }

                            throw new Error(data.message || `Eroare (${response.status})`);
                        }

                        return data;
                    })
                    .then(data => {
                        if (data.alimentare) {
                            const row = buildRowElement(data.alimentare);
                            if (tableBody) {
                                tableBody.insertBefore(row, newRow.nextSibling);
                            }
                            removeEmptyRow();
                            attachRowListeners(row);
                        }

                        if (data.metrics) {
                            applyMetrics(data.metrics);
                        }

                        resetNewRow();
                        renderFeedback(data.message || 'Alimentarea a fost adăugată.');
                    })
                    .catch(error => {
                        if (error.message === 'validation') {
                            renderFeedback('Verifică valorile introduse.', 'danger');
                            return;
                        }
                        console.error('Alimentare create error', error);
                        renderFeedback('Nu am putut adăuga alimentarea. Reîncearcă.', 'danger');
                    })
                    .finally(() => {
                        newRow.classList.remove('opacity-75');
                        newRow.querySelectorAll('button').forEach(btn => btn.disabled = false);
                    });
            };

            if (newRow) {
                const newSaveBtn = newRow.querySelector('[data-new-save]');
                const newResetBtn = newRow.querySelector('[data-new-reset]');

                if (newSaveBtn) {
                    newSaveBtn.addEventListener('click', saveNewRow);
                }

                if (newResetBtn) {
                    newResetBtn.addEventListener('click', resetNewRow);
                }

                newRow.querySelectorAll('input').forEach(input => {
                    input.addEventListener('keydown', event => {
                        if (event.key === 'Enter') {
                            event.preventDefault();
                            saveNewRow();
                        }
                        if (event.key === 'Escape') {
                            event.preventDefault();
                            resetNewRow();
                        }
                    });

                    input.addEventListener('blur', () => {
                        if (input.type !== 'number') {
                            return;
                        }
                        const decimals = Number(input.dataset.decimals ?? 2);
                        input.value = formatNumber(input.value, decimals);
                    });
                });
            }
        });
    </script>
@endpush
@endsection
