@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 mb-5">
            <div class="card bg-warning">
                <div class="card-header">Eroare</div>

                <div class="card-body text-center">
                    419 | Sesiune expirată
                    <br>
                    <br>
                    <a class="btn btn-primary border border-dark rounded-3" href="{{ url('/') }}">
                        Revino la pagina principală
                    </a>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
