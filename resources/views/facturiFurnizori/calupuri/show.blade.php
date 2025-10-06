@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card mx-2" style="border-radius: 30px;">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2" style="border-radius: 30px 30px 0 0;">
            <div>
                <span class="badge bg-secondary fs-5">Calup: {{ $calup->denumire_calup }}</span>
                <span class="badge bg-info text-dark ms-2">Status: {{ $calup->status }}</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('facturi-furnizori.plati-calupuri.index') }}" class="btn btn-outline-secondary btn-sm">Inapoi la lista</a>
            </div>
        </div>
        <div class="card-body">
            @include('errors')

            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="border rounded p-3 h-100">
                        <h6 class="text-uppercase text-muted">Sumar calup</h6>
                        <p class="mb-1"><strong>Facturi atasate:</strong> {{ $calup->facturi->count() }}</p>
                        <p class="mb-1"><strong>Total sume:</strong> {{ number_format($calup->facturi->sum('suma'), 2) }}</p>
                        <p class="mb-1"><strong>Data plata:</strong> {{ $calup->data_plata?->format('d.m.Y') ?: 'Nespecificata' }}</p>
                        @if ($calup->fisier_pdf)
                            <p class="mb-0"><a href="{{ route('facturi-furnizori.plati-calupuri.descarca-fisier', $calup) }}">Descarca PDF asociat</a> <span class="text-muted small">({{ basename($calup->fisier_pdf) }})</span></p>
                        @endif
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="border rounded p-3 h-100">
                        <h6 class="text-uppercase text-muted">Actiuni rapide</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @if ($calup->status !== \App\Models\FacturiFurnizori\PlataCalup::STATUS_PLATIT)
                                <form action="{{ route('facturi-furnizori.plati-calupuri.marcheaza-platit', $calup) }}" method="POST" class="d-flex align-items-center gap-2">
                                    @csrf
                                    <label for="data_plata_actiune" class="form-label mb-0">Data plata:</label>
                                    <input type="date" name="data_plata" id="data_plata_actiune" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
                                    <button type="submit" class="btn btn-success btn-sm">Marcheaza platit</button>
                                </form>
                            @else
                                <form action="{{ route('facturi-furnizori.plati-calupuri.redeschide', $calup) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm">Redeschide calup</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('facturi-furnizori.plati-calupuri.update', $calup) }}" method="POST" enctype="multipart/form-data" class="mb-4">
                @csrf
                @method('PUT')
                @include('facturiFurnizori.calupuri._form', ['calup' => $calup, 'disableStatus' => false])
                <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-primary">Actualizeaza calup</button>
                </div>
            </form>

            <div class="border rounded p-3 mb-4">
                <h5 class="mb-3">Facturi atasate</h5>
                @if ($calup->facturi->isEmpty())
                    <p class="text-muted mb-0">Nu sunt facturi atasate acestui calup.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Furnizor</th>
                                    <th>Numar</th>
                                    <th>Scadenta</th>
                                    <th class="text-end">Suma</th>
                                    <th>Status factura</th>
                                    <th class="text-end">Actiuni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($calup->facturi as $factura)
                                    <tr>
                                        <td>{{ $factura->denumire_furnizor }}</td>
                                        <td>{{ $factura->numar_factura }}</td>
                                        <td>{{ $factura->data_scadenta?->format('d.m.Y') }}</td>
                                        <td class="text-end">{{ number_format($factura->suma, 2) }} {{ $factura->moneda }}</td>
                                        <td><span class="badge bg-secondary text-uppercase">{{ $factura->status }}</span></td>
                                        <td class="text-end">
                                            @if ($calup->status !== \App\Models\FacturiFurnizori\PlataCalup::STATUS_PLATIT)
                                                <form action="{{ route('facturi-furnizori.plati-calupuri.detaseaza-factura', [$calup, $factura]) }}" method="POST" onsubmit="return confirm('Elimini factura din calup?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Elimina</button>
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

            <div class="border rounded p-3">
                <h5 class="mb-3">Adauga facturi in calup</h5>
                @if ($facturiDisponibile->isEmpty())
                    <p class="text-muted mb-0">Nu exista facturi neplatite disponibile.</p>
                @else
                    <form action="{{ route('facturi-furnizori.plati-calupuri.atasare-facturi', $calup) }}" method="POST" id="attach-form">
                        @csrf
                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="attach-toggle-all">
                                        </th>
                                        <th>Furnizor</th>
                                        <th>Numar</th>
                                        <th>Scadenta</th>
                                        <th class="text-end">Suma</th>
                                        <th>Moneda</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($facturiDisponibile as $facturaDisponibila)
                                        <tr>
                                            <td>
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
                            <button type="submit" class="btn btn-success">Adauga in calup</button>
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
