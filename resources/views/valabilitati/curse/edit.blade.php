@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-truck-fast me-1"></i>Modifică cursă
            </span>
        </div>
        <div class="col-lg-6 text-lg-end mt-3 mt-lg-0">
            <a href="{{ route('valabilitati.show', $valabilitate) }}" class="btn btn-sm btn-secondary text-white border border-dark rounded-3">
                <i class="fas fa-arrow-left text-white me-1"></i>Înapoi
            </a>
        </div>
    </div>

    <div class="card-body py-4">
        @include('errors')

        <form method="POST" action="{{ route('valabilitati.curse.update', [$valabilitate, $cursa]) }}" class="needs-validation" novalidate>
            @csrf
            @method('PUT')

            @include('valabilitati.curse._form', ['cursa' => $cursa])

            <div class="d-flex justify-content-end mt-4">
                <a href="{{ route('valabilitati.show', $valabilitate) }}" class="btn btn-secondary me-2">Renunță</a>
                <button type="submit" class="btn btn-primary text-white border border-dark rounded-3">
                    <i class="fa-solid fa-floppy-disk me-1"></i>Salvează cursa
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
