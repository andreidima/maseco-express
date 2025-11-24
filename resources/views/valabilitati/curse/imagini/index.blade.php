@extends('layouts.app')

@section('content')
    <div class="container-fluid py-3">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h1 class="h4 mb-1">Imagini cursă</h1>
                <p class="mb-0 text-muted">
                    {{ $valabilitate->masina?->numar_inmatriculare ?? 'Valabilitate #' . $valabilitate->id }} ·
                    {{ $cursa->data_cursa?->format('d.m.Y H:i') ?? 'Cursă #' . $cursa->id }}
                </p>
            </div>
            <div>
                <a
                    href="{{ route('valabilitati.curse.index', $valabilitate) }}"
                    class="btn btn-outline-secondary"
                >
                    <i class="fa-solid fa-arrow-left me-2"></i>
                    Înapoi la curse
                </a>
            </div>
        </div>

        @if ($cursa->images->isEmpty())
            <div class="alert alert-info" role="alert">
                Nu există imagini atașate pentru această cursă.
            </div>
        @else
            <div class="row g-3">
                @foreach ($cursa->images as $imagine)
                    <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                        <div class="card h-100 shadow-sm">
                            <div class="ratio ratio-4x3 bg-light">
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($imagine->path) }}"
                                    alt="{{ $imagine->original_name ?? 'Imagine cursă' }}"
                                    class="img-fluid w-100 h-100 object-fit-cover"
                                >
                            </div>
                            <div class="card-body d-flex flex-column gap-2">
                                <div>
                                    <h6 class="card-title mb-1 text-truncate" title="{{ $imagine->original_name ?? 'Imagine cursă' }}">
                                        {{ $imagine->original_name ?? 'Imagine cursă' }}
                                    </h6>
                                    <p class="card-text small text-muted mb-0">
                                        Încărcată {{ $imagine->created_at?->format('d.m.Y H:i') ?? '—' }}
                                    </p>
                                </div>
                                <div class="mt-auto d-flex justify-content-between align-items-center">
                                    <a
                                        href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($imagine->path) }}"
                                        class="btn btn-sm btn-outline-secondary"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        <i class="fa-solid fa-up-right-from-square me-1"></i>
                                        Deschide imaginea
                                    </a>
                                    <a
                                        href="{{ route('valabilitati.curse.images.download', [$valabilitate, $cursa, $imagine]) }}"
                                        class="btn btn-sm btn-primary"
                                    >
                                        <i class="fa-solid fa-file-arrow-down me-1"></i>
                                        Descarcă PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
