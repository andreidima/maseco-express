@extends('layouts.app')

@php
    /** @var \App\Models\FacturiFurnizori\FacturaFurnizor|null $factura */
    $factura ??= null;
    $isEdit = $factura?->exists;
    $facturiIndexUrl = \App\Support\FacturiFurnizori\FacturiIndexFilterState::route();
    $fisiereExistente = collect($factura?->fisiere ?? []);
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
                        enctype="multipart/form-data"
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
                            'fisiereExistente' => $fisiereExistente,
                        ])
                    </form>

                    @if ($isEdit && $fisiereExistente->isNotEmpty())
                        <div class="border border-dark rounded-3 p-3 bg-white mt-4">
                            <span class="text-uppercase text-muted small d-block mb-2">Fișiere existente</span>
                            <ul class="list-group list-group-flush">
                                @foreach ($fisiereExistente as $fisier)
                                    <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                        <div class="me-2">
                                            <i class="fa-solid fa-file-pdf text-danger me-2"></i>
                                            {{ $fisier->nume_original ?: basename($fisier->cale) }}
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <a
                                                class="btn btn-sm btn-outline-primary"
                                                href="{{ route('facturi-furnizori.facturi.fisiere.vizualizeaza', [$factura, $fisier]) }}"
                                                target="_blank"
                                            >
                                                <i class="fa-solid fa-eye me-1"></i>Vezi
                                            </a>
                                            <a
                                                class="btn btn-sm btn-outline-secondary"
                                                href="{{ route('facturi-furnizori.facturi.fisiere.descarca', [$factura, $fisier]) }}"
                                            >
                                                <i class="fa-solid fa-download me-1"></i>Descarcă
                                            </a>
                                            <form
                                                action="{{ route('facturi-furnizori.facturi.fisiere.destroy', [$factura, $fisier]) }}"
                                                method="POST"
                                                class="d-inline"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fa-solid fa-trash me-1"></i>Șterge
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@include('facturiFurnizori.facturi._typeahead')
@endsection
