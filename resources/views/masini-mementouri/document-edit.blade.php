@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-car me-1"></i>{{ $masina->numar_inmatriculare }}
            </span>
            <span class="badge bg-light text-dark border ms-2 fs-5">
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
                            <span class="fw-semibold">Actualizează documentul</span>
                            <form method="POST"
                                  action="{{ route('masini-mementouri.documente.update', [$masina, $document]) }}"
                                  class="mb-0">
                                @csrf
                                @method('PATCH')

                                @if ($document->fara_expirare)
                                    <input type="hidden" name="fara_expirare" value="0">
                                    <button type="submit" class="btn btn-outline-secondary border border-dark rounded-3">
                                        <i class="fa-solid fa-rotate-left me-1"></i>Anulează fără expirare
                                    </button>
                                @else
                                    <input type="hidden" name="fara_expirare" value="1">
                                    <button type="submit" class="btn btn-outline-primary border border-dark rounded-3">
                                        <i class="fa-solid fa-infinity me-1"></i>Fără expirare
                                    </button>
                                @endif
                            </form>
                        </div>
                        <div class="card-body">
                            @if ($document->fara_expirare)
                                <div class="alert alert-info border border-info-subtle rounded-4 mb-0">
                                    Documentul este marcat fără expirare.
                                </div>
                            @else
                                <form method="POST"
                                      action="{{ route('masini-mementouri.documente.fisiere.store', [$masina, $document]) }}"
                                      enctype="multipart/form-data">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label" for="data_expirare">Dată expirare</label>
                                        <input type="date"
                                               id="data_expirare"
                                               name="data_expirare"
                                               class="form-control rounded-3"
                                               value="{{ old('data_expirare', optional($document->data_expirare)->format('Y-m-d')) }}">
                                        <small class="text-muted">Modificarea datei necesită încărcarea unui fișier în același timp.</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="fisier">Selectează fișiere (PDF)</label>
                                        <input type="file" id="fisier" name="fisier[]" class="form-control rounded-3"
                                               accept="application/pdf" multiple>
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-success text-white border border-dark rounded-3">
                                            <i class="fa-solid fa-floppy-disk me-1"></i>Salvează documentul
                                        </button>
                                    </div>
                                </form>
                            @endif
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
