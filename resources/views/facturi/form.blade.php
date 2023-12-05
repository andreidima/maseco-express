@csrf

@php
    use \Carbon\Carbon;
    // dd($factura->chitante->first);
@endphp

<script type="application/javascript">
    firmeClienti = {!! json_encode($firmeClienti ?? "") !!}

    client_id = {!! json_encode(old('client_id', ($factura->client_id ?? ""))) !!}
    client_nume = {!! json_encode(old('client_nume', ($factura->client_nume ?? ""))) !!}
    client_cif = {!! json_encode(old('client_cif', ($factura->client_cif ?? ""))) !!}
    client_adresa = {!! json_encode(old('client_adresa', ($factura->client_adresa ?? ""))) !!}
    client_tara_id = {!! json_encode(old('client_tara_id', ($factura->client_tara_id ?? ""))) !!}
    client_telefon = {!! json_encode(old('client_telefon', ($factura->client_telefon ?? ""))) !!}
    client_email = {!! json_encode(old('client_email', ($factura->client_email ?? ""))) !!}

    procenteTva = {!! json_encode($procenteTva ?? "") !!}

    comandaId = {!! json_encode(old('comandaId', "")) !!}
    produse = {!! json_encode(old('produse', ($factura->produse ?? []))) !!}
    valoare_contract = {!! json_encode(old('valoare_contract', ($factura->valoare_contract ?? ""))) !!}
    moneda_id = {!! json_encode(old('moneda_id', ($factura->moneda_id ?? ""))) !!}
    procent_tva_id = {!! json_encode(old('procent_tva_id', ($factura->procent_tva_id ?? ""))) !!}
    zile_scadente = {!! json_encode(old('zile_scadente', ($factura->zile_scadente ?? ""))) !!}

    chitanta_suma_incasata = {!! json_encode(old('chitanta_suma_incasata', ($factura->chitante->first()->suma ?? ""))) !!}

    dateFacturiIntocmitDeVechi = {!! json_encode($dateFacturiIntocmitDeVechi ?? "") !!}
    dateFacturiDelegatVechi = {!! json_encode($dateFacturiDelegatVechi ?? "") !!}
    dateFacturiMentiuniVechi = {!! json_encode($dateFacturiMentiuniVechi ?? "") !!}
    intocmit_de = {!! json_encode(old('intocmit_de', ($factura->intocmit_de ?? ""))) !!}
    cnp = {!! json_encode(old('cnp', ($factura->cnp ?? ""))) !!}
    delegat = {!! json_encode(old('delegat', ($factura->delegat ?? ""))) !!}
    buletin = {!! json_encode(old('buletin', ($factura->buletin ?? ""))) !!}
    auto = {!! json_encode(old('auto', ($factura->auto ?? ""))) !!}
    mentiuni = {!! json_encode(old('mentiuni', ($factura->mentiuni ?? ""))) !!}
