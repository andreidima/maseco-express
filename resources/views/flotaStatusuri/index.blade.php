@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-truck me-1"></i>Flotă statusuri
                </span>
            </div>
            <div class="col-lg-6">
            </div>
            <div class="col-lg-3 text-end">
                <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8" href="{{ url()->current() }}/adauga" role="button">
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă status
                </a>
            </div>
        </div>

        <div class="card-body px-0 py-3">

            @include ('errors')

            <div class="table-responsive rounded mb-5">
                <table class="table table-sm table-striped table-hover table-bordered border-dark rounded">
                    <thead class="text-white rounded culoare2">
                        <tr class="" style="padding:2rem">
                            <th class="">#</th>
                            <th class="text-center">Nr auto</th>
                            <th class="text-center">Dimenssions</th>
                            <th class="text-center">Type</th>
                            <th class="text-center">Out of EU</th>
                            <th class="text-center">Info</th>
                            <th class="text-center">Abilities</th>
                            <th class="text-center">Status of the shipment</th>
                            <th class="text-center">Comanda</th>
                            <th class="text-center">Info II</th>
                            <th class="text-center">Info III</th>
                            <th class="text-center">Special info</th>
                            <th class="text-center">E/KM</th>
                            <th class="text-end">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($flotaStatusuri as $status)
                            <tr>
                                <td align="">
                                    {{ ($flotaStatusuri ->currentpage()-1) * $flotaStatusuri ->perpage() + $loop->index + 1 }}
                                </td>
                                <td class="text-center" style="color: {{ $status->utilizator->culoare_text ?? '' }}; background-color: {{ $status->utilizator->culoare_background ?? '' }}">
                                    {{ $status->nr_auto }}
                                </td>
                                <td class="text-center">
                                    {{ $status->dimenssions }}
                                </td>
                                <td class="text-center">
                                    {{ $status->type }}
                                </td>
                                <td class="text-center">
                                    {{ $status->out_of_eu }}
                                </td>
                                <td class="text-center" style="background-color: {{ $status->info == '1' ? 'yellow' : ($status->info == '2' ? 'orange' : ($status->info == '3' ? 'red' : '')) }}">

                                </td>
                                <td class="text-center">
                                    {{ $status->abilities }}
                                </td>
                                <td class="text-center">
                                    {{ $status->status_of_the_shipment }}
                                </td>
                                <td class="text-center">
                                    {{ $status->comanda }}
                                </td>
                                <td class="text-center">
                                    {{ $status->info_ii }}
                                </td>
                                <td class="text-center">
                                    {{ $status->info_iii }}
                                </td>
                                <td class="text-center">
                                    {{ $status->special_info }}
                                </td>
                                <td class="text-center">
                                    {{ $status->e_km }}
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ $status->path() }}/modifica" class="flex me-1">
                                            <span class="badge bg-primary">Modifică</span>
                                        </a>
                                        <div style="flex" class="">
                                            <a
                                                href="#"
                                                data-bs-toggle="modal"
                                                data-bs-target="#stergeStatus{{ $status->id }}"
                                                title="Șterge Status"
                                                >
                                                <span class="badge bg-danger">Șterge</span>
                                            </a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                        @endforelse
                        </tbody>
                </table>
            </div>

                <div class="d-flex justify-content-center">
                    {{$flotaStatusuri->appends(Request::except('page'))->links()}}
                </div>

            <div class="row">
                <div class="col-lg-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge culoare1">Utilizatori</span>
                        <a class="btn btn-sm btn-success text-white border border-dark rounded-3" href="/flota-statusuri-utilizatori/adauga" role="button">
                            <i class="fas fa-plus-square text-white me-1"></i>Adauga
                        </a>
                    </div>
                    <div class="table-responsive rounded mb-3">
                        <table class="table table-sm table-striped table-hover table-bordered border-dark rounded">
                            <thead class="text-white rounded culoare2">
                                <tr class="" style="padding:2rem">
                                    <th class="text-center">Nume</th>
                                    <th class="text-center">Ordine</th>
                                    <th class="text-end">Actiuni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($utilizatori as $utilizator)
                                    <tr>
                                        <td class="text-center" style="color: {{ $utilizator->culoare_text ?? '' }}; background-color: {{ $utilizator->culoare_background ?? '' }}">
                                            {{ $utilizator->nume }}
                                        </td>
                                        <td class="text-center">
                                            {{ $utilizator->ordine_afisare }}
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end">
                                                <a href="{{ $utilizator->path() }}/modifica" class="flex me-1">
                                                    <span class="badge bg-primary">Modifica</span>
                                                </a>
                                                <div style="flex" class="">
                                                    <a
                                                        href="#"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#stergeUtilizator{{ $utilizator->id }}"
                                                        title="Sterge utilizator"
                                                        >
                                                        <span class="badge bg-danger">Sterge</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div>
                        <table>
                            <tr><td class="px-1 text-black text-center" style="background-color: yellow">In tranzit, <br>fara cursa dupa descarcare</td></tr>
                            <tr><td class="px-1 text-white text-center" style="background-color: orange">De grupat</td></tr>
                            <tr><td class="px-1 text-white text-center" style="background-color: red">Liber</td></tr>
                        </table>
                    </div>
                </div>
                <div class="col-lg-9">
                    <div class="table-responsive rounded text-center">
                        <table class="table table-sm table-striped table-hover table-bordered border-dark rounded">
                            <thead class="text-white rounded culoare2">
                                <tr class="" style="padding:2rem">
                                    <th class="text-center">MODALITATE DE PLATĂ</th>
                                    <th class="text-center">SPOT</th>
                                    <th class="text-center">TERMEN</th>
                                    <th class="text-center">INFO</th>
                                    <th class="text-center"></th>
                                    <th class="text-end">
                                        <a class="btn btn-sm btn-success text-white border border-dark rounded-3" href="/flota-statusuri-informatii/adauga" role="button">
                                            <i class="fas fa-plus-square text-white me-1"></i>Adaugă informație
                                        </a>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($flotaStatusuriInformatii as $informatie)
                                    <tr>
                                        <td>
                                            {{ $informatie->modalitate_de_plata }}
                                        </td>
                                        <td>
                                            {{ $informatie->spot }}
                                        </td>
                                        <td>
                                            {{ $informatie->termen }}
                                        </td>
                                        <td>
                                            {{ $informatie->info }}
                                        </td>
                                        <td>
                                            {{ $informatie->info_2 }}
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end">
                                                <a href="{{ $informatie->path() }}/modifica" class="flex me-1">
                                                    <span class="badge bg-primary">Modifică</span>
                                                </a>
                                                <div style="flex" class="">
                                                    <a
                                                        href="#"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#stergeInformatie{{ $informatie->id }}"
                                                        title="Șterge Informatie"
                                                        >
                                                        <span class="badge bg-danger">Șterge</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        </div>
    </div>

    {{-- Modalele pentru stergere status --}}
    @foreach ($flotaStatusuri as $status)
        <div class="modal fade text-dark" id="stergeStatus{{ $status->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Status: <b>{{ $status->nr_auto }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Ești sigur că vrei să ștergi Statusul?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                    <form method="POST" action="{{ $status->path() }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Șterge Statusul
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Modalele pentru stergere utilizator --}}
    @foreach ($utilizatori as $utilizator)
        <div class="modal fade text-dark" id="stergeUtilizator{{ $utilizator->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Utilizator: <b>{{ $utilizator->nume }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Esti sigur ca vrei sa stergi utilizatorul?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunta</button>

                    <form method="POST" action="{{ $utilizator->path() }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Sterge utilizatorul
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Modalele pentru stergere informatie --}}
    @foreach ($flotaStatusuriInformatii as $informatie)
        <div class="modal fade text-dark" id="stergeInformatie{{ $informatie->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Informație: <b>{{ $informatie->nr_auto }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Ești sigur că vrei să ștergi Informația?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                    <form method="POST" action="{{ $informatie->path() }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Șterge Informația
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection
