@php
    $activeFilters = [];

    if ($searchLabel !== 'All') {
        $activeFilters[] = 'Keyword: ' . $searchLabel;
    }

    if ($typeLabel !== 'All') {
        $activeFilters[] = 'Type: ' . $typeLabel;
    }

    if ($statusLabel !== 'All') {
        $activeFilters[] = 'Status: ' . $statusLabel;
    }

    if ($dateFromLabel !== 'N/A' || $dateToLabel !== 'N/A') {
        $activeFilters[] = 'Date Range: ' . $dateFromLabel . ' to ' . $dateToLabel;
    }
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Processing Report</title>
    <style>
        @page { size: A4 portrait; margin: 11mm 12mm 14mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, Arial, Helvetica, sans-serif;
            font-size: 8.8px;
            color: #1e293b;
            line-height: 1.32;
            padding-bottom: 10mm;
        }
        .report-shell {
            width: 96.5%;
            margin: 0 auto;
        }
        .head-table,
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .report-head {
            border-bottom: 1.5px solid #dbeafe;
            padding-bottom: 7px;
            margin-bottom: 6px;
        }
        .head-table td {
            vertical-align: top;
        }
        .head-right {
            width: 34%;
            text-align: right;
        }
        .eyebrow {
            font-size: 7.2px;
            font-weight: 700;
            letter-spacing: .18em;
            text-transform: uppercase;
            color: #1d4ed8;
            margin-bottom: 2px;
        }
        .title {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .subtitle {
            font-size: 9px;
            color: #64748b;
        }
        .generated-label {
            font-size: 7px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 2px;
        }
        .generated-value {
            font-size: 8.6px;
            font-weight: 700;
            color: #0f172a;
        }
        .meta-line {
            margin-bottom: 5px;
            font-size: 8.2px;
            color: #475569;
        }
        .meta-line strong {
            color: #0f172a;
        }
        .meta-sep {
            color: #94a3b8;
            padding: 0 6px;
        }
        .filters-line {
            margin-bottom: 7px;
            padding: 5px 7px;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #f8fafc;
            font-size: 8px;
            color: #475569;
        }
        .filters-line strong {
            color: #0f172a;
        }
        .section-title {
            margin-bottom: 4px;
            font-size: 7.2px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .16em;
            color: #64748b;
        }
        .data-table {
            table-layout: fixed;
        }
        .data-table thead {
            display: table-header-group;
        }
        .data-table tr {
            page-break-inside: avoid;
        }
        .data-table th {
            background: #163d7a;
            color: #fff;
            padding: 5px 5px;
            text-align: left;
            font-size: 7px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            border-right: 1px solid rgba(255, 255, 255, .14);
        }
        .data-table th:last-child {
            border-right: none;
        }
        .data-table td {
            padding: 5px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
            font-size: 8px;
            word-break: break-word;
        }
        .data-table tbody tr:nth-child(even) td {
            background: #f8fafc;
        }
        .seq {
            color: #64748b;
            font-weight: 700;
        }
        .mono-ref,
        .mono-track {
            display: block;
            font-weight: 700;
            line-height: 1.23;
        }
        .mono-ref {
            color: #0f172a;
        }
        .mono-track {
            color: #1d4ed8;
            margin-top: 2px;
        }
        .doc-title {
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 2px;
        }
        .doc-sub,
        .muted {
            color: #64748b;
            font-size: 7.6px;
        }
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 999px;
            font-size: 6.9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .05em;
            white-space: nowrap;
            background: #fff7ed;
            color: #c2410c;
        }
        .activity-line {
            margin-bottom: 2px;
            display: flex;
            align-items: flex-start;
            gap: 5px;
        }
        .activity-line:last-child {
            margin-bottom: 0;
        }
        .activity-label {
            display: inline-block;
            min-width: 48px;
            white-space: nowrap;
            flex: 0 0 48px;
            font-size: 6.9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #64748b;
        }
        .activity-value {
            font-size: 7.8px;
            color: #0f172a;
            flex: 1 1 auto;
        }
        .empty-state {
            border: 1px dashed #cbd5e1;
            border-radius: 8px;
            padding: 15px 14px;
            text-align: center;
            color: #64748b;
            background: #f8fafc;
        }
        .footer {
            position: fixed;
            left: 12mm;
            right: 12mm;
            bottom: -7mm;
            padding-top: 4px;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 7.4px;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="report-shell">
        <div class="report-head">
            <table class="head-table">
                <tr>
                    <td>
                        <div class="eyebrow">DepEd DOCTRAX</div>
                        <div class="title">Processing Report</div>
                        <div class="subtitle">{{ $officeName }}</div>
                    </td>
                    <td class="head-right">
                        <div class="generated-label">Generated</div>
                        <div class="generated-value">{{ $generatedAt }}</div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="meta-line">
            <strong>{{ $rows->count() }}</strong> record(s)
            <span class="meta-sep">|</span>
            Date Basis: <strong>{{ $dateFieldLabel }}</strong>
            @if($statusLabel !== 'All')
                <span class="meta-sep">|</span>
                Status: <strong>{{ $statusLabel }}</strong>
            @endif
        </div>

        @if(!empty($activeFilters))
            <div class="filters-line">
                <strong>Applied Filters:</strong> {{ implode(' | ', $activeFilters) }}
            </div>
        @endif

        <div class="section-title">Document Listing</div>

        @if($rows->isEmpty())
            <div class="empty-state">No documents matched the selected filters for this PDF export.</div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:4%">#</th>
                        <th style="width:18%">Reference / Tracking</th>
                        <th style="width:22%">Document</th>
                        <th style="width:12%">Submitted By</th>
                        <th style="width:10%">Status</th>
                        <th style="width:11%">Tagged To</th>
                        <th style="width:23%">Activity</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rows as $i => $doc)
                        @php
                            $submittedAt = $doc->created_at?->copy()->setTimezone('Asia/Manila')->format('m/d/Y g:i A') ?? 'N/A';
                            $updatedAt = $doc->last_action_at?->copy()->setTimezone('Asia/Manila')->format('m/d/Y g:i A') ?? 'N/A';
                        @endphp
                        <tr>
                            <td class="seq">{{ $i + 1 }}</td>
                            <td>
                                <span class="mono-ref">{{ $doc->reference_number ?: 'N/A' }}</span>
                                <span class="mono-track">{{ $doc->tracking_number ?: 'N/A' }}</span>
                            </td>
                            <td>
                                <div class="doc-title">{{ $doc->subject ?: 'Untitled Document' }}</div>
                                <div class="doc-sub">{{ $doc->type ?: 'No type specified' }}</div>
                            </td>
                            <td>{{ $doc->sender_name ?: 'Guest' }}</td>
                            <td><span class="badge">{{ $doc->statusLabel() }}</span></td>
                            <td>{{ $doc->currentHandler?->name ?? 'Unassigned' }}</td>
                            <td>
                                <div class="activity-line"><span class="activity-label">Submitted</span><span class="activity-value">{{ $submittedAt }}</span></div>
                                <div class="activity-line"><span class="activity-label">Updated</span><span class="activity-value">{{ $updatedAt }}</span></div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="footer">This report is system-generated by DepEd DOCTRAX.</div>
</body>
</html>
