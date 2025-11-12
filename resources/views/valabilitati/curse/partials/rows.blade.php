@php
    $startIndex = $curse->firstItem() ?? 1;
@endphp

@foreach ($curse as $cursa)
    @php
        $rowNumber = $startIndex + $loop->index;
        $dataCursa = $cursa->data_cursa?->format('d.m.Y H:i');
    @endphp
    <tr>
        <td class="text-nowrap">{{ $rowNumber }}</td>
        <td>{{ $cursa->incarcare_localitate ?: '—' }}</td>
        <td>{{ $cursa->incarcare_cod_postal ?: '—' }}</td>
        <td>{{ $cursa->descarcare_localitate ?: '—' }}</td>
        <td>{{ $cursa->descarcare_cod_postal ?: '—' }}</td>
        <td class="text-nowrap">{{ $dataCursa ?: '—' }}</td>
        <td>{{ $cursa->observatii ?: '—' }}</td>
        <td class="text-end">
            <div class="d-flex flex-wrap justify-content-end">
                <div class="ms-1">
                    <a
                        href="#"
                        data-bs-toggle="modal"
                        data-bs-target="#cursaEditModal{{ $cursa->id }}"
                        class="flex"
                        title="Modifică cursa"
                    >
                        <span class="badge bg-primary">Modifică</span>
                    </a>
                </div>
                <div class="ms-1">
                    <a
                        href="#"
                        data-bs-toggle="modal"
                        data-bs-target="#cursaDeleteModal{{ $cursa->id }}"
                        class="flex"
                        title="Șterge cursa"
                    >
                        <span class="badge bg-danger">Șterge</span>
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
