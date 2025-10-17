<div class="page">
    <div class="page-header">
        <img src="{{ public_path('images/logo3.jpg') }}" alt="Logo">
        <h1 class="page-title">Foaie Service</h1>
    </div>

    <h2 class="section-heading">Fisa iesire service auto</h2>

    <div>
        <div class="mechanic-label">Nume mecanic auto</div>
        <div class="mechanic-line"></div>
    </div>

    <table class="parts-table">
        <thead>
            <tr>
                <th style="width: 50%;">Denumire piesa</th>
                <th style="width: 30%;">Cod piesa</th>
                <th style="width: 20%;">Cantitate</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < 22; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>
