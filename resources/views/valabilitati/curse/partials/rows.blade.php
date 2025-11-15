@foreach ($curse as $cursa)
    @php
        $dataTransport = $cursa->data_cursa?->format('d.m.Y H:i');

        $incarcareParts = array_filter([
            $cursa->incarcare_localitate,
            $cursa->incarcare_cod_postal,
            $cursa->incarcareTara?->nume,
        ]);
        $descarcareParts = array_filter([
            $cursa->descarcare_localitate,
            $cursa->descarcare_cod_postal,
            $cursa->descarcareTara?->nume,
        ]);
        $cursaDescriere = (count($incarcareParts) ? implode(', ', $incarcareParts) : '—') . ' → ' . (count($descarcareParts) ? implode(', ', $descarcareParts) : '—');

        $kmPlecare = $cursa->km_bord_incarcare !== null && $cursa->km_bord_incarcare !== ''
            ? (float) $cursa->km_bord_incarcare
            : null;
        $kmSosire = $cursa->km_bord_descarcare !== null && $cursa->km_bord_descarcare !== ''
            ? (float) $cursa->km_bord_descarcare
            : null;
        $kmMapsDisplay = filled($cursa->km_maps) ? $cursa->km_maps : '—';
        $kmMapsValue = is_numeric($cursa->km_maps) ? (float) $cursa->km_maps : null;
        $kmBord2 = $kmPlecare !== null && $kmSosire !== null ? $kmSosire - $kmPlecare : null;
        $kmDifference = $kmBord2 !== null && $kmMapsValue !== null ? $kmBord2 - $kmMapsValue : null;

        $canMoveUp = ! $loop->first;
        $canMoveDown = ! $loop->last;
        $hasMultipleCurse = $loop->count > 1;
    @endphp
    <tr>
        <td class="text-center fw-semibold">
            <div class="d-inline-flex align-items-center gap-2">
                <span>#{{ $cursa->nr_ordine }}</span>
                @if ($hasMultipleCurse)
                    <div class="d-flex flex-column gap-1">
                        <form
                            method="POST"
                            action="{{ route('valabilitati.curse.reorder', [$valabilitate, $cursa]) }}"
                            class="mb-0"
                        >
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="direction" value="up">
                            <button
                                type="submit"
                                class="btn btn-sm btn-outline-secondary p-1"
                                title="Mută cursa mai sus"
                                @disabled(! $canMoveUp)
                            >
                                <i class="fa-solid fa-arrow-up"></i>
                            </button>
                        </form>
                        <form
                            method="POST"
                            action="{{ route('valabilitati.curse.reorder', [$valabilitate, $cursa]) }}"
                            class="mb-0"
                        >
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="direction" value="down">
                            <button
                                type="submit"
                                class="btn btn-sm btn-outline-secondary p-1"
                                title="Mută cursa mai jos"
                                @disabled(! $canMoveDown)
                            >
                                <i class="fa-solid fa-arrow-down"></i>
                            </button>
                        </form>
                    </div>
                @endif
            </div>
        </td>
        <td class="text-nowrap">{{ $cursa->nr_cursa ?: '—' }}</td>
        <td class="text-muted small">{{ $cursaDescriere }}</td>
        <td class="text-nowrap">{{ $dataTransport ?: '—' }}</td>
        <td class="text-nowrap">{{ $kmMapsDisplay }}</td>
        <td>
            <div class="d-flex flex-column">
                <span class="text-nowrap">{{ $kmPlecare !== null ? $kmPlecare : '—' }}</span>
                <span class="text-nowrap">{{ $kmSosire !== null ? $kmSosire : '—' }}</span>
            </div>
        </td>
        <td class="text-nowrap">{{ $kmBord2 !== null ? $kmBord2 : '—' }}</td>
        <td>&nbsp;</td>
        <td class="text-nowrap">{{ $kmDifference !== null ? $kmDifference : '—' }}</td>
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
