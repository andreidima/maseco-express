<div class="meta-section">
    <h2 class="blank-table-caption">Tabel completare intervenții</h2>
    <p style="margin-bottom: 12px; color: #4b5563;">Spațiu dedicat completării manuale pentru intervenții suplimentare.</p>
</div>

<table class="blank-table">
    <thead>
        <tr>
            <th class="col-index">Nr. crt.</th>
            <th class="col-descriere">Descriere intervenție</th>
        </tr>
    </thead>
    <tbody>
        @for ($i = 1; $i <= 14; $i++)
            <tr>
                <td class="col-index">{{ $i }}</td>
                <td class="col-descriere">&nbsp;</td>
            </tr>
        @endfor
    </tbody>
</table>

<div style="margin-top: 28px; font-size: 13px;">
    <strong>NUME MECANIC AUTO:</strong>
    <span style="display: inline-block; min-width: 280px; border-bottom: 1px solid #cfd2d6; height: 18px; margin-left: 8px;"></span>
</div>
