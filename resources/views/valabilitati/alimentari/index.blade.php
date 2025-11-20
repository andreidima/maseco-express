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
        $editingAlimentareId = old('alimentare_id') !== null
            ? (int) old('alimentare_id')
            : (request()->has('edit') ? (int) request('edit') : null);

        $shouldShowErrorsForRow = function (?int $rowId) use ($editingAlimentareId, $errors): bool {
            return $errors->any() && (($rowId === null && $editingAlimentareId === null) || $editingAlimentareId === $rowId);
        };

        $inputError = function (string $field, ?int $rowId = null) use ($shouldShowErrorsForRow, $errors): ?string {
            return $shouldShowErrorsForRow($rowId) ? $errors->first($field) : null;
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
    </style>

    @php
        $filterQueryParams = fn (array $params) => array_filter($params, fn ($value) => $value !== null && $value !== '');

        $paginationQuery = $filterQueryParams(request()->only('page'));
        $curseRoute = route('valabilitati.curse.index', $valabilitate);
        $grupuriRoute = route('valabilitati.grupuri.index', $valabilitate);
        $alimentariRoute = route('valabilitati.alimentari.index', $filterQueryParams([
            'valabilitate' => $valabilitate,
            ...$paginationQuery,
        ]));
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
                    <a
                        href="#alimentari-create-row"
                        class="btn btn-sm btn-success text-white border border-dark rounded-3"
                    >
                        <i class="fas fa-plus-square text-white me-1"></i>Adaugă alimentare
                    </a>
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
                                <th class="text-center">{{ $formatNumber($totalLitri, 2) }}</th>
                                <th class="text-center">
                                    {{ $averagePret !== null ? $formatNumber($averagePret, 4) : '—' }}
                                </th>
                                <th class="text-center">{{ $formatNumber($totalPret, 4) }}</th>
                                <th class="text-center">
                                    {{ $consum !== null ? $formatNumber($consum, 2) : '—' }}
                                </th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="table-responsive">
                    <form
                        id="alimentareCreateForm"
                        method="POST"
                        action="{{ route('valabilitati.alimentari.store', $valabilitate) }}"
                        class="d-none"
                    >
                        @csrf
                    </form>
                    <table class="table table-sm alimentari-table align-middle">
                        <thead>
                            <tr>
                                <th>Dată / oră alimentare</th>
                                <th class="text-end">Litrii</th>
                                <th class="text-end">Preț / litru</th>
                                <th class="text-end">Total preț</th>
                                <th>Observații</th>
                                <th class="actions-column text-center">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-light" id="alimentari-create-row">
                                <td>
                                    <input
                                        type="datetime-local"
                                        name="data_ora_alimentare"
                                        class="form-control form-control-sm {{ $inputError('data_ora_alimentare') ? 'is-invalid' : '' }}"
                                        value="{{ old('data_ora_alimentare') }}"
                                        form="alimentareCreateForm"
                                        required
                                    >
                                    @if ($error = $inputError('data_ora_alimentare'))
                                        <div class="invalid-feedback d-block">{{ $error }}</div>
                                    @endif
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        name="litrii"
                                        class="form-control form-control-sm text-end {{ $inputError('litrii') ? 'is-invalid' : '' }}"
                                        value="{{ $formatNumber(old('litrii'), 2) }}"
                                        form="alimentareCreateForm"
                                        required
                                    >
                                    @if ($error = $inputError('litrii'))
                                        <div class="invalid-feedback d-block text-start">{{ $error }}</div>
                                    @endif
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        step="0.0001"
                                        min="0"
                                        name="pret_pe_litru"
                                        class="form-control form-control-sm text-end {{ $inputError('pret_pe_litru') ? 'is-invalid' : '' }}"
                                        value="{{ $formatNumber(old('pret_pe_litru'), 4) }}"
                                        form="alimentareCreateForm"
                                        required
                                    >
                                    @if ($error = $inputError('pret_pe_litru'))
                                        <div class="invalid-feedback d-block text-start">{{ $error }}</div>
                                    @endif
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        step="0.0001"
                                        min="0"
                                        name="total_pret"
                                        class="form-control form-control-sm text-end {{ $inputError('total_pret') ? 'is-invalid' : '' }}"
                                        value="{{ $formatNumber(old('total_pret'), 4) }}"
                                        form="alimentareCreateForm"
                                        required
                                    >
                                    @if ($error = $inputError('total_pret'))
                                        <div class="invalid-feedback d-block text-start">{{ $error }}</div>
                                    @endif
                                </td>
                                <td>
                                    <textarea
                                        name="observatii"
                                        class="form-control form-control-sm {{ $inputError('observatii') ? 'is-invalid' : '' }}"
                                        rows="1"
                                        placeholder="Opțional"
                                        form="alimentareCreateForm"
                                    >{{ old('observatii') }}</textarea>
                                    @if ($error = $inputError('observatii'))
                                        <div class="invalid-feedback d-block">{{ $error }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center flex-wrap alimentari-actions">
                                        <button
                                            type="submit"
                                            class="btn btn-sm btn-success text-white border border-dark"
                                            form="alimentareCreateForm"
                                        >
                                            <i class="fas fa-plus-square text-white me-1"></i>Adaugă
                                        </button>
                                        <a
                                            href="{{ $alimentariRoute }}"
                                            class="btn btn-sm btn-outline-secondary border border-dark"
                                        >
                                            Resetează
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @forelse ($alimentari as $alimentare)
                                @php
                                    $isEditing = $editingAlimentareId === $alimentare->id;
                                    $editFormId = "alimentareEditForm-{$alimentare->id}";
                                    $editRoute = route('valabilitati.alimentari.index', $filterQueryParams([
                                        'valabilitate' => $valabilitate,
                                        ...$paginationQuery,
                                        'edit' => $alimentare->id,
                                    ]));
                                    $clearEditRoute = route('valabilitati.alimentari.index', $filterQueryParams([
                                        'valabilitate' => $valabilitate,
                                        ...$paginationQuery,
                                    ]));
                                @endphp
                                <form
                                    id="{{ $editFormId }}"
                                    method="POST"
                                    action="{{ route('valabilitati.alimentari.update', $filterQueryParams([
                                        'valabilitate' => $valabilitate,
                                        'alimentare' => $alimentare,
                                        ...$paginationQuery,
                                        'edit' => $alimentare->id,
                                    ])) }}"
                                    class="d-none"
                                >
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="alimentare_id" value="{{ $alimentare->id }}">
                                </form>
                                <tr class="{{ $isEditing ? 'table-primary' : '' }}">
                                    <td>
                                        @if ($isEditing)
                                            <input
                                                type="datetime-local"
                                                name="data_ora_alimentare"
                                                class="form-control form-control-sm {{ $inputError('data_ora_alimentare', $alimentare->id) ? 'is-invalid' : '' }}"
                                                value="{{ $editingAlimentareId === $alimentare->id && old('data_ora_alimentare') !== null
                                                    ? old('data_ora_alimentare')
                                                    : optional($alimentare->data_ora_alimentare)->format('Y-m-d\\TH:i') }}"
                                                form="{{ $editFormId }}"
                                                required
                                            >
                                            @if ($error = $inputError('data_ora_alimentare', $alimentare->id))
                                                <div class="invalid-feedback d-block">{{ $error }}</div>
                                            @endif
                                        @else
                                            <span class="fw-semibold">{{ optional($alimentare->data_ora_alimentare)->format('d.m.Y H:i') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($isEditing)
                                            <input
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                name="litrii"
                                                class="form-control form-control-sm text-end {{ $inputError('litrii', $alimentare->id) ? 'is-invalid' : '' }}"
                                                value="{{ $editingAlimentareId === $alimentare->id ? $formatNumber(old('litrii'), 2) : $formatNumber($alimentare->litrii, 2) }}"
                                                form="{{ $editFormId }}"
                                                required
                                            >
                                            @if ($error = $inputError('litrii', $alimentare->id))
                                                <div class="invalid-feedback d-block text-start">{{ $error }}</div>
                                            @endif
                                        @else
                                            {{ $formatNumber($alimentare->litrii, 2) }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($isEditing)
                                            <input
                                                type="number"
                                                step="0.0001"
                                                min="0"
                                                name="pret_pe_litru"
                                                class="form-control form-control-sm text-end {{ $inputError('pret_pe_litru', $alimentare->id) ? 'is-invalid' : '' }}"
                                                value="{{ $editingAlimentareId === $alimentare->id ? $formatNumber(old('pret_pe_litru'), 4) : $formatNumber($alimentare->pret_pe_litru, 4) }}"
                                                form="{{ $editFormId }}"
                                                required
                                            >
                                            @if ($error = $inputError('pret_pe_litru', $alimentare->id))
                                                <div class="invalid-feedback d-block text-start">{{ $error }}</div>
                                            @endif
                                        @else
                                            {{ $formatNumber($alimentare->pret_pe_litru, 4) }}
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if ($isEditing)
                                            <input
                                                type="number"
                                                step="0.0001"
                                                min="0"
                                                name="total_pret"
                                                class="form-control form-control-sm text-end {{ $inputError('total_pret', $alimentare->id) ? 'is-invalid' : '' }}"
                                                value="{{ $editingAlimentareId === $alimentare->id ? $formatNumber(old('total_pret'), 4) : $formatNumber($alimentare->total_pret, 4) }}"
                                                form="{{ $editFormId }}"
                                                required
                                            >
                                            @if ($error = $inputError('total_pret', $alimentare->id))
                                                <div class="invalid-feedback d-block text-start">{{ $error }}</div>
                                            @endif
                                        @else
                                            {{ $formatNumber($alimentare->total_pret, 4) }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($isEditing)
                                            <textarea
                                                name="observatii"
                                                class="form-control form-control-sm {{ $inputError('observatii', $alimentare->id) ? 'is-invalid' : '' }}"
                                                rows="1"
                                                form="{{ $editFormId }}"
                                                placeholder="Opțional"
                                            >{{ $editingAlimentareId === $alimentare->id && old('observatii') !== null ? old('observatii') : $alimentare->observatii }}</textarea>
                                            @if ($error = $inputError('observatii', $alimentare->id))
                                                <div class="invalid-feedback d-block">{{ $error }}</div>
                                            @endif
                                        @else
                                            <div class="text-wrap" style="max-width: 260px;">
                                                @if ($alimentare->observatii)
                                                    <small class="text-muted">{!! nl2br(e($alimentare->observatii)) !!}</small>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center flex-wrap alimentari-actions">
                                            @if ($isEditing)
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-primary border border-dark"
                                                    form="{{ $editFormId }}"
                                                >
                                                    <i class="fa-solid fa-floppy-disk me-1"></i>Salvează
                                                </button>
                                                <a
                                                    href="{{ $clearEditRoute }}"
                                                    class="btn btn-sm btn-outline-secondary border border-dark"
                                                >
                                                    Renunță
                                                </a>
                                            @else
                                                <a
                                                    href="{{ $editRoute }}"
                                                    class="btn btn-sm btn-outline-primary border border-dark"
                                                >
                                                    <i class="fa-solid fa-pen-to-square me-1"></i>
                                                </a>
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
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">Nu există alimentări salvate.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $alimentari->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
