@php
    $isFlashDivision = optional($valabilitate->divizie)->id === 1
        && strcasecmp((string) optional($valabilitate->divizie)->nume, 'FLASH') === 0;
    $tableColumnCount = $isFlashDivision ? 23 : 12;
    $divizie = $valabilitate->divizie;
    $priceKmGol = $divizie && $divizie->pret_km_gol !== null ? (float) $divizie->pret_km_gol : null;
    $priceKmPlin = $divizie && $divizie->pret_km_plin !== null ? (float) $divizie->pret_km_plin : null;
    $priceKmCuTaxa = $divizie && $divizie->pret_km_cu_taxa !== null ? (float) $divizie->pret_km_cu_taxa : null;
    $dailyContributionUnit = $divizie && $divizie->contributie_zilnica !== null
        ? (float) $divizie->contributie_zilnica
        : null;
    $formatCalculatedValue = static function (?float $value): string {
        if ($value === null) {
            return '—';
        }

        return number_format($value, 2);
    };
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

        $kmMapsGolAmount = $kmMapsGolValue !== null && $priceKmGol !== null
            ? round($kmMapsGolValue * $priceKmGol, 2)
            : null;
        $kmMapsPlinAmount = $kmMapsPlinValue !== null && $priceKmPlin !== null
            ? round($kmMapsPlinValue * $priceKmPlin, 2)
            : null;
        $kmMapsCuTaxaAmount = $kmCuTaxaValue !== null && $priceKmCuTaxa !== null
            ? round($kmCuTaxaValue * $priceKmCuTaxa, 2)
            : null;

        $calculatedTotalAmount = null;
        if ($kmMapsGolAmount !== null || $kmMapsPlinAmount !== null || $kmMapsCuTaxaAmount !== null) {
            $calculatedTotalAmount = round(
                ($kmMapsGolAmount ?? 0.0)
                + ($kmMapsPlinAmount ?? 0.0)
                + ($kmMapsCuTaxaAmount ?? 0.0),
                2
            );
        }

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
        $groupInvoiceNumber = $group?->numar_factura ?? '—';
        $groupInvoiceDate = $group?->data_factura?->format('d.m.Y') ?? '—';
        $groupRr = $group->rr ?? '—';
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
        $groupCalculatedDays = $groupFinancialMeta['zile_calculate'] ?? '—';

        $alteTaxe = is_numeric($cursa->alte_taxe) ? (float) $cursa->alte_taxe : null;
        $fuelTax = is_numeric($cursa->fuel_tax) ? (float) $cursa->fuel_tax : null;
        $sumaIncasata = is_numeric($cursa->suma_incasata) ? (float) $cursa->suma_incasata : null;

        $diferentaPret = null;
        if ($sumaIncasata !== null || $calculatedTotalAmount !== null || $alteTaxe !== null || $fuelTax !== null) {
            $diferentaPret = round(
                ($sumaIncasata ?? 0.0)
                - ($calculatedTotalAmount ?? 0.0)
                - ($alteTaxe ?? 0.0)
                - ($fuelTax ?? 0.0),
                2
            );
        }

        $dailyContributionCalculat = null;
        if ($group && is_numeric($group->zile_calculate) && $dailyContributionUnit !== null) {
            $dailyContributionCalculat = round(((float) $group->zile_calculate) * $dailyContributionUnit, 2);
        }

        $dailyContributionIncasata = is_numeric($cursa->daily_contribution_incasata)
            ? (float) $cursa->daily_contribution_incasata
            : null;

        $dailyContributionClass = $dailyContributionCalculat !== null
                && $dailyContributionIncasata !== null
                && $dailyContributionIncasata < $dailyContributionCalculat
            ? 'text-danger fw-semibold'
            : '';

        $priceDiffClass = $diferentaPret !== null && $diferentaPret < 0
            ? 'text-danger fw-semibold'
            : '';
    @endphp

    @if ($isNewGroup)
        <tr class="curse-group-heading curse-group-heading--emphasis" style="background-color: {{ $groupColor }}; color: {{ $groupTextColor }};">
            <th colspan="{{ $tableColumnCount }}">
                @if ($isFlashDivision)
                    <div class="d-flex flex-wrap gap-3 align-items-center small">
                        <span class="fw-semibold text-uppercase">RR: {{ $groupRr }}</span>
                        <span>Număr factură: <strong>{{ $groupInvoiceNumber }}</strong></span>
                        <span>Data facturii: <strong>{{ $groupInvoiceDate }}</strong></span>
                        <span>Zile calculate: <strong>{{ $groupCalculatedDays }}</strong></span>
                    </div>
                @else
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
                @endif
            </th>
        </tr>
    @endif

    @php
        $checkboxId = 'cursa-select-' . $cursa->id;
    @endphp
    <tr
        @class(['curse-group-row' => (bool) $group])
        style="background-color: {{ $group ? $groupColor : 'transparent' }}; color: {{ $group ? $groupTextColor : '#111' }};"
        data-nav-row
        tabindex="-1"
    >
        <td class="text-center align-middle curse-sticky-col curse-col-select">
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
        <td class="text-center fw-semibold curse-col-order curse-compact-cell">
            <div class="align-items-center gap-2">
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
        <td class="text-nowrap align-middle curse-col-number curse-compact-cell">
            {{ $cursa->nr_cursa ?: '—' }}
        </td>

        {{-- Cursa (descriere) --}}
        <td class="small align-middle curse-col-route curse-compact-cell">
            {{ $cursaDescriere }}
        </td>

        {{-- Dată transport --}}
        <td class="small text-center align-middle curse-col-date curse-compact-cell">
            {{ $dataTransport ?: '—' }}
        </td>

        @if ($isFlashDivision)
            {{-- KM Maps gol/plin --}}
            <td class="text-end text-nowrap align-middle curse-col-numeric curse-compact-cell">
                {{ $kmMapsGolValue !== null ? $kmMapsGolValue : '—' }}
            </td>
            <td class="text-end text-nowrap align-middle curse-col-numeric curse-compact-cell">
                {{ $kmMapsPlinValue !== null ? $kmMapsPlinValue : '—' }}
            </td>

            {{-- KM cu taxă --}}
            <td class="text-end text-nowrap align-middle curse-col-numeric curse-compact-cell">
                {{ $kmCuTaxaValue !== null ? $kmCuTaxaValue : '—' }}
            </td>

            {{-- KM Flash gol/plin --}}
            <td class="text-end text-nowrap align-middle curse-col-numeric curse-hide-lg curse-compact-cell">
                {{ $kmFlashGolValue !== null ? $kmFlashGolValue : '—' }}
            </td>
            <td class="text-end text-nowrap align-middle curse-col-numeric curse-hide-lg curse-compact-cell">
                {{ $kmFlashPlinValue !== null ? $kmFlashPlinValue : '—' }}
            </td>

            {{-- Diferența KM (Maps – Flash) --}}
            <td class="text-end text-nowrap align-middle {{ $diffFlashGolClass }} curse-col-numeric curse-hide-lg curse-compact-cell">
                {{ $kmMapsFlashGolDiff !== null ? $kmMapsFlashGolDiff : '—' }}
            </td>
            <td class="text-end text-nowrap align-middle {{ $diffFlashPlinClass }} curse-col-numeric curse-hide-lg curse-compact-cell">
                {{ $kmMapsFlashPlinDiff !== null ? $kmMapsFlashPlinDiff : '—' }}
            </td>

            {{-- Sumă calculată subcoloane --}}
            <td class="text-end align-middle text-nowrap curse-col-numeric curse-hide-md curse-compact-cell">{{ $formatCalculatedValue($kmMapsGolAmount) }}</td>
            <td class="text-end align-middle text-nowrap curse-col-numeric curse-hide-md curse-compact-cell">{{ $formatCalculatedValue($kmMapsPlinAmount) }}</td>
            <td class="text-end align-middle text-nowrap curse-col-numeric curse-hide-md curse-compact-cell">{{ $formatCalculatedValue($kmMapsCuTaxaAmount) }}</td>
            <td class="text-end align-middle text-nowrap curse-col-numeric curse-hide-md curse-compact-cell">{{ $formatCalculatedValue($calculatedTotalAmount) }}</td>
            <td class="text-end align-middle text-nowrap curse-col-numeric curse-compact-cell">{{ $alteTaxe !== null ? number_format($alteTaxe, 2) : '—' }}</td>
            <td class="text-end align-middle text-nowrap curse-col-numeric curse-compact-cell">{{ $fuelTax !== null ? number_format($fuelTax, 2) : '—' }}</td>
            <td class="text-end align-middle text-nowrap curse-col-numeric curse-compact-cell">{{ $sumaIncasata !== null ? number_format($sumaIncasata, 2) : '—' }}</td>
            <td class="text-end align-middle text-nowrap {{ $priceDiffClass }} curse-col-numeric curse-hide-sm curse-compact-cell">{{ $diferentaPret !== null ? number_format($diferentaPret, 2) : '—' }}</td>
            <td class="text-end align-middle text-nowrap curse-col-numeric curse-hide-sm curse-compact-cell">{{ $dailyContributionCalculat !== null ? number_format($dailyContributionCalculat, 2) : '—' }}</td>
            <td class="text-end align-middle text-nowrap {{ $dailyContributionClass }} curse-col-numeric curse-hide-sm curse-compact-cell">{{ $dailyContributionIncasata !== null ? number_format($dailyContributionIncasata, 2) : '—' }}</td>
        @else
            {{-- KM Maps --}}
            <td class="text-end text-nowrap align-middle curse-col-numeric curse-compact-cell">
                {{ $kmMapsDisplay }}
            </td>

            {{-- KM Plecare --}}
            <td class="text-end text-nowrap align-middle curse-col-numeric curse-compact-cell">
                {{ $kmPlecare !== null ? $kmPlecare : '—' }}
            </td>

            {{-- KM Sosire --}}
            <td class="text-end text-nowrap align-middle curse-col-numeric curse-compact-cell">
                {{ $kmSosire !== null ? $kmSosire : '—' }}
            </td>

            {{-- KM Bord 2 – Km gol --}}
            <td class="text-end text-nowrap align-middle curse-col-numeric curse-hide-md curse-compact-cell">
                {{ $kmGol !== null ? $kmGol : '—' }}
            </td>

            {{-- KM Bord 2 – Km plin --}}
            <td class="text-end text-nowrap align-middle curse-col-numeric curse-hide-md curse-compact-cell">
                {{ $kmPlin !== null ? $kmPlin : '—' }}
            </td>

            {{-- Diferența KM (Bord – Maps) --}}
            <td class="text-end text-nowrap align-middle {{ $diffClass }} curse-col-numeric curse-hide-lg curse-compact-cell">
                {{ $kmDifference !== null ? $kmDifference : '—' }}
            </td>
        @endif

        {{-- Acțiuni --}}
        <td class="text-end align-middle curse-col-actions curse-compact-cell">
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
