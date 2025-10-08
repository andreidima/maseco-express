@php
    $calup ??= null;
    $fisiereErrors = $errors->has('fisiere_pdf') || collect($errors->get('fisiere_pdf.*'))->flatten()->isNotEmpty();
@endphp

<div class="row">
    <div class="col-lg-6 mb-3">
        <label for="denumire_calup" class="mb-0 ps-2">Denumire calup</label>
        <input
            type="text"
            name="denumire_calup"
            id="denumire_calup"
            class="form-control bg-white rounded-3 {{ $errors->has('denumire_calup') ? 'is-invalid' : '' }}"
            value="{{ old('denumire_calup', $calup->denumire_calup ?? '') }}"
            required
        >
    </div>
    <div class="col-lg-3 mb-3">
        <label for="data_plata" class="mb-0 ps-2">Data plată</label>
        <input
            type="date"
            name="data_plata"
            id="data_plata"
            class="form-control bg-white rounded-3 {{ $errors->has('data_plata') ? 'is-invalid' : '' }}"
            value="{{ old('data_plata', optional($calup?->data_plata)->format('Y-m-d')) }}"
        >
    </div>
</div>
<div class="mb-3">
    <label for="observatii" class="mb-0 ps-2">Observații</label>
    <textarea
        name="observatii"
        id="observatii"
        class="form-control bg-white rounded-3 {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
        rows="3"
    >{{ old('observatii', $calup->observatii ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label for="fisiere_pdf" class="mb-0 ps-2">Fișiere PDF</label>
    <input
        type="file"
        name="fisiere_pdf[]"
        id="fisiere_pdf"
        class="form-control bg-white rounded-3 {{ $fisiereErrors ? 'is-invalid' : '' }}"
        accept="application/pdf"
        multiple
    >
    <small class="form-text text-muted">Poți selecta unul sau mai multe fișiere PDF.</small>
    @if ($errors->has('fisiere_pdf'))
        <div class="invalid-feedback d-block">{{ $errors->first('fisiere_pdf') }}</div>
    @endif
    @foreach ($errors->get('fisiere_pdf.*') as $messages)
        @foreach ((array) $messages as $message)
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @endforeach
    @endforeach
    @if ($calup && $calup->relationLoaded('fisiere') && $calup->fisiere->isNotEmpty())
        <p class="mt-2 mb-0 text-muted small">Fișierele deja încărcate sunt listate mai jos.</p>
    @endif
</div>