</script>

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="creareFactura">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-4 rounded-3 justify-content-center"
            {{-- style="background-color:lightyellow; border-left:6px solid; border-color:goldenrod" --}}
            >
            <div class="col-lg-12 mb-0 d-flex align-items-center justify-content-center">
                <label for="comanda" class="mb-0 pe-2">Comanda: </label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3"
                    style="width:150px"
                    v-model="numarDeCautat"
                    placeholder="Nr. comanda"
                    v-on:keydown.enter.prevent=''
                    v-on:keyup.enter="axiosCautaComanda()"
                    autofocus
                    >
                <button type="button" class="btn btn-primary text-white" @click="axiosCautaComanda()">Caută</button>
            </div>
            <div v-if="afisareMesajAtentionareNegasireComanda" class="col-lg-12 mt-2 mb-0 d-flex align-items-center justify-content-center">
                <p class="mb-0 px-2 rounded-3 bg-warning">Nu a fost gasită comanda!</p>
            </div>
        </div>
        <div v-if="comandaGasita" class="row mb-4 py-0 rounded-3 justify-content-center" style="border:1px solid #e9ecef;">
        {{-- <div class="row mb-0 py-0 rounded-3 justify-content-center" style="background-color:rgb(253, 253, 185); border-left:0px solid; border-color:goldenrod"> --}}
            <div class="col-lg-12 mb-0 px-1">
                <button type="button" class="py-0 px-1 btn btn-sm btn-primary"
                    {{-- style="background-color:goldenrod;"  --}}
                    @click="preiaDateFacturare()">Preia datele de facturare</button>
                Moneda: @{{ comandaGasita.client_moneda ? comandaGasita.client_moneda.nume : '' }} / Zile scadente: @{{ comandaGasita.client_zile_scadente }} / Procent TVA: @{{ comandaGasita.client_procent_tva ? comandaGasita.client_procent_tva.nume : '' }}
            </div>
        {{-- </div> --}}
        {{-- <div class="row mb-0 py-0 rounded-3 d-flex justify-content-center" style="background-color:#ddffff; border-left:0px solid; border-color:#2196F3; border-radius: 0px 0px 0px 0px"> --}}
            <div class="col-lg-12 mb-0 px-1">
                <button type="button" class="py-0 px-1 btn btn-sm btn-primary"
                    {{-- style="background-color:#2196F3;"  --}}
                    @click="preiaDateClient()">Preia datele clientului</button>
                @{{ firmaClient.nume }} / CIF: @{{ firmaClient.cif  }} / Țara @{{ firmaClient.tara ? firmaClient.tara.nume : '' }} / Adresa @{{ firmaClient.cif }} / Telefon @{{ firmaClient.telefon }} / Email @{{ firmaClient.email }}
            </div>
        {{-- </div> --}}
        {{-- <div class="row mb-0 py-0 rounded-3 d-flex justify-content-center" style="background-color:#B8FFB8; border-left:0px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px"> --}}
            <div class="col-lg-12 mb-0 px-1 align-items-center">
                <button type="button" class="py-0 px-1 btn btn-sm btn-primary"
                    {{-- style="background-color:mediumseagreen;"  --}}
                    @click="preiaDateProdus()">Preia datele produsului</button>
                @{{ produsGasitDenumire }} / Preț: @{{ comandaGasita.client_valoare_contract }} / TVA: @{{ comandaGasita.client_procent_tva ? comandaGasita.client_procent_tva.nume : '' }}
            </div>
        </div>
        {{-- <div class="row mb-0 py-4 rounded-3 justify-content-center" style="background-color:rgb(253, 253, 185); border-left:6px solid; border-color:goldenrod"> --}}
        <div class="row mb-4 py-0 rounded-3 justify-content-center" style="border:1px solid #e9ecef; border-left:0.25rem #ec8575 solid">
            @if (str_contains(url()->current(), '/adauga'))
                <div class="col-lg-2 mb-1">
                    <label for="seria" class="mb-0 ps-3">Seria facturii<span class="text-danger">*</span></label>
                    <select name="seria"
                        class="form-select bg-white rounded-3 {{ $errors->has('seria') ? 'is-invalid' : '' }}"
                        {{ str_contains(url()->current(), '/modifica') ? 'disabled' : '' }}>
                        <option selected></option>
                        <option value="MAS" {{ (old('seria', $factura->seria) === "MAS") ? 'selected' : '' }}>MAS</option>
                        <option value="MSC" {{ (old('seria', $factura->seria) === "MSC") ? 'selected' : '' }}>MSC</option>
                        <option value="MSX" {{ (old('seria', $factura->seria) === "MSX") ? 'selected' : '' }}>MSX</option>
                    </select>
                </div>
            @elseif (str_contains(url()->current(), '/modifica'))
                <div class="col-lg-2 mb-1">
                    <label for="numar" class="mb-0 ps-3">Serie și număr</label>
                    <input
                        type="text"
                        class="form-control bg-white rounded-3 {{ $errors->has('numar') ? 'is-invalid' : '' }}"
                        value="{{ $factura->seria }} - {{ $factura->numar }}"
                        disabled>
                </div>
            @endif
            <div class="col-lg-2 mb-0 text-center">
                <label for="data" class="mb-0 ps-0">Data facturii<span class="text-danger">*</span></label>
                <vue-datepicker-next
                    data-veche="{{ old('data', $factura->data) }}"
                    nume-camp-db="data"
                    tip="date"
                    value-type="YYYY-MM-DD"
                    format="DD.MM.YYYY"
                    :latime="{ width: '125px' }"
                ></vue-datepicker-next>
            </div>
            <div class="col-lg-2 mb-1">
                <label for="moneda_id" class="mb-0 ps-3">Monedă<span class="text-danger">*</span></label>
                <select name="moneda_id"
                    v-model="moneda_id"
                class="form-select bg-white rounded-3 {{ $errors->has('moneda_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($monede as $moneda)
                        <option value="{{ $moneda->id }}">{{ $moneda->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-1">
                <label for="procent_tva_id" class="mb-0 ps-3">Procent TVA<span class="text-danger">*</span></label>
                <select name="procent_tva_id"
                    v-model="procent_tva_id"
                class="form-select bg-white rounded-3 {{ $errors->has('procent_tva_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($procenteTva as $procent_tva)
                        <option value="{{ $procent_tva->id }}">{{ $procent_tva->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-1">
                <label for="zile_scadente" class="mb-0 ps-3">Zile scadente</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('zile_scadente') ? 'is-invalid' : '' }}"
                    name="zile_scadente"
                    v-model="zile_scadente">
            </div>
            <div class="col-lg-2 mb-1">
                <label for="alerte_scadenta" class="mb-0 ps-3">
                    Alerte scadență
                    <i class="fa-solid fa-circle-info text-primary" title="Se poate seta cu câte zile înainte de scadență să se trimită mementouri. Se pot introduce mai multe mementouri, cu virgulă între ele (Ex: 1,3,7)"
                    v-on:click="showInfoAlerteScadenta = !showInfoAlerteScadenta"></i>
                </label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('alerte_scadenta') ? 'is-invalid' : '' }}"
                    name="alerte_scadenta"
                    placeholder="Ex: 1,3,7"
                    value="{{ old('alerte_scadenta', $factura->alerte_scadenta) }}">
            </div>
            <div v-if="showInfoAlerteScadenta" class="col-lg-12 mb-2 d-flex justify-content-center">
                <div class="px-2 rounded-3 d-block bg-info text-center text-white">
                    <b>Alerte scadență</b>
                    <br>
                    Se poate seta cu câte zile înainte de scadență să se trimită mementouri.
                    <br>
                    Se pot introduce mai multe mementouri, cu virgulă între ele (Ex: 1,3,7)
                </div>
            </div>
        </div>
        {{-- <div class="row mb-0 py-4 rounded-3 d-flex justify-content-center" style="background-color:#ddffff; border-left:6px solid; border-color:#2196F3; border-radius: 0px 0px 0px 0px"> --}}
        <div class="row mb-4 py-0 rounded-3 justify-content-center" style="border:1px solid #e9ecef; border-left:0.25rem #6a6ba0 solid">
            <div class="col-lg-6">
                <div class="row">
                    <div class="col-lg-12" style="position:relative;" v-click-out="() => firmeClientiListaAutocomplete = ''">
                        <label for="client_nume" class="mb-0 ps-3">Client<span class="text-danger">*</span></label>
                        <div class="input-group d-flex">
                            <div class="input-group-prepend d-flex align-items-center h-100">
                                <div v-if="!client_id" class="input-group-text py-1 px-2" id="client_nume">?</div>
                                <div v-if="client_id" class="input-group-text p-2 bg-success text-white" id="client_nume"><i class="fa-solid fa-check" style="height:100%"></i></div>
                            </div>
                            <input
                                type="hidden"
                                name="client_id"
                                v-model="client_id">
                            <input
                                type="text"
                                v-model="client_nume"
                                v-on:focus="autocompleteFirmeClienti();"
                                v-on:keyup="autocompleteFirmeClienti(); this.client_id = '';"
                                class="form-control bg-white rounded-3 {{ $errors->has('client_nume') ? 'is-invalid' : '' }}"
                                name="client_nume"
                                placeholder="Client"
                                autocomplete="off"
                                aria-describedby="client_nume"
                                required>
                            <div class="input-group-prepend d-flex align-items-center">
                                <div v-if="client_id" class="input-group-text p-2 text-danger" id="client_nume" v-on:click="client_id = null; client_nume = ''"><i class="fa-solid fa-xmark"></i></div>
                            </div>
                        </div>
                        <div v-cloak v-if="firmeClientiListaAutocomplete && firmeClientiListaAutocomplete.length" class="panel-footer" style="width:100%; position:absolute; z-index: 1000;">
                            <div class="list-group" style="max-height: 130px; overflow:auto;">
                                <button type="button" class="list-group-item list-group-item list-group-item-action py-0"
                                    v-for="firma in firmeClientiListaAutocomplete"
                                    v-on:click="axiosCautaClient(firma.id);">
                                        @{{ firma.nume }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 mb-1">
                <label for="client_cif" class="mb-0 ps-3">CIF</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('client_cif') ? 'is-invalid' : '' }}"
                    name="client_cif"
                    v-model="client_cif">
            </div>
            <div class="col-lg-3 mb-1">
                <label for="client_tara_id" class="mb-0 ps-3">Țara</label>
                <select name="client_tara_id"
                    v-model="client_tara_id"
                class="form-select bg-white rounded-3 {{ $errors->has('client_tara_id') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($tari as $tara)
                        <option value="{{ $tara->id }}">{{ $tara->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-6 mb-1">
                <label for="client_adresa" class="mb-0 ps-3">Adresa</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('client_adresa') ? 'is-invalid' : '' }}"
                    name="client_adresa"
                    v-model="client_adresa">
            </div>
            <div class="col-lg-3 mb-1">
                <label for="client_telefon" class="mb-0 ps-3">Telefon</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('client_telefon') ? 'is-invalid' : '' }}"
                    name="client_telefon"
                    v-model="client_telefon">
            </div>
            <div class="col-lg-3 mb-1">
                <label for="client_email" class="mb-0 ps-3">Email</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('client_email') ? 'is-invalid' : '' }}"
                    name="client_email"
                    v-model="client_email">
            </div>
        </div>
        {{-- <div class="row mb-0 py-4 rounded-3 d-flex justify-content-center" style="background-color:#B8FFB8; border-left:0px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px"> --}}
        <div class="row mb-4 py-0 rounded-3 justify-content-center" style="border:1px solid #e9ecef; border-left:0.25rem #ec8575 solid">
            <div class="col-lg-5 mb-1">
                <label for="produsDenumire" class="mb-0 ps-3">Denumire produs</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('produsDenumire') ? 'is-invalid' : '' }}"
                    name="produsDenumire"
                    v-model="produsDenumire">
            </div>
            <div class="col-lg-1 mb-1">
                <label for="produsUm" class="mb-0 ps-3">U.M.</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('produsUm') ? 'is-invalid' : '' }}"
                    name="produsUm"
                    v-model="produsUm">
            </div>
            <div class="col-lg-1 mb-1">
                <label for="produsCantitate" class="mb-0 ps-3">Cant.</label>
                <input
                    type="number"
                    class="form-control bg-white rounded-3 {{ $errors->has('produsCantitate') ? 'is-invalid' : '' }}"
                    name="produsCantitate"
                    v-model="produsCantitate">
            </div>
            <div class="col-lg-1 mb-1">
                <label for="produsPret" class="mb-0 ps-3">Preț</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('produsPret') ? 'is-invalid' : '' }}"
                    name="produsPret"
                    v-model="produsPret">
            </div>
            <div class="col-lg-1 mb-1">
                <label for="produsProcentTvaId" class="mb-0 ps-3">TVA</label>
                <select v-model="produsProcentTvaId"
                class="form-select bg-white rounded-3 {{ $errors->has('produsProcentTvaId') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($procenteTva as $procent_tva)
                        <option value="{{ $procent_tva->id }}">{{ $procent_tva->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-1 text-center">
                <label for="produsPretulIncludeTva" class="mb-0 ps-3"><small>Prețul include TVA?</small></label>
                <div class="d-flex py-1 justify-content-center">
                    <div class="form-check me-3">
                        <input class="form-check-input" type="radio" value=0 id="produsPretulIncludeTva_nu" v-model="produsPretulIncludeTva">
                        <label class="form-check-label" for="produsPretulIncludeTva_nu">Nu</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" value=1 id="produsPretulIncludeTva_da" v-model="produsPretulIncludeTva">
                        <label class="form-check-label" for="produsPretulIncludeTva_da">Da</label>
                    </div>
                </div>
            </div>
            <div class="col-lg-1 mb-1 d-flex align-items-center justify-content-end">
                <button type="button" class="btn btn-sm btn-primary text-white" @click="adaugaProdusLaFactura()">Adaugă</button>
            </div>

            <div v-if="showDateProdusIncomplete" class="col-lg-12 text-center text-danger">
                @{{ showDateProdusIncomplete }}
            </div>

            <div class="col-lg-12 table-responsive">
                <table class="mb-0 table table-sm table-striped table-hover">
                    {{-- <thead class="text-white" style="background-color:rgb(0, 0, 0);">
                        <tr class="p-0" style="">
                            <th class="py-0 px-1 ">#</th>
                            <th class="py-0 px-1 text-center">Denumire produs / serviciu</th>
                            <th class="py-0 px-1 text-center">U.M.</th>
                            <th class="py-0 px-1 text-center">Cant.</th>
                            <th class="py-0 px-1 text-center">Preț (fără TVA)</th>
                            <th class="text-center">Procent <br />TVA</th>
                            <th class="text-center">Prețul <br />include TVA?</th>
                            <th class="py-0 px-1 text-center">Valoare</th>
                            <th class="py-0 px-1 text-center">TVA</th>
                        </tr>
                    </thead>
                    <tbody style="vertical-align:middle">
                        <tr v-for="(produs, index) in produse" :key="produs">
                            <td>
                                <input
                                    type="hidden"
                                    :name="'produse[' + index + '][comanda_id]'"
                                    v-model="produse[index].comanda_id">

                                @{{ index+1 }}
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('denumire') ? 'is-invalid' : '' }}"
                                    :name="'produse[' + index + '][denumire]'"
                                    v-model="produse[index].denumire">
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('um') ? 'is-invalid' : '' }}"
                                    style="width: 50px"
                                    :name="'produse[' + index + '][um]'"
                                    v-model="produse[index].um">
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('cantitate') ? 'is-invalid' : '' }}"
                                    style="width: 50px"
                                    :name="'produse[' + index + '][cantitate]'"
                                    v-model="produse[index].cantitate">
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('pret_unitar_fara_tva') ? 'is-invalid' : '' }}"
                                    style="width: 50px"
                                    :name="'produse[' + index + '][pret_unitar_fara_tva]'"
                                    v-model="produse[index].pret_unitar_fara_tva">
                            </td>
                            <td>
                                <select v-model="produse[index].procent_tva_id"
                                class="form-select bg-white rounded-3 {{ $errors->has('procent_tva_id') ? 'is-invalid' : '' }}">
                                    <option selected></option>
                                    @foreach ($procenteTva as $procent_tva)
                                        <option value="{{ $procent_tva->id }}">{{ $procent_tva->nume }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <div class="d-flex py-1 justify-content-center">
                                    <div class="form-check me-3">
                                        <input class="form-check-input" type="radio" value="0" id="transportator_tarif_pe_km_nu" v-model="produse[index].pretul_include_tva">
                                        <label class="form-check-label" for="transportator_tarif_pe_km_nu">Nu</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" value="1" id="transportator_tarif_pe_km_da" v-model="produse[index].pretul_include_tva">
                                        <label class="form-check-label" for="transportator_tarif_pe_km_da">Da</label>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('valoare') ? 'is-invalid' : '' }}"
                                    style="width: 50px"
                                    :name="'produse[' + index + '][valoare]'"
                                    v-model="produse[index].valoare">
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('valoare_tva') ? 'is-invalid' : '' }}"
                                    style="width: 50px"
                                    :name="'produse[' + index + '][valoare_tva]'"
                                    v-model="produse[index].valoare_tva">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td class="text-end">
                                Total
                            </td>
                            <td class="text-end">
                                @{{ total_fara_tva_moneda }}
                            </td>
                            <td class="text-end">
                                @{{ total_tva_moneda }}
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4"></td>
                            <td class="text-end">
                                Total plată
                            </td>
                            <td colspan="2" class="text-end">
                                @{{ total_moneda }}
                            </td>
                        </tr>
                    </tbody> --}}
                    <thead class="text-white" style="background-color:rgb(0, 0, 0);">
                        <tr class="p-0" style="">
                            <th class="py-0 px-1 ">#</th>
                            <th class="py-0 px-1 text-start w-50">Denumire produs / serviciu</th>
                            <th class="py-0 px-1 text-center">U.M.</th>
                            <th class="py-0 px-1 text-center">Cant.</th>
                            <th class="py-0 px-1 text-end">Preț (fără TVA)</th>
                            <th class="py-0 px-1 text-end">Valoare</th>
                            <th class="py-0 px-1 text-end">TVA</th>
                            <th class="py-0 px-1 text-end"></th>
                        </tr>
                    </thead>
                    <tbody style="vertical-align:middle">
                        <tr v-for="(produs, index) in produse" :key="produs" style="vertical-align:top">
                            <td class="py-0">
                                <input type="hidden" :name="'produse[' + index + '][id]'" v-model="produse[index].id">
                                <input type="hidden" :name="'produse[' + index + '][comanda_id]'" v-model="produse[index].comanda_id">
                                <input type="hidden" :name="'produse[' + index + '][denumire]'" v-model="produse[index].denumire">
                                <input type="hidden" :name="'produse[' + index + '][um]'" v-model="produse[index].um">
                                <input type="hidden" :name="'produse[' + index + '][cantitate]'" v-model="produse[index].cantitate">
                                <input type="hidden" :name="'produse[' + index + '][pret_unitar_fara_tva]'" v-model="produse[index].pret_unitar_fara_tva">
                                <input type="hidden" :name="'produse[' + index + '][valoare]'" v-model="produse[index].valoare">
                                <input type="hidden" :name="'produse[' + index + '][valoare_tva]'" v-model="produse[index].valoare_tva">

                                @{{ index+1 }}
                            </td>
                            <td class="py-0"> @{{ produs.denumire }} </td>
                            <td class="py-0 text-center"> @{{ produs.um }} </td>
                            <td class="py-0 text-center"> @{{ produs.cantitate }} </td>
                            <td class="py-0 px-1 text-end"> @{{ produs.pret_unitar_fara_tva }} </td>
                            <td class="py-0 px-1 text-end"> @{{ produs.valoare }} </td>
                            <td class="py-0 px-1 text-end"> @{{ produs.valoare_tva }} </td>
                            <td class="py-0 px-1 text-end">
                                {{-- <button
                                    type="button"
                                    class="py-0 px-1 btn btn-sm btn-danger" --}}
                                    {{-- style="background-color:mediumseagreen;"  --}}
                                    {{-- @click="stergeProdusulDinFactura()"> --}}
                                    <i class="fa-solid fa-xmark text-danger me-1" title="Șterge produsul din factură"
                                        @click="produse.splice(index, 1);">
                                    </i>
                                {{-- </button> --}}

                            </td>
                        </tr>
                        <tr>
                            <td>
                                <input type="hidden" name="total_fara_tva_moneda" v-model="total_fara_tva_moneda">
                                <input type="hidden" name="total_tva_moneda" v-model="total_tva_moneda">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-lg-12">
                <br><br>
            </div>
            <div class="col-lg-12">
                <div class="row flex-row-reverse">
                    <div class="col-lg-4">
                        <div v-if="produse && produse.length">
                            <div class="d-flex justify-content-between">
                                <p class="mb-0">Total fără TVA</p>
                                <p class="mb-0">@{{ total_fara_tva_moneda }}</p>
                            </div>
                            <div class="d-flex justify-content-between">
                                <p class="mb-0">Total TVA</p>
                                <p class="mb-0">@{{ total_tva_moneda }}</p>
                            </div>
                            <div>
                                <hr class="mx-0 my-2">
                            </div>
                            <div class="d-flex justify-content-between">
                                <p class="mb-0"><b>TOTAL</b></p>
                                <p class="mb-0"><b>@{{ total_fara_tva_moneda + total_tva_moneda }}</b></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-2">
                    </div>
                    <div class="col-lg-6">
                        <div class="row">
                            <div class="col-lg-6">
                                <label for="chitanta_suma_incasata" class="mb-0 ps-3">Suma încasată acum</label>
                                <input
                                    type="text"
                                    class="form-control bg-white rounded-3 {{ $errors->has('chitanta_suma_incasata') ? 'is-invalid' : '' }}"
                                    name="chitanta_suma_incasata"
                                    v-model="chitanta_suma_incasata">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4 py-0 rounded-3 justify-content-center" style="border:1px solid #e9ecef; border-left:0.25rem #6a6ba0 solid">
            <div class="col-lg-4">
                <div class="row">
                    <div class="col-lg-12" style="position:relative;" v-click-out="() => dateFacturiIntocmitDeVechiListaAutocomplete = ''">
                        <label for="intocmit_de" class="mb-0 ps-3">Întocmit de<span class="text-danger">*</span></label>
                        <div class="input-group d-flex">
                            <input
                                type="text"
                                v-model="intocmit_de"
                                v-on:focus="autocompleteDateFacturiIntocmitDeVechi();"
                                v-on:keyup="autocompleteDateFacturiIntocmitDeVechi();"
                                class="form-control bg-white rounded-3 {{ $errors->has('intocmit_de') ? 'is-invalid' : '' }}"
                                name="intocmit_de"
                                autocomplete="off"
                                aria-describedby="intocmit_de"
                                required>
                        </div>
                        <div v-cloak v-if="dateFacturiIntocmitDeVechiListaAutocomplete && dateFacturiIntocmitDeVechiListaAutocomplete.length" class="panel-footer" style="width:100%; position:absolute; z-index: 1000;">
                            <div class="list-group" style="max-height: 130px; overflow:auto;">
                                <button type="button" class="list-group-item list-group-item list-group-item-action py-0"
                                    v-for="dateFactura in dateFacturiIntocmitDeVechiListaAutocomplete"
                                    v-on:click="intocmit_de = dateFactura.intocmit_de; cnp = dateFactura.cnp; dateFacturiIntocmitDeVechiListaAutocomplete = ''">
                                        @{{ dateFactura.intocmit_de }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <label for="cnp" class="mb-0 ps-3">CNP</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('cnp') ? 'is-invalid' : '' }}"
                            name="cnp"
                            v-model="cnp">
                    </div>
                    <div class="col-lg-12">
                        <label for="aviz_insotire" class="mb-0 ps-3">Aviz însoțire</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('aviz_insotire') ? 'is-invalid' : '' }}"
                            name="aviz_insotire"
                            value="{{ old('aviz_insotire', $factura->aviz_insotire) }}">
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="row">
                    <div class="col-lg-12" style="position:relative;" v-click-out="() => dateFacturiDelegatVechiListaAutocomplete = ''">
                        <label for="delegat" class="mb-0 ps-3">Delegat</label>
                        <div class="input-group d-flex">
                            <input
                                type="text"
                                v-model="delegat"
                                v-on:focus="autocompleteDateFacturiDelegatVechi();"
                                v-on:keyup="autocompleteDateFacturiDelegatVechi();"
                                class="form-control bg-white rounded-3 {{ $errors->has('delegat') ? 'is-invalid' : '' }}"
                                name="delegat"
                                autocomplete="off"
                                aria-describedby="delegat"
                                required>
                        </div>
                        <div v-cloak v-if="dateFacturiDelegatVechiListaAutocomplete && dateFacturiDelegatVechiListaAutocomplete.length" class="panel-footer" style="width:100%; position:absolute; z-index: 1000;">
                            <div class="list-group" style="max-height: 130px; overflow:auto;">
                                <button type="button" class="list-group-item list-group-item list-group-item-action py-0"
                                    v-for="dateFactura in dateFacturiDelegatVechiListaAutocomplete"
                                    v-on:click="delegat = dateFactura.delegat; buletin = dateFactura.buletin; dateFacturiDelegatVechiListaAutocomplete = ''">
                                        @{{ dateFactura.delegat }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <label for="buletin" class="mb-0 ps-3">Buletin</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('buletin') ? 'is-invalid' : '' }}"
                            name="buletin"
                            v-model="buletin">
                    </div>
                    <div class="col-lg-12">
                        <label for="auto" class="mb-0 ps-3">Auto</label>
                        <input
                            type="text"
                            class="form-control bg-white rounded-3 {{ $errors->has('auto') ? 'is-invalid' : '' }}"
                            name="auto"
                            value="{{ old('auto', $factura->auto) }}">
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="row">
                    <div class="col-lg-12" style="position:relative;" v-click-out="() => dateFacturiMentiuniVechiListaAutocomplete = ''">
                        <label for="mentiuni" class="mb-0 ps-3">Mențiuni</label>
                        <div class="input-group d-flex">
                            <textarea
                                v-model="mentiuni"
                                v-on:focus="autocompleteDateFacturiMentiuniVechi();"
                                v-on:keyup="autocompleteDateFacturiMentiuniVechi();"
                                class="form-control bg-white rounded-3 {{ $errors->has('mentiuni') ? 'is-invalid' : '' }}"
                                name="mentiuni"
                                autocomplete="off"
                                aria-describedby="mentiuni"
                                rows="3"
                                required>
                                </textarea>
                        </div>
                        <div v-cloak v-if="dateFacturiMentiuniVechiListaAutocomplete && dateFacturiMentiuniVechiListaAutocomplete.length" class="panel-footer" style="width:100%; position:absolute; z-index: 1000;">
                            <div class="list-group" style="max-height: 130px; overflow:auto;">
                                <button type="button" class="list-group-item list-group-item list-group-item-action py-0"
                                    v-for="dateFactura in dateFacturiMentiuniVechiListaAutocomplete"
                                    v-on:click="mentiuni = dateFactura.mentiuni; cnp = dateFactura.cnp; dateFacturiMentiuniVechiListaAutocomplete = ''">
                                        @{{ dateFactura.mentiuni }}
                                </button>
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
                <a class="btn btn-lg btn-secondary rounded-3" href="{{ Session::get('facturaReturnUrl') }}">Renunță</a>
            </div>
        </div>
    </div>
</div>
