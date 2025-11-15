@foreach ($curse as $cursa)
    @php
        $dataCursa = $cursa->data_cursa?->format('d.m.Y H:i');
    @endphp
    <tr>
        <td class="text-center fw-semibold">{{ $cursa->nr_ordine }}</td>
        <td class="text-nowrap">{{ $cursa->nr_cursa ?: '—' }}</td>
        <td>{{ $cursa->incarcare_localitate ?: '—' }}</td>
        <td>{{ $cursa->incarcare_cod_postal ?: '—' }}</td>
        <td>{{ $cursa->incarcareTara?->nume ?: '—' }}</td>
        <td>{{ $cursa->descarcare_localitate ?: '—' }}</td>
        <td>{{ $cursa->descarcare_cod_postal ?: '—' }}</td>
        <td>{{ $cursa->descarcareTara?->nume ?: '—' }}</td>
        <td class="text-nowrap">{{ $dataCursa ?: '—' }}</td>
        <td class="text-nowrap">{{ $cursa->km_bord_incarcare !== null ? $cursa->km_bord_incarcare : '—' }}</td>
        <td class="text-nowrap">{{ $cursa->km_bord_descarcare !== null ? $cursa->km_bord_descarcare : '—' }}</td>
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
