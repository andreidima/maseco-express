@php
    $isFlashDivision = optional($valabilitate->divizie)->id === 1;
    $tableColumnCount = $isFlashDivision ? 13 : 12;
    $previousKmSosire = null;
    $currentGroupKey = '__none__';
    $resolveRowTextColor = static function ($value): string {
        if (! is_string($value) || $value === '') {
            return '#111111';
        }

        $hex = strtoupper(ltrim($value, '#'));

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        if (strlen($hex) !== 6) {
            return '#111111';
        }

        $formatted = '#' . $hex;
        $r = $g = $b = 0;

        if (sscanf($formatted, '#%02X%02X%02X', $r, $g, $b) !== 3) {
            return '#111111';
        }

        $luminance = ($r * 299 + $g * 587 + $b * 114) / 1000;

        return $luminance > 150 ? '#111111' : '#ffffff';
    };
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
        $kmMapsGolValue = is_numeric($cursa->km_maps_gol) ? (float) $cursa->km_maps_gol : null;
        $kmMapsPlinValue = is_numeric($cursa->km_maps_plin) ? (float) $cursa->km_maps_plin : null;
        $kmFlashGolValue = is_numeric($cursa->km_flash_gol) ? (float) $cursa->km_flash_gol : null;
        $kmFlashPlinValue = is_numeric($cursa->km_flash_plin) ? (float) $cursa->km_flash_plin : null;
        $kmCuTaxaValue = is_numeric($cursa->km_cu_taxa) ? (float) $cursa->km_cu_taxa : null;

        // Km plin
        $kmPlin = $kmPlecare !== null && $kmSosire !== null ? $kmSosire - $kmPlecare : null;
        $kmGol = $previousKmSosire !== null && $kmPlecare !== null ? $kmPlecare - $previousKmSosire : null;
        $kmDifference = $kmPlin !== null && $kmMapsValue !== null ? $kmPlin - $kmMapsValue : null;

        $kmMapsFlashGolDiff = $kmMapsGolValue !== null && $kmFlashGolValue !== null
            ? $kmMapsGolValue - $kmFlashGolValue
            : null;
        $kmMapsFlashPlinDiff = $kmMapsPlinValue !== null && $kmFlashPlinValue !== null
            ? $kmMapsPlinValue - $kmFlashPlinValue
            : null;

        $diffClass = $kmDifference === null
            ? ''
            : ($kmDifference < 0 ? 'text-danger' : ($kmDifference > 0 ? 'text-success' : ''));
        $diffFlashGolClass = $kmMapsFlashGolDiff === null
            ? ''
            : ($kmMapsFlashGolDiff < 0 ? 'text-danger' : ($kmMapsFlashGolDiff > 0 ? 'text-success' : ''));
        $diffFlashPlinClass = $kmMapsFlashPlinDiff === null
            ? ''
            : ($kmMapsFlashPlinDiff < 0 ? 'text-danger' : ($kmMapsFlashPlinDiff > 0 ? 'text-success' : ''));

        $canMoveUp = ! $loop->first;
        $canMoveDown = ! $loop->last;
        $hasMultipleCurse = $loop->count > 1;

        $group = $cursa->cursaGrup;
        $groupKey = $group?->id ?? 'ungrouped';
        $groupColor = $group->culoare_hex ?? '#f8f9fa';
        $groupTextColor = $resolveRowTextColor($groupColor);
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
        $groupFinancialMeta = null;
        if ($isNewGroup && $group) {
            $incasata = $group->suma_incasata !== null ? number_format((float) $group->suma_incasata, 2) : null;
            $calculata = $group->suma_calculata !== null ? number_format((float) $group->suma_calculata, 2) : null;
            $diferenta = null;
            if ($group->suma_incasata !== null || $group->suma_calculata !== null) {
                $rawDiff = (float) ($group->suma_incasata ?? 0) - (float) ($group->suma_calculata ?? 0);
                $diferenta = number_format($rawDiff, 2);
            }
            $zileCalculate = is_numeric($group->zile_calculate) ? (int) $group->zile_calculate : null;
            $groupFinancialMeta = [
                'suma_incasata' => $incasata,
                'suma_calculata' => $calculata,
                'diferenta' => $diferenta,
                'zile_calculate' => $zileCalculate,
            ];
        }
    @endphp

    @if ($isNewGroup)
        <tr class="curse-group-heading curse-group-heading--emphasis" style="background-color: {{ $groupColor }}; color: {{ $groupTextColor }};">
            <th colspan="{{ $tableColumnCount }}">
                <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
                    <div class="fw-semibold fs-6 text-uppercase">{{ $groupName }}</div>
                    <div class="d-flex flex-wrap gap-3 small curse-group-heading__meta">
                        <span>Format: <strong>{{ $groupFormat }}</strong></span>
                        <span>Factură: <strong>{{ $groupInvoice }}</strong></span>
                        @if ($groupFinancialMeta)
                            <span>Sumă încasată: <strong>{{ $groupFinancialMeta['suma_incasata'] ?? '—' }}</strong></span>
                            <span>Sumă calculată: <strong>{{ $groupFinancialMeta['suma_calculata'] ?? '—' }}</strong></span>
                            <span>Diferență: <strong>{{ $groupFinancialMeta['diferenta'] ?? '—' }}</strong></span>
                            <span>Zile calculate: <strong>{{ $groupFinancialMeta['zile_calculate'] ?? '—' }}</strong></span>
                        @endif
                    </div>
                </div>
            </th>
        </tr>
    @endif

    @php
        $checkboxId = 'cursa-select-' . $cursa->id;
    @endphp
    <tr @class(['curse-group-row' => (bool) $group]) style="background-color: {{ $group ? $groupColor : 'transparent' }}; color: {{ $group ? $groupTextColor : '#111' }};">
        <td class="text-center align-middle">
            <div class="form-check mb-0">
                <input
                    type="checkbox"
                    class="form-check-input curse-row-checkbox"
                    id="{{ $checkboxId }}"
                    value="{{ $cursa->id }}"
                    data-cursa-id="{{ $cursa->id }}"
                >
                <label class="visually-hidden" for="{{ $checkboxId }}">
                    Selectează cursa #{{ $cursa->nr_ordine }}
                </label>
            </div>
        </td>
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

        @if ($isFlashDivision)
            {{-- KM Maps gol/plin --}}
            <td class="text-end text-nowrap align-middle">
                {{ $kmMapsGolValue !== null ? $kmMapsGolValue : '—' }}
            </td>
            <td class="text-end text-nowrap align-middle">
                {{ $kmMapsPlinValue !== null ? $kmMapsPlinValue : '—' }}
            </td>

            {{-- KM cu taxă --}}
            <td class="text-end text-nowrap align-middle">
                {{ $kmCuTaxaValue !== null ? $kmCuTaxaValue : '—' }}
            </td>

            {{-- KM Flash gol/plin --}}
            <td class="text-end text-nowrap align-middle">
                {{ $kmFlashGolValue !== null ? $kmFlashGolValue : '—' }}
            </td>
            <td class="text-end text-nowrap align-middle">
                {{ $kmFlashPlinValue !== null ? $kmFlashPlinValue : '—' }}
            </td>

            {{-- Diferența KM (Maps – Flash) --}}
            <td class="text-end text-nowrap align-middle {{ $diffFlashGolClass }}">
                {{ $kmMapsFlashGolDiff !== null ? $kmMapsFlashGolDiff : '—' }}
            </td>
            <td class="text-end text-nowrap align-middle {{ $diffFlashPlinClass }}">
                {{ $kmMapsFlashPlinDiff !== null ? $kmMapsFlashPlinDiff : '—' }}
            </td>
        @else
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

            {{-- Diferența KM (Bord – Maps) --}}
            <td class="text-end text-nowrap align-middle {{ $diffClass }}">
                {{ $kmDifference !== null ? $kmDifference : '—' }}
            </td>
        @endif

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
