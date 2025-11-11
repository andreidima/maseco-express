@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <span class="badge bg-primary text-uppercase">
                        <i class="fa-solid fa-pen-to-square me-1"></i>
                        Modificare valabilitate
                    </span>
                </div>
                <div class="card-body">
                    @include('errors')

                    <form method="POST" action="{{ route('valabilitati.update', $valabilitate) }}">
                        @csrf
                        @method('PUT')
                        @include('valabilitati.form', [
                            'valabilitate' => $valabilitate,
                            'masini' => $masini,
                            'submitLabel' => 'Actualizează valabilitatea',
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
