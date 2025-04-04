@csrf
@php
    // echo old('transportator_transportator_id');
    // echo json_encode(old('transportator_transportator_id'));
    // dd(old('incarcari'), old('transportator_contract'), old('client_zile_scadente'));
@endphp
<script type="application/javascript">
    firmeTransportatori = {!! json_encode($firmeTransportatori) !!}
    firmaTransportatorIdVechi = {!! json_encode(old('transportator_transportator_id', ($comanda->transportator_transportator_id ?? "")) ?? "") !!}

    // Comented on 14.01.2025 - after that we went to more that one client to a command
    firmeClienti = {!! json_encode($firmeClienti) !!}
    firmaClientIdVechi = {!! json_encode(old('client_client_id', ($comanda->client_client_id ?? "")) ?? "") !!}
    // Added on 14.01.2025 - after that we went to more that one client to a command
    clientiAtasatiLaComanda =  {!! json_encode(old('clienti', $comanda->clienti()->get())) !!}

    camioane = {!! json_encode($camioane) !!}
    camionIdVechi = {!! json_encode(old('camion_id', ($comanda->camion_id ?? "")) ?? "") !!}

    incarcari =  {!! json_encode(old('incarcari', $comanda->locuriOperareIncarcari()->get())) !!}
    descarcari =  {!! json_encode(old('descarcari', $comanda->locuriOperareDescarcari()->get())) !!}

    transportatorTarifPeKmVechi = {!! json_encode(old('transportator_tarif_pe_km', ($comanda->transportator_tarif_pe_km ?? "0")) ?? "") !!}
    transportatorPretKmGoiVechi = {!! json_encode(old('transportator_pret_km_goi', ($comanda->transportator_pret_km_goi ?? "")) ?? "") !!}
    transportatorPretKmPliniVechi = {!! json_encode(old('transportator_pret_km_plini', ($comanda->transportator_pret_km_plini ?? "")) ?? "") !!}
    transportatorKmGoiVechi = {!! json_encode(old('transportator_km_goi', ($comanda->transportator_km_goi ?? "")) ?? "") !!}
    transportatorKmPliniVechi = {!! json_encode(old('transportator_km_plini', ($comanda->transportator_km_plini ?? "")) ?? "") !!}
    transportatorValoareKmGoiVechi = {!! json_encode(old('transportator_valoare_km_goi', ($comanda->transportator_valoare_km_goi ?? "")) ?? "") !!}
    transportatorValoareKmPliniVechi = {!! json_encode(old('transportator_valoare_km_plini', ($comanda->transportator_valoare_km_plini ?? "")) ?? "") !!}
    transportatorValoareContractVechi = {!! json_encode(old('transportator_valoare_contract', ($comanda->transportator_valoare_contract ?? "")) ?? "") !!}

    transportatorPretAutostradaVechi = {!! json_encode(old('transportator_pret_autostrada', ($comanda->transportator_pret_autostrada ?? 0)) ?? "") !!}
    transportatorPretFerryVechi = {!! json_encode(old('transportator_pret_ferry', ($comanda->transportator_pret_ferry ?? 0)) ?? "") !!}

    clientValoareContractInitialaVechi = {!! json_encode(old('client_valoare_contract_initiala', ($comanda->client_valoare_contract_initiala ?? "")) ?? "") !!}
    clientValoareContractVechi = {!! json_encode(old('client_valoare_contract', ($comanda->client_valoare_contract ?? "")) ?? "") !!}
</script>

@php
    // dd(old('transportator_tarif_pe_km', ($comanda->transportator_tarif_pe_km ?? "")));
