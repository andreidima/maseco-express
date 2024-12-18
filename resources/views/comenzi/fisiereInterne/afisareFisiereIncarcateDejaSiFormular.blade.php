@extends('layouts.app')

@php
    use \Carbon\Carbon;
@endphp

@section('content')
<div class="container">
    <div class="row my-4 justify-content-center">
        <div class="col-md-9 p-0">
            <div class="shadow-lg bg-white" style="border-radius: 40px 40px 40px 40px;">
                <div class="p-2 text-white culoare2" style="border-radius: 40px 40px 0px 0px;">
                    <div class="row d-flex align-items-center">
                        <div class="col-lg-12 d-flex justify-content-center" style="">
                            <h3 class="my-2 text-center">
                                Fișiere interne - Comanda {{ $comanda->transportator_contract }}
                            </h3>
                        </div>
                    </div>
                </div>

                <div class="card-body p-3 border border-0 border-dark" style="border-radius: 0px 0px 40px 40px">

                    <div class="row">

                        @include('errors')

                        <div class="col-lg-6 mx-auto p-2 mb-4 rounded-3 text-white" style="background-color:#7474b6;">
                            <form method="POST" action="/comenzi/{{$comanda->id}}/fisiere-interne" enctype="multipart/form-data">
                                @csrf

                                <label for="files" class="mb-0 ps-3">Adăugați fișiere<span class="text-danger">*</span></label>
                                <input type="file" name="fisiere[]" class="form-control mb-2 rounded-3" multiple>

                                <div class="text-center">
                                    <button class="btn btn-success text-white border border-dark rounded-3 shadow block" type="submit">
                                        Încarcă fișierele
                                    </button>
                                </div>
                            </form>
                        </div>

                        @if ($comanda->fisiereInterne->count() > 0)
                        <div class="col-lg-12 mb-4">
                            <div class="col-lg-12 mx-auto table-responsive rounded-3">
                                <table class="table table-striped table-hover rounded-3">
                                    <thead class="text-white rounded-3 culoare2">
                                        <tr>
                                            <th>#</th>
                                            <th>Nume</th>
                                            <th class="text-end">Acțiuni</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($comanda->fisiereInterne as $fisier)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    <a href="/comenzi/{{$comanda->id}}/fisiere-interne/deschide/{{ $fisier->nume }}" target="_blank" style="text-decoration:cornflowerblue">
                                                        {{-- <i class="fa-solid fa-file"></i> --}}
                                                        {{ $fisier->nume ?? '' }}
                                                    </a>
                                                </td>
                                                <td class="d-flex justify-content-end">
                                                    <div class="d-flex">
                                                        <a
                                                            href="#"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#stergeFisier{{ $loop->iteration }}"
                                                            title="Șterge Fișier"
                                                            >
                                                            <span class="badge bg-danger">Șterge</span>
                                                        </a>
                                                    </div>
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
                                <a href="{{ Session::get('ComandaReturnUrl') }}" class="btn btn-sm btn-secondary border border-dark rounded-3 shadow block" role="button">
                                    Revino înapoi la comenzi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modalele pentru stergere fisier --}}
@foreach ($comanda->fisiereInterne as $fisier)
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

                <form method="POST" action="/comenzi/{{$comanda->id}}/fisiere-interne/sterge/{{ $fisier->nume }}">
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
