@extends('layouts.app')

@section('content')
<div class="container py-4 py-md-5">
    <div class="text-center mb-4">
        <span class="badge bg-primary-subtle text-primary fw-semibold text-uppercase px-3 py-2 rounded-pill">
            Panou șofer
        </span>
        <h1 class="h4 fw-bold mt-3">Valabilități active</h1>
        <p class="text-muted small mb-0">Consultați rapid documentele și termenele limită asociate flotei.</p>
    </div>

    @if ($activeValabilitate)
        <article class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <span class="badge bg-primary text-uppercase fw-semibold px-3 py-2">
                        {{ $activeValabilitate->numar_auto ?? 'Fără număr' }}
                    </span>
                    <span class="text-muted small fw-semibold">
                        {{ $activeValabilitate->denumire }}
                    </span>
                </div>

                <div class="row g-4 align-items-center">
                    <div class="col-12 col-md">
                        <p class="text-uppercase text-muted small fw-semibold mb-1">Perioadă valabilitate</p>
                        <p class="h6 fw-bold mb-0">
                            {{ optional($activeValabilitate->data_inceput)->format('d.m.Y') ?? '—' }}
                            <span class="text-muted">–</span>
                            {{ optional($activeValabilitate->data_sfarsit)->format('d.m.Y') ?? 'Prezent' }}
                        </p>
                    </div>
                    <div class="col-12 col-md-auto text-md-end">
                        <p class="text-uppercase text-muted small fw-semibold mb-1">Curse înregistrate</p>
                        <p class="h5 fw-bold mb-2">{{ $activeValabilitate->curse_count }}</p>
                        <a
                            href="{{ route('sofer.valabilitati.show', $activeValabilitate) }}"
                            class="btn btn-primary btn-sm px-4"
                        >
                            Gestionează cursele
                        </a>
                    </div>
                </div>
            </div>
        </article>
    @else
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center py-5">
                <i class="fa-solid fa-circle-check fa-3x text-success mb-3"></i>
                <p class="fw-semibold mb-1">Nu aveți nicio valabilitate activă</p>
                <p class="text-muted small mb-0">Veți fi notificat de echipă atunci când apar documente noi.</p>
            </div>
        </div>
    @endif

</div>
@endsection
