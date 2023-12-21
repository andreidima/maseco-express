@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    Explorer fișiere
                </span>
            </div>
            <div class="col-lg-6">
                <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    <div class="row mb-1 custom-search-form justify-content-center">
                        {{-- <div class="col-lg-4">
                            <input type="text" class="form-control rounded-3" id="search_nume" name="search_nume" placeholder="Nume" value="{{ $search_nume }}">
                        </div> --}}
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
                <div class="d-flex">
                    <div class="me-3">
                        <a
                            class="btn btn-sm btn-success text-white border border-dark rounded-3"
                            href="#"
                            data-bs-toggle="modal"
                            data-bs-target="#creazaDirector"
                            title="Crează Director"
                            >
                            <i class="fas fa-plus-square text-white me-1"></i>Crează director
                        </a>
                    </div>
                    <div>
                        <a
                            class="btn btn-sm btn-success text-white border border-dark rounded-3"
                            href="#"
                            data-bs-toggle="modal"
                            data-bs-target="#adaugaFisiere"
                            title="Adaugă fișiere"
                            >
                            <i class="fas fa-plus-square text-white me-1"></i>Adaugă fișiere
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors')

            <div class="table-responsive rounded">
                <table class="table table-striped table-hover rounded">
                    <thead class="text-white rounded culoare2">
                        <th class="" style="">
                            {{-- \ {{ str_replace('/', ' \ ', $cale) }} --}}
                            Cale:
                                    <a href="/file-manager-personalizat/" style="color:white; text-decoration: white;">
                                        <i class="fa-solid fa-hard-drive"></i>
                                    </a>
                                    @php
                                        $exploded = explode("/", $cale);
                                        // dd($exploded, count($exploded));
                                    @endphp
                                    {{ $cale ? '\\' : '' }}
                                    @foreach ($exploded as $item)
                                        @php
                                            $caleDirectorCurent = '';
                                        @endphp
                                        @for ($i = 0; $i < $loop->iteration; $i++)
                                            @php
                                                $caleDirectorCurent .= $exploded[$i] . '\\';
                                            @endphp
                                        @endfor
                                        <a href="/file-manager-personalizat/{{ $caleDirectorCurent }}" style="color:white; text-decoration: underline white;">
                                            {{ $item }}
                                        </a>
                                            \
                                    @endforeach
                        </th>
                        <th class="text-end">Acțiuni</th>
                    </thead>
                    <tbody>
                        {{-- @if ($cale)
                            <tr>
                                <td colspan="2" align="">
                                    <a href="/file-manager-personalizat/{{ substr($cale, 0, strrpos( $cale, '/')) }}" style="text-decoration:cornflowerblue">
                                        <div class="d-flex">
                                            <div class="px-1">
                                                <i class="fa-solid fa-up-long fa-lg"></i>
                                            </div>
                                            <div class="d-flex align-items-end">
                                                <i class="fa-solid fa-ellipsis fa-lg pb-2"></i>
                                            </div>
                                        </div>
                                    </a>
                                    <a href="/file-manager-personalizat/{{ substr($cale, 0, strrpos( $cale, '/')) }}" style="text-decoration:cornflowerblue">
                                        <i class="fa-solid fa-hard-drive"></i>
                                    </a>
                                    @php
                                        $exploded = explode("/", $cale);
                                    @endphp
                                    \
                                    @foreach ($exploded as $item)
                                        @php
                                            $caleDirectorCurent = '';
                                        @endphp
                                        @for ($i = 0; $i < $loop->iteration; $i++)
                                            @php
                                                $caleDirectorCurent .= $exploded[$i] . '\\';
                                            @endphp
                                        @endfor
                                        <a href="/file-manager-personalizat/{{ $caleDirectorCurent }}" style="text-decoration:cornflowerblue">
                                            {{ $item }}
                                        </a>
                                            \
                                    @endforeach
                                </td>
                            </tr>
                        @endif --}}
                        @foreach ($directoare as $director)
                            @php
                                $exploded = explode("/", $director);
                            @endphp
                            <tr>
                                <td align="">
                                    <a href="/file-manager-personalizat/{{ $director }}" style="text-decoration:cornflowerblue">
                                        <i class="fa-solid fa-folder text-warning"></i>
                                        {{ end($exploded) }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end">
                                        <div style="flex" class="">
                                            <a
                                                href="#"
                                                data-bs-toggle="modal"
                                                data-bs-target="#stergeDirector{{ $loop->iteration }}"
                                                title="Șterge Director"
                                                >
                                                <span class="badge bg-danger">Șterge</span>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                        @foreach ($fisiere as $fisier)
                            @php
                                $exploded = explode("/", $fisier);
                            @endphp
                            <tr>
                                <td align="">
                                    <a href="/file-manager-personalizat/fisier/deschide/{{ $fisier }}" target="_blank" style="text-decoration:cornflowerblue">
                                        <i class="fa-solid fa-file"></i>
                                        {{ end($exploded) }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end">
                                        <div style="flex" class="">
                                            <a
                                                href="#"
                                                data-bs-toggle="modal"
                                                data-bs-target="#stergeFisier{{ $loop->iteration }}"
                                                title="Șterge Fisier"
                                                >
                                                <span class="badge bg-danger">Șterge</span>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modala pentru creare director --}}
    <div class="modal fade text-dark" id="creazaDirector" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="/file-manager-personalizat/director/creaza">
                @csrf

                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="exampleModalLabel">Director nou</b></h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="text-align:left;">
                        <input type="hidden" class="form-control rounded-3" id="cale" name="cale" value="{{ $cale }}">

                        <label for="numeDirector" class="mb-0 ps-3">Nume director<span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="numeDirector" name="numeDirector">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                        <button type="submit" class="btn btn-success text-white">Crează Directorul</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modala pentru adăugare fisiere --}}
    <div class="modal fade text-dark" id="adaugaFisiere" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="/file-manager-personalizat/fisiere/adauga" enctype="multipart/form-data">
                @csrf

                <div class="modal-content">
                    <div class="modal-header bg-success">
                        <h5 class="modal-title text-white" id="exampleModalLabel">Adaugă fișiere</b></h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="text-align:left;">
                        <input type="hidden" class="form-control rounded-3" id="cale" name="cale" value="{{ $cale }}">

                        <label for="file" class="form-label mb-0 ps-3">Fișiere</label>
                        <input type="file" name="fisiere[]" class="form-control rounded-3" multiple>
                        @if($errors->has('fisiere'))
                            <span class="help-block text-danger">{{ $errors->first('fisiere') }}</span>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                        <button type="submit" class="btn btn-success text-white">Adaugă fișierele</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modalele pentru stergere directoare --}}
    @foreach ($directoare as $director)
        @php
            $exploded = explode("/", $director);
        @endphp
        <div class="modal fade text-dark" id="stergeDirector{{ $loop->iteration }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Director: <b>{{ end($exploded) }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Ești sigur ca vrei să ștergi Directorul?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                    <form method="POST" action="file-manager-personalizat/director/sterge/{{ $director }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Șterge Directorul
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Modalele pentru stergere fisier --}}
    @foreach ($fisiere as $fisier)
        @php
            $exploded = explode("/", $fisier);
        @endphp
        <div class="modal fade text-dark" id="stergeFisier{{ $loop->iteration }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Fișier: <b>{{ end($exploded) }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Ești sigur ca vrei să ștergi Fișierul?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                    <form method="POST" action="file-manager-personalizat/fisier/sterge/{{ $fisier }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Șterge Fișierul
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection
