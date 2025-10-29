@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-car me-1"></i>{{ $masina->numar_inmatriculare }}
            </span>
        </div>
        <div class="col-lg-6 text-end">
            <a href="{{ route('masini-mementouri.index') }}" class="btn btn-sm btn-secondary border border-dark rounded-3">
                <i class="fa-solid fa-arrow-left me-1"></i>Înapoi la listă
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="mx-3">
            <div class="row g-3">
                @forelse ($masina->documente as $document)
                    @php
                        $labelKey = $document->document_type . ($document->tara ? ':' . $document->tara : '');
                        $label = $uploadDocumentLabels[$labelKey] ?? \Illuminate\Support\Str::of($document->document_type)->headline();
                    @endphp
                    <div class="col-lg-6">
                        <div class="card h-100 border border-secondary-subtle rounded-4"
                             data-document-wrapper
                             data-document-id="{{ $document->id }}"
                             data-empty-label="Fără dată"
                             data-color-holder
                             data-base-class="card h-100 border border-secondary-subtle rounded-4">
                            <div class="card-header d-flex justify-content-between align-items-center rounded-4">
                                <span class="fw-semibold">{{ $label }}</span>
                                <span class="badge bg-light text-dark border"
                                      data-document-badge
                                      data-empty-label="Fără dată">{{ optional($document->data_expirare)->isoFormat('DD.MM.YYYY') ?? 'Fără dată' }}</span>
                            </div>
                            <div class="card-body">
                                <form method="POST"
                                      action="{{ route('masini-mementouri.documente.update', [$masina, $document]) }}"
                                      class="row g-3 mb-3"
                                      data-document-update>
                                    @csrf
                                    @method('PATCH')
                                    <div class="col-md-6">
                                        <label class="form-label" for="data_expirare_{{ $document->id }}">Dată expirare</label>
                                        <input type="date" class="form-control rounded-3" id="data_expirare_{{ $document->id }}" name="data_expirare"
                                               value="{{ old('data_expirare', optional($document->data_expirare)->format('Y-m-d')) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="email_notificare_{{ $document->id }}">Email alertă</label>
                                        <input type="email" class="form-control rounded-3" id="email_notificare_{{ $document->id }}" name="email_notificare"
                                               value="{{ old('email_notificare', $document->email_notificare) }}">
                                    </div>
                                    <div class="col-12 text-end">
                                        <button type="submit" class="btn btn-primary border border-dark rounded-3">
                                            <i class="fa-solid fa-floppy-disk me-1"></i>Salvează documentul
                                        </button>
                                    </div>
                                </form>

                                <form method="POST"
                                      action="{{ route('masini-mementouri.documente.fisiere.store', [$masina, $document]) }}"
                                      enctype="multipart/form-data"
                                      class="row g-2 align-items-end"
                                      data-document-upload>
                                    @csrf
                                    <div class="col-md-8">
                                        <label class="form-label" for="fisier_{{ $document->id }}">Încarcă fișier (PDF)</label>
                                        <input type="file" class="form-control rounded-3" id="fisier_{{ $document->id }}" name="fisier" accept="application/pdf" required>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-success text-white border border-dark w-100 rounded-3">
                                            <i class="fa-solid fa-upload me-1"></i>Încarcă
                                        </button>
                                    </div>
                                </form>

                                <hr>

                                <div data-document-files>
                                    @include('masini-mementouri.partials.document-files-list', ['masina' => $masina, 'document' => $document])
                                </div>

                                <div class="small mt-3" data-feedback-target hidden></div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-info rounded-4 border border-info-subtle">
                            Nu există documente definite pentru această mașină.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
</div>
</div>
@endsection

@include('masini-mementouri.partials.document-form-scripts')

@push('page-scripts')
    <script>
        window.MasiniMementouriDocuments?.initOnLoad();
    </script>
@endpush
