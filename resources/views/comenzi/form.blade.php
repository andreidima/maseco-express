@csrf
@php
    // echo old('transportator_transportator_id');
    // echo json_encode(old('transportator_transportator_id'));
    // dd(old('incarcari'), old('transportator_contract'), old('client_zile_scadente'));
@endphp
<script type="application/javascript">
    firmeTransportatori = {!! json_encode($firmeTransportatori) !!}
    firmaTransportatorIdVechi = {!! json_encode(old('transportator_transportator_id', ($comanda->transportator_transportator_id ?? "")) ?? "") !!}
    firmeClienti = {!! json_encode($firmeClienti) !!}
    firmaClientIdVechi = {!! json_encode(old('client_client_id', ($comanda->client_client_id ?? "")) ?? "") !!}
    camioane = {!! json_encode($camioane) !!}
    camionIdVechi = {!! json_encode(old('camion_id', ($comanda->camion_id ?? "")) ?? "") !!}

    incarcari =  {!! json_encode(old('incarcari', $comanda->locuriOperareIncarcari()->get())) !!}
    descarcari =  {!! json_encode(old('descarcari', $comanda->locuriOperareDescarcari()->get())) !!}
</script>

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="formularComanda">
    <div class="col-lg-12 px-4 pt-2 mb-0">
        <div class="row px-2 pt-4 pb-1 mb-0" style="background-color:lightyellow; border-left:6px solid; border-color:goldenrod">
            <div class="col-lg-3 mb-4 text-center mx-auto">
                <label for="data_creare" class="mb-0 ps-3">Dată creare{{ old('transportator_zile_scadente') }}{{ (Route::currentRouteName() === "comenzi.create") ? \Carbon\Carbon::today() : ''  }}<span class="text-danger">*</span></label>
                <vue-datepicker-next
                    data-veche="{{ old('data_creare', $comanda->data_creare) }}"
                    nume-camp-db="data_creare"
                    tip="date"
                    value-type="YYYY-MM-DD"
                    format="DD.MM.YYYY"
                    :latime="{ width: '125px' }"
                ></vue-datepicker-next>
            </div>
        </div>
        <div class="row px-2 pt-4 pb-1 d-flex justify-content-center" style="background-color:#ddffff; border-left:6px solid; border-color:#2196F3; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-12 mb-4 text-center">
                <span class="fs-4 badge text-white" style="background-color:#2196F3;">Transportator</span>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="transportator_contract" class="mb-0 ps-3">Contract</label>
                <input
                    type="text"
                    class="form-control rounded-3 {{ $errors->has('transportator_contract') ? 'is-invalid' : '' }}"
                    name="transportator_contract"
                    placeholder=""
                    value="{{ old('transportator_contract', $comanda->transportator_contract) }}"
                    disabled>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="transportator_limba_id" class="mb-0 ps-3">Limba</label>
                <select name="transportator_limba_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_limba_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($limbi as $limba)
                        <option value="{{ $limba->id }}" {{ ($limba->id === intval(old('transportator_limba_id', $comanda->transportator_limba_id ?? ''))) ? 'selected' : '' }}>{{ $limba->nume }}</option>
                    @endforeach
                </select>
            </div>
            {{-- <div class="col-lg-3 mb-4">
                <label for="transportator_transportator_id" class="mb-0 ps-3">Transportator</label>
                <select name="transportator_transportator_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_transportator_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($firmeTransportatori as $firmaTransportator)
                        <option value="{{ $firmaTransportator->id }}" {{ ($firmaTransportator->id === intval(old('transportator_transportator_id', $comanda->transportator_transportator_id ?? ''))) ? 'selected' : '' }}>{{ $firmaTransportator->nume }}</option>
                    @endforeach
                </select>
            </div> --}}
            <div class="col-lg-4 mb-4" style="position:relative;" v-click-out="() => firmeTransportatoriListaAutocomplete = ''">
                <label for="transportator_transportator_id" class="mb-0 ps-3">Transportator<span class="text-danger">*</span></label>
                <input
                    type="hidden"
                    v-model="firmaTransportatorId"
                    name="transportator_transportator_id">

                <div class="input-group">
                    <div class="input-group-prepend d-flex align-items-center">
                        <div v-if="!firmaTransportatorId" class="input-group-text" id="firmaTransportatorNume">?</div>
                        <div v-if="firmaTransportatorId" class="input-group-text p-2 bg-success text-white" id="firmaTransportatorNume"><i class="fa-solid fa-check" style="height:100%"></i></div>
                    </div>
                    <input
                        type="text"
                        v-model="firmaTransportatorNume"
                        v-on:focus="autocompleteFirmeTransportatori();"
                        v-on:keyup="autocompleteFirmeTransportatori(); this.firmaTransportatorId = '';"
                        class="form-control bg-white rounded-3 {{ $errors->has('transportator_transportator_id') ? 'is-invalid' : '' }}"
                        name="firmaTransportatorNume"
                        placeholder=""
                        autocomplete="off"
                        aria-describedby="firmaTransportatorNume"
                        required>
                    <div class="input-group-prepend d-flex align-items-center">
                        <div v-if="firmaTransportatorId" class="input-group-text p-2 text-danger" id="firmaTransportatorNume" v-on:click="firmaTransportatorId = null; firmaTransportatorNume = ''"><i class="fa-solid fa-xmark"></i></div>
                    </div>
                    <div class="input-group-prepend ms-2 d-flex align-items-center">
                        <button type="submit" ref="submit" formaction="{{ $comanda->path() }}/adauga-resursa/transportator" class="btn btn-success text-white rounded-3 py-0 px-2"
                            style="font-size: 30px; line-height: 1.2;" title="Adaugă transportator nou">+</button>
                    </div>
                </div>
                <div v-cloak v-if="firmeTransportatoriListaAutocomplete && firmeTransportatoriListaAutocomplete.length" class="panel-footer" style="width:100%; position:absolute; z-index: 1000;">
                    <div class="list-group" style="max-height: 218px; overflow:auto;">
                        <button class="list-group-item list-group-item-action py-0"
                            v-for="firma in firmeTransportatoriListaAutocomplete"
                            v-on:click="
                                firmaTransportatorId = firma.id;
                                firmaTransportatorNume = firma.nume;

                                firmeTransportatoriListaAutocomplete = ''
                            ">
                                @{{ firma.nume }}
                        </button>
                    </div>
                </div>
                <small v-if="!firmaTransportatorId" class="ps-3">*Selectați un transportator</small>
                <small v-else class="ps-3 text-success">*Ați selectat transportatorul</small>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="transportator_valoare_contract" class="mb-0 ps-3">Valoare contract</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('transportator_valoare_contract') ? 'is-invalid' : '' }}"
                    name="transportator_valoare_contract"
                    placeholder=""
                    value="{{ old('transportator_valoare_contract', $comanda->transportator_valoare_contract) }}">
                <small for="transportator_valoare_contract" class="mb-0 ps-3">*Punct(.) pentru zecimale</small>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="transportator_moneda_id" class="mb-0 ps-3">Monedă</label>
                <select name="transportator_moneda_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_moneda_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($monede as $moneda)
                        <option value="{{ $moneda->id }}" {{ ($moneda->id === intval(old('transportator_moneda_id', $comanda->transportator_moneda_id ?? ''))) ? 'selected' : '' }}>{{ $moneda->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="transportator_procent_tva_id" class="mb-0 ps-3">Procent TVA</label>
                <select name="transportator_procent_tva_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_procent_tva_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($procenteTVA as $procentTVA)
                        <option value="{{ $procentTVA->id }}" {{ ($procentTVA->id === intval(old('transportator_procent_tva_id', $comanda->transportator_procent_tva_id ?? ''))) ? 'selected' : '' }}>{{ $procentTVA->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="transportator_metoda_de_plata_id" class="mb-0 ps-3">Metodă de plată</label>
                <select name="transportator_metoda_de_plata_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_metoda_de_plata_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($metodeDePlata as $metodaDePlata)
                        <option value="{{ $metodaDePlata->id }}" {{ ($metodaDePlata->id === intval(old('transportator_metoda_de_plata_id', $comanda->transportator_metoda_de_plata_id ?? ''))) ? 'selected' : '' }}>{{ $metodaDePlata->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 mb-4">
                <label for="transportator_termen_plata_id" class="mb-0 ps-3">Termen de plată</label>
                <select name="transportator_termen_plata_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_termen_plata_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($termeneDePlata as $termenDePlata)
                        <option value="{{ $termenDePlata->id }}" {{ ($termenDePlata->id === intval(old('transportator_termen_plata_id', $comanda->transportator_termen_plata_id ?? ''))) ? 'selected' : '' }}>{{ $termenDePlata->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="transportator_zile_scadente" class="mb-0 ps-3">Zile scadente</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('transportator_zile_scadente') ? 'is-invalid' : '' }}"
                    name="transportator_zile_scadente"
                    placeholder=""
                    value="{{ old('transportator_zile_scadente', $comanda->transportator_zile_scadente) }}">
            </div>
            <div class="col-lg-2 mb-4">
                <div class="text-center">
                    {{-- Tarif Pe Km? --}}
                    <label class="mb-0 ps-3">Tarif Pe Km</label>
                    <div class="d-flex py-1 justify-content-center">
                        <div class="form-check me-4">
                            <input class="form-check-input" type="radio" value="1" name="transportator_tarif_pe_km" id="transportator_tarif_pe_km_da"
                                {{ old('transportator_tarif_pe_km', $comanda->transportator_tarif_pe_km) == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="transportator_tarif_pe_km_da">Da</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="0" name="transportator_tarif_pe_km" id="transportator_tarif_pe_km_nu"
                                {{ old('transportator_tarif_pe_km', $comanda->transportator_tarif_pe_km) == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="transportator_tarif_pe_km_nu">Nu</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row px-2 pt-4 pb-1 d-flex justify-content-center" style="background-color:#B8FFB8; border-left:6px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-12 mb-4 text-center">
                <span class="fs-4 badge text-white" style="background-color:mediumseagreen;">Client</span>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="client_contract" class="mb-0 ps-3">Contract</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('client_contract') ? 'is-invalid' : '' }}"
                    name="client_contract"
                    placeholder=""
                    value="{{ old('client_contract', $comanda->client_contract) }}">
            </div>
            <div class="col-lg-2 mb-4">
                <label for="client_limba_id" class="mb-0 ps-3">Limba</label>
                <select name="client_limba_id" class="form-select bg-white rounded-3 {{ $errors->has('client_limba_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($limbi as $limba)
                        <option value="{{ $limba->id }}" {{ ($limba->id === intval(old('client_limba_id', $comanda->client_limba_id ?? ''))) ? 'selected' : '' }}>{{ $limba->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-4 mb-4" style="position:relative;" v-click-out="() => firmeClientiListaAutocomplete = ''">
                <label for="client_client_id" class="mb-0 ps-3">Client<span class="text-danger">*</span></label>
                <input
                    type="hidden"
                    v-model="firmaClientId"
                    name="client_client_id">

                <div class="input-group">
                    <div class="input-group-prepend d-flex align-items-center">
                        <div v-if="!firmaClientId" class="input-group-text" id="firmaClientNume">?</div>
                        <div v-if="firmaClientId" class="input-group-text p-2 bg-success text-white" id="firmaClientNume"><i class="fa-solid fa-check" style="height:100%"></i></div>
                    </div>
                    <input
                        type="text"
                        v-model="firmaClientNume"
                        v-on:focus="autocompleteFirmeClienti();"
                        v-on:keyup="autocompleteFirmeClienti(); this.firmaClientId = '';"
                        class="form-control bg-white rounded-3 {{ $errors->has('client_client_id') ? 'is-invalid' : '' }}"
                        name="firmaClientNume"
                        placeholder=""
                        autocomplete="off"
                        aria-describedby="firmaClientNume"
                        required>
                    <div class="input-group-prepend d-flex align-items-center">
                        <div v-if="firmaClientId" class="input-group-text p-2 text-danger" id="firmaClientNume" v-on:click="firmaClientId = null; firmaClientNume = ''"><i class="fa-solid fa-xmark"></i></div>
                    </div>
                    <div class="input-group-prepend ms-2 d-flex align-items-center">
                        <button type="submit" ref="submit" formaction="{{ $comanda->path() }}/adauga-resursa/client" class="btn btn-success text-white rounded-3 py-0 px-2"
                            style="font-size: 30px; line-height: 1.2;" title="Adaugă client nou">+</button>
                    </div>
                </div>
                <div v-cloak v-if="firmeClientiListaAutocomplete && firmeClientiListaAutocomplete.length" class="panel-footer" style="width:100%; position:absolute; z-index: 1000;">
                    <div class="list-group" style="max-height: 218px; overflow:auto;">
                        <button class="list-group-item list-group-item list-group-item-action py-0"
                            v-for="firma in firmeClientiListaAutocomplete"
                            v-on:click="
                                firmaClientId = firma.id;
                                firmaClientNume = firma.nume;

                                firmeClientiListaAutocomplete = ''
                            ">
                                @{{ firma.nume }}
                        </button>
                    </div>
                </div>
                <small v-if="!firmaClientId" class="ps-3">*Selectați un client</small>
                <small v-else class="ps-3 text-success">*Ați selectat clientul</small>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="client_valoare_contract" class="mb-0 ps-3">Valoare contract</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('client_valoare_contract') ? 'is-invalid' : '' }}"
                    name="client_valoare_contract"
                    placeholder=""
                    value="{{ old('client_valoare_contract', $comanda->client_valoare_contract) }}">
                <small for="client_valoare_contract" class="mb-0 ps-3">*Punct(.) pentru zecimale</small>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="client_moneda_id" class="mb-0 ps-3">Monedă</label>
                <select name="client_moneda_id" class="form-select bg-white rounded-3 {{ $errors->has('client_moneda_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($monede as $moneda)
                        <option value="{{ $moneda->id }}" {{ ($moneda->id === intval(old('client_moneda_id', $comanda->client_moneda_id ?? ''))) ? 'selected' : '' }}>{{ $moneda->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="client_procent_tva_id" class="mb-0 ps-3">Procent TVA</label>
                <select name="client_procent_tva_id" class="form-select bg-white rounded-3 {{ $errors->has('client_procent_tva_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($procenteTVA as $procentTVA)
                        <option value="{{ $procentTVA->id }}" {{ ($procentTVA->id === intval(old('client_procent_tva_id', $comanda->client_procent_tva_id ?? ''))) ? 'selected' : '' }}>{{ $procentTVA->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="client_metoda_de_plata_id" class="mb-0 ps-3">Metodă de plată</label>
                <select name="client_metoda_de_plata_id" class="form-select bg-white rounded-3 {{ $errors->has('client_metoda_de_plata_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($metodeDePlata as $metodaDePlata)
                        <option value="{{ $metodaDePlata->id }}" {{ ($metodaDePlata->id === intval(old('client_metoda_de_plata_id', $comanda->client_metoda_de_plata_id ?? ''))) ? 'selected' : '' }}>{{ $metodaDePlata->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 mb-4">
                <label for="client_termen_plata_id" class="mb-0 ps-3">Termen de plată</label>
                <select name="client_termen_plata_id" class="form-select bg-white rounded-3 {{ $errors->has('client_termen_plata_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($termeneDePlata as $termenDePlata)
                        <option value="{{ $termenDePlata->id }}" {{ ($termenDePlata->id === intval(old('client_termen_plata_id', $comanda->client_termen_plata_id ?? ''))) ? 'selected' : '' }}>{{ $termenDePlata->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="client_zile_scadente" class="mb-0 ps-3">Zile scadente</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('client_zile_scadente') ? 'is-invalid' : '' }}"
                    name="client_zile_scadente"
                    placeholder=""
                    value="{{ old('client_zile_scadente', $comanda->client_zile_scadente) }}">
            </div>
            <div class="col-lg-2 mb-4">
                <div class="text-center">
                    {{-- Tarif Pe Km? --}}
                    <label class="mb-0 ps-3">Tarif Pe Km</label>
                    <div class="d-flex py-1 justify-content-center">
                        <div class="form-check me-4">
                            <input class="form-check-input" type="radio" value="1" name="client_tarif_pe_km" id="client_tarif_pe_km_da"
                                {{ old('client_tarif_pe_km', $comanda->client_tarif_pe_km) == '1' ? 'checked' : '' }}>
                            <label class="form-check-label" for="client_tarif_pe_km_da">Da</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="0" name="client_tarif_pe_km" id="client_tarif_pe_km_nu"
                                {{ old('client_tarif_pe_km', $comanda->client_tarif_pe_km) == '0' ? 'checked' : '' }}>
                            <label class="form-check-label" for="client_tarif_pe_km_nu">Nu</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row px-2 pt-4 pb-1 mb-0" style="background-color:lightyellow; border-left:6px solid; border-color:goldenrod">
            <div class="col-lg-6 mb-4">
                <label for="descriere_marfa" class="form-label mb-0 ps-3">Descriere marfă</label>
                <textarea class="form-control bg-white {{ $errors->has('descriere_marfa') ? 'is-invalid' : '' }}"
                    name="descriere_marfa" rows="3">{{ old('descriere_marfa', $comanda->descriere_marfa) }}</textarea>
            </div>
            <div class="col-lg-3 mb-4" style="position:relative;" v-click-out="() => camioaneListaAutocomplete = ''">
                <label for="camion_id" class="mb-0 ps-3">Camion</label>
                <input
                    type="hidden"
                    v-model="camionId"
                    name="camion_id">

                <div class="input-group">
                    <div class="input-group-prepend d-flex align-items-center">
                        <div v-if="!camionId" class="input-group-text" id="camionNumarInmatriculare">?</div>
                        <div v-if="camionId" class="input-group-text p-2 bg-success text-white" id="camionNumarInmatriculare"><i class="fa-solid fa-check" style="height:100%"></i></div>
                    </div>
                    <input
                        type="text"
                        v-model="camionNumarInmatriculare"
                        v-on:focus="autocompleteCamioane();"
                        v-on:keyup="autocompleteCamioane(); this.camionId = '';"
                        class="form-control bg-white rounded-3 {{ $errors->has('camionNumarInmatriculare') ? 'is-invalid' : '' }}"
                        name="camionNumarInmatriculare"
                        placeholder=""
                        autocomplete="off"
                        aria-describedby="camionNumarInmatriculare"
                        required>
                    <div class="input-group-prepend d-flex align-items-center">
                        <div v-if="camionId" class="input-group-text p-2 text-danger" id="camionNumarInmatriculare" v-on:click="camionId = null; camionNumarInmatriculare = ''; camionTipCamion = ''"><i class="fa-solid fa-xmark"></i></div>
                    </div>
                    <div class="input-group-prepend ms-2 d-flex align-items-center">
                        <button type="submit" ref="submit" formaction="{{ $comanda->path() }}/adauga-resursa/camion" class="btn btn-success text-white rounded-3 py-0 px-2"
                            style="font-size: 30px; line-height: 1.2;" title="Adaugă camion nou">+</button>
                    </div>
                </div>
                <div v-cloak v-if="camioaneListaAutocomplete && camioaneListaAutocomplete.length" class="panel-footer" style="width:100%; position:absolute; z-index: 1000;">
                    <div class="list-group" style="max-height: 218px; overflow:auto;">
                        <button class="list-group-item list-group-item list-group-item-action py-0"
                            v-for="camion in camioaneListaAutocomplete"
                            v-on:click="
                                camionId = camion.id;
                                camionNumarInmatriculare = camion.numar_inmatriculare;
                                camionTipCamion = camion.tip_camion;

                                camioaneListaAutocomplete = ''
                            ">
                                @{{ camion.numar_inmatriculare }}
                        </button>
                    </div>
                </div>
                <small v-if="!camionId" class="ps-3">*Selectați un camion</small>
                <small v-else class="ps-3 text-success">*Ați selectat camionul</small>
            </div>
            <div class="col-lg-3 mb-4">
                <label for="camion_tip_camion" class="mb-0 ps-3">Tip camion</label>
                <input
                    type="text"
                    class="form-control rounded-3"
                    v-model="camionTipCamion"
                    disabled>
            </div>
        </div>
        <div class="row px-2 pt-4 pb-1 d-flex justify-content-center" style="background-color:#ddffff; border-left:6px solid; border-color:#2196F3; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-12 mb-4 text-center">
                <span class="fs-4 badge text-white" style="background-color:#2196F3;">Incărcări</span>
            </div>
            <div class="col-lg-12 mb-4">
                <div class="row align-items-start mb-2" v-for="(incarcare, index) in incarcari" :key="incarcare" style="border:2px solid #2196F3;">
                    <div class="col-lg-3 mb-2" style="position:relative;"
                        v-click-out="() => locuriOperareIncarcari[index] = ''"
                        >
                        <label for="nume" class="mb-0 ps-3">Loc de încărcare @{{ index+1 }}<span class="text-danger">*</span></label>
                        <small v-if="(locuriOperareIncarcari[index]) && (locuriOperareIncarcari[index].length >= 100)" class="ps-3 text-danger">Căutarea dvs. returnează mai mult de 100 de înregistrări. Sistemul va afișa primele 100 de înregistrări găsite în baza de date. Vă rugăm să introduceți mai multe caractere pentru a regăsi înregistrările dorite!</small>
                        <input
                            type="hidden"
                            :name="'incarcari[' + index + '][id]'"
                            v-model="incarcari[index].id"
                            >
                        <div class="d-flex">
                            <div class="input-group">
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('nume') ? 'is-invalid' : '' }}"
                                    :name="'incarcari[' + index + '][nume]'"
                                    v-model="incarcari[index].nume"
                                    v-on:focus="getLocuriOperareIncarcari(index, $event.target.value);"
                                    v-on:keyup="getLocuriOperareIncarcari(index, $event.target.value);"
                                    placeholder=""
                                    autocomplete="off"
                                    aria-describedby=""
                                    required>
                                    <div class="input-group-prepend d-flex align-items-center">
                                    </div>
                            </div>
                            <div class="input-group-prepend ms-2 d-flex align-items-center">
                                <button type="submit" ref="submit" :formaction="'{{ $comanda->path() }}/adauga-resursa/loc-operare/incarcari/' + index" class="btn btn-success text-white rounded-3 py-0 px-2"
                                    style="font-size: 30px; line-height: 1.2;" title="Adaugă loc operare nou">+</button>
                            </div>
                        </div>
                        <div v-cloak v-if="locuriOperareIncarcari[index] && locuriOperareIncarcari[index].length" class="panel-footer" style="width:100%; position:absolute; z-index: 1000;">
                            <div class="list-group" style="max-height: 218px; overflow:auto;">
                                <button class="list-group-item list-group-item list-group-item-action py-0"
                                    v-for="locOperare in locuriOperareIncarcari[index]"
                                    v-on:click="
                                        incarcari[index].id = locOperare.id;
                                        incarcari[index].nume = locOperare.nume;
                                        incarcari[index].oras = locOperare.oras;
                                        incarcari[index].tara.id = locOperare.tara.id;
                                        incarcari[index].tara.nume = locOperare.tara.nume;

                                        locuriOperareIncarcari = ''
                                    ">
                                        @{{ locOperare.nume }}
                                </button>
                            </div>
                        </div>
                        <small v-if="!incarcari[index].nume || (incarcari[index].nume.length < 3)" class="ps-3">* Introduceți minim 3 caractere</small>
                    </div>
                    <div class="col-lg-3 mb-2">
                        <label for="adresa" class="mb-0 ps-3">Adresa</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('adresa') ? 'is-invalid' : '' }}"
                            :name="'incarcari[' + index + '][adresa]'"
                            v-model="incarcari[index].adresa">
                    </div>
                    <div class="col-lg-2 mb-2">
                        <label for="oras" class="mb-0 ps-3">Oraș</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('oras') ? 'is-invalid' : '' }}"
                            :name="'incarcari[' + index + '][oras]'"
                            v-model="incarcari[index].oras">
                    </div>
                    <div class="col-lg-2 mb-2">
                        <label for="tara" class="mb-0 ps-3">Țara</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('tara') ? 'is-invalid' : '' }}"
                            :name="'incarcari[' + index + '][tara][nume]'"
                            v-model="incarcari[index].tara.nume"
                            >
                    </div>
                    <div class="col-lg-2 mb-2">
                        <label for="data_ora" class="mb-0 ps-3">Data și ora<span class="text-danger">*</span></label>
                        <vue-datepicker-next
                            :data-veche='incarcari[index].pivot.data_ora'
                            :nume-camp-db="'incarcari[' + index + '][pivot][data_ora]'"
                            tip="datetime"
                            :minute-step="5"
                            value-type="YYYY-MM-DD HH:mm"
                            format="DD.MM.YYYY HH:mm"
                            :latime="{ width: '170px' }"
                        ></vue-datepicker-next>
                    </div>
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-5 mb-2">
                                <label for="observatii" class="form-label mb-0 ps-3">Observații</label>
                                <textarea class="form-control bg-white {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
                                    rows="2"
                                    :name="'incarcari[' + index + '][pivot][observatii]'"
                                    v-model="incarcari[index].pivot.observatii">
                                </textarea>
                            </div>
                            <div class="col-lg-5 mb-2">
                                <label for="referinta" class="form-label mb-0 ps-3">Referință</label>
                                <textarea class="form-control bg-white {{ $errors->has('referinta') ? 'is-invalid' : '' }}"
                                    rows="2"
                                    :name="'incarcari[' + index + '][pivot][referinta]'"
                                    v-model="incarcari[index].pivot.referinta">
                                </textarea>
                            </div>
                            <div class="col-lg-2 mb-3 d-flex justify-content-center align-items-end">
                                <br>
                                {{-- <button type="btn" title="Șterge descărcarea" class="btn btn-danger" @click="this.incarcari.splice(index, 1);">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button> --}}
                                <button type="btn" title="Șterge descărcarea" class="btn btn-danger" @click="this.incarcari.splice(index, 1);">
                                    <span class="badge bg-danger">Șterge încărcarea</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 d-flex justify-content-center py-1">
                        <button type="button" class="btn btn-primary text-white" @click="adaugaIncarcareGoala()">Adaugă încărcare</button>
                    </div>
                </div>
                {{-- <div style="flex" class="">
                    <a
                        href="#"
                        data-bs-toggle="modal"
                        data-bs-target="#adaugaLocOperareNou"
                        title="Adaugă Loc Operare Nou"
                        >
                        <span class="badge bg-danger">Adaugă loc de operare nou</span>
                    </a>
                </div> --}}
            </div>
        </div>
        <div class="row px-2 pt-4 pb-1 d-flex justify-content-center" style="background-color:#B8FFB8; border-left:6px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-12 mb-4 text-center">
                <span class="fs-4 badge text-white" style="background-color:mediumseagreen;">Descărcări</span>
            </div>
            <div class="col-lg-12 mb-4">
                <div class="row align-items-start mb-2" v-for="(descarcare, index) in descarcari" :key="descarcare" style="border:2px solid mediumseagreen;">
                    <div class="col-lg-3 mb-2" style="position:relative;"
                        v-click-out="() => locuriOperareDescarcari[index] = ''"
                        >
                        <label for="nume" class="mb-0 ps-3">Loc de descărcare @{{ index+1 }}<span class="text-danger">*</span></label>
                        <small v-if="(locuriOperareDescarcari[index]) && (locuriOperareDescarcari[index].length >= 100)" class="ps-3 text-danger">Căutarea dvs. returnează mai mult de 100 de înregistrări. Sistemul va afișa primele 100 de înregistrări găsite în baza de date. Vă rugăm să introduceți mai multe caractere pentru a regăsi înregistrările dorite!</small>
                        <input
                            type="hidden"
                            :name="'descarcari[' + index + '][id]'"
                            v-model="descarcari[index].id"
                            >
                        <div class="d-flex">
                            <div class="input-group">
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('nume') ? 'is-invalid' : '' }}"
                                    :name="'descarcari[' + index + '][nume]'"
                                    v-model="descarcari[index].nume"
                                    v-on:focus="getLocuriOperareDescarcari(index, $event.target.value);"
                                    v-on:keyup="getLocuriOperareDescarcari(index, $event.target.value);"
                                    placeholder=""
                                    autocomplete="off"
                                    aria-describedby=""
                                    required>
                                    <div class="input-group-prepend d-flex align-items-center">
                                    </div>
                            </div>
                            <div class="input-group-prepend ms-2 d-flex align-items-center">
                                <button type="submit" ref="submit" :formaction="'{{ $comanda->path() }}/adauga-resursa/loc-operare/descarcari/' + index" class="btn btn-success text-white rounded-3 py-0 px-2"
                                    style="font-size: 30px; line-height: 1.2;" title="Adaugă loc operare nou">+</button>
                            </div>
                        </div>
                        <div v-cloak v-if="locuriOperareDescarcari[index] && locuriOperareDescarcari[index].length" class="panel-footer" style="width:100%; position:absolute; z-index: 1000;">
                            <div class="list-group" style="max-height: 218px; overflow:auto;">
                                <button class="list-group-item list-group-item list-group-item-action py-0"
                                    v-for="locOperare in locuriOperareDescarcari[index]"
                                    v-on:click="
                                        descarcari[index].id = locOperare.id;
                                        descarcari[index].nume = locOperare.nume;
                                        descarcari[index].adresa = locOperare.adresa;
                                        descarcari[index].oras = locOperare.oras;
                                        descarcari[index].tara.nume = locOperare.tara.nume;

                                        locuriOperareDescarcari = ''
                                    ">
                                        @{{ locOperare.nume }}
                                </button>
                            </div>
                        </div>
                        <small v-if="!descarcari[index].nume || (descarcari[index].nume.length < 3)" class="ps-3">* Introduceți minim 3 caractere</small>
                    </div>
                    <div class="col-lg-3 mb-2">
                        <label for="adresa" class="mb-0 ps-3">Adresa</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('adresa') ? 'is-invalid' : '' }}" readonly
                            :name="'descarcari[' + index + '][adresa]'"
                            v-model="descarcari[index].adresa">
                    </div>
                    <div class="col-lg-2 mb-2">
                        <label for="oras" class="mb-0 ps-3">Oraș</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('oras') ? 'is-invalid' : '' }}" readonly
                            :name="'descarcari[' + index + '][oras]'"
                            v-model="descarcari[index].oras">
                    </div>
                    <div class="col-lg-2 mb-2">
                        <label for="tara" class="mb-0 ps-3">Țara</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('tara') ? 'is-invalid' : '' }}" readonly
                            :name="'descarcari[' + index + '][tara][nume]'"
                            v-model="descarcari[index].tara.nume">
                    </div>
                    <div class="col-lg-2 mb-2">
                        <label for="data_ora" class="mb-0 ps-3">Data și ora<span class="text-danger">*</span></label>
                        <vue-datepicker-next
                            :data-veche='descarcari[index].pivot.data_ora'
                            :nume-camp-db="'descarcari[' + index + '][pivot][data_ora]'"
                            tip="datetime"
                            :minute-step="5"
                            value-type="YYYY-MM-DD HH:mm"
                            format="DD.MM.YYYY HH:mm"
                            :latime="{ width: '170px' }"
                        ></vue-datepicker-next>
                    </div>
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-lg-5 mb-2">
                                <label for="observatii" class="form-label mb-0 ps-3">Observații</label>
                                <textarea class="form-control bg-white {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
                                    rows="2"
                                    :name="'descarcari[' + index + '][pivot][observatii]'"
                                    v-model="descarcari[index].pivot.observatii">
                                </textarea>
                            </div>
                            <div class="col-lg-5 mb-2">
                                <label for="referinta" class="form-label mb-0 ps-3">Referință</label>
                                <textarea class="form-control bg-white {{ $errors->has('referinta') ? 'is-invalid' : '' }}"
                                    rows="2"
                                    :name="'descarcari[' + index + '][pivot][referinta]'"
                                    v-model="descarcari[index].pivot.referinta">
                                </textarea>
                            </div>
                            <div class="col-lg-2 mb-3 d-flex justify-content-center align-items-end">
                                <br>
                                {{-- <button type="btn" title="Șterge descărcarea" class="btn btn-danger" @click="this.descarcari.splice(index, 1);">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button> --}}
                                <button type="btn" title="Șterge descărcarea" class="btn btn-danger" @click="this.descarcari.splice(index, 1);">
                                    <span class="badge bg-danger">Șterge descărcarea</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-lg-12 d-flex justify-content-center py-1">
                        <button type="button" class="btn btn-success text-white" @click="adaugaDescarcareGoala()">Adaugă descărcare</button>
                    </div>
                </div>
            </div>
        </div>
        {{-- <div class="row px-2 pt-4 pb-2 mb-0" style="background-color:#B8FFB8; border-left:6px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-2 mb-4">
                <label for="persoana_contact" class="mb-0 ps-3">Transportator</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('persoana_contact') ? 'is-invalid' : '' }}"
                    name="persoana_contact"
                    placeholder=""
                    value="{{ old('persoana_contact', $comanda->persoana_contact) }}">
            </div>
        </div>
        <div class="row px-2 py-2 mb-4" style="background-color:#B8FFB8; border-left:6px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-6 mb-4">
                <label for="observatii" class="form-label mb-0 ps-3">Observații</label>
                <textarea class="form-control bg-white {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
                    name="observatii" rows="3">{{ old('observatii', $comanda->observatii) }}</textarea>
            </div>
        </div> --}}
        <div class="row py-4">
            <div class="col-lg-12 mb-0 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('ComandaReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>


    {{-- Modala transportator --}}
    {{-- <div class="modal fade text-dark" id="adaugaTransportator" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white" id="exampleModalLabel">Firma</h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="text-align:left;">
                <form class="needs-validation" novalidate method="POST" action="/firme/transportatori">
                    @include ('firme.form', [
                        'firma' => new App\Models\Firma,
                        'tipPartener' => 'transportatori',
                        'tari' => App\Models\Tara::select('id', 'nume')->orderBy('nume')->get(),
                        'buttonText' => 'Adaugă Firma'
                    ])
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    Salvează
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
            </div>
            </div>
        </div>
    </div> --}}


</div>

