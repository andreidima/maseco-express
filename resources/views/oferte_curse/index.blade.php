@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0 0;">
        <div class="col-lg-3">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-truck me-1"></i> Oferte Curse
            </span>
        </div>
        <div class="col-lg-6">
            <form class="needs-validation" novalidate method="GET" action="{{ url()->current() }}">
                @csrf
                <div class="row mb-1 custom-search-form justify-content-center">
                    <div class="col-lg-3">
                        <input type="text" name="searchIncarcareCodPostal"    class="form-control rounded-3 text-nowrap" placeholder="Cod postal încărcare"      value="{{ $searchIncarcareCodPostal }}">
                    </div>
                    <div class="col-lg-3">
                        <input type="text" name="searchIncarcareLocalitate"   class="form-control rounded-3" placeholder="Localitate încărcare"   value="{{ $searchIncarcareLocalitate }}">
                    </div>
                    <div class="col-lg-3">
                        <input type="text" name="searchIncarcareDataOra"      class="form-control rounded-3" placeholder="Data & ora încărcare"   value="{{ $searchIncarcareDataOra }}">
                    </div>
                </div>
                <div class="row mb-1 custom-search-form justify-content-center">
                    <div class="col-lg-3">
                        <input type="text" name="searchDescarcareCodPostal"   class="form-control rounded-3 text-nowrap" placeholder="Cod postal descărcare"    value="{{ $searchDescarcareCodPostal }}">
                    </div>
                    <div class="col-lg-3">
                        <input type="text" name="searchDescarcareLocalitate"  class="form-control rounded-3" placeholder="Localitate descărcare" value="{{ $searchDescarcareLocalitate }}">
                    </div>
                    <div class="col-lg-3">
                        <input type="text" name="searchDescarcareDataOra"     class="form-control rounded-3" placeholder="Data & ora descărcare"  value="{{ $searchDescarcareDataOra }}">
                    </div>
                </div>
                <div class="row custom-search-form justify-content-center">
                    <div class="col-lg-4">
                        <button class="btn btn-sm w-100 btn-primary text-white border border-dark rounded-3" type="submit">
                            <i class="fas fa-search me-1"></i> Caută
                        </button>
                    </div>
                    <div class="col-lg-4">
                        <a class="btn btn-sm w-100 btn-secondary text-white border border-dark rounded-3" href="{{ url()->current() }}">
                            <i class="far fa-trash-alt me-1"></i> Resetează
                        </a>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-lg-3 text-end">
            <a class="btn btn-sm btn-success text-white border border-dark rounded-3 col-md-8" href="{{ route('oferte-curse.create') }}">
                <i class="fas fa-plus-square me-1"></i> Adaugă Ofertă
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">

        @include('errors')

        <div class="table-responsive rounded">
            <table class="table table-striped table-hover table-sm rounded">
                <thead class="culoare2">
                    <tr class="text-white">
                        {{-- <th rowspan="2">#</th> --}}
                        <th rowspan="2" class="">Data<br>ofertă</th>
                        <th colspan="3" class="text-center">Încărcare</th>
                        <th colspan="3" class="text-center">Descărcare</th>
                        <th rowspan="2">Greutate</th>
                        <th rowspan="2">Detaliile cursei</th>
                        <th rowspan="2" class="text-center">Gmail</th>
                        <th rowspan="2" class="text-end">Acțiuni</th>
                    </tr>
                    <tr class="text-white culoare2">
                        <th>Cod pos.</th>
                        <th>Localitate</th>
                        <th>Data & ora</th>
                        <th>Cod pos.</th>
                        <th>Localitate</th>
                        <th>Data & ora</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($oferte as $oferta)
                        <tr>
                            {{-- <td>{{ ($oferte->currentPage() - 1) * $oferte->perPage() + $loop->index + 1 }}</td> --}}
                            <td class="small">{{ \Carbon\Carbon::parse($oferta->data_primirii)->format('d.m.Y H:i') }}</td>
                            <td class="text-nowrap">{{ $oferta->incarcare_cod_postal }}</td>
                            <td>{{ $oferta->incarcare_localitate }}</td>
                            <td>{{ $oferta->incarcare_data_ora }}</td>
                            <td class="text-nowrap">{{ $oferta->descarcare_cod_postal }}</td>
                            <td>{{ $oferta->descarcare_localitate }}</td>
                            <td>{{ $oferta->descarcare_data_ora }}</td>
                            <td>{{ $oferta->greutate }}</td>
                            <td>{{ Str::limit($oferta->detalii_cursa, 150) }}</td>
                            <td class="text-center">
                                @if($oferta->gmail_link)
                                    <a href="{{ $oferta->gmail_link }}" target="_blank">
                                        <i class="fa-brands fa-google fs-5"></i>
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-end justify-content-end">
                                    <a href="{{ $oferta->path() }}" title="Vizualizează">
                                        <span class="badge px-1 bg-success me-1"><i class="fa-solid fa-eye text-white"></i></span>
                                    </a>
                                    <a href="{{ $oferta->path('edit') }}" title="Modifică">
                                        <span class="badge px-1 bg-primary me-1"><i class="fa-solid fa-edit text-white"></i></span>
                                    </a>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#stergeOferta{{ $oferta->id }}" title="Șterge">
                                        <span class="badge px-1 bg-danger"><i class="fa-solid fa-trash text-white"></i></span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted py-3">
                                <i class="fa-solid fa-exclamation-circle me-1"></i> Nu există oferte.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <nav>
            <ul class="pagination justify-content-center">
                {{ $oferte->appends(request()->except('page'))->links() }}
            </ul>
        </nav>
    </div>
</div>

{{-- Modalele pentru ștergere ofertă --}}
@foreach ($oferte as $oferta)
    <div class="modal fade text-dark" id="stergeOferta{{ $oferta->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Oferta: <b>{{ Str::limit($oferta->email_subiect, 30) }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-start">
                    Ești sigur că vrei să ștergi această ofertă?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                    <form method="POST" action="{{ $oferta->path('destroy') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger text-white">
                            Șterge Oferta
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@endsection
