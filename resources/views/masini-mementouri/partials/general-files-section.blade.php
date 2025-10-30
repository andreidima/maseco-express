@php($files = $fisiere ?? $masina->fisiereGenerale)
@php($uploadInputId = $uploadInputId ?? 'fisier_general')

<div class="row justify-content-center g-4" data-general-files-section>
    <div class="col-xl-6 col-lg-7">
        <div class="card border border-secondary-subtle rounded-4 h-100">
            <div class="card-header rounded-4 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    <i class="fa-solid fa-upload me-2"></i>{{ __('Încarcă fișiere generale') }}
                </span>
            </div>
            <div class="card-body">
                <form method="POST"
                      action="{{ route('masini-mementouri.fisiere-generale.store', $masina) }}"
                      enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="{{ $uploadInputId }}">{{ __('Selectează fișiere') }}</label>
                        <input type="file"
                               id="{{ $uploadInputId }}"
                               name="fisier[]"
                               class="form-control rounded-3"
                               multiple
                               required
                               accept="application/pdf,image/*,.txt,.csv,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                        <small class="text-muted">{{ __('Fișiere permise: PDF, imagini, documente Office (maxim 50 MB fiecare).') }}</small>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success text-white border border-dark rounded-3">
                            <i class="fa-solid fa-floppy-disk me-1"></i>{{ __('Salvează fișierele') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-xl-8 col-lg-10">
        <div class="card border border-secondary-subtle rounded-4">
            <div class="card-header rounded-4 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    <i class="fa-solid fa-folder-open me-2"></i>{{ __('Fișiere existente') }}
                </span>
            </div>
            <div class="card-body">
                @include('masini-mementouri.partials.general-files-list', [
                    'masina' => $masina,
                    'fisiere' => $files,
                ])
            </div>
        </div>
    </div>
</div>
