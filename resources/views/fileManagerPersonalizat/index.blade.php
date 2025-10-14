@extends ('layouts.app')

@section('content')
<div class="container mx-auto mx-3 px-3 card" style="border-radius: 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0 0;">
        <div class="col-lg-3">
            <span class="badge culoare1 fs-5">
                <i class="fas fa-folder-open"></i> Explorer fișiere
            </span>
        </div>
        <div class="col-lg-6">
            <form class="needs-validation" novalidate method="GET" action="{{ url()->current() }}">
                @csrf
                <div class="row mb-1 custom-search-form justify-content-center">
                    <div class="col-lg-6">
                        <input type="text" class="form-control rounded-3" id="searchFisier" name="searchFisier" placeholder="Nume fișier" value="{{ $searchFisier }}">
                    </div>
                </div>
                <div class="row custom-search-form justify-content-center">
                    <button class="btn btn-sm btn-primary text-white col-md-4 me-3 border border-dark rounded-3" type="submit">
                        <i class="fas fa-search me-1"></i> Caută
                    </button>
                    <a class="btn btn-sm btn-secondary text-white col-md-4 border border-dark rounded-3" href="{{ url()->current() }}" role="button">
                        <i class="fas fa-undo me-1"></i> Resetează căutarea
                    </a>
                </div>
            </form>
        </div>
        <div class="col-lg-3 text-end">
            <div class="mb-2">
                <a class="btn btn-sm btn-success text-white border border-dark rounded-3"
                   href="#"
                   data-bs-toggle="modal"
                   data-bs-target="#creazaDirector"
                   title="Crează Director">
                    <i class="fas fa-folder-plus"></i> Crează director
                </a>
            </div>
            <div>
                <a class="btn btn-sm btn-success text-white border border-dark rounded-3"
                   href="#"
                   data-bs-toggle="modal"
                   data-bs-target="#adaugaFisiere"
                   title="Adaugă fișiere">
                    <i class="fas fa-file-upload"></i> Adaugă fișiere
                </a>
            </div>
        </div>
    </div>

    <div class="card-body px-0 py-3">

        @include ('errors')

        <div class="row">
            @if ($searchFisier)
                @if ($fisiereGasite)
                    <div class="col-lg-6 mx-auto table-responsive rounded-3">
                        <table class="table table-striped table-hover rounded-3">
                            <thead class="text-white rounded-3 culoare2">
                                <tr>
                                    <th class="text-center">Fișiere găsite în urma căutării</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($fisiereGasite as $fisier)
                                    <tr>
                                        <td>
                                            <a href="/file-manager-personalizat-fisier/deschide/{{ $fisier }}" target="_blank" style="text-decoration:cornflowerblue">
                                                {{ $fisier }}
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="d-flex justify-content-center">
                        <p class="px-3 bg-warning rounded" style="width:fit-content">Căutarea nu a găsit nici un fișier</p>
                    </div>
                @endif
            @endif
        </div>

        <div class="row">
            <!-- Left column: Directory Tree -->
            <div id="directoryTree" class="col-md-3" style="border-right: 1px solid #ddd; padding-right: 15px;">
                <div class="table-responsive rounded">
                    <table class="table rounded">
                        <thead class="text-white rounded culoare2">
                            <tr>
                                <th>Directoare</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="px-0">
                                    <!-- Pass the directoryTree as JSON -->
                                    <directory-tree :nodes='@json($directoryTree)'></directory-tree>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right column: Main File Manager Content -->
            <div class="col-md-9">
                <div class="table-responsive rounded">
                    <table class="table table-striped table-hover rounded">
                        <thead class="text-white rounded culoare2">
                            <tr>
                                <th>
                                    <a href="/file-manager-personalizat/" style="color:white; text-decoration: none;">
                                        Cale: <i class="fas fa-hdd"></i>
                                    </a>
                                    @php
                                        $exploded = explode("/", $cale);
                                    @endphp
                                    {{ $cale ? '/' : '' }}
                                    @foreach ($exploded as $item)
                                        @php $caleDirectorCurent = ''; @endphp
                                        @for ($i = 0; $i < $loop->iteration; $i++)
                                            @php $caleDirectorCurent .= $exploded[$i] . '\\'; @endphp
                                        @endfor
                                        <a href="/file-manager-personalizat/{{ $caleDirectorCurent }}" style="color:white; text-decoration: underline;">
                                            {{ $item }}
                                        </a>
                                        /
                                    @endforeach
                                </th>
                                @if (auth()->user()->isAdministrator())
                                    <th class="text-end">Acțiuni</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($directoare as $director)
                                @php
                                    $exploded = explode("/", $director);
                                    $dirName = end($exploded);
                                @endphp
                                <tr>
                                    <td>
                                        <a href="/file-manager-personalizat/{{ $director }}" style="text-decoration:cornflowerblue">
                                            <i class="fas fa-folder text-warning"></i> {{ $dirName }}
                                        </a>
                                    </td>
                                    <td>
                                        @if (auth()->user()->isAdministrator())
                                            <div class="d-flex justify-content-end">
                                                <!-- Modify button  -->
                                                <div class="me-1">
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#modificaCaleNumeDirector{{ $loop->iteration }}" title="Modifică cale nume Director">
                                                        <span class="badge bg-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </span>
                                                    </a>
                                                </div>
                                                <!-- Copy button -->
                                                <div class="me-1">
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#copyDirector{{ $loop->iteration }}" title="Copiază Director">
                                                        <span class="badge bg-info">
                                                            <i class="fas fa-copy"></i>
                                                        </span>
                                                    </a>
                                                </div>
                                                <!-- Move button -->
                                                <div class="me-1">
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#moveDirector{{ $loop->iteration }}" title="Mută Director">
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-arrows-alt"></i>
                                                        </span>
                                                    </a>
                                                </div>
                                                <!-- Delete button -->
                                                <div>
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#stergeDirector{{ $loop->iteration }}" title="Șterge Director">
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @foreach ($fisiere as $fisier)
                                @php
                                    $exploded = explode("/", $fisier);
                                    $fileName = end($exploded);
                                @endphp
                                <tr>
                                    <td>
                                        <a href="/file-manager-personalizat-fisier/deschide/{{ $fisier }}" target="_blank" style="text-decoration:cornflowerblue">
                                            <i class="fas fa-file"></i> {{ $fileName }}
                                        </a>
                                    </td>
                                    <td>
                                        @if (auth()->user()->isAdministrator())
                                            <div class="d-flex justify-content-end">
                                                <!-- Modify button -->
                                                <div class="me-1">
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#modificaCaleNumeFisier{{ $loop->iteration }}" title="Modifică cale nume Fișier">
                                                        <span class="badge bg-primary">
                                                            <i class="fas fa-edit"></i>
                                                        </span>
                                                    </a>
                                                </div>
                                                <!-- Copy button -->
                                                <div class="me-1">
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#copyFisier{{ $loop->iteration }}" title="Copiază Fișier">
                                                        <span class="badge bg-info">
                                                            <i class="fas fa-copy"></i>
                                                        </span>
                                                    </a>
                                                </div>
                                                <!-- Move button -->
                                                <div class="me-1">
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#moveFisier{{ $loop->iteration }}" title="Mută Fișier">
                                                        <span class="badge bg-warning">
                                                            <i class="fas fa-arrows-alt"></i>
                                                        </span>
                                                    </a>
                                                </div>
                                                <!-- Delete button -->
                                                <div>
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#stergeFisier{{ $loop->iteration }}" title="Șterge Fișier">
                                                        <span class="badge bg-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </span>
                                                    </a>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ======================= Modale ======================= --}}

