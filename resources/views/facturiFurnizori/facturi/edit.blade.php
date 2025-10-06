@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card mx-2" style="border-radius: 30px;">
        <div class="card-header d-flex justify-content-between align-items-center" style="border-radius: 30px 30px 0 0;">
            <span class="badge bg-primary fs-5">Editeaza factura</span>
            <a href="{{ route('facturi-furnizori.facturi.index') }}" class="btn btn-outline-secondary btn-sm">Inapoi la lista</a>
        </div>
        <div class="card-body">
            @include('errors')

            <dl class="row">
                <dt class="col-sm-3">Status curent</dt>
                <dd class="col-sm-9"><span class="badge bg-secondary text-uppercase">{{ $factura->status }}</span></dd>
            </dl>

            <form action="{{ route('facturi-furnizori.facturi.update', $factura) }}" method="POST">
                @csrf
                @method('PUT')
                @include('facturiFurnizori.facturi._form', ['factura' => $factura])

                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div>
                        @if ($factura->calupuri->isNotEmpty())
                            <span class="text-muted">Face parte din calup:</span>
                            @foreach ($factura->calupuri as $calup)
                                <a href="{{ route('facturi-furnizori.plati-calupuri.show', $calup) }}" class="badge bg-info text-dark text-decoration-none">{{ $calup->denumire_calup }}</a>
                            @endforeach
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('facturi-furnizori.facturi.index') }}" class="btn btn-link">Renunta</a>
                        <button type="submit" class="btn btn-primary">Actualizeaza factura</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@include('facturiFurnizori.facturi._typeahead')
@endsection
