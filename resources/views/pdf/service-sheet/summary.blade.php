<div class="sheet">
    <div class="sheet-header">
        <img class="logo" src="{{ public_path('images/logo3.jpg') }}" alt="Logo">
        <div class="title-block">
            <h1>Foaie Service</h1>
            <span>Fisa intrare service auto</span>
        </div>
    </div>

    <div class="meta-grid">
        <div class="meta-card">
            <span class="label">Nr. auto</span>
            <span class="value">{{ $masina->numar_inmatriculare }}</span>
        </div>
        <div class="meta-card">
            <span class="label">Km bord</span>
            <span class="value">{{ number_format($km_bord, 0, ',', '.') }}</span>
        </div>
        <div class="meta-card">
            <span class="label">Data service</span>
            <span class="value">{{ $data_service->format('d.m.Y') }}</span>
        </div>
    </div>

    <div>
        <h2 class="section-title">Fisa intrare service</h2>
        <table class="entry-table">
            <thead>
                <tr>
                    <th style="width: 46px;">Nr.</th>
                    <th>Descriere interventie</th>
                    <th style="width: 28%;">Observatii</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $rowsRendered = 0;
                @endphp
                @foreach ($items as $item)
                    @php $rowsRendered++; @endphp
                    <tr>
                        <td class="number">{{ $item['index'] }}</td>
                        <td>{{ $item['descriere'] }}</td>
                        <td></td>
                    </tr>
                @endforeach
                @if ($rowsRendered < 12)
                    @for ($i = $rowsRendered + 1; $i <= 12; $i++)
                        <tr>
                            <td class="number">{{ $i }}</td>
                            <td>&nbsp;</td>
                            <td></td>
                        </tr>
                    @endfor
                @endif
            </tbody>
        </table>
    </div>

    <div class="notes-area">
        <span>Observatii suplimentare / recomandari</span>
    </div>

    <div class="signature-row">
        <div class="signature-block">
            <span class="label">Receptie service</span>
            <div class="signature-line"></div>
        </div>
        <div class="signature-block" style="align-items: flex-end; text-align: right;">
            <span class="label">Client / reprezentant</span>
            <div class="signature-line" style="width: 220px;"></div>
        </div>
    </div>
</div>
