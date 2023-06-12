@csrf

<script type="application/javascript">
    mementouriAlerte = {!! json_encode(old('mementouriAlerte', ($memento->mementouriAlerte ?? "")) ?? "") !!}
</script>

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px">
    <div class="col-lg-8 px-4 py-2 mb-0">
        <div class="row mb-0">
            <div class="col-lg-8 mb-4">
                <label for="nume" class="mb-0 ps-3">Nume</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('nume_sofer') ? 'is-invalid' : '' }}"
                    name="nume"
                    value="{{ old('nume', $memento->nume) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-4 text-center" id="app">
                <label for="data_expirare" class="mb-0 ps-0">Dată expirare</label>
                <vue-datepicker-next
                    data-veche="{{ old('data_expirare', $memento->data_expirare) }}"
                    nume-camp-db="data_expirare"
                    tip="date"
                    value-type="YYYY-MM-DD"
                    format="DD.MM.YYYY"
                    :latime="{ width: '125px' }"
                ></vue-datepicker-next>
            </div>
            <div class="col-lg-12 mb-4">
                <label for="descriere" class="form-label mb-0 ps-3">Descriere</label>
                <textarea class="form-control bg-white {{ $errors->has('descriere') ? 'is-invalid' : '' }}"
                    name="descriere" rows="3">{{ old('descriere', $memento->descriere) }}</textarea>
            </div>
            <div class="col-lg-12 mb-4">
                <label for="observatii" class="form-label mb-0 ps-3">Observații</label>
                <textarea class="form-control bg-white {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
                    name="observatii" rows="3">{{ old('observatii', $memento->observatii) }}</textarea>
            </div>
        </div>
    </div>
    <div class="col-lg-4 px-4 py-2 mb-0">
        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="shadow-lg" style="border-radius: 40px 40px 40px 40px;">
                    <div class="border border-secondary p-2 culoare2 text-center" style="border-radius: 40px 40px 0px 0px;">
                        <span class="badge text-light fs-5">
                            Alerte
                        </span>
                    </div>
                </div>
                <div class="card-body py-2 border border-secondary" style="border-radius: 0px 0px 40px 40px;">
                    <div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px">
                        <div class="col-lg-8 px-4 py-2 mb-0">
                            <div class="col-lg-4 mb-4 text-center" id="app1">
                                <label for="data_selectare" class="mb-0 ps-0">Selectează data</label>
                                <vue-datepicker-next
                                    data-veche="{{ old('data_selectare', $memento->data_expirare) }}"
                                    nume-camp-db="data_selectare"
                                    tip="date"
                                    value-type="YYYY-MM-DD"
                                    format="DD.MM.YYYY"
                                    :latime="{ width: '125px' }"
                                ></vue-datepicker-next>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('memento_return_url') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