@endphp

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="formularComanda">
    <div class="col-lg-12 px-4 pt-2 mb-0">
        <div class="row px-2 pt-4 pb-1 mb-0" style="background-color:lightyellow; border-left:6px solid; border-color:goldenrod">
            <div class="col-lg-3 mb-4 text-center mx-auto">
                <label for="data_creare" class="mb-0 ps-3">Dată creare<span class="text-danger">*</span></label>
                <vue-datepicker-next
                    data-veche="{{ old('data_creare', $comanda->data_creare) }}"
                    nume-camp-db="data_creare"
                    tip="date"
                    value-type="YYYY-MM-DD"
                    format="DD.MM.YYYY"
                    :latime="{ width: '125px' }"
                ></vue-datepicker-next>
            </div>

            <div class="col-lg-3 mb-4">
                <label for="user_id" class="mb-0 ps-3">Utilizator</label>
                <select name="user_id" class="form-select bg-white rounded-3 {{ $errors->has('user_id') ? 'is-invalid' : '' }}">
                    @foreach ($useri as $user)
                        <option value="{{ $user->id }}" {{ ($user->id === intval(old('user_id', $comanda->user_id ?? ''))) ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 mb-4">
                <label for="operator_user_id" class="mb-0 ps-3">Operator</label>
                <select name="operator_user_id" class="form-select bg-white rounded-3 {{ $errors->has('operator_user_id') ? 'is-invalid' : '' }}">
                        <option value="" selected></option>
                    @foreach ($useri as $user)
                        <option value="{{ $user->id }}" {{ ($user->id === intval(old('operator_user_id', $comanda->operator_user_id ?? ''))) ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-3 mb-4 text-center mx-auto">
                <label for="interval_notificari" class="mb-0 ps-3">Interval notificari<span class="text-danger">*</span></label>
                <vue-datepicker-next
                    data-veche="{{ old('interval_notificari', $comanda->interval_notificari) }}"
                    nume-camp-db="interval_notificari"
                    tip="time"
                    :minute-step="5"
                    value-type="HH:mm:ss"
                    format="HH:mm"
                    :latime="{ width: '100px' }"
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
                            :title="(firma.tara?.nume ?? '') + ', ' +
                                    (firma.cui ?? '') + ', ' +
                                    (firma.oras ?? '') + ', ' +
                                    (firma.judet ?? '') + ', ' +
                                    (firma.adresa ?? '') + ', ' +
                                    (firma.cod_postal ?? '')"
                            v-on:click="
                                firmaTransportatorId = firma.id;
                                firmaTransportatorNume = firma.nume;

                                firmeTransportatoriListaAutocomplete = ''
                            ">
                                @{{ firma.nume }}
                                <small class="px-2 rounded-3" style="color:white; background-color:#2196F3"> i </small>
                        </button>
                    </div>
                </div>
                <small v-if="!firmaTransportatorId" class="ps-3">*Selectați un transportator</small>
                <small v-else class="ps-3 text-success">*Ați selectat transportatorul</small>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="transportator_valoare_contract" class="mb-0 ps-3">Valoare contract<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('transportator_valoare_contract') ? 'is-invalid' : '' }}"
                    name="transportator_valoare_contract"
                    placeholder=""
                    {{-- value="{{ old('transportator_valoare_contract', $comanda->transportator_valoare_contract) }}"> --}}
                    v-model="transportatorValoareContract">
                <small for="transportator_valoare_contract" class="mb-0 ps-3">*Punct(.) pentru zecimale</small>
            </div>
            <div class="col-lg-2 mb-4 d-flex align-items-center">
                {{-- <label for="transportator_moneda_id" class="mb-0 ps-3">Monedă<span class="text-danger">*</span></label>
                <select name="transportator_moneda_id" class="form-select bg-white rounded-3 {{ $errors->has('transportator_moneda_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($monede as $moneda)
                        <option value="{{ $moneda->id }}" {{ ($moneda->id === intval(old('transportator_moneda_id', $comanda->transportator_moneda_id ?? ''))) ? 'selected' : '' }}>{{ $moneda->nume }}</option>
                    @endforeach
                </select> --}}

                <input
                    type="hidden"
                    name="transportator_moneda_id"
                    value="{{ old('transportator_moneda_id', $comanda->transportator_moneda_id ?? '') }}"
                >
                <span class="text-danger">* ATENȚIE EURO!</span>
            </div>
            <div class="col-lg-1 mb-4">
                <label for="transportator_procent_tva_id" class="mb-0 ps-3 small">% TVA</label>
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
                <label for="transportator_format_documente" class="mb-0 ps-3">Format documente<span class="text-danger">*</span></label>
                <select name="transportator_format_documente" class="form-select bg-white rounded-3 {{ $errors->has('transportator_format_documente') ? 'is-invalid' : '' }}">
                    <option selected></option>
                        <option value="1" {{ intval(old('transportator_format_documente', $comanda->transportator_format_documente === 1 )) ? 'selected' : '' }}>Per post</option>
                        <option value="2" {{ intval(old('transportator_format_documente', $comanda->transportator_format_documente === 2 )) ? 'selected' : '' }}>Digital</option>
                </select>
            </div>
            <div class="col-lg-2 mb-4">
                <div class="text-center">
                    {{-- Tarif Pe Km? --}}
                    <label class="mb-0 ps-3">Tarif Pe Km</label>
                    <div class="d-flex py-1 justify-content-center">
                        <div class="form-check me-4">
                            <input class="form-check-input" type="radio" value="1" name="transportator_tarif_pe_km" id="transportator_tarif_pe_km_da"
                                {{-- {{ old('transportator_tarif_pe_km', $comanda->transportator_tarif_pe_km) == '1' ? 'checked' : '' }} --}}
                                v-model="transportatorTarifPeKm"
                            >
                            <label class="form-check-label" for="transportator_tarif_pe_km_da">Da</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" value="0" name="transportator_tarif_pe_km" id="transportator_tarif_pe_km_nu"
                                {{-- {{ old('transportator_tarif_pe_km', $comanda->transportator_tarif_pe_km) == '0' ? 'checked' : '' }} --}}
                                v-model="transportatorTarifPeKm"
                            >
                            <label class="form-check-label" for="transportator_tarif_pe_km_nu">Nu</label>
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="transportatorTarifPeKm == 1" class="col-lg-12 mb-4">
                <div class="row d-flex justify-content-center">
                    <div class="col-lg-6">
                        <div class="row">
                            <div class="col-lg-4 mb-2">
                                <label for="transportator_km_goi" class="mb-0 ps-3">Km goi</label>
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('transportator_km_goi') ? 'is-invalid' : '' }}"
                                    name="transportator_km_goi"
                                    placeholder=""
                                    v-model="transportatorKmGoi">
                            </div>
                            <div class="col-lg-4 mb-2">
                                <label for="transportator_pret_km_goi" class="mb-0 ps-3">Preț km goi</label>
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('transportator_pret_km_goi') ? 'is-invalid' : '' }}"
                                    name="transportator_pret_km_goi"
                                    placeholder=""
                                    v-model="transportatorPretKmGoi">
                            </div>
                            <div class="col-lg-4 mb-2">
                                <label for="transportator_valoare_km_goi" class="mb-0 ps-3">Valoare km goi</label>
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('transportator_valoare_km_goi') ? 'is-invalid' : '' }}"
                                    name="transportator_valoare_km_goi"
                                    placeholder=""
                                    v-model="transportatorValoareKmGoi">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 mb-2">
                                <label for="transportator_km_plini" class="mb-0 ps-3">Km plini</label>
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('transportator_km_plini') ? 'is-invalid' : '' }}"
                                    name="transportator_km_plini"
                                    placeholder=""
                                    v-model="transportatorKmPlini">
                            </div>
                            <div class="col-lg-4 mb-2">
                                <label for="transportator_pret_km_plini" class="mb-0 ps-3">Preț km plini</label>
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('transportator_pret_km_plini') ? 'is-invalid' : '' }}"
                                    name="transportator_pret_km_plini"
                                    placeholder=""
                                    v-model="transportatorPretKmPlini">
                            </div>
                            <div class="col-lg-4 mb-2">
                                <label for="transportator_valoare_km_plini" class="mb-0 ps-3">Valoare km plini</label>
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('transportator_valoare_km_plini') ? 'is-invalid' : '' }}"
                                    name="transportator_valoare_km_plini"
                                    placeholder=""
                                    v-model="transportatorValoareKmPlini">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-lg-4 mb-2">
                                <label for="transportator_pret_autostrada" class="mb-0 ps-3">Preț autostradă</label>
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('transportator_pret_autostrada') ? 'is-invalid' : '' }}"
                                    name="transportator_pret_autostrada"
                                    placeholder=""
                                    v-model="transportatorPretAutostrada">
                            </div>
                        </div>
                        <div class="row">
                            {{-- Removed on 18.02.2025 - ferry price is not used anymore, because is not exactly known when the command is registered, so it's added later in 'intermedieri'. --}}
                            {{-- <div class="col-lg-4 mb-2">
                                <label for="transportator_pret_ferry" class="mb-0 ps-3">Preț ferry</label>
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('transportator_pret_ferry') ? 'is-invalid' : '' }}"
                                    name="transportator_pret_ferry"
                                    placeholder=""
                                    v-model="transportatorPretFerry">
                            </div> --}}
                        </div>
                        <div class="row">
                            <div class="col-lg-4 mb-2">
                            </div>
                            <div class="col-lg-4 mb-2">
                                <button type="button" class="btn btn-primary px-0 w-100 text-white rounded-3"
                                    v-on:click="
                                        transportatorPretKmGoi = camionPretKmGoi;
                                        transportatorPretKmPlini = camionPretKmPlini;
                                    ">Preia prețurile Camionului</button>
                            </div>
                            <div class="col-lg-4 mb-2">
                                <button type="button" class="btn btn-primary px-0 w-100 text-white rounded-3"
                                    v-on:click="
                                        transportatorValoareKmGoi = (transportatorKmGoi * transportatorPretKmGoi).toFixed(2);
                                        transportatorValoareKmPlini = (transportatorKmPlini * transportatorPretKmPlini).toFixed(2);
                                        transportatorValoareContract = (Number(transportatorValoareKmGoi) + Number(transportatorValoareKmPlini) + Number(transportatorPretAutostrada)).toFixed(2);
                                    ">Calculează valorile</button>
                            </div>
                        </div>
                        {{-- <div class="row">
                            <div class="col-lg-12 mb-2">
                                <div v-if="alertaCampuriNecompletate" class="text-center">
                                    <span class="px-1 bg-danger text-white rounded">
                                        @{{ alertaCampuriNecompletate }}
                                    </span>
                                    <br>
                                </div>
                                <small>
                                    * Completează inclusiv „Clienți - Valoare contract inițială” înainte de a „Calcula valorile”
                                </small>
                            </div>
                        </div> --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- Removed on 14.01.2025 - to set more clients to a command, not just one --}}
        {{-- <div class="row px-2 pt-4 pb-1 d-flex justify-content-center" style="background-color:#B8FFB8; border-left:6px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px">
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
                <label for="client_valoare_contract_initiala" class="mb-0 ps-0 small">Valoare contract inițială<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('client_valoare_contract_initiala') ? 'is-invalid' : '' }}"
                    name="client_valoare_contract_initiala"
                    placeholder=""
                    v-model="clientValoareContractInitiala">
                <small for="client_valoare_contract" class="mb-0 ps-3">*Punct(.) pentru zecimale</small>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="client_valoare_contract" class="mb-0 ps-0 small">Valoare contract finală<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('client_valoare_contract') ? 'is-invalid' : '' }}"
                    name="client_valoare_contract"
                    placeholder=""
                    v-model="clientValoareContract">
                <small for="client_valoare_contract" class="mb-0 ps-3">*Punct(.) pentru zecimale</small>
            </div>
            <div class="col-lg-2 mb-4">
                <label for="client_moneda_id" class="mb-0 ps-3">Monedă<span class="text-danger">*</span></label>
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
        </div> --}}

        {{-- Added on 14.01.2025 - to set more clients to a command, not just one --}}
        <div class="row px-2 pt-4 pb-1 d-flex justify-content-center" style="background-color:#B8FFB8; border-left:6px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-12 mb-4 text-center">
                <span class="fs-4 badge text-white" style="background-color:mediumseagreen;">Clienți</span>
            </div>
            <div class="col-lg-12 mb-4">
                <div class="row mb-2" v-for="(client, index) in clientiAtasatiLaComanda" :key="client" style="border:2px solid rgb(15, 97, 52);">
                    <div class="col-lg-2 mb-2">
                        <input
                            type="hidden"
                            :name="'clienti[' + index + '][pivot][id]'"
                            v-model="clientiAtasatiLaComanda[index].pivot.id"
                            >

                        <label for="contract" class="mb-0 ps-3">Contract<span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('contract') ? 'is-invalid' : '' }}"
                            :name="'clienti[' + index + '][pivot][contract]'"
                            v-model="clientiAtasatiLaComanda[index].pivot.contract">
                    </div>
                    <div class="col-lg-2 mb-2">
                        <label for="limba_id" class="mb-0 ps-3">Limba</label>
                        <select
                            :name="'clienti[' + index + '][pivot][limba_id]'"
                            v-model="clientiAtasatiLaComanda[index].pivot.limba_id"
                            class="form-select bg-white rounded-3 {{ $errors->has('limba_id') ? 'is-invalid' : '' }}"
                        >
                            <option value="" selected disabled></option>
                            @foreach ($limbi as $limba)
                                <option value="{{ $limba->id }}">{{ $limba->nume }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4 mb-2" style="position:relative;"
                        v-click-out="() => clientiListaTotiDinDB[index] = ''"
                        >
                        <label for="nume" class="mb-0 ps-3">Client @{{ index+1 }}<span class="text-danger">*</span></label>
                        <small v-if="(clientiListaTotiDinDB[index]) && (clientiListaTotiDinDB[index].length >= 100)" class="ps-3 text-danger">Căutarea dvs. returnează mai mult de 100 de înregistrări. Sistemul va afișa primele 100 de înregistrări găsite în baza de date. Vă rugăm să introduceți mai multe caractere pentru a regăsi înregistrările dorite!</small>
                        <input
                            type="hidden"
                            :name="'clienti[' + index + '][id]'"
                            v-model="clientiAtasatiLaComanda[index].id"
                            >
                        <div class="d-flex">
                            <div class="input-group-prepend d-flex align-items-center">
                                <div v-if="!clientiAtasatiLaComanda[index].id" class="input-group-text" id="firmaClientNume">?</div>
                                <div v-if="clientiAtasatiLaComanda[index].id" class="input-group-text p-2 bg-success text-white" id="firmaClientNume"><i class="fa-solid fa-check" style="height:100%"></i></div>
                            </div>
                            <div class="input-group">
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('nume') ? 'is-invalid' : '' }}"
                                    :name="'clienti[' + index + '][nume]'"
                                    v-model="clientiAtasatiLaComanda[index].nume"
                                    v-on:focus="getClienti(index);"
                                    v-on:keyup="getClienti(index);"
                                    placeholder=""
                                    autocomplete="off"
                                    aria-describedby=""
                                    required>
                                    <div class="input-group-prepend d-flex align-items-center">
                                    </div>
                            </div>
                            <div class="input-group-prepend d-flex align-items-center">
                                <div v-if="clientiAtasatiLaComanda[index].id" class="input-group-text p-2 text-danger" id="" v-on:click="clientiAtasatiLaComanda[index].id = null; clientiAtasatiLaComanda[index].nume = ''"><i class="fa-solid fa-xmark"></i></div>
                            </div>
                            <div class="input-group-prepend ms-2 d-flex align-items-center">
                                <button type="submit" ref="submit" :formaction="'{{ $comanda->path() }}/adauga-resursa/client/client/' + index" class="btn btn-success text-white rounded-3 py-0 px-2"
                                    style="font-size: 30px; line-height: 1.2;" title="Adaugă client nou">+</button>
                            </div>
                        </div>
                        <div v-cloak v-if="clientiListaTotiDinDB[index] && clientiListaTotiDinDB[index].length" class="panel-footer" style="width:100%; position:absolute; z-index: 1000;">
                            <div class="list-group" style="max-height: 218px; overflow:auto;">
                                <button class="list-group-item list-group-item list-group-item-action py-0"
                                    v-for="client in clientiListaTotiDinDB[index]"
                                    :title="(client.tara?.nume ?? '') + ', ' +
                                            (client.cui ?? '') + ', ' +
                                            (client.oras ?? '') + ', ' +
                                            (client.judet ?? '') + ', ' +
                                            (client.adresa ?? '') + ', ' +
                                            (client.cod_postal ?? '')"
                                    v-on:click="
                                        clientiAtasatiLaComanda[index].id = client.id;
                                        clientiAtasatiLaComanda[index].nume = client.nume;
                                        clientiAtasatiLaComanda[index].client_limba_id = 1;
                                        clientiAtasatiLaComanda[index].client_tarif_pe_km = 0;

                                        clientiListaTotiDinDB = ''
                                    ">
                                        @{{ client.nume }}
                                <small class="px-2 rounded-3" style="color:white; background-color:#2196F3"> i </small>
                                </button>
                        </button>
                            </div>
                        </div>
                        <small v-if="!clientiAtasatiLaComanda[index].nume || (clientiAtasatiLaComanda[index].nume.length < 3)" class="ps-3">* Introduceți minim 3 caractere</small>
                    </div>
                    <div class="col-lg-2 mb-2">
                        <label for="valoare_contract_initiala" class="mb-0 ps-0 small">Valoare contract inițială<span class="text-danger">*</span></label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('valoare_contract_initiala') ? 'is-invalid' : '' }}"
                            :name="'clienti[' + index + '][pivot][valoare_contract_initiala]'"
                            v-model="clientiAtasatiLaComanda[index].pivot.valoare_contract_initiala">
                        <small for="valoare_contract_initiala" class="mb-0 ps-3">*Punct(.) pentru zecimale</small>
                    </div>
                    <div class="col-lg-2 mb-2 d-flex align-items-center">
                        {{-- <label for="moneda_id" class="mb-0 ps-3">Moneda<span class="text-danger">*</span></label>
                        <select
                            :name="'clienti[' + index + '][pivot][moneda_id]'"
                            v-model="clientiAtasatiLaComanda[index].pivot.moneda_id"
                            class="form-select bg-white rounded-3 {{ $errors->has('moneda_id') ? 'is-invalid' : '' }}"
                        >
                            <option value="" selected disabled></option>
                            @foreach ($monede as $moneda)
                                <option value="{{ $moneda->id }}">{{ $moneda->nume }}</option>
                            @endforeach
                        </select> --}}
                        <input
                            type="hidden"
                            :name="'clienti[' + index + '][pivot][moneda_id]'"
                            v-model="clientiAtasatiLaComanda[index].pivot.moneda_id"
                        >
                        <span class="text-danger">* ATENȚIE EURO!</span>
                    </div>
                    <div class="col-lg-1 mb-2">
                        <label for="procent_tva_id" class="mb-0 ps-3">% TVA</label>
                        <select
                            :name="'clienti[' + index + '][pivot][procent_tva_id]'"
                            v-model="clientiAtasatiLaComanda[index].pivot.procent_tva_id"
                            class="form-select bg-white rounded-3 {{ $errors->has('procent_tva_id') ? 'is-invalid' : '' }}"
                        >
                            <option value="" selected disabled></option>
                            @foreach ($procenteTVA as $procentTVA)
                                <option value="{{ $procentTVA->id }}">{{ $procentTVA->nume }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 mb-2">
                        <label for="metoda_de_plata_id" class="mb-0 ps-3">Metoda de plată</label>
                        <select
                            :name="'clienti[' + index + '][pivot][metoda_de_plata_id]'"
                            v-model="clientiAtasatiLaComanda[index].pivot.metoda_de_plata_id"
                            class="form-select bg-white rounded-3 {{ $errors->has('metoda_de_plata_id') ? 'is-invalid' : '' }}"
                        >
                            <option value="" selected disabled></option>
                            @foreach ($metodeDePlata as $metodaDePlata)
                                <option value="{{ $metodaDePlata->id }}">{{ $metodaDePlata->nume }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-3 mb-3">
                        <label for="termen_plata_id" class="mb-0 ps-3">Termen de plată</label>
                        <select
                            :name="'clienti[' + index + '][pivot][termen_plata_id]'"
                            v-model="clientiAtasatiLaComanda[index].pivot.termen_plata_id"
                            class="form-select bg-white rounded-3 {{ $errors->has('termen_plata_id') ? 'is-invalid' : '' }}"
                        >
                            <option value="" selected disabled></option>
                            @foreach ($termeneDePlata as $termenDePlata)
                                <option value="{{ $termenDePlata->id }}">{{ $termenDePlata->nume }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-2 mb-2">
                        <label for="zile_scadente" class="mb-0 ps-0 small">Zile scadente</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('zile_scadente') ? 'is-invalid' : '' }}"
                            :name="'clienti[' + index + '][pivot][zile_scadente]'"
                            v-model="clientiAtasatiLaComanda[index].pivot.zile_scadente">
                    </div>
                    <div class="col-lg-2 mb-2">
                        <label for="tarif_pe_km" class="mb-0 ps-3">Tarif Pe Km</label>
                        <select
                            :name="'clienti[' + index + '][pivot][tarif_pe_km]'"
                            v-model="clientiAtasatiLaComanda[index].pivot.tarif_pe_km"
                            class="form-select bg-white rounded-3 {{ $errors->has('tarif_pe_km') ? 'is-invalid' : '' }}"
                        >
                            <option value="" selected disabled></option>
                            <option value="1">DA</option>
                            <option value="0">NU</option>
                        </select>
                    </div>
                    <div class="col-lg-2 mb-2 d-flex justify-content-end align-items-end">
                        <button type="btn" title="Șterge descărcarea" class="btn btn-danger px-1 mb-2" @click="this.clientiAtasatiLaComanda.splice(index, 1);">
                            <span class="badge bg-danger p-0 mb-0">Șterge clientul</span>
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div v-if="(clientiAtasatiLaComanda && clientiAtasatiLaComanda.length > 0)" class="col-lg-12">
                        <small class="ps-3">* Puteți căuta clienți în baza de date introducănd minim 3 caractere la numele clientului</small>
                    </div>
                    <div v-if="alertaCampuriNecompletate" class="col-lg-12 mb-2">
                        <div class="text-center">
                            <span class="px-1 bg-danger text-white rounded" v-html="alertaCampuriNecompletate"></span>
                            <br>
                        </div>
                    </div>
                    <div class="col-lg-12 mb-0">
                        <div class="row d-flex justify-content-between">
                            <div class="col-lg-5 mb-0 text-center">
                                &nbsp;
                            </div>
                            <div class="col-lg-2 mb-4 d-flex justify-content-center align-items-end">
                                <button type="button" class="btn btn-success text-white" @click="adaugaClientGol()">Adaugă client</button>
                            </div>
                            <div class="col-lg-5 d-flex justify-content-end">
                                <div class="d-flex px-2 rounded-3" style="border:2px solid rgb(15, 97, 52);">
                                    <div class="text-start me-2">
                                        <br>
                                        <button type="button" class="btn btn-primary w-100 text-white rounded-3"
                                            v-on:click="
                                                let hasMissingValues = false;
                                                let alertaMessage = '';
                                                let totalValoareContractInitiala = 0;

                                                clientiAtasatiLaComanda.forEach((client, index) => {
                                                    if (!client.pivot.valoare_contract_initiala) {
                                                        hasMissingValues = true;
                                                        alertaMessage += `Completează mai întâi câmpul pentru client ${index + 1}: Valoare contract inițială!<br>`;
                                                    } else {
                                                        totalValoareContractInitiala += Number(client.pivot.valoare_contract_initiala);
                                                    }
                                                });

                                                if (hasMissingValues) {
                                                    alertaCampuriNecompletate = alertaMessage.trim();
                                                } else {
                                                    alertaCampuriNecompletate = '';

                                                    // Calculate the general clientValoareContract
                                                    // clientValoareContract = (totalValoareContractInitiala - transportatorPretFerry).toFixed(2); - commented 14.02.2025
                                                    clientValoareContract = totalValoareContractInitiala.toFixed(2);
                                                }
                                            ">Calculează</button>
                                    </div>
                                    <div class="text-start me-2">
                                        <label for="client_valoare_contract" class="mb-0 ps-0 small">Valoare contract finală<span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            class="form-control bg-white rounded-3 {{ $errors->has('client_valoare_contract') ? 'is-invalid' : '' }}"
                                            name="client_valoare_contract"
                                            placeholder=""
                                            v-model="clientValoareContract">
                                        <small for="client_valoare_contract" class="mb-0 ps-3">*Punct(.) pentru zecimale</small>
                                    </div>
                                    <div class="text-start d-flex align-items-center">
                                        {{-- <label for="client_moneda_id" class="mb-0 ps-0">Moneda<span class="text-danger">*</span></label>
                                        <select
                                            type="hidden"
                                            name="client_moneda_id"
                                            class="form-select bg-white rounded-3 {{ $errors->has('client_moneda_id') ? 'is-invalid' : '' }}"
                                        >
                                            <option value="" selected disabled></option>
                                            @foreach ($monede as $moneda)
                                                <option value="{{ $moneda->id }}" {{ $moneda->id === intval(old('client_moneda_id', $comanda->client_moneda_id ?? '')) ? 'selected' : null }}>{{ $moneda->nume }}</option>
                                            @endforeach
                                        </select> --}}
                                        <input
                                            type="hidden"
                                            name="client_moneda_id"
                                            value="{{ old('client_moneda_id', $comanda->client_moneda_id ?? '') }}"
                                        >
                                        <span class="text-danger">* ATENȚIE EURO!</span>
                                    </div>
                                </div>
                            </div>
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
                <label for="camion_id" class="mb-0 ps-3">Camion<span class="text-danger">*</span></label>
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
                                camionPretKmGoi = camion.pret_km_goi;
                                camionPretKmPlini = camion.pret_km_plini;

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
                    <div class="col-lg-4 mb-2" style="position:relative;"
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
                                    {{-- v-on:focus="getLocuriOperareIncarcari(index, $event.target.value, 'nume');"
                                    v-on:keyup="getLocuriOperareIncarcari(index, $event.target.value, 'nume');" --}}
                                    v-on:focus="getLocuriOperareIncarcari(index);"
                                    v-on:keyup="getLocuriOperareIncarcari(index);"
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
                                        incarcari[index].adresa = locOperare.adresa;
                                        incarcari[index].oras = locOperare.oras;
                                        {{-- incarcari[index].tara.id = locOperare.tara.id; --}}
                                        incarcari[index].tara.nume = locOperare.tara.nume;

                                        locuriOperareIncarcari = ''
                                    ">
                                        @{{ locOperare.nume }}
                                </button>
                            </div>
                        </div>
                        {{-- <small v-if="!incarcari[index].nume || (incarcari[index].nume.length < 3)" class="ps-3">* Introduceți minim 3 caractere</small> --}}
                    </div>
                    <div class="col-lg-4 mb-2">
                        <label for="adresa" class="mb-0 ps-3">Adresa</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('adresa') ? 'is-invalid' : '' }}" readonly
                            :name="'incarcari[' + index + '][adresa]'"
                            v-model="incarcari[index].adresa">
                    </div>
                    <div class="col-lg-2 mb-2">
                        <label for="oras" class="mb-0 ps-3">Oraș</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('oras') ? 'is-invalid' : '' }}"
                            :name="'incarcari[' + index + '][oras]'"
                            v-model="incarcari[index].oras"
                            {{-- v-on:focus="getLocuriOperareIncarcari(index, $event.target.value, 'oras');"
                            v-on:keyup="getLocuriOperareIncarcari(index, $event.target.value, 'oras');" --}}
                            v-on:focus="getLocuriOperareIncarcari(index);"
                            v-on:keyup="getLocuriOperareIncarcari(index);"
                            placeholder=""
                            autocomplete="off"
                            aria-describedby="">
                        {{-- <small v-if="!incarcari[index].nume || (incarcari[index].nume.length < 3)" class="ps-2">* Introduceți > 2 caractere</small> --}}
                    </div>
                    <div class="col-lg-2 mb-2">
                        <label for="tara" class="mb-0 ps-3">Țara</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('tara') ? 'is-invalid' : '' }}" readonly
                            :name="'incarcari[' + index + '][tara][nume]'"
                            v-model="incarcari[index].tara.nume"
                            >
                    </div>
                    <div class="col-lg-12">
                        <div class="row">
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
                            <div class="col-lg-2 mb-2">
                                <label for="data_ora" class="mb-0 ps-3">Durata<span class="text-danger">*</span></label>
                                <vue-datepicker-next
                                    :data-veche='incarcari[index].pivot.durata'
                                    :nume-camp-db="'incarcari[' + index + '][pivot][durata]'"
                                    tip="time"
                                    :minute-step="5"
                                    value-type="HH:mm"
                                    format="HH:mm"
                                    :latime="{ width: '80px' }"
                                ></vue-datepicker-next>
                            </div>
                            <div class="col-lg-3 mb-2">
                                <label for="observatii" class="form-label mb-0 ps-3">Observații</label>
                                <textarea class="form-control bg-white {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
                                    rows="2"
                                    :name="'incarcari[' + index + '][pivot][observatii]'"
                                    v-model="incarcari[index].pivot.observatii">
                                </textarea>
                            </div>
                            <div class="col-lg-3 mb-2">
                                <label for="referinta" class="form-label mb-0 ps-3">Referință</label>
                                <textarea class="form-control bg-white {{ $errors->has('referinta') ? 'is-invalid' : '' }}"
                                    rows="2"
                                    :name="'incarcari[' + index + '][pivot][referinta]'"
                                    v-model="incarcari[index].pivot.referinta">
                                </textarea>
                            </div>
                            {{-- <div class="col-lg-1 mb-3 d-flex justify-content-center align-items-end">
                            </div> --}}
                            <div class="col-lg-2 mb-3 d-flex justify-content-end align-items-end">
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
                    <div v-if="(incarcari && incarcari.length > 0)" class="col-lg-12">
                        <small class="ps-3">* Puteți căuta locurile de operare din baza de date introducănd minim 3 caractere la numele locului de încărcare, sau la oraș</small>
                    </div>
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
                    <div class="col-lg-4 mb-2" style="position:relative;"
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
                                    {{-- v-on:focus="getLocuriOperareDescarcari(index, $event.target.value, 'nume');"
                                    v-on:keyup="getLocuriOperareDescarcari(index, $event.target.value, 'nume');" --}}
                                    v-on:focus="getLocuriOperareDescarcari(index);"
                                    v-on:keyup="getLocuriOperareDescarcari(index);"
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
                        {{-- <small v-if="!descarcari[index].nume || (descarcari[index].nume.length < 3)" class="ps-3">* Introduceți minim 3 caractere</small> --}}
                    </div>
                    <div class="col-lg-4 mb-2">
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
                            class="form-control bg-white rounded-3 {{ $errors->has('oras') ? 'is-invalid' : '' }}"
                            :name="'descarcari[' + index + '][oras]'"
                            v-model="descarcari[index].oras"
                            {{-- v-on:focus="getLocuriOperareDescarcari(index, $event.target.value, 'oras');"
                            v-on:keyup="getLocuriOperareDescarcari(index, $event.target.value, 'oras');" --}}
                            v-on:focus="getLocuriOperareDescarcari(index);"
                            v-on:keyup="getLocuriOperareDescarcari(index);"
                            placeholder=""
                            autocomplete="off"
                            aria-describedby="">
                        {{-- <small v-if="!descarcari[index].nume || (descarcari[index].nume.length < 3)" class="ps-2">* Introduceți > 2 caractere</small> --}}
                    </div>
                    <div class="col-lg-2 mb-2">
                        <label for="tara" class="mb-0 ps-3">Țara</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('tara') ? 'is-invalid' : '' }}" readonly
                            :name="'descarcari[' + index + '][tara][nume]'"
                            v-model="descarcari[index].tara.nume">
                    </div>
                    <div class="col-lg-12">
                        <div class="row">
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
                            <div class="col-lg-2 mb-2">
                                <label for="durata" class="mb-0 ps-3">Durata<span class="text-danger">*</span></label>
                                <vue-datepicker-next
                                    :data-veche='descarcari[index].pivot.durata'
                                    :nume-camp-db="'descarcari[' + index + '][pivot][durata]'"
                                    tip="time"
                                    :minute-step="5"
                                    value-type="HH:mm"
                                    format="HH:mm"
                                    :latime="{ width: '80px' }"
                                ></vue-datepicker-next>
                            </div>
                            <div class="col-lg-3 mb-2">
                                <label for="observatii" class="form-label mb-0 ps-3">Observații</label>
                                <textarea class="form-control bg-white {{ $errors->has('observatii') ? 'is-invalid' : '' }}"
                                    rows="2"
                                    :name="'descarcari[' + index + '][pivot][observatii]'"
                                    v-model="descarcari[index].pivot.observatii">
                                </textarea>
                            </div>
                            <div class="col-lg-3 mb-2">
                                <label for="referinta" class="form-label mb-0 ps-3">Referință</label>
                                <textarea class="form-control bg-white {{ $errors->has('referinta') ? 'is-invalid' : '' }}"
                                    rows="2"
                                    :name="'descarcari[' + index + '][pivot][referinta]'"
                                    v-model="descarcari[index].pivot.referinta">
                                </textarea>
                            </div>
                            <div class="col-lg-2 mb-3 d-flex justify-content-end align-items-end">
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
                    <div v-if="(descarcari && descarcari.length > 0)" class="col-lg-12">
                        <small class="ps-3">* Puteți căuta locurile de operare din baza de date introducănd minim 3 caractere la numele locului de încărcare, sau la oraș</small>
                    </div>
                    <div class="col-lg-12 d-flex justify-content-center py-1">
                        <button type="button" class="btn btn-success text-white" @click="adaugaDescarcareGoala()">Adaugă descărcare</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row px-2 pt-4 pb-1 mb-0" style="background-color:lightyellow; border-left:6px solid; border-color:goldenrod">
            <div class="col-lg-6 mb-4">
                <label for="observatii_interne" class="form-label mb-0 ps-3">Observații interne</label>
                <textarea class="form-control bg-white {{ $errors->has('observatii_interne') ? 'is-invalid' : '' }}"
                    name="observatii_interne" rows="3">{{ old('observatii_interne', $comanda->observatii_interne) }}</textarea>
            </div>
            <div class="col-lg-6 mb-4">
                <label for="observatii_externe" class="form-label mb-0 ps-3">Observații externe</label>
                <textarea class="form-control bg-white {{ $errors->has('observatii_externe') ? 'is-invalid' : '' }}"
                    name="observatii_externe" rows="3">{{ old('observatii_externe', $comanda->observatii_externe) }}</textarea>
            </div>
        </div>
        <div class="row px-2 py-2 mb-4" style="background-color:#ddffff; border-left:6px solid; border-color:#2196F3; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-6 mb-4">
                <label for="debit_note" class="form-label mb-0 ps-3">Debit note</label>
                <textarea class="form-control bg-white {{ $errors->has('debit_note') ? 'is-invalid' : '' }}"
                    name="debit_note" rows="3">{{ old('debit_note', $comanda->debit_note) }}</textarea>
            </div>
        </div>
        <div class="row py-4">
            <div class="col-lg-12 mb-0 d-flex justify-content-center">
                <button type="submit" ref="submit" class="btn btn-lg btn-primary text-white me-3 rounded-3">{{ $buttonText }}</button>
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('ComandaReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>


</div>

