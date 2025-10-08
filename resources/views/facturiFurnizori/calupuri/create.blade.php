@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-layer-group me-1"></i>Creează calup de plată
            </span>
        </div>
        <div class="col-lg-6 text-end">
            <a class="btn btn-sm btn-secondary text-white border border-dark rounded-3" href="{{ route('facturi-furnizori.plati-calupuri.index') }}">
                <i class="fa-solid fa-rotate-left me-1"></i>Înapoi la calupuri
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="px-3">
            <form action="{{ route('facturi-furnizori.plati-calupuri.store') }}" method="POST" enctype="multipart/form-data" id="calup-create-form" class="border border-dark rounded-3 p-3 bg-white">
                @csrf
                @include('facturiFurnizori.calupuri._form', ['calup' => null])

                <div class="border border-dark rounded-3 p-3 mb-3">
                    <div class="d-flex flex-column flex-lg-row justify-content-lg-between align-items-lg-center mb-3">
                        <h5 class="mb-2 mb-lg-0">Selectează facturi ({{ count($facturiDisponibile) }})</h5>
                        <span id="selected-counter" class="badge bg-info text-dark">Selectate: 0</span>
                    </div>

                    @if ($facturiDisponibile->isEmpty())
                        <p class="text-muted mb-0">Nu există facturi disponibile pentru selecție.</p>
                    @else
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead class="text-white rounded culoare2">
                                    <tr>
                                        <th class="text-center" style="width: 50px;">
                                            <input type="checkbox" id="toggle-all">
                                        </th>
                                        <th>Furnizor</th>
                                        <th>Număr</th>
                                        <th>Data</th>
                                        <th>Scadență</th>
                                        <th class="text-end">Sumă</th>
                                        <th>Monedă</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($facturiDisponibile as $factura)
                                        <tr>
                                            <td class="text-center">
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

                <div class="d-flex flex-column flex-lg-row justify-content-lg-end gap-2">
                    <a href="{{ route('facturi-furnizori.plati-calupuri.index') }}" class="btn btn-sm btn-secondary text-white border border-dark rounded-3">
                        <i class="fa-solid fa-angles-left me-1"></i>Renunță
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary text-white border border-dark rounded-3">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Salvează calupul
                    </button>
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
