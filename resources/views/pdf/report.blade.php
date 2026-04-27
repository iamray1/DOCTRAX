<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Processing Report</title>
    <style>
        @page {
            size: 210mm 297mm;
            margin: 10mm;
        }

        @php
            $poppinsFontPath = str_replace('\\', '/', storage_path('fonts/Poppins'));
        @endphp

        @font-face {
            font-family: 'Poppins';
            src: url("{{ $poppinsFontPath }}/Poppins-Regular.ttf") format("truetype");
            font-weight: 400;
            font-style: normal;
        }

        @font-face {
            font-family: 'Poppins';
            src: url("{{ $poppinsFontPath }}/Poppins-Medium.ttf") format("truetype");
            font-weight: 500;
            font-style: normal;
        }

        @font-face {
            font-family: 'Poppins';
            src: url("{{ $poppinsFontPath }}/Poppins-SemiBold.ttf") format("truetype");
            font-weight: 600;
            font-style: normal;
        }

        @font-face {
            font-family: 'Poppins';
            src: url("{{ $poppinsFontPath }}/Poppins-Bold.ttf") format("truetype");
            font-weight: 700;
            font-style: normal;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            width: 100%;
            min-height: 100%;
        }

        body {
            font-family: 'Poppins', 'DejaVu Sans', sans-serif;
            font-size: 7.5px;
            line-height: 1.3;
            color: #1f2937;
            background: #ffffff;
        }

        .page {
            width: 100%;
        }

        .header {
            padding-bottom: 6px;
            margin-bottom: 8px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        .header-table td {
            border: none;
            padding: 0;
            vertical-align: top;
        }

        .header-left {
            width: 65%;
            padding-right: 10px;
            padding-left: 5px;
        }

        .header-right {
            width: 35%;
            text-align: right;
            padding-left: 10px;
            padding-right: 5px;
        }

        .brand {
            font-size: 6.5px;
            font-weight: 600;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: #6b7280;
            margin-top: 3px;
            margin-bottom: -1px;
            margin-left: 8px;
            line-height: 1;
        }

        .report-title {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            line-height: 1;
            margin-bottom: -1px;
            margin-left: 8px;
        }

        .report-subtitle {
            font-size: 8px;
            font-weight: 500;
            color: #4b5563;
            margin-left: 8px;
            line-height: 1;
        }

        .meta-label {
            font-size: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #9ca3af;
            margin-bottom: 1px;
            margin-right: 8px;
        }

        .meta-value {
            font-size: 7.5px;
            font-weight: 600;
            color: #111827;
            line-height: 1.3;
            margin-right: 8px;
        }

        .summary {
            width: 100%;
            margin-bottom: 8px;
            border: 1px solid #e5e7eb;
            background: #f9fafb;
            border-collapse: collapse;
        }

        .summary td {
            border: none;
            padding: 5px 6px;
            vertical-align: top;
        }

        .summary-item {
            font-size: 6.5px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-bottom: 1px;
        }

        .summary-value {
            font-size: 8px;
            font-weight: 600;
            color: #111827;
        }

        .summary-divider {
            width: 1px;
            background: #e5e7eb;
            padding: 0 !important;
        }

        .table-wrap {
            width: 100%;
        }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #d1d5db;
        }

        .report-table thead th {
            background: #f3f4f6;
            color: #111827;
            font-size: 6.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: left;
            padding: 4px 3px;
            border: 1px solid #d1d5db;
            vertical-align: middle;
            line-height: 1.2;
        }

        .report-table tbody tr {
            page-break-inside: avoid;
        }

        .report-table tbody tr:nth-child(even) {
            background: #fafafa;
        }

        .report-table tbody td {
            padding: 3px 3px;
            border: 1px solid #e5e7eb;
            vertical-align: top;
            font-size: 6.5px;
            color: #1f2937;
            word-wrap: break-word;
            line-height: 1.2;
        }

        .col-num {
            width: 4%;
            text-align: center;
            font-weight: 600;
        }

        .col-ref {
            width: 16%;
        }

        .col-doc {
            width: 28%;
        }

        .col-sender {
            width: 13%;
        }

        .col-status {
            width: 11%;
        }

        .col-handler {
            width: 12%;
        }

        .col-activity {
            width: 16%;
        }

        .ref-main {
            display: block;
            font-weight: 600;
            font-size: 6.5px;
            color: #111827;
            margin-bottom: 0.5px;
            line-height: 1.15;
        }

        .ref-sub {
            display: block;
            font-size: 5.8px;
            color: #6b7280;
            line-height: 1.15;
        }

        .doc-title {
            font-weight: 600;
            font-size: 6.5px;
            color: #111827;
            line-height: 1.2;
            margin-bottom: 0.5px;
        }

        .doc-type {
            font-size: 5.8px;
            color: #6b7280;
            line-height: 1.15;
        }

        .sender-name,
        .handler-name {
            font-size: 6.5px;
            color: #1f2937;
            line-height: 1.2;
        }

        .status-badge {
            display: inline-block;
            padding: 0;
            border: none;
            background: transparent;
            color: #111827;
            font-size: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        .activity-line {
            margin-bottom: 1px;
            line-height: 1.15;
        }

        .activity-line:last-child {
            margin-bottom: 0;
        }

        .activity-label {
            display: block;
            font-size: 5.5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            color: #9ca3af;
            margin-bottom: 0.3px;
            line-height: 1.1;
        }

        .activity-value {
            display: block;
            font-size: 6.2px;
            color: #1f2937;
            line-height: 1.15;
        }

        .empty-state {
            border: 1px solid #e5e7eb;
            background: #fafafa;
            text-align: center;
            padding: 24px 12px;
            color: #6b7280;
        }

        .empty-title {
            font-size: 9px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 3px;
        }

        .empty-text {
            font-size: 7.5px;
            color: #6b7280;
        }

        .footer {
            position: fixed;
            bottom: 8mm;
            left: 10mm;
            right: 10mm;
            padding-top: 4px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 6px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="header-left">
                        <div class="brand">DepEd DOCTRAX</div>
                        <div class="report-title">Processing Report</div>
                        <div class="report-subtitle">{{ $officeName }}</div>
                    </td>
                    <td class="header-right">
                        <div class="meta-label">Generated</div>
                        <div class="meta-value">{{ $generatedAt }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <table class="summary">
            <tr>
                <td width="34%">
                    <div class="summary-item">Total Records</div>
                    <div class="summary-value">{{ $rows->count() }}</div>
                </td>
                <td class="summary-divider"></td>
                <td width="33%">
                    <div class="summary-item">Date Basis</div>
                    <div class="summary-value">{{ $dateFieldLabel }}</div>
                </td>
                <td class="summary-divider"></td>
                <td width="33%">
                    <div class="summary-item">Status Filter</div>
                    <div class="summary-value">{{ $statusLabel }}</div>
                </td>
            </tr>
        </table>

        @if($rows->isEmpty())
            <div class="empty-state">
                <div class="empty-title">No documents found</div>
                <div class="empty-text">There are no records matching the selected filters.</div>
            </div>
        @else
            <div class="table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th class="col-num">#</th>
                            <th class="col-ref">Reference / Tracking</th>
                            <th class="col-doc">Document</th>
                            <th class="col-sender">Submitted By</th>
                            <th class="col-status">Status</th>
                            <th class="col-handler">Tagged To</th>
                            <th class="col-activity">Activity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rows as $i => $doc)
                            @php
                                $submittedAt = $doc->created_at?->copy()->setTimezone('Asia/Manila')->format('m/d/Y g:i A') ?? 'N/A';
                            @endphp
                            <tr>
                                <td class="col-num">{{ $i + 1 }}</td>

                                <td class="col-ref">
                                    <span class="ref-main">{{ $doc->reference_number ?: 'N/A' }}</span>
                                    <span class="ref-sub">{{ $doc->tracking_number ?: 'N/A' }}</span>
                                </td>

                                <td class="col-doc">
                                    <div class="doc-title">{{ $doc->subject ?: 'Untitled Document' }}</div>
                                    <div class="doc-type">{{ $doc->type ?: 'No type' }}</div>
                                </td>

                                <td class="col-sender">
                                    <div class="sender-name">{{ $doc->sender_name ?: 'Guest' }}</div>
                                </td>

                                <td class="col-status">
                                    <span class="status-badge">{{ $doc->statusLabel() }}</span>
                                </td>

                                <td class="col-handler">
                                    <div class="handler-name">{{ $doc->currentHandler?->name ?? 'Unassigned' }}</div>
                                </td>

                                <td class="col-activity">
                                    <div class="activity-line">
                                        <span class="activity-label">Submitted</span>
                                        <span class="activity-value">{{ $submittedAt }}</span>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="footer">
        System-generated by DepEd DOCTRAX
    </div>
</body>
</html>