@extends ('layouts.app')

@php
    use \Carbon\Carbon;
@endphp

@section('content')
<div class="mx-3 px-3 card mx-auto" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-users me-1"></i>Utilizatori
                </span>
            </div>
            <div class="col-lg-6">
                <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    <div class="row mb-1 custom-search-form justify-content-center">
                        <div class="col-lg-8">
                            <input type="text" class="form-control rounded-3" id="searchNume" name="searchNume" placeholder="Nume" value="{{ $searchNume }}">
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
                <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8" href="{{ url()->current() }}/adauga" role="button">
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă utilizator
                </a>
            </div>
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors')

            <div class="table-responsive rounded-3">
                <table class="table table-striped table-hover">
                    <thead class="">
                        <tr class="" style="padding:2rem">
                            <th class="culoare2 text-white">#</th>
                            <th class="culoare2 text-white">Nume</th>
                            <th class="culoare2 text-white">Rol</th>
                            <th class="culoare2 text-white">Telefon</th>
                            <th class="culoare2 text-white">Email</th>
                            <th class="culoare2 text-white">Stare Cont</th>
                            <th class="culoare2 text-white text-end">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($useri as $user)
                            <tr>
                                <td align="">
                                    {{ ($useri ->currentpage()-1) * $useri ->perpage() + $loop->index + 1 }}
                                </td>
                                <td class="">
                                    {{ $user->name }}
                                </td>
                                <td class="">
                                    @switch($user->role)
                                        @case(1)
                                            Admin
                                            @break
                                        @case(2)
                                            Dispecer
                                            @break
                                    @endswitch
                                </td>
                                <td class="">
                                    {{ $user->telefon }}
                                </td>
                                <td class="">
                                    {{ $user->email }}
                                </td>
                                <td>
                                    @if ($user->activ == 0)
                                        <span class="text-danger">Închis</span>
                                    @else
                                        <span class="text-success">Deschis</span>
                                    @endif
                                </td>
                                <td class="">
                                    <div class="text-end">
                                        <a href="{{ $user->path() }}" class="flex me-1">
                                            <span class="badge bg-success">Vizualizează</span></a>
                                        <a href="{{ $user->path() }}/modifica" class="flex me-1">
                                            <span class="badge bg-primary">Modifică</span></a>
                                        <a href="#"
                                            data-bs-toggle="modal"
                                            data-bs-target="#stergeUser{{ $user->id }}"
                                            title="Șterge utilizator"
                                            >
                                            <span class="badge bg-danger">Șterge</span></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                        </tbody>
                </table>
            </div>

            <p class="ms-5">
                * Nu se recomandă ștergerea utilizatorilor ce au deja înregistrări în aplicație. Este suficientă închiderea conturilor.
            </p>

                <nav>
                    <ul class="pagination justify-content-center">
                        {{$useri->appends(Request::except('page'))->links()}}
                    </ul>
                </nav>
        </div>
    </div>

    {{-- Modalele pentru stergere user --}}
    @foreach ($useri as $user)
        <div class="modal fade text-dark" id="stergeUser{{ $user->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Utilizator: <b>{{ $user->name }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Ești sigur ca vrei să ștergi utilizatorul?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                    <form method="POST" action="{{ $user->path() }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Șterge utilizatorul
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection
