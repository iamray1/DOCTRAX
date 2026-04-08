<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - DepEd DOCTRAX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">


    <style>
        :root {
            --primary: #0056b3;
            --primary-dark: #004494;
            --primary-gradient: linear-gradient(135deg, #0056b3 0%, #004494 100%);
            --accent: #fca311;
            --text-dark: #1b263b;
            --text-muted: #64748b;
            --white: #ffffff;
            --bg: #f0f2f5;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html { overflow-y: scroll; }

        body {
            background: var(--bg);
            font-family: 'Poppins', sans-serif;
            -webkit-font-smoothing: antialiased;
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ─── Sidebar (matches office/admin dashboard) ─── */
        .sidebar{position:fixed;top:0;left:0;width:240px;height:100vh;background:#0056b3;display:flex;flex-direction:column;z-index:200;transform:translateX(-100%);transition:transform .28s cubic-bezier(.4,0,.2,1)}
        .sidebar.open{transform:translateX(0)}
        .sb-brand{padding:22px 20px 18px;border-bottom:1px solid rgba(255,255,255,.12);text-align:center}
        .sb-brand img{width:64px;height:64px;margin-bottom:8px}
        .sb-brand h2{font-size:18px;font-weight:700;color:#fff;margin-bottom:2px}
        .sb-brand small{font-size:11px;color:rgba(255,255,255,.65);display:block}
        .sb-nav{flex:1;padding:12px 0;overflow-y:auto}
        .sb-nav a{display:flex;align-items:center;gap:11px;padding:11px 20px;color:rgba(255,255,255,.78);text-decoration:none;font-size:13px;font-weight:500;transition:background .15s,color .15s}
        .sb-nav a:hover,.sb-nav a.active{background:rgba(255,255,255,.14);color:#fff}
        .sb-nav a i{width:16px;text-align:center}
        .sb-nav .nav-section{padding:10px 20px 4px;font-size:9px;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.4);font-weight:600}
        .sb-footer{padding:14px 20px;border-top:1px solid rgba(255,255,255,.12)}
        .sb-user{display:flex;align-items:center;gap:10px}
        .sb-avatar{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0}
        .sb-user-info small{font-size:10px;color:rgba(255,255,255,.55);display:block}
        .sb-user-info span{font-size:12px;font-weight:600;color:#fff}
        .btn-logout{display:flex;align-items:center;gap:7px;margin-top:8px;padding:8px 14px;background:rgba(255,255,255,.1);border:none;border-radius:8px;color:rgba(255,255,255,.8);font-size:12px;cursor:pointer;font-family:Poppins,sans-serif;width:100%;justify-content:center;transition:background .2s}
        .btn-logout:hover{background:rgba(220,38,38,.75)}

        /* ─── Mobile top bar ─── */
        .mob-topbar{display:flex;position:sticky;top:0;z-index:100;background:#0056b3;padding:14px 18px;align-items:center;justify-content:space-between;gap:14px;box-shadow:0 2px 8px rgba(0,0,0,.1)}
        .mob-hamburger{background:none;border:none;cursor:pointer;display:flex;flex-direction:column;gap:5px;z-index:1001;user-select:none;padding:4px}
        .mob-hamburger span{height:2px;width:24px;background:#fff;border-radius:2px;transition:all .4s ease}
        .mob-hamburger.toggle span:nth-child(1){transform:rotate(-45deg) translate(-4px,5px)}
        .mob-hamburger.toggle span:nth-child(2){opacity:0}
        .mob-hamburger.toggle span:nth-child(3){transform:rotate(45deg) translate(-4px,-5px)}
        .mob-brand{flex:1;display:flex;flex-direction:column;color:#fff;gap:4px}
        .mob-brand .brand-subtitle{font-size:clamp(10px,2.4vw,11px);font-weight:500;opacity:.88;text-transform:uppercase;letter-spacing:2.4px;line-height:1.1}
        .mob-brand h1{font-size:clamp(18px,4.8vw,22px);font-weight:700;margin:0;line-height:1.08}
        .mob-brand .brand-caption{font-size:clamp(11px,2.9vw,13px);font-weight:300;opacity:.9;line-height:1.18}
        .mob-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:199}
        .mob-overlay.open{display:block}

        /* ─── Main Content Area ─── */
        .main{margin-left:0;flex:1;display:flex;flex-direction:column}

        /* ─── Main Content ─── */
        .dash-wrapper {
            max-width: 1140px;
            width: 100%;
            margin: 0 auto;
            padding: 28px 24px 48px;
        }

        /* ─── Top Bar ─── */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 28px;
        }

        .greeting-section h1 {
            font-size: 22px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 2px;
        }

        .greeting-section p {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: 400;
        }

        /* ─── Live Clock ─── */
        .live-clock {
            display: flex;
            align-items: center;
            gap: 14px;
            background: var(--white);
            padding: 10px 18px;
            border-radius: 8px;
            border: 1px solid var(--border);
        }

        .clock-time-display {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dark);
            font-variant-numeric: tabular-nums;
            line-height: 1;
            white-space: nowrap;
        }

        #c-h, #c-m {
            display: inline-block;
            width: 2ch;
            text-align: center;
        }

        .clock-time-display .seconds {
            font-size: 14px;
            color: #9ca3af;
            font-weight: 600;
            display: inline-block;
            width: 2ch;
            text-align: center;
        }

        .clock-time-display .period {
            font-size: 11px;
            font-weight: 600;
            color: var(--text-muted);
            margin-left: 3px;
            vertical-align: top;
        }

        .clock-sep {
            width: 1px;
            height: 28px;
            background: var(--border);
        }

        .clock-date-display {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 400;
            line-height: 1.4;
        }

        .clock-date-display .day {
            font-weight: 600;
            color: var(--text-dark);
            display: block;
        }

        /* ─── Stats Cards ─── */
        .stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: var(--white);
            border-radius: 10px;
            padding: 20px 22px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .s-icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }

        .s-icon.blue,
        .s-icon.orange,
        .s-icon.green { background: rgba(0, 86, 179, 0.1); color: var(--primary); }

        .s-num {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1;
            margin-bottom: 2px;
        }

        .s-label { font-size: 13px; color: var(--text-muted); font-weight: 400; }

        /* ─── Content Grid ─── */
        .grid {
            display: grid;
            grid-template-columns: 1.7fr 1fr;
            gap: 20px;
            align-items: start;
        }

        .panel {
            background: var(--white);
            border-radius: 10px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .panel-fixed {
            display: flex;
            flex-direction: column;
            align-self: start;
            height: 560px;
        }

        .panel-scroll-body {
            flex: 1;
            min-height: 0;
            overflow: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }

        .panel-scroll-body .dtable th {
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .panel-actions {
            display: flex;
            flex-direction: column;
            align-self: start;
            height: auto;
        }

        .panel-actions .actions-list {
            flex: none;
            min-height: auto;
            overflow: visible;
        }

        .panel-fixed .empty-state,
        .panel-fixed .mob-cards {
            flex: 1;
            min-height: 0;
        }

        .panel-fixed .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .recent-docs-panel .panel-head {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            gap: 12px;
            flex-wrap: wrap;
        }

        .recent-docs-panel .panel-head-left {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .table-doc-count {
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
        }

        .recent-docs-panel .dtable {
            width: 100%;
            table-layout: fixed;
        }

        .cell-ellipsis {
            display: block;
            max-width: 100%;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .recent-docs-panel .panel-scroll-body {
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-gutter: stable;
        }

        .recent-docs-panel .dtable th {
            text-align: left;
            padding: 10px 10px;
            font-size: 10.5px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .6px;
            background: #fff;
            border-bottom: 1px solid var(--border);
        }

        .recent-docs-panel .dtable td {
            padding: 10px 10px;
            font-size: 13px;
            color: var(--text-dark);
            border-top: none;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .recent-docs-panel .dtable tbody tr {
            cursor: pointer;
        }

        .recent-docs-panel .dtable tbody tr:hover td {
            background: #f8faff;
        }

        .recent-docs-panel .dtable tbody tr:last-child td {
            border-bottom: none;
        }

        .recent-docs-panel .dtable th,
        .recent-docs-panel .dtable td {
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .recent-docs-panel .pill {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            white-space: nowrap;
        }

        .recent-docs-panel .td-action {
            width: 40px;
            text-align: center;
        }

        .recent-docs-panel .dtable th.td-action,
        .recent-docs-panel .dtable td.td-action {
            padding-left: 2px;
            padding-right: 8px;
        }

        .recent-docs-panel .row-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            border-radius: 6px;
            color: #94a3b8;
            transition: all .15s;
            flex-shrink: 0;
        }

        .recent-docs-panel .dtable tbody tr:hover .row-arrow,
        .recent-docs-panel .mob-card:hover .mob-card-arrow {
            background: var(--primary);
            color: #fff;
        }

        .recent-docs-panel .col-ref { width: 24%; }
        .recent-docs-panel .col-subject { width: 36%; }
        .recent-docs-panel .col-status { width: 18%; }
        .recent-docs-panel .col-date { width: 16%; }
        .recent-docs-panel .col-action { width: 44px; }

        .recent-docs-panel .t-ref {
            font-family: monospace;
            font-size: 12px;
            font-weight: 600;
            color: var(--primary);
            white-space: nowrap;
            min-width: 0;
        }

        .recent-docs-panel .t-subject .cell-ellipsis {
            max-width: 100%;
        }

        .recent-docs-panel .t-subject {
            min-width: 0;
        }

        .recent-docs-panel .t-status {
            white-space: nowrap;
            min-width: 0;
        }

        .recent-docs-panel .t-date {
            font-size: 11px;
            color: #94a3b8;
            white-space: nowrap;
            min-width: 0;
        }

        .recent-docs-panel .mob-cards {
            display: none;
        }

        .recent-docs-panel .mob-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: box-shadow .15s, border-color .15s;
        }

        .recent-docs-panel .mob-card:hover {
            border-color: var(--primary);
            box-shadow: 0 2px 8px rgba(0,86,179,.08);
        }

        .recent-docs-panel .mob-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
            gap: 10px;
        }

        .recent-docs-panel .mob-card-ref {
            font-size: 12px;
            font-weight: 600;
            color: var(--primary);
            font-family: monospace;
        }

        .recent-docs-panel .mob-card-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 6px;
            color: #94a3b8;
            font-size: 12px;
            flex-shrink: 0;
        }

        .recent-docs-panel .mob-card-subject {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 10px;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .recent-docs-panel .mob-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
            font-size: 12px;
            color: var(--text-muted);
            align-items: center;
        }

        .recent-docs-panel .mob-card-date {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-size: 11px;
            color: #94a3b8;
        }

        .recent-docs-panel .mob-card-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .recent-docs-panel .mob-card-row i {
            font-size: 11px;
            opacity: .75;
            flex-shrink: 0;
        }

        .panel-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            border-bottom: 1px solid #f1f5f9;
        }

        .panel-title {
            font-size: 17px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .panel-link {
            font-size: 13px;
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: color 0.15s;
        }

        .panel-link:hover { color: var(--primary-dark); }

        /* ─── Table ─── */
        .dtable { width: 100%; border-collapse: collapse; }

        .dtable th {
            text-align: left;
            padding: 10px 24px;
            font-size: 11px;
            font-weight: 600;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: #fafbfc;
        }

        .dtable td {
            padding: 13px 24px;
            border-top: 1px solid #f1f5f9;
            font-size: 13px;
            color: #374151;
        }

        .dtable tbody tr { transition: background 0.1s; }
        .dtable tbody tr:hover { background: #f8fafc; }

        .t-num { font-weight: 600; color: var(--primary); font-size: 13px; }

        .pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .pill.pending,
        .pill.forwarded,
        .pill.processing,
        .pill.completed,
        .pill.other { background: #fff7ed; color: #c2410c; }

        .t-date { font-size: 12px; color: #94a3b8; }

        .empty-state {
            padding: 48px 24px;
            text-align: center;
            color: #94a3b8;
        }

        .empty-state i { font-size: 32px; margin-bottom: 10px; display: block; color: #cbd5e1; }
        .empty-state p { font-size: 14px; }

        /* ─── Quick Actions ─── */
        .actions-list { padding: 10px; display: flex; flex-direction: column; gap: 6px; }

        .act {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid transparent;
            transition: background 0.15s;
        }

        .act:hover {
            background: #f8fafc;
            border-color: var(--border);
        }

        .act-icon {
            width: 38px; height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .act-body { flex: 1; }
        .act-title { font-size: 14px; font-weight: 600; color: var(--text-dark); margin-bottom: 2px; }
        .act-desc { font-size: 12px; color: #94a3b8; line-height: 1.4; }
        .act-arrow { color: #cbd5e1; font-size: 13px; }
        .act:hover .act-arrow { color: var(--text-muted); }

        /* ─── Footer ─── */
        .dash-footer {
            width: 100%;
            background: var(--white);
            border-top: 1px solid var(--border);
            padding: 20px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #94a3b8;
            margin-top: auto;
        }

        .footer-left {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .footer-left img {
            height: 22px;
            width: auto;
            opacity: 0.5;
        }

        .footer-right {
            font-size: 11px;
            color: #b0b8c4;
        }

        @media (max-width: 768px) {
            .dash-footer {
                flex-direction: column;
                gap: 6px;
                text-align: center;
                padding: 16px 5%;
            }
        }

        /* ─── Animations ─── */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .anim { animation: fadeIn 0.25s ease forwards; }

        /* ─── Responsive ─── */
        @media (max-width: 1024px) {
            .grid { grid-template-columns: 1fr; }
            .panel-fixed { height: min(72vh, 560px); }
            .panel-scroll-body { max-height: none; }
            .panel-actions .actions-list { overflow: visible; }
        }

        @media (max-width: 768px) {
            .dash-wrapper { padding: 20px 16px 40px; }

            .top-bar {
                flex-direction: column;
                align-items: flex-start;
                gap: 14px;
            }

            .greeting-section h1 { font-size: 20px; }

            .live-clock { width: 100%; }

            .stats { grid-template-columns: 1fr; }

            .dtable-wrap { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .dtable th, .dtable td { white-space: nowrap; padding: 10px 14px; font-size: 12px; }
            .dtable th:first-child, .dtable td:first-child { position: sticky; left: 0; background: #fff; z-index: 1; }
            .dtable thead th:first-child { background: #fafbfc; }
            .t-num { font-size: 11px; }
            .pill { font-size: 11px; padding: 2px 8px; }
            .t-date { font-size: 11px; }
            .panel-head { padding: 14px 16px; }
            .panel-title { font-size: 14px; }
            .panel-link { font-size: 12px; }
            .panel-scroll-body { max-height: none; }
            .panel-fixed { height: min(68vh, 520px); }
            .panel-actions .actions-list { overflow: visible; }
        }

        @media (max-width: 900px) {
            .recent-docs-panel .dtable-wrap { display: none; }
            .recent-docs-panel .mob-cards {
                display: block;
                padding: 10px 12px;
                overflow-y: auto;
                overscroll-behavior: contain;
                -webkit-overflow-scrolling: touch;
            }
        }

        @media (max-width: 400px) {
            .greeting-section h1 { font-size: 18px; }
        }
        /* ─── Pickup notification ─── */
        @keyframes blink-pulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.4);opacity:.35}}
        .blink-dot{display:inline-block;width:9px;height:9px;border-radius:50%;background:#ea580c;animation:blink-pulse 1.2s ease-in-out infinite;vertical-align:middle;flex-shrink:0}
        .pill.for_pickup{background:#fff7ed;color:#c2410c;font-weight:700}
        .pickup-alert strong{color:#9a3412}

        /* ─── Pickup banner ─── */
        .pickup-banner{background:#fff;border:1.5px solid var(--border);border-radius:14px;padding:18px 20px;margin-bottom:20px;box-shadow:0 2px 12px rgba(0,0,0,.04)}
        .pickup-banner-title{font-size:17px;font-weight:700;color:var(--text-dark);margin-bottom:14px;display:flex;align-items:center;gap:9px}
        .pickup-banner-title i{color:#16a34a;font-size:18px}
        .pickup-banner-title .pickup-count{background:#dcfce7;color:#15803d;font-size:12px;font-weight:700;padding:2px 9px;border-radius:20px;margin-left:2px}
        .pickup-doc-item{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:12px 14px;background:#f8fafc;border:1.5px solid var(--border);border-radius:10px;margin-bottom:8px;transition:border-color .15s,box-shadow .15s}
        .pickup-doc-item:last-child{margin-bottom:0}
        .pickup-doc-item:hover{border-color:#bbf7d0;box-shadow:0 2px 8px rgba(22,163,74,.08)}
        .pickup-doc-ref{font-size:12px;font-weight:700;color:var(--primary);font-family:monospace;letter-spacing:.5px}
        .pickup-doc-subject{font-size:13px;color:var(--text-dark);font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:1px}
        .btn-confirm-sm{padding:8px 16px;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;border:none;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;font-family:Poppins,sans-serif;white-space:nowrap;transition:all .2s;flex-shrink:0;box-shadow:0 2px 6px rgba(22,163,74,.2)}
        .btn-confirm-sm:hover{background:linear-gradient(135deg,#15803d,#166534);box-shadow:0 3px 10px rgba(22,163,74,.3)}
        .btn-confirm-sm:disabled{opacity:.6;cursor:not-allowed;transform:none;box-shadow:none}

        /* ─── Pickup modal ─── */
        .modal-backdrop{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:500;align-items:center;justify-content:center;padding:16px}
        .modal-backdrop.show{display:flex}
        .modal-box{background:#fff;border-radius:16px;max-width:420px;width:100%;padding:28px 24px;box-shadow:0 20px 60px rgba(0,0,0,.2);text-align:center;animation:modalIn .18s ease}
        @keyframes modalIn{from{opacity:0;transform:scale(.96)}to{opacity:1;transform:scale(1)}}
        .modal-icon-wrap{display:none}
        .modal-box h3{font-size:17px;font-weight:700;color:var(--text-dark);margin-bottom:8px}
        .modal-box p{font-size:13px;color:var(--text-muted);line-height:1.6;margin-bottom:22px;text-align:left}
        .modal-actions{display:flex;gap:10px;justify-content:center}
        .modal-actions button{padding:9px 20px;border-radius:9px;font-size:13px;font-weight:600;cursor:pointer;font-family:Poppins,sans-serif;border:1.5px solid var(--border);background:#fff;color:var(--text-dark);transition:all .2s}
        .modal-actions button:hover{background:#f1f5f9}
        .modal-actions .modal-confirm{background:#ea580c;color:#fff;border-color:#ea580c}
        .modal-actions .modal-confirm:hover{background:#c2410c}
        .modal-actions .modal-confirm:disabled{opacity:.6;cursor:not-allowed}

        /* ─── Recent document drawer ─── */
        .drawer-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:400;opacity:0;pointer-events:none;transition:opacity .25s}
        .drawer-overlay.open{opacity:1;pointer-events:all}
        .drawer{position:fixed;top:0;right:0;height:100vh;width:460px;max-width:100vw;background:#fff;z-index:401;box-shadow:-4px 0 24px rgba(0,0,0,.12);display:flex;flex-direction:column;transform:translateX(100%);transition:transform .28s cubic-bezier(.4,0,.2,1)}
        .drawer.open{transform:translateX(0)}
        .drawer-head{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;gap:12px}
        .drawer-head-info{flex:1;min-width:0}
        .drawer-head h3{font-size:16px;font-weight:700;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:4px}
        .drawer-ref{font-size:13px;color:var(--text-muted);font-family:monospace;letter-spacing:.4px;margin-bottom:2px}
        .drawer-track{font-size:11px;color:var(--text-muted);font-family:monospace;letter-spacing:.4px;margin-bottom:4px}
        .drawer-close{width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:#f8fafc;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:14px;flex-shrink:0;transition:all .15s}
        .drawer-close:hover{background:#fee2e2;color:#dc2626;border-color:#fca5a5}
        .drawer-body{flex:1;overflow-y:auto}
        .drawer-tl-head{padding:14px 20px 6px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);display:flex;align-items:center;gap:6px}
        .drawer-timeline{padding:10px 20px 24px}
        .tl{position:relative}
        .tl::before{content:'';position:absolute;left:7px;top:8px;bottom:8px;width:2px;background:var(--border);z-index:-1}
        .tl-item{position:relative;margin-bottom:20px;padding-left:24px}
        .tl-item:last-child{margin-bottom:0}
        .tl-dot{width:16px;height:16px;border-radius:50%;border:2.5px solid #fff;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0}
        .tl-dot.c-active{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.c-done{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.c-warn{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.c-danger{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.c-latest{background:#f59e0b;box-shadow:0 0 0 2px #f59e0b}
        .tl-action{font-size:12px;font-weight:700;color:#1b263b}
        .tl-meta{font-size:12px;color:#64748b;margin:2px 0}
        .tl-remarks{font-size:12px;color:#64748b;background:#f8fafc;border-left:3px solid var(--border);padding:5px 9px;border-radius:4px;margin-top:5px}
        .tl-office-hdr{display:flex;align-items:center;font-size:13px;font-weight:700;color:var(--text-dark);text-transform:none;letter-spacing:0;margin:18px 0 8px -7px;padding-left:7px;padding-bottom:6px;position:relative}
        .tl-office-hdr::after{content:'';position:absolute;left:21px;right:0;bottom:0;height:1.5px;background:var(--border)}
        .tl-office-hdr:first-child{margin-top:0}
        .tl-item.voided{opacity:.45}
        .tl-item.voided .tl-action,.tl-item.voided .tl-meta,.tl-item.voided .tl-remarks{text-decoration:line-through;color:#94a3b8}
        .tl-dot.c-voided{background:#cbd5e1;box-shadow:0 0 0 2px #cbd5e1}
        .tl-void-badge{display:inline-block;font-size:9px;font-weight:700;letter-spacing:.6px;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;border-radius:4px;padding:1px 5px;margin-left:4px;vertical-align:middle;text-decoration:none !important}
        .drawer-loader{display:flex;align-items:center;justify-content:center;padding:48px;flex-direction:column;gap:12px;color:var(--text-muted);font-size:13px}
        .drawer-action-bar{padding:16px 20px;border-top:2px solid #fed7aa;background:#fff7ed;flex-shrink:0}
        .pickup-notice{font-size:12.5px;color:#9a3412;margin-bottom:12px;display:flex;align-items:flex-start;gap:8px;line-height:1.5}
        .pickup-notice i{margin-top:2px;flex-shrink:0}
        .btn-confirm-pickup{width:100%;padding:11px 16px;background:#ea580c;color:#fff;border:none;border-radius:9px;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:background .2s}
        .btn-confirm-pickup:hover{background:#c2410c}
        .btn-confirm-pickup:disabled{opacity:.6;cursor:not-allowed}
        @media (max-width: 768px) {
            .drawer{width:100%;max-width:100%}
        }
    </style>
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
</head>
<body>

    @php
        $isRep       = ($user->account_type ?? '') === 'representative';
        $navOfficeName = $isRep ? ($user->representativeOfficeName() ?? '') : '';
        $navRepName    = $isRep ? $user->representativeDisplayName() : $user->name;
        $navDisplayName = $isRep ? ($navOfficeName ?: 'Representative') : explode(' ', trim($user->name))[0];
        $navDisplayRole = $isRep ? ($user->office_id ? 'Office' : 'Representative') : ucfirst($user->role ?? 'User');
        $dashboardWelcomeName = $navRepName ?: $navDisplayName;
        $dashboardContextLabel = $navOfficeName ?: ($isRep ? 'Representative Dashboard' : 'Personal Dashboard');
        $sidebarAvatarLabel = $isRep ? ($navRepName ?: $user->name) : $navDisplayName;
        $pickupCount = $stats['for_pickup'] ?? 0;
    @endphp

<!-- Mobile top bar -->
<div class="mob-topbar">
    <button class="mob-hamburger" id="mobHamBtn" type="button" onclick="toggleSidebar()" aria-label="Menu"><span></span><span></span><span></span></button>
    <div class="mob-brand">
        <span class="brand-subtitle">Department of Education</span>
        <h1>CITY OF SAN JOSE DEL MONTE</h1>
        <span class="brand-caption">Document Tracking System &mdash; DOCTRAX</span>
    </div>
</div>
<div class="mob-overlay" id="mobOverlay" onclick="closeSidebar()"></div>

<!-- ─── Sidebar ─── -->
<div class="sidebar" id="mainSidebar">
    <div class="sb-brand">
        <img src="{{ asset('images/DOCTRAXLOGO.svg') }}" alt="DOCTRAX Logo">
        <h2>DOCTRAX</h2>
        <small>DepEd Document Tracking System</small>
    </div>
    <nav class="sb-nav">
        <span class="nav-section">Overview</span>
        <a href="/dashboard" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
        <span class="nav-section">Documents</span>
        <a href="/submit"><i class="fas fa-paper-plane"></i> Submit Document</a>
        <a href="/my-documents"><i class="fas fa-folder-open"></i> My Documents</a>
        <a href="/track"><i class="fas fa-search"></i> Track Document</a>
        <span class="nav-section">Account</span>
        <a href="/profile"><i class="fas fa-user-cog"></i> My Profile</a>
    </nav>
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">{{ strtoupper(substr($sidebarAvatarLabel, 0, 1)) }}</div>
            <div class="sb-user-info">
                <small>{{ ($isRep && $navRepName) ? $navRepName : $navDisplayRole }}</small>
                <span>{{ $navDisplayName }}</span>
            </div>
        </div>
        <button onclick="logout()" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>
</div>

<!-- ─── Main Content ─── -->
<div class="main">

    <!-- ─── Dashboard Content ─── -->
    <div class="dash-wrapper">

        <!-- Top Bar -->
        <div class="top-bar anim">
            <div class="greeting-section">
                <h1>Welcome back, {{ $dashboardWelcomeName }}!</h1>
                <p>{{ $dashboardContextLabel }} &mdash; here's your document overview.</p>
            </div>

            <div class="live-clock">
                <div class="clock-time-display">
                    <span id="c-h">--</span>:<span id="c-m">--</span>:<span class="seconds" id="c-s">--</span>
                    <span class="period" id="c-p">--</span>
                </div>
                <div class="clock-sep"></div>
                <div class="clock-date-display">
                    <span class="day" id="c-day">Loading...</span>
                    <span id="c-date"></span>
                </div>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats">
            <div class="stat-card blue anim">
                <div class="s-icon blue"><i class="fas fa-folder-open"></i></div>
                <div class="s-data">
                    <div class="s-num" id="stat-total">{{ \App\Support\UiNumber::compact($stats['total']) }}</div>
                    <div class="s-label">Total Documents</div>
                </div>
            </div>
            <div class="stat-card orange anim">
                <div class="s-icon orange"><i class="fas fa-clock"></i></div>
                <div class="s-data">
                    <div class="s-num" id="stat-pending">{{ \App\Support\UiNumber::compact($stats['pending']) }}</div>
                    <div class="s-label">Pending</div>
                </div>
            </div>
            <div class="stat-card green anim">
                <div class="s-icon green"><i class="fas fa-check-circle"></i></div>
                <div class="s-data">
                    <div class="s-num" id="stat-completed">{{ \App\Support\UiNumber::compact($stats['completed']) }}</div>
                    <div class="s-label">Completed</div>
                </div>
            </div>
        </div>
        {{-- ─── For Pickup Banner ─── --}}
        @if(!empty($pickupDocs) && $pickupDocs->isNotEmpty())
        <div class="pickup-banner anim">
            <div class="pickup-banner-title">
                Ready for Pickup <span class="pickup-count">{{ \App\Support\UiNumber::compact($pickupDocs->count()) }}</span>
            </div>
            <div class="pickup-doc-list">
                @foreach($pickupDocs as $pDoc)
                <div class="pickup-doc-item">
                    <div class="pickup-doc-info">
                        <div class="pickup-doc-ref">{{ $pDoc->reference_number }}</div>
                        <div class="pickup-doc-subject">{{ $pDoc->subject }}</div>
                    </div>
                    <button class="btn-confirm-sm"
                            onclick="openPickupConfirm('{{ $pDoc->reference_number }}', this)">
                        Confirm Receipt
                    </button>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        <!-- Grid -->
        <div class="grid">

            <!-- Recent Documents -->
            <div class="panel panel-fixed recent-docs-panel anim" id="recentDocumentsPanel">
                <div class="panel-head">
                    <div class="panel-head-left">
                        <div class="panel-title">Recent Documents</div>
                        <span class="table-doc-count">{{ \App\Support\UiNumber::compact($recentDocs->count()) }} showing</span>
                    </div>
                    <a href="/my-documents" class="panel-link">View all <i class="fas fa-arrow-right" style="font-size:11px"></i></a>
                </div>

                @if($recentDocs->count() > 0)
                <div class="dtable-wrap panel-scroll-body">
                <table class="dtable">
                    <colgroup>
                        <col class="col-ref">
                        <col class="col-subject">
                        <col class="col-status">
                        <col class="col-date">
                        <col class="col-action">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Reference #</th>
                            <th>Subject</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="td-action"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentDocs as $doc)
                        @php
                            $recentLookup = $doc->reference_number ?: ($doc->tracking_number ?: '');
                            $recentTracking = $doc->tracking_number ?: ($doc->reference_number ?: '');
                        @endphp
                        <tr onclick='openRecentDocDetail(@json($recentLookup), @json($recentTracking))'>
                            <td class="t-ref"><div class="cell-ellipsis" title="{{ $recentLookup }}">{{ $recentLookup ?: 'N/A' }}</div></td>
                            <td class="t-subject"><div class="cell-ellipsis" style="font-weight:600" title="{{ $doc->subject }}">{{ $doc->subject }}</div></td>
                            <td class="t-status">
                                @php
                                    $sc = match($doc->status) {
                                        'submitted', 'received' => 'pending',
                                        'in_review', 'on_hold' => 'processing',
                                        'completed', 'for_pickup' => 'completed',
                                        default => 'other',
                                    };
                                @endphp
                                <span style="display:inline-flex;align-items:center;gap:5px">
                                    <span class="pill {{ $sc }}">{{ $doc->statusLabel() }}</span>
                                    @if($doc->status === 'for_pickup')
                                        <span class="blink-dot"></span>
                                    @endif
                                </span>
                            </td>
                            <td class="t-date">{{ $doc->created_at->format('M d, Y') }}</td>
                            <td class="td-action"><span class="row-arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                <div class="mob-cards">
                    @foreach($recentDocs as $doc)
                    @php
                        $recentLookup = $doc->reference_number ?: ($doc->tracking_number ?: '');
                        $recentTracking = $doc->tracking_number ?: ($doc->reference_number ?: '');
                    @endphp
                    <div class="mob-card" onclick='openRecentDocDetail(@json($recentLookup), @json($recentTracking))'>
                        <div class="mob-card-top">
                            <div class="mob-card-ref">{{ $recentLookup ?: 'N/A' }}</div>
                            <span class="mob-card-arrow"><i class="fas fa-chevron-right"></i></span>
                        </div>
                        <div class="mob-card-subject">{{ $doc->subject }}</div>
                        <div class="mob-card-meta">
                            @php
                                $sc = match($doc->status) {
                                    'submitted', 'received' => 'pending',
                                    'in_review', 'on_hold' => 'processing',
                                    'completed', 'for_pickup' => 'completed',
                                    default => 'other',
                                };
                            @endphp
                            <span class="pill {{ $sc }}">{{ $doc->statusLabel() }}</span>
                            <span class="mob-card-date"><i class="fas fa-calendar"></i>{{ $doc->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="mob-card-row">
                            <i class="fas fa-building"></i>
                            <span class="cell-ellipsis" title="{{ $doc->currentOffice?->name ?? 'Awaiting routing' }}">{{ $doc->currentOffice?->name ?? 'Awaiting routing' }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No documents submitted yet.</p>
                </div>
                @endif
            </div>

            <!-- Quick Actions -->
            <div class="panel panel-actions anim" id="quickActionsPanel">
                <div class="panel-head">
                    <div class="panel-title">Quick Actions</div>
                </div>
                <div class="actions-list">
                    <a href="/" class="act">
                        <div class="act-icon" style="background:rgba(0,86,179,0.1);color:var(--primary);"><i class="fas fa-home"></i></div>
                        <div class="act-body">
                            <div class="act-title">Home</div>
                            <div class="act-desc">Go to the main landing page</div>
                        </div>
                        <i class="fas fa-chevron-right act-arrow"></i>
                    </a>
                    <a href="/submit" class="act">
                        <div class="act-icon" style="background:rgba(0,86,179,0.1);color:var(--primary);"><i class="fas fa-plus"></i></div>
                        <div class="act-body">
                            <div class="act-title">Submit Document</div>
                            <div class="act-desc">File a new document</div>
                        </div>
                        <i class="fas fa-chevron-right act-arrow"></i>
                    </a>
                    <a href="/track" class="act">
                        <div class="act-icon" style="background:rgba(0,86,179,0.1);color:var(--primary);"><i class="fas fa-search"></i></div>
                        <div class="act-body">
                            <div class="act-title">Track Document</div>
                            <div class="act-desc">Check document status</div>
                        </div>
                        <i class="fas fa-chevron-right act-arrow"></i>
                    </a>
                    <a href="/profile" class="act">
                        <div class="act-icon" style="background:rgba(0,86,179,0.1);color:var(--primary);"><i class="fas fa-user-cog"></i></div>
                        <div class="act-body">
                            <div class="act-title">Account Settings</div>
                            <div class="act-desc">Profile &amp; password</div>
                        </div>
                        <i class="fas fa-chevron-right act-arrow"></i>
                    </a>
                    <a href="/help" class="act">
                        <div class="act-icon" style="background:rgba(0,86,179,0.1);color:var(--primary);"><i class="fas fa-question-circle"></i></div>
                        <div class="act-body">
                            <div class="act-title">Help</div>
                            <div class="act-desc">System guide &amp; FAQs</div>
                        </div>
                        <i class="fas fa-chevron-right act-arrow"></i>
                    </a>
                </div>
            </div>

        </div>

        </div>

    <footer class="dash-footer">
        <div class="footer-left">
            <span>&copy; {{ date('Y') }} DepEd Document Tracking System</span>
        </div>
        <div class="footer-right">
            Developed by Raymond Bautista
        </div>
    </footer>

</div><!-- /.main -->

    @php
        $recentDocDrawerData = [];
        foreach ($recentDocs as $doc) {
            $fallback = [
                'reference_number' => $doc->reference_number ?: $doc->tracking_number,
                'tracking_number' => $doc->tracking_number ?: $doc->reference_number,
                'subject' => $doc->subject,
                'status' => $doc->status,
                'status_label' => $doc->statusLabel(),
                'sender_name' => $doc->sender_name ?? ($user->name ?? 'Guest'),
                'type' => $doc->type ?? 'General',
                'submitted_to_office' => optional($doc->currentOffice)->name,
                'current_office' => optional($doc->currentOffice)->name ?? 'Awaiting routing',
                'current_handler' => 'Unassigned',
                'date' => optional($doc->created_at)->format('M d, Y h:i A'),
            ];
            $primaryKey = $doc->tracking_number ?: $doc->reference_number;
            if ($primaryKey) {
                $recentDocDrawerData[$primaryKey] = $fallback;
            }
            if ($doc->reference_number && $doc->reference_number !== $doc->tracking_number) {
                $recentDocDrawerData[$doc->reference_number] = $fallback;
            }
        }
    @endphp
    <script type="application/json" id="recentDocsData">@json($recentDocDrawerData)</script>

    <div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>
    <div class="drawer" id="docDrawer">
        <div class="drawer-head">
            <div class="drawer-head-info">
                <h3 id="drTitle">—</h3>
                <div class="drawer-ref" id="drRef">—</div>
                <div class="drawer-track" id="drTrack"></div>
            </div>
            <button class="drawer-close" type="button" onclick="closeDrawer()"><i class="fas fa-times"></i></button>
        </div>
        <div class="drawer-body" id="drawerBody">
            <div class="drawer-loader">
                <span class="loading-dots"><span></span></span>
                Loading details...
            </div>
        </div>
        <div class="drawer-action-bar" id="drawerActionBar" style="display:none">
            <div class="pickup-notice">
                <i class="fas fa-box-open"></i>
                <span>Your document is ready for pickup. Please confirm once you have physically received it.</span>
            </div>
            <button class="btn-confirm-pickup" id="drawerPickupBtn" type="button" onclick="openDrawerPickupConfirm()">
                <i class="fas fa-check-circle"></i> Confirm I Received This Document
            </button>
        </div>
    </div>

    {{-- ─── Pickup Confirm Modal ─── --}}
    <div class="modal-backdrop" id="pickupModal">
        <div class="modal-box">
            <h3>Confirm Document Receipt</h3>
            <p>Are you sure you have physically received this document? This action cannot be undone and will mark it as <strong>Completed</strong>.</p>
            <div class="modal-actions">
                <button onclick="closePickupModal()">Cancel</button>
                <button class="modal-confirm" id="pickupModalConfirmBtn" onclick="submitPickupConfirm()">
                    <i class="fas fa-check"></i> Yes, I Received It
                </button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var recentDocsData = JSON.parse(document.getElementById('recentDocsData').textContent || '{}');
        var _currentDrawerRef = null;

        function escapeHtml(value) {
            return String(value === null || value === undefined ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function closeDrawer() {
            document.getElementById('drawerOverlay').classList.remove('open');
            document.getElementById('docDrawer').classList.remove('open');
            document.body.style.overflow = '';
        }
        window.closeDrawer = closeDrawer;

        function dotClass(status) {
            if (status === 'cancelled' || status === 'returned') return 'c-danger';
            if (status === 'completed') return 'c-done';
            if (status === 'forwarded') return 'c-warn';
            return 'c-active';
        }

        function buildFallbackDocument(fallback, ref, tracking) {
            return {
                subject: fallback.subject || '-',
                reference_number: fallback.reference_number || ref || tracking || '-',
                tracking_number: fallback.tracking_number || tracking || ref || '-',
                status: fallback.status || 'unknown',
                status_label: fallback.status_label || 'Unknown',
                sender_name: fallback.sender_name || '-',
                type: fallback.type || '-',
                submitted_to_office: fallback.submitted_to_office || fallback.current_office || '-',
                current_office: fallback.current_office || fallback.submitted_to_office || '-',
                current_handler: fallback.current_handler || 'Unassigned',
                date: fallback.date || '-',
                routing_logs: []
            };
        }

        function renderDrawer(doc) {
            _currentDrawerRef = doc.reference_number || doc.tracking_number || null;
            var refNo = doc.reference_number || doc.tracking_number || '-';
            document.getElementById('drTitle').textContent = doc.subject || '-';
            document.getElementById('drRef').textContent = refNo;
            document.getElementById('drTrack').textContent = '';

            var actionBar = document.getElementById('drawerActionBar');
            var pickupBtn = document.getElementById('drawerPickupBtn');
            if (doc.status === 'for_pickup' && _currentDrawerRef) {
                actionBar.style.display = '';
                pickupBtn.disabled = false;
            } else {
                actionBar.style.display = 'none';
            }

            var logs = Array.isArray(doc.routing_logs) ? doc.routing_logs : [];
            var tlHtml = '';

            if (!logs.length) {
                tlHtml = '<div style="color:var(--text-muted);font-size:13px;padding:4px 0">No routing history yet.</div>';
            } else {
                var voidedIds = new Set();
                var revertStatuses = ['in_review', 'on_hold'];
                var logsChron = logs.slice().sort(function(a, b) {
                    return new Date(a.created_at) - new Date(b.created_at);
                });

                for (var vi = 0; vi < logsChron.length; vi++) {
                    if (logsChron[vi].status_after === 'for_pickup') {
                        for (var vj = vi + 1; vj < logsChron.length; vj++) {
                            if (revertStatuses.indexOf(logsChron[vj].status_after) !== -1) {
                                voidedIds.add(logsChron[vi].id);
                                break;
                            }
                        }
                    }
                }

                var prevGroupKey = null;
                Array.from(logs).reverse().forEach(function(log, idx) {
                    var isLatest = idx === 0;
                    var isVoided = voidedIds.has(log.id);
                    var dc = isVoided ? 'c-voided' : (isLatest ? 'c-latest' : dotClass(log.status_after));
                    var dotIcon = isVoided ? 'fa-ban' : (isLatest ? 'fa-arrow-up' : 'fa-check');
                    var groupKey = (log.action === 'submitted') ? '__pending__' :
                        (log.action === 'forwarded' ? (log.from_office || 'Unknown') : (log.to_office || log.from_office || 'Unknown'));
                    var groupLabel = (groupKey === '__pending__') ? 'Submitted — Pending Physical Submission' : groupKey;

                    if (groupKey !== prevGroupKey) {
                        prevGroupKey = groupKey;
                        tlHtml += '<div class="tl-office-hdr"><div class="tl-dot ' + dc + '" style="margin-right:5px"><i class="fas ' + dotIcon + '" style="font-size:5px"></i></div><span>' + escapeHtml(groupLabel) + '</span></div>';
                    }

                    var voidBadge = isVoided ? ' <span class="tl-void-badge">VOIDED</span>' : '';
                    var performerHtml = log.performed_by
                        ? '<div class="tl-action">' + escapeHtml(log.performed_by) + voidBadge + '</div>'
                        : (isVoided ? '<div class="tl-action">' + voidBadge + '</div>' : '');

                    tlHtml += '<div class="tl-item' + (isVoided ? ' voided' : '') + '">' +
                        performerHtml +
                        '<div class="tl-meta"><i class="fas fa-clock" style="margin-right:3px;font-size:10px"></i>' + escapeHtml(log.timestamp || '-') + '</div>' +
                        '<div class="tl-meta"><i class="fas fa-tasks" style="margin-right:3px;font-size:10px"></i>' + escapeHtml(log.action_label || 'Status Updated') + '</div>' +
                        (log.remarks ? '<div class="tl-remarks">' + escapeHtml(log.remarks) + '</div>' : '') +
                        '</div>';
                });
            }

            document.getElementById('drawerBody').innerHTML =
                '<div class="drawer-tl-head"><i class="fas fa-history"></i> Routing History</div>' +
                '<div class="drawer-timeline"><div class="tl">' + tlHtml + '</div></div>';
        }

        window.openRecentDocDetail = function(ref, tracking) {
            ref = (ref || '').toString().trim();
            tracking = (tracking || ref).toString().trim();

            document.getElementById('drTitle').textContent = '—';
            document.getElementById('drRef').textContent = ref || tracking || '-';
            document.getElementById('drTrack').textContent = '';
            document.getElementById('drawerActionBar').style.display = 'none';
            document.getElementById('drawerBody').innerHTML =
                '<div class="drawer-loader"><span class="loading-dots"><span></span></span>Loading details...</div>';
            document.getElementById('drawerOverlay').classList.add('open');
            document.getElementById('docDrawer').classList.add('open');
            document.body.style.overflow = 'hidden';

            var fallback = recentDocsData[tracking] || recentDocsData[ref];
            window.docTraxFetchJson('/api/track-document', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 'Accept': 'application/json' },
                timeoutMs: 15000,
                body: JSON.stringify({
                    reference_number: ref,
                    tracking_number: tracking || ref
                })
            })
            .then(function(data) {
                if (data.success && data.document) {
                    renderDrawer(data.document);
                    return;
                }
                if (fallback) {
                    renderDrawer(buildFallbackDocument(fallback, ref, tracking));
                    return;
                }
                document.getElementById('drawerBody').innerHTML =
                    '<div class="drawer-loader"><i class="fas fa-file-circle-question" style="font-size:32px;color:#cbd5e1;margin-bottom:8px"></i>Document not found.</div>';
            })
            .catch(function(error) {
                if (fallback) {
                    renderDrawer(buildFallbackDocument(fallback, ref, tracking));
                    window.showNetworkNotice('Showing basic document details from the recent documents list while the live request is unavailable.', {
                        type: 'warning',
                        duration: 5000
                    });
                    return;
                }
                document.getElementById('drawerBody').innerHTML =
                    '<div class="drawer-loader">' + escapeHtml(window.describeRequestError(error, 'Could not load document details. Please try again.')) + '</div>';
            });
        };

        window.openDrawerPickupConfirm = function() {
            if (!_currentDrawerRef) return;
            openPickupConfirm(_currentDrawerRef, document.getElementById('drawerPickupBtn'));
        };

        function syncRecentPanelHeight() {
            var recentPanel = document.getElementById('recentDocumentsPanel');
            var quickActionsPanel = document.getElementById('quickActionsPanel');
            if (!recentPanel) return;

            recentPanel.style.height = '';
            recentPanel.style.minHeight = '';

            if (!quickActionsPanel || window.innerWidth <= 1024) {
                return;
            }

            var quickActionsHeight = quickActionsPanel.offsetHeight;
            if (quickActionsHeight > 0) {
                recentPanel.style.height = quickActionsHeight + 'px';
            }
        }

        // ─── Clock ───
        function tick() {
            var n = new Date();
            var h = n.getHours(), m = n.getMinutes(), s = n.getSeconds();
            var p = h >= 12 ? 'PM' : 'AM';
            var h12 = h % 12 || 12;
            var el = document.getElementById('c-h'); if (!el) { clearInterval(clockInterval); return; }

            el.textContent = String(h12).padStart(2, '0');
            document.getElementById('c-m').textContent = String(m).padStart(2, '0');
            document.getElementById('c-s').textContent = String(s).padStart(2, '0');
            document.getElementById('c-p').textContent = p;

            var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            var mos = ['January','February','March','April','May','June','July','August','September','October','November','December'];
            document.getElementById('c-day').textContent = days[n.getDay()];
            document.getElementById('c-date').textContent = mos[n.getMonth()] + ' ' + n.getDate() + ', ' + n.getFullYear();
        }
        tick(); var clockInterval = setInterval(tick, 1000);

        // ─── Sidebar Toggle ───
        window.toggleSidebar = function() {
            var s = document.getElementById('mainSidebar');
            var o = document.getElementById('mobOverlay');
            var open = s.classList.toggle('open');
            o.classList.toggle('open', open);
            document.body.style.overflow = open ? 'hidden' : '';
            document.getElementById('mobHamBtn').classList.toggle('toggle', open);
        };
        window.closeSidebar = function() {
            document.getElementById('mainSidebar').classList.remove('open');
            document.getElementById('mobOverlay').classList.remove('open');
            document.body.style.overflow = '';
            var btn = document.getElementById('mobHamBtn'); if (btn) btn.classList.remove('toggle');
        };

        // ─── Logout ───
        window.logout = function() {
            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/api/logout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            }).then(function() {
                window.location.href = '/login';
            }).catch(function() {
                window.location.href = '/login';
            });
        };

        // ─── Live stats (refresh every 30s, silent update) ───
        function refreshStats() {
            window.docTraxFetchJson('/api/my-stats', {
                headers: { 'Accept': 'application/json' },
                timeoutMs: 10000
            })
                .then(function(d) {
                    var compactCount = window.formatCompactCount || function(v) { return String(v); };
                    document.getElementById('stat-total').textContent     = compactCount(d.total);
                    document.getElementById('stat-pending').textContent   = compactCount(d.pending);
                    document.getElementById('stat-completed').textContent = compactCount(d.completed);
                    window.clearStatusNotice('user-dashboard-stats');
                })
                .catch(function() {
                    window.setStatusNotice('user-dashboard-stats', 'Live dashboard updates are temporarily unavailable. Showing the last known counts.', {
                        type: 'warning',
                        priority: 30
                    });
                });
        }
        if (window.smartInterval) { window.smartInterval(refreshStats, 30000); }
        else { setInterval(refreshStats, 30000); }

        syncRecentPanelHeight();
        window.addEventListener('resize', syncRecentPanelHeight);
        window.addEventListener('load', syncRecentPanelHeight);

        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(syncRecentPanelHeight).catch(function() {});
        }

        if (window.ResizeObserver) {
            var quickActionsPanel = document.getElementById('quickActionsPanel');
            if (quickActionsPanel) {
                var recentPanelObserver = new ResizeObserver(function() {
                    syncRecentPanelHeight();
                });
                recentPanelObserver.observe(quickActionsPanel);
            }
        }

        // ─── Pickup confirmation ───
        var _pickupRef = null;
        var _pickupTriggerBtn = null;

        window.openPickupConfirm = function(ref, btn) {
            _pickupRef = ref;
            _pickupTriggerBtn = btn;
            document.getElementById('pickupModal').classList.add('show');
        };
        window.closePickupModal = function() {
            document.getElementById('pickupModal').classList.remove('show');
            document.getElementById('pickupModalConfirmBtn').disabled = false;
        };
        window.submitPickupConfirm = function() {
            if (!_pickupRef) return;
            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var confirmBtn = document.getElementById('pickupModalConfirmBtn');
            confirmBtn.disabled = true;
            if (_pickupTriggerBtn) _pickupTriggerBtn.disabled = true;
            fetch('/api/documents/' + encodeURIComponent(_pickupRef) + '/confirm-pickup', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: '{}'
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) {
                    closePickupModal();
                    window.location.reload();
                } else {
                    alert(d.message || 'Failed. Please try again.');
                    closePickupModal();
                    if (_pickupTriggerBtn) _pickupTriggerBtn.disabled = false;
                }
            })
            .catch(function() {
                alert('Something went wrong. Please try again.');
                closePickupModal();
                if (_pickupTriggerBtn) _pickupTriggerBtn.disabled = false;
            });
        };
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDrawer();
                closePickupModal();
            }
        });
    })();
    </script>
</body>
</html>
