@extends ('layouts.app')

@php
    use Carbon\Carbon;
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
        <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
            <div class="col-lg-3">
                <span class="badge culoare1 fs-5">
                    <i class="fa-solid fa-file-invoice me-1"></i>Facturi scadente
                </span>
            </div>
            <div class="col-lg-6">
                <form class="needs-validation" novalidate method="GET" action="{{ url()->current()  }}">
                    @csrf
                    <div class="row mb-1 custom-search-form justify-content-center">
                        <div class="col-lg-4">
                            <input type="text" class="form-control rounded-3" id="searchTransportator" name="searchTransportator" placeholder="Transportator" value="{{ $searchTransportator }}">
                        </div>
                        <div class="col-lg-4">
                            <input type="text" class="form-control rounded-3" id="searchTransportatorContract" name="searchTransportatorContract" placeholder="Nr. comandă" value="{{ $searchTransportatorContract }}">
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
                            {{-- <th class="">Dată<br>creare</th> --}}
                            <th class="">Transportator</th>
                            <th class="">Comandă</th>
                            <th class="">Factură<br>transportator</th>
                            <th class="">Dată<br>factură</th>
                            <th class="">Dată<br>scadență</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($comenzi as $comanda)
                            <tr>
                                <td>
                                    {{ ($comenzi ->currentpage()-1) * $comenzi ->perpage() + $loop->index + 1 }}
                                </td>
                                {{-- <td class="">
                                    {{ $comanda->data_creare ? Carbon::parse($comanda->data_creare)->isoFormat('DD.MM.YYYY') : ''}}
                                </td> --}}
                                <td class="">
                                    {{ $comanda->transportator->nume ?? ''}}
                                </td>
                                <td class="">
                                    {{ $comanda->transportator_contract }}
                                </td>
                                <td class="">
                                    {{ $comanda->factura_transportator }}
                                </td>
                                <td class="">
                                    {{ $comanda->data_factura_transportator ? Carbon::parse($comanda->data_factura_transportator)->isoFormat('DD.MM.YYYY') : ''}}
                                </td>
                                <td class="">
                                    {{ $comanda->data_scadenta_plata_transportator ? Carbon::parse($comanda->data_scadenta_plata_transportator)->isoFormat('DD.MM.YYYY') : '' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    <div>Nu s-au găsit facturi scadente ...</div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                </table>
            </div>

                <nav>
                    <ul class="pagination justify-content-center">
                        {{ $comenzi->appends(Request::except('page'))->links() }}
                    </ul>
                </nav>
        </div>
    </div>
@endsection
