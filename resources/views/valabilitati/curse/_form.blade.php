<div class="row g-3">
    <div class="col-md-6">
        <label for="localitate_plecare" class="form-label">Localitate plecare</label>
        <input type="text" class="form-control" id="localitate_plecare" name="localitate_plecare"
            value="{{ old('localitate_plecare', $cursa->localitate_plecare ?? '') }}" required maxlength="255">
    </div>

    <div class="col-md-6">
        <label for="localitate_sosire" class="form-label">Localitate sosire</label>
        <input type="text" class="form-control" id="localitate_sosire" name="localitate_sosire"
            value="{{ old('localitate_sosire', $cursa->localitate_sosire ?? '') }}" maxlength="255">
    </div>

    <div class="col-md-6">
        <label for="plecare_la" class="form-label">Plecare la</label>
        <input type="datetime-local" class="form-control" id="plecare_la" name="plecare_la"
            value="{{ old('plecare_la', optional($cursa->plecare_la ?? null)->format('Y-m-d\TH:i')) }}">
    </div>

    <div class="col-md-6">
        <label for="sosire_la" class="form-label">Sosire la</label>
        <input type="datetime-local" class="form-control" id="sosire_la" name="sosire_la"
            value="{{ old('sosire_la', optional($cursa->sosire_la ?? null)->format('Y-m-d\TH:i')) }}">
    </div>

    <div class="col-md-6">
        <label for="km_bord" class="form-label">Kilometraj bord</label>
        <input type="number" class="form-control" id="km_bord" name="km_bord" min="0"
            value="{{ old('km_bord', $cursa->km_bord ?? '') }}">
    </div>

    <div class="col-12">
        <label for="observatii" class="form-label">Observații</label>
        <textarea class="form-control" id="observatii" name="observatii" rows="3">{{ old('observatii', $cursa->observatii ?? '') }}</textarea>
    </div>
</div>
