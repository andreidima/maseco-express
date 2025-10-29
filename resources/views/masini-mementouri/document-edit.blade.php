@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-car me-1"></i>{{ $masina->numar_inmatriculare }}
            </span>
            <span class="badge bg-light text-dark border ms-2">
                {{ $documentLabel }}
            </span>
        </div>
        <div class="col-lg-6 text-end">
            <a href="{{ route('masini-mementouri.index') }}" class="btn btn-sm btn-secondary border border-dark rounded-3">
                <i class="fa-solid fa-arrow-left me-1"></i>Înapoi la listă
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors', ['showSessionAlerts' => false])

        @php($statusMessage = session('status') ?? session('success'))

        @if ($statusMessage)
            <div class="alert alert-success border border-success-subtle rounded-4 mx-3">
                {{ $statusMessage }}
            </div>
        @endif

        <div class="mx-3">
            <div class="row justify-content-center g-4">
                <div class="col-xl-6 col-lg-7">
                    <div class="card border border-secondary-subtle rounded-4 h-100">
                        <div class="card-header rounded-4 d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Actualizează data expirării</span>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('masini-mementouri.documente.update', [$masina, $document]) }}">
                                @csrf
                                @method('PATCH')

                                <div class="mb-3">
                                    <label class="form-label" for="data_expirare">Dată expirare</label>
                                    <input type="date"
                                           id="data_expirare"
                                           name="data_expirare"
                                           class="form-control rounded-3"
                                           value="{{ old('data_expirare', optional($document->data_expirare)->format('Y-m-d')) }}">
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary border border-dark rounded-3">
                                        <i class="fa-solid fa-floppy-disk me-1"></i>Salvează data
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6 col-lg-7">
                    <div class="card border border-secondary-subtle rounded-4 h-100">
                        <div class="card-header rounded-4 d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Încarcă documente (PDF)</span>
                        </div>
                        <div class="card-body">
                            <form method="POST"
                                  action="{{ route('masini-mementouri.documente.fisiere.store', [$masina, $document]) }}"
                                  enctype="multipart/form-data">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label" for="fisier">Selectează fișier</label>
                                    <input type="file" id="fisier" name="fisier" class="form-control rounded-3"
                                           accept="application/pdf" required>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-success text-white border border-dark rounded-3">
                                        <i class="fa-solid fa-upload me-1"></i>Încarcă
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8 col-lg-10">
                    <div class="card border border-secondary-subtle rounded-4">
                        <div class="card-header rounded-4 d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Documente existente</span>
                        </div>
                        <div class="card-body">
                            @include('masini-mementouri.partials.document-files-list', ['masina' => $masina, 'document' => $document])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
