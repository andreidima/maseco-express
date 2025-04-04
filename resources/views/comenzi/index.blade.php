<script type="application/javascript">
    firmeTransportatori = {!! json_encode($firmeTransportatori ?? "") !!}
    firmaTransportatorIdVechi = {!! json_encode($searchTransportatorId ?? "") !!}
    firmeClienti = {!! json_encode($firmeClienti ?? "") !!}
    firmaClientIdVechi = {!! json_encode($searchClientId ?? "") !!}
    camioane = {!! json_encode($camioane ?? "") !!}
    camionIdVechi = {!! json_encode(old('camion_id', ($comanda->camion_id ?? "")) ?? "") !!}
</script>

@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-2 mb-2">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-clipboard-list me-1"></i>Comenzi
                </span>
            </div>
            <div class="col-lg-8 mb-0" id="formularComanda">
                <form class="needs-validation mb-lg-0" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    <div class="row mb-1 custom-search-form d-flex justify-content-center">
                        <div class="col-lg-4">
                            <input type="text" class="form-control rounded-3" id="searchTransportatorContract" name="searchTransportatorContract" placeholder="Comandă MASECO" value="{{ $searchTransportatorContract }}">
                        </div>
                        <div class="col-lg-4">
                            <input type="text" class="form-control rounded-3" id="searchClientContract" name="searchClientContract" placeholder="Comandă CLIENT" value="{{ $searchClientContract }}">
                        </div>
                        <div class="col-lg-4 d-flex justify-content-center align-items-center">
                            <label for="searchDataCreare" class="mb-0 align-self-center me-1"><small>Dată creare:</small></label>
                            <vue-datepicker-next
                                data-veche="{{ $searchDataCreare }}"
                                nume-camp-db="searchDataCreare"
                                tip="date"
                                range="range"
                                value-type="YYYY-MM-DD"
                                format="DD.MM.YYYY"
                                :latime="{ width: '210px' }"
                            ></vue-datepicker-next>
                        </div>
                        <div class="col-lg-4">
                            <select name="searchStare" id="searchStare" class="form-select bg-white rounded-3 {{ $errors->has('stare') ? 'is-invalid' : '' }}">
                                <option value="" selected>Selectează Stare</option>
                                <option value="1" {{ intval($searchStare) === 1 ? 'selected' : '' }}>Deschise</option>
                                <option value="2" {{ intval($searchStare) === 2 ? 'selected' : '' }}>Închise</option>
                                <option value="3" {{ intval($searchStare) === 3 ? 'selected' : '' }}>Anulate</option>
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <select name="searchUser" id="searchUser" class="form-select bg-white rounded-3 {{ $errors->has('stare') ? 'is-invalid' : '' }}">
                                <option value="" selected>Selectează Utilizator</option>
                                @foreach ($useri as $user)
                                    <option value="{{ $user->id }}" {{ intval($searchUser) === $user->id ? 'selected' : ''  }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <select name="searchOperatorUser" id="searchOperatorUser" class="form-select bg-white rounded-3 {{ $errors->has('stare') ? 'is-invalid' : '' }}">
                                <option value="" selected>Selectează Operator</option>
                                @foreach ($useri as $user)
                                    <option value="{{ $user->id }}" {{ intval($searchOperatorUser) === $user->id ? 'selected' : ''  }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-1 custom-search-form d-flex justify-content-center">
                        <div class="col-lg-5" style="position:relative;" v-click-out="() => firmeTransportatoriListaAutocomplete = ''">
                            <input
                                type="hidden"
                                v-model="firmaTransportatorId"
                                name="searchTransportatorId">

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
                                    class="form-control bg-white rounded-3 {{ $errors->has('firmaTransportatorNume') ? 'is-invalid' : '' }}"
                                    name="firmaTransportatorNume"
                                    placeholder="Transportator"
                                    autocomplete="off"
                                    aria-describedby="firmaTransportatorNume"
                                    required>
                                <div class="input-group-prepend d-flex align-items-center">
                                    <div v-if="firmaTransportatorId" class="input-group-text p-2 text-danger" id="firmaTransportatorNume" v-on:click="firmaTransportatorId = null; firmaTransportatorNume = ''"><i class="fa-solid fa-xmark"></i></div>
                                </div>
                            </div>
                            <div v-cloak v-if="firmeTransportatoriListaAutocomplete && firmeTransportatoriListaAutocomplete.length" class="panel-footer" style="width:100%; position:absolute; z-index: 1000;">
                                <div class="list-group" style="max-height: 130px; overflow:auto;">
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
                        </div>
                        <div class="col-lg-5" style="position:relative;" v-click-out="() => firmeClientiListaAutocomplete = ''">
                            <input
                                type="hidden"
                                v-model="firmaClientId"
                                name="searchClientId">

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
                                    class="form-control bg-white rounded-3 {{ $errors->has('firmaClientNume') ? 'is-invalid' : '' }}"
                                    name="firmaClientNume"
                                    placeholder="Client"
                                    autocomplete="off"
                                    aria-describedby="firmaClientNume"
                                    required>
                                <div class="input-group-prepend d-flex align-items-center">
                                    <div v-if="firmaClientId" class="input-group-text p-2 text-danger" id="firmaClientNume" v-on:click="firmaClientId = null; firmaClientNume = ''"><i class="fa-solid fa-xmark"></i></div>
                                </div>
                            </div>
                            <div v-cloak v-if="firmeClientiListaAutocomplete && firmeClientiListaAutocomplete.length" class="panel-footer" style="width:100%; position:absolute; z-index: 1000;">
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
                        </div>
                        <div class="col-lg-2">
                            <input type="text" class="form-control rounded-3" id="searchNrAuto" name="searchNrAuto" placeholder="Nr. auto" value="{{ $searchNrAuto }}">
                        </div>
                    </div>
                    <div class="row custom-search-form justify-content-center">
                        <button class="btn btn-sm btn-primary text-white col-md-4 me-3 border border-dark rounded-3" type="submit">
                            <i class="fas fa-search text-white me-1"></i>Caută
                        </button>
                        <a class="btn btn-sm btn-secondary text-white col-md-4 border border-dark rounded-3" href="{{ url()->current() }}" role="button">
                            <i class="far fa-trash-alt text-white me-1"></i>Resetează căutarea
                        </a>
                    </div>
                </form>
            </div>
            <div class="col-lg-2 text-lg-end">
                <a class="btn btn-sm btn-success text-white border border-dark rounded-3" href="{{ url()->current() }}/adauga" role="button">
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă comandă
                </a>
            </div>
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors')

            <div class="table-responsive rounded">
                <table class="table table-sm table-striped table-hover rounded">
                    <thead class="text-white rounded culoare2">
                    {{-- <thead class="text-white rounded" style="background-color: #69A1B1"> --}}
                        <tr class="" style="padding:2rem">
                            <th class="">#</th>
                            <th class="">Contract</th>
                            <th class="">Dată creare</th>
                            <th class="">Transportator</th>
                            <th class="">Clienți</th>
                            <th class="text-center small">Zile scadente<br>Ctr. Client</th>
                            <th class="">Încărcări</th>
                            <th class="">Descărcări</th>
                            <th class="">Nr. auto</th>
                            <th class="text-center small">Status<br>FrmDoc</th>
                            <th class="text-center small">Contract<br>Debit note</th>
                            {{-- <th class="text-center small">Trimite Ctr.<br>pe email</th> --}}
                            <th class="text-center">Mesaje<br>trimise</th>
                            <th class="text-center">Stare</th>
                            <th class="text-center">Utilizator<br>Operator</th>
                            <th class="text-end">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody id="statusuri">
                        @forelse ($comenzi as $comanda)
                            <tr>
                                <td align="">
                                    {{ ($comenzi ->currentpage()-1) * $comenzi ->perpage() + $loop->index + 1 }}
                                </td>
                                <td class="">
                                    {{ $comanda->transportator_contract }}
                                    <br>
                                    {{-- {{ $comanda->ultimaDescarcare ?? '' }} --}}
                                    {{-- {{ $comanda->ultimaDescarcare()->data_ora ?? '' }} --}}
                                    {{-- {{ $comanda->ultimaDescarcare()->pivot->data_ora ?? '' }} --}}
                                </td>
                                <td class="">
                                    {{ $comanda->data_creare ? \Carbon\Carbon::parse($comanda->data_creare)->isoFormat('DD.MM.YYYY') : '' }}
                                </td>
                                <td class="">
                                    {{ $comanda->transportator->nume ?? ''}}
                                </td>
                                <td class="">
                                    @foreach ($comanda->clienti as $client)
                                        {{ $client->nume }}
                                        <br>
                                    @endforeach
                                    {{-- {{ $comanda->client->nume ?? ''}} --}}
                                </td>
                                <td class="text-center">
                                    {{ $comanda->client_zile_scadente }}
                                </td>
                                <td style="width:150px">
                                    @foreach ($comanda->locuriOperareIncarcari as $locOperareIncarcare)
                                        <p class="mb-0" style="display: inline-block">
                                            {{ $locOperareIncarcare->pivot->data_ora ? \Carbon\Carbon::parse($locOperareIncarcare->pivot->data_ora)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                        </p>
                                    @endforeach
                                </td>
                                <td style="width:150px">
                                    @foreach ($comanda->locuriOperareDescarcari as $locOperareDescarcare)
                                        <p class="mb-0" style="display: inline-block">
                                            {{ $locOperareDescarcare->pivot->data_ora ? \Carbon\Carbon::parse($locOperareDescarcare->pivot->data_ora)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                        </p>
                                    @endforeach
                                </td>
                                <td>
                                    {{ $comanda->camion->numar_inmatriculare ?? ''}}
                                </td>
                                <td>
                                    <div class="text-center">
                                        {{-- <a data-bs-toggle="collapse" href="#status{{ $comanda->id }}" role="button" aria-expanded="false" aria-controls="Status"> --}}
                                            {{-- <span class="badge bg-primary" @click="setComandaId({{ $comanda->id}})"> --}}
                                            <span class="badge bg-info mb-1"
                                                v-on:click="
                                                    if (comandaId === {{$comanda->id}}){
                                                        comandaId = '';
                                                        statusuri = [];
                                                    } else {
                                                        statusuri = [];
                                                        comandaId = {{$comanda->id}};
                                                        mesajLipsaStatusuri = '';
                                                        getStatusuri();
                                                    }"
                                                    {{-- ? (comandaId = '';statusuri = []) : ((comandaId = {{$comanda->id}});getStatusuri())" --}}
                                                    >
                                            {{-- <span class="badge bg-primary" @click="comandaId = {{$comanda->id}}"> --}}
                                                <i class="fa-solid fa-arrows-up-down" style=""></i>
                                            </span>
                                        {{-- </a> --}}
                                        <br>
                                        @if ($comanda->transportator_format_documente == "1")
                                            <i class="fa-solid fa-envelope text-danger" title="Documentele se trimit prin posta"></i>
                                        @elseif ($comanda->transportator_format_documente == "2")
                                            <i class="fa-solid fa-at text-success" title="Documentele se trimit digital"></i>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center mb-1">
                                        <!-- First row: Contract-related buttons in blue -->
                                        <div class="me-1">
                                            <a href="{{ $comanda->path() }}/export-pdf" target="_blank" class="flex">
                                                <span class="badge bg-primary px-1" title="Contract">
                                                    {{-- <i class="fas fa-file-contract fa-1x" title="Contract"></i> --}}
                                                    Ctr.
                                                </span>
                                            </a>
                                        </div>
                                        <div class="">
                                            <a
                                                href="#"
                                                data-bs-toggle="modal"
                                                data-bs-target="#trimiteCatreTransportator{{ $comanda->id }}"
                                                title="Trimite Comanda"
                                            >
                                                <span class="badge bg-primary px-1">
                                                    {{-- Trimite --}}
                                                    <i class="fa-solid fa-envelope fa-1x"></i>
                                                    <span class="badge bg-dark py-0 px-1" style="font-size:100%" title="Numărul de emailuri trimise până acum">{{ $comanda->contracte_trimise_pe_email_catre_transportator_count }}</span>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-center">
                                        <!-- Second row: Debit note-related buttons in red -->
                                        <div class="me-1">
                                            <a href="{{ $comanda->path() }}/export-debit-note-pdf" target="_blank" class="flex">
                                                <span class="badge bg-danger px-1" title="Debit note">
                                                    {{-- <i class="fas fa-file-invoice-dollar fa-1x"></i> --}}
                                                    Dbn
                                                </span>
                                            </a>
                                        </div>
                                        <div class="">
                                            <a
                                                href="#"
                                                data-bs-toggle="modal"
                                                data-bs-target="#trimiteDebitNoteCatreTransportator{{ $comanda->id }}"
                                                title="Trimite Debit note"
                                            >
                                                <span class="badge bg-danger px-1">
                                                    {{-- Trimite --}}
                                                    <i class="fa-solid fa-envelope fa-1x"></i>
                                                    <span class="badge bg-dark py-0 px-1" style="font-size:100%" title="Numărul de emailuri trimise până acum">{{ $comanda->debit_note_trimise_pe_email_catre_transportator_count }}</span>
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                </td>

                                {{-- <td>
                                    <div class="d-flex justify-content-center">
                                        <a
                                            href="#"
                                            data-bs-toggle="modal"
                                            data-bs-target="#trimiteCatreTransportator{{ $comanda->id }}"
                                            title="Trimite Comanda"
                                        >
                                            <span class="badge bg-primary">
                                                Trimite
                                                <span class="badge bg-dark" title="Numărul de emailuri trimise până acum">{{ $comanda->contracte_trimise_pe_email_catre_transportator_count }}</span>
                                            </span>
                                        </a>
                                    </div>
                                </td> --}}
                                <td>
                                    <div class="text-center">
                                        <div class="mb-1">
                                            <a class="" data-bs-toggle="collapse" href="#emailuriTrimise{{ $comanda->id }}" role="button" aria-expanded="false" aria-controls="emailuriTrimise{{ $comanda->id }}">
                                                <span class="badge bg-info align-items-center">
                                                    <i class="fa-solid fa-arrows-up-down pe-1 fa-sm" style=""></i> Email
                                                </span>
                                            </a>
                                        </div>
                                        <div>
                                            <a class="" data-bs-toggle="collapse" href="#smsTrimise{{ $comanda->id }}" role="button" aria-expanded="false" aria-controls="smsTrimise{{ $comanda->id }}">
                                                <span class="badge bg-info align-items-center">
                                                    <i class="fa-solid fa-arrows-up-down pe-1 fa-sm" style=""></i> Sms
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="text-center">
                                        <a href="{{ $comanda->path() }}/stare/deschide" class="flex me-1" title="Deschisă">
                                            <span class="badge {{ $comanda->stare === 1 ? 'bg-success' : 'bg-light text-dark' }}">
                                                <i class="fa-solid fa-lock-open fa-1x"></i>
                                            </span>
                                        </a>
                                        <a href="{{ $comanda->path() }}/stare/inchide" class="flex me-1" title="Închisă">
                                            <span class="badge {{ $comanda->stare === 2 ? 'bg-dark' : 'bg-white text-dark' }}">
                                                <i class="fa-solid fa-lock fa-1x"></i>
                                            </span>
                                        </a>
                                        <a href="{{ $comanda->path() }}/stare/anuleaza" class="flex me-1" title="Anulată">
                                            <span class="badge {{ $comanda->stare === 3 ? 'bg-danger' : 'bg-light text-dark' }}">
                                                <i class="fa-solid fa-ban fa-1x"></i>
                                            </span>
                                        </a>
                                    </div>
                                </td>
                                <td class="text-center small">
                                    {{ $comanda->user->name ?? '' }}
                                    <br>
                                    {{ $comanda->operator->name ?? '' }}
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end">
                                        <div class="mb-1 me-1">
                                            <a href="{{ $comanda->path() }}/modifica" class="flex">
                                                <span class="badge bg-primary">Modifică</span>
                                            </a>
                                        </div>
                                        <div class="">
                                            <a
                                                href="#"
                                                data-bs-toggle="modal"
                                                data-bs-target="#stergeComanda{{ $comanda->id }}"
                                                title="Șterge Comanda"
                                                >
                                                <span class="badge bg-danger">Șterge</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <div class="mb-1 me-1">
                                            <a href="/facturi-memento/deschide/comanda/{{ $comanda->id }}" class="flex">
                                                <span class="badge bg-warning text-dark">Fact.</span>
                                            </a>
                                        </div>
                                        <div class="mb-1 me-1">
                                            <a href="{{ $comanda->path() }}/export-excel" class="flex">
                                                <span class="badge bg-success">Xls</span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <div class="mb-1 me-1">
                                            <a href="{{ url('/comanda-documente-transportator/' . $comanda->cheie_unica) }}" class="flex" title="Documente transportator">
                                                <span class="badge culoare1">Doc. Trans.</span>
                                            </a>
                                        </div>
                                        <div class="mb-1">
                                            <a href="{{ url('/comenzi/' . $comanda->id . '/fisiere-interne') }}" class="flex" title="Documente interne">
                                                <span class="badge bg-secondary">Doc. Int.</span>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="comandaId === {{ $comanda->id }}">
                                <td colspan="13">
                                    <div class="table-responsive rounded mx-auto w-75">
                                        <table class="table table-striped table-hover rounded">
                                            <thead class="text-white rounded culoare2">
                                                <tr class="" style="padding:2rem">
                                                    <th colspan="5" class="text-center">
                                                        Comanda {{ $comanda->transportator_contract }} | Statusuri
                                                    </th>
                                                </tr>
                                                <tr class="" style="padding:2rem">
                                                    <th class="">#</th>
                                                    <th class="">Status</th>
                                                    <th class="">Mod transmitere</th>
                                                    <th class="text-center">Ora</th>
                                                    <th class="text-center">Data</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(status, index) in statusuri">
                                                    <td>
                                                        @{{ index+1 }}
                                                    </td>
                                                    <td >
                                                        @{{ status.status }}
                                                    </td>
                                                    <td>
                                                        @{{ status.mod_transmitere }}
                                                    </td>
                                                    <td class="text-center">
                                                        @{{ status.ora }}
                                                    </td>
                                                    <td class="text-center">
                                                        @{{ status.data }}
                                                    </td>
                                                </tr>
                                                <tr v-if="mesajLipsaStatusuri">
                                                    <td colspan="5">@{{ mesajLipsaStatusuri }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>

                            <tr class="collapse" id="emailuriTrimise{{ $comanda->id }}">
                                <td colspan="13">
                                    <div class="table-responsive rounded mx-auto w-75">
                                        <table class="table table-striped table-hover rounded">
                                            <thead class="text-white rounded culoare2">
                                                <tr class="" style="padding:2rem">
                                                    <th colspan="4" class="text-center">
                                                        Comanda {{ $comanda->transportator_contract }} | Email-uri trimise
                                                    </th>
                                                </tr>
                                                <tr class="" style="padding:2rem">
                                                    <th style="">#</th>
                                                    <th class="">Email</th>
                                                    <th style="text-center">Mesaj</th>
                                                    <th class="text-right">Data trimitere</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($comanda->mesajeTrimiseEmail as $email)
                                                    <tr>
                                                        <td>
                                                            {{ $loop->iteration }}
                                                        </td>
                                                        <td class="">
                                                            {{ $email->email }}
                                                        </td>
                                                        <td class="">
                                                            @switch ($email->categorie)
                                                                @case(1)
                                                                    Informare de începere comandă
                                                                    @break
                                                                @case(2)
                                                                    Cerere Status de la Transportator
                                                                    @break
                                                                @case(3)
                                                                    Contract către Transportator
                                                                    @break
                                                                @case(4)
                                                                    CCA către Transportator
                                                                    @break
                                                                @case(5)
                                                                    Răspuns Transportator la cerere Status
                                                                    @break
                                                            @endswitch
                                                        </td>
                                                        <td class="text-right">
                                                            {{ $email->created_at ? \Carbon\Carbon::parse($email->created_at)->isoFormat('HH:mm - DD.MM.YYYY') : '' }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6">
                                                            Nu au fost trimise email-uri pentru această comandă
                                                        </td>
                                                    </tr>
                                                @endforelse
                                                </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>

                            <tr class="collapse" id="smsTrimise{{ $comanda->id }}">
                                <td colspan="13">
                                    <div class="table-responsive rounded mx-auto w-75">
                                        <table class="table table-striped table-hover rounded">
                                            <thead class="text-white rounded culoare2">
                                                <tr class="" style="padding:2rem">
                                                    <th colspan="6" class="text-center">
                                                        Comanda {{ $comanda->transportator_contract }} | Sms-uri trimise
                                                    </th>
                                                </tr>
                                                <tr class="" style="padding:2rem">
                                                    <th style="">#</th>
                                                    <th style="">Telefon SMS</th>
                                                    <th class="text-center">Mesaj</th>
                                                    <th class="text-center">Trimis</th>
                                                    <th class="text-center">Mesaj success/ eroare</th>
                                                    <th class="text-right">Data trimitere</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse ($comanda->mesajeTrimiseSms as $mesajSms)
                                                    <tr>
                                                        <td>
                                                            {{ $loop->iteration }}
                                                        </td>
                                                        <td class="">
                                                            {{ $mesajSms->telefon ?? '' }}
                                                        </td>
                                                        <td class="">
                                                            {{ $mesajSms->mesaj }}
                                                        </td>
                                                        <td class="text-center">
                                                            @if ($mesajSms->trimis === 1)
                                                                <span class="text-success">DA</span>
                                                            @else
                                                                <span class="text-danger">NU</span>
                                                            @endif
                                                        </td>
                                                        <td class="">
                                                            {{ $mesajSms->raspuns }}
                                                        </td>
                                                        <td class="text-right">
                                                            {{ $mesajSms->created_at ? \Carbon\Carbon::parse($mesajSms->created_at)->isoFormat('HH:mm - DD.MM.YYYY') : '' }}
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6">
                                                            Nu au fost trimise sms-uri pentru această comandă
                                                        </td>
                                                    </tr>
                                                @endforelse
                                                </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            {{-- <div>Nu s-au gasit rezervări în baza de date. Încearcă alte date de căutare</div> --}}
                        @endforelse
                        </tbody>
                </table>
            </div>

                <nav>
                    <ul class="pagination justify-content-center">
                        {{$comenzi->appends(Request::except('page'))->links()}}
                    </ul>
                </nav>
        </div>
    </div>

    {{-- Modalele pentru trimitere comanda catre transportator --}}
    <div id="disableButton1">
        @foreach ($comenzi as $comanda)
            <div class="modal fade text-dark" id="trimiteCatreTransportator{{ $comanda->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white" id="exampleModalLabel">Comanda: <b>{{ $comanda->transportator_contract }}</b></h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="text-align:left;">
                        Ești sigur ca vrei să trimiți Comanda către transportator?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                        <a href="{{ $comanda->path() }}/trimite-catre-transportator" class="btn btn-primary flex"
                            v-on:click="disableButton = true" :hidden="disableButton ? true : false">
                                Trimite
                                <span class="badge bg-dark" title="Numărul de emailuri trimise până acum">{{ $comanda->contracte_trimise_pe_email_catre_transportator_count }}</span>
                        </a>
                        <span class="text-center"
                            :hidden="disableButton ? false : true"
                        >Se trimite emailul</span>

                    </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Modalele pentru trimitere debit note catre transportator --}}
    <div id="disableButton2">
        @foreach ($comenzi as $comanda)
            <div class="modal fade text-dark" id="trimiteDebitNoteCatreTransportator{{ $comanda->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white" id="exampleModalLabel">Debit note comanda: <b>{{ $comanda->transportator_contract }}</b></h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="text-align:left;">
                        Ești sigur ca vrei să trimiți DEBIT NOTE către transportator?
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                        <a href="{{ $comanda->path() }}/trimite-debit-note-catre-transportator" class="btn btn-danger flex"
                            v-on:click="disableButton = true" :hidden="disableButton ? true : false">
                                Trimite
                                <span class="badge bg-dark" title="Numărul de emailuri trimise până acum">{{ $comanda->debit_note_trimise_pe_email_catre_transportator_count }}</span>
                        </a>
                        <span class="text-center"
                            :hidden="disableButton ? false : true"
                        >Se trimite emailul</span>

                    </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Modalele pentru stergere comanda --}}
    @foreach ($comenzi as $comanda)
        <div class="modal fade text-dark" id="stergeComanda{{ $comanda->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Comanda: <b>{{ $comanda->transportator_contract }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Ești sigur ca vrei să ștergi Comanda?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                    <form method="POST" action="{{ $comanda->path() }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Șterge Comanda
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection
