@csrf

<script type="application/javascript">
    tipCamionVechi = {!! json_encode(old('tip_camion', ($camion->tip_camion))) !!}

    firme = {!! json_encode($firme) !!}
    tipuriCamioane = {!! json_encode($tipuriCamioane) !!}
</script>

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="camion">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row px-2 py-2 mb-0" style="background-color:lightyellow; border-left:6px solid; border-color:goldenrod">
            <div class="col-lg-3 mb-2">
                <label for="nume" class="mb-0 ps-3">Tip<span class="text-danger">*</span></label>
                <input
                    type="text"
                    v-model="tip_camion"
                    v-on:focus="autocompleteTipuriCamioane()"
                    class="form-control bg-white rounded-pill {{ $errors->has('tip_camion') ? 'is-invalid' : '' }}"
                    name="tip_camion"
                    placeholder=""
                    autocomplete="off"
                    required>
                        <div v-cloak v-if="tipuriCamioaneListaAutocomplete.length" class="panel-footer">
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
                    *Introdu minim 3 caractere pentru completare automată
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
