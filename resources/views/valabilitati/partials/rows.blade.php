@php($azi = now()->startOfDay())

@foreach ($valabilitati as $valabilitate)
    @php
        $dataInceput = $valabilitate->data_inceput;
        $dataSfarsit = $valabilitate->data_sfarsit;
        $isActive = is_null($dataSfarsit) || $dataSfarsit->greaterThanOrEqualTo($azi);
        $statusLabel = $isActive ? 'Activă' : 'Expirată';
        $statusClass = $isActive ? 'bg-success' : 'bg-secondary';
        $zileRamase = is_null($dataSfarsit)
            ? null
            : $azi->diffInDays($dataSfarsit, false);
    @endphp
    <tr>
        <td class="fw-semibold">{{ $valabilitate->denumire }}</td>
        <td class="text-nowrap">{{ $valabilitate->numar_auto }}</td>
        <td>{{ $valabilitate->sofer->name ?? '—' }}</td>
        <td class="text-nowrap">{{ optional($dataInceput)->format('d.m.Y') ?? '—' }}</td>
        <td class="text-nowrap">{{ optional($dataSfarsit)->format('d.m.Y') ?? '—' }}</td>
        <td>
            <span class="badge {{ $statusClass }} text-white">{{ $statusLabel }}</span>
        </td>
        <td class="text-end">
            @if (is_null($zileRamase))
                Nelimitat
            @elseif ($zileRamase >= 0)
                {{ $zileRamase }} zile
            @else
                Expirat de {{ abs($zileRamase) }} zile
            @endif
        </td>
    </tr>
@endforeach
