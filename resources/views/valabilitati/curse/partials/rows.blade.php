@php
    $previousKmSosire = null;
    $currentGroupKey = '__none__';
@endphp

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

        // Km plin
        $kmPlin = $kmPlecare !== null && $kmSosire !== null ? $kmSosire - $kmPlecare : null;
        $kmGol = $previousKmSosire !== null && $kmPlecare !== null ? $kmPlecare - $previousKmSosire : null;
        $kmDifference = $kmPlin !== null && $kmMapsValue !== null ? $kmPlin - $kmMapsValue : null;

        $diffClass = $kmDifference === null
            ? ''
            : ($kmDifference < 0 ? 'text-danger' : ($kmDifference > 0 ? 'text-success' : ''));

        $canMoveUp = ! $loop->first;
        $canMoveDown = ! $loop->last;
        $hasMultipleCurse = $loop->count > 1;

        $group = $cursa->cursaGrup;
        $groupKey = $group?->id ?? 'ungrouped';
        $groupColor = $group->culoare_hex ?? '#f8f9fa';
        $groupName = $group->nume ?? 'Fără grup';
        $groupFormat = $group?->formatDocumenteLabel() ?? '—';
        $groupInvoice = $group?->numar_factura ?? '—';
        if ($groupInvoice !== '—' && $group?->data_factura) {
            $groupInvoice .= ' / ' . optional($group->data_factura)->format('d.m.Y');
        } elseif ($groupInvoice === '—' && $group?->data_factura) {
            $groupInvoice = optional($group->data_factura)->format('d.m.Y');
        }
        $isNewGroup = $groupKey !== $currentGroupKey;
        if ($isNewGroup) {
            $currentGroupKey = $groupKey;
        }
        $groupSumDisplay = $isNewGroup && $group && $group->suma_incasata !== null
            ? number_format((float) $group->suma_incasata, 2)
            : '—';
    @endphp

    @if ($isNewGroup)
        <tr class="curse-group-heading" style="background-color: {{ $groupColor }}; color: #111;">
            <th colspan="12">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <span class="fw-semibold">{{ $groupName }}</span>
                    <span class="curse-group-heading__meta">
                        Format: {{ $groupFormat }} | Factură: {{ $groupInvoice }}
                    </span>
                </div>
            </th>
        </tr>
    @endif

    <tr class="curse-group-row" style="background-color: {{ $group ? $groupColor : 'transparent' }}; color: #111;">
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

        {{-- Cursa (descriere) --}}
        <td class="small align-middle">
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

        {{-- KM Bord 2 – Km gol --}}
        <td class="text-end text-nowrap align-middle">
            {{ $kmGol !== null ? $kmGol : '—' }}
        </td>

        {{-- KM Bord 2 – Km plin --}}
        <td class="text-end text-nowrap align-middle">
            {{ $kmPlin !== null ? $kmPlin : '—' }}
        </td>

        {{-- Sumă încasată --}}
        <td class="text-end align-middle" style="background-color: {{ $group ? $groupColor : 'transparent' }};">
            {{ $groupSumDisplay }}
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

    @php
        $previousKmSosire = $kmSosire;
    @endphp
@endforeach
