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
</table>
