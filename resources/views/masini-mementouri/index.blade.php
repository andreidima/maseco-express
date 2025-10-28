@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-4">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-car me-1"></i>Mementouri mașini
            </span>
        </div>
        <div class="col-lg-8 text-end">
            <a class="btn btn-sm btn-outline-secondary border border-dark rounded-3" href="{{ route('masini-mementouri.index') }}">
                <i class="fas fa-rotate-right me-1"></i>Actualizează lista
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        @if (session('status'))
            <div class="alert alert-success mx-3">{{ session('status') }}</div>
        @endif

        <div class="mx-3 mb-4">
            <form method="POST" action="{{ route('masini-mementouri.store') }}" class="row gy-2 gx-3 align-items-end">
                @csrf
                <div class="col-lg-3">
                    <label for="numar_inmatriculare" class="form-label">Număr înmatriculare</label>
                    <input type="text" class="form-control rounded-3" id="numar_inmatriculare" name="numar_inmatriculare" value="{{ old('numar_inmatriculare') }}" required>
                </div>
                <div class="col-lg-3">
                    <label for="descriere" class="form-label">Descriere</label>
                    <input type="text" class="form-control rounded-3" id="descriere" name="descriere" value="{{ old('descriere') }}">
                </div>
                <div class="col-lg-3">
                    <label for="email_notificari" class="form-label">Email notificări</label>
                    <input type="email" class="form-control rounded-3" id="email_notificari" name="email_notificari" value="{{ old('email_notificari') }}">
                </div>
                <div class="col-lg-2">
                    <label for="telefon_notificari" class="form-label">Telefon</label>
                    <input type="text" class="form-control rounded-3" id="telefon_notificari" name="telefon_notificari" value="{{ old('telefon_notificari') }}">
                </div>
                <div class="col-lg-1 d-grid">
                    <button type="submit" class="btn btn-success text-white border border-dark rounded-3 mt-4">
                        <i class="fas fa-plus me-1"></i>Adaugă
                    </button>
                </div>
                <div class="col-12 mt-2">
                    <label for="observatii" class="form-label">Observații</label>
                    <textarea class="form-control rounded-3" id="observatii" name="observatii" rows="1">{{ old('observatii') }}</textarea>
                </div>
            </form>
        </div>

        <div class="table-responsive rounded">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="text-white rounded culoare2">
                    <tr>
                        <th>#</th>
                        <th>Număr auto</th>
                        <th>Descriere</th>
                        @foreach ($gridDocumentTypes as $label)
                            <th class="text-center">{{ $label }}</th>
                        @endforeach
                        @foreach ($vignetteCountries as $code => $label)
                            <th class="text-center">Vignetă {{ $label }}</th>
                        @endforeach
                        <th class="text-center">Documente</th>
                        <th class="text-end">Acțiuni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($masini as $masina)
                        @php
                            $documents = $masina->documente->keyBy(fn($document) => $document->document_type . ($document->tara ? ':' . $document->tara : ''));
                        @endphp
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $masina->numar_inmatriculare }}</td>
                            <td>{{ $masina->descriere }}</td>
                            @foreach ($gridDocumentTypes as $type => $label)
                                @php
                                    $document = $documents->get($type);
                                @endphp
                                <td class="text-center">
                                    @if ($document)
                                        @include('masini-mementouri.partials.document-cell', ['masina' => $masina, 'document' => $document])
                                    @endif
                                </td>
                            @endforeach
                            @foreach ($vignetteCountries as $code => $label)
                                @php
                                    $document = $documents->get(\App\Models\Masini\MasinaDocument::TYPE_VIGNETA . ':' . $code);
                                @endphp
                                <td class="text-center">
                                    @if ($document)
                                        @include('masini-mementouri.partials.document-cell', ['masina' => $masina, 'document' => $document])
                                    @endif
                                </td>
                            @endforeach
                            <td class="text-center">
                                <a href="{{ route('masini-mementouri.show', $masina) }}" class="btn btn-sm btn-outline-primary border border-dark rounded-3">
                                    <i class="fa-solid fa-file-lines me-1"></i>Documente
                                </a>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2 justify-content-end">
                                    <a href="{{ route('masini-mementouri.show', $masina) }}" class="badge bg-primary text-decoration-none">Editează</a>
                                    <form method="POST" action="{{ route('masini-mementouri.destroy', $masina) }}" onsubmit="return confirm('Sigur dorești să ștergi această mașină?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="badge bg-danger border-0">Șterge</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ 5 + count($gridDocumentTypes) + count($vignetteCountries) }}" class="text-center py-4">Nu există mașini înregistrate momentan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const forms = document.querySelectorAll('[data-document-update]');

        forms.forEach((form) => {
            const input = form.querySelector('input[type="date"]');
            if (!input) {
                return;
            }

            input.addEventListener('change', () => {
                const formData = new FormData(form);
                const token = form.querySelector('input[name="_token"]').value;

                fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: formData,
                })
                    .then((response) => response.json())
                    .then((data) => {
                        if (!data || data.status !== 'ok') {
                            return;
                        }

                        const holder = form.closest('[data-color-holder]');
                        if (holder) {
                            holder.className = holder.dataset.baseClass + (data.color_class ? ' ' + data.color_class : '');
                        }

                        const daysLabel = form.querySelector('[data-days-label]');
                        if (daysLabel) {
                            if (data.days_until_expiry === null || typeof data.days_until_expiry === 'undefined') {
                                daysLabel.textContent = 'Fără dată';
                            } else if (data.days_until_expiry < 0) {
                                const days = Math.abs(data.days_until_expiry);
                                daysLabel.textContent = `Expirat de ${days} ${days === 1 ? 'zi' : 'zile'}`;
                            } else {
                                daysLabel.textContent = `Expiră în ${data.days_until_expiry} ${data.days_until_expiry === 1 ? 'zi' : 'zile'}`;
                            }
                        }
                    })
                    .catch(() => {
                        // Silent failure; inline feedback is optional.
                    });
            });
        });
    });
</script>
@endpush
