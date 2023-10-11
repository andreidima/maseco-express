@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-bell me-1"></i>Mementouri
                </span>
            </div>
            <div class="col-lg-6">
                <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    <div class="row mb-1 custom-search-form justify-content-center">
                        <div class="col-lg-8">
                            <input type="text" class="form-control rounded-3" id="searchNume" name="searchNume" placeholder="Nume memento" value="{{ $searchNume }}">
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
                <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8" href="{{ url()->current() }}/adauga" role="button">
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă memento
                </a>
            </div>
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors')

            <div class="table-responsive rounded">
                <table class="table table-striped table-hover rounded">
                    <thead class="text-white rounded culoare2">
                        <tr class="" style="padding:2rem">
                            <th class="">#</th>
                            <th class="">Nume</th>
                            <th class="text-center">Dată expirare</th>
                            <th class="">Telefon/ Email</th>
                            <th class="text-center">Alerte</th>
                            <th class="">Descriere</th>
                            {{-- <th class="">Observații</th> --}}
                            <th class="text-end">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($mementouri as $memento)
                            <tr>
                                <td align="">
                                    {{ ($mementouri ->currentpage()-1) * $mementouri ->perpage() + $loop->index + 1 }}
                                </td>
                                <td class="">
                                    {{ $memento->nume }}
                                </td>
                                <td class="text-center">
                                    {{ $memento->data_expirare ? \Carbon\Carbon::parse($memento->data_expirare)->isoFormat('DD.MM.YYYY') : '' }}
                                </td>
                                <td class="">
                                    @if ($memento->telefon)
                                        {{ $memento->telefon }}
                                        <br>
                                    @endif
                                    {{ $memento->email }}
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center">
                                    @foreach ($memento->alerte as $alerta)
                                        {{ $alerta->data ? \Carbon\Carbon::parse($alerta->data)->isoFormat('DD.MM.YYYY') : '' }}
                                        {{-- @if (!$loop->last)
                                            /&nbsp;
                                        @endif --}}
                                        <br>
                                    @endforeach
                                    </div>
                                </td>
                                <td class="">
                                    {{ $memento->descriere }}
                                </td>
                                {{-- <td class="">
                                    {{ $memento->observatii }}
                                </td> --}}
                                <td>
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ $memento->path() }}" class="flex me-1">
                                            <span class="badge bg-success">Vizualizează</span>
                                        </a>
                                        <a href="{{ $memento->path() }}/modifica" class="flex me-1">
                                            <span class="badge bg-primary">Modifică</span>
                                        </a>
                                        <div style="flex" class="">
                                            <a
                                                href="#"
                                                data-bs-toggle="modal"
                                                data-bs-target="#stergeMemento{{ $memento->id }}"
                                                title="Șterge Memento"
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
                        {{$mementouri->appends(Request::except('page'))->links()}}
                    </ul>
                </nav>
        </div>
    </div>

    {{-- Modalele pentru stergere memento --}}
    @foreach ($mementouri as $memento)
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
    @endforeach

@endsection