{{-- Crează Director Modal --}}
<div class="modal fade text-dark" id="creazaDirector" tabindex="-1" role="dialog" aria-labelledby="creazaDirectorLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="/file-manager-personalizat-director/creaza">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="creazaDirectorLabel">
                        <i class="fas fa-folder-plus"></i> Director nou
                    </h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cale" name="cale" value="{{ $cale }}">
                    <div class="mb-3">
                        <label for="numeDirector" class="form-label">Nume director <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="numeDirector" name="numeDirector">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Renunță
                    </button>
                    <button type="submit" class="btn btn-success text-white">
                        <i class="fas fa-check"></i> Crează Directorul
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Adaugă Fisiere Modal --}}
<div class="modal fade text-dark" id="adaugaFisiere" tabindex="-1" role="dialog" aria-labelledby="adaugaFisiereLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="POST" action="/file-manager-personalizat-fisiere/adauga" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white" id="adaugaFisiereLabel">
                        <i class="fas fa-file-upload"></i> Adaugă fișiere
                    </h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="cale" name="cale" value="{{ $cale }}">
                    <div class="mb-3">
                        <label for="file" class="form-label">Fișiere</label>
                        <input type="file" name="fisiere[]" class="form-control rounded-3" multiple>
                        @if($errors->has('fisiere'))
                            <span class="help-block text-danger">{{ $errors->first('fisiere') }}</span>
                        @endif
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Renunță
                    </button>
                    <button type="submit" class="btn btn-success text-white">
                        <i class="fas fa-check"></i> Adaugă fișierele
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modifica Director Modal --}}
@foreach ($directoare as $director)
    @php $exploded = explode("/", $director); @endphp
    <div class="modal fade text-dark" id="modificaCaleNumeDirector{{ $loop->iteration }}" tabindex="-1" role="dialog" aria-labelledby="modificaDirectorLabel{{ $loop->iteration }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="/file-manager-personalizat-resursa/modifica-cale-nume">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white" id="modificaDirectorLabel{{ $loop->iteration }}">
                            <i class="fas fa-edit"></i> Director: <b>{{ end($exploded) }}</b>
                        </h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Modifică numele directorului.</p>
                        <input type="hidden" name="cale" value="{{ $cale }}">
                        <div class="mb-3">
                            <label for="numeVechi{{ $loop->iteration }}" class="form-label">Nume director - vechi</label>
                            <input type="text" class="form-control rounded-3" id="numeVechi{{ $loop->iteration }}" name="numeVechi" value="{{ end($exploded) }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="numeNou{{ $loop->iteration }}" class="form-label">Nume director - nou</label>
                            <input type="text" class="form-control rounded-3" id="numeNou{{ $loop->iteration }}" name="numeNou">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Renunță
                        </button>
                        <button type="submit" class="btn btn-primary text-white">
                            <i class="fas fa-edit"></i> Modifică
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endforeach

{{-- Copy Directory Modal --}}
@foreach ($directoare as $director)
    @php
        $exploded = explode("/", $director);
        $dirName = end($exploded);
    @endphp
    <div class="modal fade text-dark" id="copyDirector{{ $loop->iteration }}" tabindex="-1" role="dialog" aria-labelledby="copyDirLabel{{ $loop->iteration }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="/file-manager-personalizat-director/copy">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title text-white" id="copyDirLabel{{ $loop->iteration }}">
                            <i class="fas fa-copy"></i> Copiază director: <b>{{ $dirName }}</b>
                        </h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="source" value="{{ $director }}">
                        <!-- Current location -->
                        <div class="mb-3">
                            <label for="currentLocationCopyDir{{ $loop->iteration }}" class="form-label">Locația curentă</label>
                            <input type="text" class="form-control rounded-3" id="currentLocationCopyDir{{ $loop->iteration }}" name="currentLocation" value="{{ $cale }}" readonly>
                        </div>
                        <!-- Destination -->
                        <div class="mb-3">
                            <label for="destinationCopyDir{{ $loop->iteration }}" class="form-label">Destinație (calea părinte pentru noua locație)</label>
                            <input type="text" class="form-control rounded-3" id="destinationCopyDir{{ $loop->iteration }}" name="destination" placeholder="ex: folder/subfolder" value="{{ $cale }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Renunță
                        </button>
                        <button type="submit" class="btn btn-info text-white">
                            <i class="fas fa-copy"></i> Copiază
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endforeach

