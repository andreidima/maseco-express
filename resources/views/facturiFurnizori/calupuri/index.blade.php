@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card mx-2" style="border-radius: 30px;">
        <div class="card-header d-flex justify-content-between align-items-center" style="border-radius: 30px 30px 0 0;">
            <span class="badge bg-info fs-5">Calupuri plati</span>
            <div>
                <a href="{{ route('facturi-furnizori.facturi.index') }}" class="btn btn-outline-secondary btn-sm me-2">Inapoi la facturi</a>
                <a href="{{ route('facturi-furnizori.plati-calupuri.create') }}" class="btn btn-primary btn-sm">Creeaza calup</a>
            </div>
        </div>
        <div class="card-body">
            @include('errors')

            <form method="GET" class="row g-2 align-items-end mb-4">
                <div class="col-md-3">
                    <label for="filter-status" class="form-label">Status</label>
                    <select name="status" id="filter-status" class="form-select">
                        <option value="">Toate</option>
                        @foreach ($statusOptions as $key => $label)
                            <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter-data-de" class="form-label">Data plata de la</label>
                    <input type="date" name="data_plata_de_la" id="filter-data-de" class="form-control" value="{{ $filters['data_plata_de_la'] }}">
                </div>
                <div class="col-md-3">
                    <label for="filter-data-pana" class="form-label">Data plata pana</label>
                    <input type="date" name="data_plata_pana" id="filter-data-pana" class="form-control" value="{{ $filters['data_plata_pana'] }}">
                </div>
                <div class="col-md-3">
                    <label for="filter-cauta" class="form-label">Cauta</label>
                    <input type="text" name="cauta" id="filter-cauta" class="form-control" value="{{ $filters['cauta'] }}" placeholder="Denumire sau observatii">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">Filtreaza</button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('facturi-furnizori.plati-calupuri.index') }}" class="btn btn-link w-100">Reseteaza</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Denumire</th>
                            <th>Status</th>
                            <th>Data plata</th>
                            <th>Facturi</th>
                            <th>Observatii</th>
                            <th class="text-end">Actiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($calupuri as $calup)
                            <tr>
                                <td>{{ $calup->denumire_calup }}</td>
                                <td><span class="badge bg-secondary text-uppercase">{{ $calup->status }}</span></td>
                                <td>{{ $calup->data_plata?->format('d.m.Y') ?: '-' }}</td>
                                <td>{{ $calup->facturi_count }}</td>
                                <td class="text-muted">{{ \Illuminate\Support\Str::limit($calup->observatii, 60) }}</td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('facturi-furnizori.plati-calupuri.show', $calup) }}" class="btn btn-outline-secondary">Vezi</a>
                                        <form action="{{ route('facturi-furnizori.plati-calupuri.destroy', $calup) }}" method="POST" onsubmit="return confirm('Stergi acest calup?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger">Sterge</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Nu exista calupuri inregistrate.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $calupuri->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
