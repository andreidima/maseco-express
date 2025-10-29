@php
    $baseClass = 'document-cell rounded-3 px-2 py-2 text-center';
    $colorClass = $document->colorClass();

    $displayDate = optional($document->data_expirare)->format('d.m.Y');
@endphp

<div class="{{ $baseClass }} {{ $colorClass }}" data-color-holder data-base-class="{{ $baseClass }}">
    <form method="POST" action="{{ route('masini-mementouri.documente.update', [$masina, $document]) }}"
          class="document-date-form"
          data-document-update data-empty-label="—">
        @csrf
        @method('PATCH')

        <span class="document-date-text" data-date-text>
            {{ $displayDate ?? '—' }}
        </span>

        <input type="date" name="data_expirare"
               class="document-date-input visually-hidden"
               value="{{ optional($document->data_expirare)->format('Y-m-d') }}"
               data-date-input>

        <button type="button" class="btn btn-link p-0 document-date-edit" data-edit-trigger aria-label="Editează data">
            <i class="fa-solid fa-pen"></i>
        </button>
    </form>
</div>
