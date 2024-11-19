@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-gas-pump me-1"></i>Stații peco
                </span>
            </div>
            <div class="col-lg-6">
                <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    <div class="row mb-1 custom-search-form justify-content-center">
                        <div class="col-lg-8">
                            <input type="text" class="form-control rounded-3" id="searchNumarStatie" name="searchNumarStatie" placeholder="Număr stație" value="{{ $searchNumarStatie }}">
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
                            <th class="">Număr stație</th>
                            <th>Nume</th>
                            <th>Strada</th>
                            <th>Cod poștal</th>
                            <th>Localitate</th>
                            <th>Coordonate</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($statiiPeco as $statiePeco)
                            <tr>
                                <td align="">
                                    {{ ($statiiPeco ->currentpage()-1) * $statiiPeco ->perpage() + $loop->index + 1 }}
                                </td>
                                <td>{{ $statiePeco->numar_statie }}</td>
                                <td>{{ $statiePeco->nume }}</td>
                                <td>{{ $statiePeco->strada }}</td>
                                <td>{{ $statiePeco->cod_postal }}</td>
                                <td>{{ $statiePeco->localitate }}</td>
                                <td>{{ $statiePeco->coordonate }}</td>
                            </tr>
                        @empty
                            {{-- <div>Nu s-au gasit rezervări în baza de date. Încearcă alte date de căutare</div> --}}
                        @endforelse
                        </tbody>
                </table>
            </div>

                <nav>
                    <ul class="pagination justify-content-center">
                        {{$statiiPeco->appends(Request::except('page'))->links()}}
                    </ul>
                </nav>
        </div>
    </div>

@endsection
