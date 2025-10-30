@php($files = $fisiere ?? $masina->fisiereGenerale)
@php($uploadInputId = $uploadInputId ?? 'fisier_general')
<div>
    <div class="mb-4">
        <form method="POST"
              action="{{ route('masini-mementouri.fisiere-generale.store', $masina) }}"
              enctype="multipart/form-data"
              class="row g-3 align-items-end">
            @csrf
            <div class="col-md-8">
                <label class="form-label" for="{{ $uploadInputId }}">{{ __('Încarcă fișier') }}</label>
                <input type="file" id="{{ $uploadInputId }}" name="fisier" class="form-control rounded-3" required>
                <small class="text-muted">{{ __('Fișiere permise: pdf, imagini, documente Office (maxim 50 MB).') }}</small>
            </div>
            <div class="col-md-4 text-md-end">
                <button type="submit" class="btn btn-success text-white border border-dark w-100 rounded-3">
                    <i class="fa-solid fa-upload me-1"></i>{{ __('Încarcă') }}
                </button>
            </div>
        </form>
    </div>

    <div>
        <h5 class="mb-3">{{ __('Fișiere generale') }}</h5>
        @include('masini-mementouri.partials.general-files-list', [
            'masina' => $masina,
            'fisiere' => $files,
        ])
    </div>
</div>
