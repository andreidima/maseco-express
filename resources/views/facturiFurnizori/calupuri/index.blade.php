@extends('layouts.app')

@php
    $facturiIndexUrl = \App\Support\FacturiFurnizori\FacturiIndexFilterState::route();
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-2 mb-2">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-layer-group me-1"></i>Calupuri plăți
            </span>
        </div>
        <div class="col-lg-8 mb-0" id="formularCalupuri">
            <form class="needs-validation mb-lg-0" novalidate method="GET" action="{{ url()->current() }}">
                <div class="row gy-1 gx-4 mb-2 custom-search-form d-flex justify-content-center">
                    <div class="col-lg-4 col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <label for="filter-data-de" class="form-label small text-muted mb-0 flex-shrink-0 text-nowrap">Data plată de la</label>
                            <input
                                type="date"
                                class="form-control rounded-3"
                                id="filter-data-de"
                                name="data_plata_de_la"
                                value="{{ $filters['data_plata_de_la'] }}"
                            >
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <label for="filter-data-pana" class="form-label small text-muted mb-0 flex-shrink-0 text-nowrap">Data plată până la</label>
                            <input
                                type="date"
                                class="form-control rounded-3"
                                id="filter-data-pana"
                                name="data_plata_pana"
                                value="{{ $filters['data_plata_pana'] }}"
                            >
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid fa-magnifying-glass text-muted"></i>
                            <input
                                type="text"
                                class="form-control rounded-3 flex-grow-1"
                                id="filter-cauta"
                                name="cauta"
                                placeholder="Denumire sau observații"
                                value="{{ $filters['cauta'] }}"
                                aria-label="Caută calupuri"
                            >
                        </div>
                    </div>
                </div>
                <div class="row custom-search-form justify-content-center">
                    <button class="btn btn-sm btn-primary text-white col-md-4 me-3 border border-dark rounded-3" type="submit">
                        <i class="fas fa-search text-white me-1"></i>Caută
                    </button>
                    <a class="btn btn-sm btn-secondary text-white col-md-4 border border-dark rounded-3" href="{{ route('facturi-furnizori.plati-calupuri.index') }}" role="button">
                        <i class="far fa-trash-alt text-white me-1"></i>Resetează
                    </a>
                </div>
            </form>
        </div>
        <div class="col-lg-2 text-lg-end mt-3 mt-lg-0">
            <div class="d-flex flex-column align-items-stretch align-items-lg-end gap-2">
                <a class="btn btn-sm btn-secondary text-white border border-dark rounded-3" href="{{ $facturiIndexUrl }}">
                    <i class="fa-solid fa-rotate-left me-1"></i>Înapoi la facturi
                </a>
                {{-- <a class="btn btn-sm btn-primary text-white border border-dark rounded-3" href="{{ route('facturi-furnizori.plati-calupuri.create') }}">
                    <i class="fa-solid fa-plus me-1"></i>Creează calup
                </a> --}}
            </div>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="table-responsive rounded">
            <table class="table table-sm table-striped table-hover rounded align-middle">
                <thead class="text-white rounded culoare2">
                    <tr>
                        <th>Denumire</th>
                        <th>Data plată</th>
                        <th class="text-center">Facturi</th>
                        <th>Observații</th>
                        <th class="text-end">Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($calupuri as $calup)
                        <tr>
                            <td>{{ $calup->denumire_calup }}</td>
                            <td>{{ $calup->data_plata?->format('d.m.Y') ?: '-' }}</td>
                            <td class="text-center">{{ $calup->facturi_count }}</td>
                            <td class="text-muted">{{ \Illuminate\Support\Str::limit($calup->observatii, 60) }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                    <a href="{{ route('facturi-furnizori.plati-calupuri.show', $calup) }}" class="badge bg-success text-white text-decoration-none rounded-3 px-3 py-2">Vezi</a>
                                    <form action="{{ route('facturi-furnizori.plati-calupuri.destroy', $calup) }}" method="POST" class="m-0" onsubmit="return confirm('Ștergi acest calup?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="badge bg-danger text-white border-0 rounded-3 px-3 py-2">Șterge</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Nu există calupuri înregistrate.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-3">
            {{ $calupuri->appends(request()->except('page'))->links() }}
        </div>
    </div>
</div>
@endsection
