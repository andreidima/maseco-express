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
        $cursaDescriere = (count($incarcareParts) ? implode(', ', $incarcareParts) : '—')
            . ' → '
            . (count($descarcareParts) ? implode(', ', $descarcareParts) : '—');

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

        $diffClass = $kmDifference === null
            ? ''
            : ($kmDifference < 0 ? 'text-danger' : ($kmDifference > 0 ? 'text-success' : ''));

        $canMoveUp = ! $loop->first;
        $canMoveDown = ! $loop->last;
        $hasMultipleCurse = $loop->count > 1;
    @endphp

    <tr>
        {{-- # + up/down controls --}}
        <td class="text-center fw-semibold">
            <div class="d-inline-flex align-items-center gap-2">
                <span>#{{ $cursa->nr_ordine }}</span>
                @if ($hasMultipleCurse)
                    <div class="d-flex gap-1">
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
                                class="btn btn-sm btn-outline-secondary p-0"
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
                                class="btn btn-sm btn-outline-secondary p-0"
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

        {{-- Nr. cursă --}}
        <td class="text-nowrap align-middle">
            {{ $cursa->nr_cursa ?: '—' }}
        </td>

        {{-- Format documente --}}
        <td class="text-center align-middle">
            @if ($cursa->format_documente === 'Per post')
                <i class="fa-solid fa-envelope text-danger" title="Documentele se trimit prin poștă"></i>
            @elseif ($cursa->format_documente === 'Digital')
                <i class="fa-solid fa-at text-success" title="Documentele se trimit digital"></i>
            @else
                —
            @endif
        </td>

        {{-- Cursa (descriere) --}}
        <td class="text-muted small align-middle">
            {{ $cursaDescriere }}
        </td>

        {{-- Dată transport --}}
        <td class="text-nowrap align-middle">
            {{ $dataTransport ?: '—' }}
        </td>

        {{-- KM Maps --}}
        <td class="text-end text-nowrap align-middle">
            {{ $kmMapsDisplay }}
        </td>

        {{-- KM Plecare --}}
        <td class="text-end text-nowrap align-middle">
            {{ $kmPlecare !== null ? $kmPlecare : '—' }}
        </td>

        {{-- KM Sosire --}}
        <td class="text-end text-nowrap align-middle">
            {{ $kmSosire !== null ? $kmSosire : '—' }}
        </td>

        {{-- KM Bord 2 --}}
        <td class="text-end text-nowrap align-middle">
            {{ $kmBord2 !== null ? $kmBord2 : '—' }}
        </td>

        {{-- Sumă încasată – încă nu există în model, rămâne golă --}}
        <td class="text-end align-middle">
            &nbsp;
        </td>

        {{-- Diferența KM (Bord – Maps) --}}
        <td class="text-end text-nowrap align-middle {{ $diffClass }}">
            {{ $kmDifference !== null ? $kmDifference : '—' }}
        </td>

        {{-- Acțiuni --}}
        <td class="text-end align-middle">
            <div class="d-flex flex-wrap justify-content-end">
                <div class="ms-1">
                    <a
                        href="#" data-bs-toggle="modal"
                        data-bs-target="#cursaEditModal{{ $cursa->id }}"
                        class="flex"
                        title="Modifică cursa"
                        aria-label="Modifică cursa"
                    >
                        <span class="badge bg-primary d-inline-flex align-items-center justify-content-center" aria-hidden="true">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </span>
                        <span class="visually-hidden">Modifică</span>
                    </a>
                </div>
                <div class="ms-1">
                    <a
                        href="#"
                        data-bs-toggle="modal"
                        data-bs-target="#cursaDeleteModal{{ $cursa->id }}"
                        class="flex"
                        title="Șterge cursa"
                        aria-label="Șterge cursa"
                    >
                        <span class="badge bg-danger d-inline-flex align-items-center justify-content-center" aria-hidden="true">
                            <i class="fa-solid fa-trash"></i>
                        </span>
                        <span class="visually-hidden">Șterge</span>
                    </a>
                </div>
            </div>
        </td>
    </tr>
@endforeach
