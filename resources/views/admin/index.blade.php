<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - DepEd DOCTRAX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">


    <style>
        :root {
            --primary: #0056b3;
            --primary-dark: #004494;
            --primary-gradient: linear-gradient(135deg, #0056b3 0%, #004494 100%);
            --blue-soft: #eff6ff;
            --blue-soft-2: #dbeafe;
            --blue-soft-3: #e8f1fb;
            --blue-deep: #1d4ed8;
            --slate-soft: #f1f5f9;
            --slate-soft-2: #e2e8f0;
            --slate: #475569;
            --slate-dark: #334155;
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
        }

        /* ─── Sidebar ─── */
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
        .main{margin-left:0;min-height:100vh}

        /* ─── Main Content ─── */
        .dash-wrapper {
            max-width: 1200px;
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
            grid-template-columns: repeat(4, 1fr);
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
        .s-icon.green,
        .s-icon.purple { background: var(--blue-soft); color: var(--primary); }

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
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .recent-panel .panel-scroll-body .dtable th {
            position: sticky;
            top: 0;
            z-index: 1;
        }

        .recent-panel {
            border-radius: 12px;
        }

        .recent-panel .panel-head {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            gap: 12px;
            flex-wrap: wrap;
        }

        .recent-panel .panel-head-left {
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

        .recent-panel .dtable {
            width: 100%;
            table-layout: fixed;
        }

        .recent-panel .panel-scroll-body {
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-gutter: stable;
        }

        .recent-panel .dtable th {
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

        .recent-panel .dtable td {
            padding: 10px 10px;
            font-size: 13px;
            color: var(--text-dark);
            border-top: none;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .recent-panel .dtable tbody tr:hover td {
            background: #f8faff;
        }

        .recent-panel .dtable tbody tr:last-child td {
            border-bottom: none;
        }

        .recent-panel .dtable th,
        .recent-panel .dtable td {
            padding-top: 10px;
            padding-bottom: 10px;
        }

        .recent-panel .pill {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            white-space: nowrap;
        }

        .recent-panel .td-action {
            width: 40px;
            text-align: center;
        }

        .recent-panel .dtable th.td-action,
        .recent-panel .dtable td.td-action {
            padding-left: 2px;
            padding-right: 8px;
        }

        .recent-panel .row-arrow {
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

        .recent-panel .dtable tbody tr:hover .row-arrow {
            background: var(--primary);
            color: #fff;
        }

        .recent-panel .col-track { width: 17%; }
        .recent-panel .col-ref { width: 15%; }
        .recent-panel .col-subject { width: 24%; }
        .recent-panel .col-submitted { width: 24%; }
        .recent-panel .col-status { width: 12%; }
        .recent-panel .col-action { width: 44px; }

        .recent-panel .t-track,
        .recent-panel .t-ref {
            font-family: monospace;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
        }

        .recent-panel .t-track { color: var(--primary); }
        .recent-panel .t-ref { color: var(--text-dark); }

        .recent-panel .t-subject .cell-ellipsis,
        .recent-panel .t-user .cell-ellipsis {
            max-width: 100%;
        }

        .recent-panel .t-status {
            white-space: nowrap;
            min-width: 0;
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

        .t-user { min-width: 0; }
        .submission-person { font-size: 12px; color: var(--text-dark); font-weight: 500; }
        .submission-date {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 4px;
            font-size: 11px;
            color: #94a3b8;
            white-space: nowrap;
        }
        .submission-date i { font-size: 10px; }
        .cell-ellipsis { display:block; max-width:100%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

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
            background: var(--blue-soft);
            color: var(--primary);
        }

        .act-body { flex: 1; }
        .act-title { font-size: 14px; font-weight: 600; color: var(--text-dark); margin-bottom: 2px; }
        .act-desc { font-size: 12px; color: #94a3b8; line-height: 1.4; }
        .act-arrow { color: #cbd5e1; font-size: 13px; }
        .act:hover .act-arrow { color: var(--text-muted); }

        /* ─── Footer ─── */
        .site-footer {
            width: 100%;
            background: var(--white);
            border-top: 1px solid var(--border);
            padding: 20px 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #94a3b8;
            margin-top: 40px;
        }

        .footer-left {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .footer-right {
            font-size: 11px;
            color: #b0b8c4;
        }

        /* ─── Mobile Cards ─── */
        .mob-cards { display: none; }
        .mob-card {
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: box-shadow .15s, border-color .15s;
        }
        .mob-card:hover { border-color: var(--primary); box-shadow: 0 2px 8px rgba(0,86,179,.08); }
        .mob-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 6px;
        }
        .mob-card-ref { font-size: 11.5px; font-weight: 700; color: var(--primary); font-family: monospace; line-height: 1.25; }
        .mob-card-track { font-size: 10px; color: var(--text-muted); font-family: monospace; margin-top: 2px; line-height: 1.25; }
        .mob-card-subject {
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
        .mob-card-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
            font-size: 12px;
            color: var(--text-muted);
            align-items: center;
        }
        .mob-card-meta i { margin-right: 4px; font-size: 11px; }
        .mob-card-date { display: inline-flex; align-items: center; gap: 4px; }
        .mob-card-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            font-size: 12px;
            color: var(--text-muted);
        }
        .mob-card-row i { font-size: 11px; opacity: .75; flex-shrink: 0; }
        .mob-card-arrow {
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

        /* ─── Animations ─── */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .anim { animation: fadeIn 0.25s ease forwards; }

        /* ─── Responsive ─── */
        @media (max-width: 1024px) {
            .grid { grid-template-columns: 1fr; }
            .stats { grid-template-columns: repeat(2, 1fr); }
            .panel-fixed { height: min(72vh, 560px); }
        }

        @media (max-width: 900px) {
            .dash-wrapper{padding:20px 16px 40px}
            .top-bar{flex-direction:column;align-items:flex-start;gap:14px}
            .greeting-section h1{font-size:20px}
            .live-clock{width:100%}
            .stats{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px;margin-bottom:16px}
            .stat-card{min-width:0;padding:13px 12px 12px;gap:10px;flex-direction:column;align-items:flex-start}
            .s-icon{width:34px;height:34px;border-radius:8px;font-size:14px}
            .s-data{min-width:0;width:100%}
            .s-num{font-size:24px;letter-spacing:-.6px}
            .s-label{font-size:11px;line-height:1.2}
            .panel-fixed { height: min(68vh, 520px); }
            .dtable-wrap { display: none; }
            .mob-cards { display: block; padding: 10px 12px; overflow-y: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; }
            .drawer { width: 100%; max-width: 100%; }
            .drawer-meta { grid-template-columns: 1fr; }
            .dm-item { border-right: none; }
            .site-footer{flex-direction:column;gap:6px;text-align:center;padding:16px 5%}
        }

        @media (max-width: 400px) {
            .greeting-section h1 { font-size: 18px; }
            .stat-card{padding:12px 10px 10px;gap:8px}
            .s-icon{width:30px;height:30px;font-size:13px}
            .s-num{font-size:22px}
            .s-label{font-size:10px}
        }

        /* Badge colors for office docs */
        .badge-submitted,
        .badge-received,
        .badge-in_review{background:#fff7ed;color:#c2410c}

        /* ─── Receive strip (office style) ─── */
        .receive-strip{background:#fff;border:1px solid var(--border);border-radius:12px;padding:22px 24px;margin-bottom:24px}
        .receive-strip h2{font-size:20px;font-weight:700;color:var(--text-dark);margin:0 0 6px}
        .receive-strip p.rs-sub{font-size:13px;color:var(--text-muted);margin:0 0 18px}
        .rs-main{width:100%;display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:8px;margin-bottom:0;min-width:0}
        .ref-boxes-row{display:flex;align-items:center;gap:7px;flex:1;min-width:0;flex-wrap:nowrap}
        .ref-box{flex:1;min-width:0;height:clamp(60px,5.8vw,72px);text-align:center;font-size:clamp(21px,2.2vw,26px);font-weight:700;font-family:'Poppins',sans-serif;border:1.5px solid #cbd5e1;border-radius:8px;outline:none;text-transform:uppercase;background:#f8fafc;transition:border-color .2s,box-shadow .2s,background .2s;color:#1e293b;padding:0;caret-color:var(--primary);box-shadow:0 0 0 1px rgba(203,213,225,.75)}
        .ref-box:focus{border-color:var(--primary);box-shadow:0 0 0 1px rgba(0,86,179,.28),0 0 0 4px rgba(0,86,179,.13);background:#fff}
        .ref-box.filled{background:#fff;border-color:#94a3b8;box-shadow:0 0 0 1px rgba(148,163,184,.42)}
        .ref-sep{font-size:18px;color:#cbd5e1;user-select:none;padding:0 2px}
        .btn-clear-x{width:36px;height:36px;border:1.5px solid #e2e8f0;border-radius:50%;background:#f8fafc;color:#94a3b8;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;flex-shrink:0;padding:0}
        .rs-center{width:100%;margin:0 auto}
        .rs-btn-wrap{display:flex;justify-content:center;margin-top:18px;gap:12px}
        .btn-receive{flex:1;height:clamp(54px,5.6vw,60px);padding:0 clamp(16px,2.8vw,32px);border:none;border-radius:8px;background:var(--slate-dark);color:#fff;font-family:'Poppins',sans-serif;font-size:clamp(13px,1.7vw,14px);font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:background .2s}
        .btn-receive:hover{background:#243244}
        .btn-receive:active{background:#1e293b}
        .btn-receive:disabled{opacity:.5;cursor:not-allowed}
        .btn-scan-qr{flex:1;height:clamp(54px,5.6vw,60px);padding:0 clamp(16px,2.8vw,32px);border:none;border-radius:8px;background:var(--primary);color:#fff;font-family:'Poppins',sans-serif;font-size:clamp(13px,1.7vw,14px);font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:background .2s;text-decoration:none}
        .btn-scan-qr:hover{background:var(--primary-dark)}
        .btn-scan-qr:active{background:#003976}
        .btn-scan-qr svg{width:18px;height:18px;flex-shrink:0}
        .receive-alert{margin-top:12px;padding:8px 12px;border-radius:7px;font-size:12px;display:none;align-items:center;gap:8px;animation:rcvFadeIn .2s ease-out;width:100%}
        .receive-alert.show{display:flex}
        .receive-alert.err{background:#fef2f2;border-left:3px solid #dc2626;color:#b91c1c}
        .receive-alert.ok{background:var(--blue-soft);border-left:3px solid var(--primary);color:var(--primary-dark)}
        .receive-alert i{font-size:13px;flex-shrink:0}
        .receive-alert span{line-height:1.4}
        @keyframes rcvFadeIn{from{opacity:0;transform:translateY(-3px)}to{opacity:1;transform:translateY(0)}}
        @media(max-width:900px){
            .receive-strip{padding:16px 18px}
            .receive-strip h2{font-size:15px}
            .rs-main{gap:0;grid-template-columns:minmax(0,1fr)}
            .ref-boxes-row{gap:3px}
            .ref-box{height:clamp(52px,13vw,58px);font-size:clamp(17px,4.4vw,19px)}
            .ref-sep{font-size:13px;padding:0 1px}
            .btn-clear-x{display:none}
            .rs-btn-wrap .btn-receive{flex:1 1 0;min-width:0;width:auto;height:48px;padding:0 12px;font-size:12.5px;white-space:nowrap}
            .rs-btn-wrap .btn-scan-qr{flex:1 1 0;min-width:0;width:auto;height:48px;padding:0 12px;font-size:12.5px;white-space:nowrap}
            .rs-btn-wrap{flex-direction:row;gap:8px}
        }
        /* ─── QR Scanner Modal ─── */
        .scanner-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:500;align-items:center;justify-content:center;padding:16px}
        .scanner-overlay.show{display:flex}
        .scanner-modal{background:#fff;border-radius:16px;max-width:440px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.25);animation:modalIn .18s ease;max-height:90vh;overflow-y:auto}
        .scanner-modal-head{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--border)}
        .scanner-modal-head h3{font-size:15px;font-weight:700;color:var(--text-dark)}
        .scanner-close{width:32px;height:32px;border:none;background:#f1f5f9;border-radius:8px;font-size:16px;color:#64748b;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .2s}
        .scanner-close:hover{background:#e2e8f0}
        .scanner-body{padding:20px 22px}
        .scanner-hint{font-size:12px;color:var(--text-muted);margin-bottom:14px;text-align:left}
        #qr-reader{width:100%;border-radius:8px;overflow:hidden}
        #qr-reader video{border-radius:8px}
        .camera-status{text-align:left;padding:10px 0 4px;font-size:12px;color:var(--text-muted)}
        .camera-status .cam-steps{margin:4px 0 8px;padding-left:16px;font-size:11.5px;line-height:1.7}
        .btn-cam-retry{margin-top:6px;padding:6px 16px;background:var(--primary);color:#fff;border:none;border-radius:6px;font-size:12px;cursor:pointer;font-weight:600}
        .btn-cam-retry:hover{background:var(--primary-dark)}
        /* ─── Tracking Drawer ─── */
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
        .drawer-meta{display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid var(--border)}
        .dm-item{padding:14px 20px;border-right:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9}
        .dm-item:nth-child(2n){border-right:none}
        .dm-label{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:#94a3b8;font-weight:600;margin-bottom:3px}
        .dm-value{font-size:14px;color:var(--text-dark);font-weight:500}
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
        .tl-dur{font-size:10px;font-weight:600;color:#6366f1;background:#eef2ff;border:1px solid #c7d2fe;border-radius:20px;padding:1px 8px;text-transform:none;letter-spacing:0;white-space:nowrap;flex-shrink:0;margin-left:auto}
        .drawer-loader{display:flex;align-items:center;justify-content:center;padding:48px;flex-direction:column;gap:12px;color:var(--text-muted);font-size:13px}
        .badge-forwarded,
        .badge-for_pickup,
        .badge-completed{background:#fff7ed;color:#c2410c}
    </style>
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
</head>
<body>

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
        <a href="/dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <span class="nav-section">Management</span>
        <a href="/admin/users"><i class="fas fa-users"></i> Users</a>
        <a href="/admin/offices"><i class="fas fa-building"></i> Offices</a>
        @unless($user->isSuperAdmin())
        <a href="/admin/documents"><i class="fas fa-folder-open"></i> Documents</a>
        @endunless
        @if($user->isSuperAdmin())
        <a href="/records/documents"><i class="fas fa-folder-open"></i> All Documents</a>
        <span class="nav-section">ICT Unit</span>
        <a href="/ict/documents"><i class="fas fa-network-wired"></i> ICT Documents</a>
        <a href="/office/search"><i class="fas fa-chart-line"></i> Reports</a>
        @endif
        <span class="nav-section">My Documents</span>
        <a href="/submit"><i class="fas fa-paper-plane"></i> Submit Document</a>
        <a href="/my-documents"><i class="fas fa-folder"></i> My Documents</a>
        <a href="/track"><i class="fas fa-search"></i> Track Document</a>
        <span class="nav-section">Account</span>
        <a href="/profile"><i class="fas fa-user-cog"></i> My Profile</a>
    </nav>
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="sb-user-info">
                <small>{{ $user->isSuperAdmin() ? 'Super Admin' : 'Admin' }}</small>
                <span>{{ explode(' ', $user->name)[0] }}</span>
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
            @php
                $adminWelcomeName = $user->name ?? 'Admin';
                $adminQueueLabel = $user->office?->name
                    ?? ($user->isSuperAdmin() ? 'Super Admin' : 'Admin');
                $adminQueueCopy = $user->isSuperAdmin()
                    ? "DepEd DOCTRAX &mdash; here's your system overview."
                    : ($user->office_id
                        ? $adminQueueLabel . " &mdash; here's your document queue."
                        : $adminQueueLabel . " &mdash; here's your system overview.");
            @endphp
            <div class="greeting-section">
                <h1>Welcome back, {{ $adminWelcomeName }}!</h1>
                <p>{!! $adminQueueCopy !!}</p>
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
            <div class="stat-card anim">
                <div class="s-icon blue"><i class="fas fa-users"></i></div>
                <div class="s-data">
                    <div class="s-num" id="stat-users">{{ \App\Support\UiNumber::compact($stats['total_users']) }}</div>
                    <div class="s-label">Total Users</div>
                </div>
            </div>
            <div class="stat-card anim">
                <div class="s-icon purple"><i class="fas fa-file-alt"></i></div>
                <div class="s-data">
                    <div class="s-num" id="stat-docs">{{ \App\Support\UiNumber::compact($stats['total_documents']) }}</div>
                    <div class="s-label">Total Documents</div>
                </div>
            </div>
            <div class="stat-card anim">
                <div class="s-icon orange"><i class="fas fa-clock"></i></div>
                <div class="s-data">
                    <div class="s-num" id="stat-pending">{{ \App\Support\UiNumber::compact($stats['pending_docs']) }}</div>
                    <div class="s-label">Pending</div>
                </div>
            </div>
            <div class="stat-card anim">
                <div class="s-icon green"><i class="fas fa-check-circle"></i></div>
                <div class="s-data">
                    <div class="s-num" id="stat-completed">{{ \App\Support\UiNumber::compact($stats['completed_docs']) }}</div>
                    <div class="s-label">Completed</div>
                </div>
            </div>
        </div>

        <!-- Grid -->
        <div class="grid">

            <!-- Recent Submissions -->
            <div class="panel panel-fixed recent-panel anim" id="recentSubmissionsPanel">
                <div class="panel-head">
                    <div class="panel-head-left">
                        <div class="panel-title">Recent Submissions</div>
                        <span class="table-doc-count">{{ \App\Support\UiNumber::compact($recentDocs->count()) }} showing</span>
                    </div>
                    <a href="{{ $user->isSuperAdmin() ? '/records/documents' : '/admin/documents' }}" class="panel-link">View all <i class="fas fa-arrow-right" style="font-size:11px"></i></a>
                </div>

                @if($recentDocs->count() > 0)
                <div class="dtable-wrap panel-scroll-body">
                <table class="dtable">
                    <colgroup>
                        <col class="col-track">
                        <col class="col-ref">
                        <col class="col-subject">
                        <col class="col-submitted">
                        <col class="col-status">
                        <col class="col-action">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Reference #</th>
                            <th>Tracking #</th>
                            <th>Subject</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th class="td-action"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentDocs as $doc)
                        @php
                            $docLookup = $doc->tracking_number ?: $doc->reference_number;
                        @endphp
                        <tr class="doc-row" style="cursor:pointer;" onclick='openDocDetail(@json($docLookup))'>
                            <td class="t-track"><div class="cell-ellipsis" title="{{ $doc->reference_number ?: 'N/A' }}">{{ $doc->reference_number ?: 'N/A' }}</div></td>
                            <td class="t-ref"><div class="cell-ellipsis" title="{{ $doc->tracking_number ?: ($doc->reference_number ?: 'N/A') }}">{{ $doc->tracking_number ?: ($doc->reference_number ?: 'N/A') }}</div></td>
                            <td class="t-subject"><div class="cell-ellipsis" style="font-weight:600" title="{{ $doc->subject }}">{{ $doc->subject }}</div></td>
                            <td class="t-user">
                                <div class="cell-ellipsis submission-person" style="max-width:170px" title="{{ $doc->user ? $doc->user->name : ($doc->sender_name ?? 'Guest') }}">{{ $doc->user ? $doc->user->name : ($doc->sender_name ?? 'Guest') }}</div>
                                <div class="submission-date"><i class="fas fa-calendar-alt"></i>{{ $doc->created_at->format('M d, Y') }}</div>
                            </td>
                            <td class="t-status">
                                @php
                                    $sc = match($doc->status) {
                                        'submitted', 'received' => 'pending',
                                        'in_review', 'on_hold' => 'processing',
                                        'completed', 'for_pickup' => 'completed',
                                        default => 'other',
                                    };
                                    $statusLabel = $doc->statusLabel();
                                @endphp
                                <span class="pill {{ $sc }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                            <td class="td-action"><span class="row-arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></span></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div><!-- end dtable-wrap -->

                <!-- Mobile Cards -->
                <div class="mob-cards">
                    @foreach($recentDocs as $doc)
                    @php
                        $docLookup = $doc->tracking_number ?: $doc->reference_number;
                    @endphp
                    <div class="mob-card" onclick='openDocDetail(@json($docLookup))'>
                        <div class="mob-card-top">
                            <div>
                                <div class="mob-card-ref">{{ $doc->reference_number ?: 'N/A' }}</div>
                                <div class="mob-card-track">Tracking: {{ $doc->tracking_number ?: ($doc->reference_number ?: 'N/A') }}</div>
                            </div>
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
                            <i class="fas fa-user"></i>
                            <span class="cell-ellipsis" title="{{ $doc->user ? $doc->user->name : ($doc->sender_name ?? 'Guest') }}">{{ $doc->user ? $doc->user->name : ($doc->sender_name ?? 'Guest') }}</span>
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
                        <div class="act-icon"><i class="fas fa-home"></i></div>
                        <div class="act-body">
                            <div class="act-title">Home</div>
                            <div class="act-desc">Go to the main landing page</div>
                        </div>
                        <i class="fas fa-chevron-right act-arrow"></i>
                    </a>
                    <a href="/admin/users" class="act">
                        <div class="act-icon"><i class="fas fa-users-cog"></i></div>
                        <div class="act-body">
                            <div class="act-title">Manage Users</div>
                            <div class="act-desc">View &amp; manage accounts</div>
                        </div>
                        <i class="fas fa-chevron-right act-arrow"></i>
                    </a>
                    @unless($user->isSuperAdmin())
                    <a href="/admin/documents" class="act">
                        <div class="act-icon"><i class="fas fa-folder-open"></i></div>
                        <div class="act-body">
                            <div class="act-title">All Documents</div>
                            <div class="act-desc">Browse all submissions</div>
                        </div>
                        <i class="fas fa-chevron-right act-arrow"></i>
                    </a>
                    @endunless
                    <a href="/admin/users?status=pending" class="act">
                        <div class="act-icon"><i class="fas fa-user-clock"></i></div>
                        <div class="act-body">
                            <div class="act-title">Pending Accounts</div>
                            <div class="act-desc">Accounts waiting for activation</div>
                        </div>
                        <i class="fas fa-chevron-right act-arrow"></i>
                    </a>
                    <a href="/admin/offices" class="act">
                        <div class="act-icon"><i class="fas fa-building"></i></div>
                        <div class="act-body">
                            <div class="act-title">Office Accounts</div>
                            <div class="act-desc">Manage internal DepEd office accounts</div>
                        </div>
                        <i class="fas fa-chevron-right act-arrow"></i>
                    </a>
                    <a href="{{ $user->isSuperAdmin() ? '/records/documents?status=in_review' : '/admin/documents?status=in_review' }}" class="act">
                        <div class="act-icon"><i class="fas fa-inbox"></i></div>
                        <div class="act-body">
                            <div class="act-title">Pending Documents</div>
                            <div class="act-desc">Documents awaiting processing</div>
                        </div>
                        <i class="fas fa-chevron-right act-arrow"></i>
                    </a>
                    @if($user->isSuperAdmin())
                    <a href="/records/documents" class="act">
                        <div class="act-icon"><i class="fas fa-folder-open"></i></div>
                        <div class="act-body">
                            <div class="act-title">All Documents</div>
                            <div class="act-desc">View all documents in Records Section</div>
                        </div>
                        <i class="fas fa-chevron-right act-arrow"></i>
                    </a>
                    @endif
                </div>
            </div>

        </div>

    </div>

    <!-- Tracking Drawer -->
    <div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>
    <div class="drawer" id="docDrawer">
        <div class="drawer-head">
            <div class="drawer-head-info">
                <h3 id="drTitle">—</h3>
                <div class="drawer-ref" id="drRef">—</div>
                <div class="drawer-track" id="drTrack"></div>
            </div>
            <button class="drawer-close" onclick="closeDrawer()"><i class="fas fa-times"></i></button>
        </div>
        <div class="drawer-body" id="drawerBody">
            <div class="drawer-loader"><span class="loading-dots"><span></span></span>Loading details...</div>
        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-left">
            <span>&copy; {{ date('Y') }} DepEd Document Tracking System</span>
        </div>
        <div class="footer-right">
            Developed by Raymond Bautista
        </div>
    </footer>

</div><!-- end .main -->

    @php
        $docDrawerData = [];
        foreach ($recentDocs as $doc) {
            $fallback = [
                'reference_number' => $doc->reference_number ?: $doc->tracking_number,
                'tracking_number' => $doc->tracking_number ?: $doc->reference_number,
                'subject' => $doc->subject,
                'status' => $doc->status,
                'status_label' => $doc->statusLabel(),
                'sender_name' => $doc->user ? $doc->user->name : ($doc->sender_name ?? 'Guest'),
                'submitted_to_office' => null,
                'current_office' => null,
                'current_handler' => null,
                'date' => optional($doc->created_at)->format('M d, Y'),
            ];
            $primaryKey = $doc->tracking_number ?: $doc->reference_number;
            if ($primaryKey) {
                $docDrawerData[$primaryKey] = $fallback;
            }
            if ($doc->reference_number && $doc->reference_number !== $doc->tracking_number) {
                $docDrawerData[$doc->reference_number] = $fallback;
            }
        }
    @endphp
    <script type="application/json" id="docsData">@json($docDrawerData)</script>

    <script>
    (function() {
        function syncRecentPanelHeight() {
            var recentPanel = document.getElementById('recentSubmissionsPanel');
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

        // ─── Live stats (refresh every 30s) ───
        // Live stats (silent update every 30s)
        (function() {
            function refreshStats() {
                window.docTraxFetchJson('/api/admin-stats', {
                    headers: { 'Accept': 'application/json' },
                    timeoutMs: 10000
                })
                    .then(function(d) {
                        var compactCount = window.formatCompactCount || function(v) { return String(v); };
                        document.getElementById('stat-users').textContent     = compactCount(d.total_users);
                        document.getElementById('stat-docs').textContent      = compactCount(d.total_documents);
                        document.getElementById('stat-pending').textContent   = compactCount(d.pending_docs);
                        document.getElementById('stat-completed').textContent = compactCount(d.completed_docs);
                        window.clearStatusNotice('admin-dashboard-stats');
                    })
                    .catch(function() {
                        window.setStatusNotice('admin-dashboard-stats', 'Live dashboard updates are temporarily unavailable. Showing the last known counts.', {
                            type: 'warning',
                            priority: 30
                        });
                    });

                // Refresh office stats too
                window.docTraxFetchJson('/api/office-stats', {
                    headers: { 'Accept': 'application/json' },
                    timeoutMs: 10000
                })
                    .then(function(d) {
                        var el;
                        var compactCount = window.formatCompactCount || function(v) { return String(v); };
                        el = document.getElementById('os-incoming');  if (el) el.textContent = compactCount(d.incoming);
                        el = document.getElementById('os-review');    if (el) el.textContent = compactCount(d.in_review);
                        window.clearStatusNotice('admin-office-stats');
                    })
                    .catch(function() {
                        window.setStatusNotice('admin-office-stats', 'Office-side live counts are temporarily unavailable. Showing the last known values.', {
                            type: 'warning',
                            priority: 20
                        });
                    });
            }
            if (window.smartInterval) { window.smartInterval(refreshStats, 30000); }
            else { setInterval(refreshStats, 30000); }
        })();

        // ─── Office document actions (SuperAdmin with office) ───
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

        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var docsData = JSON.parse(document.getElementById('docsData').textContent || '{}');

        window.acceptDoc = function(id) {
            var btn = document.querySelector('.btn-accept-office[data-id="' + id + '"]');
            if (btn) { btn.disabled = true; }

            fetch('/api/office/documents/' + id + '/accept', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(d) {
                if (d.success) {
                    if (btn) { btn.innerHTML = '<i class="fas fa-check"></i> Accepted'; btn.style.background = '#059669'; btn.disabled = true; }
                    setTimeout(function() { location.reload(); }, 800);
                } else {
                    alert(d.message || 'Could not accept document.');
                    if (btn) { btn.disabled = false; }
                }
            })
            .catch(function() {
                alert('Network error. Please try again.');
                if (btn) { btn.disabled = false; }
            });
        };

        /* ─── Segmented Reference Box Logic ─── */
        (function(){
            var container = document.getElementById('refBoxes');
            if(!container) return;
            var boxes = container.querySelectorAll('.ref-box');
            boxes.forEach(function(box){
                box.addEventListener('input', function(){
                    this.value = this.value.replace(/[^A-Za-z0-9]/g,'').toUpperCase();
                    this.classList.toggle('filled', this.value.length > 0);
                    if(this.value.length === 1){
                        var next = container.querySelector('[data-idx="'+(parseInt(this.dataset.idx)+1)+'"]');
                        if(next) next.focus();
                    }
                });
                box.addEventListener('keydown', function(e){
                    if(e.key === 'Backspace' && !this.value){
                        var prev = container.querySelector('[data-idx="'+(parseInt(this.dataset.idx)-1)+'"]');
                        if(prev){ prev.focus(); prev.select(); }
                    }
                    if(e.key === 'Enter'){ e.preventDefault(); window.receiveByReference(); }
                    if(e.key === 'ArrowLeft'){
                        var p2 = container.querySelector('[data-idx="'+(parseInt(this.dataset.idx)-1)+'"]');
                        if(p2) p2.focus();
                    }
                    if(e.key === 'ArrowRight'){
                        var n2 = container.querySelector('[data-idx="'+(parseInt(this.dataset.idx)+1)+'"]');
                        if(n2) n2.focus();
                    }
                });
                box.addEventListener('paste', function(e){
                    e.preventDefault();
                    var paste = (e.clipboardData.getData('text')||'').replace(/[^A-Za-z0-9]/g,'').toUpperCase();
                    var startIdx = parseInt(this.dataset.idx);
                    for(var i=0; i<paste.length && startIdx+i<boxes.length; i++){
                        boxes[startIdx+i].value = paste[i];
                        boxes[startIdx+i].classList.add('filled');
                    }
                    var lastIdx = Math.min(startIdx+paste.length, boxes.length)-1;
                    boxes[lastIdx].focus();
                });
                box.addEventListener('focus', function(){ this.select(); });
            });
        })();

        function getRefValue(){
            var boxes = document.querySelectorAll('#refBoxes .ref-box');
            var val = '';
            boxes.forEach(function(b){ val += b.value; });
            return val.trim().toUpperCase();
        }

        function showReceiveMsg(message, kind){
            var el = document.getElementById('receiveRefMsg');
            if(!el) return;
            var icon = el.querySelector('i');
            var span = el.querySelector('span');
            if(!message){ el.classList.remove('show','ok','err'); return; }
            span.textContent = message;
            el.className = 'receive-alert show ' + (kind || '');
            if(icon) icon.className = kind === 'ok' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
        }

        function clearRefBoxes(){
            var boxes = document.querySelectorAll('#refBoxes .ref-box');
            boxes.forEach(function(b){ b.value=''; b.classList.remove('filled'); });
            if(boxes.length) boxes[0].focus();
            showReceiveMsg('', '');
        }

        async function submitReceiveLookup(lookupValue, pendingMessage){
            var receiveBtn = document.getElementById('receiveRefBtn');
            var scanBtn = document.getElementById('scanQrBtn');
            var lookup = String(lookupValue || '').trim().toUpperCase();

            if(!lookup){
                showReceiveMsg('Reference number is required.', 'err');
                return false;
            }

            showReceiveMsg(pendingMessage || 'Receiving document...', '');
            if(receiveBtn) receiveBtn.disabled = true;
            if(scanBtn) scanBtn.disabled = true;

            try{
                var res = await fetch(@json($user->isSuperAdmin() ? '/api/ict/receive-by-reference' : '/api/office/documents/receive-by-reference'), {
                    method: 'POST',
                    headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                    body: JSON.stringify({
                        reference_number: lookup,
                        tracking_number: lookup
                    })
                });
                var data = await res.json();

                if(data.success){
                    showReceiveMsg(data.message || 'Document received successfully.', 'ok');
                    setTimeout(function(){ location.reload(); }, 700);
                    return true;
                }

                showReceiveMsg(data.message || 'Failed to receive document.', 'err');
            }catch(e){
                showReceiveMsg('Network error. Please try again.', 'err');
            }

            if(receiveBtn) receiveBtn.disabled = false;
            if(scanBtn) scanBtn.disabled = false;
            return false;
        }

        async function receiveByReference(){
            var ref = getRefValue();
            if(ref.length < 8){
                showReceiveMsg('Please enter all 8 characters of the reference number.', 'err');
                var boxes = document.querySelectorAll('#refBoxes .ref-box');
                for(var i=0;i<boxes.length;i++){
                    if(!boxes[i].value){ boxes[i].focus(); break; }
                }
                return;
            }

            return submitReceiveLookup(ref, 'Receiving document...');
        }

        window.clearRefBoxes = clearRefBoxes;
        window.submitReceiveLookup = submitReceiveLookup;
        window.receiveByReference = receiveByReference;

        /* ─── Tracking Drawer ─── */
        function escapeHtml(value){
            return String(value === null || value === undefined ? '' : value)
                .replace(/&/g,'&amp;')
                .replace(/</g,'&lt;')
                .replace(/>/g,'&gt;')
                .replace(/"/g,'&quot;')
                .replace(/'/g,'&#39;');
        }
        window.openDocDetail = function(ref){
            ref = (ref || '').toString().trim();
            document.getElementById('drTitle').textContent='—';
            document.getElementById('drRef').textContent=ref;
            document.getElementById('drTrack').textContent='';
            document.getElementById('drawerBody').innerHTML='<div class="drawer-loader"><span class="loading-dots"><span></span></span>Loading details...</div>';
            document.getElementById('drawerOverlay').classList.add('open');
            document.getElementById('docDrawer').classList.add('open');
            document.body.style.overflow='hidden';
            window.docTraxFetchJson('/api/track-document',{
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                timeoutMs: 15000,
                body:JSON.stringify({ reference_number: ref, tracking_number: ref })
            })
            .then(function(data){
                if(!data.success || !data.document){
                    document.getElementById('drawerBody').innerHTML='<div class="drawer-loader">Document not found.</div>';
                    return;
                }
                renderDrawer(data.document);
            })
            .catch(function(error){
                var fallback = docsData[ref];
                if (fallback) {
                    renderDrawer({
                        subject: fallback.subject || '-',
                        reference_number: fallback.reference_number || ref,
                        tracking_number: fallback.tracking_number || ref,
                        status: fallback.status || 'unknown',
                        status_label: fallback.status_label || 'Unknown',
                        sender_name: fallback.sender_name || '-',
                        submitted_to_office: fallback.submitted_to_office || '-',
                        current_office: fallback.current_office || '-',
                        current_handler: fallback.current_handler || 'Unassigned',
                        date: fallback.date || '-',
                        routing_logs: []
                    });
                    window.showNetworkNotice('Showing basic document details from the current list while the live request is unavailable.', {
                        type: 'warning',
                        duration: 5000
                    });
                    return;
                }
                document.getElementById('drawerBody').innerHTML =
                    '<div class="drawer-loader">' + escapeHtml(window.describeRequestError(error, 'Could not load tracking details. Please try again.')) + '</div>';
            });
        };
        window.closeDrawer = function(){
            document.getElementById('drawerOverlay').classList.remove('open');
            document.getElementById('docDrawer').classList.remove('open');
            document.body.style.overflow='';
        };
        function dotClass(s){
            if(s==='cancelled' || s==='returned') return 'c-danger';
            if(s==='completed') return 'c-done';
            if(s==='forwarded') return 'c-warn';
            return 'c-active';
        }
        function renderDrawer(doc){
            var ref = doc.reference_number || doc.tracking_number || '-';
            var trackingNo = doc.tracking_number || '';
            document.getElementById('drTitle').textContent = doc.subject || '-';
            document.getElementById('drRef').textContent = 'TN · ' + ref;
            document.getElementById('drTrack').textContent = (trackingNo && trackingNo !== ref) ? ('Ref · ' + trackingNo) : '';
            document.getElementById('drRef').textContent = 'Ref · ' + ref;
            document.getElementById('drTrack').textContent = (trackingNo && trackingNo !== ref) ? ('TN · ' + trackingNo) : '';
            var logs = Array.isArray(doc.routing_logs) ? doc.routing_logs : [];
            var tlHtml = '';
            if (!logs.length) {
                tlHtml = '<div style="color:var(--text-muted);font-size:13px;padding:4px 0">No routing history yet.</div>';
            } else {
                function _gk(log) {
                    return (log.action === 'submitted') ? '__pending__' :
                                   (log.action === 'forwarded' ? (log.from_office || 'Unknown') :
                                   (log.to_office || log.from_office || 'Unknown'));
                }
                var segDurations = [];
                logs.forEach(function(log) {
                    if (log.office_duration_human != null) {
                        segDurations.push({ key: _gk(log), dur: log.office_duration_human });
                    }
                });
                var segDurIdx = segDurations.length - 1;
                var prevGroupKey = null;
                logs.slice().reverse().forEach(function(log, idx) {
                    var isLatest = idx === 0;
                    var dc = isLatest ? 'c-latest' : dotClass(log.status_after);
                    var dotIcon = isLatest ? 'fa-arrow-up' : 'fa-check';
                    var groupKey = _gk(log);
                    var groupLabel = (groupKey === '__pending__') ? 'Submitted — Pending Physical Submission' : groupKey;
                    if (groupKey !== prevGroupKey) {
                        prevGroupKey = groupKey;
                        var dur = null;
                        if (segDurIdx >= 0 && segDurations[segDurIdx] && segDurations[segDurIdx].key === groupKey) {
                            dur = segDurations[segDurIdx--].dur;
                        }
                        tlHtml += '<div class="tl-office-hdr"><div class="tl-dot ' + dc + '" style="margin-right:5px"><i class="fas ' + dotIcon + '" style="font-size:5px"></i></div><span>' + escapeHtml(groupLabel) + '</span>' + (dur ? '<span class="tl-dur"><i class="fas fa-hourglass-half" style="margin-right:4px;font-size:9px"></i>' + escapeHtml(dur) + '</span>' : '') + '</div>';
                    }
                    tlHtml += '<div class="tl-item">' +
                        (log.performed_by ? '<div class="tl-action">' + escapeHtml(log.performed_by) + '</div>' : '') +
                        '<div class="tl-meta"><i class="fas fa-clock" style="margin-right:3px;font-size:10px"></i>' + escapeHtml(log.timestamp || '-') + '</div>' +
                        '<div class="tl-meta"><i class="fas fa-tasks" style="margin-right:3px;font-size:10px"></i>' + escapeHtml(log.action_label || 'Status Updated') + '</div>' +
                        (log.remarks ? '<div class="tl-remarks">' + escapeHtml(log.remarks) + '</div>' : '') +
                        '</div>';
                });
            }
            var currentOfficeText = (doc.status === 'submitted')
                ? ('Awaiting physical submission to ' + (doc.submitted_to_office || doc.current_office || 'Records Section'))
                : (doc.current_office || doc.submitted_to_office || '-');
            var currentHandlerText = doc.current_handler || 'Unassigned';
            document.getElementById('drawerBody').innerHTML =
                '<div class="drawer-tl-head"><i class="fas fa-history"></i> Routing History</div>' +
                '<div class="drawer-timeline"><div class="tl">' + tlHtml + '</div></div>';
        }
        document.addEventListener('keydown', function(e){
            if(e.key === 'Escape'){
                var scannerOpen = document.getElementById('scannerOverlay') && document.getElementById('scannerOverlay').classList.contains('show');
                if(scannerOpen) window.closeScanner();
                else { closeDrawer(); closeSidebar(); }
            }
        });
    })();
    </script>

    <!-- QR Scanner Modal -->
    <div class="scanner-overlay" id="scannerOverlay" onclick="if(event.target===this)closeScanner()">
        <div class="scanner-modal">
            <div class="scanner-modal-head">
                <h3>Scan Document QR Code</h3>
                <button class="scanner-close" onclick="closeScanner()">&#10005;</button>
            </div>
            <div class="scanner-body">
                <div class="scanner-hint">Point your camera at the document's QR code to review it before receiving.</div>
                <div id="qr-reader"></div>
                <p class="camera-status" id="cameraStatus">Initializing camera...</p>
            </div>
        </div>
    </div>
    <script src="/js/html5-qrcode.min.js"></script>
    <script src="/js/jsqr.js"></script>
    <script>
    (function(){
        if (window.__docTraxAdminScanner && typeof window.__docTraxAdminScanner.cleanup === 'function') {
            try { window.__docTraxAdminScanner.cleanup(); } catch (e) {}
        }

        var html5QrCode = null;
        var scannerRunning = false;
        var activeStream = null;
        var scanLoopTimer = null;
        var barcodeDetector = null;
        var previewVideo = null;
        var scanCooldown = false;
        var scanBuffer = '';
        var scanTimer = null;
        var SCAN_IDLE_MS = 80;
        var scannerDestroyed = false;

        function scannerOverlay(){
            return document.getElementById('scannerOverlay');
        }

        function statusEl(){
            return document.getElementById('cameraStatus');
        }

        function showStatus(message, isHtml){
            var el = statusEl();
            if (!el) return;
            if (isHtml) { el.innerHTML = message; } else { el.textContent = message; }
            el.style.display = 'block';
        }

        function showPermissionDenied(){
            var localhostUrl = (location.hostname === '127.0.0.1')
                ? location.href.replace('127.0.0.1', 'localhost')
                : null;
            var msg = '<strong style="color:#dc2626;">&#9888; Camera blocked.</strong> ';
            if (localhostUrl) {
                msg += 'Your browser blocked camera for <strong>127.0.0.1</strong>. '
                    + '<a href="' + localhostUrl + '" style="color:#0056b3;font-weight:700;">Open on localhost instead</a> '
                    + '(same app, camera will work there).';
            } else {
                msg += 'Click the <strong>lock icon</strong> in the address bar → Camera → <strong>Allow</strong>, then '
                    + '<button class="btn-cam-retry" onclick="window.retryCamera()" style="padding:2px 10px;">Retry</button>.';
            }
            showStatus(msg, true);
        }

        function isScannerOpen(){
            var overlay = scannerOverlay();
            return !!(overlay && overlay.classList.contains('show'));
        }

        function onDecodeSuccess(decodedText) {
            if (scanCooldown) return;
            scanCooldown = true;
            setTimeout(function(){ scanCooldown = false; }, 2000);
            processScannedText(decodedText);
        }

        function fillRefBoxes(tracking){
            if (!/^[A-Z0-9]{1,8}$/.test(tracking)) return;
            var boxes = document.querySelectorAll('#refBoxes .ref-box');
            if (!boxes.length) return;
            for (var i = 0; i < boxes.length; i++) {
                boxes[i].value = '';
                boxes[i].classList.remove('filled');
            }
            for (var j = 0; j < boxes.length && j < tracking.length; j++) {
                boxes[j].value = tracking[j];
                boxes[j].classList.add('filled');
            }
        }

        function normalizeScannedLookup(text) {
            var raw = String(text || '').trim();
            if (!raw) return '';

            try {
                var parsed = new URL(raw, window.location.origin);
                var receiveMatch = parsed.pathname.match(/\/receive\/([A-Za-z0-9\-]+)/i);
                if (receiveMatch) {
                    raw = receiveMatch[1];
                } else {
                    var lookupParam = parsed.searchParams.get('ref')
                        || parsed.searchParams.get('tracking')
                        || parsed.searchParams.get('reference');
                    if (lookupParam) raw = lookupParam;
                }
            } catch (e) {}

            var fallbackMatch = raw.match(/\/receive\/([A-Za-z0-9\-]+)/i);
            if (fallbackMatch) raw = fallbackMatch[1];

            raw = raw.trim().toUpperCase();
            if (!raw) return '';

            var compact = raw.replace(/[^A-Z0-9]/g, '');
            if (/^[A-Z0-9]{8}$/.test(compact)) {
                return compact;
            }

            return raw.replace(/[^A-Z0-9\-]/g, '').replace(/^-+|-+$/g, '');
        }

        function processScannedText(text) {
            var lookup = normalizeScannedLookup(text);
            if (!lookup || lookup.length < 8) return;

            window.closeScanner();
            window.location.assign('/receive/' + encodeURIComponent(lookup));
        }

        window.openScanner = function() {
            var overlay = scannerOverlay();
            if (!overlay) return;
            scannerDestroyed = false;
            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
            scanCooldown = false;
            scanBuffer = '';
            if (scanTimer) {
                clearTimeout(scanTimer);
                scanTimer = null;
            }
            stopCamera();
            startCamera();
        };

        window.closeScanner = function() {
            var overlay = scannerOverlay();
            if (overlay) overlay.classList.remove('show');
            document.body.style.overflow = '';
            stopCamera();
        };

        function readerEl() {
            return document.getElementById('qr-reader');
        }

        function clearReader() {
            var el = readerEl();
            if (el) el.innerHTML = '';
            previewVideo = null;
        }

        function isPermDenied(e) {
            var s = String(e || '').toLowerCase();
            return s.indexOf('notallowed') !== -1 || s.indexOf('permission') !== -1 || s.indexOf('denied') !== -1;
        }

        function normalizeCameraError(err) {
            var raw = String((err && (err.name || err.message)) || err || '').toLowerCase();
            if (raw.indexOf('notallowed') !== -1 || raw.indexOf('permission') !== -1 || raw.indexOf('denied') !== -1) return 'denied';
            if (raw.indexOf('notfound') !== -1 || raw.indexOf('devicesnotfound') !== -1 || raw.indexOf('overconstrained') !== -1 || raw.indexOf('constraint') !== -1) return 'notfound';
            if (raw.indexOf('notreadable') !== -1 || raw.indexOf('trackstart') !== -1 || raw.indexOf('could not start') !== -1 || raw.indexOf('device in use') !== -1 || raw.indexOf('in use') !== -1) return 'busy';
            if (raw.indexOf('security') !== -1 || raw.indexOf('secure') !== -1) return 'security';
            return raw || 'unknown';
        }

        function getCameraStream(constraints) {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                return Promise.reject(new Error('GET_USER_MEDIA_UNAVAILABLE'));
            }
            return navigator.mediaDevices.getUserMedia({ video: constraints, audio: false });
        }

        function listVideoInputs() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
                return Promise.resolve([]);
            }
            return navigator.mediaDevices.enumerateDevices()
                .then(function(devices) {
                    return (devices || []).filter(function(device) {
                        return device && device.kind === 'videoinput';
                    });
                })
                .catch(function() {
                    return [];
                });
        }

        function cameraScore(device, isMobile) {
            var label = String((device && device.label) || '').toLowerCase();
            var score = 0;

            if (isMobile) {
                if (/back|rear|environment|world|traseira|trasera|externa/.test(label)) score += 50;
                if (/front|user|selfie|facetime|integrated|frontal|frente/.test(label)) score -= 25;
            } else {
                if (/usb|external|rear|back|environment/.test(label)) score += 20;
                if (/integrated|front|facetime|user/.test(label)) score += 5;
            }

            return score;
        }

        function buildCameraAttempts(isMobile, devices) {
            var attempts = [];
            var seen = {};
            var hdHint = { width: { ideal: 1280 }, height: { ideal: 720 } };

            function addAttempt(constraints) {
                var key = typeof constraints === 'boolean'
                    ? ('bool:' + constraints)
                    : JSON.stringify(constraints);
                if (seen[key]) return;
                seen[key] = true;
                attempts.push(constraints);
            }

            if (isMobile) {
                addAttempt({
                    facingMode: { ideal: 'environment' },
                    width: hdHint.width,
                    height: hdHint.height
                });
            }

            (devices || []).slice().sort(function(a, b) {
                return cameraScore(b, isMobile) - cameraScore(a, isMobile);
            }).forEach(function(device) {
                if (!device.deviceId) return;
                addAttempt({
                    deviceId: { exact: device.deviceId },
                    width: hdHint.width,
                    height: hdHint.height
                });
            });

            addAttempt(true);
            addAttempt({
                facingMode: 'user',
                width: hdHint.width,
                height: hdHint.height
            });

            return attempts;
        }

        function attachPreview(stream) {
            clearReader();
            var el = readerEl();
            if (!el) return Promise.reject(new Error('QR_READER_MISSING'));
            var video = document.createElement('video');
            video.setAttribute('autoplay', '');
            video.setAttribute('muted', '');
            video.setAttribute('playsinline', '');
            video.muted = true;
            video.srcObject = stream;
            video.style.width = '100%';
            video.style.display = 'block';
            video.style.borderRadius = '8px';
            el.appendChild(video);
            previewVideo = video;
            return video.play().catch(function() {}).then(function(){ return video; });
        }

        function startDetectLoop() {
            if (typeof jsQR === 'undefined') {
                showStatus('QR library not loaded. Please refresh the page.');
                return;
            }
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            function scanFrame() {
                if (!scannerRunning || !previewVideo) return;
                if (previewVideo.readyState < 2 || !previewVideo.videoWidth) {
                    if (scannerRunning) scanLoopTimer = setTimeout(scanFrame, 200);
                    return;
                }
                try {
                    canvas.width = previewVideo.videoWidth;
                    canvas.height = previewVideo.videoHeight;
                    ctx.drawImage(previewVideo, 0, 0);
                    var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    var code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'dontInvert' });
                    if (code && code.data) {
                        onDecodeSuccess(code.data);
                        return;
                    }
                } catch (e) {}
                if (scannerRunning) scanLoopTimer = setTimeout(scanFrame, 150);
            }
            scanLoopTimer = setTimeout(scanFrame, 600);
        }

        window.retryCamera = function() {
            stopCamera();
            startCamera();
        };

        function startCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showStatus('Camera not available. Please use Chrome or Edge.');
                return;
            }

            function doStart() {
                showStatus('Requesting camera access...');
                var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                listVideoInputs().then(function(devices) {
                    var attempts = buildCameraAttempts(isMobile, devices);
                    var seenFailures = {};

                    function finishFailure() {
                        if (seenFailures.security) {
                            showStatus('Camera access requires HTTPS or localhost. Open this app on localhost or HTTPS and try again.');
                            return;
                        }
                        if (seenFailures.notfound) {
                            showStatus('No available camera was found on this device. Allow camera access, then reopen the scanner to try another camera.');
                            return;
                        }
                        showStatus('Camera could not start. Close any app using your webcam (Zoom, Teams, OBS), then reload the page.');
                    }

                    function tryNext(idx) {
                        if (idx >= attempts.length) {
                            finishFailure();
                            return;
                        }

                        getCameraStream(attempts[idx])
                            .then(function(stream) {
                                activeStream = stream;
                                return attachPreview(stream);
                            })
                            .then(function() {
                                scannerRunning = true;
                                showStatus('Camera live. Point it at a QR code.');
                                startDetectLoop();
                            })
                            .catch(function(err) {
                                var kind = normalizeCameraError(err);
                                seenFailures[kind] = true;
                                if (kind === 'denied') { showPermissionDenied(); return; }
                                if (kind === 'busy') { showStatus('Camera is in use by another app. Close Zoom, Teams, or OBS and retry.'); return; }
                                tryNext(idx + 1);
                            });
                    }

                    tryNext(0);
                });
            }

            doStart();
        }

        function stopCamera() {
            scannerRunning = false;
            if (scanLoopTimer) {
                clearTimeout(scanLoopTimer);
                scanLoopTimer = null;
            }
            if (activeStream) {
                activeStream.getTracks().forEach(function(track) { track.stop(); });
                activeStream = null;
            }
            if (html5QrCode) {
                try { html5QrCode.stop(); } catch (e) {}
                try { html5QrCode.clear(); } catch (e2) {}
            }
            clearReader();
        }

        function handleScannerKeydown(e) {
            if (e.key === 'Escape' && isScannerOpen()) {
                e.preventDefault();
                window.closeScanner();
                return;
            }

            if (!isScannerOpen()) return;
            if (e.ctrlKey || e.altKey || e.metaKey) return;

            if (e.key === 'Enter') {
                if (scanBuffer.length) {
                    var payload = scanBuffer;
                    scanBuffer = '';
                    if (scanTimer) {
                        clearTimeout(scanTimer);
                        scanTimer = null;
                    }
                    processScannedText(payload);
                }
                return;
            }

            if (e.key.length === 1) {
                scanBuffer += e.key;
                if (scanTimer) clearTimeout(scanTimer);
                scanTimer = setTimeout(function(){
                    if (scanBuffer.length >= 6) processScannedText(scanBuffer);
                    scanBuffer = '';
                    scanTimer = null;
                }, SCAN_IDLE_MS);
            }
        }

        function destroyScanner() {
            if (scannerDestroyed) return;
            scannerDestroyed = true;
            scanCooldown = false;
            scanBuffer = '';
            if (scanTimer) {
                clearTimeout(scanTimer);
                scanTimer = null;
            }
            stopCamera();
            var overlay = scannerOverlay();
            if (overlay) overlay.classList.remove('show');
            if (document.body) document.body.style.overflow = '';
            document.removeEventListener('keydown', handleScannerKeydown);
            window.removeEventListener('spa:before-swap', destroyScanner);
            window.removeEventListener('pagehide', destroyScanner);
            if (window.__docTraxAdminScanner && window.__docTraxAdminScanner.cleanup === destroyScanner) {
                window.__docTraxAdminScanner = null;
            }
        }

        window.__docTraxAdminScanner = { cleanup: destroyScanner };
        window.addEventListener('spa:before-swap', destroyScanner);
        window.addEventListener('pagehide', destroyScanner);
        document.addEventListener('keydown', handleScannerKeydown);
    })();
    </script>
</body>
</html>
