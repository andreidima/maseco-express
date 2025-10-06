@extends('layouts.app')

@php
    /** @var \App\Models\FacturiFurnizori\FacturaFurnizor|null $factura */
    $factura ??= null;
    $isEdit = $factura?->exists;
@endphp

@section('content')
<div class="mx-3 px-3 card" style="border-radius: 40px 40px 40px 40px;">
    <div class="row card-header align-items-center" style="border-radius: 40px 40px 0px 0px;">
        <div class="col-lg-6">
            <span class="badge culoare1 fs-5">
                <i class="fa-solid fa-file-invoice-dollar me-1"></i>
                {{ $isEdit ? 'Modifică factură furnizor' : 'Adaugă factură furnizor' }}
            </span>
        </div>
        <div class="col-lg-6 text-end">
            <a class="btn btn-sm btn-secondary text-white border border-dark rounded-3" href="{{ route('facturi-furnizori.facturi.index') }}">
                <i class="fa-solid fa-rotate-left me-1"></i>Înapoi la listă
            </a>
        </div>
    </div>

    <div class="card-body px-0 py-3">
        @include('errors')

        <div class="px-3">
            @if ($isEdit)
                <div class="row mb-3">
                    <div class="col-lg-6">
                        <div class="border border-dark rounded-3 p-3 h-100 bg-white">
                            <span class="text-uppercase text-muted small d-block">Status curent</span>
                            <span class="badge bg-secondary text-uppercase fs-6">{{ $factura->status }}</span>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        @if ($factura->calupuri->isNotEmpty())
                            <div class="border border-dark rounded-3 p-3 h-100 bg-white">
                                <span class="text-uppercase text-muted small d-block mb-1">Face parte din calup</span>
                                @foreach ($factura->calupuri as $calup)
                                    <a href="{{ route('facturi-furnizori.plati-calupuri.show', $calup) }}" class="badge bg-info text-dark text-decoration-none me-1 mb-1">{{ $calup->denumire_calup }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <form
                action="{{ $isEdit ? route('facturi-furnizori.facturi.update', $factura) : route('facturi-furnizori.facturi.store') }}"
                method="POST"
                class="border border-dark rounded-3 p-3 bg-white"
            >
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                @include('facturiFurnizori.facturi.form', ['factura' => $factura])

                <div class="d-flex flex-column flex-md-row justify-content-md-between align-items-md-center mt-3">
                    <div class="mb-3 mb-md-0">
                        <a class="btn btn-sm btn-secondary text-white border border-dark rounded-3" href="{{ route('facturi-furnizori.facturi.index') }}">
                            <i class="fa-solid fa-angles-left me-1"></i>Renunță
                        </a>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-sm {{ $isEdit ? 'btn-primary' : 'btn-success' }} text-white border border-dark rounded-3">
                            <i class="fa-solid fa-floppy-disk me-1"></i>{{ $isEdit ? 'Actualizează factura' : 'Salvează factura' }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@include('facturiFurnizori.facturi._typeahead')
@endsection
