<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>My Documents - DepEd DOCTRAX</title>
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

        .panel.list-panel.has-list .empty-state {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
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
        .t-date { font-size: 12px; color: #94a3b8; }
        .t-type { font-size: 12px; color: var(--text-muted); }
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

        /* ─── Drawer ─── */
        .drawer-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.35);
            z-index: 280;
            opacity: 0;
            pointer-events: none;
            transition: opacity .25s;
        }
        .drawer-overlay.open { opacity: 1; pointer-events: all; }

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

        .drawer-head-info { flex: 1; min-width: 0; }

        .drawer-head h3 {
            font-size: 16px;
            font-weight: 700;
            color: var(--text-dark);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 4px;
        }

        .drawer-ref { font-size: 13px; color: var(--text-muted); font-family: monospace; letter-spacing: .4px; margin-bottom: 2px; }
        .drawer-track { font-size: 11px; color: var(--text-muted); font-family: monospace; letter-spacing: .4px; margin-bottom: 4px; }

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
        .drawer-close:hover { background: #fee2e2; color: #dc2626; border-color: #fca5a5; }

        .drawer-body { flex: 1; overflow-y: auto; }

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

        .drawer-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 1px solid var(--border);
        }
        .dm-item { padding: 14px 20px; border-right: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; }
        .dm-item:nth-child(2n) { border-right: none; }
        .dm-label { font-size: 11px; text-transform: uppercase; letter-spacing: .6px; color: #94a3b8; font-weight: 600; margin-bottom: 3px; }
        .dm-value { font-size: 14px; color: var(--text-dark); font-weight: 500; word-break: break-word; }

        .drawer-tl-head { padding: 14px 20px 6px; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .8px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; }

        .drawer-timeline { padding: 10px 20px 24px; }

        .tl { position:relative; }
        .tl::before { content:''; position:absolute; left:7px; top:8px; bottom:8px; width:2px; background:var(--border); z-index:-1; }

        .tl-item { position: relative; margin-bottom: 20px; padding-left: 24px; }
        .tl-item:last-child { margin-bottom: 0; }

        .tl-dot { width: 16px; height: 16px; border-radius: 50%; border: 2.5px solid #fff; display: flex; align-items: center; justify-content: center; color: #fff; flex-shrink: 0; }
        .tl-dot.c-active { background: #22c55e; box-shadow: 0 0 0 2px #22c55e; }
        .tl-dot.c-done { background: #22c55e; box-shadow: 0 0 0 2px #22c55e; }
        .tl-dot.c-warn { background: #22c55e; box-shadow: 0 0 0 2px #22c55e; }
        .tl-dot.c-danger { background: #dc2626; box-shadow: 0 0 0 2px #dc2626; }
        .tl-dot.c-latest { background: #f59e0b; box-shadow: 0 0 0 2px #f59e0b; }

        .tl-action { font-size: 12px; font-weight: 700; color: #1b263b; }
        .tl-meta { font-size: 12px; color: #64748b; margin: 2px 0; }
        .tl-remarks { font-size: 12px; color: #64748b; background: #f8fafc; border-left: 3px solid var(--border); padding: 5px 9px; border-radius: 4px; margin-top: 5px; }
        .tl-office-hdr{display:flex;align-items:center;font-size:13px;font-weight:700;color:var(--text-dark);text-transform:none;letter-spacing:0;margin:18px 0 8px -7px;padding-left:7px;padding-bottom:6px;position:relative}
        .tl-office-hdr::after{content:'';position:absolute;left:21px;right:0;bottom:0;height:1.5px;background:var(--border)}
        .tl-office-hdr:first-child{margin-top:0}
        .tl-dur{font-size:10px;font-weight:600;color:#6366f1;background:#eef2ff;border:1px solid #c7d2fe;border-radius:20px;padding:1px 8px;text-transform:none;letter-spacing:0;white-space:nowrap;flex-shrink:0;margin-left:auto}
        .drawer-empty { color: var(--text-muted); font-size: 13px; padding: 4px 0; }

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

        /* Modal */
        .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:500; align-items:center; justify-content:center; padding:16px; }
        .modal-backdrop.show { display:flex; }
        .modal-box { background:#fff; border-radius:16px; max-width:420px; width:100%; padding:28px 24px; box-shadow:0 20px 60px rgba(0,0,0,.2); text-align:center; animation:modalIn .18s ease; }
        @keyframes modalIn { from { opacity:0; transform:scale(.95); } to { opacity:1; transform:scale(1); } }
        .modal-box h3 { font-size:17px; font-weight:700; color:var(--text-dark); margin-bottom:8px; }
        .modal-box p { font-size:13px; color:var(--text-muted); line-height:1.6; margin-bottom:22px; }
        .modal-icon-wrap { width:52px; height:52px; border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; font-size:22px; }
        .modal-actions { display:flex; gap:10px; justify-content:center; }
        .modal-actions button { padding:10px 20px; border-radius:9px; font-family:Poppins,sans-serif; font-size:13px; font-weight:600; cursor:pointer; border:1.5px solid var(--border); background:#fff; color:var(--text-dark); transition:background .15s; }
        .modal-actions .modal-danger { background:#dc2626; color:#fff; border-color:#dc2626; }
        .modal-actions .modal-danger:hover { background:#b91c1c; }
        .modal-actions .modal-danger:disabled { opacity:.6; cursor:not-allowed; }

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

        /* ─── Loading dots ─── */
        .loading-dots span { display:inline-block; width:8px; height:8px; margin:0 2px; border-radius:50%; background:var(--text-muted); animation:ldots 1s infinite; }
        .loading-dots span:nth-child(2){animation-delay:.2s}
        .loading-dots span:nth-child(3){animation-delay:.4s}
        @keyframes ldots{0%,80%,100%{opacity:.2;transform:scale(.8)}40%{opacity:1;transform:scale(1)}}

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
            .panel.list-panel.has-list { max-height: min(68vh, 560px); }
            .dtable th:nth-child(3), .dtable td:nth-child(3) { display: none; }
            .dtable th:nth-child(5), .dtable td:nth-child(5) { display: none; }
            .dash-footer { flex-direction: column; gap: 6px; text-align: center; padding: 16px 5%; }
            .drawer { width: 100%; max-width: 100%; }
            .drawer-meta { grid-template-columns: 1fr; }
            .dm-item { border-right: none; }
        }

        /* Office account My Documents UI */
        .main{margin-left:0;padding:60px 32px 60px;flex:1;display:block}
        .dash-wrapper{max-width:none;width:100%;margin:0;padding:0;flex:initial}
        .page-header{display:block;margin-bottom:28px;padding-bottom:20px;border-bottom:1px solid var(--border)}
        .page-header-top{display:flex;align-items:center;justify-content:space-between;gap:16px}
        .page-header h1{font-size:19px;font-weight:700;color:var(--text-dark);letter-spacing:-.2px}
        .page-header p{font-size:12.5px;color:var(--text-muted);margin-top:4px}
        .live-clock{display:flex;align-items:center;gap:14px;background:#fff;padding:10px 18px;border-radius:8px;border:1px solid var(--border);flex-shrink:0}
        .clock-time-display{font-size:18px;font-weight:600;color:var(--text-dark);font-variant-numeric:tabular-nums;line-height:1}
        .clock-time-display .seconds{font-size:14px;color:#9ca3af;font-weight:600}
        .clock-time-display .period{font-size:11px;font-weight:600;color:var(--text-muted);margin-left:3px;vertical-align:top}
        .clock-sep{width:1px;height:28px;background:var(--border)}
        .clock-date-display{font-size:13px;color:var(--text-muted);font-weight:400;line-height:1.4}
        .clock-date-display .day{font-weight:600;color:var(--text-dark);display:block}
        .search-card{background:#fff;border-radius:12px;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.06);padding:18px 22px;margin-bottom:20px;display:flex;gap:12px;align-items:center;flex-wrap:wrap}
        .search-wrap{position:relative;flex:1;min-width:200px}
        .search-wrap i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:14px;pointer-events:none}
        .search-wrap input{width:100%;padding:10px 14px 10px 38px;font-family:Poppins,sans-serif;font-size:13px;border:1.5px solid var(--border);border-radius:9px;outline:none;color:var(--text-dark);background:#fff;transition:border-color .2s,box-shadow .2s}
        .search-wrap input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,86,179,.1)}
        .search-wrap input::placeholder{color:#94a3b8}
        .status-select{padding:10px 36px 10px 14px;font-family:Poppins,sans-serif;font-size:13px;border:1.5px solid var(--border);border-radius:9px;outline:none;color:var(--text-dark);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%2394a3b8' d='M5 7L0 2h10z'/%3E%3C/svg%3E") no-repeat right 12px center;-webkit-appearance:none;appearance:none;cursor:pointer;min-width:160px;transition:border-color .2s,box-shadow .2s}
        .status-select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,86,179,.1)}
        .search-count{font-size:12px;color:var(--text-muted);white-space:nowrap}
        .table-card{background:#fff;border-radius:12px;border:1px solid var(--border);box-shadow:0 1px 3px rgba(0,0,0,.06);overflow:hidden}
        .table-card.list-table-card.has-list{display:flex;flex-direction:column;max-height:clamp(520px,72vh,820px)}
        .table-card.list-table-card.has-list .table-scroll{flex:1;min-height:0;overflow:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch}
        .table-card.list-table-card.has-list .table-scroll th{position:sticky;top:0;z-index:2}
        .table-card.list-table-card.has-list .empty-state{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center}
        .table-card.list-table-card.has-list .pagination-bar{flex-shrink:0}
        .table-head{padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
        .table-title{font-size:17px;font-weight:700;color:var(--text-dark)}
        .table-doc-count{font-size:12px;color:var(--text-muted);font-weight:400}
        .my-docs-panel .table-head{padding:14px 20px;border-bottom:1px solid var(--border);gap:12px;flex-wrap:wrap}
        .my-docs-panel .table-head-left{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
        .my-docs-panel table{width:100%;table-layout:fixed;border-collapse:collapse}
        .my-docs-panel .table-scroll{overflow-y:auto;overflow-x:hidden;scrollbar-gutter:stable}
        .my-docs-panel th{padding:10px 10px;background:#fff;text-align:left;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted);border-bottom:1px solid var(--border)}
        .my-docs-panel td{padding:10px 10px;font-size:13px;color:var(--text-dark);border-bottom:1px solid #f1f5f9;vertical-align:middle}
        .my-docs-panel tr:last-child td{border-bottom:none}
        .my-docs-panel tbody tr{transition:background .1s;cursor:pointer}
        .my-docs-panel tbody tr:hover td{background:#f8fafc}
        .my-docs-panel tbody tr.hidden-row{display:none}
        .my-docs-panel .col-ref{width:16%}
        .my-docs-panel .col-track{width:18%}
        .my-docs-panel .col-subject{width:24%}
        .my-docs-panel .col-office{width:18%}
        .my-docs-panel .col-date{width:12%}
        .my-docs-panel .col-status{width:12%}
        .my-docs-panel .col-action{width:44px}
        .my-docs-panel .t-ref,.my-docs-panel .t-track{font-family:monospace;font-size:12px;font-weight:600;white-space:nowrap}
        .my-docs-panel .t-ref{color:var(--primary)}
        .my-docs-panel .t-track{color:var(--text-dark)}
        .my-docs-panel .t-subject-main{display:block;font-size:13px;font-weight:600;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .my-docs-panel .t-subject-sub{display:block;margin-top:4px;font-size:11px;color:#94a3b8;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .my-docs-panel .t-office .cell-ellipsis{max-width:100%}
        .my-docs-panel .t-status{white-space:nowrap;min-width:0}
        .pill{display:inline-block;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;white-space:nowrap}
        .my-docs-panel .pill{padding:3px 9px;border-radius:20px;font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:.4px}
        .pill-submitted,
        .pill-received,
        .pill-in_review,
        .pill-forwarded,
        .pill-completed,
        .pill-for_pickup,
        .pill-on_hold,
        .pill-returned,
        .pill-cancelled,
        .pill-archived{background:#fff7ed;color:#c2410c}
        .pill-returned{background:#fef2f2;color:#dc2626}
        .pill-cancelled{background:#f8fafc;color:#64748b}
        .my-docs-panel .mob-cards{display:none;padding:12px}
        .my-docs-panel .mob-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:12px;box-shadow:0 1px 4px rgba(0,0,0,.04);cursor:pointer;transition:border-color .15s,box-shadow .15s}
        .my-docs-panel .mob-card + .mob-card{margin-top:10px}
        .my-docs-panel .mob-card:hover{border-color:var(--primary);box-shadow:0 2px 8px rgba(0,86,179,.08)}
        .my-docs-panel .mob-card-top{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:6px}
        .my-docs-panel .mob-card-ids{min-width:0}
        .my-docs-panel .mob-card-ref{font-size:11.5px;font-weight:700;color:var(--primary);font-family:monospace;line-height:1.25}
        .my-docs-panel .mob-card-track{font-size:10px;color:var(--text-muted);font-family:monospace;margin-top:2px;line-height:1.25}
        .my-docs-panel .mob-card-arrow{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:6px;color:#94a3b8;font-size:11px;flex-shrink:0;background:#f8fafc}
        .my-docs-panel .mob-card-subject{font-size:13.5px;font-weight:600;color:var(--text-dark);margin-bottom:8px;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .my-docs-panel .mob-card-meta{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px}
        .my-docs-panel .mob-card-date{font-size:10.5px;color:var(--text-muted);display:inline-flex;align-items:center;gap:4px;white-space:nowrap}
        .my-docs-panel .mob-card-row{display:flex;align-items:center;gap:8px;margin-top:10px;font-size:12px;color:var(--text-muted)}
        .my-docs-panel .mob-card-row i{font-size:11px;opacity:.75;flex-shrink:0}
        .my-docs-panel .mob-card:hover .mob-card-arrow{background:var(--primary);color:#fff}
        .site-footer{margin-left:0;width:100%;background:#fff;border-top:1px solid #e2e8f0;padding:20px 28px;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8}
        .site-footer .footer-left{display:flex;align-items:center;gap:6px}
        .site-footer .footer-right{font-size:11px;color:#b0b8c4}

        @media(max-width:900px){
            .main{padding:68px 16px 60px}
            .dash-wrapper{padding:0}
            .page-header-top{flex-direction:column;align-items:flex-start;gap:10px}
            .live-clock{display:none}
            .site-footer{padding:16px 5%;flex-direction:column;gap:6px;text-align:center}
            .search-card{flex-direction:column;align-items:stretch}
            .status-select{min-width:unset}
            .table-card.list-table-card.has-list{max-height:min(68vh,560px)}
            .my-docs-panel .table-scroll{display:none}
            .my-docs-panel .mob-cards{display:block;flex:1;min-height:0;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;padding:10px}
        }
    </style>
    <script src="{{ asset('js/spa.js') }}?v={{ filemtime(public_path('js/spa.js')) }}" defer></script>
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
        @if($user->isSuperAdmin())
        <a href="/admin/schools"><i class="fas fa-school"></i> Schools</a>
        @endif
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
        <a href="/my-documents" class="active"><i class="fas fa-folder"></i> My Documents</a>
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
    <div class="dash-wrapper">

        <div class="page-header">
            <div class="page-header-top">
                <div>
                    <h1>My Documents</h1>
                    <p>All documents you have submitted through the system.</p>
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
        </div>

        <!-- Search & Filter -->
        <form class="search-card" method="GET" action="/my-documents" id="searchForm" data-live-search data-live-debounce="700" data-live-min-interval="1200">
            <div class="search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" id="searchInput" name="search" placeholder="Search by tracking #, document control #, subject, or type..." data-clearable data-no-capitalize
                       value="{{ $search }}">
            </div>
            <select class="status-select" id="statusFilter" name="status">
                <option value="">All Statuses</option>
                @foreach(\App\Models\Document::FILTER_STATUSES as $key => $label)
                    @if($key === 'received')
                        @continue
                    @endif
                    <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <span class="search-count" id="resultCount"></span>
        </form>

        <!-- Documents Table -->
        <div class="table-card list-table-card my-docs-panel{{ $documents->count() ? ' has-list' : '' }}">
            <div class="table-head">
                <div class="table-head-left">
                    <span class="table-title">Results</span>
                    <span class="table-doc-count" id="totalCount">{{ \App\Support\UiNumber::compact($documents->total()) }} total</span>
                </div>
            </div>

            @if($documents->isEmpty() && !$search && !$status)
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Documents Yet</h3>
                    <p>Documents you submit will appear here.</p>
                </div>
            @else
                <div class="table-scroll">
                <table id="docsTable">
                    <colgroup>
                        <col class="col-ref">
                        <col class="col-track">
                        <col class="col-subject">
                        <col class="col-office">
                        <col class="col-date">
                        <col class="col-status">
                        <col class="col-action">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Tracking #</th>
                            <th>Document Control #</th>
                            <th>Subject</th>
                            <th>Current Office</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th class="td-action"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($documents as $doc)
                        @php
                            $docRef = $doc->reference_number ?: $doc->tracking_number;
                            $docTracking = $doc->tracking_number ?: $doc->reference_number;
                            $docLookup = $docTracking ?: $docRef;
                            $docOffice = $doc->status === 'submitted'
                                ? 'Awaiting physical submission to Records Section for routing to ' . ($doc->submittedToOffice->name ?? 'the selected destination office')
                                : ($doc->currentOffice->name ?? $doc->submittedToOffice->name ?? 'No office assigned');
                        @endphp
                        <tr class="doc-row" onclick='viewDoc(@json($docLookup))'>
                            <td class="t-ref">
                                <div class="cell-ellipsis" title="{{ $docRef }}">{{ $docRef }}</div>
                            </td>
                            <td class="t-track">
                                <div class="cell-ellipsis" title="{{ $docTracking }}">{{ $docTracking }}</div>
                            </td>
                            <td class="t-subject">
                                <span class="t-subject-main" title="{{ $doc->subject }}">{{ $doc->subject }}</span>
                                <span class="t-subject-sub" title="{{ $doc->type }}">{{ $doc->type }}</span>
                            </td>
                            <td class="t-office">
                                <div class="cell-ellipsis" title="{{ $docOffice }}">{{ $docOffice }}</div>
                            </td>
                            <td class="t-date">{{ $doc->created_at->format('M d, Y') }}</td>
                            <td class="t-status">
                                <span class="pill pill-{{ $doc->status }}">{{ $doc->statusLabel() }}</span>
                            </td>
                            <td class="td-action"><span class="row-arrow"><i class="fas fa-chevron-right"></i></span></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-search"></i>
                                    <h3>No Results Found</h3>
                                    <p>Try adjusting your search or filter.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                </div>
                <div class="mob-cards">
                    @forelse($documents as $doc)
                        @php
                            $docRef = $doc->reference_number ?: $doc->tracking_number;
                            $docTracking = $doc->tracking_number ?: $doc->reference_number;
                            $docLookup = $docTracking ?: $docRef;
                            $docOffice = $doc->status === 'submitted'
                                ? 'Awaiting physical submission to Records Section for routing to ' . ($doc->submittedToOffice->name ?? 'the selected destination office')
                                : ($doc->currentOffice->name ?? $doc->submittedToOffice->name ?? 'No office assigned');
                        @endphp
                        <div class="mob-card" onclick='viewDoc(@json($docLookup))'>
                            <div class="mob-card-top">
                                <div class="mob-card-ids">
                                    <div class="mob-card-ref">{{ $docRef }}</div>
                                    <div class="mob-card-track">Document Control #: {{ $docTracking }}</div>
                                </div>
                                <span class="mob-card-arrow"><i class="fas fa-chevron-right"></i></span>
                            </div>
                            <div class="mob-card-subject">{{ $doc->subject }}</div>
                            <div class="mob-card-meta">
                                <span class="pill pill-{{ $doc->status }}">{{ $doc->statusLabel() }}</span>
                                <span class="mob-card-date"><i class="fas fa-calendar"></i>{{ $doc->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="mob-card-row">
                                <i class="fas fa-building"></i>
                                <span class="cell-ellipsis" title="{{ $docOffice }}">{{ $docOffice }}</span>
                            </div>
                            <div class="mob-card-row">
                                <i class="fas fa-tag"></i>
                                <span class="cell-ellipsis" title="{{ $doc->type }}">{{ $doc->type }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <i class="fas fa-search"></i>
                            <h3>No Results Found</h3>
                            <p>Try adjusting your search or filter.</p>
                        </div>
                    @endforelse
                </div>
                @include('partials.shared-pagination', [
                    'paginator' => $documents,
                    'itemLabel' => 'documents',
                ])
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
            <div class="drawer-loader"><span class="loading-dots"><span></span><span></span><span></span></span>Loading details...</div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

    <footer class="site-footer">
        <div class="footer-left">
            <span>&copy; {{ date('Y') }} DepEd Document Tracking System</span>
        </div>
        <div class="footer-right">Developed by Raymond Bautista</div>
    </footer>

    <!-- Inline doc data for fallback -->
    <script type="application/json" id="docsData">
        @php
            $docData = [];
            foreach($documents as $doc) {
                $fallback = [
                    'reference_number' => $doc->reference_number ?: $doc->tracking_number,
                    'tracking_number'  => $doc->tracking_number,
                    'subject'          => $doc->subject,
                    'type'             => $doc->type ?? 'General',
                    'status'           => $doc->statusLabel(),
                    'sender_name'      => $user->name,
                    'recipient_office' => optional($doc->submittedToOffice)->name ?? 'No office assigned',
                    'description'      => $doc->description ?? 'No description provided',
                    'date'             => $doc->created_at->format('M d, Y h:i A'),
                ];
                $primaryKey = $doc->tracking_number ?: $doc->reference_number;
                if ($primaryKey) {
                    $docData[$primaryKey] = $fallback;
                }
                if ($doc->reference_number && $doc->reference_number !== $doc->tracking_number) {
                    $docData[$doc->reference_number] = $fallback;
                }
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
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        function dotClass(status) {
            var st = String(status || '').toLowerCase();
            if (st === 'cancelled' || /return|resubmit/.test(st)) return 'c-danger';
            if (status === 'completed') return 'c-done';
            if (status === 'forwarded') return 'c-warn';
            return 'c-active';
        }

        function renderDrawer(doc) {
            _currentDrawerRef = doc.reference_number || doc.tracking_number || '-';
            var ref = _currentDrawerRef;
            var trackingNo = doc.tracking_number || '';
            var normalizedStatus = (String(doc.status || '').toLowerCase() === 'forwarded' || String(doc.status_label || '').toLowerCase() === 'forwarded')
                ? 'Received'
                : (doc.status_label || doc.status || '-');

            document.getElementById('drTitle').textContent = doc.subject || '-';
            document.getElementById('drRef').textContent = 'TN · ' + ref;
            document.getElementById('drTrack').textContent = (trackingNo && trackingNo !== ref) ? ('Document Control # ' + trackingNo) : '';

            var logs = Array.isArray(doc.routing_logs) ? doc.routing_logs : [];
            var tlHtml = '';
            if (!logs.length) {
                tlHtml = '<div class="drawer-empty">No routing history yet.</div>';
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
                    var latestStatus = String(doc.status || '').toLowerCase();
                    var dc = isLatest
                        ? (latestStatus === 'completed' ? 'c-done' : (/return|resubmit/.test(latestStatus) ? 'c-danger' : 'c-latest'))
                        : dotClass(log.status_after);
                    var dotIcon = isLatest ? 'fa-arrow-up' : 'fa-check';
                    var groupKey = _gk(log);
                    var groupLabel = (groupKey === '__pending__') ? 'Submitted — Pending Physical Submission' : (((log.action === 'archived' || log.status_after === 'archived') && groupKey === 'Unknown') ? 'Archived' : groupKey);
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
            document.getElementById('viewDocContent').innerHTML =
                '<div class="drawer-loader"><span class="loading-dots"><span></span><span></span><span></span></span>Loading details...</div>';

            try {
                var data = await window.docTraxFetchJson('/api/internal/track-document', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                    timeoutMs: 15000,
                    body: JSON.stringify({ tracking_number: trackingNumber })
                });
                if (!data.success || !data.document) throw new Error(data.message || 'Unable to load tracking details.');
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
                        date: fallback.date || '-',
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
            if (e.key === 'Escape') { closeViewDrawer(); closeSidebar(); }
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

        (function(){
            var clockInterval;
            function tick(){
                var n = new Date();
                var h = n.getHours(), m = n.getMinutes(), s = n.getSeconds();
                var p = h >= 12 ? 'PM' : 'AM', h12 = h % 12 || 12;
                var hEl = document.getElementById('c-h');
                if (!hEl) { clearInterval(clockInterval); return; }
                hEl.textContent = String(h12).padStart(2, '0');
                document.getElementById('c-m').textContent = String(m).padStart(2, '0');
                document.getElementById('c-s').textContent = String(s).padStart(2, '0');
                document.getElementById('c-p').textContent = p;
                var days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
                var mos = ['January','February','March','April','May','June','July','August','September','October','November','December'];
                document.getElementById('c-day').textContent = days[n.getDay()];
                document.getElementById('c-date').textContent = mos[n.getMonth()] + ' ' + n.getDate() + ', ' + n.getFullYear();
            }
            tick();
            clockInterval = setInterval(tick, 1000);
        })();
    })();
    </script>
</div><!-- end .main -->
</body>
</html>
