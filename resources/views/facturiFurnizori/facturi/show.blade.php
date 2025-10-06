@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card mx-2" style="border-radius: 30px;">
        <div class="card-header d-flex justify-content-between align-items-center" style="border-radius: 30px 30px 0 0;">
            <span class="badge bg-secondary fs-5">Detalii factura</span>
            <div class="d-flex gap-2">
                <a href="{{ route('facturi-furnizori.facturi.edit', $factura) }}" class="btn btn-outline-primary btn-sm">Editeaza</a>
                <a href="{{ route('facturi-furnizori.facturi.index') }}" class="btn btn-outline-secondary btn-sm">Inapoi</a>
            </div>
        </div>
        <div class="card-body">
            @include('errors')

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h5 class="mb-3">Informatii principale</h5>
                        <p class="mb-1"><strong>Furnizor:</strong> {{ $factura->denumire_furnizor }}</p>
                        <p class="mb-1"><strong>Numar factura:</strong> {{ $factura->numar_factura }}</p>
                        <p class="mb-1"><strong>Data factura:</strong> {{ $factura->data_factura?->format('d.m.Y') }}</p>
                        <p class="mb-1"><strong>Data scadenta:</strong> {{ $factura->data_scadenta?->format('d.m.Y') }}</p>
                        <p class="mb-1"><strong>Suma:</strong> {{ number_format($factura->suma, 2) }} {{ $factura->moneda }}</p>
                        <p class="mb-1"><strong>Departament / nr auto:</strong> {{ $factura->departament_vehicul ?: '-' }}</p>
                        <p class="mb-1"><strong>Status:</strong> <span class="badge bg-secondary text-uppercase">{{ $factura->status }}</span></p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h5 class="mb-3">Observatii</h5>
                        <p class="mb-0">{{ $factura->observatii ?: 'Nu exista observatii.' }}</p>
                    </div>
                </div>
            </div>

            <div class="border rounded p-3">
                <h5 class="mb-3">Calupuri asociate</h5>
                @if ($factura->calupuri->isEmpty())
                    <p class="text-muted mb-0">Factura nu este atasata niciunui calup.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Denumire</th>
                                    <th>Status</th>
                                    <th>Data plata</th>
                                    <th>Actiuni</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($factura->calupuri as $calup)
                                    <tr>
                                        <td>{{ $calup->denumire_calup }}</td>
                                        <td><span class="badge bg-secondary text-uppercase">{{ $calup->status }}</span></td>
                                        <td>{{ $calup->data_plata?->format('d.m.Y') ?: '-' }}</td>
                                        <td>
                                            <a href="{{ route('facturi-furnizori.plati-calupuri.show', $calup) }}" class="btn btn-outline-secondary btn-sm">Vezi calup</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
