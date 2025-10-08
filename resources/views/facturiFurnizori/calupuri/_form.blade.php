@php
    $calup ??= null;
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
    <label for="fisier_pdf" class="mb-0 ps-2">Fișier PDF</label>
    <input
        type="file"
        name="fisier_pdf"
        id="fisier_pdf"
        class="form-control bg-white rounded-3 {{ $errors->has('fisier_pdf') ? 'is-invalid' : '' }}"
        accept="application/pdf"
    >
    @if (!empty($calup?->fisier_pdf))
        <p class="mt-2 mb-0">
            <a href="{{ route('facturi-furnizori.plati-calupuri.descarca-fisier', $calup) }}">Descarcă fișier existent</a>
        </p>
    @endif
</div>
