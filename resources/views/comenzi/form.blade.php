@csrf

<script type="application/javascript">
    firmeTransportatori = {!! json_encode($firmeTransportatori) !!}
    firmaTransportatorIdVechi = {!! json_encode(old('transportator_transportator_id', ($comanda->transportator_transportator_id ?? "")) ?? "") !!}
    firmeClienti = {!! json_encode($firmeClienti) !!}
    firmaClientIdVechi = {!! json_encode(old('client_client_id', ($comanda->client_client_id ?? "")) ?? "") !!}
    camioane = {!! json_encode($camioane) !!}
    camionIdVechi = {!! json_encode(old('camion_id', ($comanda->camion_id ?? "")) ?? "") !!}
</script>

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="formularComanda">
    <div class="col-lg-12 px-4 pt-2 mb-0">
        <div class="row px-2 pt-4 pb-1 mb-0" style="background-color:lightyellow; border-left:6px solid; border-color:goldenrod">
            <div class="col-lg-3 mb-4 text-center mx-auto">
                <label for="data_creare" class="mb-0 ps-3">Dată creare{{ (Route::currentRouteName() === "comenzi.create") ? \Carbon\Carbon::today() : ''  }}<span class="text-danger">*</span></label>
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
                <select name="transportator_limba_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_limba_id') ? 'is-invalid' : '' }}" v-on:focus="golireListe();">
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
            <div class="col-lg-3 mb-4">
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
                        v-on:focus="golireListe(); autocompleteFirmeTransportatori();"
                        v-on:keyup="autocompleteFirmeTransportatori(); this.firmaTransportatorId = '';"
                        class="form-control bg-white rounded-3 {{ $errors->has('firmaTransportatorNume') ? 'is-invalid' : '' }}"
                        name="firmaTransportatorNume"
                        placeholder=""
                        autocomplete="off"
                        aria-describedby="firmaTransportatorNume"
                        required>
                    <div class="input-group-prepend d-flex align-items-center">
                        <div v-if="firmaTransportatorId" class="input-group-text p-2 text-danger" id="firmaTransportatorNume" v-on:click="firmaTransportatorId = null; firmaTransportatorNume = ''"><i class="fa-solid fa-xmark"></i></div>
                    </div>
                </div>
                <div v-cloak v-if="firmeTransportatoriListaAutocomplete && firmeTransportatoriListaAutocomplete.length" class="panel-footer">
                    <div class="list-group" style="max-height: 130px; overflow:auto;">
                        <button class="list-group-item list-group-item list-group-item-action py-0"
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
                    v-on:focus="golireListe();"
                    class="form-control bg-white rounded-3 {{ $errors->has('transportator_valoare_contract') ? 'is-invalid' : '' }}"
                    name="transportator_valoare_contract"
                    placeholder=""
                    value="{{ old('transportator_valoare_contract', $comanda->transportator_valoare_contract) }}">
                <small for="transportator_valoare_contract" class="mb-0 ps-3">*Punct(.) pentru zecimale</small>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="transportator_moneda_id" class="mb-0 ps-3">Monedă</label>
                <select name="transportator_moneda_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_moneda_id') ? 'is-invalid' : '' }}" v-on:focus="golireListe();">
                    <option selected></option>
                    @foreach ($monede as $moneda)
                        <option value="{{ $moneda->id }}" {{ ($moneda->id === intval(old('transportator_moneda_id', $comanda->transportator_moneda_id ?? ''))) ? 'selected' : '' }}>{{ $moneda->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="transportator_procent_tva_id" class="mb-0 ps-3">Procent TVA</label>
                <select name="transportator_procent_tva_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_procent_tva_id') ? 'is-invalid' : '' }}" v-on:focus="golireListe();">
                    <option selected></option>
                    @foreach ($procenteTVA as $procentTVA)
                        <option value="{{ $procentTVA->id }}" {{ ($procentTVA->id === intval(old('transportator_procent_tva_id', $comanda->transportator_procent_tva_id ?? ''))) ? 'selected' : '' }}>{{ $procentTVA->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="transportator_metoda_de_plata_id" class="mb-0 ps-3">Metodă de plată</label>
                <select name="transportator_metoda_de_plata_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_metoda_de_plata_id') ? 'is-invalid' : '' }}" v-on:focus="golireListe();">
                    <option selected></option>
                    @foreach ($metodeDePlata as $metodaDePlata)
                        <option value="{{ $metodaDePlata->id }}" {{ ($metodaDePlata->id === intval(old('transportator_metoda_de_plata_id', $comanda->transportator_metoda_de_plata_id ?? ''))) ? 'selected' : '' }}>{{ $metodaDePlata->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 mb-4">
                <label for="transportator_termen_plata_id" class="mb-0 ps-3">Termen de plată</label>
                <select name="transportator_termen_plata_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_termen_plata_id') ? 'is-invalid' : '' }}" v-on:focus="golireListe();">
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
                    v-on:focus="golireListe();"
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
                <select name="client_limba_id" class="form-select bg-white rounded-3 {{ $errors->has('client_limba_id') ? 'is-invalid' : '' }}" v-on:focus="golireListe();">
                    <option selected></option>
                    @foreach ($limbi as $limba)
                        <option value="{{ $limba->id }}" {{ ($limba->id === intval(old('client_limba_id', $comanda->client_limba_id ?? ''))) ? 'selected' : '' }}>{{ $limba->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 mb-4">
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
                        v-on:focus="golireListe(); autocompleteFirmeClienti();"
                        v-on:keyup="autocompleteFirmeClienti(); this.firmaClientId = '';"
                        class="form-control bg-white rounded-3 {{ $errors->has('firmaClientNume') ? 'is-invalid' : '' }}"
                        name="firmaClientNume"
                        placeholder=""
                        autocomplete="off"
                        aria-describedby="firmaClientNume"
                        required>
                    <div class="input-group-prepend d-flex align-items-center">
                        <div v-if="firmaClientId" class="input-group-text p-2 text-danger" id="firmaClientNume" v-on:click="firmaClientId = null; firmaClientNume = ''"><i class="fa-solid fa-xmark"></i></div>
                    </div>
                </div>
                <div v-cloak v-if="firmeClientiListaAutocomplete && firmeClientiListaAutocomplete.length" class="panel-footer">
                    <div class="list-group" style="max-height: 130px; overflow:auto;">
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
                    v-on:focus="golireListe();"
                    class="form-control bg-white rounded-3 {{ $errors->has('client_valoare_contract') ? 'is-invalid' : '' }}"
                    name="client_valoare_contract"
                    placeholder=""
                    value="{{ old('client_valoare_contract', $comanda->client_valoare_contract) }}">
                <small for="client_valoare_contract" class="mb-0 ps-3">*Punct(.) pentru zecimale</small>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="client_moneda_id" class="mb-0 ps-3">Monedă</label>
                <select name="client_moneda_id" class="form-select bg-white rounded-3 {{ $errors->has('client_moneda_id') ? 'is-invalid' : '' }}" v-on:focus="golireListe();">
                    <option selected></option>
                    @foreach ($monede as $moneda)
                        <option value="{{ $moneda->id }}" {{ ($moneda->id === intval(old('client_moneda_id', $comanda->client_moneda_id ?? ''))) ? 'selected' : '' }}>{{ $moneda->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="client_procent_tva_id" class="mb-0 ps-3">Procent TVA</label>
                <select name="client_procent_tva_id" class="form-select bg-white rounded-3 {{ $errors->has('client_procent_tva_id') ? 'is-invalid' : '' }}" v-on:focus="golireListe();">
                    <option selected></option>
                    @foreach ($procenteTVA as $procentTVA)
                        <option value="{{ $procentTVA->id }}" {{ ($procentTVA->id === intval(old('client_procent_tva_id', $comanda->client_procent_tva_id ?? ''))) ? 'selected' : '' }}>{{ $procentTVA->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="client_metoda_de_plata_id" class="mb-0 ps-3">Metodă de plată</label>
                <select name="client_metoda_de_plata_id" class="form-select bg-white rounded-3 {{ $errors->has('client_metoda_de_plata_id') ? 'is-invalid' : '' }}" v-on:focus="golireListe();">
                    <option selected></option>
                    @foreach ($metodeDePlata as $metodaDePlata)
                        <option value="{{ $metodaDePlata->id }}" {{ ($metodaDePlata->id === intval(old('client_metoda_de_plata_id', $comanda->client_metoda_de_plata_id ?? ''))) ? 'selected' : '' }}>{{ $metodaDePlata->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 mb-4">
                <label for="client_termen_plata_id" class="mb-0 ps-3">Termen de plată</label>
                <select name="client_termen_plata_id" class="form-select bg-white rounded-3 {{ $errors->has('client_termen_plata_id') ? 'is-invalid' : '' }}" v-on:focus="golireListe();">
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
                    v-on:focus="golireListe();"
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
                <textarea class="form-control bg-white {{ $errors->has('descriere_marfa') ? 'is-invalid' : '' }}" v-on:focus="golireListe();"
                    name="descriere_marfa" rows="3">{{ old('descriere_marfa', $comanda->descriere_marfa) }}</textarea>
            </div>
            <div class="col-lg-3 mb-4">
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
                        v-on:focus="golireListe(); autocompleteCamioane();"
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
                </div>
                <div v-cloak v-if="camioaneListaAutocomplete && camioaneListaAutocomplete.length" class="panel-footer">
                    <div class="list-group" style="max-height: 130px; overflow:auto;">
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
                <div class="row align-items-start mb-0" v-for="incarcare in numarIncarcari" :key="incarcare">
                    <div class="col-lg-5 mb-2">
                        <label for="nume" class="mb-0 ps-3">Nume<span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('nume') ? 'is-invalid' : '' }}"
                            :name="'incarcari[nume][' + incarcare + ']'"
                            v-model="incarcariNume[incarcare-1]">
                    </div>
                    <div class="col-lg-3 mb-2">
                        <label for="oras" class="mb-0 ps-3">Oraș</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('oras') ? 'is-invalid' : '' }}"
                            :name="'incarcari[oras][' + incarcare + ']'"
                            v-model="incarcariOras[incarcare-1]">
                    </div>
                    {{-- <div class="col-lg-1 d-flex align-items-center border border-dark border-1">
                        <button  type="button" class="btn m-0 p-0 mb-1" @click="stergeMedicament(medicament-1)">
                            <span class="px-1 badge" style="background-color:red; color:white; border-radius:20px">
                                Șterge
                            </span>
                        </button>
                    </div> --}}
                </div>
                <div class="row">
                    <div class="col-lg-12 d-flex justify-content-center py-1">
                        <input type="hidden" name="numarIncarcari" v-model="numarIncarcari">
                        <button type="button" class="btn btn-success text-white" @click="numarIncarcari++">Adaugă încărcare</button>
                    </div>
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
        <div class="row">
            <div class="col-lg-12 mb-4 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('ComandaReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
