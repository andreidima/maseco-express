@csrf

<script type="application/javascript">
    tipuriCamioane = {!! json_encode($tipuriCamioane) !!}
    tipCamionVechi = {!! json_encode(old('tip_camion', ($camion->tip_camion ?? ""))) !!}

    firme = {!! json_encode($firme) !!}
    firmaIdVechi = {!! json_encode(old('firma_id', ($camion->firma_id ?? ""))) !!}
</script>

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="camion">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-0">
            <div class="col-lg-4 mb-5 mx-auto">
                <label for="nume" class="mb-0 ps-3">Tip <span class="text-danger">*</span></label>
                <input
                    type="text"
                    v-model="tip_camion"
                    v-on:focus="autocompleteTipuriCamioane()"
                    v-on:keyup="autocompleteTipuriCamioane()"
                    class="form-control bg-white rounded-pill {{ $errors->has('tip_camion') ? 'is-invalid' : '' }}"
                    name="tip_camion"
                    placeholder=""
                    autocomplete="off"
                    required>
                        <div v-cloak v-if="tipuriCamioaneListaAutocomplete && tipuriCamioaneListaAutocomplete.length" class="panel-footer">
                            <div class="list-group">
                                <button class="list-group-item list-group-item list-group-item-action py-0"
                                    v-for="tipCamion in tipuriCamioaneListaAutocomplete"
                                    v-on:click="
                                        tip_camion = tipCamion.tip_camion;

                                        tipuriCamioaneListaAutocomplete = ''
                                    ">
                                        @{{ tipCamion.tip_camion }}
                                </button>
                            </div>
                        </div>
                <small class="ps-3">
                    * Completare automată
                </small>
            </div>
            <div class="col-lg-4 mb-5 mx-auto">
                <label for="numar_inmatriculare" class="mb-0 ps-3">Număr de înmatriculare</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('numar_inmatriculare') ? 'is-invalid' : '' }}"
                    name="numar_inmatriculare"
                    placeholder=""
                    value="{{ old('numar_inmatriculare', $camion->numar_inmatriculare) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-5 mx-auto">
                <label for="nume_sofer" class="mb-0 ps-3">Nume șofer</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('nume_sofer') ? 'is-invalid' : '' }}"
                    name="nume_sofer"
                    placeholder=""
                    value="{{ old('nume_sofer', $camion->nume_sofer) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-5 mx-auto">
                <label for="telefon_sofer" class="mb-0 ps-3">Telefon șofer</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('telefon_sofer') ? 'is-invalid' : '' }}"
                    name="telefon_sofer"
                    placeholder=""
                    value="{{ old('telefon_sofer', $camion->telefon_sofer) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-5 mx-auto">
                <label for="firma_id" class="mb-0 ps-3">Firmă proprietar</label>
                <input
                    type="hidden"
                    name="firma_id"
                <input
                    type="text"
                    v-model="firma_id"
                    v-on:focus="autocompleteFirme()"
                    v-on:keyup="autocompleteFirme()"
                    class="form-control bg-white rounded-pill {{ $errors->has('firma_id') ? 'is-invalid' : '' }}"
                    name="firma_id"
                    placeholder=""
                    autocomplete="off"
                    required>
                        <div v-cloak v-if="firmeListaAutocomplete && firmeListaAutocomplete.length" class="panel-footer">
                            <div class="list-group">
                                <button class="list-group-item list-group-item list-group-item-action py-0"
                                    v-for="firma in firmeListaAutocomplete"
                                    v-on:click="
                                        firma_id = firma.firma_id;

                                        firmeListaAutocomplete = ''
                                    ">
                                        @{{ firma.nume }}
                                </button>
                            </div>
                        </div>
                <small class="ps-3">
                    * Completare automată
                </small>
            </div>
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('camion_return_url') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
