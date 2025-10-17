<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title>Foaie service - {{ $masina->denumire }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 25px;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            margin: 0;
            padding: 0;
            font-weight: 600;
        }

        .page-break {
            page-break-before: always;
        }

        .meta-table,
        .summary-table,
        .blank-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .meta-table td {
            padding: 4px 6px;
            vertical-align: top;
        }

        .summary-table th,
        .summary-table td,
        .blank-table th,
        .blank-table td {
            border: 1px solid #cfd2d6;
            padding: 6px 8px;
            vertical-align: top;
            word-break: break-word;
        }

        .summary-table th,
        .blank-table th {
            background-color: #f3f4f6;
            font-weight: 600;
        }

        .summary-table td,
        .blank-table td {
            min-height: 28px;
        }

        .summary-table .col-index,
        .blank-table .col-index {
            width: 8%;
            text-align: center;
        }

        .summary-table .col-description,
        .blank-table .col-description {
            width: 44%;
        }

        .summary-table .col-quantity,
        .blank-table .col-quantity {
            width: 18%;
            text-align: center;
        }

        .summary-table .col-notes,
        .blank-table .col-notes {
            width: 30%;
        }

        .section-title {
            font-size: 18px;
            margin-bottom: 12px;
        }

        .meta-section {
            margin-bottom: 16px;
        }

        .meta-grid {
            width: 100%;
            border: 1px solid #cfd2d6;
            border-radius: 6px;
            overflow: hidden;
        }

        .meta-grid tr:nth-child(even) td {
            background: #f9fafb;
        }

        .meta-grid strong {
            display: inline-block;
            min-width: 140px;
        }

        .blank-table-caption {
            font-size: 16px;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    @include('pdf.service-sheet.summary')

    <div class="page-break"></div>

    @include('pdf.service-sheet.blank-table')
</body>

</html>
