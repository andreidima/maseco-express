@extends('layouts.app')

@php
    use \Carbon\Carbon;
    use App\Support\BrowserViewableFile;

@endphp

@section('content')
<div class="container">
    <div class="row my-4 justify-content-center">
        <div class="col-md-9 p-0">
            <div class="shadow-lg bg-white" style="border-radius: 40px;">
                <div class="p-2 text-white culoare2" style="border-radius: 40px 40px 0 0;">
                    <div class="row d-flex align-items-center">
                        @auth
                            <div class="col-lg-12 d-flex justify-content-center">
                                <h3 class="my-2 text-center">
                                    Documentele comenzii {{ $comanda->transportator_contract }}
                                </h3>
                            </div>
                        @else
                            <div class="col-lg-12 mb-0 py-3 d-flex justify-content-center">
                                <img src="{{ url('/images/logo.jpg') }}" alt="Logo PDF" height="200" class="bg-white rounded-3 px-1">
                            </div>
                            <div class="col-lg-12 d-flex justify-content-center">
                                <h3 class="my-2 text-center">
                                    Va rugam sa incarcati documentele necesare comenzii {{ $comanda->transportator_contract }}
                                </h3>
                            </div>
                        @endauth
                    </div>
                </div>

                <div class="card-body p-3 border border-0 border-dark" style="border-radius: 0 0 40px 40px;">
                    @if (isset($comanda))
                        <div class="row">
                            <div class="col-lg-6 mb-5">
                                Transportator: <b>{{ $comanda->transportator->nume ?? '' }}</b>
                                <br><br>
                                Camion: {{ $comanda->camion->numar_inmatriculare ?? '' }}
                                <br><br>
                                Comanda: <b>{{ $comanda->transportator_contract }}</b>
                            </div>
                            <div class="col-lg-6 mb-5">
                                Incarcari:
                                <br>
                                @foreach ($comanda->locuriOperareIncarcari as $locOperareIncarcare)
                                    <p class="mb-0" style="display: inline-block">
                                        {{ $locOperareIncarcare->nume }}:
                                        {{ $locOperareIncarcare->pivot->data_ora ? Carbon::parse($locOperareIncarcare->pivot->data_ora)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                    </p>
                                @endforeach
                                <br><br>
                                Descarcari:
                                <br>
                                @foreach ($comanda->locuriOperareDescarcari as $locOperareDescarcare)
                                    <p class="mb-0" style="display: inline-block">
                                        {{ $locOperareDescarcare->nume }}:
                                        {{ $locOperareDescarcare->pivot->data_ora ? Carbon::parse($locOperareDescarcare->pivot->data_ora)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                    </p>
                                @endforeach
                            </div>

                            @include('errors')
                        </div>

                        <div class="row">
                            @auth
                                @if ($comanda->transportator_format_documente != '2')
                                    <div class="col-lg-6 p-2 my-2 rounded-3 text-white border border-white" style="background-color:#7474b6;">
                                        <form method="POST" action="{{ url('/comanda-documente-transportator/' . $comanda->cheie_unica) }}" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="este_factura" value="0">
                                            <label for="files" class="mb-0 ps-3">
                                                Adaugati alte documente (doar in format PDF)
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="file" name="fisiere[]" class="form-control mb-2 rounded-3" multiple>
                                            <div class="text-center">
                                                <button class="btn btn-success text-white border border-dark rounded-3 shadow block" type="submit">
                                                    Salveaza alte documente
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="col-lg-6 p-2 my-2 rounded-3 text-white border border-white" style="background-color:#5a4a9c;">
                                        <form method="POST" action="{{ url('/comanda-documente-transportator/' . $comanda->cheie_unica) }}" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="este_factura" value="1">
                                            <label for="files" class="mb-0 ps-3">
                                                Adaugati facturile (doar in format PDF)
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="file" name="fisiere[]" class="form-control mb-2 rounded-3" multiple>
                                            <div class="text-center">
                                                <button class="btn btn-warning text-dark border border-dark rounded-3 shadow block" type="submit">
                                                    Salveaza facturile
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @endif

                                <div class="col-lg-6 mx-auto p-2 my-2 rounded-3 text-white border border-white" style="background-color:#7474b6;">
                                    <form method="POST" action="{{ url('/comanda-informatii-documente-transportator/' . $comanda->cheie_unica) }}">
                                        @csrf
                                        <div class="row">
                                            <div class="col-lg-6 mb-2">
                                                <label for="documente_transport_incarcate" class="mb-0 ps-0 small">Documente transport incarcate:</label>
                                                <select name="documente_transport_incarcate" class="form-select bg-white rounded-3 {{ $errors->has('documente_transport_incarcate') ? 'is-invalid' : '' }}">
                                                    <option value="0" selected>NU</option>
                                                    <option value="1" {{ intval(old('documente_transport_incarcate', $comanda->documente_transport_incarcate ?? '')) === 1 ? 'selected' : null }}>DA</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-6 mb-2">
                                                <label class="mb-0 ps-3 small">Factura incarcata:</label>
                                                <div class="form-control bg-light rounded-3 border-0">
                                                    <strong>{{ intval($comanda->factura_transportator_incarcata ?? 0) === 1 ? 'DA' : 'NU' }}</strong>
                                                    <div class="small text-muted mt-1">
                                                        Statusul se actualizeaza automat din fisierele incarcate in rubrica de facturi.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button class="btn btn-success text-white border border-dark rounded-3 shadow block" type="submit">
                                                Salveaza statusuri
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endauth
                        </div>

                        <div class="row">
                            @guest
                                @if ($comanda->transportator_blocare_incarcare_documente != '1')
                                    <div class="col-lg-6 p-2 my-2 rounded-3 text-white" style="background-color:#7474b6;">
                                        <form method="POST" action="{{ url('/comanda-incarcare-documente-de-catre-transportator/' . $comanda->cheie_unica) }}" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="este_factura" value="0">
                                            <label for="files" class="mb-0 ps-3">
                                                Adaugati alte documente (doar in format PDF)
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="file" name="fisiere[]" class="form-control mb-2 rounded-3" multiple>
                                            <div class="text-center">
                                                <button class="btn btn-success text-white border border-dark rounded-3 shadow block" type="submit">
                                                    Incarca alte documente
                                                </button>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="col-lg-6 p-2 my-2 rounded-3 text-white" style="background-color:#5a4a9c;">
                                        <form method="POST" action="{{ url('/comanda-incarcare-documente-de-catre-transportator/' . $comanda->cheie_unica) }}" enctype="multipart/form-data">
                                            @csrf
                                            <input type="hidden" name="este_factura" value="1">
                                            <label for="files" class="mb-0 ps-3">
                                                Adaugati facturile (doar in format PDF)
                                                <span class="text-danger">*</span>
                                            </label>
                                            <input type="file" name="fisiere[]" class="form-control mb-2 rounded-3" multiple>
                                            <div class="text-center">
                                                <button class="btn btn-warning text-dark border border-dark rounded-3 shadow block" type="submit">
                                                    Incarca facturile
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                @else
                                    Administratorul aplicatiei a blocat posibilitatea de a incarca noi documente.
                                @endif
                            @endguest

                            <div class="col-lg-12 mb-4">
                                Numarul fisierelor incarcate: <b>{{ $comanda->fisiereIncarcateDeTransportator->count() }}</b>.
                                <br>
                                Factura incarcata: <b>{{ intval($comanda->factura_transportator_incarcata ?? 0) === 1 ? 'DA' : 'NU' }}</b>.
                                <br>
                                Dupa validarea documentelor de catre administratori, fisierele nu mai pot fi sterse.

                                @if ($comanda->fisiereIncarcateDeTransportator->count() > 0)
                                    <div class="col-lg-12 mx-auto table-responsive rounded-3 mt-3">
                                        <table class="table table-striped table-hover rounded-3">
                                            <thead class="text-white rounded-3 culoare2">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nume</th>
                                                    <th>Tip</th>
                                                    <th>Stare</th>
                                                    <th>Actiuni</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($comanda->fisiereIncarcateDeTransportator as $fisier)
                                                    @php
                                                        $isViewable = BrowserViewableFile::isViewable($fisier->nume ?? '');
                                                        $previewUrl = url('/comanda-incarcare-documente-de-catre-transportator/' . $comanda->cheie_unica . '/deschide/' . $fisier->nume);
                                                        $downloadUrl = url('/comanda-incarcare-documente-de-catre-transportator/' . $comanda->cheie_unica . '/descarca/' . $fisier->nume);
                                                    @endphp
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td class="text-break">
                                                            {{ $fisier->nume ?? '' }}
                                                        </td>
                                                        <td class="text-center align-middle">
                                                            @if ($fisier->este_factura)
                                                                <span class="badge bg-warning text-dark">Factura</span>
                                                            @else
                                                                <span class="badge bg-secondary">Document</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if (!$fisier->user_id)
                                                                {{ $fisier->validat == '1' ? 'Validat' : 'In proces de validare' }}
                                                            @else
                                                                Incarcat intern
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                @if ($isViewable)
                                                                    <a href="{{ $previewUrl }}" target="_blank" rel="noopener" title="Deschide fisierul">
                                                                        <span class="badge bg-primary d-inline-flex align-items-center gap-1">
                                                                            <i class="fa-solid fa-up-right-from-square"></i>
                                                                            Deschide
                                                                        </span>
                                                                    </a>
                                                                @endif
                                                                <a href="{{ $downloadUrl }}" title="Descarca fisierul">
                                                                    <span class="badge bg-secondary d-inline-flex align-items-center gap-1">
                                                                        <i class="fa-solid fa-download"></i>
                                                                        Descarca
                                                                    </span>
                                                                </a>
                                                                @auth
                                                                    @if (!$fisier->user_id)
                                                                        <a href="{{ url('/comanda-documente-transportator/' . $comanda->cheie_unica . '/valideaza-invalideaza/' . $fisier->nume) }}" class="flex">
                                                                            @if ($fisier->validat != '1')
                                                                                <span class="badge bg-primary me-1">Valideaza</span>
                                                                            @else
                                                                                <span class="badge bg-warning text-dark me-1">Invalideaza</span>
                                                                            @endif
                                                                        </a>
                                                                    @endif
                                                                @endauth
                                                                @if ($fisier->validat != '1')
                                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#stergeFisier{{ $fisier->id }}" title="Sterge fisier">
                                                                        <span class="badge bg-danger">Sterge</span>
                                                                    </a>
                                                                @endif
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            @guest
                                <div class="col-lg-12 mb-4 text-center">
                                    <br>
                                    De fiecare data cand incarci documentele necesare, te rugam sa trimiti si o notificare catre Maseco.
                                    <br>
                                    <div class="row">
                                        <div class="col-lg-6">
                                            <div class="mb-4">
                                                <form method="POST" action="{{ url('/comanda-incarcare-documente-de-catre-transportator/' . $comanda->cheie_unica . '/trimitere-email-transportator-catre-maseco-documente-incarcate/documenteTransport') }}">
                                                    @csrf
                                                    <button class="btn btn-lg btn-primary py-0 mx-1 text-white rounded" type="submit" name="action">
                                                        Notifica Maseco - Documentele de transport au fost incarcate
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div class="mb-4">
                                                <form method="POST" action="{{ url('/comanda-incarcare-documente-de-catre-transportator/' . $comanda->cheie_unica . '/trimitere-email-transportator-catre-maseco-documente-incarcate/facturaTransport') }}">
                                                    @csrf
                                                    <button class="btn btn-lg btn-warning text-dark py-0 mx-1 rounded" type="submit" name="action">
                                                        Notifica Maseco - Factura a fost incarcata
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endguest

                            @auth
                                @if ($comanda->transportator_format_documente == '2')
                                    <div class="col-lg-12 mb-5">
                                        <br>
                                        @if ($comanda->transportator_blocare_incarcare_documente == '1')
                                            In acest moment transportatorul NU ARE acces la a incarca noi documente.
                                            <a href="{{ url('/comanda-documente-transportator/' . $comanda->cheie_unica . '/blocare-deblocare-incarcare-documente') }}" class="flex">
                                                <span class="badge bg-primary me-1">Reda accesul</span>
                                            </a>
                                        @else
                                            In acest moment transportatorul ARE acces la a incarca noi documente.
                                            <a href="{{ url('/comanda-documente-transportator/' . $comanda->cheie_unica . '/blocare-deblocare-incarcare-documente') }}" class="flex">
                                                <span class="badge bg-primary me-1">Opreste accesul</span>
                                            </a>
                                        @endif
                                    </div>

                                    <div class="col-lg-12 mb-5 d-flex flex-wrap align-items-center gap-2">
                                        <span>Notifica transportatorul privind starea documentelor:</span>
                                        <form method="POST" action="{{ url('/comanda-documente-transportator/' . $comanda->cheie_unica . '/trimitere-email-catre-transportator-privind-documente-incarcate') }}">
                                            @csrf
                                            <button class="btn btn-sm btn-success py-0 mx-1 text-white rounded" type="submit" name="action" value="emailGoodDocuments">
                                                Documentele sunt corecte
                                            </button>
                                        </form>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#emailDocumenteIncorecte" title="Email documente incorecte">
                                            <span class="badge bg-danger">Documentele sunt gresite</span>
                                        </a>
                                    </div>

                                    <div class="col-lg-12 mb-4">
                                        <div class="col-lg-12 mx-auto table-responsive rounded-3">
                                            <table class="table table-striped table-hover rounded-3">
                                                <thead class="text-white rounded-3 culoare2">
                                                    <tr>
                                                        <th colspan="3" class="text-center">Corespondenta de pana acum cu transportatorul</th>
                                                    </tr>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Email</th>
                                                        <th>Data</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($comanda->emailuriPentruFisiereIncarcateDeTransportator as $email)
                                                        <tr>
                                                            <td>{{ $loop->iteration }}</td>
                                                            <td>
                                                                @switch($email->tip)
                                                                    @case(1)
                                                                        Transportatorul a notificat Maseco ca a incarcat documentele.
                                                                        @break
                                                                    @case(2)
                                                                        Maseco a notificat transportatorul ca documentele sunt corecte si sa trimita factura.
                                                                        @break
                                                                    @case(3)
                                                                        Maseco a notificat transportatorul ca documentele nu sunt corecte. Motiv:
                                                                        <br>
                                                                        {!! nl2br($email->mesaj) !!}
                                                                        @break
                                                                    @case(4)
                                                                        Transportatorul a notificat Maseco ca a incarcat factura.
                                                                        @break
                                                                    @default
                                                                @endswitch
                                                            </td>
                                                            <td>{{ $email->created_at ? Carbon::parse($email->created_at)->isoFormat('DD.MM.YYYY HH:mm') : '' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endif

                                <div class="col-lg-12 mb-4">
                                    <div class="text-center">
                                        <a href="{{ Session::get('ComandaReturnUrl') ?? url('/comenzi') }}" class="btn btn-sm btn-secondary border border-dark rounded-3 shadow block" role="button">
                                            Revino inapoi la comenzi
                                        </a>
                                    </div>
                                </div>
                            @endauth
                        </div>
                    @else
                        <div class="row">
                            <div class="col-lg-12 py-2 mx-auto">
                                <h5 class="ps-3 py-2 mb-0 text-center bg bg-danger text-white">
                                    In acest moment nu exista nicio comanda activa cu acest cod.
                                </h5>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@foreach ($comanda->fisiereIncarcateDeTransportator as $fisier)
    <div class="modal fade text-dark" id="stergeFisier{{ $fisier->id }}" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">Fisier: <b>{{ $fisier->nume }}</b></h5>
                    <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="text-align:left;">
                    Esti sigur ca vrei sa stergi fisierul?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunta</button>
                    <form method="POST" action="{{ url('/comanda-incarcare-documente-de-catre-transportator/' . $comanda->cheie_unica . '/sterge/' . $fisier->nume) }}">
                        @method('DELETE')
                        @csrf
                        <button type="submit" class="btn btn-danger text-white">
                            Sterge fisierul
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endforeach

@auth
    <div class="modal fade text-dark" id="emailDocumenteIncorecte" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="{{ url('/comanda-documente-transportator/' . $comanda->cheie_unica . '/trimitere-email-catre-transportator-privind-documente-incarcate') }}">
                    @csrf
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white">Comanda: <b>{{ $comanda->transportator_contract }}</b></h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="text-align:left;">
                        <label for="mesaj" class="mb-0 ps-3">Motiv documente gresite</label>
                        <textarea class="form-control bg-white {{ $errors->has('mesaj') ? 'is-invalid' : '' }}" name="mesaj" rows="5">{{ old('mesaj') }}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunta</button>
                        <button type="submit" class="btn btn-primary text-white" name="action" value="emailBadDocuments">
                            Trimite emailul
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endauth
@endsection
