@php
    $baseClass = 'document-cell rounded-3 px-2 py-2 text-center';
    $colorClass = $document->colorClass();
    $displayDate = $document->readableExpiryDate();
    $formattedDate = $document->formattedExpiryDate();
    $noExpiry = $document->isWithoutExpiry();
    $noExpiryInputId = 'document-' . $document->id . '-fara-expirare';
@endphp

<div class="{{ $baseClass }} {{ $colorClass }}"
     data-color-holder
     data-base-class="{{ $baseClass }}"
     data-document-wrapper
     data-document-id="{{ $document->id }}"
     data-empty-label="—"
     data-no-expiry-label="FĂRĂ">
    <form method="POST" action="{{ route('masini-mementouri.documente.update', [$masina, $document]) }}"
          class="document-date-form"
          data-document-update data-auto-submit="true">
        @csrf
        @method('PATCH')

        <span class="document-date-text" data-date-text>
            {{ $displayDate }}
        </span>

        <div class="document-inline-controls visually-hidden mt-2" data-edit-controls>
            <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                <input type="date" name="data_expirare"
                       class="form-control form-control-sm"
                       value="{{ $formattedDate }}">
                <div class="form-check mb-0">
                    <input type="checkbox"
                           class="form-check-input"
                           id="{{ $noExpiryInputId }}"
                           name="fara_expirare"
                           value="1"
                           {{ $noExpiry ? 'checked' : '' }}>
                    <label class="form-check-label small" for="{{ $noExpiryInputId }}">Fără expirare</label>
                </div>
            </div>
            <div class="d-flex justify-content-center gap-2 mt-2">
                <button type="submit" class="btn btn-sm btn-primary" data-save-trigger>
                    <i class="fa-solid fa-floppy-disk me-1"></i>Salvează
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-cancel-edit>
                    Renunță
                </button>
            </div>
        </div>

        <button type="button" class="btn btn-link p-0 document-date-edit" data-edit-trigger aria-label="Editează data">
            <i class="fa-solid fa-pen"></i>
        </button>

        <div class="document-feedback small mt-1" data-feedback-target hidden></div>
    </form>
</div>
