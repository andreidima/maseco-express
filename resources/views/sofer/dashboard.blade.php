@extends('layouts.app')

@section('content')
<div class="container py-2 py-md-2">
    <div class="text-center mb-4">
        {{-- <span class="badge bg-primary-subtle h4 text-primary fw-semibold text-uppercase px-3 py-2 rounded-pill">
            Valabilități active
        </span> --}}
        <h1 class="h4 fw-bold mt-3">Valabilități active</h1>
        {{-- <p class="text-muted small mb-0">Consultați rapid documentele și termenele limită asociate flotei.</p> --}}
    </div>

    @forelse ($activeValabilitati as $valabilitate)
        <article class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4 p-lg-5 text-center">
                <div class="gap-2 mb-3">
                    <b class="h4 text-primary fw-bold">
                        {{ $valabilitate->numar_auto ?? 'Fără număr' }}
                    </b>
                    <p class="text-muted mb-0">
                        {{ $valabilitate->divizie->nume ?? 'Fără divizie' }}
                    </p>
                </div>

                <div class="row g-4 align-items-center">
                    <div class="col-12">
                        <p class="text-uppercase mb-1">Perioadă valabilitate</p>
                        <p class="h6 mb-0">
                            {{ optional($valabilitate->data_inceput)->format('d.m.Y') ?? '—' }}
                            <span class="text-muted">–</span>
                            {{ optional($valabilitate->data_sfarsit)->format('d.m.Y') ?? 'Prezent' }}
                        </p>
                    </div>
                    <div class="col-12">
                        <p class="text-uppercase mb-1">Curse înregistrate:
                            <b>
                                {{ $valabilitate->curse_count }}
                            </b>
                        </p>
                        <a
                            href="{{ route('sofer.valabilitati.show', $valabilitate) }}"
                            class="btn btn-primary px-4"
                        >
                            Gestionează cursele
                        </a>
                    </div>
                </div>
            </div>
        </article>
    @empty
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center py-5">
                <i class="fa-solid fa-circle-check fa-3x text-success mb-3"></i>
                <p class="fw-semibold mb-1">Nu aveți nicio valabilitate activă</p>
                <p class="text-muted small mb-0">Veți fi notificat de echipă atunci când apar documente noi.</p>
            </div>
        </div>
    @endforelse

</div>
@endsection
