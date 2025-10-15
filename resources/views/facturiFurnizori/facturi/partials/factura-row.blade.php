@php
    $checkboxDisabled = $factura->calupuri->isNotEmpty();
    $shouldCheck = in_array($factura->id, $selectedFacturiOld ?? [], true);
@endphp
<tr>
    <td class="text-center">
        <input
            type="checkbox"
            class="select-factura"
            value="{{ $factura->id }}"
            @disabled($checkboxDisabled)
            @checked(!$checkboxDisabled && $shouldCheck)
        >
    </td>
    <td>{{ $factura->denumire_furnizor }}</td>
    <td>{{ $factura->numar_factura }}</td>
    <td>{{ $factura->data_factura?->format('d.m.Y') }}</td>
    <td>{{ $factura->data_scadenta?->format('d.m.Y') }}</td>
    <td class="text-end">{{ number_format($factura->suma, 2) }}</td>
    <td>{{ $factura->moneda }}</td>
    <td>{{ $factura->departament_vehicul }}</td>
    <td>
        @if ($factura->calupuri->isNotEmpty())
            @foreach ($factura->calupuri as $calup)
                <a href="{{ route('facturi-furnizori.plati-calupuri.show', $calup) }}" class="badge bg-info text-white text-decoration-none mb-1">{{ $calup->denumire_calup }}</a>
            @endforeach
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td class="text-muted">{{ \Illuminate\Support\Str::limit($factura->observatii, 60) }}</td>
    <td class="text-end">
        <div class="text-end">
            @if ($factura->fisiere_count ?? 0)
                <a
                    href="{{ route('facturi-furnizori.facturi.show', $factura) }}#factura-fisiere"
                    class="text-decoration-none me-1"
                >
                    <span class="badge bg-secondary text-white">
                        {{ $factura->fisiere_count }} pdf
                    </span>
                </a>
            @endif
            <a href="{{ route('facturi-furnizori.facturi.show', $factura) }}" class="flex me-1">
                <span class="badge bg-success">Vezi</span></a>
            <a href="{{ route('facturi-furnizori.facturi.edit', $factura) }}" class="flex me-1">
                <span class="badge bg-primary">Editează</span></a>
            <a href="#" class="flex"
                data-bs-toggle="modal"
                data-bs-target="#stergeFactura{{ $factura->id }}">
                <span class="badge bg-danger">Șterge</span>
            </a>
        </div>
    </td>
</tr>