{{-- Move Directory Modal --}}
@foreach ($directoare as $director)
    @php
        $exploded = explode("/", $director);
        $dirName = end($exploded);
    @endphp
    <div class="modal fade text-dark" id="moveDirector{{ $loop->iteration }}" tabindex="-1" role="dialog" aria-labelledby="moveDirLabel{{ $loop->iteration }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="/file-manager-personalizat-director/move">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-white" id="moveDirLabel{{ $loop->iteration }}">
                            <i class="fas fa-arrows-alt"></i> Mută director: <b>{{ $dirName }}</b>
                        </h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="source" value="{{ $director }}">
                        <!-- Current location -->
                        <div class="mb-3">
                            <label for="currentLocationMoveDir{{ $loop->iteration }}" class="form-label">Locația curentă</label>
                            <input type="text" class="form-control rounded-3" id="currentLocationMoveDir{{ $loop->iteration }}" name="currentLocation" value="{{ $cale }}" readonly>
                        </div>
                        <!-- Destination -->
                        <div class="mb-3">
                            <label for="destinationMoveDir{{ $loop->iteration }}" class="form-label">Destinație (calea părinte pentru noua locație)</label>
                            <input type="text" class="form-control rounded-3" id="destinationMoveDir{{ $loop->iteration }}" name="destination" placeholder="ex: folder/subfolder" value="{{ $cale }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Renunță
                        </button>
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="fas fa-arrows-alt"></i> Mută
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endforeach

{{-- Sterge Director Modal --}}
@foreach ($directoare as $director)
    @php $exploded = explode("/", $director); @endphp
    <div class="modal fade text-dark" id="stergeDirector{{ $loop->iteration }}" tabindex="-1" role="dialog" aria-labelledby="stergeDirectorLabel{{ $loop->iteration }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="stergeDirectorLabel{{ $loop->iteration }}">
                        <i class="fas fa-trash"></i> Director: <b>{{ end($exploded) }}</b>
                    </h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Ești sigur că vrei să ștergi directorul?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Renunță
                    </button>
                    <form method="POST" action="/file-manager-personalizat-director/sterge/{{ $director }}">
                        @method('DELETE')
                        @csrf
                        <button type="submit" class="btn btn-danger text-white">
                            <i class="fas fa-trash"></i> Șterge Directorul
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

{{-- Modifica Fișier Modal --}}
@foreach ($fisiere as $fisier)
    @php $exploded = explode("/", $fisier); @endphp
    <div class="modal fade text-dark" id="modificaCaleNumeFisier{{ $loop->iteration }}" tabindex="-1" role="dialog" aria-labelledby="modificaFisierLabel{{ $loop->iteration }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="/file-manager-personalizat-resursa/modifica-cale-nume">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white" id="modificaFisierLabel{{ $loop->iteration }}">
                            <i class="fas fa-edit"></i> Fișier: <b>{{ end($exploded) }}</b>
                        </h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Modifică numele fișierului.</p>
                        <input type="hidden" name="cale" value="{{ $cale }}">
                        <input type="hidden" name="extensieFisier" value="{{ pathinfo(end($exploded), PATHINFO_EXTENSION) }}">
                        <div class="mb-3">
                            <label for="numeVechiFisier{{ $loop->iteration }}" class="form-label">Nume fișier - vechi</label>
                            <input type="text" class="form-control rounded-3" id="numeVechiFisier{{ $loop->iteration }}" name="numeVechi" value="{{ pathinfo(end($exploded), PATHINFO_FILENAME) }}" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="numeNouFisier{{ $loop->iteration }}" class="form-label">Nume fișier - nou</label>
                            <input type="text" class="form-control rounded-3" id="numeNouFisier{{ $loop->iteration }}" name="numeNou">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Renunță
                        </button>
                        <button type="submit" class="btn btn-primary text-white">
                            <i class="fas fa-edit"></i> Modifică
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endforeach

