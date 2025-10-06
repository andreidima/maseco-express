@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-3">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-file-invoice-dollar me-1"></i>Facturi furnizori
            </span>
        </div>
        <div class="col-lg-9 text-lg-end mt-3 mt-lg-0">
            <a class="btn btn-sm btn-secondary text-white border border-dark rounded-3 me-2" href="{{ route('facturi-furnizori.plati-calupuri.index') }}">
                <i class="fa-solid fa-layer-group me-1"></i>Calupuri plăți
            </a>
            <a class="btn btn-sm btn-success text-white border border-dark rounded-3" href="{{ route('facturi-furnizori.facturi.create') }}">
                <i class="fa-solid fa-plus me-1"></i>Adaugă factură
            </a>
        </div>
        <div class="col-12 mt-3">
            <form method="GET" action="{{ url()->current() }}" class="row g-2 g-md-3 align-items-end">
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="filter-status" class="mb-0 ps-2">Status</label>
                    <select name="status" id="filter-status" class="form-select bg-white rounded-3">
                        <option value="">Toate</option>
                        @foreach ($statusOptions as $key => $label)
                            <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="filter-furnizor" class="mb-0 ps-2">Furnizor</label>
                    <input type="text" name="furnizor" id="filter-furnizor" class="form-control bg-white rounded-3" value="{{ $filters['furnizor'] }}">
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="filter-departament" class="mb-0 ps-2">Departament</label>
                    <input type="text" name="departament" id="filter-departament" class="form-control bg-white rounded-3" value="{{ $filters['departament'] }}">
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="filter-moneda" class="mb-0 ps-2">Monedă</label>
                    <select name="moneda" id="filter-moneda" class="form-select bg-white rounded-3">
                        <option value="">Toate</option>
                        @foreach ($monede as $moneda)
                            <option value="{{ $moneda }}" @selected($filters['moneda'] === $moneda)>{{ $moneda }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="filter-scadenta-de-la" class="mb-0 ps-2">Scadență de la</label>
                    <input type="date" name="scadenta_de_la" id="filter-scadenta-de-la" class="form-control bg-white rounded-3" value="{{ $filters['scadenta_de_la'] }}">
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="filter-scadenta-pana" class="mb-0 ps-2">Scadență până la</label>
                    <input type="date" name="scadenta_pana" id="filter-scadenta-pana" class="form-control bg-white rounded-3" value="{{ $filters['scadenta_pana'] }}">
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="filter-scadente-in-zile" class="mb-0 ps-2">Scadente în (zile)</label>
                    <input type="number" name="scadente_in_zile" id="filter-scadente-in-zile" min="0" class="form-control bg-white rounded-3" value="{{ $filters['scadente_in_zile'] }}">
                </div>
                <div class="col-12 col-md-6 col-xl-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary text-white border border-dark rounded-3 flex-fill">
                        <i class="fa-solid fa-filter me-1"></i>Filtrează
                    </button>
                    <a href="{{ route('facturi-furnizori.facturi.index') }}" class="btn btn-sm btn-secondary text-white border border-dark rounded-3 flex-fill">
                        <i class="fa-solid fa-rotate-left me-1"></i>Resetează
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="px-3">
            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach ($statusOptions as $statusKey => $statusLabel)
                    <span class="badge bg-white border border-dark rounded-pill text-dark d-flex align-items-center gap-2 py-2 px-3">
                        <span class="text-uppercase text-muted small">{{ $statusLabel }}</span>
                        <span class="fw-bold">{{ $statusCounts[$statusKey] ?? 0 }}</span>
                    </span>
                @endforeach
            </div>
        </div>

        <div class="table-responsive rounded mb-3 px-3">
            <table class="table table-sm table-striped table-hover table-bordered border-dark align-middle">
                <thead class="text-white rounded culoare2">
                    <tr>
                        <th class="text-center"><input type="checkbox" id="select-all" title="Selectează toate"></th>
                        <th>Furnizor</th>
                        <th>Număr</th>
                        <th>Data</th>
                        <th>Scadență</th>
                        <th class="text-end">Sumă</th>
                        <th>Monedă</th>
                        <th>Departament</th>
                        <th>Status</th>
                        <th>Calup</th>
                        <th>Observații</th>
                        <th class="text-end">Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($facturi as $factura)
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" class="select-factura" value="{{ $factura->id }}" @disabled($factura->status !== \App\Models\FacturiFurnizori\FacturaFurnizor::STATUS_NEPLATITA)>
                            </td>
                            <td>{{ $factura->denumire_furnizor }}</td>
                            <td>{{ $factura->numar_factura }}</td>
                            <td>{{ $factura->data_factura?->format('d.m.Y') }}</td>
                            <td>{{ $factura->data_scadenta?->format('d.m.Y') }}</td>
                            <td class="text-end">{{ number_format($factura->suma, 2) }}</td>
                            <td>{{ $factura->moneda }}</td>
                            <td>{{ $factura->departament_vehicul }}</td>
                            <td>
                                <span class="badge bg-white border border-dark rounded-pill text-dark fw-normal">
                                    <small>{{ $statusOptions[$factura->status] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $factura->status)) }}</small>
                                </span>
                            </td>
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
                                <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                    <a href="{{ route('facturi-furnizori.facturi.show', $factura) }}" class="badge bg-secondary text-dark text-decoration-none rounded-3 px-3 py-2">Vezi</a>
                                    <a href="{{ route('facturi-furnizori.facturi.edit', $factura) }}" class="badge bg-primary text-white text-decoration-none rounded-3 px-3 py-2">Editează</a>
                                    <form action="{{ route('facturi-furnizori.facturi.destroy', $factura) }}" method="POST" class="m-0" onsubmit="return confirm('Ștergi această factură?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="badge bg-danger text-white border-0 rounded-3 px-3 py-2">Șterge</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted py-4">Nu există facturi înregistrate.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-column flex-lg-row justify-content-lg-between align-items-lg-center gap-3 px-3">
            <div>
                {{ $facturi->appends(request()->except('page'))->links() }}
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-primary text-white border border-dark rounded-3" id="prepare-calup">
                    <i class="fa-solid fa-file-circle-plus me-1"></i>Pregătește calup
                </button>
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
                const selected = checkboxItems
                    .filter(checkbox => checkbox.checked && !checkbox.disabled)
                    .map(item => item.value);

                if (!selected.length) {
                    alert('Selectați cel puțin o factură neplătită.');
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
