@extends('layouts.app')

@php
    use Carbon\Carbon;
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-2 mb-2">
            <span class="badge culoare1 fs-5">
                <span class="d-inline-flex flex-column align-items-start gap-1 lh-1">
                    <span><i class="fa-solid fa-file-invoice me-1"></i>Facturi</span>
                    <span class="ms-4">transportatori</span>
                </span>
            </span>
        </div>
        <div class="col-lg-7 mb-0">
            <form class="needs-validation mb-lg-0" novalidate method="GET" action="{{ url()->current() }}">
                <div class="row gy-1 gx-3 mb-2 custom-search-form d-flex justify-content-center align-items-end">
                    <div class="col-lg-4 col-md-6">
                        <input
                            type="text"
                            class="form-control rounded-3"
                            id="filter-transportator"
                            name="transportator"
                            placeholder="Transportator"
                            value="{{ $filters['transportator'] }}"
                        >
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <input
                            type="text"
                            class="form-control rounded-3"
                            id="filter-comanda"
                            name="comanda"
                            placeholder="Nr. comanda"
                            value="{{ $filters['comanda'] }}"
                        >
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="filter-scadenta-de-la" class="form-label ps-2 small text-muted mb-0">Scadenta de la</label>
                        <input type="date" class="form-control rounded-3" id="filter-scadenta-de-la" name="scadenta_de_la" value="{{ $filters['scadenta_de_la'] }}">
                    </div>
                    <div class="col-lg-2 col-md-6">
                        <label for="filter-scadenta-pana" class="form-label ps-2 small text-muted mb-0">Scadenta pana la</label>
                        <input type="date" class="form-control rounded-3" id="filter-scadenta-pana" name="scadenta_pana" value="{{ $filters['scadenta_pana'] }}">
                    </div>
                    <div class="col-lg-1 col-md-6">
                        <label for="filter-are-pdf" class="form-label ps-2 small text-muted mb-0">PDF</label>
                        <select name="are_pdf" id="filter-are-pdf" class="form-select bg-white rounded-3">
                            <option value="" @selected($filters['are_pdf'] === null)>Toate</option>
                            <option value="da" @selected($filters['are_pdf'] === 'da')>Da</option>
                            <option value="nu" @selected($filters['are_pdf'] === 'nu')>Nu</option>
                        </select>
                    </div>
                </div>
                <div class="row custom-search-form justify-content-center">
                    <button class="btn btn-sm btn-primary text-white col-md-4 me-3 border border-dark rounded-3" type="submit">
                        <i class="fas fa-search text-white me-1"></i>Cauta
                    </button>
                    <a class="btn btn-sm btn-secondary text-white col-md-4 border border-dark rounded-3" href="{{ route('facturi-transportatori.index') }}" role="button">
                        <i class="far fa-trash-alt text-white me-1"></i>Reseteaza
                    </a>
                </div>
            </form>
        </div>
        <div class="col-lg-3 text-lg-end mt-3 mt-lg-0"></div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="table-responsive rounded">
            <table class="table table-sm table-striped table-hover rounded align-middle">
                <thead class="text-white rounded culoare2">
                    <tr>
                        <th>#</th>
                        <th>Transportator</th>
                        <th>Comanda</th>
                        <th>Factura transportator</th>
                        <th>Data comanda</th>
                        <th>Ultima descarcare</th>
                        <th>Data factura</th>
                        <th>Scadenta factura</th>
                        <th class="text-end">Suma</th>
                        <th>PDF factura</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($comenzi as $comanda)
                        @php
                            $ultimaDescarcare = $comanda->locuriOperareDescarcari->sortByDesc('pivot.data_ora')->first();
                        @endphp
                        <tr>
                            <td>
                                {{ ($comenzi->currentPage() - 1) * $comenzi->perPage() + $loop->index + 1 }}
                            </td>
                            <td>{{ $comanda->transportator->nume ?? '' }}</td>
                            <td>
                                <div>{{ $comanda->transportator_contract }}</div>
                                <a href="{{ url('/facturi-memento/deschide/comanda/' . $comanda->id) }}" class="small">
                                    Deschide memento
                                </a>
                            </td>
                            <td>{{ $comanda->factura_transportator }}</td>
                            <td>{{ $comanda->data_creare ? Carbon::parse($comanda->data_creare)->isoFormat('DD.MM.YYYY') : '' }}</td>
                            <td>{{ $ultimaDescarcare && $ultimaDescarcare->pivot->data_ora ? Carbon::parse($ultimaDescarcare->pivot->data_ora)->isoFormat('DD.MM.YYYY') : '' }}</td>
                            <td>{{ $comanda->data_factura_transportator ? Carbon::parse($comanda->data_factura_transportator)->isoFormat('DD.MM.YYYY') : '' }}</td>
                            <td>{{ $comanda->data_scadenta_plata_transportator ? Carbon::parse($comanda->data_scadenta_plata_transportator)->isoFormat('DD.MM.YYYY') : '' }}</td>
                            <td class="text-end">
                                @if (!is_null($comanda->transportator_valoare_contract))
                                    {{ rtrim(rtrim(number_format((float) $comanda->transportator_valoare_contract, 2, '.', ''), '0'), '.') }}
                                    {{ $comanda->transportatorMoneda->nume ?? '' }}
                                @endif
                            </td>
                            <td>
                                @forelse ($comanda->facturiIncarcateDeTransportator as $fisierFactura)
                                    <div class="mb-1">
                                        <a
                                            href="{{ url('/comanda-incarcare-documente-de-catre-transportator/' . $comanda->cheie_unica . '/deschide/' . $fisierFactura->nume) }}"
                                            target="_blank"
                                            rel="noopener"
                                        >
                                            Vezi PDF {{ $loop->iteration }}
                                        </a>
                                        <span class="text-muted">|</span>
                                        <a href="{{ url('/comanda-incarcare-documente-de-catre-transportator/' . $comanda->cheie_unica . '/descarca/' . $fisierFactura->nume) }}">
                                            Descarca
                                        </a>
                                    </div>
                                @empty
                                    <span class="text-muted">Fara PDF</span>
                                @endforelse
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                Nu exista facturi de transportatori care sa corespunda filtrelor selectate.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="d-flex justify-content-center">
            {{ $comenzi->appends(Request::except('page'))->links() }}
        </div>
    </div>
</div>
@endsection
