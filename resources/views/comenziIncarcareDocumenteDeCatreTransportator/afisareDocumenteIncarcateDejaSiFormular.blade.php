@extends('layouts.app')

@php
    use \Carbon\Carbon;
    use App\Support\BrowserViewableFile;
@endphp

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
                                    {{ $locOperareIncarcare->nume }}: {{ $locOperareIncarcare->pivot->data_ora ? Carbon::parse($locOperareIncarcare->pivot->data_ora)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                </p>
                            @endforeach
                        <br><br>
                            Descărcări: <br>
                            @foreach ($comanda->locuriOperareDescarcari as $locOperareDescarcare)
                                <p class="mb-0" style="display: inline-block">
                                    {{ $locOperareDescarcare->nume }}: {{ $locOperareDescarcare->pivot->data_ora ? Carbon::parse($locOperareDescarcare->pivot->data_ora)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                </p>
                            @endforeach
                        </div>

                        @include('errors')
                    </div>

                    <div class="row">
                        @auth
                            @if ($comanda->transportator_format_documente != "2")
                                <div class="col-lg-6 p-2 my-2 rounded-3 text-white border border-white" style="background-color:#7474b6;">
                                    <form method="POST" action="/comanda-documente-transportator/{{$comanda->cheie_unica}}" enctype="multipart/form-data">
                                        @csrf

                                        <label for="files" class="mb-0 ps-3">Adăugați documentele necesare (doar în format PDF)<span class="text-danger">*</span></label>
                                        <input type="file" name="fisiere[]" class="form-control mb-2 rounded-3" multiple>

                                        <div class="text-center">
                                            <button class="btn btn-success text-white border border-dark rounded-3 shadow block" type="submit">
                                                Salvează fișierele
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                                <div class="col-lg-6 mx-auto p-2 my-2 rounded-3 text-white border border-white" style="background-color:#7474b6;">
                                    <form method="POST" action="/comanda-informatii-documente-transportator/{{$comanda->cheie_unica}}" enctype="multipart/form-data">
                                        @csrf

                                        <div class="row">
                                            <div class="col-lg-6 mb-2">
                                                <label for="documente_transport_incarcate" class="mb-0 ps-0 small">Documente transport încărcate:</label>
                                                <select name="documente_transport_incarcate" class="form-select bg-white rounded-3 {{ $errors->has('documente_transport_incarcate') ? 'is-invalid' : '' }}">
                                                    <option value="0" selected>NU</option>
                                                    <option value="1" {{ (intval(old('documente_transport_incarcate', $comanda->documente_transport_incarcate ?? '')) === 1) ? 'selected' : null }}>DA</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-6 mb-2">
                                                <label for="factura_transportator_incarcata" class="mb-0 ps-3 small">Factura încărcată:</label>
                                                <select name="factura_transportator_incarcata" class="form-select bg-white rounded-3 {{ $errors->has('factura_transportator_incarcata') ? 'is-invalid' : '' }}">
                                                    <option value="0" selected>NU</option>
                                                    <option value="1" {{ (intval(old('factura_transportator_incarcata', $comanda->factura_transportator_incarcata ?? '')) === 1) ? 'selected' : null }}>DA</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="text-center">
                                            <button class="btn btn-success text-white border border-dark rounded-3 shadow block" type="submit">
                                                Salvează statusuri
                                            </button>
                                        </div>
                                    </form>
                                </div>
                        @endauth
                    </div>

                    <div class="row">
                        @guest
                            @if ($comanda->transportator_blocare_incarcare_documente != "1")
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
                                                <td>
                                                    {{-- If the file is uploaded by operators is shouldn't require validation --}}
                                                    @if (!$fisier->user_id)
                                                        @if ($fisier->validat == "1")
                                                            Validat
                                                        @else
                                                            În proces de validare
                                                        @endif
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @if ($isViewable)
                                                            <a href="{{ $previewUrl }}" target="_blank" rel="noopener" title="Deschide fișierul">
                                                                <span class="badge bg-primary d-inline-flex align-items-center gap-1">
                                                                    <i class="fa-solid fa-up-right-from-square"></i>
                                                                    Deschide
                                                                </span>
                                                            </a>
                                                        @endif
                                                        <a href="{{ $downloadUrl }}" title="Descarcă fișierul">
                                                            <span class="badge bg-secondary d-inline-flex align-items-center gap-1">
                                                                <i class="fa-solid fa-download"></i>
                                                                Descarcă
                                                            </span>
                                                        </a>
                                                        @auth
                                                            {{-- If the file is uploaded by operators is shouldn't require validation --}}
                                                            @if (!$fisier->user_id)
                                                                <a href="/comanda-documente-transportator/{{$comanda->cheie_unica}}/valideaza-invalideaza/{{ $fisier->nume }}" class="flex">
                                                                    @if ($fisier->validat != "1")
                                                                        <span class="badge bg-primary me-1">Validează</span>
                                                                    @else
                                                                        <span class="badge bg-warning text-dark me-1">Invalidează</span>
                                                                    @endif
                                                                </a>
                                                            @endif
                                                        @endauth
                                                        {{-- @if (($fisier->validat != "1") && ($comanda->transportator_blocare_incarcare_documente != "1")) --}}
                                                        @if ($fisier->validat != "1")
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

                        @guest
                            <div class="col-lg-12 mb-4 text-center">
                                <br>
                                De fiecare dată când încarci documentele necesare, te rugăm sa ne trimiți și o notificare către Maseco.
                                <br>
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div id="disableButton1" class="mb-4">
                                            <form method="POST" action="/comanda-incarcare-documente-de-catre-transportator/{{$comanda->cheie_unica}}/trimitere-email-transportator-catre-maseco-documente-incarcate/documenteTransport">
                                                @csrf
                                                    <button class="btn btn-lg btn-primary py-0 mx-1 text-white rounded" type="submit" name="action"
                                                        v-on:click="disableButton = true" :hidden="disableButton ? true : false">
                                                        Notifică Maseco - Documentele de transport au fost încărcate
                                                    </button>
                                                    <span class="text-center"
                                                        :hidden="disableButton ? false : true"
                                                    >Se trimite notificarea</span>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div id="disableButton4" class="mb-4">
                                            <form method="POST" action="/comanda-incarcare-documente-de-catre-transportator/{{$comanda->cheie_unica}}/trimitere-email-transportator-catre-maseco-documente-incarcate/facturaTransport">
                                                @csrf
                                                    <button class="btn btn-lg btn-warning text-dark py-0 mx-1 rounded" type="submit" name="action"
                                                        v-on:click="disableButton = true" :hidden="disableButton ? true : false">
                                                        Notifică Maseco - Factură a fost încarcată
                                                    </button>
                                                    <span class="text-center"
                                                        :hidden="disableButton ? false : true"
                                                    >Se trimite notificarea</span>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endguest

                        @auth
                            @if ($comanda->transportator_format_documente == "2")
                                <div class="col-lg-12 mb-5">
                                    <br>
                                    @if ($comanda->transportator_blocare_incarcare_documente == "1")
                                        În acest moment transportatorul NU ARE acces la a încărca noi documente.
                                        <a href="/comanda-documente-transportator/{{$comanda->cheie_unica}}/blocare-deblocare-incarcare-documente" class="flex">
                                            <span class="badge bg-primary me-1">Redă accesul</span>
                                        </a>
                                    @else
                                        În acest moment transportatorul ARE acces la a încărca noi documente.
                                        <a href="/comanda-documente-transportator/{{$comanda->cheie_unica}}/blocare-deblocare-incarcare-documente" class="flex">
                                            <span class="badge bg-primary me-1">Oprește accesul</span>
                                        </a>
                                    @endif
                                </div>

                                <div class="col-lg-12 mb-5 d-flex">
                                        Notifică transportatorul privind starea documentelor:
                                        {{-- <ul>
                                            <li>
                                                <form method="POST" action="/comanda-incarcare-documente-de-catre-transportator/{{$comanda->cheie_unica}}/trimitere-email-catre-transportator-privind-documente-incarcate">
                                                    @csrf
                                                    <button class="btn btn-sm btn-success py-0 text-white border border-dark rounded-3 shadow block" type="submit" name="action" value="emailGoodDocuments">
                                                        Documentele sunt corecte
                                                    </button>
                                                    - se trimite automat un email către Transportator; i se va restricționa și accesul la adăugare/ modificare documente, putând în continuare doar sa le vizualizeze;
                                                </form>
                                            </li>
                                            <li>
                                                <a
                                                    href="#"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#emailDocumenteIncorecte"
                                                    title="Email Documente Incorecte"
                                                    >
                                                    <span class="badge bg-danger">Documentele sunt greșite</span></a>
                                                - va trebui să completezi motivul, iar apoi se trimite automat emailul către Transportator; va avea în continuare acces deplin la platformă, mai puțin la documentele pe care le-ați validat deja că sunt corecte;
                                            </li>
                                        </ul> --}}
                                        <form method="POST" action="/comanda-documente-transportator/{{$comanda->cheie_unica}}/trimitere-email-catre-transportator-privind-documente-incarcate" id="disableButton2">
                                            @csrf
                                                <button class="btn btn-sm btn-success py-0 mx-1 text-white rounded" type="submit" name="action" value="emailGoodDocuments"
                                                    v-on:click="disableButton = true" :hidden="disableButton ? true : false">
                                                    Documentele sunt corecte
                                                </button>
                                                <span class="text-center"
                                                    :hidden="disableButton ? false : true"
                                                >Se trimite emailul</span>
                                        </form>
                                        <a href="#"
                                            data-bs-toggle="modal"
                                            data-bs-target="#emailDocumenteIncorecte"
                                            title="Email Documente Incorecte"
                                            >
                                            <span class="badge bg-danger">Documentele sunt greșite</span></a>
                                </div>

                                <div class="col-lg-12 mb-4">
                                    <div class="col-lg-12 mx-auto table-responsive rounded-3">
                                        <table class="table table-striped table-hover rounded-3">
                                            <thead class="text-white rounded-3 culoare2">
                                                <tr>
                                                    <th colspan="3" class="text-center">
                                                        Corespondența de pînă acum cu transportatorul
                                                    </th>
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
                                                                    Transportatorul a notificat Maseco că a încărcat documentele.
                                                                    @break
                                                                @case(2)
                                                                    Maseco a notificat transportatorul că documentele sunt corecte și să trimită factura.
                                                                    @break
                                                                @case(3)
                                                                    Maseco a notificat transportatorul că documentele nu sunt corecte. Motiv:
                                                                    <br>
                                                                    {!! nl2br($email->mesaj) !!}
                                                                    @break
                                                                @case(4)
                                                                    Transportatorul a notificat Maseco că a încărcat factura.
                                                                    @break
                                                                @default
                                                            @endswitch
                                                        </td>
                                                        <td>
                                                            {{ $email->created_at ? Carbon::parse($email->created_at)->isoFormat('DD.MM.YYYY HH:mm') : '' }}
                                                        </td>
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

{{-- Modal for sending negative email - when the documents are not allright --}}
@auth
    <div class="modal fade text-dark" id="emailDocumenteIncorecte" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form method="POST" action="/comanda-documente-transportator/{{$comanda->cheie_unica}}/trimitere-email-catre-transportator-privind-documente-incarcate">
                @csrf
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title text-white" id="exampleModalLabel">Comanda: <b>{{ $comanda->transportator_contract }}</b></h5>
                        <button type="button" class="btn-close bg-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="text-align:left;">

                    <label for="mesaj" class="mb-0 ps-3">Motiv documente greșite</label>
                    <textarea class="form-control bg-white {{ $errors->has('mesaj') ? 'is-invalid' : '' }}"
                        name="mesaj" rows="5">{{ old('mesaj') }}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Renunță</button>
                        <div id="disableButton3">
                            <button type="submit" class="btn btn-primary text-white" name="action" value="emailBadDocuments"
                                v-on:click="disableButton = true" :hidden="disableButton ? true : false">
                                Trimite emailul
                            </button>
                            <span class="text-center"
                                :hidden="disableButton ? false : true"
                            >Se trimite emailul</span>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endauth

@endsection
