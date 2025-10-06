@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card mx-2" style="border-radius: 30px;">
        <div class="card-header d-flex justify-content-between align-items-center" style="border-radius: 30px 30px 0 0;">
            <span class="badge bg-success fs-5">Adauga factura furnizor</span>
            <a href="{{ route('facturi-furnizori.facturi.index') }}" class="btn btn-outline-secondary btn-sm">Inapoi la lista</a>
        </div>
        <div class="card-body">
            @include('errors')

            <form action="{{ route('facturi-furnizori.facturi.store') }}" method="POST">
                @csrf
                @include('facturiFurnizori.facturi._form', ['factura' => null])

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('facturi-furnizori.facturi.index') }}" class="btn btn-link">Renunta</a>
                    <button type="submit" class="btn btn-success">Salveaza factura</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('facturiFurnizori.facturi._typeahead')
@endsection
