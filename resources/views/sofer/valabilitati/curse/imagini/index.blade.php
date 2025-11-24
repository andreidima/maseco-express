@extends('layouts.app')

@php
    $imageCount = $images->count();
    $canUpload = $imageCount < $maxImages;
@endphp

@section('content')
<div class="container py-4 py-md-5 sofer-valabilitati" id="cursa-images-app"
    data-upload-url="{{ route('sofer.valabilitati.curse.imagini.store', [$valabilitate, $cursa]) }}"
    data-csrf="{{ csrf_token() }}"
>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <a href="{{ route('sofer.valabilitati.show', $valabilitate) }}" class="btn btn-link text-decoration-none px-0">
            <i class="fa-solid fa-arrow-left-long me-1"></i>
            Înapoi la curse
        </a>
        <span class="badge text-bg-secondary">{{ $cursa->nr_cursa ? 'Cursa #' . $cursa->nr_cursa : 'Cursă fără număr' }}</span>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Închide"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body d-flex flex-column flex-sm-row align-items-start align-items-sm-center justify-content-between gap-3">
            <div>
                <h2 class="h5 mb-1 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-images text-primary"></i>
                    Imagini cursă
                </h2>
                <p class="text-muted small mb-0">
                    {{ $imageCount }}/{{ $maxImages }} imagini. Poți încărca fișiere .jpg, .png sau .webp (max 10MB).
                </p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button
                    type="button"
                    class="btn btn-primary btn-sm"
                    id="uploadButton"
                    @disabled(! $canUpload)
                >
                    <i class="fa-solid fa-cloud-arrow-up me-1"></i>
                    Încarcă imagine
                </button>
                @unless ($canUpload)
                    <span class="badge text-bg-warning text-dark small">Limită atinsă</span>
                @endunless
            </div>
        </div>
    </div>

    @if ($images->isEmpty())
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center p-5">
                <i class="fa-solid fa-image fa-2x text-primary mb-3"></i>
                <p class="fw-semibold mb-1">Nu există imagini pentru această cursă</p>
                <p class="text-muted small mb-0">Încarcă prima imagine pentru a începe.</p>
            </div>
        </div>
    @else
        <div class="row g-3">
            @foreach ($images as $imagine)
                <div class="col-12 col-sm-6 col-md-4">
                    @php
                        $aspectRatio = $imagine->width && $imagine->height
                            ? ($imagine->height / $imagine->width * 100)
                            : 75;
                    @endphp
                    <div class="card h-100 shadow-sm border-0">
                        <div class="ratio bg-light rounded-top overflow-hidden" style="--bs-aspect-ratio: {{ $aspectRatio }}%;">
                            <img
                                src="{{ route('sofer.valabilitati.curse.imagini.stream', [$valabilitate, $cursa, $imagine]) }}"
                                alt="Imagine cursă"
                                class="w-100 h-100"
                                style="object-fit: contain; display: block;"
                            >
                        </div>
                        <div class="card-body d-flex flex-column gap-2">
                            <div class="text-muted small">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span><i class="fa-solid fa-file-image me-1"></i>{{ $imagine->original_name }}</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span><i class="fa-solid fa-weight-hanging me-1"></i>{{ number_format($imagine->size_bytes / 1024, 1) }} KB</span>
                                    @if ($imagine->width && $imagine->height)
                                        <span><i class="fa-solid fa-ruler-combined me-1"></i>{{ $imagine->width }}×{{ $imagine->height }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-auto">
                                <button
                                    type="button"
                                    class="btn btn-outline-primary btn-sm w-100"
                                    data-recrop
                                    data-update-url="{{ route('sofer.valabilitati.curse.imagini.update', [$valabilitate, $cursa, $imagine]) }}"
                                    data-stream-url="{{ route('sofer.valabilitati.curse.imagini.stream', [$valabilitate, $cursa, $imagine]) }}"
                                    data-mime="{{ $imagine->mime_type }}"
                                    data-original-name="{{ $imagine->original_name }}"
                                >
                                    <i class="fa-solid fa-crop me-1"></i>
                                    Reeditează
                                </button>
                                <form
                                    method="POST"
                                    action="{{ route('sofer.valabilitati.curse.imagini.destroy', [$valabilitate, $cursa, $imagine]) }}"
                                    class="w-100"
                                    onsubmit="return confirm('Ștergi această imagine?');"
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                        <i class="fa-solid fa-trash-can me-1"></i>
                                        Șterge
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- Hidden file input for upload --}}
<input type="file" accept=".jpg,.jpeg,.png,.webp" id="imageInput" class="d-none">

{{-- Cropper Modal --}}
<div class="modal fade" id="imageCropperModal" tabindex="-1" aria-labelledby="cropperModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cropperModalTitle">Imagine</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Închide"></button>
            </div>
            <div class="modal-body">
                <div
                    id="cropperContainer"
                    class="cropper-wrapper bg-light rounded mb-3 d-flex justify-content-center align-items-center p-2 overflow-hidden"
                >
                    <img
                        id="cropperImage"
                        src=""
                        alt="Crop imagine"
                        class="cropper-image"
                    >
                </div>
                <p class="small text-muted mb-0">
                    Ajustează zona dorită și apasă „Salvează”. Imaginea se va salva doar în varianta croppată curentă.
                </p>
                <div id="cropperStatus" class="text-danger small mt-2"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Anulează</button>
                <button type="button" class="btn btn-primary" id="saveCropButton">
                    <i class="fa-solid fa-floppy-disk me-1"></i>
                    Salvează
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
    @vite('resources/js/sofer-valabilitate-imagini.js')
@endpush
