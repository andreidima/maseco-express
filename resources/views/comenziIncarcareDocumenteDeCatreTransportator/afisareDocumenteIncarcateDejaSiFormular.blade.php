@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row my-4 justify-content-center">
        <div class="col-md-9 p-0">
            <div class="shadow-lg bg-white" style="border-radius: 40px 40px 40px 40px;">
                <div class="p-2 text-white culoare2" style="border-radius: 40px 40px 0px 0px;"
                >
                    <div class="row d-flex align-items-center">
                        <div class="col-lg-12 mb-0 py-3 d-flex justify-content-center">
                            <img src="{{url('/images/logo.jpg')}}" alt="Logo PDF" height="200px" class="bg-white rounded-3 px-1">
                        </div>
                        {{-- <div class="col-lg-6 mb-0 py-3 d-flex justify-content-center">
                            <h1>
                                MASECO EXPRES
                            </h1>
                        </div> --}}
                        <div class="col-lg-12 d-flex justify-content-center" style="">
                            <h3 class="my-2 text-center">
                                @if (isset($comanda))
                                    Vă rugăm să încărcați documentele necesare comenzii {{ $comanda->transportator_contract }}
                                @endif
                            </h3>
                        </div>
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
                        <div class="col-lg-12 mb-4">
                            Numărul fișierelor încărcate până acum: {{ $comanda->fisiereIncarcateDeTransportator->count() }}
                            @foreach ($comanda->fisiereIncarcateDeTransportator as $fisier)
                                <br>
                                {{ $fisier->nume ?? '' }}
                            @endforeach
                        </div>
                        <div class="col-lg-12 mb-4">
                            <form method="POST" action="/comanda-incarcare-documente-de-catre-transportator/{{$comanda->cheie_unica}}" enctype="multipart/form-data">
                                @csrf

                                @include('errors')

                                <label for="files" class="mb-0 ps-3">Adăugați documentele necesare (doar în format PDF)<span class="text-danger">*</span></label>
                                {{-- @if($errors->has('fisiere'))
                                    <br>
                                    <span class="help-block text-danger">{{ $errors->first('fisiere') }}</span>
                                    <br>
                                @endif --}}
                                <input type="file" name="fisiere[]" class="form-control mb-3 rounded-3" multiple>

                                <div class="text-center">
                                    <button class="btn btn-lg btn-success text-white border border-dark rounded-3 shadow block" type="submit">
                                        Încarcă fișierele
                                    </button>
                                </div>
                            </form>
                        </div>
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
@endsection
