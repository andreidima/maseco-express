@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-calendar-check me-1"></i>Valabilitate
            </span>
        </div>
        <div class="col-lg-6 text-lg-end mt-3 mt-lg-0">
            <div class="d-flex flex-column flex-lg-row justify-content-lg-end align-items-stretch align-items-lg-center gap-2">
                <a href="{{ $backUrl }}" class="btn btn-sm btn-secondary text-white border border-dark rounded-3">
                    <i class="fas fa-arrow-left text-white me-1"></i>Înapoi
                </a>
                <a
                    href="{{ route('valabilitati.edit', $valabilitate) }}"
                    class="btn btn-sm btn-primary text-white border border-dark rounded-3"
                >
                    <i class="fa-solid fa-pen-to-square text-white me-1"></i>Modifică
                </a>
                <a
                    href="#"
                    class="btn btn-sm btn-danger text-white border border-dark rounded-3"
                    data-valabilitate-delete
                    data-delete-url="{{ route('valabilitati.destroy', $valabilitate) }}"
                    data-delete-divizie="{{ $valabilitate->divizie->nume ?? '' }}"
                    data-delete-numar-auto="{{ $valabilitate->numar_auto }}"
                >
                    <i class="fa-solid fa-trash-can text-white me-1"></i>Șterge
                </a>
            </div>
        </div>
    </div>

    <div class="card-body py-4">
        @include('errors')

        @if (session('status'))
            <div class="alert alert-success" role="alert">
                {{ session('status') }}
            </div>
        @endif

        <div class="row g-4">
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100">
                    <h6 class="text-muted text-uppercase mb-2">Informații generale</h6>
                    <p class="mb-1"><strong>Divizie:</strong> {{ $valabilitate->divizie->nume ?? '—' }}</p>
                    <p class="mb-1"><strong>Număr auto:</strong> {{ $valabilitate->numar_auto }}</p>
                    <p class="mb-1">
                        <strong>KM plecare:</strong>
                        {{ $valabilitate->km_plecare !== null ? number_format((int) $valabilitate->km_plecare, 0, ',', '.') : '—' }}
                    </p>
                    <p class="mb-1">
                        <strong>KM sosire:</strong>
                        {{ $valabilitate->km_sosire !== null ? number_format((int) $valabilitate->km_sosire, 0, ',', '.') : '—' }}
                    </p>
                    <p class="mb-0"><strong>Șofer:</strong> {{ $valabilitate->sofer->name ?? '—' }}</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded-3 p-3 h-100">
                    <h6 class="text-muted text-uppercase mb-2">Perioadă</h6>
                    <p class="mb-1">
                        <strong>Început:</strong>
                        {{ optional($valabilitate->data_inceput)->format('d.m.Y') ?? '—' }}
                    </p>
                    <p class="mb-1">
                        <strong>Sfârșit:</strong>
                        {{ optional($valabilitate->data_sfarsit)->format('d.m.Y') ?? '—' }}
                    </p>
                    @php($azi = now()->startOfDay())
                    @php($isActive = is_null($valabilitate->data_sfarsit) || optional($valabilitate->data_sfarsit)->greaterThanOrEqualTo($azi))
                    <p class="mb-0">
                        <strong>Status:</strong>
                        <span class="badge {{ $isActive ? 'bg-success' : 'bg-secondary' }}">
                            {{ $isActive ? 'Activă' : 'Expirată' }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <div class="border rounded-3 p-3">
                <h6 class="text-muted text-uppercase mb-2">Taxe de drum</h6>

                @if ($valabilitate->taxeDrum->isEmpty())
                    <p class="mb-0 text-muted">Nu există taxe de drum înregistrate.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Nume</th>
                                    <th>Țară</th>
                                    <th class="text-end">Sumă</th>
                                    <th>Monedă</th>
                                    <th>Dată</th>
                                    <th>Observații</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($valabilitate->taxeDrum as $taxa)
                                    <tr>
                                        <td>{{ $taxa->nume ?? '—' }}</td>
                                        <td>{{ $taxa->tara ?: '—' }}</td>
                                        <td class="text-end">
                                            {{ $taxa->suma !== null ? number_format((float) $taxa->suma, 2, ',', '.') : '—' }}
                                        </td>
                                        <td>{{ $taxa->moneda ?: '—' }}</td>
                                        <td>{{ optional($taxa->data)->format('d.m.Y') ?? '—' }}</td>
                                        <td>{{ $taxa->observatii ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@include('valabilitati.partials.delete-modal')
@endsection
