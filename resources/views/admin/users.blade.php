<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Manage Users - DepEd DOCTRAX</title>
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
        /* ─── Main ─── */
        .main{margin-left:0;flex:1;display:flex;flex-direction:column;}

        /* ─── Main Content ─── */
        .dash-wrapper { max-width: 1200px; width: 100%; margin: 0 auto; padding: 28px 24px 48px; flex:1; }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .page-header h1 { font-size: 22px; font-weight: 600; color: var(--text-dark); }
        .page-header p { font-size: 14px; color: var(--text-muted); font-weight: 400; }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            padding: 7px 14px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--white);
            transition: all 0.15s;
        }
        .back-link:hover { background: #f8fafc; border-color: var(--primary); }

        /* ─── Filters ─── */
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
            align-items: center;
        }

        .filter-search-wrap {
            position: relative;
            flex: 1 1 360px;
            min-width: min(100%, 360px);
            display: flex;
            align-items: center;
        }

        .filter-search-wrap .clearable-wrap {
            width: 100%;
        }

        .filter-search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 13px;
            pointer-events: none;
            z-index: 1;
        }

        .filter-input {
            width: 100%;
            padding: 10px 14px 10px 38px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            background: var(--white);
            color: var(--text-dark);
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .filter-input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.08); }

        .filter-select-wrap {
            position: relative;
            flex: 0 0 190px;
            min-width: 180px;
        }

        .filter-select-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }

        .filter-select-icon svg {
            width: 14px;
            height: 14px;
            display: block;
        }

        .filter-select {
            width: 100%;
            padding: 10px 34px 10px 38px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            background: var(--white);
            color: var(--text-dark);
            outline: none;
            cursor: pointer;
            transition: border-color 0.15s, box-shadow 0.15s;
        }
        .filter-select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0, 86, 179, 0.08); }

        .filter-btn {
            padding: 9px 18px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s;
        }
        .filter-btn:hover { background: var(--primary-dark); }

        .filter-clear {
            padding: 10px 14px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.15s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
            min-height: 42px;
        }
        .filter-clear:hover { background: #f8fafc; color: var(--text-dark); }

        /* ─── Panel ─── */
        .panel {
            background: var(--white);
            border-radius: 10px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .panel.list-panel.has-list {
            display: flex;
            flex-direction: column;
            max-height: clamp(520px, 72vh, 820px);
        }

        .panel.list-panel.has-list .dtable-wrap {
            flex: 1;
            min-height: 0;
            overflow: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
        }

        .panel.list-panel.has-list .dtable-wrap .dtable th {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .panel.list-panel.has-list .mob-cards,
        .panel.list-panel.has-list .empty-state {
            flex: 1;
            min-height: 0;
        }

        .panel.list-panel.has-list .pagination-bar {
            flex-shrink: 0;
        }

        .panel-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            border-bottom: 1px solid #f1f5f9;
        }

        .panel-title { font-size: 17px; font-weight: 700; color: var(--text-dark); }
        .panel-badge {
            background: var(--blue-soft);
            color: var(--primary);
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

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

        .pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }

        .pill.active { background: var(--blue-soft); color: var(--primary); }
        .pill.pending { background: var(--slate-soft); color: var(--slate-dark); }
        .pill.suspended { background: var(--slate-soft-2); color: var(--slate-dark); }

        .t-date { font-size: 12px; color: #94a3b8; }
        .t-docs { font-size: 12px; color: var(--text-muted); font-weight: 500; }

        .empty-state {
            padding: 48px 24px;
            text-align: center;
            color: #94a3b8;
        }
        .empty-state i { font-size: 32px; margin-bottom: 10px; display: block; color: #cbd5e1; }
        .empty-state p { font-size: 14px; }

        /* ─── Action Buttons ─── */
        .action-btns { display: flex; gap: 6px; }

        .btn-sm {
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-family: inherit;
            font-weight: 500;
            border: 1px solid var(--border);
            background: var(--white);
            color: var(--text-dark);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: all 0.15s;
        }
        .btn-sm:hover { background: #f8fafc; }

        .btn-sm.activate { color: var(--primary); border-color: #bfdbfe; }
        .btn-sm.activate:hover { background: var(--blue-soft); }

        .btn-sm.suspend { color: var(--slate-dark); border-color: var(--slate-soft-2); }
        .btn-sm.suspend:hover { background: var(--slate-soft); }

        .btn-sm.delete { color: #991b1b; border-color: #fecaca; }
        .btn-sm.delete:hover { background: #fef2f2; }

        /* ─── Pagination ─── */
        .pagination-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 14px 24px;
            border-top: 1px solid #f1f5f9;
            font-size: 13px;
            color: var(--text-muted);
        }

        .pagination-links { display: flex; gap: 4px; }

        .page-btn {
            padding: 6px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 12px;
            font-family: inherit;
            background: var(--white);
            color: var(--text-dark);
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            transition: all 0.15s;
        }
        .page-btn:hover { background: #f8fafc; border-color: var(--primary); color: var(--primary); }
        .page-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }
        .page-btn.disabled { opacity: 0.4; cursor: default; pointer-events: none; }

        /* ─── Modal ─── */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.4);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.show { display: flex; }

        .modal {
            background: var(--white);
            border-radius: 12px;
            width: 90%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .modal-head {
            padding: 18px 24px;
            border-bottom: 1px solid #f1f5f9;
        }
        .modal-head h3 { font-size: 16px; font-weight: 600; color: var(--text-dark); }

        .modal-body { padding: 20px 24px; }
        .modal-body p { font-size: 14px; color: var(--text-muted); line-height: 1.6; margin-bottom: 4px; }
        .modal-body strong { color: var(--text-dark); }

        .modal-foot {
            padding: 14px 24px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .modal-btn {
            padding: 8px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-family: inherit;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
            border: 1px solid var(--border);
            background: var(--white);
            color: var(--text-dark);
        }
        .modal-btn:hover { background: #f8fafc; }

        .modal-btn.danger { background: #dc2626; color: #fff; border-color: #dc2626; }
        .modal-btn.danger:hover { background: #b91c1c; }

        .modal-btn.warning { background: var(--slate-dark); color: #fff; border-color: var(--slate-dark); }
        .modal-btn.warning:hover { background: #243244; }

        .modal-btn.success { background: var(--primary); color: #fff; border-color: var(--primary); }
        .modal-btn.success:hover { background: var(--primary-dark); }

        .modal-btn.primary { background: var(--primary); color: #fff; border-color: var(--primary); }
        .modal-btn.primary:hover { background: var(--primary-dark); }

        /* ─── Edit Modal Form Fields ─── */
        .modal-field { margin-bottom: 14px; }
        .modal-field:last-child { margin-bottom: 0; }
        .modal-label { display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .modal-input {
            width: 100%; padding: 9px 12px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13px;
            font-family: inherit;
            color: var(--text-dark);
            background: #f8fafc;
            transition: border-color 0.2s;
            outline: none;
        }
        .modal-input:focus { border-color: var(--primary); background: #fff; }
        .modal-err { font-size: 12px; color: #dc2626; margin-top: 4px; display: none; }
        .modal-err.show { display: block; }
        .btn-sm.edit { color: #1d4ed8; border-color: #bfdbfe; }
        .btn-sm.edit:hover { background: #eff6ff; }

        /* ─── Account type badges ─── */
        .type-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .type-badge.individual { background: var(--blue-soft); color: var(--primary); }
        .type-badge.representative { background: var(--slate-soft); color: var(--slate-dark); }

        /* ─── Rep name cell ─── */
        .name-cell { display: flex; flex-direction: column; gap: 2px; }
        .name-office { font-weight: 600; color: var(--text-dark); font-size: 13px; }
        .name-rep { font-size: 11px; color: var(--text-muted); }
        .name-rep i { margin-right: 3px; }

        /* wider table for extra columns */
        .dtable { min-width: 900px; }
        .panel { overflow-x: auto; }

        /* ─── Toast ─── */
        .toast {
            position: fixed;
            top: 80px;
            right: 24px;
            z-index: 300;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 14px 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 8px;
            transform: translateX(calc(100% + 60px));
            transition: transform 0.3s ease;
        }
        .toast.show { transform: translateX(0); }
        .toast.success { border-left: 3px solid var(--primary); }
        .toast.error { border-left: 3px solid #dc2626; }

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
            margin-top: 40px;
        }
        .footer-left { display: flex; align-items: center; gap: 6px; }
        .footer-right { font-size: 11px; color: #b0b8c4; }

        /* ─── Animations ─── */
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .anim { animation: fadeIn 0.25s ease forwards; }

        /* ─── Mobile cards (hidden on desktop) ─── */
        .mob-cards { display:none; }
        .mob-card {
            background:#fff;
            border-radius:12px;
            border:1px solid var(--border);
            padding:16px;
            margin-bottom:12px;
        }
        .mob-card-head { display:flex; justify-content:space-between; align-items:flex-start; gap:8px; margin-bottom:10px; }
        .mob-card-name { font-size:14px; font-weight:700; color:var(--text-dark); }
        .mob-card-sub { font-size:11px; color:var(--text-muted); margin-top:2px; }
        .mob-card-sub i { margin-right:3px; }
        .mob-card-row { display:flex; justify-content:space-between; align-items:center; padding:5px 0; font-size:12px; color:var(--text-muted); }
        .mob-card-row .label { font-weight:600; text-transform:uppercase; font-size:10px; letter-spacing:.3px; }
        .mob-card-row .value { font-weight:500; color:var(--text-dark); text-align:right; word-break:break-all; }
        .mob-card-actions { display:flex; gap:8px; margin-top:12px; padding-top:12px; border-top:1px solid var(--border); flex-wrap:wrap; }
        .mob-card-actions .btn-sm { width:auto; padding:6px 12px; font-size:11px; font-weight:600; gap:5px; height:auto; border-radius:8px; }
        .mob-card-actions .btn-sm i { font-size:11px; }

        /* ─── Responsive ─── */
        @media (max-width: 900px) {
            .dash-wrapper { padding: 20px 16px 40px; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .page-header h1 { font-size:18px; }
            .page-header p { font-size:12px; }
            .back-link { font-size:12px; padding:6px 12px; }
            .filters { gap: 8px; }
            .filter-search-wrap,
            .filter-select-wrap,
            .filter-clear { flex: 1 1 100%; min-width: 0; }
            .filter-input { font-size: 12px; padding: 9px 10px 9px 34px; }
            .filter-select { font-size: 12px; padding: 9px 28px 9px 34px; }
            .filter-btn { font-size: 12px; padding: 8px 12px; }
            .filter-clear { font-size: 12px; padding: 8px 10px; }
            .panel.list-panel.has-list { max-height: min(68vh, 560px); }
            .panel .dtable-wrap { display:none; }
            .mob-cards { display:block; padding:12px; overflow-y:auto; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; }
            .modal { max-width:95vw; }
            .modal-body { padding:16px; }
            .modal-head { padding:16px 16px 0; }
            .modal-foot { padding:12px 16px; }
            .pagination-bar { flex-direction:column; gap:10px; text-align:center; padding:12px 16px; }
            .dash-footer { flex-direction:column; gap:6px; text-align:center; padding:16px 5%; }
            .toast { right:12px; left:12px; max-width:none; }
        }
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
        <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <span class="nav-section">Management</span>
        <a href="/admin/users" class="active"><i class="fas fa-users"></i> Users</a>
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

<div class="main">
    <!-- ─── Content ─── -->
    <div class="dash-wrapper">

        <div class="page-header anim">
            <div>
                <h1>Manage Users</h1>
                <p>View and manage registered accounts</p>
            </div>
            <a href="/dashboard" class="back-link" aria-label="Back to Dashboard" title="Back to Dashboard" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;"><span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></span></a>
        </div>

        <!-- Filters -->
        <form class="filters anim" method="GET" action="/admin/users" id="searchForm" data-live-search>
            <div class="filter-search-wrap">
                <i class="fas fa-search filter-search-icon" aria-hidden="true"></i>
                <input type="text" id="usersSearch" name="search" class="filter-input" placeholder="Search name, email, or mobile..." value="{{ $filters['search'] }}" data-clearable data-no-capitalize>
            </div>
            <div class="filter-select-wrap">
                <span class="filter-select-icon" aria-hidden="true">@include('partials.filter-icon', ['size' => 14])</span>
                <select name="status" class="filter-select" id="usersStatus">
                    <option value="">All Status</option>
                    <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="suspended" {{ $filters['status'] === 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            @if($filters['search'] || $filters['status'])
                <button type="button" class="filter-clear" onclick="clearUserFilters()"><i class="fas fa-rotate-left"></i> Clear</button>
            @endif
        </form>

        <!-- Users Table -->
        <div class="panel list-panel{{ $users->count() ? ' has-list' : '' }} anim">
            <div class="panel-head">
                <div class="panel-title">Registered Users</div>
                <span class="panel-badge">{{ \App\Support\UiNumber::compact($users->total()) }} total</span>
            </div>

            @if($users->count() > 0)
            <div class="dtable-wrap">
            <table class="dtable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Docs</th>
                        <th>Status</th>
                        <th>Joined</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $u)
                    @php
                        $isRep = $u->account_type === 'representative';
                        $officeName = $isRep ? ($u->representativeOfficeName() ?? 'No office assigned') : $u->name;
                        $repName = $isRep ? $u->representativeDisplayName() : '';
                    @endphp
                    <tr id="user-row-{{ $u->id }}">
                        <td>
                            <div class="name-cell">
                                <span class="name-office">{{ $isRep ? $repName : $officeName }}</span>
                                @if($isRep)
                                    <span class="name-rep"><i class="fas fa-building" style="margin-right:3px;"></i>{{ $officeName }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <span class="type-badge {{ $u->account_type ?? 'individual' }}">
                                {{ ($isRep && $u->office_id) ? 'Office' : ($isRep ? 'Representative' : 'Individual') }}
                            </span>
                        </td>
                        <td><!--email_off-->{{ $u->email }}<!--/email_off--></td>
                        <td>{{ $u->mobile ?? 'No number provided' }}</td>
                        <td class="t-docs">{{ $u->documents_count }}</td>
                        <td>
                            <span class="pill {{ $u->status }}" id="user-status-{{ $u->id }}">{{ ucfirst($u->status) }}</span>
                        </td>
                        <td class="t-date">{{ $u->created_at->format('M d, Y') }}</td>
                        <td>
                            <div class="action-btns">
                                @if($u->status !== 'active')
                                    <button class="btn-sm activate" onclick="updateStatus({{ $u->id }}, 'active', '{{ addslashes($u->name) }}')" title="Activate">
                                        <i class="fas fa-check"></i>
                                    </button>
                                @endif
                                @if($u->status !== 'suspended')
                                    <button class="btn-sm suspend" onclick="updateStatus({{ $u->id }}, 'suspended', '{{ addslashes($u->name) }}')" title="Suspend">
                                        <i class="fas fa-ban"></i>
                                    </button>
                                @endif
                                <button class="btn-sm edit" onclick="openEditModal({{ $u->id }}, '{{ addslashes($officeName) }}', '{{ addslashes($repName) }}', '{{ $u->email }}', '{{ $u->mobile ?? '' }}', '{{ $u->account_type ?? 'individual' }}')" title="Edit">
                                    <i class="fas fa-pencil-alt"></i>
                                </button>
                                @if(!$user->isSuperAdmin())
                                <button class="btn-sm delete" onclick="confirmDelete({{ $u->id }}, '{{ addslashes($u->name) }}')" title="Delete">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>

            {{-- Mobile card layout --}}
            <div class="mob-cards">
                @foreach($users as $u)
                @php
                    $isRep = $u->account_type === 'representative';
                    $officeName = $isRep ? ($u->representativeOfficeName() ?? 'No office assigned') : $u->name;
                    $repName = $isRep ? $u->representativeDisplayName() : '';
                @endphp
                <div class="mob-card" id="mob-user-row-{{ $u->id }}">
                    <div class="mob-card-head">
                        <div>
                            <div class="mob-card-name">{{ $isRep ? $repName : $officeName }}</div>
                            @if($isRep)
                                <div class="mob-card-sub"><i class="fas fa-building" style="margin-right:3px;"></i>{{ $officeName }}</div>
                            @endif
                        </div>
                        <span class="pill {{ $u->status }}" id="mob-user-status-{{ $u->id }}">{{ ucfirst($u->status) }}</span>
                    </div>
                    <div class="mob-card-row">
                        <span class="label">Type</span>
                        <span class="value">
                            <span class="type-badge {{ $u->account_type ?? 'individual' }}">
                                {{ ($isRep && $u->office_id) ? 'Office' : ($isRep ? 'Representative' : 'Individual') }}
                            </span>
                        </span>
                    </div>
                    <div class="mob-card-row">
                        <span class="label">Email</span>
                        <span class="value"><!--email_off-->{{ $u->email }}<!--/email_off--></span>
                    </div>
                    <div class="mob-card-row">
                        <span class="label">Mobile</span>
                        <span class="value">{{ $u->mobile ?? 'No number provided' }}</span>
                    </div>
                    <div class="mob-card-row">
                        <span class="label">Documents</span>
                        <span class="value">{{ $u->documents_count }}</span>
                    </div>
                    <div class="mob-card-row">
                        <span class="label">Joined</span>
                        <span class="value">{{ $u->created_at->format('M d, Y') }}</span>
                    </div>
                    <div class="mob-card-actions">
                        @if($u->status !== 'active')
                            <button class="btn-sm activate" onclick="updateStatus({{ $u->id }}, 'active', '{{ addslashes($u->name) }}')" title="Activate">
                                <i class="fas fa-check"></i> Activate
                            </button>
                        @endif
                        @if($u->status !== 'suspended')
                            <button class="btn-sm suspend" onclick="updateStatus({{ $u->id }}, 'suspended', '{{ addslashes($u->name) }}')" title="Suspend">
                                <i class="fas fa-ban"></i> Suspend
                            </button>
                        @endif
                        <button class="btn-sm edit" onclick="openEditModal({{ $u->id }}, '{{ addslashes($officeName) }}', '{{ addslashes($repName) }}', '{{ $u->email }}', '{{ $u->mobile ?? '' }}', '{{ $u->account_type ?? 'individual' }}')" title="Edit">
                            <i class="fas fa-pencil-alt"></i> Edit
                        </button>
                        @if(!$user->isSuperAdmin())
                        <button class="btn-sm delete" onclick="confirmDelete({{ $u->id }}, '{{ addslashes($u->name) }}')" title="Delete">
                            <i class="fas fa-trash-alt"></i> Delete
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @include('partials.shared-pagination', [
                'paginator' => $users,
                'itemLabel' => 'users',
            ])

            @else
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <p>No users found.</p>
            </div>
            @endif
        </div>

    </div>

    <!-- Edit User Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <div class="modal-head">
                <h3><i class="fas fa-user-edit" style="color:var(--primary);margin-right:6px;"></i> Edit User</h3>
            </div>
            <div class="modal-body">
                <!-- Individual fields -->
                <div id="editFieldIndividual">
                    <div class="modal-field">
                        <label class="modal-label">Full Name</label>
                        <input type="text" class="modal-input" id="editName" placeholder="Full name" maxlength="255">
                    </div>
                </div>
                <!-- Representative fields -->
                <div id="editFieldRep" style="display:none;">
                    <div class="modal-field">
                        <label class="modal-label">Office / Institution Name</label>
                        <input type="text" class="modal-input" id="editOfficeName" placeholder="e.g. City Hall Office" maxlength="255">
                    </div>
                    <div class="modal-field">
                        <label class="modal-label">Contact Person</label>
                        <input type="text" class="modal-input" id="editRepName" placeholder="e.g. Juan dela Cruz" maxlength="255">
                    </div>
                </div>
                <!-- Common fields -->
                <div class="modal-field">
                    <label class="modal-label">Email Address</label>
                    <input type="email" class="modal-input" id="editEmail" placeholder="Email address" maxlength="255">
                </div>
                <div class="modal-field">
                    <label class="modal-label">Mobile Number <span style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                    <input type="text" class="modal-input" id="editMobile" placeholder="09XXXXXXXXX" maxlength="11" inputmode="numeric"
                        oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)">
                    <div class="modal-err" id="editMobileErr"></div>
                </div>
            </div>
            <div class="modal-foot">
                <button class="modal-btn" onclick="closeEditModal()">Cancel</button>
                <button class="modal-btn primary" id="saveEditBtn"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal-overlay" id="deleteModal">
        <div class="modal">
            <div class="modal-head">
                <h3>Delete User</h3>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong id="deleteUserName"></strong>?</p>
                <p style="font-size:12px;color:#94a3b8;margin-top:8px;">This action cannot be undone. All associated documents will be unlinked.</p>
            </div>
            <div class="modal-foot">
                <button class="modal-btn" onclick="closeModal()">Cancel</button>
                <button class="modal-btn danger" id="confirmDeleteBtn">Delete</button>
            </div>
        </div>
    </div>

    <!-- Suspend / Activate Confirmation Modal -->
    <div class="modal-overlay" id="statusModal">
        <div class="modal">
            <div class="modal-head" id="statusModalHead">
                <h3 id="statusModalTitle">Suspend User</h3>
            </div>
            <div class="modal-body">
                <div style="display:flex;align-items:flex-start;gap:14px;">
                    <div id="statusModalIcon" style="flex-shrink:0;width:42px;height:42px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;"></div>
                    <div>
                        <p id="statusModalMsg" style="margin-bottom:0;"></p>
                        <p id="statusModalSub" style="font-size:12px;color:#94a3b8;margin-top:6px;"></p>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button class="modal-btn" onclick="closeStatusModal()">Cancel</button>
                <button class="modal-btn" id="confirmStatusBtn"></button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

    <footer class="dash-footer">
        <div class="footer-left">
            <span>&copy; {{ date('Y') }} DepEd Document Tracking System</span>
        </div>
        <div class="footer-right">
            Developed by Raymond Bautista
        </div>
    </footer>

    <script>
    (function() {
        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
        }

        // ─── Toast ───
        function showToast(msg, type) {
            var t = document.getElementById('toast');
            t.textContent = msg;
            t.className = 'toast ' + type + ' show';
            setTimeout(function() { t.classList.remove('show'); }, 3000);
        }

        // ─── Update Status ───
        var statusTargetId   = null;
        var statusTargetVal  = null;

        window.updateStatus = function(id, status, name) {
            statusTargetId  = id;
            statusTargetVal = status;

            var isSuspend = status === 'suspended';
            var title     = isSuspend ? 'Suspend User' : 'Activate User';
            var iconBg    = isSuspend ? 'var(--slate-soft)' : 'var(--blue-soft)';
            var iconColor = isSuspend ? 'var(--slate-dark)' : 'var(--primary)';
            var iconClass = isSuspend ? 'fas fa-ban' : 'fas fa-check-circle';
            var btnClass  = isSuspend ? 'danger'  : 'success';
            var btnLabel  = isSuspend ? 'Suspend' : 'Activate';
            var msg       = isSuspend
                ? 'Are you sure you want to suspend <strong>' + escapeHtml(name) + '</strong>?'
                : 'Are you sure you want to activate <strong>' + escapeHtml(name) + '</strong>?';
            var sub       = isSuspend
                ? 'This user will no longer be able to log in until reactivated.'
                : 'This user will regain access to the system.';

            document.getElementById('statusModalTitle').textContent = title;
            document.getElementById('statusModalMsg').innerHTML     = msg;
            document.getElementById('statusModalSub').textContent   = sub;

            var iconEl = document.getElementById('statusModalIcon');
            iconEl.style.background = iconBg;
            iconEl.innerHTML = '<i class="' + iconClass + '" style="color:' + iconColor + ';"></i>';

            var btn = document.getElementById('confirmStatusBtn');
            btn.textContent = btnLabel;
            btn.className   = 'modal-btn ' + btnClass;

            document.getElementById('statusModal').classList.add('show');
        };

        window.closeStatusModal = function() {
            document.getElementById('statusModal').classList.remove('show');
            statusTargetId  = null;
            statusTargetVal = null;
        };

        document.getElementById('statusModal').addEventListener('click', function(e) {
            if (e.target === this) closeStatusModal();
        });

        document.getElementById('confirmStatusBtn').addEventListener('click', function() {
            if (!statusTargetId) return;
            var id     = statusTargetId;
            var status = statusTargetVal;
            closeStatusModal();

            fetch('/api/admin/users/' + id, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ status: status })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(function() { window.location.reload(); }, 800);
                } else {
                    showToast(data.message || 'Failed to update.', 'error');
                }
            })
            .catch(function() { showToast('Something went wrong.', 'error'); });
        });

        // ─── Delete ───
        var deleteId = null;

        window.confirmDelete = function(id, name) {
            deleteId = id;
            document.getElementById('deleteUserName').textContent = name;
            document.getElementById('deleteModal').classList.add('show');
        };

        window.closeModal = function() {
            document.getElementById('deleteModal').classList.remove('show');
            deleteId = null;
        };

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (!deleteId) return;
            fetch('/api/admin/users/' + deleteId, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                closeModal();
                if (data.success) {
                    showToast(data.message, 'success');
                    var row = document.getElementById('user-row-' + deleteId);
                    if (row) row.style.display = 'none';
                    var mobCard = document.getElementById('mob-user-row-' + deleteId);
                    if (mobCard) mobCard.style.display = 'none';
                } else {
                    showToast(data.message || 'Failed to delete.', 'error');
                }
            })
            .catch(function() { closeModal(); showToast('Something went wrong.', 'error'); });
        });

        // Click outside modal to close
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        // ─── Edit User ───
        var editId = null;
        var editAccountType = 'individual';
        var editOriginal = {};

        window.openEditModal = function(id, officeName, repName, email, mobile, accountType) {
            editId = id;
            editAccountType = accountType || 'individual';
            editOriginal = { officeName: officeName, repName: repName, email: email, mobile: mobile };

            var isRep = editAccountType === 'representative';
            document.getElementById('editFieldIndividual').style.display = isRep ? 'none' : 'block';
            document.getElementById('editFieldRep').style.display      = isRep ? 'block' : 'none';

            if (isRep) {
                document.getElementById('editOfficeName').value = officeName;
                document.getElementById('editRepName').value   = repName;
            } else {
                document.getElementById('editName').value = officeName;
            }
            document.getElementById('editEmail').value  = email;
            document.getElementById('editMobile').value = mobile;
            document.getElementById('editModal').classList.add('show');
        };

        window.closeEditModal = function() {
            document.getElementById('editModal').classList.remove('show');
            editId = null;
        };

        document.getElementById('saveEditBtn').addEventListener('click', function() {
            if (!editId) return;
            var email  = document.getElementById('editEmail').value.trim();
            var mobile = document.getElementById('editMobile').value.trim();
            var name   = '';

            if (editAccountType === 'representative') {
                var office = document.getElementById('editOfficeName').value.trim();
                var rep    = document.getElementById('editRepName').value.trim();
                if (!office) { showToast('Office name is required.', 'error'); return; }
                if (!rep)    { showToast('Contact person name is required.', 'error'); return; }
                name = office + ' - ' + rep;
            } else {
                name = document.getElementById('editName').value.trim();
                if (!name) { showToast('Name is required.', 'error'); return; }
            }

            if (!email) { showToast('Email is required.', 'error'); return; }

            if (mobile) {
                if (mobile.length !== 11) { showToast('Mobile number must be exactly 11 digits.', 'error'); return; }
                if (!mobile.startsWith('09')) { showToast('Mobile number must start with 09.', 'error'); return; }
            }
            // Check if anything actually changed
            var origName = editAccountType === 'representative'
                ? (editOriginal.officeName + ' - ' + editOriginal.repName)
                : editOriginal.officeName;
            if (name === origName && email === editOriginal.email && mobile === editOriginal.mobile) {
                showToast('No changes were made.', 'error');
                return;
            }

            var btn = document.getElementById('saveEditBtn');
            btn.disabled = true;

            var payload = { name: name, email: email, mobile: mobile };

            fetch('/api/admin/users/' + editId, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                closeEditModal();
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(function() { window.location.reload(); }, 800);
                } else {
                    showToast(data.message || 'Failed to update.', 'error');
                }
            })
            .catch(function() {
                btn.disabled = false;
                showToast('Something went wrong.', 'error');
            });
        });

        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });

        // ─── Sidebar ───
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
        window.clearUserFilters = function() {
            var form = document.getElementById('searchForm');
            if (!form) return;
            var search = document.getElementById('usersSearch');
            var status = document.getElementById('usersStatus');
            if (search) search.value = '';
            if (status) status.value = '';
            if (typeof form.requestSubmit === 'function') form.requestSubmit();
            else form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
            if (search) search.focus();
        };
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeSidebar(); });

        // ─── Logout ───
        window.logout = function() {
            fetch('/api/logout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            }).then(function() { window.location.href = '/login'; })
              .catch(function() { window.location.href = '/login'; });
        };

        // ─── Search Rate Limit ───
        (function() {
            var form = document.getElementById('searchForm');
            var btn = document.getElementById('searchBtn');
            if (!form || !btn || form.hasAttribute('data-live-search')) return;
            var lastSubmit = 0;
            var cooldown = 2000; // 2 seconds between searches
            form.addEventListener('submit', function(e) {
                var now = Date.now();
                if (now - lastSubmit < cooldown) {
                    e.preventDefault();
                    return;
                }
                lastSubmit = now;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Searching...';
            });
        })();
    })();
    </script>
</div><!-- end .main -->
</body>
</html>
