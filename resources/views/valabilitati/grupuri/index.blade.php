@extends('layouts.app')

@section('content')
    <style>
        .curse-summary-table,
        .curse-data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        .curse-summary-table th,
        .curse-summary-table td,
        .curse-data-table th,
        .curse-data-table td {
            border: 1px solid #000000ff;
            padding: 0.4rem 0.6rem;
            line-height: 1.1;
            vertical-align: middle;
        }

        .curse-data-table thead th {
            background-color: #f8f9fa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
        }

        .curse-data-table tbody tr:nth-child(even) {
            background-color: #fcfcfc;
        }

        .curse-data-table tbody tr:hover {
            background-color: #f1f3f5;
        }

        .curse-nowrap {
            white-space: nowrap;
        }
    </style>

    @php
        $grupuriRoute = route('valabilitati.grupuri.index', $valabilitate);
        $curseRoute = route('valabilitati.curse.index', $valabilitate);
    @endphp

    <div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center text-center text-lg-start" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-12 col-lg-4 mb-2 mb-lg-0">
                <span class="badge culoare1 fs-5">
                    <span class="d-inline-flex flex-column align-items-start gap-1 lh-1">
                        <span>
                            <i class="fa-solid fa-layer-group me-1"></i>Grupuri
                            /
                            {{ $valabilitate->denumire }}
                        </span>
                    </span>
                </span>
            </div>
            <div class="col-12 col-lg-4 my-2 my-lg-0">
                <div class="d-inline-flex justify-content-center gap-2">
                    <a
                        href="{{ $curseRoute }}"
                        class="btn btn-sm btn-outline-primary border border-dark rounded-3"
                    >
                        <i class="fa-solid fa-truck-fast me-1"></i>Curse
                    </a>
                    <a
                        href="{{ $grupuriRoute }}"
                        class="btn btn-sm btn-primary text-white border border-dark rounded-3"
                    >
                        <i class="fa-solid fa-layer-group me-1"></i>Grupuri
                    </a>
                </div>
            </div>
            <div class="col-12 col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="d-flex align-items-stretch align-items-lg-end gap-2 flex-wrap justify-content-center justify-content-lg-end">
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary border border-dark rounded-3"
                        data-bs-toggle="modal"
                        data-bs-target="#cursaGroupCreateModal"
                    >
                        <i class="fa-solid fa-plus me-1"></i>Crează grup
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

            <div id="curse-summary" class="px-3 mb-3">
                @include('valabilitati.curse.partials.summary', [
                    'valabilitate' => $valabilitate,
                    'summary' => $summary,
                    'showGroupSummary' => true,
                ])
            </div>

            <div class="px-3">
                <div class="table-responsive">
                    <table class="table table-sm curse-data-table align-middle">
                        <thead>
                            <tr>
                                <th class="text-center curse-nowrap">#</th>
                                <th>Grup</th>
                                <th class="curse-nowrap">Format</th>
                                <th>Factură</th>
                                <th class="text-end curse-nowrap">Sumă încasată</th>
                                <th class="text-end curse-nowrap">Sumă calculată</th>
                                <th class="text-end curse-nowrap">Diferență</th>
                                <th class="text-center curse-nowrap">Curse atașate</th>
                                <th class="text-end curse-nowrap">Acțiuni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($grupuri as $grup)
                                @php
                                    $rowIndex = ($grupuri->firstItem() ?? 1) + $loop->index;
                                    $facturaLabel = $grup->numar_factura ?: '—';
                                    if ($grup->data_factura) {
                                        $facturaLabel = $facturaLabel === '—'
                                            ? optional($grup->data_factura)->format('d.m.Y')
                                            : $facturaLabel . ' / ' . optional($grup->data_factura)->format('d.m.Y');
                                    }
                                    $incasata = $grup->suma_incasata !== null ? (float) $grup->suma_incasata : null;
                                    $calculata = $grup->suma_calculata !== null ? (float) $grup->suma_calculata : null;
                                    $diferenta = null;
                                    if ($incasata !== null || $calculata !== null) {
                                        $diferenta = ($incasata ?? 0) - ($calculata ?? 0);
                                    }
                                    $canDelete = ($grup->curse_count ?? 0) === 0;
                                @endphp
                                <tr style="background-color: {{ $grup->culoare_hex ?? '#ffffff' }}; color: #111;">
                                    <td class="text-center fw-semibold">#{{ $rowIndex }}</td>
                                    <td class="fw-semibold">{{ $grup->nume }}</td>
                                    <td class="curse-nowrap">{{ $grup->formatDocumenteLabel() }}</td>
                                    <td>{{ $facturaLabel }}</td>
                                    <td class="text-end">{{ $incasata !== null ? number_format($incasata, 2) : '—' }}</td>
                                    <td class="text-end">{{ $calculata !== null ? number_format($calculata, 2) : '—' }}</td>
                                    <td class="text-end">{{ $diferenta !== null ? number_format($diferenta, 2) : '—' }}</td>
                                    <td class="text-center">{{ $grup->curse_count ?? 0 }}</td>
                                    <td class="text-end">
                                        <div class="d-flex gap-2 justify-content-end">
                                            <a
                                                href="#"
                                                data-bs-toggle="modal"
                                                data-bs-target="#cursaGroupEditModal{{ $grup->id }}"
                                                class="flex"
                                                title="Editează grupul"
                                                aria-label="Editează grupul"
                                            >
                                                <span class="badge bg-primary d-inline-flex align-items-center justify-content-center" aria-hidden="true">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </span>
                                                <span class="visually-hidden">Editează</span>
                                            </a>
                                            @if ($canDelete)
                                                <a
                                                    href="#"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#cursaGroupDeleteModal{{ $grup->id }}"
                                                    class="flex"
                                                    title="Șterge grupul"
                                                    aria-label="Șterge grupul"
                                                >
                                                    <span class="badge bg-danger d-inline-flex align-items-center justify-content-center" aria-hidden="true">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </span>
                                                    <span class="visually-hidden">Șterge</span>
                                                </a>
                                            @else
                                                <span class="badge bg-secondary" title="Grupul are curse atașate" aria-label="Grup blocat">
                                                    <i class="fa-solid fa-lock"></i>
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4">
                                        Nu există grupuri definite pentru această valabilitate.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if ($grupuri instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator && $grupuri->hasPages())
                <div class="px-3 mt-3">
                    {{ $grupuri->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    <div id="curse-modals" data-active-modal="{{ session('curse.modal') }}">
        @include('valabilitati.curse.partials.modals', $modalViewData)
    </div>
@endsection
