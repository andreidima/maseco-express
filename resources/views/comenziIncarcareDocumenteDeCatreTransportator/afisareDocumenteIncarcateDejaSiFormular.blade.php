@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row my-4 justify-content-center">
        <div class="col-md-9 p-0">
            <div class="shadow-lg bg-white" style="border-radius: 40px 40px 40px 40px;">
                <div class="p-2 text-white culoare2" style="border-radius: 40px 40px 0px 0px;">
                    <div class="row d-flex align-items-center">
                        @auth
                            <div class="col-lg-12 d-flex justify-content-center" style="">
                                <h3 class="my-2 text-center">
                                    Documentele comenzii {{ $comanda->transportator_contract }}
                                </h3>
                            </div>
                        @else
                            <div class="col-lg-12 mb-0 py-3 d-flex justify-content-center">
                                <img src="{{url('/images/logo.jpg')}}" alt="Logo PDF" height="200px" class="bg-white rounded-3 px-1">
                            </div>
                            <div class="col-lg-12 d-flex justify-content-center" style="">
                                <h3 class="my-2 text-center">
                                    @if (isset($comanda))
                                        Vă rugăm să încărcați documentele necesare comenzii {{ $comanda->transportator_contract }}
                                    @endif
                                </h3>
                            </div>
                        @endauth
                    </div>
                </div>

                <div class="card-body p-3 border border-0 border-dark" style="border-radius: 0px 0px 40px 40px">

                @if (isset($comanda))
                    <div class="row">
                        <div class="col-lg-6 mb-5">
                            Transportator: <b>{{ $comanda->transportator->nume ?? ''}}</b>
                        <br><br>
                            Camion: {{ $comanda->camion->numar_inmatriculare ?? '' }}
                        <br><br>
                            Comanda: <b>{{ $comanda->transportator_contract }}</b>
                        </div>
                        <div class="col-lg-6 mb-5">
                            Încărcări: <br>
                            @foreach ($comanda->locuriOperareIncarcari as $locOperareIncarcare)
                                <p class="mb-0" style="display: inline-block">
                                    {{ $locOperareIncarcare->nume }}: {{ $locOperareIncarcare->pivot->data_ora ? \Carbon\Carbon::parse($locOperareIncarcare->pivot->data_ora)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                </p>
                            @endforeach
                        <br><br>
                            Descărcări: <br>
                            @foreach ($comanda->locuriOperareDescarcari as $locOperareDescarcare)
                                <p class="mb-0" style="display: inline-block">
                                    {{ $locOperareDescarcare->nume }}: {{ $locOperareDescarcare->pivot->data_ora ? \Carbon\Carbon::parse($locOperareDescarcare->pivot->data_ora)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                </p>
                            @endforeach
                        </div>

                        @include('errors')

                        @guest
                            @if ($comanda->transportator_blocare_incarcare_documente == "0")
                                {{-- <div class="col-lg-12 mb-4 rounded-3" style="background-color:#112233"> --}}
                                <div class="col-lg-6 mx-auto p-2 my-2 rounded-3 text-white" style="background-color:#7474b6;">
                                    <form method="POST" action="/comanda-incarcare-documente-de-catre-transportator/{{$comanda->cheie_unica}}" enctype="multipart/form-data">
                                        @csrf

                                        <label for="files" class="mb-0 ps-3">Adăugați documentele necesare (doar în format PDF)<span class="text-danger">*</span></label>
                                        <input type="file" name="fisiere[]" class="form-control mb-2 rounded-3" multiple>

                                        <div class="text-center">
                                            <button class="btn btn-success text-white border border-dark rounded-3 shadow block" type="submit">
                                                Încarcă fișierele
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @else
                                Adminstratorul aplicației a blocat posibilitatea de a încărca noi documente.
                            @endif
                        @endguest

                        <div class="col-lg-12 mb-4">
                            {{-- @guest --}}
                                Numărul fișierelor încărcate: <b>{{ $comanda->fisiereIncarcateDeTransportator->count() }}</b>.
                                <br>
                                Din momentul în care documentele sunt vizualizate si validate de către administratorii aplicației, acestea nu mai pot fi șterse.
                            {{-- @endguest --}}
                            @if ($comanda->fisiereIncarcateDeTransportator->count() > 0)
                            <div class="col-lg-12 mx-auto table-responsive rounded-3">
                                <table class="table table-striped table-hover rounded-3">
                                    <thead class="text-white rounded-3 culoare2">
                                        <tr>
                                            <th>#</th>
                                            <th>Nume</th>
                                            <th>Stare</th>
                                            <th>Acțiuni</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($comanda->fisiereIncarcateDeTransportator as $fisier)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <a href="/comanda-incarcare-documente-de-catre-transportator/{{$comanda->cheie_unica}}/deschide/{{ $fisier->nume }}" target="_blank" style="text-decoration:cornflowerblue">
                                                        {{-- <i class="fa-solid fa-file"></i> --}}
                                                        {{ $fisier->nume ?? '' }}
                                                    </a>
                                                </td>
                                                <td>
                                                    @if ($fisier->validat == "1")
                                                        Validat
                                                    @else
                                                        În proces de validare
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex">
                                                        @auth
                                                            <a href="/comanda-incarcare-documente-de-catre-transportator/{{$comanda->cheie_unica}}/valideaza-invalideaza/{{ $fisier->nume }}" class="flex">
                                                                @if ($fisier->validat != "1")
                                                                    <span class="badge bg-primary me-1">Validează</span>
                                                                @else
                                                                    <span class="badge bg-warning text-dark me-1">Invalidează</span>
                                                                @endif
                                                            </a>
                                                        @endauth
                                                        @if (($fisier->validat != "1") && ($comanda->transportator_blocare_incarcare_documente == "0"))
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
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>

                        @auth
                            <div class="col-lg-12 mb-4">
                                 @if ($comanda->transportator_blocare_incarcare_documente == "1")
                                    În acest moment transportatorul NU ARE acces la a încărca noi documente.
                                    <a href="/comanda-incarcare-documente-de-catre-transportator/{{$comanda->cheie_unica}}/blocare-deblocare-incarcare-documente" class="flex">
                                        <span class="badge bg-primary me-1">Redă accesul</span>
                                    </a>
                                @else
                                    În acest moment transportatorul ARE acces la a încărca noi documente.
                                    <a href="/comanda-incarcare-documente-de-catre-transportator/{{$comanda->cheie_unica}}/blocare-deblocare-incarcare-documente" class="flex">
                                        <span class="badge bg-primary me-1">Oprește accesul</span>
                                    </a>
                                @endif
                            </div>
                            <div class="col-lg-12 mb-4">
                                <div class="text-center">
                                    <a href="{{ url('/comenzi') }}" class="btn btn-secondary border border-dark rounded-3 shadow block" role="button">
                                        Revino înapoi la comenzi
                                    </a>
                                </div>
                            </div>
                        @endauth
                    </div>
                @else
                    <div class="row">
                        <div class="col-lg-12 py-2 mx-auto">
                            <h5 class="ps-3 py-2 mb-0 text-center bg bg-danger text-white">
                                În acest moment nu există nici o comandă activă cu acest cod!
                            </h5>
                        </div>
                    </div>
                @endif


                    {{-- <div class="row">
                        <div class="col-lg-7 py-2 mx-auto">
                            <div class="row justify-content-center">
                                <div class="col-lg-12 d-flex justify-content-center">
                                    <a class="" href="https://www.maseco-express.eu/">Închide și mergi la site-ul principal</a>
                                </div>
                            </div>
                        </div>
                    </div> --}}




                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modalele pentru stergere fisier --}}
@foreach ($comanda->fisiereIncarcateDeTransportator as $fisier)
    <div class="modal fade text-dark" id="stergeFisier{{ $loop->iteration }}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title text-white" id="exampleModalLabel">Fișier: <b>{{ $fisier->nume }}</b></h5>
                <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="text-align:left;">
                Ești sigur ca vrei să ștergi Fișierul?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>

                <form method="POST" action="/comanda-incarcare-documente-de-catre-transportator/{{$comanda->cheie_unica}}/sterge/{{ $fisier->nume }}">
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
