@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card mx-2" style="border-radius: 30px;">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center" style="border-radius: 30px 30px 0 0;">
            <div class="mb-2 mb-md-0">
                <span class="badge bg-primary fs-5">Facturi furnizori</span>
            </div>
            <div class="text-end">
                <a href="{{ route('facturi-furnizori.plati-calupuri.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="fa-solid fa-layer-group me-1"></i>Calupuri plati
                </a>
                <a href="{{ route('facturi-furnizori.facturi.create') }}" class="btn btn-success btn-sm">
                    <i class="fa-solid fa-plus me-1"></i>Adauga factura
                </a>
            </div>
        </div>
        <div class="card-body">
            @include('errors')

            <form method="GET" class="row g-2 align-items-end mb-4">
                <div class="col-md-2">
                    <label for="filter-status" class="form-label">Status</label>
                    <select name="status" id="filter-status" class="form-select">
                        <option value="">Toate</option>
                        @foreach ($statusOptions as $key => $label)
                            <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter-furnizor" class="form-label">Furnizor</label>
                    <input type="text" name="furnizor" id="filter-furnizor" class="form-control" value="{{ $filters['furnizor'] }}">
                </div>
                <div class="col-md-2">
                    <label for="filter-departament" class="form-label">Departament</label>
                    <input type="text" name="departament" id="filter-departament" class="form-control" value="{{ $filters['departament'] }}">
                </div>
                <div class="col-md-2">
                    <label for="filter-moneda" class="form-label">Moneda</label>
                    <select name="moneda" id="filter-moneda" class="form-select">
                        <option value="">Toate</option>
                        @foreach ($monede as $moneda)
                            <option value="{{ $moneda }}" @selected($filters['moneda'] === $moneda)>{{ $moneda }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter-scadenta-de-la" class="form-label">Scadenta de la</label>
                    <input type="date" name="scadenta_de_la" id="filter-scadenta-de-la" class="form-control" value="{{ $filters['scadenta_de_la'] }}">
                </div>
                <div class="col-md-2">
                    <label for="filter-scadenta-pana" class="form-label">Scadenta pana</label>
                    <input type="date" name="scadenta_pana" id="filter-scadenta-pana" class="form-control" value="{{ $filters['scadenta_pana'] }}">
                </div>
                <div class="col-md-2">
                    <label for="filter-scadente-in-zile" class="form-label">Scadente in (zile)</label>
                    <input type="number" name="scadente_in_zile" id="filter-scadente-in-zile" min="0" class="form-control" value="{{ $filters['scadente_in_zile'] }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filtreaza</button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('facturi-furnizori.facturi.index') }}" class="btn btn-link w-100">Reseteaza</a>
                </div>
            </form>

            <div class="row mb-4">
                @foreach ($statusOptions as $statusKey => $statusLabel)
                    <div class="col-md-3 mb-2">
                        <div class="border rounded p-3 text-center">
                            <div class="fw-bold">{{ $statusLabel }}</div>
                            <div class="fs-5">{{ $statusCounts[$statusKey] ?? 0 }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all" title="Selecteaza toate">
                            </th>
                            <th>Furnizor</th>
                            <th>Numar</th>
                            <th>Data</th>
                            <th>Scadenta</th>
                            <th class="text-end">Suma</th>
                            <th>Moneda</th>
                            <th>Departament</th>
                            <th>Status</th>
                            <th>Calup</th>
                            <th>Observatii</th>
                            <th class="text-end">Actiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($facturi as $factura)
                            <tr>
                                <td>
                                    <input type="checkbox"
                                           class="select-factura"
                                           value="{{ $factura->id }}"
                                           @disabled($factura->status !== \App\Models\FacturiFurnizori\FacturaFurnizor::STATUS_NEPLATITA)>
                                </td>
                                <td>{{ $factura->denumire_furnizor }}</td>
                                <td>{{ $factura->numar_factura }}</td>
                                <td>{{ $factura->data_factura?->format('d.m.Y') }}</td>
                                <td>{{ $factura->data_scadenta?->format('d.m.Y') }}</td>
                                <td class="text-end">{{ number_format($factura->suma, 2) }}</td>
                                <td>{{ $factura->moneda }}</td>
                                <td>{{ $factura->departament_vehicul }}</td>
                                <td><span class="badge bg-secondary text-uppercase">{{ $factura->status }}</span></td>
                                <td>
                                    @if ($factura->calupuri->isNotEmpty())
                                        @foreach ($factura->calupuri as $calup)
                                            <a href="{{ route('facturi-furnizori.plati-calupuri.show', $calup) }}" class="badge bg-info text-dark text-decoration-none mb-1">{{ $calup->denumire_calup }}</a>
                                        @endforeach
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-muted">{{ \Illuminate\Support\Str::limit($factura->observatii, 60) }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('facturi-furnizori.facturi.show', $factura) }}" class="btn btn-outline-secondary">Vezi</a>
                                        <a href="{{ route('facturi-furnizori.facturi.edit', $factura) }}" class="btn btn-outline-primary">Editeaza</a>
                                        <form action="{{ route('facturi-furnizori.facturi.destroy', $factura) }}" method="POST" onsubmit="return confirm('Stergi aceasta factura?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">Sterge</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">Nu exista facturi inregistrate.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    {{ $facturi->appends(request()->except('page'))->links() }}
                </div>
                <div>
                    <button type="button" class="btn btn-primary" id="prepare-calup">Pregateste calup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<form method="GET" action="{{ route('facturi-furnizori.plati-calupuri.create') }}" id="create-calup-form" class="d-none"></form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const selectAll = document.getElementById('select-all');
        const checkboxItems = Array.from(document.querySelectorAll('.select-factura'));
        const prepareButton = document.getElementById('prepare-calup');
        const form = document.getElementById('create-calup-form');

        if (selectAll) {
            selectAll.addEventListener('change', () => {
                checkboxItems.forEach(checkbox => {
                    if (!checkbox.disabled) {
                        checkbox.checked = selectAll.checked;
                    }
                });
            });
        }

        if (prepareButton) {
            prepareButton.addEventListener('click', () => {
                const selected = checkboxItems.filter(checkbox => checkbox.checked && !checkbox.disabled).map(item => item.value);

                if (!selected.length) {
                    alert('Selectati cel putin o factura neplatita.');
                    return;
                }

                form.innerHTML = '';
                selected.forEach(value => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'facturi[]';
                    input.value = value;
                    form.appendChild(input);
                });

                form.submit();
            });
        }
    });
</script>
@endsection




