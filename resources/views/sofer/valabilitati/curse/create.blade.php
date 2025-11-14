@extends('layouts.app')

@section('content')
<div class="container py-4 py-md-5 sofer-valabilitati">
    <div class="mb-4">
        <a href="{{ route('sofer.valabilitati.show', $valabilitate) }}" class="btn btn-link text-decoration-none px-0">
            <i class="fa-solid fa-arrow-left-long me-1"></i>
            Înapoi la valabilitate
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4 p-lg-5">
            <h1 class="h4 fw-bold mb-4">Adaugă cursă</h1>

            @include('sofer.valabilitati.curse.partials.form', [
                'valabilitate' => $valabilitate,
                'tari' => $tari,
                'action' => route('sofer.valabilitati.curse.store', $valabilitate),
                'method' => 'POST',
                'submitLabel' => 'Salvează cursa',
                'requiresTime' => $requiresTime,
                'lockTime' => $lockTime,
                'romanianCountryIds' => $romanianCountryIds,
            ])
        </div>
    </div>
</div>
@endsection

@include('sofer.valabilitati.curse.partials.final-return-modal')
