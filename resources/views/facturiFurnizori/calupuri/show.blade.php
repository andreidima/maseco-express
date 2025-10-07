@extends('layouts.app')

@section('content')
@php
    $calupStatusLabels = $statusOptions;
    $facturaStatusLabels = [
        \App\Models\FacturiFurnizori\FacturaFurnizor::STATUS_NEPLATITA => 'Neplătită',
        \App\Models\FacturiFurnizori\FacturaFurnizor::STATUS_PLATITA => 'Plătită',
    ];
@endphp
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-layer-group me-1"></i>Calup: {{ $calup->denumire_calup }}
            </span>
            <span class="badge bg-white border border-dark rounded-pill text-dark ms-2">
                <small>Status: {{ $calupStatusLabels[$calup->status] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $calup->status)) }}</small>
            </span>
        </div>
        <div class="col-lg-6 text-end">
            <a class="btn btn-sm btn-secondary text-white border border-dark rounded-3" href="{{ route('facturi-furnizori.facturi.index') }}">
                <i class="fa-solid fa-rotate-left me-1"></i>Înapoi la facturi
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="px-3">
            <div class="mb-3">
                <div class="border border-dark rounded-3 p-3 bg-white">
                    <h6 class="text-uppercase text-muted">Sumar calup</h6>
                    <p class="mb-1"><strong>Facturi atașate:</strong> {{ $calup->facturi->count() }}</p>
                    <p class="mb-1"><strong>Total sume:</strong> {{ number_format($calup->facturi->sum('suma'), 2) }}</p>
                    <p class="mb-1"><strong>Data plată:</strong> {{ $calup->data_plata?->format('d.m.Y') ?: 'Nespecificată' }}</p>
                    @if ($calup->fisier_pdf)
                        <p class="mb-0"><a href="{{ route('facturi-furnizori.plati-calupuri.descarca-fisier', $calup) }}">Descarcă PDF asociat</a> <span class="text-muted small">({{ basename($calup->fisier_pdf) }})</span></p>
                    @endif
                </div>
            </div>

            <form action="{{ route('facturi-furnizori.plati-calupuri.update', $calup) }}" method="POST" enctype="multipart/form-data" class="border border-dark rounded-3 p-3 mb-3 bg-white">
                @csrf
                @method('PUT')
                @include('facturiFurnizori.calupuri._form', ['calup' => $calup, 'disableStatus' => false])
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary text-white border border-dark rounded-3">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Actualizează calup
                    </button>
                </div>
            </form>

            <div class="border border-dark rounded-3 p-3 mb-3 bg-white">
                <h6 class="text-uppercase text-muted mb-3">Facturi atașate</h6>
                @if ($calup->facturi->isEmpty())
                    <p class="text-muted mb-0">Nu sunt facturi atașate acestui calup.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover table-bordered border-dark align-middle mb-0">
                            <thead class="text-white rounded culoare2">
                                <tr>
                                    <th>Furnizor</th>
                                    <th>Număr</th>
                                    <th>Scadență</th>
                                    <th class="text-end">Sumă</th>
                                    <th>Status factură</th>
                                    <th class="text-end">Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($calup->facturi as $factura)
                                    <tr>
                                        <td>{{ $factura->denumire_furnizor }}</td>
                                        <td>{{ $factura->numar_factura }}</td>
                                        <td>{{ $factura->data_scadenta?->format('d.m.Y') }}</td>
                                        <td class="text-end">{{ number_format($factura->suma, 2) }} {{ $factura->moneda }}</td>
                                        <td>
                                            <span class="badge bg-white border border-dark rounded-pill text-dark fw-normal">
                                                <small>{{ $facturaStatusLabels[$factura->status] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $factura->status)) }}</small>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            @if ($calup->status !== \App\Models\FacturiFurnizori\PlataCalup::STATUS_PLATIT)
                                                <form action="{{ route('facturi-furnizori.plati-calupuri.detaseaza-factura', [$calup, $factura]) }}" method="POST" onsubmit="return confirm('Elimini factura din calup?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="badge bg-danger text-white border-0 rounded-3 px-3 py-2">Elimină</button>
                                                </form>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="border border-dark rounded-3 p-3 bg-white">
                <h6 class="text-uppercase text-muted mb-3">Adaugă facturi în calup</h6>
                @if ($facturiDisponibile->isEmpty())
                    <p class="text-muted mb-0">Nu există facturi neplătite disponibile.</p>
                @else
                    <form action="{{ route('facturi-furnizori.plati-calupuri.atasare-facturi', $calup) }}" method="POST" id="attach-form">
                        @csrf
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="text-white rounded culoare2">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">
                                            <input type="checkbox" id="attach-toggle-all">
                                        </th>
                                        <th>Furnizor</th>
                                        <th>Număr</th>
                                        <th>Scadență</th>
                                        <th class="text-end">Sumă</th>
                                        <th>Monedă</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($facturiDisponibile as $facturaDisponibila)
                                        <tr>
                                            <td class="text-center">
                                                <input type="checkbox" name="facturi[]" value="{{ $facturaDisponibila->id }}" class="attach-checkbox">
                                            </td>
                                            <td>{{ $facturaDisponibila->denumire_furnizor }}</td>
                                            <td>{{ $facturaDisponibila->numar_factura }}</td>
                                            <td>{{ $facturaDisponibila->data_scadenta?->format('d.m.Y') }}</td>
                                            <td class="text-end">{{ number_format($facturaDisponibila->suma, 2) }}</td>
                                            <td>{{ $facturaDisponibila->moneda }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <span class="badge bg-info text-dark" id="attach-counter">Selectate: 0</span>
                            <button type="submit" class="btn btn-sm btn-success text-white border border-dark rounded-3">
                                <i class="fa-solid fa-plus me-1"></i>Adaugă în calup
                            </button>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleAll = document.getElementById('attach-toggle-all');
        const checkboxes = Array.from(document.querySelectorAll('.attach-checkbox'));
        const counter = document.getElementById('attach-counter');

        const updateCounter = () => {
            if (!counter) {
                return;
            }
            const total = checkboxes.filter(checkbox => checkbox.checked).length;
            counter.textContent = `Selectate: ${total}`;
        };

        if (toggleAll) {
            toggleAll.addEventListener('change', () => {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = toggleAll.checked;
                });
                updateCounter();
            });
        }

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateCounter);
        });

        updateCounter();
    });
</script>
@endsection
