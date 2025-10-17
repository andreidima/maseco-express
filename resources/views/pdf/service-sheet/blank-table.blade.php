<div class="sheet">
    <div class="sheet-header">
        <img class="logo" src="{{ public_path('images/logo3.jpg') }}" alt="Logo">
        <div class="title-block">
            <h1>Foaie Service</h1>
            <span>Fisa iesire service auto</span>
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
            <span class="label">Data completare</span>
            <span class="value">{{ now()->format('d.m.Y') }}</span>
        </div>
    </div>

    <div>
        <h2 class="section-title">Piese si materiale utilizate</h2>
        <table class="parts-table">
            <thead>
                <tr>
                    <th style="width: 46px;">Nr.</th>
                    <th>Denumire piesa</th>
                    <th style="width: 28%;">Cod piesa</th>
                    <th style="width: 16%;">Cantitate</th>
                </tr>
            </thead>
            <tbody>
                @for ($i = 1; $i <= 18; $i++)
                    <tr>
                        <td class="number">{{ $i }}</td>
                        <td>&nbsp;</td>
                        <td></td>
                        <td></td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

    <div class="signature-row" style="margin-top: 60px;">
        <div class="signature-block">
            <span class="label">Mecanic auto</span>
            <div class="signature-line"></div>
        </div>
        <div class="signature-block" style="align-items: flex-end; text-align: right;">
            <span class="label">Client / reprezentant</span>
            <div class="signature-line" style="width: 220px;"></div>
        </div>
    </div>
</div>
