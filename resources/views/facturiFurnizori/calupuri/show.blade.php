@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-layer-group me-1"></i>Calup: {{ $calup->denumire_calup }}
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
                    <p class="mb-1"><strong>Facturi atașate:</strong> {{ $facturiCalup->count() }}</p>
                    <p class="mb-1"><strong>Data plată:</strong> {{ $calup->data_plata?->format('d.m.Y') ?: 'Nespecificată' }}</p>
                    <p class="mb-0"><strong>Fișiere PDF:</strong> {{ $calup->fisiere->count() }}</p>
                </div>
            </div>

            <div class="mb-3">
                <div class="border border-dark rounded-3 p-3 bg-white">
                    <h6 class="text-uppercase text-muted">Fișiere PDF atașate</h6>
                    @if ($calup->fisiere->isEmpty())
                        <p class="text-muted mb-0">Nu există fișiere atașate acestui calup.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-bordered border-dark align-middle mb-0">
                                <thead class="text-white rounded culoare2">
                                    <tr>
                                        <th>Nume fișier</th>
                                        <th>Încărcat la</th>
                                        <th class="text-end">Acțiuni</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($calup->fisiere as $fisier)
                                        <tr>
                                            <td class="align-middle">{{ $fisier->nume_original ?: basename($fisier->cale) }}</td>
                                            <td class="align-middle">{{ $fisier->created_at?->format('d.m.Y H:i') }}</td>
                                            <td class="text-end">
                                                <a
                                                    href="{{ route('facturi-furnizori.plati-calupuri.descarca-fisier', [$calup, $fisier]) }}"
                                                    class="btn btn-sm btn-outline-primary border border-primary me-2"
                                                >
                                                    <i class="fa-solid fa-download me-1"></i>Descarcă
                                                </a>
                                                <form
                                                    action="{{ route('facturi-furnizori.plati-calupuri.fisiere.destroy', [$calup, $fisier]) }}"
                                                    method="POST"
                                                    class="d-inline"
                                                    onsubmit="return confirm('Ștergi acest fișier PDF?');"
                                                >
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border border-danger">
                                                        <i class="fa-solid fa-trash me-1"></i>Șterge
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <form action="{{ route('facturi-furnizori.plati-calupuri.update', $calup) }}" method="POST" enctype="multipart/form-data" class="border border-dark rounded-3 p-3 mb-3 bg-white">
                @csrf
                @method('PUT')
                @include('facturiFurnizori.calupuri._form', ['calup' => $calup])
                <div class="d-flex justify-content-end">
                    <button type="submit" class="btn btn-sm btn-primary text-white border border-dark rounded-3">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Actualizează calup
                    </button>
                </div>
            </form>

            <div class="border border-dark rounded-3 p-3 mb-3 bg-white">
                <h6 class="text-uppercase text-muted mb-3">Facturi atașate</h6>
                @if ($facturiCalup->isEmpty())
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
                                    <th class="text-end">Acțiuni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($facturiCalup as $factura)
                                    <tr>
                                        <td>{{ $factura->denumire_furnizor }}</td>
                                        <td>{{ $factura->numar_factura }}</td>
                                        <td>{{ $factura->data_scadenta?->format('d.m.Y') }}</td>
                                        <td class="text-end">{{ number_format($factura->suma, 2) }} {{ $factura->moneda }}</td>
                                        <td class="text-end">
                                            <button
                                                type="button"
                                                class="badge bg-danger text-white border-0 rounded-3 px-3 py-2"
                                                data-bs-toggle="modal"
                                                data-bs-target="#detaseazaFacturaModal{{ $factura->id }}"
                                            >
                                                Elimină
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @foreach ($facturiCalup as $factura)
                <div
                    class="modal fade"
                    id="detaseazaFacturaModal{{ $factura->id }}"
                    tabindex="-1"
                    aria-labelledby="detaseazaFacturaModalLabel{{ $factura->id }}"
                    aria-hidden="true"
                >
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="detaseazaFacturaModalLabel{{ $factura->id }}">
                                    Elimină factura din calup
                                </h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-start">
                                Sigur dorești să elimini factura <strong>{{ $factura->numar_factura }}</strong>
                                de la <strong>{{ $factura->denumire_furnizor }}</strong> din acest calup?
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                                <form action="{{ route('facturi-furnizori.plati-calupuri.detaseaza-factura', [$calup, $factura]) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger">Elimină factura</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="border border-dark rounded-3 p-3 bg-white">
                <h6 class="text-uppercase text-muted mb-3">Adaugă facturi în calup</h6>
                @if ($facturiDisponibile->isEmpty())
                    <p class="text-muted mb-0">Nu există facturi disponibile.</p>
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
                            <span class="badge bg-primary text-white" id="attach-counter">Selectate: 0</span>
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
