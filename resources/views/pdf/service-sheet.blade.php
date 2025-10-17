<!DOCTYPE html>
<html lang="ro">

<head>
    <meta charset="UTF-8">
    <title>Foaie service - {{ $masina->denumire }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 18mm 15mm 18mm 15mm;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #111827;
            margin: 0;
            padding: 0;
            line-height: 1.35;
            background: #ffffff;
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
            color: #0f172a;
            letter-spacing: 0.02em;
            text-transform: uppercase;
        }

        .sheet {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }

        .sheet-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
        }

        .sheet-header .logo {
            height: 58px;
            width: auto;
        }

        .sheet-header .title-block {
            text-align: right;
        }

        .sheet-header .title-block h1 {
            font-size: 22px;
            margin-bottom: 4px;
        }

        .sheet-header .title-block span {
            display: block;
            font-size: 12px;
            letter-spacing: 0.12em;
            color: #475569;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }

        .meta-card {
            border: 1px solid #cbd5f5;
            border-radius: 8px;
            padding: 10px 12px;
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .meta-card span.label {
            font-size: 10px;
            color: #475569;
            letter-spacing: 0.08em;
        }

        .meta-card span.value {
            font-size: 15px;
            font-weight: 600;
            color: #0f172a;
            text-transform: uppercase;
        }

        .section-title {
            font-size: 16px;
            letter-spacing: 0.08em;
            padding-bottom: 6px;
            border-bottom: 1px solid #cbd5f5;
        }

        .entry-table,
        .parts-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        .entry-table th,
        .entry-table td,
        .parts-table th,
        .parts-table td {
            border: 1px solid #d0d7e2;
            padding: 7px 8px;
            vertical-align: top;
        }

        .entry-table thead th,
        .parts-table thead th {
            background: #e9effa;
            font-size: 11px;
            color: #1e293b;
            letter-spacing: 0.04em;
        }

        .entry-table tbody td.number,
        .parts-table tbody td.number {
            width: 46px;
            text-align: center;
            font-weight: 600;
        }

        .notes-area {
            margin-top: 12px;
            border: 1px dashed #94a3b8;
            border-radius: 6px;
            min-height: 80px;
            padding: 10px 12px;
            background: #f8fafc;
        }

        .notes-area span {
            display: block;
            font-size: 10px;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.08em;
        }

        .signature-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-top: 40px;
        }

        .signature-block {
            display: flex;
            flex-direction: column;
            gap: 6px;
            min-width: 220px;
        }

        .signature-block span.label {
            font-size: 10px;
            color: #475569;
            letter-spacing: 0.06em;
        }

        .signature-line {
            border-bottom: 1px solid #1e293b;
            height: 18px;
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
