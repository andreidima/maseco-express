@extends('layouts.app')

@php
    use App\Support\CountryList;

    $firstTrip = $valabilitate->curse->sortBy('plecare_la')->first();
@endphp

@section('content')
    <div class="container py-4 py-md-5">
        <div class="mb-3">
            <a href="{{ route('sofer.dashboard') }}" class="btn btn-link px-0">
                <i class="fa-solid fa-arrow-left me-1"></i> Înapoi la panou
            </a>
        </div>

        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-semibold text-uppercase px-3 py-2 rounded-pill">
                    Valabilitate activă
                </span>
                <h1 class="h4 fw-bold mt-3 mb-0">{{ $valabilitate->referinta ?: 'Fără referință' }}</h1>
                <p class="text-muted small mb-0">Actualizat la {{ $valabilitate->updated_at?->format('d.m.Y H:i') ?? '—' }}</p>
            </div>
            @if ($valabilitate->masina)
                <div class="text-md-end">
                    <p class="text-muted text-uppercase small mb-1">Vehicul</p>
                    <p class="h5 fw-semibold mb-0">{{ $valabilitate->masina->numar_inmatriculare }}</p>
                </div>
            @endif
        </div>

        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Închide"></button>
            </div>
        @endif

        @include('errors')

        <section class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row g-3 g-md-4">
                    <div class="col-6 col-md-3">
                        <p class="text-muted text-uppercase small mb-1">Prima cursă</p>
                        <p class="fw-semibold mb-0">{{ $valabilitate->prima_cursa?->format('d.m.Y H:i') ?? '—' }}</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="text-muted text-uppercase small mb-1">Ultima cursă</p>
                        <p class="fw-semibold mb-0">{{ $valabilitate->ultima_cursa?->format('d.m.Y H:i') ?? '—' }}</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="text-muted text-uppercase small mb-1">Total curse</p>
                        <p class="fw-semibold mb-0">{{ $valabilitate->total_curse }}</p>
                    </div>
                    <div class="col-6 col-md-3">
                        <p class="text-muted text-uppercase small mb-1">Prima locație</p>
                        <p class="fw-semibold mb-0">{{ $firstTrip?->localitate_plecare ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="mb-5">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-3">
                <div>
                    <h2 class="h5 fw-bold mb-1">Curse înregistrate</h2>
                    <p class="text-muted small mb-0">Actualizați cursele rapid direct din cardurile de mai jos.</p>
                </div>
            </div>

            <div class="d-flex flex-column gap-3">
                @forelse ($valabilitate->curse as $cursa)
                    <article class="card border-0 shadow-sm">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                        <span class="badge bg-primary-subtle text-primary fw-semibold">
                                            {{ $cursa->localitate_plecare }} → {{ $cursa->localitate_sosire ?? '—' }}
                                        </span>
                                        @if ($cursa->descarcare_tara)
                                            <span class="badge bg-light text-muted border">
                                                {{ CountryList::label($cursa->descarcare_tara) }}
                                            </span>
                                        @endif
                                        @if ($cursa->ultima_cursa)
                                            <span class="badge bg-success text-uppercase">Ultima cursă</span>
                                        @endif
                                    </div>
                                    <dl class="row mb-0 small text-muted">
                                        <dt class="col-sm-4">Plecare</dt>
                                        <dd class="col-sm-8 mb-1">
                                            {{ $cursa->plecare_la?->format('d.m.Y H:i') ?? '—' }}
                                        </dd>
                                        <dt class="col-sm-4">Sosire</dt>
                                        <dd class="col-sm-8 mb-1">
                                            {{ $cursa->sosire_la?->format('d.m.Y H:i') ?? '—' }}
                                        </dd>
                                        <dt class="col-sm-4">Ora</dt>
                                        <dd class="col-sm-8 mb-1">
                                            {{ $cursa->ora ? substr($cursa->ora, 0, 5) : '—' }}
                                        </dd>
                                        <dt class="col-sm-4">Km bord</dt>
                                        <dd class="col-sm-8 mb-1">
                                            {{ $cursa->km_bord ?? '—' }}
                                        </dd>
                                        <dt class="col-sm-4">Observații</dt>
                                        <dd class="col-sm-8">
                                            {{ $cursa->observatii ?: '—' }}
                                        </dd>
                                    </dl>
                                </div>
                                <form method="POST" action="{{ route('valabilitati.curse.destroy', [$valabilitate, $cursa]) }}"
                                      class="ms-auto" onsubmit="return confirm('Ștergi această cursă?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>

                            <details class="mt-3">
                                <summary class="btn btn-sm btn-outline-secondary">
                                    <i class="fa-solid fa-pen me-1"></i> Editează cursa
                                </summary>
                                <div class="mt-3 border-top pt-3">
                                    <form method="POST" action="{{ route('valabilitati.curse.update', [$valabilitate, $cursa]) }}">
                                        @csrf
                                        @method('PUT')
                                        @include('valabilitati.curse._form', [
                                            'cursa' => $cursa,
                                            'countries' => $countries,
                                            'isFirstTrip' => $valabilitate->curse->count() === 1,
                                            'formId' => 'sofer-edit-' . $loop->index,
                                        ])
                                        <div class="d-flex justify-content-end gap-2 mt-3">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fa-solid fa-save me-1"></i> Salvează
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </details>
                        </div>
                    </article>
                @empty
                    <div class="card border-0 shadow-sm">
                        <div class="card-body text-center py-5">
                            <i class="fa-solid fa-route fa-2x text-muted mb-3"></i>
                            <p class="fw-semibold mb-1">Nu există încă nicio cursă înregistrată.</p>
                            <p class="text-muted small mb-0">Folosește formularul de mai jos pentru a adăuga prima cursă.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </section>

        <section class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h2 class="h5 fw-bold mb-3">Adaugă cursă nouă</h2>
                <form method="POST" action="{{ route('valabilitati.curse.store', $valabilitate) }}">
                    @csrf
                    @include('valabilitati.curse._form', [
                        'cursa' => null,
                        'countries' => $countries,
                        'isFirstTrip' => $valabilitate->curse->isEmpty(),
                        'formId' => 'sofer-create',
                    ])
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-plus me-1"></i> Adaugă cursă
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
@endsection
