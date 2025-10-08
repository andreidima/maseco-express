@extends('layouts.app')

@php
    /** @var \App\Models\FacturiFurnizori\FacturaFurnizor|null $factura */
    $factura ??= null;
    $isEdit = $factura?->exists;
    $facturiIndexUrl = \App\Support\FacturiFurnizori\FacturiIndexFilterState::route();
@endphp

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="shadow-lg" style="border-radius: 40px;">
                <div class="border border-secondary p-2 culoare2 d-flex justify-content-between align-items-center" style="border-radius: 40px 40px 0 0;">
                    <span class="badge text-light fs-5">
                        <i class="fa-solid fa-file-invoice-dollar me-1"></i>
                        {{ $isEdit ? 'Modifică factură furnizor' : 'Adaugă factură furnizor' }}
                    </span>
                    <a class="btn btn-sm btn-secondary text-white border border-dark rounded-3" href="{{ $facturiIndexUrl }}">
                        <i class="fa-solid fa-rotate-left me-1"></i>Înapoi la listă
                    </a>
                </div>

                @include('errors')

                <div class="card-body py-3 px-4 border border-secondary bg-white" style="border-radius: 0 0 40px 40px;">
                    @if ($isEdit && $factura->calupuri->isNotEmpty())
                        <div class="row mb-3">
                            <div class="col-lg-6">
                                <div class="border border-dark rounded-3 p-3 h-100 bg-white">
                                    <span class="text-uppercase text-muted small d-block mb-1">Face parte din calup</span>
                                    @foreach ($factura->calupuri as $calup)
                                        <a href="{{ route('facturi-furnizori.plati-calupuri.show', $calup) }}" class="badge bg-info text-dark text-decoration-none me-1 mb-1">{{ $calup->denumire_calup }}</a>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    <form
                        class="needs-validation"
                        novalidate
                        action="{{ $isEdit ? route('facturi-furnizori.facturi.update', $factura) : route('facturi-furnizori.facturi.store') }}"
                        method="POST"
                    >
                        @csrf
                        @if ($isEdit)
                            @method('PUT')
                        @endif

                        @include('facturiFurnizori.facturi.form', [
                            'factura' => $factura,
                            'buttonText' => $isEdit ? 'Actualizează factura' : 'Salvează factura',
                            'buttonClass' => $isEdit ? 'btn-primary' : 'btn-success',
                            'cancelUrl' => $facturiIndexUrl,
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('facturiFurnizori.facturi._typeahead')
@endsection
