@extends('layouts.app')

@php
    $facturiIndexUrl = \App\Support\FacturiFurnizori\FacturiIndexFilterState::route();
    $stockDetails = $stockDetails ?? [];
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-circle-info me-1"></i>Detalii factură furnizor
            </span>
        </div>
        <div class="col-lg-6 text-end">
            <a class="btn btn-sm btn-primary text-white border border-dark rounded-3 me-2" href="{{ route('facturi-furnizori.facturi.edit', $factura) }}">
                <i class="fa-solid fa-pen-to-square me-1"></i>Modifică
            </a>
            <a class="btn btn-sm btn-secondary text-white border border-dark rounded-3" href="{{ $facturiIndexUrl }}">
                <i class="fa-solid fa-rotate-left me-1"></i>Înapoi
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="px-3">
            <div class="row g-3 mb-3">
                <div class="col-lg-6">
                    <div class="border border-dark rounded-3 p-3 h-100 bg-white">
                        <h6 class="text-uppercase text-muted">Informații principale</h6>
                        <p class="mb-1"><strong>Furnizor:</strong> {{ $factura->denumire_furnizor }}</p>
                        <p class="mb-1"><strong>Număr factură:</strong> {{ $factura->numar_factura }}</p>
                        <p class="mb-1"><strong>Data factură:</strong> {{ $factura->data_factura?->format('d.m.Y') }}</p>
                        <p class="mb-1"><strong>Data scadență:</strong> {{ $factura->data_scadenta?->format('d.m.Y') }}</p>
                        <p class="mb-1"><strong>Sumă:</strong> {{ number_format($factura->suma, 2) }} {{ $factura->moneda }}</p>
                        <p class="mb-1"><strong>Cont IBAN:</strong> {{ $factura->cont_iban ?: '-' }}</p>
                        <p class="mb-1"><strong>Nr auto / departament:</strong> {{ $factura->departament_vehicul ?: '-' }}</p>
                        <p class="mb-0"><strong>Creată la:</strong> {{ $factura->created_at?->format('d.m.Y H:i') ?: '-' }}</p>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="border border-dark rounded-3 p-3 h-100 bg-white">
                        <h6 class="text-uppercase text-muted">Observații</h6>
                        <p class="mb-0">{{ $factura->observatii ?: 'Nu există observații.' }}</p>
                    </div>
                </div>
            </div>

            <div class="border border-dark rounded-3 p-3 bg-white mb-3">
                <h6 class="text-uppercase text-muted mb-3">Produse</h6>
                @if ($factura->piese->isEmpty())
                    <p class="text-muted mb-0">Factura nu are produse asociate.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered border-dark align-middle mb-0">
                            <thead class="text-white rounded culoare2">
                                <tr>
                                    <th style="min-width: 200px;">Denumire</th>
                                    <th style="min-width: 140px;">Cod</th>
                                    <th class="text-end" style="min-width: 140px;">Cantitate inițială</th>
                                    <th class="text-end" style="min-width: 120px;">Preț NET/buc</th>
                                    <th class="text-end" style="min-width: 110px;">Cotă TVA</th>
                                    <th class="text-end" style="min-width: 140px;">Preț BRUT/buc</th>
                                    <th class="text-center" style="min-width: 150px;">Detalii stoc</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($factura->piese as $piesa)
                                    @php
                                        $pieceId = (int) $piesa->getKey();
                                        $details = $stockDetails[$pieceId] ?? null;
                                        $initialValue = $details['initial'] ?? ($piesa->cantitate_initiala !== null ? (float) $piesa->cantitate_initiala : null);
                                        $remainingValue = $details['remaining'] ?? ($piesa->nr_bucati !== null ? (float) $piesa->nr_bucati : null);
                                        $usedValue = $details['used'] ?? null;

                                        if ($usedValue === null) {
                                            if ($initialValue !== null && $remainingValue !== null) {
                                                $usedValue = max($initialValue - $remainingValue, 0);
                                            } else {
                                                $usedValue = 0.0;
                                            }
                                        }

                                        $initialDisplay = $initialValue !== null ? number_format((float) $initialValue, 2, '.', '') : '';
                                        $usedDisplay = number_format((float) $usedValue, 2, '.', '');
                                        $machinesData = $details['machines'] ?? [];
                                    @endphp
                                    <tr>
                                        <td>{{ $piesa->denumire }}</td>
                                        <td>{{ $piesa->cod ?: '-' }}</td>
                                        <td class="text-end">{{ $piesa->cantitate_initiala !== null ? number_format($piesa->cantitate_initiala, 2) : '-' }}</td>
                                        <td class="text-end">{{ $piesa->pret !== null ? number_format($piesa->pret, 2) : '-' }}</td>
                                        <td class="text-end">{{ $piesa->tva_cota !== null ? number_format($piesa->tva_cota, 2) . '%' : '-' }}</td>
                                        <td class="text-end">{{ $piesa->pret_brut !== null ? number_format($piesa->pret_brut, 2) : '-' }}</td>
                                        <td class="text-center">
                                            @if ($pieceId > 0)
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#stockDetailsModal"
                                                    data-piece-name="{{ $piesa->denumire }}"
                                                    data-piece-code="{{ $piesa->cod ?? '' }}"
                                                    data-piece-initial="{{ $initialDisplay }}"
                                                    data-piece-remaining="{{ $remainingValue !== null ? number_format((float) $remainingValue, 2, '.', '') : '' }}"
                                                    data-piece-used="{{ $usedDisplay }}"
                                                    data-piece-machines='@json($machinesData)'
                                                >
                                                    <i class="fa-solid fa-circle-info me-1"></i>Detalii
                                                </button>
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="border border-dark rounded-3 p-3 bg-white mb-3" id="factura-fisiere">
                <h6 class="text-uppercase text-muted mb-3">Fișiere atașate</h6>
                @if ($factura->fisiere->isEmpty())
                    <p class="text-muted mb-0">Factura nu are fișiere atașate.</p>
                @else
                    <ul class="list-group list-group-flush">
                        @foreach ($factura->fisiere as $fisier)
                            <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                <div class="me-2">
                                    <i class="fa-solid fa-file-pdf text-danger me-2"></i>
                                    {{ $fisier->nume_original ?: basename($fisier->cale) }}
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <a
                                        class="btn btn-sm btn-outline-primary"
                                        href="{{ route('facturi-furnizori.facturi.fisiere.vizualizeaza', [$factura, $fisier]) }}"
                                        target="_blank"
                                    >
                                        <i class="fa-solid fa-eye me-1"></i>Vezi
                                    </a>
                                    <a
                                        class="btn btn-sm btn-outline-secondary"
                                        href="{{ route('facturi-furnizori.facturi.fisiere.descarca', [$factura, $fisier]) }}"
                                    >
                                        <i class="fa-solid fa-download me-1"></i>Descarcă
                                    </a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="border border-dark rounded-3 p-3 bg-white">
                <h6 class="text-uppercase text-muted mb-3">Calupuri asociate</h6>
                @if ($factura->calupuri->isEmpty())
                    <p class="text-muted mb-0">Factura nu este atașată niciunui calup.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered border-dark align-middle mb-0">
                            <thead class="text-white rounded culoare2">
                                <tr>
                                    <th>Denumire</th>
                                    <th>Data plată</th>
                                    <th class="text-end">Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($factura->calupuri as $calup)
                                    <tr>
                                        <td>{{ $calup->denumire_calup }}</td>
                                        <td>{{ $calup->data_plata?->format('d.m.Y') ?: '-' }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('facturi-furnizori.plati-calupuri.show', $calup) }}" class="badge bg-secondary text-dark text-decoration-none rounded-3 px-3 py-2">Vezi calup</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@include('partials.stock-details-modal')
