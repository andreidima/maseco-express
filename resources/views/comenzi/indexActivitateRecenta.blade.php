@php
    use Carbon\Carbon;
@endphp

@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-2 mb-2">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-clipboard-list me-1"></i>Comenzi <br> Activitate recentă
                </span>
            </div>
            <div class="col-lg-8 mb-0" id="datePicker">
                <form class="needs-validation mb-lg-0" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    <div class="row mb-1 custom-search-form d-flex justify-content-center">
                        <div class="col-lg-2">
                            <input type="text" class="form-control rounded-3" id="searchTransportatorContract" name="searchTransportatorContract" placeholder="Comandă MASECO" value="{{ $searchTransportatorContract }}">
                        </div>
                        <div class="col-lg-2">
                            <input type="text" class="form-control rounded-3" id="searchOperareDescriere" name="searchOperareDescriere" placeholder="Operare" value="{{ $searchOperareDescriere }}">
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
                        <div class="col-lg-4 d-flex justify-content-center align-items-center">
                            <label for="searchOperareData" class="mb-0 align-self-center me-1"><small>Dată operare:</small></label>
                            <vue-datepicker-next
                                data-veche="{{ $searchOperareData }}"
                                nume-camp-db="searchOperareData"
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
                        <div class="col-lg-4">
                            <select name="searchOperator" id="searchOperator" class="form-select bg-white rounded-3 {{ $errors->has('stare') ? 'is-invalid' : '' }}">
                                <option value="" selected>Selectează Operator</option>
                                @foreach ($useri as $user)
                                    <option value="{{ $user->id }}" {{ intval($searchOperator) === $user->id ? 'selected' : ''  }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-4">
                            <select name="searchUserOperare" id="searchUserOperare" class="form-select bg-white rounded-3 {{ $errors->has('stare') ? 'is-invalid' : '' }}">
                                <option value="" selected>Selectează User Operare</option>
                                @foreach ($useri as $user)
                                    <option value="{{ $user->id }}" {{ intval($searchUserOperare) === $user->id ? 'selected' : ''  }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
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
            </div>
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors')

            <div class="table-responsive rounded">
                <table class="table table-sm table-striped table-hover rounded">
                    <thead class="text-white rounded culoare2">
                        <tr class="" style="padding:2rem">
                            <th class="">#</th>
                            <th class="">Contract</th>
                            <th class="">Dată creare</th>
                            <th class="text-center">Utilizator<br>Operator</th>
                            <th class="">Operare</th>
                            <th class="text-center">Utilizator operare</th>
                            <th class="">Data operare</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($comenziIstoric as $comanda)
                            <tr>
                                <td>
                                    {{ ($comenziIstoric ->currentpage()-1) * $comenziIstoric ->perpage() + $loop->index + 1 }}
                                </td>
                                <td class="">
                                    <a href="{{ $comanda->path() }}/modifica">
                                        {{ $comanda->transportator_contract }}
                                    </a>
                                </td>
                                <td class="">
                                    {{ $comanda->data_creare ? Carbon::parse($comanda->data_creare)->isoFormat('DD.MM.YYYY') : '' }}
                                </td>
                                <td class="text-center">
                                    {{ $comanda->user->name ?? '' }}
                                    <br>
                                    {{ $comanda->operator->name ?? '' }}
                                </td>
                                <td class="">
                                    {{ $comanda->operare_descriere }}
                                </td>
                                <td class="text-center">
                                    {{ $comanda->userOperare->name ?? '' }}
                                </td>
                                <td class="">
                                    {{ $comanda->operare_data ? Carbon::parse($comanda->operare_data)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                </td>
                        @empty
                        @endforelse
                        </tbody>
                </table>
            </div>

                <div class="d-flex justify-content-center">
                    {{$comenziIstoric->appends(Request::except('page'))->links()}}
                </div>
        </div>
    </div>

@endsection
