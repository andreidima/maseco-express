<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title>Foaie service - {{ $masina->denumire }}</title>
    <style>

        * {
            box-sizing: border-box;
        }

        html,
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 14px;
            color: #0f172a;
            margin: 2mm 5mm;
            padding: 0;
            line-height: 1.4;
            background: #ffffff;
        }

        h1,
        h2,
        h3 {
            margin: 0;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .page {
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .page-header {
            text-align: center;
        }

        .page-header img {
            width: 360px;
            height: auto;
        }

        .page-title {
            font-size: 40px;
            margin-top: 6px;
        }

        .section-heading {
            text-align: center;
            font-size: 16px;
            letter-spacing: 0.08em;
        }

        .info-table,
        .parts-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table th,
        .info-table td,
        .parts-table th,
        .parts-table td {
            border: 1px solid #0f172a;
            padding: 8px 10px;
            text-align: left;
        }

        .info-table th {
            width: 40%;
            font-size: 14px;
        }

        .info-table td {
            font-size: 14px;
            font-weight: 600;
        }

        .interventions-list {
            margin: 0;
            padding-left: 22px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .mechanic-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 18px;
        }

        .mechanic-label {
            font-size: 14px;
            font-weight: 600;
        }

        .mechanic-line {
            border-bottom: 1px solid #0f172a;
            flex: 1;
            height: 0;
        }

        .parts-table th {
            text-transform: uppercase;
            font-size: 14px;
        }

        .parts-table td {
            height: 26px;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    @include('pdf.service-sheet.summary')

    <div class="page-break"></div>

    @include('pdf.service-sheet.blank-table')
</body>

</html>
