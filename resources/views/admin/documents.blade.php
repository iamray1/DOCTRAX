<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>All Documents - DepEd DOCTRAX</title>
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
        .btn-logout{display:flex;align-items:center;gap:7px;margin-top:8px;padding:8px 14px;background:rgba(255,255,255,.1);border:none;border-radius:8px;color:rgba(255,255,255,.8);font-size:12px;cursor:pointer;font-family:'Poppins',sans-serif;width:100%;justify-content:center;transition:background .2s}
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

        /* ─── Stats Row ─── */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-bottom: 20px;
        }

        .mini-stat {
            background: var(--white);
            border-radius: 8px;
            padding: 14px 18px;
            border: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .mini-stat-icon {
            width: 36px; height: 36px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .mini-stat-num { font-size: 18px; font-weight: 700; color: var(--text-dark); line-height: 1; margin-bottom: 1px; }
        .mini-stat-label { font-size: 12px; color: var(--text-muted); }

        /* ─── Filters ─── */
        .filters {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: nowrap;
            align-items: center;
        }

        .filter-input {
            padding: 9px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            background: var(--white);
            color: var(--text-dark);
            flex: 1;
            min-width: 0;
            outline: none;
            transition: border-color 0.15s;
        }
        .filter-input:focus { border-color: var(--primary); }

        .filter-select {
            padding: 9px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            background: var(--white);
            color: var(--text-dark);
            outline: none;
            cursor: pointer;
            transition: border-color 0.15s;
        }
        .filter-select:focus { border-color: var(--primary); }

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
            display: flex; align-items: center; gap: 6px;
            transition: background 0.15s;
        }
        .filter-btn:hover { background: var(--primary-dark); }

        .filter-clear {
            padding: 9px 14px;
            background: none;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            color: var(--text-muted);
            cursor: pointer;
            text-decoration: none;
            transition: all 0.15s;
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
            background: rgba(0, 86, 179, 0.08);
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

        .t-num { font-weight: 600; color: var(--primary); font-size: 13px; }
        .t-num-sub { display:block; margin-top:2px; font-size:11px; color: var(--text-muted); font-family: monospace; }
        .t-user { font-size: 12px; color: var(--text-muted); }
        .t-date { font-size: 12px; color: #94a3b8; }
        .cell-ellipsis { display:block; max-width:100%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

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

        .empty-state {
            padding: 48px 24px;
            text-align: center;
            color: #94a3b8;
        }
        .empty-state i { font-size: 32px; margin-bottom: 10px; display: block; color: #cbd5e1; }
        .empty-state p { font-size: 14px; }

        /* ─── Action Buttons ─── */
        .action-btns { display: flex; gap: 6px; }
        .dtable tbody tr.doc-row { cursor: pointer; }
        .td-action { width: 44px; text-align: center; }
        .row-arrow {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 28px;
            height: 28px;
            border-radius: 7px;
            color: #94a3b8;
            transition: all .15s;
        }
        .dtable tbody tr.doc-row:hover .row-arrow { background: var(--primary); color: #fff; }

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
        .drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.35);
            z-index: 280;
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s;
        }
        .drawer-overlay.open {
            opacity: 1;
            pointer-events: all;
        }

        .drawer {
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            width: 460px;
            max-width: 100vw;
            background: #fff;
            z-index: 281;
            box-shadow: -4px 0 24px rgba(0,0,0,.12);
            display: flex;
            flex-direction: column;
            transform: translateX(100%);
            transition: transform .28s cubic-bezier(.4,0,.2,1);
        }
        .drawer.open { transform: translateX(0); }

        .drawer-head {
            padding: 18px 22px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }

        .drawer-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--text-dark);
        }

        .drawer-close {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #f8fafc;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-muted);
            font-size: 14px;
            flex-shrink: 0;
            transition: all .15s;
        }
        .drawer-close:hover {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fca5a5;
        }

        .drawer-body {
            flex: 1;
            overflow-y: auto;
        }

        .drawer-loader {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px;
            flex-direction: column;
            gap: 12px;
            color: var(--text-muted);
            font-size: 13px;
            text-align: center;
        }

        .spin {
            width: 22px;
            height: 22px;
            border: 3px solid #e2e8f0;
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin .7s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .drawer-head-info {
            flex: 1;
            min-width: 0;
        }

        .drawer-head h3 {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 4px;
        }

        .drawer-ref {
            font-size: 13px;
            color: var(--text-muted);
            font-family: monospace;
            letter-spacing: .4px;
            margin-bottom: 2px;
        }

        .drawer-track {
            font-size: 11px;
            color: var(--text-muted);
            font-family: monospace;
            letter-spacing: .4px;
            margin-bottom: 4px;
        }

        .drawer-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 1px solid var(--border);
        }

        .dm-item {
            padding: 14px 20px;
            border-right: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
        }

        .dm-item:nth-child(2n) {
            border-right: none;
        }

        .dm-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .dm-value {
            font-size: 14px;
            color: var(--text-dark);
            font-weight: 500;
            word-break: break-word;
        }

        .drawer-tl-head {
            padding: 14px 20px 6px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .8px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .drawer-timeline {
            padding: 10px 20px 24px;
        }

        .tl { position:relative; }
        .tl::before { content:''; position:absolute; left:7px; top:8px; bottom:8px; width:2px; background:var(--border); z-index:-1; }

        .tl-item {
            position: relative;
            margin-bottom: 20px;
            padding-left: 24px;
        }

        .tl-item:last-child {
            margin-bottom: 0;
        }

        .tl-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2.5px solid #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            flex-shrink: 0;
        }

        .tl-dot.c-active { background: #22c55e; box-shadow: 0 0 0 2px #22c55e; }
        .tl-dot.c-done { background: #22c55e; box-shadow: 0 0 0 2px #22c55e; }
        .tl-dot.c-warn { background: #22c55e; box-shadow: 0 0 0 2px #22c55e; }
        .tl-dot.c-danger { background: #22c55e; box-shadow: 0 0 0 2px #22c55e; }
        .tl-dot.c-latest { background: #f59e0b; box-shadow: 0 0 0 2px #f59e0b; }

        .tl-action {
            font-size: 12px;
            font-weight: 700;
            color: #1b263b;
        }

        .tl-meta {
            font-size: 12px;
            color: #64748b;
            margin: 2px 0;
        }

        .tl-remarks {
            font-size: 12px;
            color: #64748b;
            background: #f8fafc;
            border-left: 3px solid var(--border);
            padding: 5px 9px;
            border-radius: 4px;
            margin-top: 5px;
        }
        .tl-office-hdr{display:flex;align-items:center;font-size:13px;font-weight:700;color:var(--text-dark);text-transform:none;letter-spacing:0;margin:18px 0 8px -7px;padding-left:7px;padding-bottom:6px;position:relative}
        .tl-office-hdr::after{content:'';position:absolute;left:21px;right:0;bottom:0;height:1.5px;background:var(--border)}
        .tl-office-hdr:first-child{margin-top:0}
        .tl-dur{font-size:10px;font-weight:600;color:#6366f1;background:#eef2ff;border:1px solid #c7d2fe;border-radius:20px;padding:1px 8px;text-transform:none;letter-spacing:0;white-space:nowrap;flex-shrink:0;margin-left:auto}

        .drawer-empty {
            color: var(--text-muted);
            font-size: 13px;
            padding: 4px 0;
        }

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
        .toast.success { border-left: 3px solid #16a34a; }
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
            margin-bottom: 8px;
        }
        .mob-card-ref { font-size: 12px; font-weight: 600; color: var(--primary); font-family: monospace; }
        .mob-card-track { font-size: 10px; color: var(--text-muted); font-family: monospace; margin-top: 2px; }
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
            margin-left: auto;
        }

        /* ─── Animations ─── */
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .anim { animation: fadeIn 0.25s ease forwards; }

        /* ─── Responsive ─── */
        @media (max-width: 900px) {
            .dash-wrapper { padding: 20px 16px 40px; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .filters { gap: 8px; }
            .filter-input { font-size: 12px; padding: 8px 10px; }
            .filter-select { font-size: 12px; padding: 8px 6px; }
            .filter-btn { font-size: 12px; padding: 8px 12px; }
            .filter-clear { font-size: 12px; padding: 8px 10px; }
            .stats-row { grid-template-columns: 1fr 1fr; }
            .panel.list-panel.has-list { max-height: min(68vh, 560px); }
            .dtable-wrap { display: none; }
            .mob-cards { display: block; overflow-y: auto; overscroll-behavior: contain; -webkit-overflow-scrolling: touch; }
            .dash-footer { flex-direction: column; gap: 6px; text-align: center; padding: 16px 5%; }
            .drawer { width: 100%; max-width: 100%; }
            .drawer-meta { grid-template-columns: 1fr; }
            .dm-item { border-right: none; }
            .pagination-bar { flex-direction: column; gap: 10px; text-align: center; padding: 14px 16px; }
            .pagination-links { flex-wrap: wrap; justify-content: center; }
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
        <a href="/admin/users"><i class="fas fa-users"></i> Users</a>
        <a href="/admin/offices"><i class="fas fa-building"></i> Offices</a>
        <a href="/admin/documents" class="active"><i class="fas fa-folder-open"></i> Documents</a>
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
                <h1>All Documents</h1>
                <p>Browse and manage all submitted documents</p>
            </div>
            <a href="/dashboard" class="back-link" aria-label="Back to Dashboard" title="Back to Dashboard" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;"><span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></span></a>
        </div>

        <!-- Stats Row -->
        <div class="stats-row anim">
            <div class="mini-stat">
                <div class="mini-stat-icon" style="background:rgba(0,86,179,0.1);color:var(--primary);"><i class="fas fa-file-alt"></i></div>
                <div>
                    <div class="mini-stat-num">{{ \App\Support\UiNumber::compact($stats['total']) }}</div>
                    <div class="mini-stat-label">Total</div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon" style="background:rgba(0,86,179,0.1);color:var(--primary);"><i class="fas fa-clock"></i></div>
                <div>
                    <div class="mini-stat-num">{{ \App\Support\UiNumber::compact($stats['processing']) }}</div>
                    <div class="mini-stat-label">Processing</div>
                </div>
            </div>
            <div class="mini-stat">
                <div class="mini-stat-icon" style="background:rgba(0,86,179,0.1);color:var(--primary);"><i class="fas fa-check-circle"></i></div>
                <div>
                    <div class="mini-stat-num">{{ \App\Support\UiNumber::compact($stats['completed']) }}</div>
                    <div class="mini-stat-label">Completed</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <form class="filters anim" method="GET" action="/admin/documents" id="searchForm" data-live-search>
            <input type="text" name="search" class="filter-input" placeholder="Search tracking/reference no., subject, or sender..." value="{{ $filters['search'] }}" data-clearable data-no-capitalize>
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                @foreach(\App\Models\Document::FILTER_STATUSES as $key => $label)
                    <option value="{{ $key }}" {{ $filters['status'] === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="filter-btn" id="searchBtn" data-no-auto-loading>@include('partials.filter-icon', ['size' => 16]) Search</button>
            @if($filters['search'] || $filters['status'])
                <a href="/admin/documents" class="filter-clear">Clear</a>
            @endif
        </form>

        <!-- Documents Table -->
        <div class="panel list-panel{{ $documents->count() > 0 ? ' has-list' : '' }} anim">
            <div class="panel-head">
                <div class="panel-title">Documents</div>
                <span class="panel-badge">{{ \App\Support\UiNumber::compact($documents->total()) }} total</span>
            </div>

            @if($documents->count() > 0)
            <div class="dtable-wrap">
            <table class="dtable">
                <thead>
                    <tr>
                        <th>Tracking #</th>
                        <th>Reference #</th>
                        <th>Subject</th>
                        <th>Submitted By</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="td-action"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $doc)
                    <tr class="doc-row" id="doc-row-{{ $doc->id }}" onclick='viewDoc(@json($doc->tracking_number))'>
                        <td><span class="t-num">{{ $doc->tracking_number }}</span></td>
                        <td><span class="t-num" style="color:var(--text-dark)">{{ $doc->reference_number ?: 'N/A' }}</span></td>
                        <td style="max-width:200px"><div class="cell-ellipsis" title="{{ $doc->subject }}">{{ $doc->subject }}</div></td>
                        <td class="t-user"><div class="cell-ellipsis" style="max-width:170px" title="{{ $doc->user ? $doc->user->name : ($doc->sender_name ?? 'Guest') }}">{{ $doc->user ? $doc->user->name : ($doc->sender_name ?? 'Guest') }}</div></td>
                        <td>
                            @php
                                $sc = match($doc->status) {
                                    'submitted', 'received' => 'pending',
                                    'in_review', 'on_hold' => 'processing',
                                    'completed', 'for_pickup' => 'completed',
                                    default => 'other',
                                };
                                $statusLabel = $doc->statusLabel();
                            @endphp
                            <span class="pill {{ $sc }}" id="doc-status-{{ $doc->id }}">{{ $statusLabel }}</span>
                        </td>
                        <td class="t-date">{{ $doc->created_at->format('M d, Y') }}</td>
                        <td class="td-action"><span class="row-arrow"><i class="fas fa-chevron-right"></i></span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div><!-- end dtable-wrap -->

            <!-- Mobile Cards -->
            <div class="mob-cards">
                @foreach($documents as $doc)
                <div class="mob-card" onclick='viewDoc(@json($doc->tracking_number))'>
                    <div class="mob-card-top">
                        <div>
                            <div class="mob-card-ref">{{ $doc->tracking_number }}</div>
                        <div style="font-size:10px;color:var(--text-muted);font-family:monospace;margin-top:1px">Ref: {{ $doc->reference_number ?: 'N/A' }}</div>
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
                        <span><i class="fas fa-user"></i>{{ $doc->user ? $doc->user->name : ($doc->sender_name ?? 'Guest') }}</span>
                        <span><i class="fas fa-calendar"></i>{{ $doc->created_at->format('M d, Y') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
            @include('partials.shared-pagination', [
                'paginator' => $documents,
                'itemLabel' => 'documents',
            ])

            @else
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No documents found.</p>
            </div>
            @endif
        </div>

    </div>

    <!-- View Document Drawer -->
    <div class="drawer-overlay" id="viewDrawerOverlay" onclick="closeViewDrawer()"></div>
    <div class="drawer" id="viewDrawer">
        <div class="drawer-head">
            <div class="drawer-head-info">
                <h3 id="drTitle">-</h3>
                <div class="drawer-ref" id="drRef">-</div>
                <div class="drawer-track" id="drTrack"></div>
            </div>
            <button class="drawer-close" onclick="closeViewDrawer()"><i class="fas fa-times"></i></button>
        </div>
        <div class="drawer-body" id="viewDocContent">
            <div class="drawer-loader"><span class="loading-dots"><span></span></span>Loading details...</div>
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

    <!-- Document data for view modal -->
    <script type="application/json" id="docsData">
        @php
            $docData = [];
            foreach($documents as $doc) {
                $docData[$doc->tracking_number] = [
                    'reference_number' => $doc->reference_number ?: $doc->tracking_number,
                    'tracking_number' => $doc->tracking_number,
                    'subject' => $doc->subject,
                    'type' => $doc->type ?? 'General',
                    'status' => $doc->statusLabel(),
                    'sender_name' => $doc->user ? $doc->user->name : ($doc->sender_name ?? 'Guest'),
                    'sender_office' => $doc->sender_office ?? 'No office specified',
                    'recipient_office' => $doc->recipient_office ?? 'No office specified',
                    'description' => $doc->description ?? 'No description provided',
                    'date' => $doc->created_at->format('M d, Y h:i A'),
                ];
            }
        @endphp
        @json($docData)
    </script>

    <script>
    (function() {
        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var docsData = JSON.parse(document.getElementById('docsData').textContent || '{}');

        // ─── Toast ───
        function showToast(msg, type) {
            var t = document.getElementById('toast');
            t.textContent = msg;
            t.className = 'toast ' + type + ' show';
            setTimeout(function() { t.classList.remove('show'); }, 3000);
        }

        function escapeHtml(value) {
            return String(value === null || value === undefined ? '' : value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function dotClass(status) {
            if (status === 'cancelled' || status === 'returned') return 'c-danger';
            if (status === 'completed') return 'c-done';
            if (status === 'forwarded') return 'c-warn';
            return 'c-active';
        }

        function renderDrawer(doc) {
            var ref = doc.reference_number || doc.tracking_number || '-';
            var trackingNo = doc.tracking_number || '';
            var normalizedStatus = (String(doc.status || '').toLowerCase() === 'forwarded' || String(doc.status_label || '').toLowerCase() === 'forwarded')
                ? 'Received'
                : (doc.status_label || doc.status || '-');

            document.getElementById('drTitle').textContent = doc.subject || '-';
            document.getElementById('drRef').textContent = 'TN · ' + ref;
            document.getElementById('drTrack').textContent = (trackingNo && trackingNo !== ref) ? ('Ref · ' + trackingNo) : '';

            var logs = Array.isArray(doc.routing_logs) ? doc.routing_logs : [];
            var tlHtml = '';
            if (!logs.length) {
                tlHtml = '<div class="drawer-empty">No routing history yet.</div>';
            } else {
                // Pre-pass: collect per-segment durations in chronological order.
                // office_duration_human is only set on the first (arrival) log of each segment.
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
                // Consume from end as we iterate reversed (newest→oldest)
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

            document.getElementById('viewDocContent').innerHTML =
                '<div class="drawer-tl-head"><i class="fas fa-history"></i> Routing History</div>' +
                '<div class="drawer-timeline"><div class="tl">' + tlHtml + '</div></div>';
        }

        // ─── View Document ───
        window.viewDoc = async function(trackingNumber) {
            if (!trackingNumber) return;
            trackingNumber = String(trackingNumber).trim().toUpperCase();

            document.getElementById('viewDrawerOverlay').classList.add('open');
            document.getElementById('viewDrawer').classList.add('open');
            document.body.style.overflow = 'hidden';
            document.getElementById('drTitle').textContent = '-';
            document.getElementById('drRef').textContent = trackingNumber;
            document.getElementById('drTrack').textContent = '';
            document.getElementById('viewDocContent').innerHTML = '<div class="drawer-loader"><span class="loading-dots"><span></span></span>Loading details...</div>';

            try {
                var data = await window.docTraxFetchJson('/api/track-document', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    timeoutMs: 15000,
                    body: JSON.stringify({ tracking_number: trackingNumber })
                });
                if (!data.success || !data.document) {
                    throw new Error(data.message || 'Unable to load tracking details.');
                }

                renderDrawer(data.document);
            } catch (error) {
                var fallback = docsData[trackingNumber];
                if (fallback) {
                    renderDrawer({
                        subject: fallback.subject || '-',
                        reference_number: fallback.reference_number || fallback.tracking_number || trackingNumber,
                        tracking_number: fallback.tracking_number || trackingNumber,
                        status: fallback.status || 'unknown',
                        status_label: fallback.status || 'Unknown',
                        status_color: '#64748b',
                        sender_name: fallback.sender_name || '-',
                        type: fallback.type || '-',
                        submitted_to_office: fallback.recipient_office || '-',
                        current_office: fallback.recipient_office || '-',
                        current_handler: 'Unassigned',
                        routing_logs: []
                    });
                } else {
                    document.getElementById('viewDocContent').innerHTML =
                        '<div class="drawer-loader">' + escapeHtml(window.describeRequestError(error, 'Failed to load tracking details.')) + '</div>';
                }
                showToast(window.describeRequestError(error, error.message || 'Failed to load tracking details.'), 'error');
            }
        };

        window.closeViewDrawer = function() {
            document.getElementById('viewDrawerOverlay').classList.remove('open');
            document.getElementById('viewDrawer').classList.remove('open');
            document.body.style.overflow = '';
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeViewDrawer();
            }
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

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeViewDrawer(); closeSidebar();
            }
        });

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
            var cooldown = 2000;
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
