@extends('layouts.app')

@section('content')
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
            @if (session('status'))
                <div class="alert alert-success mx-3">{{ session('status') }}</div>
            @endif

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
                                <th class="text-center">{{ number_format($totalLitri, 2) }}</th>
                                <th class="text-center">
                                    {{ $averagePret !== null ? number_format($averagePret, 4) : '—' }}
                                </th>
                                <th class="text-center">{{ number_format($totalPret, 4) }}</th>
                                <th class="text-center">
                                    {{ $consum !== null ? number_format($consum, 2) : '—' }}
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
                        <tbody>
                            @forelse ($alimentari as $alimentare)
                                <tr>
                                    <td class="fw-semibold">{{ optional($alimentare->data_ora_alimentare)->format('d.m.Y H:i') }}</td>
                                    <td class="text-end">{{ number_format((float) $alimentare->litrii, 2) }}</td>
                                    <td class="text-end">{{ number_format((float) $alimentare->pret_pe_litru, 4) }}</td>
                                    <td class="text-end">{{ number_format((float) $alimentare->total_pret, 4) }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center flex-wrap alimentari-actions">
                                            <button
                                                class="btn btn-sm btn-outline-primary border border-dark"
                                                type="button"
                                                data-bs-toggle="modal"
                                                data-bs-target="#alimentareEditModal-{{ $alimentare->id }}"
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
                                    </td>
                                </tr>
                            @empty
                                <tr>
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
                                            value="{{ old('litrii') }}"
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
                                            value="{{ old('pret_pe_litru') }}"
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
                                            value="{{ old('total_pret') }}"
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
                                                value="{{ $shouldPrefillFromOld ? old('litrii') : $alimentare->litrii }}"
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
                                                value="{{ $shouldPrefillFromOld ? old('pret_pe_litru') : $alimentare->pret_pe_litru }}"
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
                                                value="{{ $shouldPrefillFromOld ? old('total_pret') : $alimentare->total_pret }}"
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
@endsection
