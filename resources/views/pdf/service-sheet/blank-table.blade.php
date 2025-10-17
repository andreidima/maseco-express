<div class="page">
    <div class="page-header">
        <img src="{{ public_path('images/logo3.jpg') }}" alt="Logo">
    </div>

    <h2 class="section-heading">Fisa iesire service auto</h2>

    <br>
    <div class="mechanic-row">
        <span class="mechanic-label">Nume mecanic auto ____________________________________________________________________________</span>
    </div>

    <table class="parts-table">
        <thead>
            <tr>
                <th style="width: 65%;">Denumire piesa</th>
                <th style="width: 30%;">Cod piesa</th>
                <th style="width: 5%;">Buc</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < 10; $i++)
                <tr>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>