{{-- Copy Fișier Modal --}}
@foreach ($fisiere as $fisier)
    @php
        $exploded = explode("/", $fisier);
        $fileName = end($exploded);
    @endphp
    <div class="modal fade text-dark" id="copyFisier{{ $loop->iteration }}" tabindex="-1" role="dialog" aria-labelledby="copyFisierLabel{{ $loop->iteration }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="/file-manager-personalizat-fisier/copy">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-info">
                        <h5 class="modal-title text-white" id="copyFisierLabel{{ $loop->iteration }}">
                            <i class="fas fa-copy"></i> Copiază fișier: <b>{{ $fileName }}</b>
                        </h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="source" value="{{ $fisier }}">
                        <!-- Current location -->
                        <div class="mb-3">
                            <label for="currentLocationCopyFile{{ $loop->iteration }}" class="form-label">Locația curentă</label>
                            <input type="text" class="form-control rounded-3" id="currentLocationCopyFile{{ $loop->iteration }}" name="currentLocation" value="{{ $cale }}" readonly>
                        </div>
                        <!-- Destination -->
                        <div class="mb-3">
                            <label for="destinationCopyFile{{ $loop->iteration }}" class="form-label">Destinație (calea directorului țintă)</label>
                            <input type="text" class="form-control rounded-3" id="destinationCopyFile{{ $loop->iteration }}" name="destination" placeholder="ex: folder/subfolder" value="{{ $cale }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Renunță
                        </button>
                        <button type="submit" class="btn btn-info text-white">
                            <i class="fas fa-copy"></i> Copiază
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endforeach

{{-- Move Fișier Modal --}}
@foreach ($fisiere as $fisier)
    @php
        $exploded = explode("/", $fisier);
        $fileName = end($exploded);
    @endphp
    <div class="modal fade text-dark" id="moveFisier{{ $loop->iteration }}" tabindex="-1" role="dialog" aria-labelledby="moveFisierLabel{{ $loop->iteration }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="POST" action="/file-manager-personalizat-fisier/move">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title text-white" id="moveFisierLabel{{ $loop->iteration }}">
                            <i class="fas fa-arrows-alt"></i> Mută fișier: <b>{{ $fileName }}</b>
                        </h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="source" value="{{ $fisier }}">
                        <!-- Current location -->
                        <div class="mb-3">
                            <label for="currentLocationMoveFile{{ $loop->iteration }}" class="form-label">Locația curentă</label>
                            <input type="text" class="form-control rounded-3" id="currentLocationMoveFile{{ $loop->iteration }}" name="currentLocation" value="{{ $cale }}" readonly>
                        </div>
                        <!-- Destination -->
                        <div class="mb-3">
                            <label for="destinationMoveFile{{ $loop->iteration }}" class="form-label">Destinație (calea directorului țintă)</label>
                            <input type="text" class="form-control rounded-3" id="destinationMoveFile{{ $loop->iteration }}" name="destination" placeholder="ex: folder/subfolder" value="{{ $cale }}">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times"></i> Renunță
                        </button>
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="fas fa-arrows-alt"></i> Mută
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endforeach

{{-- Sterge Fișier Modal --}}
@foreach ($fisiere as $fisier)
    @php $exploded = explode("/", $fisier); @endphp
    <div class="modal fade text-dark" id="stergeFisier{{ $loop->iteration }}" tabindex="-1" role="dialog" aria-labelledby="stergeFisierLabel{{ $loop->iteration }}" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="stergeFisierLabel{{ $loop->iteration }}">
                        <i class="fas fa-trash"></i> Fișier: <b>{{ end($exploded) }}</b>
                    </h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Ești sigur că vrei să ștergi fișierul?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Renunță
                    </button>
                    <form method="POST" action="/file-manager-personalizat-fisier/sterge/{{ $fisier }}">
                        @method('DELETE')
                        @csrf
                        <button type="submit" class="btn btn-danger text-white">
                            <i class="fas fa-trash"></i> Șterge Fișierul
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection
