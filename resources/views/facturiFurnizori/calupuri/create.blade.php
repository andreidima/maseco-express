@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card mx-2" style="border-radius: 30px;">
        <div class="card-header d-flex justify-content-between align-items-center" style="border-radius: 30px 30px 0 0;">
            <span class="badge bg-primary fs-5">Creeaza calup de plata</span>
            <a href="{{ route('facturi-furnizori.plati-calupuri.index') }}" class="btn btn-outline-secondary btn-sm">Inapoi la calupuri</a>
        </div>
        <div class="card-body">
            @include('errors')

            <form action="{{ route('facturi-furnizori.plati-calupuri.store') }}" method="POST" enctype="multipart/form-data" id="calup-create-form">
                @csrf
                @include('facturiFurnizori.calupuri._form', ['calup' => null, 'disableStatus' => true])

                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">Selecteaza facturi ({{ count($facturiDisponibile) }})</h5>
                        <span id="selected-counter" class="badge bg-info text-dark">Selectate: 0</span>
                    </div>

                    @if ($facturiDisponibile->isEmpty())
                        <p class="text-muted mb-0">Nu exista facturi disponibile pentru selectie.</p>
                    @else
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="toggle-all">
                                        </th>
                                        <th>Furnizor</th>
                                        <th>Numar</th>
                                        <th>Data</th>
                                        <th>Scadenta</th>
                                        <th class="text-end">Suma</th>
                                        <th>Moneda</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($facturiDisponibile as $factura)
                                        <tr>
                                            <td>
                                                <input type="checkbox" name="facturi[]" value="{{ $factura->id }}" class="factura-checkbox" @checked(in_array($factura->id, $facturiSelectate))>
                                            </td>
                                            <td>{{ $factura->denumire_furnizor }}</td>
                                            <td>{{ $factura->numar_factura }}</td>
                                            <td>{{ $factura->data_factura?->format('d.m.Y') }}</td>
                                            <td>{{ $factura->data_scadenta?->format('d.m.Y') }}</td>
                                            <td class="text-end">{{ number_format($factura->suma, 2) }}</td>
                                            <td>{{ $factura->moneda }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('facturi-furnizori.plati-calupuri.index') }}" class="btn btn-link">Renunta</a>
                    <button type="submit" class="btn btn-primary">Salveaza calupul</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleAll = document.getElementById('toggle-all');
        const checkboxes = Array.from(document.querySelectorAll('.factura-checkbox'));
        const counter = document.getElementById('selected-counter');

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
