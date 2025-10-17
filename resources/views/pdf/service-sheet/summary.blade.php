<div class="meta-section">
    <h1 class="section-title">Foaie service</h1>
    <table class="meta-grid">
        <tbody>
            <tr>
                <td><strong>Mașină:</strong> {{ $masina->denumire }}</td>
                <td><strong>Nr. înmatriculare:</strong> {{ $masina->numar_inmatriculare }}</td>
            </tr>
            <tr>
                <td><strong>Serie șasiu:</strong> {{ $masina->serie_sasiu ?? '—' }}</td>
                <td><strong>Km bord:</strong> {{ number_format($km_bord, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Data service:</strong> {{ $data_service->format('d.m.Y') }}</td>
                <td><strong>Generat la:</strong> {{ now()->format('d.m.Y H:i') }}</td>
            </tr>
        </tbody>
    </table>
</div>

<table class="summary-table">
    <thead>
        <tr>
            <th class="col-index">Nr. crt.</th>
            <th class="col-description">Descriere intervenție</th>
            <th class="col-quantity">Cantitate</th>
            <th class="col-notes">Observații / manoperă</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
            <tr>
                <td class="col-index">{{ $item['index'] }}</td>
                <td class="col-description">{{ $item['description'] }}</td>
                <td class="col-quantity">{{ $item['quantity'] !== '' ? $item['quantity'] : '—' }}</td>
                <td class="col-notes">{{ $item['notes'] !== '' ? $item['notes'] : '—' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
