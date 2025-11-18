@php
    $azi = now()->startOfDay();
@endphp

@foreach ($valabilitati as $valabilitate)
    @php
        $dataInceput = $valabilitate->data_inceput;
        $dataSfarsit = $valabilitate->data_sfarsit;
        $isActive = is_null($dataSfarsit) || $dataSfarsit->greaterThanOrEqualTo($azi);
        $statusLabel = $isActive ? 'Activă' : 'Expirată';
        $statusClass = $isActive ? 'bg-success' : 'bg-secondary';
    @endphp
    <tr>
        <td class="fw-semibold">{{ $valabilitate->divizie->nume ?? '—' }}</td>
        <td class="text-nowrap">{{ $valabilitate->numar_auto }}</td>
        <td>{{ $valabilitate->sofer->name ?? '—' }}</td>
        <td class="text-nowrap">{{ optional($dataInceput)->format('d.m.Y') ?? '—' }}</td>
        <td class="text-nowrap">{{ optional($dataSfarsit)->format('d.m.Y') ?? '—' }}</td>
        <td>
            <span class="badge {{ $statusClass }} text-white">{{ $statusLabel }}</span>
        </td>
        <td class="text-end">
            <div class="d-flex flex-wrap justify-content-end">
                <div class="ms-1">
                    <a href="{{ route('valabilitati.curse.index', $valabilitate) }}" class="flex">
                        <span class="badge bg-info text-dark">Curse</span>
                    </a>
                </div>
                <div class="ms-1">
                    <a href="{{ route('valabilitati.show', $valabilitate) }}" class="flex">
                        <span class="badge bg-success">Vezi</span>
                    </a>
                </div>
                <div class="ms-1">
                    <a
                        href="{{ route('valabilitati.edit', $valabilitate) }}"
                        class="flex"
                        title="Modifică valabilitatea"
                    >
                        <span class="badge bg-primary">Modifică</span>
                    </a>
                </div>
                <div class="ms-1">
                    <a
                        href="#"
                        class="flex"
                        data-valabilitate-delete
                        data-delete-url="{{ route('valabilitati.destroy', $valabilitate) }}"
                        data-delete-divizie="{{ $valabilitate->divizie->nume ?? '' }}"
                        data-delete-numar-auto="{{ $valabilitate->numar_auto }}"
                        title="Șterge valabilitatea"
                    >
                        <span class="badge bg-danger">Șterge</span>
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
