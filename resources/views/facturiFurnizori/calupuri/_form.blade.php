@php
    $calup ??= null;
    $disableStatus ??= false;
    $statusValue = old('status', $calup->status ?? \App\Models\FacturiFurnizori\PlataCalup::STATUS_DESCHIS);
@endphp

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="denumire_calup" class="form-label">Denumire calup</label>
        <input type="text" name="denumire_calup" id="denumire_calup" class="form-control" value="{{ old('denumire_calup', $calup->denumire_calup ?? '') }}" required>
    </div>
    <div class="col-md-3 mb-3">
        <label for="data_plata" class="form-label">Data plata</label>
        <input type="date" name="data_plata" id="data_plata" class="form-control" value="{{ old('data_plata', optional($calup?->data_plata)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-3 mb-3">
        <label for="status" class="form-label">Status</label>
        <select name="status" id="status" class="form-select" @disabled($disableStatus)>
            @foreach ($statusOptions as $key => $label)
                <option value="{{ $key }}" @selected($statusValue === $key)>{{ $label }}</option>
            @endforeach
        </select>
        @if ($disableStatus)
            <input type="hidden" name="status" value="{{ $statusValue }}">
        @endif
    </div>
</div>
<div class="mb-3">
    <label for="observatii" class="form-label">Observatii</label>
    <textarea name="observatii" id="observatii" class="form-control" rows="3">{{ old('observatii', $calup->observatii ?? '') }}</textarea>
</div>
<div class="mb-3">
    <label for="fisier_pdf" class="form-label">Fisier PDF</label>
    <input type="file" name="fisier_pdf" id="fisier_pdf" class="form-control" accept="application/pdf">
    @if (!empty($calup?->fisier_pdf))
        <p class="mt-2">
            <a href="{{ route('facturi-furnizori.plati-calupuri.descarca-fisier', $calup) }}">Descarca fisier existent</a>
        </p>
    @endif
</div>
