@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    Explorer - vizualizare și descăcare fișiere
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
                <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8" href="{{ url()->current() }}/adauga" role="button">
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă
                </a>
            </div>
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors')

            <div class="table-responsive rounded">
                <table class="table table-striped table-hover rounded">
                    <thead class="text-white rounded culoare2">
                        <th class="" style="">
                            \ {{ str_replace('/', ' \ ', $cale) }}
                        </th>
                        <th class="text-end">Acțiuni</th>
                    </thead>
                    <tbody>
                        @if ($cale)
                            <tr>
                                <td colspan="2" align="">
                                    @php
                                        $exploded = explode("/", $cale);
                                    @endphp
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
                                    </td>
                                </tr>
                            @endif
                        @foreach ($directories as $directory)
                            @php
                                $exploded = explode("/", $directory);
                            @endphp
                            <tr>
                                <td align="">
                                    <a href="/file-manager-personalizat/{{ $directory }}" style="text-decoration:cornflowerblue">
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
                                                data-bs-target="#sterge{{ str_replace([' ', '.'], '', end($exploded)) }}"
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
                                    <a href="/file-manager-personalizat-fisier/deschide/{{ $fisier }}" target="_blank" style="text-decoration:cornflowerblue">
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
                                                data-bs-target="#sterge{{ str_replace([' ', '.'], '', end($exploded)) }}"
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

    {{-- Modalele pentru stergere fisier --}}
    @foreach ($fisiere as $fisier)
        @php
            $exploded = explode("/", $fisier);
            dd(array_combine($directories, $fisiere));
        @endphp
        <div class="modal fade text-dark" id="sterge{{ str_replace([' ', '.'], '', end($exploded)) }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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

                    <form method="POST" action="file-manager-personalizat-fisier/sterge/{{ $fisier }}">
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
