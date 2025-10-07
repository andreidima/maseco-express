@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-2 mb-2">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-file-invoice-dollar me-1"></i>Facturi furnizori
            </span>
        </div>
        <div class="col-lg-8 mb-0" id="formularFacturi">
            <form class="needs-validation mb-lg-0" novalidate method="GET" action="{{ url()->current() }}">
                @csrf
                <div class="row mb-1 custom-search-form d-flex justify-content-center">
                    <div class="col-lg-3">
                        <select name="status" id="filter-status" class="form-select bg-white rounded-3">
                            <option value="">Status</option>
                            @foreach ($statusOptions as $key => $label)
                                <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3">
                        <input type="text" class="form-control rounded-3" id="filter-furnizor" name="furnizor" placeholder="Furnizor" value="{{ $filters['furnizor'] }}">
                    </div>
                    <div class="col-lg-3">
                        <input type="text" class="form-control rounded-3" id="filter-departament" name="departament" placeholder="Departament" value="{{ $filters['departament'] }}">
                    </div>
                    <div class="col-lg-3">
                        <select name="moneda" id="filter-moneda" class="form-select bg-white rounded-3">
                            <option value="">Monedă</option>
                            @foreach ($monede as $moneda)
                                <option value="{{ $moneda }}" @selected($filters['moneda'] === $moneda)>{{ $moneda }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row mb-1 custom-search-form d-flex justify-content-center">
                    <div class="col-lg-3">
                        <input type="date" class="form-control rounded-3" id="filter-scadenta-de-la" name="scadenta_de_la" placeholder="Scadență de la" value="{{ $filters['scadenta_de_la'] }}">
                    </div>
                    <div class="col-lg-3">
                        <input type="date" class="form-control rounded-3" id="filter-scadenta-pana" name="scadenta_pana" placeholder="Scadență până la" value="{{ $filters['scadenta_pana'] }}">
                    </div>
                    <div class="col-lg-3">
                        <input type="number" min="0" class="form-control rounded-3" id="filter-scadente-in-zile" name="scadente_in_zile" placeholder="Scadente în (zile)" value="{{ $filters['scadente_in_zile'] }}">
                    </div>
                    <div class="col-lg-3"></div>
                </div>
                <div class="row custom-search-form justify-content-center">
                    <button class="btn btn-sm btn-primary text-white col-md-4 me-3 border border-dark rounded-3" type="submit">
                        <i class="fas fa-search text-white me-1"></i>Caută
                    </button>
                    <a class="btn btn-sm btn-secondary text-white col-md-4 border border-dark rounded-3" href="{{ route('facturi-furnizori.facturi.index') }}" role="button">
                        <i class="far fa-trash-alt text-white me-1"></i>Resetează
                    </a>
                </div>
            </form>
        </div>
        <div class="col-lg-2 text-lg-end">
            <a class="btn btn-sm btn-secondary text-white border border-dark rounded-3 me-2" href="{{ route('facturi-furnizori.plati-calupuri.index') }}" role="button">
                <i class="fa-solid fa-layer-group me-1"></i>Calupuri plăți
            </a>
            <a class="btn btn-sm btn-success text-white border border-dark rounded-3" href="{{ route('facturi-furnizori.facturi.create') }}" role="button">
                <i class="fas fa-plus-square text-white me-1"></i>Adaugă factură
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="px-3">
            <div class="d-flex flex-wrap gap-2 mb-3">
                @foreach ($statusOptions as $statusKey => $statusLabel)
                    <span class="badge bg-white border border-dark rounded-pill text-dark d-flex align-items-center gap-2 py-2 px-3">
                        <span class="text-uppercase small">{{ $statusLabel }}</span>
                        <span class="badge bg-dark text-white">{{ $statusCounts[$statusKey] ?? 0 }}</span>
                    </span>
                @endforeach
            </div>
        </div>

        <div class="table-responsive rounded">
            <table class="table table-sm table-striped table-hover rounded align-middle">
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
                                <span class="badge bg-white border border-dark text-dark rounded-pill px-3 py-2">
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
                                    <a href="{{ route('facturi-furnizori.facturi.show', $factura) }}" class="btn btn-sm btn-secondary text-white border border-dark rounded-3">
                                        <i class="fa-solid fa-eye me-1"></i>Vezi
                                    </a>
                                    <a href="{{ route('facturi-furnizori.facturi.edit', $factura) }}" class="btn btn-sm btn-primary text-white border border-dark rounded-3">
                                        <i class="fa-solid fa-pen-to-square me-1"></i>Editează
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger text-white border border-dark rounded-3" data-bs-toggle="modal" data-bs-target="#stergeFactura{{ $factura->id }}">
                                        <i class="fa-solid fa-trash-can me-1"></i>Șterge
                                    </button>
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
                <button type="button" class="btn btn-sm btn-success text-white border border-dark rounded-3" id="prepare-calup">
                    <i class="fa-solid fa-file-circle-plus me-1"></i>Pregătește calup
                </button>
            </div>
        </div>
    </div>
</div>

<form method="GET" action="{{ route('facturi-furnizori.plati-calupuri.create') }}" id="create-calup-form" class="d-none"></form>

@foreach ($facturi as $factura)
    <div class="modal fade text-dark" id="stergeFactura{{ $factura->id }}" tabindex="-1" role="dialog" aria-labelledby="stergeFacturaLabel{{ $factura->id }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="stergeFacturaLabel{{ $factura->id }}">Factura {{ $factura->numar_factura }}</h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Sigur ștergi factura <strong>{{ $factura->numar_factura }}</strong> de la <strong>{{ $factura->denumire_furnizor }}</strong>?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                    <form action="{{ route('facturi-furnizori.facturi.destroy', $factura) }}" method="POST" class="m-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Șterge factura</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

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
