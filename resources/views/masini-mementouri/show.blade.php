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

        @if (session('status'))
            <div class="alert alert-success mx-3">{{ session('status') }}</div>
        @endif

        <div class="mx-3 mb-4">
            <form method="POST" action="{{ route('masini-mementouri.update', $masina) }}" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-3">
                    <label class="form-label" for="numar_inmatriculare">Număr înmatriculare</label>
                    <input type="text" id="numar_inmatriculare" name="numar_inmatriculare" class="form-control rounded-3"
                           value="{{ old('numar_inmatriculare', $masina->numar_inmatriculare) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="descriere">Descriere</label>
                    <input type="text" id="descriere" name="descriere" class="form-control rounded-3"
                           value="{{ old('descriere', $masina->descriere) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="email_notificari">Email notificări</label>
                    <input type="email" id="email_notificari" name="email_notificari" class="form-control rounded-3"
                           value="{{ old('email_notificari', optional($masina->memento)->email_notificari) }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="telefon_notificari">Telefon notificări</label>
                    <input type="text" id="telefon_notificari" name="telefon_notificari" class="form-control rounded-3"
                           value="{{ old('telefon_notificari', optional($masina->memento)->telefon_notificari) }}">
                </div>
                <div class="col-12">
                    <label class="form-label" for="observatii">Observații</label>
                    <textarea id="observatii" name="observatii" class="form-control rounded-3" rows="2">{{ old('observatii', optional($masina->memento)->observatii) }}</textarea>
                </div>
                <div class="col-12 text-end">
                    <button type="submit" class="btn btn-primary border border-dark rounded-3">
                        <i class="fa-solid fa-floppy-disk me-1"></i>Salvează datele mașinii
                    </button>
                </div>
            </form>
        </div>

        <div class="mx-3">
            <h5 class="mb-3">Documente</h5>
            <div class="row g-3">
                @foreach ($uploadDocumentLabels as $key => $label)
                    @php
                        if (str_contains($key, ':')) {
                            [$type, $country] = explode(':', $key, 2);
                            $document = $masina->documente->firstWhere(fn($doc) => $doc->document_type === $type && $doc->tara === $country);
                        } else {
                            $document = $masina->documente->firstWhere('document_type', $key);
                        }
                    @endphp
                    @if ($document)
                        <div class="col-lg-6">
                            <div class="card h-100 border border-secondary-subtle rounded-4">
                                <div class="card-header d-flex justify-content-between align-items-center rounded-4">
                                    <span class="fw-semibold">{{ $label }}</span>
                                    <span class="badge bg-light text-dark border">{{ optional($document->data_expirare)->isoFormat('DD.MM.YYYY') }}</span>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="{{ route('masini-mementouri.documente.update', [$masina, $document]) }}" class="row g-2 mb-3">
                                        @csrf
                                        @method('PATCH')
                                        <div class="col-md-6">
                                            <label class="form-label" for="data_expirare_{{ $document->id }}">Dată expirare</label>
                                            <input type="date" class="form-control" id="data_expirare_{{ $document->id }}" name="data_expirare" value="{{ optional($document->data_expirare)->format('Y-m-d') }}">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label" for="email_notificare_{{ $document->id }}">Email alertă</label>
                                            <input type="email" class="form-control" id="email_notificare_{{ $document->id }}" name="email_notificare" value="{{ $document->email_notificare }}">
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-outline-primary border border-dark">
                                                <i class="fa-solid fa-floppy-disk me-1"></i>Salvează documentul
                                            </button>
                                        </div>
                                    </form>

                                    <form method="POST" action="{{ route('masini-mementouri.documente.fisiere.store', [$masina, $document]) }}" enctype="multipart/form-data" class="row g-2 align-items-end">
                                        @csrf
                                        <div class="col-md-8">
                                            <label class="form-label" for="fisier_{{ $document->id }}">Încarcă fișier (PDF)</label>
                                            <input type="file" class="form-control" id="fisier_{{ $document->id }}" name="fisier" accept="application/pdf" required>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-success text-white border border-dark w-100">
                                                <i class="fa-solid fa-upload me-1"></i>Încarcă
                                            </button>
                                        </div>
                                    </form>

                                    <hr>
                                    <ul class="list-unstyled mb-0">
                                        @forelse ($document->fisiere as $fisier)
                                            <li class="d-flex justify-content-between align-items-center py-1">
                                                <div>
                                                    <i class="fa-solid fa-file-pdf text-danger me-2"></i>
                                                    <a href="{{ route('masini-mementouri.documente.fisiere.download', [$masina, $document, $fisier]) }}" class="text-decoration-none">
                                                        {{ $fisier->nume_original }}
                                                    </a>
                                                    <small class="text-muted ms-2">{{ number_format(($fisier->dimensiune ?? 0) / 1024, 1) }} KB</small>
                                                </div>
                                                <form method="POST" action="{{ route('masini-mementouri.documente.fisiere.destroy', [$masina, $document, $fisier]) }}" onsubmit="return confirm('Ștergi fișierul?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger border border-dark">
                                                        <i class="fa-solid fa-trash me-1"></i>Șterge
                                                    </button>
                                                </form>
                                            </li>
                                        @empty
                                            <li class="text-muted">Nu există fișiere încărcate.</li>
                                        @endforelse
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
