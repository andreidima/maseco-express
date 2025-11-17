<table class="curse-summary-table">
    <tr>
        <th class="curse-summary-title">
            {{ $valabilitate->numar_auto ?? '—' }}
        </th>
        <th colspan="2" class="curse-summary-driver">
            {{ $valabilitate->sofer->name ?? '—' }}
        </th>
    </tr>
    @php
        $taxeDrum = $valabilitate->taxeDrum ?? collect();
    @endphp
    @foreach ($taxeDrum as $taxa)
        <tr>
            <td colspan="6" class="text-start">
                Taxă de drum: {{ $taxa->nume ?? '—' }}
            </td>
        </tr>
    @endforeach
    <tr>
        <th class="curse-summary-label">Dată plecare</th>
        <td class="curse-nowrap">
            {{ optional($valabilitate->data_inceput)->format('d.m.Y') ?? '—' }}
        </td>

        <th class="text-end curse-summary-label">KM plecare</th>
        <td class="text-end curse-nowrap">
            {{ $summary['kmPlecare'] !== null ? $summary['kmPlecare'] : '—' }}
        </td>

        <th class="text-end curse-summary-label">KM Maps total</th>
        <td class="text-end curse-nowrap">
            {{ $summary['totalKmMaps'] ? $summary['totalKmMaps'] : '—' }}
        </td>
    </tr>
    <tr>
        <th class="curse-summary-label">Dată sosire</th>
        <td class="curse-nowrap">
            {{ optional($valabilitate->data_sfarsit)->format('d.m.Y') ?? '—' }}
        </td>

        <th class="text-end curse-summary-label">KM sosire</th>
        <td class="text-end curse-nowrap">
            {{ $summary['kmSosire'] !== null ? $summary['kmSosire'] : '—' }}
        </td>

        <th class="text-end curse-summary-label">KM Bord 2 total</th>
        <td class="text-end curse-nowrap">
            {{ $summary['totalKmBord2'] ? $summary['totalKmBord2'] : '—' }}
        </td>
    </tr>
    <tr>
        <th class="curse-summary-label">Total zile</th>
        <td class="curse-nowrap">
            {{ $summary['totalZile'] !== null ? $summary['totalZile'] : '—' }}
        </td>

        <th class="text-end curse-summary-label">KM total (plecare → sosire)</th>
        <td class="text-end curse-nowrap">
            {{ $summary['kmTotal'] !== null ? $summary['kmTotal'] : '—' }}
        </td>

        <th class="text-end curse-summary-label">Diferență totală (Bord–Maps)</th>
        <td class="text-end curse-nowrap">
            {{ $summary['totalKmDiff'] ? $summary['totalKmDiff'] : '—' }}
        </td>
    </tr>
    @php
        $groupFinancials = collect($summary['groupFinancials'] ?? []);
    @endphp
    @if ($groupFinancials->isNotEmpty())
        <tr>
            <th colspan="6" class="curse-summary-label text-center">Situație pe grupuri</th>
        </tr>
        <tr>
            <th class="curse-summary-label">Grup</th>
            <th class="curse-summary-label">Format</th>
            <th class="curse-summary-label">Factură</th>
            <th class="text-end curse-summary-label">Sumă încasată</th>
            <th class="text-end curse-summary-label">Sumă calculată</th>
            <th class="text-end curse-summary-label">Diferență</th>
        </tr>
        @foreach ($groupFinancials as $group)
            @php
                $rowColor = $group['culoare_hex'] ?? '#ffffff';
                $facturaLabel = $group['numar_factura'] ? $group['numar_factura'] : '—';
                $facturaDate = $group['data_factura'] ?? null;
                if ($facturaDate) {
                    $formattedDate = $facturaDate instanceof \Carbon\CarbonInterface
                        ? $facturaDate->format('d.m.Y')
                        : \Illuminate\Support\Carbon::parse($facturaDate)->format('d.m.Y');
                    $facturaLabel = $facturaLabel === '—'
                        ? $formattedDate
                        : $facturaLabel . ' / ' . $formattedDate;
                }
            @endphp
            <tr style="background-color: {{ $rowColor }}; color: #111;">
                <td class="fw-semibold">{{ $group['nume'] }}</td>
                <td>{{ $group['format_label'] ?? '—' }}</td>
                <td>{{ $facturaLabel }}</td>
                <td class="text-end">{{ $group['suma_incasata'] !== null ? number_format($group['suma_incasata'], 2) : '—' }}</td>
                <td class="text-end">{{ $group['suma_calculata'] !== null ? number_format($group['suma_calculata'], 2) : '—' }}</td>
                <td class="text-end">{{ $group['diferenta'] !== null ? number_format($group['diferenta'], 2) : '—' }}</td>
            </tr>
        @endforeach
        @php
            $groupTotals = $summary['groupFinancialTotals'] ?? [];
        @endphp
        <tr>
            <th colspan="3" class="text-end curse-summary-label">Total grupuri</th>
            <th class="text-end">
                {{ isset($groupTotals['suma_incasata']) ? number_format($groupTotals['suma_incasata'], 2) : '—' }}
            </th>
            <th class="text-end">
                {{ isset($groupTotals['suma_calculata']) ? number_format($groupTotals['suma_calculata'], 2) : '—' }}
            </th>
            <th class="text-end">
                {{ isset($groupTotals['diferenta']) ? number_format($groupTotals['diferenta'], 2) : '—' }}
            </th>
        </tr>
    @endif
</table>
