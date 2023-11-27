@extends ('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-file-invoice me-1"></i>Facturi
                </span>
            </div>
            <div class="col-lg-6">
                <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    <div class="row mb-1 custom-search-form justify-content-center">
                        <div class="col-lg-8">
                            <input type="text" class="form-control rounded-3" id="searchComandaNumar" name="searchComandaNumar" placeholder="Comanda:serie-numar" value="{{ $searchComandaNumar }}">
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
                    <i class="fas fa-plus-square text-white me-1"></i>Adaugă factură
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
                            <th class="">Factura</th>
                            <th class="">Client</th>
                            <th class="">Valoare</th>
                            <th class="text-center">Data</th>
                            <th class="text-end">Acțiuni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($facturi as $factura)
                            <tr>
                                <td align="">
                                    {{ ($facturi ->currentpage()-1) * $facturi ->perpage() + $loop->index + 1 }}
                                </td>
                                <td class="">
                                    {{ $factura->seria }} nr. {{ $factura->numar }}
                                </td>
                                <td class="">
                                    {{ $factura->client_nume }}
                                </td>
                                <td class="">
                                    {{ $factura->total_plata_moneda }} {{ $factura->moneda }}
                                </td>
                                <td class="text-center">
                                    {{ $factura->data ? \Carbon\Carbon::parse($factura->data)->isoFormat('DD.MM.YYYY') : '' }}
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end">
                                        @if ($factura->id === App\Models\Factura::where('seria', $factura->seria)->latest()->first()->id)
                                            <div style="flex" class="ms-1">
                                                <a
                                                    href="#"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#stergeFactura{{ $factura->id }}"
                                                    title="Șterge Factura"
                                                    >
                                                    <span class="badge bg-danger">Șterge</span>
                                                </a>
                                            </div>
                                        @endif
                                        <div style="" class="ms-1">
                                            @if($factura->anulata === 1)
                                                Anulată
                                            @elseif($factura->anulare_factura_id_originala !== null)
                                                Storno
                                            @else
                                                <a
                                                    href="#"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#anuleazaFactura{{ $factura->id }}"
                                                    title="Anulează Factura"
                                                    >
                                                        <span class="badge bg-warning">
                                                            Anulează
                                                        </span>
                                                </a>
                                            @endif
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
                        {{$facturi->appends(Request::except('page'))->links()}}
                    </ul>
                </nav>
        </div>
    </div>

    {{-- Modalele pentru anulare factura --}}
    @foreach ($facturi as $factura)
        <div class="modal fade text-dark" id="anuleazaFactura{{ $factura->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Factura seria {{ $factura->seria }} nr. {{ $factura->numar }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="/facturi/{{ $factura->id }}/anuleaza">
                    @method('PATCH')
                    @csrf
                    <div class="modal-body" style="text-align:left;">
                        Ești sigur ca vrei să anulezi Factura?
                        <div class="row">
                            <div class="col-lg-12">
                                <label for="anulare_motiv" class="mb-0 pl-3">Motiv anulare:</label>
                                <textarea class="form-control {{ $errors->has('anulare_motiv') ? 'is-invalid' : '' }}"
                                    name="anulare_motiv"
                                    rows="2"
                                >{{ old('anulare_motiv', ($factura->anulare_motiv ?? '')) }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                            <button
                                type="submit"
                                class="btn btn-danger text-white"
                                >
                                Anulează Factura
                            </button>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- Modalele pentru stergere factura --}}
    @foreach ($facturi as $factura)
        <div class="modal fade text-dark" id="stergeFactura{{ $factura->id }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white" id="exampleModalLabel">Factura seria {{ $factura->seria }} nr. {{ $factura->numar }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Ești sigur ca vrei să ștergi Factura?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                    <form method="POST" action="{{ $factura->path() }}">
                        @method('DELETE')
                        @csrf
                        <button
                            type="submit"
                            class="btn btn-danger text-white"
                            >
                            Șterge Factura
                        </button>
                    </form>

                </div>
                </div>
            </div>
        </div>
    @endforeach

@endsection
