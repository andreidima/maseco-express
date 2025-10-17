<div class="page">
    <div class="page-header">
        <img src="{{ public_path('images/logo3.jpg') }}" alt="Logo">
        <h1 class="page-title">FOAIE SERVICE</h1>
    </div>

    <table class="info-table">
        <tr>
            <th>Nr. auto</th>
            <td>{{ $masina->numar_inmatriculare }}</td>
        </tr>
        <tr>
            <th>Km bord</th>
            <td>{{ number_format($km_bord, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Data service</th>
            <td>{{ $data_service->format('d.m.Y') }}</td>
        </tr>
    </table>

    <h2 class="section-heading">Fisa intrare service</h2>

    <ol class="interventions-list">
        @forelse ($items as $item)
            <li>{{ $item['descriere'] }}</li>
        @empty
            <li>&nbsp;</li>
        @endforelse
    </ol>
</div>
