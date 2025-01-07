@csrf
@php
    // dd(\Illuminate\Support\Arr::flatten($memento->alerte->pluck('data')));
@endphp
<script type="application/javascript">
    tip = {!! json_encode($memento->tip ?? "") !!}

    dateSelectate = {!! json_encode(old('dateSelectate', \Illuminate\Support\Arr::flatten($memento->alerte->pluck('data')))) !!}
    // dateSelectate = {!! json_encode(\Illuminate\Support\Arr::flatten(old('dateSelectate', ($memento->alerte['data'] ?? [])))) !!}
</script>

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="mementoAlerte">
    <div class="col-lg-7 px-4 py-2 mb-0">
        <div class="row mb-0">
            <div class="col-lg-12 mb-4">
                <input type="hidden" name="tip" v-model="tip">

                <label for="nume" class="mb-0 ps-3">Nume<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('nume') ? 'is-invalid' : '' }}"
                    name="nume"
                    value="{{ old('nume', $memento->nume) }}"
                    required>
            </div>
            <div class="col-lg-5 mb-4">
                <label for="telefon" class="mb-0 ps-3">Telefon către care se trimite alerta</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('telefon') ? 'is-invalid' : '' }}"
                    name="telefon"
                    value="{{ old('telefon', $memento->telefon) }}"
                    required>
            </div>
            <div class="col-lg-5 mb-4">
                <label for="email" class="mb-0 ps-3">Email către care se trimite alerta</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('email') ? 'is-invalid' : '' }}"
                    name="email"
                    value="{{ old('email', $memento->email) }}"
                    required>
            </div>
            <div class="col-lg-2 mb-4 text-center">
                <label for="data_expirare" class="mb-0 ps-0">Dată expirare</label>
                <vue-datepicker-next
                    data-veche="{{ old('data_expirare', $memento->data_expirare) }}"
                    nume-camp-db="data_expirare"
                    tip="date"
                    value-type="YYYY-MM-DD"
                    format="DD.MM.YYYY"
                    :latime="{ width: '125px' }"
                    @trimitere_data_expirare="captureDataExpirare"
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
    <div class="col-lg-5 px-4 py-2 mb-0">
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
                        <div class="col-lg-12 mb-3 d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <label for="data_selectare" class="mb-0 pe-2">Selectează data:</label>
                                <vue-datepicker-next
                                    {{-- data-veche="{{ old('data_selectare', $memento->data_selectare) }}" --}}
                                    data-veche=""
                                    {{-- nume-camp-db="data_selectare" --}}
                                    tip="date"
                                    value-type="YYYY-MM-DD"
                                    format="DD.MM.YYYY"
                                    :latime="{ width: '125px' }"
                                    @trimitere_data_catre_parinte="captureDataDeLaCopil"
                                ></vue-datepicker-next>
                            </div>
                            <div>
                                <label for="data_expirare" class="mx-2 px-2 bg-success text-white rounded-3" @click="adaugaAlerta">Adaugă alertă</label>
                            </div>
                        </div>
                        <div v-if="dateSelectate.length" class="col-lg-12 mb-4">
                            <hr class="mb-4">
                            <h4 class="card-header text-center mb-2">Alerte adăugate</h4>
                            <div class="card-body p-0">
                                <li v-for="data in dateSelectate" class="list-group-item d-flex justify-content-center align-items-center">
                                    <input type="hidden" name="dateSelectate[]" :value=data>
                                    <span class="badge bg-secondary me-1 py-0 px-1">
                                        <h5 class="m-0">
                                            @{{new Date(data).toLocaleDateString('ro-RO')}}
                                        </h5>
                                    </span>
                                    <span type="button" class="badge badge-white p-0" title="Șterge data"
                                        @click="stergeAlerta(data)"
                                    ><i class="fas fa-minus-square text-danger fa-2x"></i></span>
                                </li>
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
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('mementoReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
