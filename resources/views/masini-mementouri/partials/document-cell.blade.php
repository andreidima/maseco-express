@php
    $baseClass = 'document-cell rounded-3 px-2 py-2 text-center';
    $colorClass = $document->colorClass();
    $daysUntilExpiry = $document->daysUntilExpiry();

    if ($daysUntilExpiry === null) {
        $daysText = 'Fără dată';
    } elseif ($daysUntilExpiry < 0) {
        $abs = abs($daysUntilExpiry);
        $daysText = 'Expirat de ' . $abs . ' ' . ($abs === 1 ? 'zi' : 'zile');
    } else {
        $daysText = 'Expiră în ' . $daysUntilExpiry . ' ' . ($daysUntilExpiry === 1 ? 'zi' : 'zile');
    }
@endphp

<div class="{{ $baseClass }} {{ $colorClass }}" data-color-holder data-base-class="{{ $baseClass }}">
    <form method="POST" action="{{ route('masini-mementouri.documente.update', [$masina, $document]) }}" data-document-update>
        @csrf
        @method('PATCH')
        <input type="date" name="data_expirare" class="form-control form-control-sm rounded-3"
               value="{{ optional($document->data_expirare)->format('Y-m-d') }}">
    </form>
    <small class="d-block" data-days-label>{{ $daysText }}</small>
</div>
