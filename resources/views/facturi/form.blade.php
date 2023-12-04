@csrf

@php
    use \Carbon\Carbon;
    // dd($firmeClienti);
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
    moneda = {!! json_encode(old('moneda', ($factura->moneda ?? ""))) !!}
    procent_tva_id = {!! json_encode(old('procent_tva_id', ($factura->procent_tva_id ?? ""))) !!}
    zile_scadente = {!! json_encode(old('zile_scadente', ($factura->zile_scadente ?? ""))) !!}
</script>

<div class="row mb-0 px-3 d-flex border-radius: 0px 0px 40px 40px" id="creareFactura">
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-0 rounded-3 justify-content-center"
            {{-- style="background-color:lightyellow; border-left:6px solid; border-color:goldenrod" --}}
            >
            <div class="col-lg-12 mb-3 d-flex align-items-center justify-content-center">
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
            <div v-if="afisareMesajAtentionareNegasireComanda" class="col-lg-12 mb-4 d-flex align-items-center justify-content-center">
                <p class="px-2 rounded-3 bg-warning">Nu a fost gasită comanda!</p>
            </div>
        </div>
    </div>
    <div v-if="comandaGasita" class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-0 py-0 rounded-3 justify-content-center" style="background-color:lightyellow; border-left:0px solid; border-color:goldenrod">
            <div class="col-lg-12 mb-0 px-1">
                <button type="button" class="py-0 px-1 rounded-3 btn btn-sm text-white" style="background-color:goldenrod;" @click="adaugaDateFacturareLaFactura()">Adaugă la factură</button>
                Date facturare: Moneda: @{{ comandaGasita.client_moneda ? comandaGasita.client_moneda.nume : '' }} / Zile scadente: @{{ comandaGasita.client_zile_scadente }} / Procent TVA: @{{ comandaGasita.client_procent_tva ? comandaGasita.client_procent_tva.nume : '' }}
            </div>
        </div>
        <div class="row mb-0 py-0 rounded-3 d-flex justify-content-center" style="background-color:#ddffff; border-left:0px solid; border-color:#2196F3; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-12 mb-0 px-1">
                <button type="button" class="py-0 px-1 rounded-3 btn btn-sm text-white" style="background-color:#2196F3;" @click="adaugaClientLaFactura()">Adaugă la factură</button>
                Client: <b>@{{ firmaClient.nume }}</b> / CIF: @{{ firmaClient.cif  }} / Țara @{{ firmaClient.tara ? firmaClient.tara.nume : '' }} / Adresa @{{ firmaClient.cif }} / Telefon @{{ firmaClient.telefon }} / Email @{{ firmaClient.email }}
            </div>
        </div>
        <div class="row mb-0 py-0 rounded-3 d-flex justify-content-center" style="background-color:#B8FFB8; border-left:0px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-12 mb-0 px-1 align-items-center">
                <button type="button" class="py-0 px-1 rounded-3 btn btn-sm text-white" style="background-color:mediumseagreen;" @click="adaugaProdusLaFactura()">Adaugă la factură</button>
                Produs: @{{ produsGasit }} / Preț: @{{ comandaGasita.client_valoare_contract }} / TVA: @{{ comandaGasita.client_procent_tva ? comandaGasita.client_procent_tva.nume : '' }}
            </div>
        </div>
    </div>
    <div class="col-lg-12 px-4 py-2 mb-0">
        <div class="row mb-2 rounded-3 justify-content-center" style="background-color:lightyellow; border-left:6px solid; border-color:goldenrod">
            <div class="col-lg-2 mb-4">
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
            @if (str_contains(url()->current(), '/modifica'))
                <div class="col-lg-2 mb-4">
                    <label for="numar" class="mb-0 ps-3">Număr</label>
                    <input
                        type="text"
                        class="form-control bg-white rounded-3 {{ $errors->has('numar') ? 'is-invalid' : '' }}"
                        value="{{ old('numar', $factura->numar) }}"
                        disabled>
                </div>
            @endif
            <div class="col-lg-2 mb-4 text-center">
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
            <div class="col-lg-2 mb-4">
                <label for="moneda" class="mb-0 ps-3">Monedă<span class="text-danger">*</span></label>
                <select name="moneda"
                    v-model="moneda"
                class="form-select bg-white rounded-3 {{ $errors->has('moneda') ? 'is-invalid' : '' }}">
                    <option selected></option>
                    @foreach ($monede as $moneda)
                        <option value="{{ $moneda->id }}">{{ $moneda->nume }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2 mb-4">
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
            <div class="col-lg-2 mb-4">
                <label for="zile_scadente" class="mb-0 ps-3">Zile scadente</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('zile_scadente') ? 'is-invalid' : '' }}"
                    name="zile_scadente"
                    v-model="zile_scadente">
            </div>
            <div class="col-lg-2 mb-4">
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
        <div class="row mb-2 rounded-3 pt-4 d-flex justify-content-center" style="background-color:#ddffff; border-left:6px solid; border-color:#2196F3; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-6">
                <div class="row">
                    <div class="col-lg-12" style="position:relative;" v-click-out="() => firmeClientiListaAutocomplete = ''">
                        <label for="client_nume" class="mb-0 ps-3">Client<span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend d-flex align-items-center">
                                <div v-if="!client_id" class="input-group-text" id="client_nume">?</div>
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
            <div class="col-lg-3 mb-4">
                <label for="client_cif" class="mb-0 ps-3">CIF</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('client_cif') ? 'is-invalid' : '' }}"
                    name="client_cif"
                    v-model="client_cif">
            </div>
            <div class="col-lg-3 mb-4">
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
            <div class="col-lg-6 mb-4">
                <label for="client_adresa" class="mb-0 ps-3">Adresa</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('client_adresa') ? 'is-invalid' : '' }}"
                    name="client_adresa"
                    v-model="client_adresa">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="client_telefon" class="mb-0 ps-3">Telefon</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('client_telefon') ? 'is-invalid' : '' }}"
                    name="client_telefon"
                    v-model="client_telefon">
            </div>
            <div class="col-lg-3 mb-4">
                <label for="client_email" class="mb-0 ps-3">Email</label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('client_email') ? 'is-invalid' : '' }}"
                    name="client_email"
                    v-model="client_email">
            </div>
        </div>
        <div class="row mb-4 rounded-3 p-0 d-flex justify-content-center" style="background-color:#B8FFB8; border-left:0px solid; border-color:mediumseagreen; border-radius: 0px 0px 0px 0px">
            <div class="col-lg-12 p-0 table-responsive rounded">
                <table class="table table-striped table-hover rounded">
                    <thead class="text-white rounded" style="background-color:mediumseagreen;">
                        <tr class="" style="padding:2rem">
                            <th class="">#</th>
                            <th class="text-center">Denumire produs / serviciu</th>
                            <th class="text-center">U.M.</th>
                            <th class="text-center">Cant.</th>
                            <th class="text-center">Preț <br />(fără TVA)</th>
                            <th class="text-center">Procent <br />TVA</th>
                            <th class="text-center">Prețul <br />include TVA?</th>
                            <th class="text-center">Valoare</th>
                            <th class="text-center">TVA</th>
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
                                    class="form-control form-control-sm bg-white rounded-3 {{ $errors->has('denumire') ? 'is-invalid' : '' }}"
                                    :name="'produse[' + index + '][denumire]'"
                                    v-model="produse[index].denumire">
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="form-control form-control-sm bg-white rounded-3 {{ $errors->has('um') ? 'is-invalid' : '' }}"
                                    style="width: 50px"
                                    :name="'produse[' + index + '][um]'"
                                    v-model="produse[index].um">
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="form-control form-control-sm bg-white rounded-3 {{ $errors->has('cantitate') ? 'is-invalid' : '' }}"
                                    style="width: 50px"
                                    :name="'produse[' + index + '][cantitate]'"
                                    v-model="produse[index].cantitate">
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="form-control form-control-sm bg-white rounded-3 {{ $errors->has('pret_unitar_fara_tva') ? 'is-invalid' : '' }}"
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

                                    {{-- <input type="radio" id="one" value="One" v-model="picked" />
                                    <label for="one">One</label>

                                    <input type="radio" id="two" value="Two" v-model="picked" />
                                    <label for="two">Two</label> --}}
                                </div>
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="form-control form-control-sm bg-white rounded-3 {{ $errors->has('valoare') ? 'is-invalid' : '' }}"
                                    style="width: 50px"
                                    :name="'produse[' + index + '][valoare]'"
                                    v-model="produse[index].valoare">
                            </td>
                            <td>
                                <input
                                    type="text"
                                    class="form-control form-control-sm bg-white rounded-3 {{ $errors->has('valoare_tva') ? 'is-invalid' : '' }}"
                                    style="width: 50px"
                                    :name="'produse[' + index + '][valoare_tva]'"
                                    v-model="produse[index].valoare_tva">
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4" rowspan="2"></td>
                            <td>
                                Total
                            </td>
                            <td>
                                @{{ total_fara_tva_moneda }}
                            </td>
                            <td>
                                @{{ total_tva_moneda }}
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Total plata
                            </td>
                            <td>
                                @{{ total_moneda }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row mb-4 justify-content-center">
            <div class="col-lg-2 mb-4">
                <label for="intocmit_de" class="mb-0 ps-3">Întocmit de<span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control bg-white rounded-3 {{ $errors->has('intocmit_de') ? 'is-invalid' : '' }}"
                    name="intocmit_de"
                    {{-- value="{{ old('intocmit_de', auth()->user()->name) }}"> --}}
                    value="{{ old('intocmit_de', $factura->intocmit_de) }}">
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
