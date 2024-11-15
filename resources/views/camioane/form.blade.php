@csrf

<script type="application/javascript">
    tipuriCamioane = {!! json_encode($tipuriCamioane) !!}
    tipCamionVechi = {!! json_encode(old('tip_camion', ($camion->tip_camion ?? "")) ?? "") !!}

    firme = {!! json_encode($firme) !!}
    firmaIdVechi = {!! json_encode(old('firma_id', ($camion->firma_id ?? "")) ?? "") !!}
</script>

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="camion">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-0">
            <div class="col-lg-4 mb-5 mx-auto">
                <label for="nume" class="mb-0 ps-3">Tip camion<span class="text-danger">*</span></label>
                <input
                    type="text"
                    v-model="tip_camion"
                    v-on:focus="autocompleteTipuriCamioane(); firmeListaAutocomplete = ''"
                    v-on:keyup="autocompleteTipuriCamioane()"
                    class="form-control bg-white rounded-3 {{ $errors->has('tip_camion') ? 'is-invalid' : '' }}"
                    name="tip_camion"
                    placeholder=""
                    autocomplete="off"
                    required>
                        <div v-cloak v-if="tipuriCamioaneListaAutocomplete && tipuriCamioaneListaAutocomplete.length" class="panel-footer">
                            <div class="list-group" style="max-height: 130px; overflow:auto;">
                                <button class="list-group-item list-group-item-action py-0"
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
            <div class="col-lg-2 mb-5 mx-auto">
                <label for="numar_inmatriculare" class="mb-0 ps-0">Număr de înmatriculare</label>
                <input
                    type="text"
                    v-on:focus="tipuriCamioaneListaAutocomplete = ''; firmeListaAutocomplete = ''"
                    class="form-control bg-white rounded-3 {{ $errors->has('numar_inmatriculare') ? 'is-invalid' : '' }}"
                    name="numar_inmatriculare"
                    placeholder=""
                    value="{{ old('numar_inmatriculare', $camion->numar_inmatriculare) }}"
                    required>
            </div>
            <div class="col-lg-2 mb-5 mx-auto">
                <label for="numar_remorca" class="mb-0 ps-3">Număr remorcă</label>
                <input
                    type="text"
                    v-on:focus="tipuriCamioaneListaAutocomplete = ''; firmeListaAutocomplete = ''"
                    class="form-control bg-white rounded-3 {{ $errors->has('numar_remorca') ? 'is-invalid' : '' }}"
                    name="numar_remorca"
                    placeholder=""
                    value="{{ old('numar_remorca', $camion->numar_remorca) }}"
                    required>
            </div>
            <div class="col-lg-2 mb-5">
                <label for="pret_km_goi" class="mb-0 ps-3">Preț km goi</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('pret_km_goi') ? 'is-invalid' : '' }}"
                    name="pret_km_goi"
                    placeholder=""
                    value="{{ old('pret_km_goi', $camion->pret_km_goi) }}">
                <small for="pret_km_goi" class="mb-0 ps-0">*Punct(.) pentru zecimale</small>
            </div>
            <div class="col-lg-2 mb-5">
                <label for="pret_km_plini" class="mb-0 ps-3">Preț km plini</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('pret_km_plini') ? 'is-invalid' : '' }}"
                    name="pret_km_plini"
                    placeholder=""
                    value="{{ old('pret_km_plini', $camion->pret_km_plini) }}">
                <small for="pret_km_plini" class="mb-0 ps-0">*Punct(.) pentru zecimale</small>
            </div>
            <div class="col-lg-4 mb-5 mx-auto">
                <label for="nume_sofer" class="mb-0 ps-3">Nume șofer</label>
                <input
                    type="text"
                    v-on:focus="tipuriCamioaneListaAutocomplete = ''; firmeListaAutocomplete = ''"
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
                    v-on:focus="tipuriCamioaneListaAutocomplete = ''; firmeListaAutocomplete = ''"
                    class="form-control bg-white rounded-3 {{ $errors->has('telefon_sofer') ? 'is-invalid' : '' }}"
                    name="telefon_sofer"
                    placeholder=""
                    value="{{ old('telefon_sofer', $camion->telefon_sofer) }}"
                    required>
            </div>
            <div class="col-lg-4 mb-5 mx-auto">
                <label for="skype_sofer" class="mb-0 ps-3">Skype șofer</label>
                <input
                    type="text"
                    v-on:focus="tipuriCamioaneListaAutocomplete = ''; firmeListaAutocomplete = ''"
                    class="form-control bg-white rounded-3 {{ $errors->has('skype_sofer') ? 'is-invalid' : '' }}"
                    name="skype_sofer"
                    placeholder=""
                    value="{{ old('skype_sofer', $camion->skype_sofer) }}"
                    required>
            </div>
            <div class="col-lg-6 mb-5 mx-auto">
                <label for="firma_id" class="mb-0 ps-3">Firmă proprietar</label>
                <input
                    type="hidden"
                    v-model="firma_id"
                    name="firma_id">

                <div v-on:focus="autocompleteFirme();" class="input-group">
                    <div class="input-group-prepend d-flex align-items-center">
                        <span v-if="!firma_id" class="input-group-text" id="firma_nume">?</span>
                        <span v-if="firma_id" class="input-group-text bg-success text-white" id="firma_nume"><i class="fa-solid fa-check"></i></span>
                    </div>
                    <input
                        type="text"
                        v-model="firma_nume"
                        v-on:focus="autocompleteFirme(); tipuriCamioaneListaAutocomplete = ''"
                        v-on:keyup="autocompleteFirme(); this.firma_id = '';"
                        class="form-control bg-white rounded-3 {{ $errors->has('firma_nume') ? 'is-invalid' : '' }}"
                        name="firma_nume"
                        placeholder=""
                        autocomplete="off"
                        aria-describedby="firma_nume"
                        required>
                    <div class="input-group-prepend d-flex align-items-center">
                        <span v-if="firma_id" class="input-group-text text-danger" id="firma_nume" v-on:click="firma_id = null; firma_nume = ''"><i class="fa-solid fa-xmark"></i></span>
                    </div>
                </div>
                <div v-cloak v-if="firmeListaAutocomplete && firmeListaAutocomplete.length" class="panel-footer">
                    <div class="list-group" style="max-height: 130px; overflow:auto;">
                        <button class="list-group-item list-group-item list-group-item-action py-0"
                            v-for="firma in firmeListaAutocomplete"
                            v-on:click="
                                firma_id = firma.id;
                                firma_nume = firma.nume;

                                firmeListaAutocomplete = ''
                            ">
                                @{{ firma.nume }}
                        </button>
                    </div>
                </div>
                <small v-if="!firma_id" class="ps-3">* Selectați un proprietar</small>
                <small v-else class="ps-3 text-success">* Ați selectat un proprietar</small>
            </div>
            <div class="col-lg-6 mb-5">
                <label for="observatii" class="form-label mb-0 ps-3">Observații</label>
                <textarea class="form-control bg-white {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
                    name="observatii" rows="3">{{ old('observatii', $camion->observatii) }}</textarea>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 mb-2 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('camion_return_url') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
