@extends('layouts.app')

@section('content')
<div class="container py-4 py-md-5 sofer-valabilitati">
    <div class="mb-4">
        <a href="{{ route('sofer.dashboard') }}" class="btn btn-link text-decoration-none px-0">
            <i class="fa-solid fa-arrow-left-long me-1"></i>
            Înapoi la panou
        </a>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Închide"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <p class="text-uppercase text-muted small fw-semibold mb-1">Valabilitate activă</p>
                    <h1 class="h4 fw-bold mb-1">{{ $valabilitate->denumire }}</h1>
                    <p class="text-muted mb-0">
                        {{ optional($valabilitate->data_inceput)->format('d.m.Y') ?? '—' }}
                        <span class="mx-1">–</span>
                        {{ optional($valabilitate->data_sfarsit)->format('d.m.Y') ?? 'Prezent' }}
                    </p>
                </div>
                <div class="text-md-end">
                    <p class="text-uppercase text-muted small fw-semibold mb-1">Număr auto</p>
                    <p class="h5 fw-bold mb-2">{{ $valabilitate->numar_auto ?? 'Fără număr' }}</p>
                    <button
                        type="button"
                        class="btn btn-primary btn-sm px-4"
                        data-bs-toggle="modal"
                        data-bs-target="#cursaCreateModal"
                    >
                        <i class="fa-solid fa-plus me-1"></i>
                        Adaugă cursă
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if ($curse->isEmpty())
                <div class="p-4 text-center">
                    <i class="fa-solid fa-route fa-2x text-primary mb-3"></i>
                    <p class="fw-semibold mb-1">Nu există curse înregistrate</p>
                    <p class="text-muted small mb-0">Începeți prin adăugarea primei curse pentru această valabilitate.</p>
                </div>
            @else
                <div class="list-group list-group-flush">
                    @foreach ($curse as $cursa)
                        <div class="list-group-item py-4 px-3 px-md-4">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                        <span class="badge bg-light text-dark fw-semibold">
                                            {{ optional($cursa->data_cursa)->format('d.m.Y H:i') ?? 'Fără dată' }}
                                        </span>
                                        @if ($cursa->km_bord)
                                            <span class="badge bg-secondary-subtle text-secondary fw-semibold">
                                                {{ number_format($cursa->km_bord, 0, '.', ' ') }} km
                                            </span>
                                        @endif
                                    </div>
                                    <div class="row g-3 small text-muted">
                                        <div class="col-12 col-md-6">
                                            <p class="fw-semibold text-uppercase text-dark small mb-1">Încărcare</p>
                                            <p class="mb-0">{{ $cursa->incarcare_localitate ?? '—' }}</p>
                                            <p class="mb-0">{{ $cursa->incarcare_cod_postal ?? '—' }}</p>
                                            <p class="mb-0">{{ $cursa->incarcareTara?->nume ?? '—' }}</p>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <p class="fw-semibold text-uppercase text-dark small mb-1">Descărcare</p>
                                            <p class="mb-0">{{ $cursa->descarcare_localitate ?? '—' }}</p>
                                            <p class="mb-0">{{ $cursa->descarcare_cod_postal ?? '—' }}</p>
                                            <p class="mb-0">{{ $cursa->descarcareTara?->nume ?? '—' }}</p>
                                        </div>
                                    </div>
                                    @if ($cursa->observatii)
                                        <p class="text-muted small mt-3 mb-0">{{ $cursa->observatii }}</p>
                                    @endif
                                </div>
                                <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2">
                                    <button
                                        type="button"
                                        class="btn btn-outline-primary btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#cursaEditModal-{{ $cursa->id }}"
                                    >
                                        <i class="fa-solid fa-pen me-1"></i>
                                        Editează
                                    </button>
                                    <form
                                        method="POST"
                                        action="{{ route('sofer.valabilitati.curse.destroy', [$valabilitate, $cursa]) }}"
                                        onsubmit="return confirm('Sigur doriți să ștergeți această cursă?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">
                                            <i class="fa-solid fa-trash-can me-1"></i>
                                            Șterge
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@include('sofer.valabilitati.partials.modals')
