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
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;" id="formularComanda">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-2 mb-2">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-clipboard-list me-1"></i>Comenzi
                </span>
            </div>
            <div class="col-lg-8 mb-2">
                <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    <div class="row mb-1 custom-search-form d-flex justify-content-center">
                        <div class="col-lg-2">
                            <input type="text" class="form-control rounded-3" id="searchTransportatorContract" name="searchTransportatorContract" placeholder="Ctr. transp." value="{{ $searchTransportatorContract }}">
                        </div>
                        <div class="col-lg-4 d-flex justify-content-center align-items-center">
                            <label for="searchDataCreare" class="mb-0 align-self-center me-1"><small>Dată creare:</small></label>
                            <vue-datepicker-next
                                data-veche="{{ $searchDataCreare }}"
                                nume-camp-db="searchDataCreare"
                                tip="date"
                                value-type="YYYY-MM-DD"
                                format="DD.MM.YYYY"
                                :latime="{ width: '125px' }"
                                style="margin-right: 20px;"
                            ></vue-datepicker-next>
                        </div>
                        <div class="col-lg-3" style="position:relative;" v-click-out="() => firmeTransportatoriListaAutocomplete = ''">
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
                        <div class="col-lg-3" style="position:relative;" v-click-out="() => firmeClientiListaAutocomplete = ''">
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
                <table class="table table-striped table-hover rounded">
                    <thead class="text-white rounded culoare2">
                    {{-- <thead class="text-white rounded" style="background-color: #69A1B1"> --}}
                        <tr class="" style="padding:2rem">
                            <th class="">#</th>
                            <th class="">Contract transportator</th>
                            <th class="">Dată creare</th>
                            <th class="">Transportator</th>
                            <th class="">Client</th>
                            <th class="">Zile scadente Ctr. Client</th>
                            <th class="text-center">Contract</th>
                            <th class="text-end">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($comenzi as $comanda)
                            <tr>
                                <td align="">
                                    {{ ($comenzi ->currentpage()-1) * $comenzi ->perpage() + $loop->index + 1 }}
                                </td>
                                <td class="">
                                    {{ $comanda->transportator_contract }}
                                </td>
                                <td class="">
                                    {{ $comanda->data_creare ? \Carbon\Carbon::parse($comanda->data_creare)->isoFormat('DD.MM.YYYY') : '' }}
                                </td>
                                <td class="">
                                    {{ $comanda->transportator->nume ?? ''}}
                                </td>
                                <td class="">
                                    {{ $comanda->client->nume ?? ''}}
                                </td>
                                <td class="">
                                    {{ $comanda->client_zile_scadente }}
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <a href="{{ $comanda->path() }}/export-html" class="flex me-1">
                                            <span class="badge bg-success">Contract</span>
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ $comanda->path() }}/modifica" class="flex me-1">
                                            <span class="badge bg-primary">Modifică</span>
                                        </a>
                                        <div style="flex" class="">
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
