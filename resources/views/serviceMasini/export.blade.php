<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title>Service mașini - {{ $masina->denumire }}</title>
    <style>
        @page {
            margin: 20px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            margin: 0;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            table-layout: fixed;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
            word-break: break-word;
            overflow-wrap: anywhere;
        }

        th {
            background-color: #f0f0f0;
        }

        .text-end {
            text-align: right;
        }

        .col-date {
            width: 10%;
        }

        .col-tip {
            width: 8%;
        }

        .col-denumire {
            width: 20%;
        }

        .col-cod {
            width: 12%;
        }

        .col-cantitate {
            width: 10%;
        }

        .col-mecanic {
            width: 15%;
        }

        .col-utilizator {
            width: 15%;
        }

        .col-observatii {
            width: 10%;
        }

        .meta {
            margin-bottom: 15px;
        }

        .meta div {
            margin-bottom: 4px;
        }

        .small {
            font-size: 11px;
            color: #666;
        }
    </style>
</head>

<body>
    <h1>Service mașini</h1>

    <div class="meta">
        <div><strong>Mașină:</strong> {{ $masina->denumire }} ({{ $masina->numar_inmatriculare }})</div>
        @if ($masina->serie_sasiu)
            <div><strong>Serie șasiu:</strong> {{ $masina->serie_sasiu }}</div>
        @endif
        @if ($filters['data_start'] ?? null)
            <div><strong>De la:</strong> {{ \Carbon\Carbon::parse($filters['data_start'])->format('d.m.Y') }}</div>
        @endif
        @if ($filters['data_end'] ?? null)
            <div><strong>Până la:</strong> {{ \Carbon\Carbon::parse($filters['data_end'])->format('d.m.Y') }}</div>
        @endif
        @if (($filters['piesa'] ?? '') !== '')
            <div><strong>Denumire piesă:</strong> {{ $filters['piesa'] }}</div>
        @endif
        @if (($filters['cod'] ?? '') !== '')
            <div><strong>Cod piesă:</strong> {{ $filters['cod'] }}</div>
        @endif
        <div class="small">Generat la {{ now()->format('d.m.Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-date">Data</th>
                <th class="col-tip">Tip</th>
                <th class="col-denumire">Denumire</th>
                <th class="col-cod">Cod</th>
                <th class="col-cantitate text-end">Cantitate</th>
                <th class="col-mecanic">Mecanic</th>
                <th class="col-utilizator">Utilizator</th>
                <th class="col-observatii">Observații</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($entries as $entry)
                <tr>
                    <td class="col-date">{{ optional($entry->data_montaj)->format('d.m.Y') ?? '—' }}</td>
                    <td class="col-tip">{{ $entry->tip === 'piesa' ? 'Piesă' : 'Manual' }}</td>
                    <td class="col-denumire">
                        @if ($entry->tip === 'piesa')
                            {{ $entry->denumire_piesa ?? '—' }}
                        @else
                            {{ $entry->denumire_interventie ?? '—' }}
                        @endif
                    </td>
                    <td class="col-cod">{{ $entry->tip === 'piesa' ? ($entry->cod_piesa ?? '—') : '—' }}</td>
                    <td class="col-cantitate text-end">
                        @if ($entry->tip === 'piesa')
                            {{ $entry->cantitate !== null ? number_format((float) $entry->cantitate, 2) : '—' }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="col-mecanic">{{ $entry->nume_mecanic ?? '—' }}</td>
                    <td class="col-utilizator">{{ $entry->nume_utilizator ?? optional($entry->user)->name ?? '—' }}</td>
                    <td class="col-observatii">{{ $entry->observatii ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center;">Nu există intervenții pentru filtrul selectat.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
