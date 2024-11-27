@extends ('layouts.app')

@php
    use \Carbon\Carbon;
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-file me-1"></i>Intermedieri
                </span>
            </div>
            <div class="col-lg-6">
                <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    <div class="row mb-1 custom-search-form justify-content-center">
                        {{-- <div class="col-lg-4 d-flex">
                            <label for="searchLuna" class="mb-0 align-self-center me-1">Luna:</label>
                            <input type="text" class="form-control rounded-3" id="searchLuna" name="searchLuna" placeholder="Luna" value="{{ $searchLuna }}">
                        </div>
                        <div class="col-lg-4 d-flex">
                            <label for="searchAn" class="mb-0 align-self-center me-1">An:</label>
                            <input type="text" class="form-control rounded-3" id="searchAn" name="searchAn" placeholder="An" value="{{ $searchAn }}">
                        </div> --}}

                        <div class="col-lg-6 d-flex align-items-center" id="datePicker">
                            <label for="searchInterval" class="pe-1">Interval:</label>
                            <vue-datepicker-next
                                data-veche="{{ $searchInterval }}"
                                nume-camp-db="searchInterval"
                                tip="date"
                                range="range"
                                value-type="YYYY-MM-DD"
                                format="DD.MM.YYYY"
                                :latime="{ width: '210px' }"
                            ></vue-datepicker-next>
                        </div>
                        <div class="col-lg-4">
                            <select name="searchUser" id="searchUser" class="form-select bg-white rounded-3 {{ $errors->has('stare') ? 'is-invalid' : '' }}">
                                <option value="" selected>Selectează Utilizator</option>
                                @foreach ($useri as $user)
                                    <option value="{{ $user->id }}" {{ intval($searchUser) === $user->id ? 'selected' : ''  }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row custom-search-form justify-content-center">
                        <div class="col-lg-4">
                            <button class="btn btn-sm w-100 btn-primary text-white border border-dark rounded-3" type="submit">
                                <i class="fas fa-search text-white me-1"></i>Caută
                            </button>
                        </div>
                        <div class="col-lg-4">
                            <a class="btn btn-sm w-100 btn-secondary text-white border border-dark rounded-3" href="{{ url()->current() }}" role="button">
                                <i class="far fa-trash-alt text-white me-1"></i>Resetează căutarea
                            </a>
                        </div>
                    </div>
                </form>
            </div>
            {{-- <div class="col-lg-3 text-end">
                <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8" href="{{ url()->current() }}/adauga" role="button">
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă memento
                </a>
            </div> --}}
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors')

            <div class="table-responsive rounded">
                <table class="table table-sm table-striped table-hover rounded" style="font-size: 0.8rem !important;">
                    <thead class="text-white rounded culoare2">
                        <tr class="" style="padding:2rem">
                            <th class="fs-6">#</th>
                            <th class="fs-6">Msc order</th>
                            <th class="fs-6">Spediteur</th>
                            <th class="fs-6">Carrier</th>
                            <th class="fs-6 text-end">Sold inițial</th>
                            <th class="fs-6 text-end">Sold final</th>
                            <th class="fs-6 text-end">Job value</th>
                            {{-- <th class="text-end">Prima încărcare</th> --}}
                            <th class="fs-6 text-center">Dată creare</th>
                            <th class="fs-6">Contract client</th>
                            <th class="fs-6">Frm<br>Doc</th>
                            <th class="fs-6">Nr. factură</th>
                            <th class="fs-6">Data factură</th>
                            <th class="fs-6">Aplicație</th>
                            <th class="fs-6">Achitat</th>
                            <th class="fs-6">Observații</th>
                            <th class="fs-6">Număr mașină</th>
                            <th class="fs-6">Motis</th>
                            <th class="fs-6">DKV</th>
                            <th class="fs-6 text-end">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($comenzi as $comanda)
                            <tr>
                                <td class="fs-6" align="">
                                    {{ ($comenzi ->currentpage()-1) * $comenzi ->perpage() + $loop->index + 1 }}
                                </td>
                                <td class="fs-6">
                                    {{ $comanda->transportator_contract }}
                                </td>
                                <td class="fs-6">
                                    {{ $comanda->transportator->nume ?? '' }}
                                </td>
                                <td class="fs-6">
                                    {{-- {{ $comanda->client->nume ?? '' }} --}}
                                    {{ $comanda->factura->client_nume ?? '' }}
                                </td>
                                <td class="fs-6 text-end">
                                    {{ $comanda->client_valoare_contract_initiala }} {{ $comanda->clientMoneda->nume ?? null }}
                                </td>
                                <td class="fs-6 text-end">
                                    {{ $comanda->client_valoare_contract }} {{ $comanda->clientMoneda->nume ?? null }}
                                </td>
                                <td class="fs-6 text-end">
                                    {{ $comanda->transportator_valoare_contract }} {{ $comanda->transportatorMoneda->nume ?? null }}
                                </td>
                                {{-- <td class="text-end">
                                    @foreach ($comanda->locuriOperareIncarcari as $locOperareIncarcare)
                                        @if($loop->first)
                                            <p class="mb-0" style="display: inline-block">
                                                {{ $locOperareIncarcare->pivot->data_ora ? Carbon::parse($locOperareIncarcare->pivot->data_ora)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                            </p>
                                        @endif
                                    @endforeach
                                </td> --}}
                                <td class="fs-6 text-center">
                                    {{ $comanda->data_creare ? Carbon::parse($comanda->data_creare)->isoFormat('DD.MM.YYYY') : null }}
                                </td>
                                <td class="fs-6">
                                    {{ $comanda->factura->client_contract ?? '' }}
                                </td>
                                <td class="fs-6">
                                    @if ($comanda->transportator_format_documente == "1")
                                        <i class="fa-solid fa-envelope text-danger" title="Documentele se trimit prin posta"></i>
                                    @elseif ($comanda->transportator_format_documente == "2")
                                        <i class="fa-solid fa-at text-success" title="Documentele se trimit digital"></i>
                                    @endif
                                </td>
                                <td class="fs-6">
                                    {{ $comanda->factura->seria ?? null }} {{ $comanda->factura->numar ?? null }}
                                </td>
                                <td class="fs-6">
                                    @if ($comanda->factura)
                                        {{ $comanda->factura->data ? Carbon::parse($comanda->factura->data)->isoFormat('DD.MM.YYYY') : null }}
                                    @endif
                                </td>
                                <td class="fs-6">
                                    {{ $comanda->intermediere->aplicatie ?? null }}
                                </td>
                                <td class="fs-6">
                                    @if ($comanda->factura)
                                        {{ $comanda->factura->data_plata_transportator ? Carbon::parse($comanda->factura->data_plata_transportator)->isoFormat('DD.MM.YYYY') : null }}
                                    @endif
                                </td>
                                <td class="fs-6">
                                    {{ $comanda->intermediere->observatii ?? null }}
                                </td>
                                <td class="fs-6">
                                    {{ $comanda->camion->numar_inmatriculare ?? null }}
                                </td>
                                <td class="fs-6">
                                    {{ $comanda->intermediere->motis ?? null }}
                                </td>
                                <td class="fs-6">
                                    {{ $comanda->intermediere->dkv ?? null }}
                                </td>
                                <td class="fs-6">
                                    <div class="d-flex justify-content-end">
                                        <div class="mb-1 me-1">
                                            @if (!$comanda->intermediere)
                                                <a href="{{ url()->current() }}/adauga?comandaId={{ $comanda->id }}" class="flex">
                                                    <span class="badge bg-primary">Modifică</span>
                                                </a>
                                            @else
                                                <a href="{{ $comanda->intermediere->path() }}/modifica" class="flex">
                                                    <span class="badge bg-primary">Modifică</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
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

    {{-- Modalele pentru stergere memento --}}
    {{-- @foreach ($comenzi as $memento)
        <div class="modal fade text-dark" id="stergeMemento{{ $memento->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Memento: <b>{{ $memento->nume }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Ești sigur ca vrei să ștergi Mementoul?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                    <form method="POST" action="{{ $memento->path() }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Șterge Mementoul
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach --}}

@endsection
