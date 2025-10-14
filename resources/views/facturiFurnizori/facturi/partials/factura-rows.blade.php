@foreach ($facturi as $factura)
    @include('facturiFurnizori.facturi.partials.factura-row', [
        'factura' => $factura,
        'selectedFacturiOld' => $selectedFacturiOld ?? [],
    ])
@endforeach
