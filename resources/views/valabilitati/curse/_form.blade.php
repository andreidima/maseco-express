<div class="row g-3">
    <div class="col-md-6">
        <label for="incarcare_localitate" class="form-label">Localitate încărcare</label>
        <input
            type="text"
            class="form-control"
            id="incarcare_localitate"
            name="incarcare_localitate"
            value="{{ old('incarcare_localitate', optional($cursa)->incarcare_localitate ?? '') }}"
            maxlength="255"
        >
    </div>

    <div class="col-md-6">
        <label for="incarcare_cod_postal" class="form-label">Cod poștal încărcare</label>
        <input
            type="text"
            class="form-control"
            id="incarcare_cod_postal"
            name="incarcare_cod_postal"
            value="{{ old('incarcare_cod_postal', optional($cursa)->incarcare_cod_postal ?? '') }}"
            maxlength="255"
        >
    </div>

    <div class="col-md-6">
        <label for="descarcare_localitate" class="form-label">Localitate descărcare</label>
        <input
            type="text"
            class="form-control"
            id="descarcare_localitate"
            name="descarcare_localitate"
            value="{{ old('descarcare_localitate', optional($cursa)->descarcare_localitate ?? '') }}"
            maxlength="255"
        >
    </div>

    <div class="col-md-6">
        <label for="descarcare_cod_postal" class="form-label">Cod poștal descărcare</label>
        <input
            type="text"
            class="form-control"
            id="descarcare_cod_postal"
            name="descarcare_cod_postal"
            value="{{ old('descarcare_cod_postal', optional($cursa)->descarcare_cod_postal ?? '') }}"
            maxlength="255"
        >
    </div>

    <div class="col-md-6">
        <label for="data_cursa" class="form-label">Data și ora cursei</label>
        <input
            type="datetime-local"
            class="form-control"
            id="data_cursa"
            name="data_cursa"
            value="{{ old('data_cursa', optional($cursa)->data_cursa?->format('Y-m-d\\TH:i')) }}"
        >
    </div>

    <div class="col-12">
        <label for="observatii" class="form-label">Observații</label>
        <textarea
            class="form-control"
            id="observatii"
            name="observatii"
            rows="3"
        >{{ old('observatii', optional($cursa)->observatii ?? '') }}</textarea>
    </div>
</div>
