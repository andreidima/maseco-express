@extends('layouts.app')

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-calendar-check me-1"></i>Modifică valabilitate
            </span>
        </div>
        <div class="col-lg-6 text-lg-end mt-3 mt-lg-0">
            <a href="{{ $backUrl ?? route('valabilitati.index') }}" class="btn btn-sm btn-secondary text-white border border-dark rounded-3">
                <i class="fas fa-arrow-left text-white me-1"></i>Înapoi
            </a>
        </div>
    </div>

    <div class="card-body py-4">
        @include('errors')

        @include('valabilitati.partials.form', [
            'valabilitate' => $valabilitate,
            'action' => route('valabilitati.update', $valabilitate),
            'method' => 'PUT',
            'submitLabel' => 'Salvează modificările',
            'backUrl' => $backUrl ?? route('valabilitati.index'),
            'soferi' => $soferi,
            'divizii' => $divizii,
        ])
    </div>
</div>
@endsection
