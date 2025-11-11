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

    @if ($valabilitati->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fa-solid fa-circle-check fa-3x text-success mb-3"></i>
                <p class="fw-semibold mb-1">Nu aveți nicio valabilitate activă</p>
                <p class="text-muted small mb-0">Veți fi notificat de echipă atunci când apar documente noi.</p>
            </div>
        </div>
    @else
        <div class="d-flex flex-column gap-3">
            @foreach ($valabilitati as $valabilitate)
                <article class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <span class="badge bg-primary text-uppercase fw-semibold px-3 py-2">
                                {{ $valabilitate->nr_auto ?? 'Fără număr' }}
                            </span>
                            @if ($valabilitate->divizie)
                                <span class="text-muted small text-uppercase fw-semibold">
                                    {{ $valabilitate->divizie }}
                                </span>
                            @endif
                        </div>

                        @if ($valabilitate->nume_sofer)
                            <p class="fw-semibold mb-1">{{ $valabilitate->nume_sofer }}</p>
                        @endif

                        @if ($valabilitate->detalii_sofer)
                            <p class="text-muted small mb-3">{{ $valabilitate->detalii_sofer }}</p>
                        @endif

                        <div class="row g-4">
                            @if ($valabilitate->valabilitate_1_inceput || $valabilitate->valabilitate_1_sfarsit || $valabilitate->observatii_1)
                                <div class="col-12 col-md-6">
                                    <h2 class="h6 text-uppercase text-muted mb-2">Valabilitate 1</h2>
                                    <p class="mb-1 fw-semibold">
                                        {{ optional($valabilitate->valabilitate_1_inceput)->format('d.m.Y') ?? '—' }}
                                        <span class="text-muted">–</span>
                                        {{ optional($valabilitate->valabilitate_1_sfarsit)->format('d.m.Y') ?? '—' }}
                                    </p>
                                    @if ($valabilitate->observatii_1)
                                        <p class="text-muted small mb-0">{{ $valabilitate->observatii_1 }}</p>
                                    @endif
                                </div>
                            @endif

                            @if ($valabilitate->valabilitate_2_inceput || $valabilitate->valabilitate_2_sfarsit || $valabilitate->observatii_2)
                                <div class="col-12 col-md-6">
                                    <h2 class="h6 text-uppercase text-muted mb-2">Valabilitate 2</h2>
                                    <p class="mb-1 fw-semibold">
                                        {{ optional($valabilitate->valabilitate_2_inceput)->format('d.m.Y') ?? '—' }}
                                        <span class="text-muted">–</span>
                                        {{ optional($valabilitate->valabilitate_2_sfarsit)->format('d.m.Y') ?? '—' }}
                                    </p>
                                    @if ($valabilitate->observatii_2)
                                        <p class="text-muted small mb-0">{{ $valabilitate->observatii_2 }}</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
