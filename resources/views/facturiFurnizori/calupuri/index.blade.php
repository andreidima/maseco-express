@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-3">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-layer-group me-1"></i>Calupuri plăți
            </span>
        </div>
        <div class="col-lg-9 text-lg-end mt-3 mt-lg-0">
            <a class="btn btn-sm btn-secondary text-white border border-dark rounded-3 me-2" href="{{ route('facturi-furnizori.facturi.index') }}">
                <i class="fa-solid fa-rotate-left me-1"></i>Înapoi la facturi
            </a>
            <a class="btn btn-sm btn-primary text-white border border-dark rounded-3" href="{{ route('facturi-furnizori.plati-calupuri.create') }}">
                <i class="fa-solid fa-plus me-1"></i>Creează calup
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
                    <label for="filter-data-de" class="mb-0 ps-2">Data plată de la</label>
                    <input type="date" name="data_plata_de_la" id="filter-data-de" class="form-control bg-white rounded-3" value="{{ $filters['data_plata_de_la'] }}">
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="filter-data-pana" class="mb-0 ps-2">Data plată până la</label>
                    <input type="date" name="data_plata_pana" id="filter-data-pana" class="form-control bg-white rounded-3" value="{{ $filters['data_plata_pana'] }}">
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <label for="filter-cauta" class="mb-0 ps-2">Caută</label>
                    <input type="text" name="cauta" id="filter-cauta" class="form-control bg-white rounded-3" placeholder="Denumire sau observații" value="{{ $filters['cauta'] }}">
                </div>
                <div class="col-12 col-md-6 col-xl-3 d-flex gap-2">
                    <button type="submit" class="btn btn-sm btn-primary text-white border border-dark rounded-3 flex-fill">
                        <i class="fa-solid fa-filter me-1"></i>Filtrează
                    </button>
                    <a href="{{ route('facturi-furnizori.plati-calupuri.index') }}" class="btn btn-sm btn-secondary text-white border border-dark rounded-3 flex-fill">
                        <i class="fa-solid fa-rotate-left me-1"></i>Resetează
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="table-responsive rounded mb-3 px-3">
            <table class="table table-sm table-striped table-hover table-bordered border-dark align-middle">
                <thead class="text-white rounded culoare2">
                    <tr>
                        <th>Denumire</th>
                        <th>Status</th>
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
                            <td>
                                <span class="badge bg-white border border-dark rounded-pill text-dark fw-normal">
                                    <small>{{ $statusOptions[$calup->status] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $calup->status)) }}</small>
                                </span>
                            </td>
                            <td>{{ $calup->data_plata?->format('d.m.Y') ?: '-' }}</td>
                            <td class="text-center">{{ $calup->facturi_count }}</td>
                            <td class="text-muted">{{ \Illuminate\Support\Str::limit($calup->observatii, 60) }}</td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end align-items-center gap-2 flex-wrap">
                                    <a href="{{ route('facturi-furnizori.plati-calupuri.show', $calup) }}" class="badge bg-secondary text-dark text-decoration-none rounded-3 px-3 py-2">Vezi</a>
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
                            <td colspan="6" class="text-center text-muted py-4">Nu există calupuri înregistrate.</td>
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
