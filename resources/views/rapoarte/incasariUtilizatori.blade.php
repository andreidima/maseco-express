@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-chart-pie me-1"></i>Încasări utilizatori
                </span>
            </div>
            <div class="col-lg-6">
                <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    <div class="row mb-1 custom-search-form justify-content-center">
                        {{-- <div class="col-lg-2">
                            <select name="searchUser" id="searchUser" class="form-select bg-white rounded-3 {{ $errors->has('stare') ? 'is-invalid' : '' }}">
                                <option value="" selected>Selectează Utilizator</option>
                                @foreach ($useri as $user)
                                    <option value="{{ $user->id }}" {{ intval($searchUser) === $user->id ? 'selected' : ''  }}>{{ $user->name }}</option>
                                @endforeach
                            </select>
                        </div> --}}
                        <div class="col-lg-4 d-flex align-items-center" id="datePicker">
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
            <div class="col-lg-3 text-end">
            </div>
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors')

            <div class="table-responsive rounded">
                <table class="table table-striped table-hover rounded">
                    <thead class="text-white rounded culoare2">
                        <tr class="" style="padding:2rem">
                            <th class="">#</th>
                            <th class="">Utilizator</th>
                            <th class="text-end">Nr. comenzi</th>
                            <th class="text-end">Sumă totală intrări</th>
                            <th class="text-end">Sumă totală ieșiri</th>
                            <th class="text-end">Sumă totală profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($useri as $user)
                            <tr>
                                <td align="">
                                    {{ $loop->iteration }}
                                </td>
                                <td class="">
                                    {{ $user->name }}
                                </td>
                                <td class="text-end">
                                    {{ $user->comenzi->count() ?? '' }}
                                </td>
                                <td class="text-end">
                                    @foreach ($monede as $moneda)
                                        @if (($suma = $user->comenzi->where('client_moneda_id', $moneda->id)->sum('client_valoare_contract')) !== 0)
                                            {{ $suma }} {{ $moneda->nume }}
                                            <br>
                                        @endif
                                    @endforeach
                                </td>
                                <td class="text-end">
                                    @foreach ($monede as $moneda)
                                        @if (($suma = $user->comenzi->where('transportator_moneda_id', $moneda->id)->sum('transportator_valoare_contract')) !== 0)
                                            {{ $suma }} {{ $moneda->nume }}
                                            <br>
                                        @endif
                                    @endforeach
                                </td>
                                <td class="text-end">
                                    @foreach ($monede as $moneda)
                                        @if (($suma = $user->comenzi->where('client_moneda_id', $moneda->id)->sum('client_valoare_contract') - $user->comenzi->where('transportator_moneda_id', $moneda->id)->sum('transportator_valoare_contract')) !== 0)
                                            {{ $suma }} {{ $moneda->nume }}
                                            <br>
                                        @endif
                                    @endforeach
                                </td>
                            </tr>
                        @empty
                            {{-- <div>Nu s-au gasit rezervări în baza de date. Încearcă alte date de căutare</div> --}}
                        @endforelse
                        </tbody>
                </table>
            </div>
        </div>
    </div>

@endsection
