<div class="page">
    <div class="page-header">
        <img src="{{ public_path('images/logo3.jpg') }}" alt="Logo">
        <h1 class="page-title">FOAIE SERVICE</h1>
    </div>

    <table class="info-table w-50">
        <tr>
            <th>NR. AUTO</th>
            <td>{{ $masina->numar_inmatriculare }}</td>
        </tr>
        <tr>
            <th>KM BORD</th>
            <td>{{ number_format($km_bord, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <th>DATA SERVICE</th>
            <td>{{ $data_service->format('d.m.Y') }}</td>
        </tr>
    </table>

    <br><br>
    <h2 style="text-align: center;">Fisa intrare service</h2>
    <br>
    <ol class="interventions-list" style="margin:0 5mm">
        @forelse ($items as $item)
            <li>{{ $item['descriere'] }}</li>
        @empty
            <li>&nbsp;</li>
        @endforelse
    </ol>
</div>
