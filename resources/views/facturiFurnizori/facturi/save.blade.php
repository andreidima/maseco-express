@extends('layouts.app')

@php
    /** @var \App\Models\FacturiFurnizori\FacturaFurnizor|null $factura */
    $factura ??= null;
    $isEdit = $factura?->exists;
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
                </div>

                @include('errors')

                <div class="card-body py-3 px-4 border border-secondary bg-white" style="border-radius: 0 0 40px 40px;">
                    @if ($isEdit)
                        <div class="row mb-3">
                            <div class="col-lg-6 mb-3 mb-lg-0">
                                <div class="border border-dark rounded-3 p-3 h-100 bg-white">
                                    <span class="text-uppercase text-muted small d-block">Status curent</span>
                                    <span class="badge bg-white border border-dark rounded-pill text-dark fw-normal">
                                        <small>{{ $statusOptions[$factura->status] ?? \Illuminate\Support\Str::title(str_replace('_', ' ', $factura->status)) }}</small>
                                    </span>
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
                            'cancelUrl' => route('facturi-furnizori.facturi.index'),
                        ])
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('facturiFurnizori.facturi._typeahead')
@endsection
