@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="mb-3">
        <a href="{{ route('valabilitati.show', $valabilitate) }}" class="btn btn-link px-0">
            <i class="fa-solid fa-arrow-left me-1"></i> Înapoi la valabilitate
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-10 col-xl-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <span class="badge bg-primary text-uppercase">
                        <i class="fa-solid fa-route me-1"></i>
                        Modificare cursă
                    </span>
                </div>
                <div class="card-body">
                    @include('errors')

                    <form method="POST" action="{{ route('valabilitati.curse.update', [$valabilitate, $cursa]) }}">
                        @csrf
                        @method('PUT')
                        @include('valabilitati.curse._form', ['cursa' => $cursa])
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-save me-1"></i> Salvează cursa
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
