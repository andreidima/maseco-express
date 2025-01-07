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
                        <div class="col-lg-6">
                            <input type="text" class="form-control rounded-3" id="searchNumarStatie" name="searchNumarStatie" placeholder="Număr stație" value="{{ $searchNumarStatie }}">
                        </div>
                        <div class="col-lg-6">
                            <input type="text" class="form-control rounded-3" id="searchNume" name="searchNume" placeholder="Nume stație" value="{{ $searchNume }}">
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
                <a href="#"
                    class="mb-1 btn btn-sm btn-success text-white border border-dark rounded-3"
                    data-bs-toggle="modal"
                    data-bs-target="#adaugaStatiiPeco"
                    title="Adaugă stații peco"
                    >
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă stații peco
                </a>
                <br>
                <a href="#"
                    class="btn btn-sm btn-danger text-white border border-dark rounded-3"
                    data-bs-toggle="modal"
                    data-bs-target="#stergeStatiiPeco"
                    title="Șterge stații peco"
                    >
                    <i class="far fa-trash-alt text-white me-1"></i>Șterge stațiile căutate</a>
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

    {{-- Modal to add statii peco --}}
    <div id="disableButton1">
        <div class="modal fade text-dark" id="adaugaStatiiPeco" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="exampleModalLabel">Adaugă stații peco</b></h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <form method="POST" action="/statii-peco/excel-import" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-body" style="text-align:left;">
                            Încarcă un fișier cu stații peco.
                            <br>
                            Șterge capul de tabel înainte de încărcare.
                            <br>
                            Coloanele trebuie să fie în ordinea din tabel: numar statie, nume, strada, cod_postal, localitate, coordonate.
                            <br><br>
                            <input type="file" name="fisier_excel">
                        </div>
                        <div class="modal-footer">

                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                            {{-- <a href="{{ $comanda->path() }}/trimite-catre-transportator" class="btn btn-primary flex"
                                v-on:click="disableButton = true" :hidden="disableButton ? true : false">
                                    Trimite
                                    <span class="badge bg-dark" title="Numărul de emailuri trimise până acum">{{ $comanda->contracte_trimise_pe_email_catre_transportator_count }}</span>
                            </a>
                            <span class="text-center"
                                :hidden="disableButton ? false : true"
                            >Se trimite emailul</span> --}}

                            <button type="submit" class="btn btn-success text-white"
                                v-on:click="disableButton = true" :hidden="disableButton ? true : false">
                                Adaugă stații peco
                            </button>
                            <span class="text-center"
                                :hidden="disableButton ? false : true"
                            >Se încarcă datele în baza de date</span>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal to mass delete statii peco --}}
    <div class="modal fade text-dark" id="stergeStatiiPeco" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Stații peco</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    În căutare sunt <span style="font-size: 140%; font-weight: bold;">{{ $totalCount }}</span> Stații Peco.
                    <br><br>
                    Ești sigur că vrei să le ștergi?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                    {{-- <form method="POST" action="{{ url()->current()  }}/mass-delete">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Șterge stațiile peco
                        </button>
                    </form> --}}
                    <form method="GET" action="{{ url()->current()  }}">
                        {{-- @method('DELETE') --}}
                        @csrf
                        <input type="hidden" id="searchNumarStatie" name="searchNumarStatie" value="{{ $searchNumarStatie }}">
                        <input type="hidden" id="searchNume" name="searchNume" value="{{ $searchNume }}">

                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            name="action" value="massDelete"
                            >
                            Șterge stațiile peco
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
