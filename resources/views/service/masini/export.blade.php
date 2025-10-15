<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title>Service mașini - {{ $masina->denumire }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
        }

        h1 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
        }

        th {
            background-color: #f0f0f0;
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
                <th style="width: 80px;">Data</th>
                <th style="width: 80px;">Tip</th>
                <th>Denumire</th>
                <th style="width: 100px;">Cod</th>
                <th style="width: 80px;">Cantitate</th>
                <th style="width: 130px;">Mecanic</th>
                <th style="width: 130px;">Utilizator</th>
                <th>Observații</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($entries as $entry)
                <tr>
                    <td>{{ optional($entry->data_montaj)->format('d.m.Y') ?? '—' }}</td>
                    <td>{{ $entry->tip === 'piesa' ? 'Piesă' : 'Manual' }}</td>
                    <td>
                        @if ($entry->tip === 'piesa')
                            {{ $entry->denumire_piesa ?? '—' }}
                        @else
                            {{ $entry->denumire_interventie ?? '—' }}
                        @endif
                    </td>
                    <td>{{ $entry->tip === 'piesa' ? ($entry->cod_piesa ?? '—') : '—' }}</td>
                    <td>
                        @if ($entry->tip === 'piesa')
                            {{ $entry->cantitate !== null ? number_format((float) $entry->cantitate, 2) : '—' }}
                        @else
                            —
                        @endif
                    </td>
                    <td>{{ $entry->nume_mecanic ?? '—' }}</td>
                    <td>{{ $entry->nume_utilizator ?? optional($entry->user)->name ?? '—' }}</td>
                    <td>{{ $entry->observatii ?? '—' }}</td>
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
