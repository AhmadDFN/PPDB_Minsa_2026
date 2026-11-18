<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        @page {
            margin: 28px 32px 42px;
        }

        body {
            color: #172033;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
            line-height: 1.4;
        }

        .letterhead {
            border-bottom: 2px solid #163b68;
            margin-bottom: 13px;
            padding-bottom: 9px;
            text-align: center;
        }

        .ministry {
            font-size: 9px;
            font-weight: bold;
            letter-spacing: .4px;
            text-transform: uppercase;
        }

        .school {
            color: #163b68;
            font-size: 17px;
            font-weight: bold;
            margin-top: 3px;
        }

        .document-title {
            color: #163b68;
            font-size: 13px;
            font-weight: bold;
            margin: 0 0 8px;
            text-align: center;
            text-transform: uppercase;
        }

        .notice {
            background: #edf4fb;
            border-left: 3px solid #163b68;
            margin: 8px 0 12px;
            padding: 7px 9px;
        }

        .section-title {
            background: #e8eef5;
            color: #163b68;
            font-size: 9px;
            font-weight: bold;
            margin: 11px 0 0;
            padding: 5px 7px;
            text-transform: uppercase;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        .data-table td,
        .data-table th {
            border: 1px solid #cbd5e1;
            padding: 5px 6px;
            vertical-align: top;
        }

        .data-table th {
            background: #f8fafc;
            font-weight: bold;
            text-align: left;
            width: 27%;
        }

        .photo-cell {
            padding: 7px;
            text-align: center;
            vertical-align: top;
            width: 105px;
        }

        .photo {
            max-height: 120px;
            max-width: 90px;
        }

        .small {
            color: #475569;
            font-size: 8px;
        }

        .signature-table {
            margin-top: 20px;
            page-break-inside: avoid;
        }

        .signature-cell {
            height: 82px;
            padding: 8px;
            text-align: center;
            vertical-align: top;
            width: 50%;
        }

        .signature-line {
            border-bottom: 1px solid #475569;
            height: 42px;
            margin: 0 auto 5px;
            width: 85%;
        }

        .qr-table {
            margin-top: 14px;
            page-break-inside: avoid;
        }

        .qr-code {
            height: 95px;
            width: 95px;
        }

        .muted {
            color: #64748b;
        }

        .avoid-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <header class="letterhead">
        <div class="ministry">{{ $document['ministry_name'] }}</div>
        <div class="school">{{ $document['school_name'] }}</div>
    </header>

    @yield('content')
</body>
</html>
