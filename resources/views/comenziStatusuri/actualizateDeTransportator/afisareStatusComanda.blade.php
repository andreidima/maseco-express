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
                                    Am primit statusul dumneavoastră. Mulțumim!
                                @endif
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3 border border-0 border-dark" style="border-radius: 0px 0px 40px 40px">

                @include ('errors')

                @if (isset($comanda))
                    <div class="row align-items-center mb-4">
                        @foreach ($comanda->statusuri as $status)
                            <div class="col-lg-12 mb-4 culoare1">
                                Data: {{ \Carbon\Carbon::parse($status->created_at)->isoFormat('DD.MM.YYYY') }}
                                <br>
                                Ora: {{ \Carbon\Carbon::parse($status->created_at)->isoFormat('HH:mm') }}
                                <br>
                                Status: {{ $status->status }}
                            </div>
                        @endforeach
                    </div>
                    <div class="row align-items-center mb-4">
                        <div class="col-lg-12 mb-2 d-flex justify-content-center">
                            Dacă doriți, puteți oricând trimite un nou status
                        </div>
                        <div class="col-lg-12 mb-0 d-flex justify-content-center">
                            <a href="/cerere-status-comanda/{{ $comanda->cheie_unica }}" class="btn btn-lg bg-primary text-white border border-dark rounded-3 shadow block">
                                Trimitere status nou
                            </a>
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
