@csrf

@if ($categorieFisier === 'maseco')
    <input type="hidden" name="categorie" value="1">
@elseif ($categorieFisier === 'masini')
    <input type="hidden" name="categorie" value="2">
@endif

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px">
    <div class="col-lg-12 mb-4">
        <label for="nume" class="mb-0 ps-3">Nume<span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control bg-white rounded-3 {{ $errors->has('nume') ? 'is-invalid' : '' }}"
            name="nume"
            placeholder=""
            value="{{ old('nume', $fisier->nume) }}"
            required>
    </div>
    <div class="col-lg-12 mb-4">
        <label for="fisier" class="form-label mb-0 ps-3">Fișier<span class="text-danger">*</span></label>
        @if ($fisier->fisier_nume)
            <br>
            <label class="form-label mb-0 ps-4">{{ $fisier->fisier_nume }}</label>
        @else
            <input class="form-control" type="file" id="fisier" name="fisier">
        @endif
    </div>
    <div class="col-lg-12 mb-4">
        <label for="observatii" class="form-label mb-0 ps-3">Observații</label>
        <textarea class="form-control bg-white {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
            name="observatii" rows="3">{{ old('observatii', $fisier->observatii) }}</textarea>
    </div>
    <div class="col-lg-12 mb-4 d-flex justify-content-center">
        <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
        <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('fisier_return_url') }}">Renunță</a>
    </div>
</div>
