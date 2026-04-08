@php
    $user = auth()->user();
    $isRep = $user->account_type === 'representative';
    $navOfficeName = $isRep ? ($user->representativeOfficeName() ?? 'Office') : null;
    $navRepName = $isRep ? $user->representativeDisplayName() : $user->name;
    $navDisplayName = $navOfficeName ?? $navRepName;
    $initials = collect(explode(' ', trim($navRepName ?: $user->name)))->filter()->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->implode('');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Office Reports - DepEd DTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #0056b3;
            --primary-dark: #004494;
            --bg: #f0f2f5;
            --border: #e2e8f0;
            --text-dark: #1b263b;
            --text-muted: #64748b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background: var(--bg);
            font-family: Poppins, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--text-dark);
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 240px;
            height: 100vh;
            background: #0056b3;
            display: flex;
            flex-direction: column;
            z-index: 200;
            transform: translateX(-100%);
            transition: transform .25s ease;
        }
        .sidebar.open { transform: translateX(0); }

        .sb-brand { padding: 22px 20px 18px; border-bottom: 1px solid rgba(255,255,255,.12); text-align: center; }
        .sb-brand img { width: 64px; height: 64px; margin-bottom: 8px; }
        .sb-brand h2 { font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 2px; }
        .sb-brand small { font-size: 11px; color: rgba(255,255,255,.65); display: block; }

        .sb-nav { flex: 1; padding: 12px 0; overflow-y: auto; }
        .sb-nav a {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 20px;
            color: rgba(255,255,255,.78);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: background .15s;
        }
        .sb-nav a:hover,
        .sb-nav a.active { background: rgba(255,255,255,.14); color: #fff; }
        .sb-nav a i { width: 16px; text-align: center; }
        .sb-nav .nav-section { padding: 10px 20px 4px; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,.4); font-weight: 600; }

        .sb-footer { padding: 14px 20px; border-top: 1px solid rgba(255,255,255,.12); }
        .sb-user { display: flex; align-items: center; gap: 10px; }
        .sb-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: rgba(255,255,255,.15);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
        }
        .sb-user-info small { font-size: 10px; color: rgba(255,255,255,.55); display: block; }
        .sb-user-info span { font-size: 12px; font-weight: 600; color: #fff; }

        .btn-logout {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            margin-top: 8px;
            padding: 8px 14px;
            background: rgba(255,255,255,.1);
            border: none;
            border-radius: 8px;
            color: rgba(255,255,255,.8);
            font-size: 12px;
            cursor: pointer;
            font-family: Poppins, sans-serif;
            width: 100%;
            transition: background .2s;
        }
        .btn-logout:hover { background: rgba(255,255,255,.2); }

        .mob-topbar {
            display: flex;
            position: sticky;
            top: 0;
            z-index: 100;
            background: #0056b3;
            padding: 12px 16px;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,.1);
        }

        .mob-hamburger{background:none;border:none;cursor:pointer;display:flex;flex-direction:column;gap:5px;z-index:1001;user-select:none;padding:4px}
        .mob-hamburger span{height:2px;width:24px;background:#fff;border-radius:2px;transition:all .4s ease}
        .mob-hamburger.toggle span:nth-child(1){transform:rotate(-45deg) translate(-4px,5px)}
        .mob-hamburger.toggle span:nth-child(2){opacity:0}
        .mob-hamburger.toggle span:nth-child(3){transform:rotate(45deg) translate(-4px,-5px)}

        .mob-brand{flex:1;display:flex;flex-direction:column;color:#fff;gap:4px}
        .mob-brand .brand-subtitle{font-size:clamp(10px,2.4vw,11px);font-weight:500;opacity:.88;text-transform:uppercase;letter-spacing:2.4px;line-height:1.1}
        .mob-brand h1{font-size:clamp(18px,4.8vw,22px);font-weight:700;margin:0;line-height:1.08}
        .mob-brand .brand-caption{font-size:clamp(11px,2.9vw,13px);font-weight:300;opacity:.9;line-height:1.18}

        .mob-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.35); z-index: 199; }
        .mob-overlay.open { display: block; }

        .main {
            margin-left: 0;
            padding: 60px 28px 50px;
            flex: 1;
        }

        .page-header { margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1px solid var(--border); }
        .page-header h1 { font-size: 19px; font-weight: 700; color: var(--text-dark); letter-spacing: -.2px; }
        .page-header p { font-size: 12.5px; color: var(--text-muted); margin-top: 4px; }

        .filters-card,
        .stats-wrap,
        .table-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0,0,0,.05);
        }
        .table-card.report-table-card.has-list { display:flex; flex-direction:column; max-height:clamp(520px,72vh,820px); overflow:hidden; }
        .table-card.report-table-card.has-list .table-scroll { flex:1; min-height:0; overflow:auto; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; }
        .table-card.report-table-card.has-list .table-scroll thead th { position:sticky; top:0; z-index:2; }
        .table-card.report-table-card.has-list .mob-doc-cards { flex:1; min-height:0; }
        .table-card.report-table-card.has-list .pager { flex-shrink:0; }

        .filters-card { padding: 16px 20px; margin-bottom: 16px; }
        .filters-row { display: flex; gap: 10px; align-items: center; flex-wrap: nowrap; }
        .filters-row .field-search { flex: 1; min-width: 0; }
        .filters-row .field-select { flex: 0 0 160px; min-width: 0; }
        .filters-row .field-select-sm { flex: 0 0 140px; min-width: 0; }
        .filters-divider { width: 1px; height: 32px; background: var(--border); flex-shrink: 0; }
        .btn-date-trigger {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 9px 14px; border-radius: 9px;
            border: 1.5px solid var(--border); background: #fff;
            font-family: Poppins, sans-serif; font-size: 12px; font-weight: 600;
            color: #475569; cursor: pointer; white-space: nowrap; transition: all .2s;
        }
        .btn-date-trigger:hover { border-color: var(--primary); color: var(--primary); }
        .btn-date-trigger.has-dates { border-color: var(--primary); color: var(--primary); background: #eff6ff; }
        .btn-date-trigger .date-badge { display:none; }
        .btn-date-trigger.has-dates .date-badge { display:inline-flex; align-items:center; justify-content:center; width:16px; height:16px; border-radius:50%; background:var(--primary); color:#fff; font-size:9px; font-weight:700; }
        .filters-actions { display: flex; gap: 8px; align-items: center; flex-shrink: 0; }

        /* Date Range Modal */
        .date-modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:500; align-items:center; justify-content:center; padding:16px; }
        .date-modal-overlay.show { display:flex; }
        .date-modal { background:#fff; border-radius:16px; width:100%; max-width:400px; box-shadow:0 20px 60px rgba(0,0,0,.18); animation:dmIn .18s ease; }
        @keyframes dmIn { from{opacity:0;transform:scale(.96)} to{opacity:1;transform:scale(1)} }
        .date-modal-head { padding:20px 22px 12px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid var(--border); }
        .date-modal-head h3 { font-size:15px; font-weight:700; color:var(--text-dark); display:flex; align-items:center; gap:8px; }
        .date-modal-close { width:30px; height:30px; border-radius:7px; border:1px solid var(--border); background:#f8fafc; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:13px; transition:all .15s; }
        .date-modal-close:hover { background:#fee2e2; color:#dc2626; border-color:#fca5a5; }
        .date-modal-body { padding:18px 22px; display:flex; flex-direction:column; gap:14px; }
        .date-field-group { display:flex; flex-direction:column; gap:5px; }
        .date-field-label { font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.6px; }
        .date-row { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
        .date-modal-foot { padding:14px 22px; border-top:1px solid var(--border); display:flex; gap:8px; justify-content:flex-end; }

        .field {
            width: 100%;
            padding: 10px 12px;
            border: 1.5px solid var(--border);
            border-radius: 9px;
            font-family: Poppins, sans-serif;
            font-size: 13px;
            color: var(--text-dark);
            outline: none;
            background: #fff;
        }
        .field:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0,86,179,.1);
        }
        .btn {
            border: none;
            border-radius: 9px;
            padding: 10px 14px;
            font-family: Poppins, sans-serif;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            cursor: pointer;
        }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); }
        .btn-light { background: #eef2f7; color: #334155; }
        .btn-light:hover { background: #e2e8f0; }
        .stats-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:16px; }
        .stat-card { background:#fff; border-radius:12px; padding:20px 22px 18px; border:1px solid var(--border); position:relative; overflow:hidden; }
        .stat-label { display:inline-flex; align-items:center; padding:5px 10px; border-radius:999px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; margin-bottom:12px; }
        .stat-card.c-blue .stat-label    { background:#eff6ff; color:#2563eb; }
        .stat-card.c-green .stat-label   { background:#fffbeb; color:#d97706; }
        .stat-card.c-amber .stat-label   { background:#fffbeb; color:#d97706; }
        .stat-card.c-emerald .stat-label { background:#f1f5f9; color:#64748b; }
        .stat-num   { font-size:32px; font-weight:800; color:var(--text-dark); line-height:1; letter-spacing:-1px; }
        .stat-sub   { font-size:11px; color:var(--text-muted); margin-top:6px; }

        .table-head {
            padding: 14px 20px;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .table-title { font-size: 17px; font-weight: 700; color: var(--text-dark); }
        .table-doc-count { font-size: 11px; color: #94a3b8; font-weight: 500; margin-left: 6px; }

        table { width: 100%; border-collapse: collapse; }
        th {
            text-align: left;
            padding: 11px 14px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .7px;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            background: #f8fafc;
        }

        td {
            padding: 12px 14px;
            font-size: 12.5px;
            color: var(--text-dark);
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }

        tr.doc-row {
            cursor: pointer;
            transition: background .15s;
        }

        tr.doc-row:hover td {
            background: #f8fafc;
        }

        tr:last-child td { border-bottom: none; }

        .badge {
            display: inline-block;
            padding: 3px 9px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .4px;
            white-space: nowrap;
        }

        .badge-submitted,
        .badge-received,
        .badge-in_review,
        .badge-forwarded,
        .badge-completed,
        .badge-for_pickup,
        .badge-returned,
        .badge-cancelled { background: #fff7ed; color: #c2410c; }

        .mono { font-family: Poppins, sans-serif; font-size: 12px; font-weight: 600; letter-spacing: .2px; }
        .mono.track { color: var(--primary); }
        .mono.ref { color: var(--text-dark); }
        .muted-sm { color: var(--text-muted); font-size: 11px; }

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
        tr.doc-row:hover .row-arrow { background: var(--primary); color: #fff; }
        .cell-ellipsis { display:block; max-width:100%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

        .empty-state { text-align: center; padding: 46px 20px; color: var(--text-muted); }
        .empty-state i { font-size: 40px; color: #cbd5e1; margin-bottom: 10px; display: block; }
        .empty-state h3 { font-size: 15px; font-weight: 600; color: #94a3b8; margin-bottom: 6px; }

        .pager { padding: 14px 20px; border-top: 1px solid var(--border); }
        .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-scroll table { min-width: 900px; }

        /* ─── Mobile Document Cards ─── */
        .mob-doc-cards { display: none; padding: 12px 14px; }
        .mob-doc-card { background: #fff; border: 1px solid var(--border); border-radius: 10px; padding: 14px; margin-bottom: 10px; cursor: pointer; transition: box-shadow .15s; }
        .mob-doc-card:active { box-shadow: 0 0 0 2px var(--primary); }
        .mob-doc-ref { font-family: Poppins, sans-serif; font-size: 12px; color: var(--primary); font-weight: 600; letter-spacing: .2px; }
        .mob-doc-subject { font-size: 13px; font-weight: 600; color: var(--text-dark); margin: 4px 0; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .mob-doc-meta { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-top: 6px; }
        .mob-doc-meta .badge { font-size: 9px; }
        .mob-doc-date { font-size: 10px; color: var(--text-muted); }
        .mob-doc-row { display: flex; align-items: center; justify-content: space-between; margin-top: 8px; }
        .mob-doc-sender { font-size: 11px; color: var(--text-muted); }
        .mob-doc-arrow { color: #94a3b8; font-size: 12px; }

        .drawer-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.35); z-index: 400; opacity: 0; pointer-events: none; transition: opacity .25s; }
        .drawer-overlay.open { opacity: 1; pointer-events: all; }
        .drawer { position: fixed; top: 0; right: 0; height: 100vh; width: 460px; max-width: 100vw; background: #fff; z-index: 401; box-shadow: -4px 0 24px rgba(0,0,0,.12); display: flex; flex-direction: column; transform: translateX(100%); transition: transform .28s cubic-bezier(.4,0,.2,1); }
        .drawer.open { transform: translateX(0); }
        .drawer-head { padding: 18px 22px; border-bottom: 1px solid var(--border); display: flex; align-items: flex-start; gap: 12px; }
        .drawer-head-info { flex: 1; min-width: 0; }
        .drawer-head h3 { font-size: 16px; font-weight: 700; color: var(--text-dark); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px; }
        .drawer-ref { font-size: 13px; color: var(--text-muted); font-family: Poppins, sans-serif; font-weight: 600; letter-spacing: .2px; margin-bottom: 2px; }
        .drawer-track { font-size: 11px; color: var(--text-muted); font-family: Poppins, sans-serif; font-weight: 500; letter-spacing: .2px; margin-bottom: 4px; }
        .drawer-close { width: 32px; height: 32px; border-radius: 8px; border: 1px solid var(--border); background: #f8fafc; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--text-muted); font-size: 14px; flex-shrink: 0; transition: all .15s; }
        .drawer-close:hover { background: #fee2e2; color: #dc2626; border-color: #fca5a5; }
        .drawer-body { flex: 1; overflow-y: auto; }
        .drawer-meta { display: grid; grid-template-columns: 1fr 1fr; border-bottom: 1px solid var(--border); }
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
        .tl-dot.c-danger { background: #22c55e; box-shadow: 0 0 0 2px #22c55e; }
        .tl-dot.c-latest { background: #f59e0b; box-shadow: 0 0 0 2px #f59e0b; }
        .tl-action { font-size: 12px; font-weight: 700; color: #1b263b; }
        .tl-meta { font-size: 12px; color: #64748b; margin: 2px 0; }
        .tl-remarks { font-size: 12px; color: #64748b; background: #f8fafc; border-left: 3px solid var(--border); padding: 5px 9px; border-radius: 4px; margin-top: 5px; }
        .tl-office-hdr{display:flex;align-items:center;font-size:13px;font-weight:700;color:var(--text-dark);text-transform:none;letter-spacing:0;margin:18px 0 8px -7px;padding-left:7px;padding-bottom:6px;position:relative}
        .tl-office-hdr::after{content:'';position:absolute;left:21px;right:0;bottom:0;height:1.5px;background:var(--border)}
        .tl-office-hdr:first-child{margin-top:0}
        .drawer-loader { display: flex; align-items: center; justify-content: center; padding: 48px; flex-direction: column; gap: 12px; color: var(--text-muted); font-size: 13px; text-align: center; }
        .spin { width: 22px; height: 22px; border: 3px solid #e2e8f0; border-top-color: var(--primary); border-radius: 50%; animation: spin .7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .print-meta {
            display: none;
            margin-top: 8px;
            font-size: 11px;
            color: #475569;
            line-height: 1.5;
        }

        .site-footer {
            margin-left: 0;
            width: 100%;
            background: #fff;
            border-top: 1px solid #e2e8f0;
            padding: 20px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            color: #94a3b8;
        }

        .site-footer .footer-right { font-size: 11px; color: #b0b8c4; }

        /* Section Divider */
        .section-divider { display:flex; align-items:center; gap:10px; margin:28px 0 16px; padding-bottom:10px; border-bottom:2px solid var(--border); }
        .section-divider i { font-size:16px; color:var(--primary); }
        .section-divider span { font-size:15px; font-weight:700; color:var(--text-dark); }

        /* User filter banner */
        .user-filter-bar { display:flex; align-items:center; gap:10px; padding:10px 16px; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; margin-bottom:14px; font-size:13px; color:#1e40af; font-weight:500; flex-wrap:wrap; }
        .user-filter-bar .clear-lnk { color:#1e40af; font-size:12px; font-weight:600; text-decoration:none; margin-left:auto; padding:4px 10px; border-radius:7px; background:#dbeafe; display:flex; align-items:center; gap:5px; }
        .user-filter-bar .clear-lnk:hover { background:#bfdbfe; }

        /* Staff Performance */
        .users-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:14px; margin-bottom:16px; }
        .user-card { background:#fff; border-radius:12px; border:1px solid var(--border); padding:18px 18px 14px; display:flex; flex-direction:column; gap:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); transition:box-shadow .2s; }
        .user-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.1); }
        .user-card-head { display:flex; align-items:center; gap:12px; }
        .u-avatar { width:42px; height:42px; border-radius:50%; background:#eff6ff; color:var(--primary); font-size:15px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .u-avatar.is-individual { background:#f5f3ff; color:#7c3aed; }
        .u-type-badge { display:block; font-size:9.5px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.6px; margin-bottom:1px; }
        .u-type-badge.rep { color:var(--primary); }
        .u-type-badge.ind { color:#7c3aed; }
        .u-name { font-size:13px; font-weight:700; color:var(--text-dark); line-height:1.3; }
        .u-office { font-size:11px; color:var(--text-muted); margin-top:2px; }
        .u-stats-row { display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px; }
        .u-stat { background:#f8fafc; border-radius:8px; padding:8px 8px; }
        .u-stat-num { font-size:20px; font-weight:800; color:var(--text-dark); line-height:1; }
        .u-stat-num.perf-num { color:#16a34a; font-size:22px; }
        .u-stat-lbl { font-size:9px; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:.5px; margin-top:3px; }
        .btn-view-act { display:flex; align-items:center; justify-content:center; gap:6px; padding:8px 12px; border-radius:9px; background:var(--primary); color:#fff; font-size:12px; font-weight:600; text-decoration:none; border:none; cursor:pointer; font-family:Poppins,sans-serif; transition:background .2s; margin-top:auto; }
        .btn-view-act:hover { background:var(--primary-dark); }
        .u-section-label { font-size:10px; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; display:flex; align-items:center; margin-bottom:4px; }

        /* User Activity Drawer */
        .ua-stats-row { display:grid; grid-template-columns:repeat(3,1fr); gap:8px; padding:14px 20px; border-bottom:1px solid var(--border); }
        .ua-stat { background:#f8fafc; border-radius:8px; padding:8px 10px; text-align:center; }
        .ua-stat-num { font-size:20px; font-weight:800; color:var(--text-dark); line-height:1; }
        .ua-stat-lbl { font-size:10px; color:var(--text-muted); font-weight:600; text-transform:uppercase; letter-spacing:.4px; margin-top:3px; }

        /* UA filter grouping wrappers */
        .ua-filter-bar { display:flex; gap:8px; align-items:center; padding:10px 20px; background:#f8fafc; border-bottom:1px solid var(--border); }
        .ua-filter-search { flex:1; min-width:0; position:relative; }
        .ua-filter-search i.fa-search { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:11px; pointer-events:none; }
        .ua-filter-search .ua-clear-btn { position:absolute; right:6px; top:50%; transform:translateY(-50%); width:20px; height:20px; border:none; background:#e2e8f0; border-radius:50%; cursor:pointer; display:none; align-items:center; justify-content:center; color:#64748b; font-size:10px; transition:background .15s,color .15s; padding:0; line-height:1; }
        .ua-filter-search .ua-clear-btn:hover { background:#fca5a5; color:#dc2626; }
        .ua-filter-search .ua-clear-btn.show { display:flex; }
        .ua-table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }

        /* UA filter bar field styles */
        .ua-field { width:100%; padding:8px 30px 8px 30px; border:1.5px solid var(--border); border-radius:8px; font-size:12px; outline:none; font-family:Poppins,sans-serif; color:var(--text-dark); background:#fff; transition:border-color .2s,box-shadow .2s; }
        .ua-field:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(0,86,179,.08); }

        /* Three-dots menu trigger */
        .ua-dots-btn { width:34px; height:34px; flex-shrink:0; border:1.5px solid var(--border); border-radius:8px; background:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:15px; transition:all .15s; position:relative; }
        .ua-dots-btn:hover { border-color:var(--primary); color:var(--primary); background:#eff6ff; }
        .ua-dots-btn.active { border-color:var(--primary); color:var(--primary); background:#eff6ff; }
        .ua-dots-btn .ua-dots-badge { display:none; position:absolute; top:-4px; right:-4px; width:14px; height:14px; border-radius:50%; background:var(--primary); color:#fff; font-size:8px; font-weight:700; align-items:center; justify-content:center; }
        .ua-dots-btn.has-filters .ua-dots-badge { display:flex; }

        /* Dropdown panel */
        .ua-dropdown { display:none; position:absolute; top:calc(100% + 6px); right:0; width:260px; background:#fff; border:1.5px solid var(--border); border-radius:12px; box-shadow:0 8px 30px rgba(0,0,0,.12); z-index:20; padding:12px; animation:uaDropIn .15s ease; }
        .ua-dropdown.show { display:block; }
        @keyframes uaDropIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
        .ua-drop-label { font-size:10px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:.5px; margin-bottom:6px; }
        .ua-drop-group { margin-bottom:10px; }
        .ua-drop-group:last-child { margin-bottom:0; }
        .ua-select { width:100%; padding:8px 28px 8px 10px; border:1.5px solid var(--border); border-radius:8px; font-size:12px; outline:none; font-family:Poppins,sans-serif; color:var(--text-dark); background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%2394a3b8' d='M5 7L0 2h10z'/%3E%3C/svg%3E") no-repeat right 10px center; -webkit-appearance:none; appearance:none; cursor:pointer; transition:border-color .2s; }
        .ua-select:focus { border-color:var(--primary); }
        .ua-drop-actions { display:flex; gap:6px; }
        .ua-btn { padding:8px 12px; border:none; border-radius:8px; font-size:12px; cursor:pointer; font-family:Poppins,sans-serif; display:inline-flex; align-items:center; justify-content:center; font-weight:600; transition:opacity .2s; white-space:nowrap; flex:1; }
        .ua-btn:hover { opacity:.85; }
        .ua-btn-primary { background:var(--primary); color:#fff; }
        .ua-btn-pdf { background:#fef2f2; border:1px solid #fca5a5; color:#dc2626; }
        .ua-btn-date { width:100%; padding:8px 10px; border:1.5px solid var(--border); border-radius:8px; background:#fff; color:var(--text-muted); font-size:12px; font-family:Poppins,sans-serif; cursor:pointer; display:flex; align-items:center; gap:6px; transition:border-color .2s,color .2s,background .2s; font-weight:500; }
        .ua-btn-date:hover { border-color:var(--primary); color:var(--primary); }
        .ua-btn-date.has-dates { border-color:var(--primary); color:var(--primary); background:#eff6ff; font-weight:600; }
        .ua-btn-date .ua-date-badge { display:none; width:16px; height:16px; border-radius:50%; background:var(--primary); color:#fff; font-size:9px; font-weight:700; align-items:center; justify-content:center; margin-left:auto; }
        .ua-btn-date.has-dates .ua-date-badge { display:inline-flex; }

        /* PDF confirm modal */
        .pdf-confirm-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:600; align-items:center; justify-content:center; padding:16px; }
        .pdf-confirm-overlay.show { display:flex; }
        .pdf-confirm { background:#fff; border-radius:14px; width:100%; max-width:360px; padding:24px; box-shadow:0 20px 60px rgba(0,0,0,.18); animation:dmIn .18s ease; text-align:center; }
        .pdf-confirm-icon { width:48px; height:48px; border-radius:50%; background:#fff7ed; display:inline-flex; align-items:center; justify-content:center; margin-bottom:14px; }
        .pdf-confirm-icon i { font-size:20px; color:#f59e0b; }
        .pdf-confirm h3 { font-size:15px; font-weight:700; color:var(--text-dark); margin-bottom:6px; }
        .pdf-confirm p { font-size:12.5px; color:var(--text-muted); line-height:1.6; margin-bottom:20px; }
        .pdf-confirm-btns { display:flex; gap:8px; }
        .pdf-confirm-btns button { flex:1; padding:10px 0; border-radius:8px; font-size:13px; font-weight:600; font-family:'Poppins',sans-serif; cursor:pointer; border:none; transition:opacity .15s; }
        .pdf-confirm-btns .btn-pdf-cancel { background:#f1f5f9; color:var(--text-dark); }
        .pdf-confirm-btns .btn-pdf-cancel:hover { background:#e2e8f0; }
        .pdf-confirm-btns .btn-pdf-proceed { background:var(--primary); color:#fff; }
        .pdf-confirm-btns .btn-pdf-proceed:hover { opacity:.85; }

        @media (max-width: 1160px) {
            .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }

        @media (max-width: 900px) {
            .main { padding: 68px 14px 28px; }
            .site-footer { padding: 16px 5%; flex-direction: column; gap: 6px; text-align: center; }
            /* Page header */
            .page-header { padding-bottom: 14px; margin-bottom: 18px; }
            .page-header h1 { font-size: 16px; }
            /* Filters — stack vertically on mobile (too many items for one row) */
            .filters-card { padding: 14px 14px; }
            .filters-row { flex-wrap: wrap; gap: 8px; }
            .filters-row .field-search { flex: 1 1 100%; }
            .filters-row .field-select { flex: 1 1 calc(50% - 4px); }
            .filters-row .field-select-sm { flex: 1 1 calc(50% - 4px); }
            .filters-row .field { font-size: 12px; padding: 9px 10px; }
            .filters-row .field-search .field { padding-left: 30px; }
            .filters-divider { display: none; }
            .btn-date-trigger { flex: 1 1 100%; justify-content: center; font-size: 12px; padding: 9px 12px; }
            .filters-actions { flex: 1 1 100%; }
            .filters-actions .btn { flex: 1; justify-content: center; }
            /* Stats */
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 8px; }
            .stat-card { padding: 14px 14px; }
            .stat-num { font-size: 24px; }
            .stat-sub { font-size: 10px; }
            /* Table head */
            .table-head { flex-direction: column; align-items: stretch; gap: 10px; padding: 12px 14px; }
            .table-head > div:last-child { width: 100%; display: flex; gap: 6px; }
            .table-head > div:last-child > a,
            .table-head > div:last-child > button { flex: 1; justify-content: center; font-size: 11px; padding: 8px 10px; }
            .table-card.report-table-card.has-list { max-height:min(68vh,560px); }
            /* Hide desktop table, show mobile cards */
            .table-scroll { display: none; }
            .mob-doc-cards { display: block; overflow-y:auto; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; }
            /* Drawer */
            .drawer { width: 100%; max-width: 100%; }
            .drawer-meta { grid-template-columns: 1fr; }
            .dm-item { border-right: none; }
            /* Users grid */
            .users-grid { grid-template-columns: 1fr 1fr; gap: 10px; }
            /* User activity drawer */
            .ua-stats-row { grid-template-columns: 1fr 1fr; }
            #uaDrawer { width: 100% !important; }
            /* UA filter bar */
            #uaFilterBar { padding: 10px 14px !important; gap: 6px !important; }
            .ua-field { padding: 9px 30px !important; font-size: 13px !important; }
            .ua-dropdown { width: 240px; right: 0; }
            .ua-btn { padding: 9px 12px !important; font-size: 13px !important; }
            /* UA table scroll */
            #uaBody .ua-table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            #uaBody .ua-table-scroll table { min-width: 420px; }
            /* User stats */
            .u-stat-num { font-size: 16px; }
            .u-stat-num.perf-num { font-size: 18px; }
            .u-stat { padding: 6px 6px; }
            .u-stat-lbl { font-size: 8px; }
            /* User card tighter */
            .user-card { padding: 14px 12px 12px; gap: 10px; }
            .u-name { font-size: 12px; }
            .u-office { font-size: 10px; }
            .u-type-badge { font-size: 8.5px; }
            .u-avatar { width: 36px; height: 36px; font-size: 13px; }
            .btn-view-act { font-size: 11px; padding: 8px 10px; }
            /* User filter bar */
            .user-filter-bar { font-size: 12px; padding: 8px 12px; flex-direction: column; align-items: flex-start; gap: 6px; }
            .user-filter-bar .clear-lnk { margin-left: 0; }
            /* Pager */
            .pager { padding: 10px 14px; }
        }

        @media (max-width: 600px) {
            .users-grid { grid-template-columns: 1fr; gap: 8px; }
            .ua-stats-row { grid-template-columns: 1fr 1fr; }
            .stats-grid { grid-template-columns: 1fr 1fr; gap: 6px; }
            .stat-card { padding: 12px 12px; }
            .stat-num { font-size: 20px; }
            .stat-sub { font-size: 9px; }
            .stat-label { font-size: 9px; margin-bottom: 6px; }
            .filters-actions .btn { width: 100%; }
        }

        @media print {
            body { background: #fff; }
            .sidebar, .mob-topbar, .mob-overlay,
            .filters-card, .filters-actions,
            .stats-grid, .pager, .site-footer,
            .row-arrow, .mob-doc-arrow, .date-modal-overlay,
            .users-grid,
            .section-divider { display: none !important; }
            .main { margin: 0 !important; padding: 0 !important; }
            .table-card { box-shadow: none; border: 1px solid #dbe2ea; }
            .print-meta { display: block; }
        }
    </style>
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
</head>
<body>
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
        @if($user->isSuperAdmin())
        <span class="nav-section">Overview</span>
        <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <span class="nav-section">Management</span>
        <a href="/admin/users"><i class="fas fa-users"></i> Users</a>
        <a href="/admin/offices"><i class="fas fa-building"></i> Offices</a>
        @unless($user->isSuperAdmin())
        <a href="/admin/documents"><i class="fas fa-folder-open"></i> Documents</a>
        @endunless
        <a href="/records/documents"><i class="fas fa-folder-open"></i> All Documents</a>
        <span class="nav-section">ICT Unit</span>
        <a href="/ict/documents"><i class="fas fa-network-wired"></i> ICT Documents</a>
        <a href="/office/search" class="active"><i class="fas fa-chart-line"></i> Reports</a>
        <span class="nav-section">My Documents</span>
        <a href="/submit"><i class="fas fa-paper-plane"></i> Submit Document</a>
        <a href="/my-documents"><i class="fas fa-folder"></i> My Documents</a>
        <a href="/track"><i class="fas fa-search"></i> Track Document</a>
        <span class="nav-section">Account</span>
        <a href="/profile"><i class="fas fa-user-cog"></i> My Profile</a>
        @else
        <span class="nav-section">Office</span>
        <a href="/office/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/office/search" id="reports-nav-link" class="active" style="{{ $user->hasReportsAccess() ? '' : 'display:none' }}"><i class="fas fa-chart-line"></i> Reports</a>
        @if($user->isRecords())
        <span class="nav-section">Records Section</span>
        <a href="/records/documents"><i class="fas fa-folder-open"></i> All Documents</a>
        @endif
        <span class="nav-section">My Documents</span>
        <a href="/submit"><i class="fas fa-paper-plane"></i> Submit Document</a>
        <a href="/my-documents"><i class="fas fa-folder"></i> My Documents</a>
        <span class="nav-section">Account</span>
        <a href="/profile"><i class="fas fa-user-cog"></i> My Profile</a>
        @endif
    </nav>
    <div class="sb-footer">
        <div class="sb-user">
            @if($user->isSuperAdmin())
            <div class="sb-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="sb-user-info">
                <small>Super Admin</small>
                <span>{{ explode(' ', $user->name)[0] }}</span>
            </div>
            @else
            <div class="sb-avatar">{{ $initials }}</div>
            <div class="sb-user-info">
                <small>{{ $navOfficeName ?? 'Office' }}</small>
                <span>{{ $navRepName ?? $navDisplayName }}</span>
            </div>
            @endif
        </div>
        <button onclick="logout()" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>
</div>

<div class="main">
    <div class="page-header">
        <h1>Reports</h1>
        <p>Documents and staff activity for {{ $office?->name ?? 'your office' }}.</p>
        <div class="print-meta">
            Generated: {{ now()->setTimezone('Asia/Manila')->format('M d, Y h:i A') }}<br>
            @if($office) Office: {{ $office->name }} @endif
        </div>
    </div>

    {{-- Documents Section --}}
    @if($selectedUser)
    @php
        $selIsRep = $selectedUser->account_type === 'representative';
        if ($selIsRep && str_contains($selectedUser->name, ' - ')) {
            [$selOfficePart, $selDisplayName] = explode(' - ', $selectedUser->name, 2);
            $selOffice = $selectedUser->office?->name ?? $selOfficePart;
        } else {
            $selDisplayName = $selectedUser->name;
            $selOffice = $selectedUser->office?->name ?? ($selIsRep ? 'No Office' : $selectedUser->email);
        }
    @endphp
    <div class="user-filter-bar">
        <i class="fas fa-user-circle" style="font-size:16px"></i>
        <span>Activity for: <strong>{{ $selDisplayName }}</strong> &mdash; {{ $selOffice }}</span>
        <a class="clear-lnk" href="{{ route('office.search') }}"><i class="fas fa-times"></i> Clear</a>
    </div>
    @endif

    <div class="filters-card">
        <form method="GET" action="{{ route('office.search') }}" id="reportForm" data-live-search>
            {{-- Hidden date fields — values set by modal --}}
            <input type="hidden" name="date_field" id="hDateField" value="{{ $filters['date_field'] ?: 'created_at' }}">
            <input type="hidden" name="date_from"  id="hDateFrom"  value="{{ $filters['date_from'] }}">
            <input type="hidden" name="date_to"    id="hDateTo"    value="{{ $filters['date_to'] }}">

            <div class="filters-row">
                <div class="field-search" style="position:relative">
                    <input class="field" style="padding-left:34px" type="text" name="search" value="{{ $filters['search'] }}" placeholder="Search reference, subject, sender..." data-clearable data-no-capitalize>
                    <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:12px;pointer-events:none"></i>
                </div>

                <select class="field field-select" name="status">
                    <option value="">All Statuses</option>
                    @foreach($reportStatusOptions as $key => $label)
                        <option value="{{ $key }}" {{ $filters['status'] === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>

                <select class="field field-select-sm" name="type">
                    <option value="">All Types</option>
                    @foreach($availableTypes as $docType)
                        <option value="{{ $docType }}" {{ $filters['type'] === $docType ? 'selected' : '' }}>{{ $docType }}</option>
                    @endforeach
                </select>

                <div class="filters-divider"></div>

                <button type="button" class="btn-date-trigger {{ ($filters['date_from'] || $filters['date_to']) ? 'has-dates' : '' }}" id="dateRangeBtn" onclick="openDateModal()">
                    <i class="fas fa-calendar-alt"></i>
                    Date Range
                    <span class="date-badge">!</span>
                </button>

                <div class="filters-actions">
                    <button class="btn btn-primary" type="submit" data-no-auto-loading>@include('partials.filter-icon', ['size' => 16]) Apply Filters</button>
                    <a class="btn btn-light" href="{{ route('office.search') }}"><i class="fas fa-rotate-left"></i> Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Date Range Modal --}}
    <div class="date-modal-overlay" id="dateModalOverlay" onclick="if(event.target===this)closeDateModal()">
        <div class="date-modal">
            <div class="date-modal-head">
                <h3><i class="fas fa-calendar-alt" style="color:var(--primary)"></i> Set Date Range</h3>
                <button class="date-modal-close" onclick="closeDateModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="date-modal-body">
                <div class="date-field-group">
                    <label class="date-field-label">Date Basis</label>
                    <select class="field" id="mDateField">
                        <option value="created_at"    {{ ($filters['date_field'] ?: 'created_at') === 'created_at'    ? 'selected' : '' }}>Date Submitted</option>
                        <option value="last_action_at" {{ ($filters['date_field'] ?: 'created_at') === 'last_action_at' ? 'selected' : '' }}>Last Action Date</option>
                    </select>
                </div>
                <div class="date-field-group">
                    <label class="date-field-label">Date Range</label>
                    <div class="date-row">
                        <div>
                            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px">From</div>
                            <input class="field" type="date" id="mDateFrom" value="{{ $filters['date_from'] }}">
                        </div>
                        <div>
                            <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px">To</div>
                            <input class="field" type="date" id="mDateTo" value="{{ $filters['date_to'] }}">
                        </div>
                    </div>
                </div>
            </div>
            <div class="date-modal-foot">
                <button type="button" class="btn btn-light" onclick="clearDateModal()"><i class="fas fa-rotate-left"></i> Clear</button>
                <button type="button" class="btn btn-primary" onclick="applyDateModal()"><i class="fas fa-check"></i> Apply</button>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card c-blue">
            <div class="stat-label">Total Results</div>
            <div class="stat-num">{{ \App\Support\UiNumber::compact($reportStats['total']) }}</div>
            <div class="stat-sub">Matching your filters</div>
        </div>
        <div class="stat-card c-green">
            <div class="stat-label">Processing</div>
            <div class="stat-num">{{ \App\Support\UiNumber::compact($reportStats['processing']) }}</div>
            <div class="stat-sub">Being processed</div>
        </div>
        <div class="stat-card c-emerald">
            <div class="stat-label">Completed / Returned</div>
            <div class="stat-num">{{ \App\Support\UiNumber::compact($reportStats['completed']) }}</div>
            <div class="stat-sub">Completed and closed</div>
        </div>
    </div>

    <div class="table-card report-table-card{{ $documents->isNotEmpty() ? ' has-list' : '' }}">
        <div class="table-head">
            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                <span class="table-title"><i class="fas fa-file-chart-column" style="color:var(--primary);margin-right:6px"></i>Processing Report</span>
                <span class="table-doc-count">{{ \App\Support\UiNumber::compact($documents->total()) }} row(s)</span>
            </div>
            <div style="display:flex;gap:8px;align-items:center">
                <button type="button" id="docPdfBtn" title="Download PDF Report" style="display:inline-flex;align-items:center;gap:6px;padding:7px 13px;border-radius:8px;background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;font-size:12px;font-weight:600;cursor:pointer;font-family:'Poppins',sans-serif;transition:background .2s" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'" onclick="handleDocPdfClick()"><i class="fas fa-file-pdf"></i> Download PDF</button>
            </div>
        </div>

        @if($documents->isEmpty())
            <div class="empty-state">
                <i class="fas fa-chart-line"></i>
                <h3>No Report Data</h3>
                <p>Try adjusting the filters to see matching records.</p>
            </div>
        @else
            <div class="table-scroll">
            <table>
                <thead>
                <tr>
                    <th>Tracking #</th>
                    <th>Reference #</th>
                    <th>Subject</th>
                    <th>Type</th>
                    <th>Submitted By</th>
                    <th>Process</th>
                    <th>Tagged To</th>
                    <th>Submitted At</th>
                    <th>Last Action</th>
                    <th class="td-action"></th>
                </tr>
                </thead>
                <tbody>
                @foreach($documents as $doc)
                    <tr
                        class="doc-row"
                        data-ref="{{ $doc->reference_number ?: $doc->tracking_number }}"
                        data-tracking="{{ $doc->tracking_number }}"
                        tabindex="0"
                        role="button"
                        aria-label="View routing details"
                        onclick="openDocDetail(@json($doc->reference_number ?: $doc->tracking_number), @json($doc->tracking_number))"
                        onkeydown="if(event.key==='Enter'||event.key===' '){ event.preventDefault(); openDocDetail(@json($doc->reference_number ?: $doc->tracking_number), @json($doc->tracking_number)); }"
                    >
                        <td><div class="mono track">{{ $doc->tracking_number }}</div></td>
                        <td><div class="mono ref">{{ $doc->reference_number ?: 'N/A' }}</div></td>
                        <td style="max-width:200px"><div class="cell-ellipsis" title="{{ $doc->subject }}">{{ $doc->subject }}</div></td>
                        <td><div class="cell-ellipsis" style="max-width:160px" title="{{ $doc->type }}">{{ $doc->type }}</div></td>
                        <td><div class="cell-ellipsis" style="max-width:170px" title="{{ $doc->sender_name }}">{{ $doc->sender_name }}</div></td>
                        <td><span class="badge badge-{{ $doc->status }}">{{ $doc->statusLabel() }}</span></td>
                        <td>{{ $doc->currentHandler?->name ?? 'Unassigned' }}</td>
                        <td class="muted-sm">{{ $doc->created_at?->copy()->setTimezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
                        <td class="muted-sm">{{ $doc->last_action_at ? $doc->last_action_at->copy()->setTimezone('Asia/Manila')->format('M d, Y h:i A') : '-' }}</td>
                        <td class="td-action">
                            <span class="row-arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>

            {{-- Mobile document cards (visible < 900px) --}}
            <div class="mob-doc-cards">
                @foreach($documents as $doc)
                    <div class="mob-doc-card" onclick="openDocDetail(@json($doc->reference_number ?: $doc->tracking_number), @json($doc->tracking_number))">
                        <div class="mob-doc-ref">{{ $doc->tracking_number }}</div>
                        <div style="font-size:11px;color:var(--text-muted);font-family:Poppins,sans-serif;font-weight:500;margin-top:2px">Ref: {{ $doc->reference_number ?: 'N/A' }}</div>
                        <div class="mob-doc-subject">{{ $doc->subject }}</div>
                        <div class="mob-doc-meta">
                            <span class="badge badge-{{ $doc->status }}">{{ $doc->statusLabel() }}</span>
                            <span class="mob-doc-date">{{ $doc->last_action_at ? $doc->last_action_at->copy()->setTimezone('Asia/Manila')->format('M d, Y h:i A') : '-' }}</span>
                        </div>
                        <div class="mob-doc-row">
                            <span class="mob-doc-sender"><i class="fas fa-user" style="margin-right:4px;opacity:.5"></i>{{ $doc->sender_name }}</span>
                            <span class="mob-doc-arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                        </div>
                    </div>
                @endforeach
            </div>
            @include('partials.shared-pagination', [
                'paginator' => $documents,
                'itemLabel' => 'documents',
            ])
        @endif
    </div>

    {{-- ────────────────── OFFICE STAFF PERFORMANCE ────────────────── --}}
    <div class="section-divider">
        <i class="fas fa-users"></i>
        <span>Office Staff Performance</span>
    </div>

    @if($users->isEmpty())
        <div class="table-card">
            <div class="empty-state">
                <i class="fas fa-users"></i>
                <h3>No Staff Members</h3>
                <p>No active staff members found for this office.</p>
            </div>
        </div>
    @else
        <div class="users-grid">
            @foreach($users as $u)
                @php
                    $isRep = $u->account_type === 'representative';
                    $uRaw  = $u->name;
                    // Parse "Office - RepName" format for representatives
                    if ($isRep && str_contains($uRaw, ' - ')) {
                        [$uOfficePart, $uDisplayName] = explode(' - ', $uRaw, 2);
                        $uOfficeName = $u->office?->name ?? $uOfficePart;
                    } else {
                        $uDisplayName = $uRaw;
                        $uOfficeName  = $u->office?->name ?? null;
                    }
                    $uInits = collect(explode(' ', trim($uDisplayName)))->filter()->map(fn($w) => strtoupper(substr($w, 0, 1)))->take(2)->implode('');
                @endphp
                <div class="user-card">
                    <div class="user-card-head">
                        <div class="u-avatar {{ $isRep ? '' : 'is-individual' }}">{{ $uInits }}</div>
                        <div>
                            <span class="u-type-badge {{ $isRep ? 'rep' : 'ind' }}">
                                {{ $isRep ? ($uOfficeName ?: 'Representative') : 'Individual' }}
                            </span>
                            <div class="u-name">{{ $uDisplayName }}</div>
                        </div>
                    </div>

                    {{-- Handled documents performance --}}
                    <div class="u-stats-row">
                        <div class="u-stat">
                            <div class="u-stat-num" style="color:#0891b2">{{ \App\Support\UiNumber::compact($u->handled_received_count) }}</div>
                            <div class="u-stat-lbl">Processing</div>
                        </div>
                        <div class="u-stat">
                            <div class="u-stat-num" style="color:#f59e0b">{{ \App\Support\UiNumber::compact($u->handled_pending_count) }}</div>
                            <div class="u-stat-lbl">Pending</div>
                        </div>
                        <div class="u-stat">
                            <div class="u-stat-num" style="color:#16a34a">{{ \App\Support\UiNumber::compact($u->handled_processed_count) }}</div>
                            <div class="u-stat-lbl">Processed</div>
                        </div>
                    </div>

                    <button class="btn-view-act" onclick="openUserActivity({{ $u->id }})">
                        <i class="fas fa-chart-bar"></i> View Activity
                    </button>
                </div>
            @endforeach
        </div>
        @if($users->hasPages())
        <div class="table-card" style="margin-top:0">
            @include('partials.shared-pagination', [
                'paginator' => $users,
                'itemLabel' => 'staff members',
            ])
        </div>
        @endif
</div>

<div class="drawer-overlay" id="uaDrawerOverlay" onclick="closeUserActivity()"></div>
<div class="drawer" id="uaDrawer" style="width:560px">
    <div class="drawer-head">
        <div class="drawer-head-info">
            <h3 id="uaName">-</h3>
            <div class="drawer-ref" id="uaSubInfo">-</div>
            <span class="u-type-badge" id="uaTypeBadge" style="margin-top:5px"></span>
        </div>
        <button class="drawer-close" onclick="closeUserActivity()"><i class="fas fa-times"></i></button>
    </div>
    <div class="ua-stats-row" id="uaStats"></div>
    <div class="ua-filter-bar" id="uaFilterBar">
        <div class="ua-filter-search">
            <i class="fas fa-search"></i>
            <input id="uaFSearch" type="text" placeholder="Search subject, reference..." class="ua-field" data-no-capitalize data-no-clearable oninput="toggleUaClear()" />
            <button type="button" class="ua-clear-btn" id="uaClearSearch" onclick="clearUaSearch()" title="Clear search"><i class="fas fa-times"></i></button>
        </div>
        <div style="position:relative">
            <button type="button" class="ua-dots-btn" id="uaDotsBtn" onclick="toggleUaDropdown()">
                <i class="fas fa-ellipsis-v"></i>
                <span class="ua-dots-badge">!</span>
            </button>
            <div class="ua-dropdown" id="uaDropdown">
                <div class="ua-drop-group">
                    <div class="ua-drop-label">Status</div>
                    <select id="uaFStatus" class="ua-select">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="processed">Processed</option>
                    </select>
                </div>
                <div class="ua-drop-group">
                    <div class="ua-drop-label">Date Range</div>
                    <button type="button" class="ua-btn-date" id="uaDateRangeBtn" onclick="openUaDateModal()">
                        <i class="fas fa-calendar-alt"></i>
                        <span id="uaDateRangeLabel">Date Range</span>
                        <span class="ua-date-badge">!</span>
                    </button>
                </div>
                <input id="uaFFrom" type="hidden" />
                <input id="uaFTo" type="hidden" />
                <div class="ua-drop-actions">
                    <button onclick="applyUaFilters()" class="ua-btn ua-btn-primary">@include('partials.filter-icon', ['size' => 15]) Apply</button>
                    <button id="uaPdfBtn" onclick="" class="ua-btn ua-btn-pdf" title="Download PDF"><i class="fas fa-file-pdf" style="margin-right:4px"></i>PDF</button>
                </div>
            </div>
        </div>
    </div>
    <div class="drawer-tl-head"><i class="fas fa-file-alt"></i> Document Activity <span id="uaDocCount" style="font-weight:400;color:#94a3b8"></span></div>
    <div id="uaBody" style="overflow-y:auto;flex:1">
        <div class="drawer-loader"><span class="loading-dots"><span></span></span>Loading activity...</div>
    </div>
</div>

<div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>
<div class="drawer" id="docDrawer">
    <div class="drawer-head">
        <div class="drawer-head-info">
            <h3 id="drTitle">-</h3>
            <div class="drawer-ref" id="drRef">-</div>
            <div class="drawer-track" id="drTrack"></div>
        </div>
        <button class="drawer-close" onclick="closeDrawer()"><i class="fas fa-times"></i></button>
    </div>
    <div class="drawer-body" id="drawerBody">
        <div class="drawer-loader"><span class="loading-dots"><span></span></span>Loading details...</div>
    </div>
</div>

{{-- UA Date Range Modal --}}
<div class="date-modal-overlay" id="uaDateModalOverlay" onclick="if(event.target===this)closeUaDateModal()">
    <div class="date-modal">
        <div class="date-modal-head">
            <h3><i class="fas fa-calendar-alt" style="color:var(--primary)"></i> Set Date Range</h3>
            <button class="date-modal-close" onclick="closeUaDateModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="date-modal-body">
            <div class="date-field-group">
                <label class="date-field-label">Date Range</label>
                <div class="date-row">
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px">From</div>
                        <input class="field" type="date" id="uaMDateFrom">
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px">To</div>
                        <input class="field" type="date" id="uaMDateTo">
                    </div>
                </div>
            </div>
        </div>
        <div class="date-modal-foot">
            <button type="button" class="btn btn-light" onclick="clearUaDateModal()"><i class="fas fa-rotate-left"></i> Clear</button>
            <button type="button" class="btn btn-primary" onclick="applyUaDateModal()"><i class="fas fa-check"></i> Apply</button>
        </div>
    </div>
</div>

{{-- PDF empty-results confirm modal --}}
<div class="pdf-confirm-overlay" id="pdfConfirmOverlay" onclick="if(event.target===this)closePdfConfirm()">
    <div class="pdf-confirm">
        <div class="pdf-confirm-icon"><i class="fas fa-triangle-exclamation"></i></div>
        <h3>No Records Found</h3>
        <p>The current filters returned <strong>0 documents</strong>. The exported PDF will be empty. Continue anyway?</p>
        <div class="pdf-confirm-btns">
            <button class="btn-pdf-cancel" onclick="closePdfConfirm()">Cancel</button>
            <button class="btn-pdf-proceed" onclick="proceedPdfExport()"><i class="fas fa-file-pdf" style="margin-right:4px"></i>Export</button>
        </div>
    </div>
</div>

<footer class="site-footer">
    <div class="footer-left">&copy; {{ date('Y') }} DepEd Document Tracking System</div>
    <div class="footer-right">Developed by Raymond Bautista</div>
</footer>

@php
    $docDrawerData = [];
    foreach ($documents as $doc) {
        $fallback = [
            'reference_number' => $doc->reference_number ?: $doc->tracking_number,
            'tracking_number' => $doc->tracking_number ?: $doc->reference_number,
            'subject' => $doc->subject,
            'type' => $doc->type,
            'status' => $doc->status,
            'status_label' => $doc->statusLabel(),
            'sender_name' => $doc->sender_name,
            'submitted_to_office' => optional($doc->submittedToOffice)->name,
            'current_office' => optional($doc->currentOffice)->name,
            'current_handler' => optional($doc->currentHandler)->name,
            'date' => optional($doc->created_at)->copy()?->setTimezone('Asia/Manila')?->format('M d, Y h:i A'),
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
var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var docsData = JSON.parse(document.getElementById('docsData').textContent || '{}');

// ─── User Activity Drawer ───
var currentUaUid = null;
var _uaDocCount = 0;
var _uaPdfUrl = '';

function openUserActivity(uid) {
    currentUaUid = uid;
    document.getElementById('uaFSearch').value = '';
    document.getElementById('uaFStatus').value = '';
    document.getElementById('uaFFrom').value   = '';
    document.getElementById('uaFTo').value     = '';
    document.getElementById('uaName').textContent = 'Loading...';
    document.getElementById('uaSubInfo').textContent = '';
    document.getElementById('uaTypeBadge').textContent = '';
    document.getElementById('uaStats').innerHTML = '';
    document.getElementById('uaDocCount').textContent = '';
    document.getElementById('uaBody').innerHTML = '<div class="drawer-loader"><span class="loading-dots"><span></span></span>Loading activity...</div>';
    document.getElementById('uaDrawerOverlay').classList.add('open');
    document.getElementById('uaDrawer').classList.add('open');
    document.body.style.overflow = 'hidden';
    fetchUaData();
}

function fetchUaData() {
    if (!currentUaUid) return;
    var params = new URLSearchParams();
    var s  = document.getElementById('uaFSearch').value.trim();
    var st = document.getElementById('uaFStatus').value;
    var df = document.getElementById('uaFFrom').value;
    var dt = document.getElementById('uaFTo').value;
    if (s)  params.set('search',    s);
    if (st) params.set('status',    st);
    if (df) params.set('date_from', df);
    if (dt) params.set('date_to',   dt);
    var qs = params.toString();
    document.getElementById('uaBody').innerHTML = '<div class="drawer-loader"><span class="loading-dots"><span></span></span>Loading...</div>';
    fetch('/api/office/user-activity/' + currentUaUid + (qs ? '?' + qs : ''), {
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) {
            document.getElementById('uaBody').innerHTML = '<div class="drawer-loader">Failed to load activity.</div>';
            return;
        }
        renderUserActivity(data);
        updateUaExportLinks(currentUaUid, params);
    })
    .catch(function() {
        document.getElementById('uaBody').innerHTML = '<div class="drawer-loader">Something went wrong.</div>';
    });
}

function applyUaFilters()  { fetchUaData(); closeUaDropdown(); }
function resetUaFilters() {
    document.getElementById('uaFSearch').value = '';
    document.getElementById('uaFStatus').value = '';
    document.getElementById('uaFFrom').value   = '';
    document.getElementById('uaFTo').value     = '';
    updateUaDateBtn();
    toggleUaClear();
    updateUaDotsBadge();
    fetchUaData();
}

// UA search clear × button
function toggleUaClear() {
    var v = document.getElementById('uaFSearch').value;
    var btn = document.getElementById('uaClearSearch');
    if (v.length > 0) btn.classList.add('show');
    else btn.classList.remove('show');
}
function clearUaSearch() {
    document.getElementById('uaFSearch').value = '';
    toggleUaClear();
    fetchUaData();
}

// UA three-dots dropdown
function toggleUaDropdown() {
    var dd = document.getElementById('uaDropdown');
    var btn = document.getElementById('uaDotsBtn');
    dd.classList.toggle('show');
    btn.classList.toggle('active');
}
function closeUaDropdown() {
    document.getElementById('uaDropdown').classList.remove('show');
    document.getElementById('uaDotsBtn').classList.remove('active');
}
function updateUaDotsBadge() {
    var st = document.getElementById('uaFStatus').value;
    var df = document.getElementById('uaFFrom').value;
    var dt = document.getElementById('uaFTo').value;
    var btn = document.getElementById('uaDotsBtn');
    if (st || df || dt) btn.classList.add('has-filters');
    else btn.classList.remove('has-filters');
}
// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    var dd = document.getElementById('uaDropdown');
    var btn = document.getElementById('uaDotsBtn');
    if (dd && dd.classList.contains('show') && !dd.contains(e.target) && !btn.contains(e.target)) {
        closeUaDropdown();
    }
});

// UA Date Range Modal
function openUaDateModal() {
    document.getElementById('uaMDateFrom').value = document.getElementById('uaFFrom').value;
    document.getElementById('uaMDateTo').value   = document.getElementById('uaFTo').value;
    document.getElementById('uaDateModalOverlay').classList.add('show');
}
function closeUaDateModal() {
    document.getElementById('uaDateModalOverlay').classList.remove('show');
}
function applyUaDateModal() {
    document.getElementById('uaFFrom').value = document.getElementById('uaMDateFrom').value;
    document.getElementById('uaFTo').value   = document.getElementById('uaMDateTo').value;
    updateUaDateBtn();
    updateUaDotsBadge();
    closeUaDateModal();
    fetchUaData();
}
function clearUaDateModal() {
    document.getElementById('uaMDateFrom').value = '';
    document.getElementById('uaMDateTo').value   = '';
}
function updateUaDateBtn() {
    var df = document.getElementById('uaFFrom').value;
    var dt = document.getElementById('uaFTo').value;
    var btn = document.getElementById('uaDateRangeBtn');
    var label = document.getElementById('uaDateRangeLabel');
    if (df || dt) {
        btn.classList.add('has-dates');
        var parts = [];
        if (df) parts.push(df);
        if (dt) parts.push(dt);
        label.textContent = parts.join(' – ');
    } else {
        btn.classList.remove('has-dates');
        label.textContent = 'Date Range';
    }
}

function updateUaExportLinks(uid, params) {
    var pdfParams = new URLSearchParams(params);
    pdfParams.set('format', 'pdf');
    var base = '/office/users/' + uid + '/export';
    var pdfUrl = base + '?' + pdfParams.toString();
    _uaPdfUrl = pdfUrl;
    document.getElementById('uaPdfBtn').onclick = function() {
        if (_uaDocCount === 0) {
            showPdfConfirm(pdfUrl);
        } else {
            window.location.href = pdfUrl;
        }
    };
}

function closePdfConfirm() {
    document.getElementById('pdfConfirmOverlay').classList.remove('show');
}
var _pendingPdfUrl = '';
function showPdfConfirm(url) {
    _pendingPdfUrl = url;
    document.getElementById('pdfConfirmOverlay').classList.add('show');
}
function proceedPdfExport() {
    closePdfConfirm();
    if (_pendingPdfUrl) window.location.href = _pendingPdfUrl;
}

// Documents panel PDF — check if table is empty
var _docTableTotal = {{ $documents->total() }};
var _docPdfUrl = {!! json_encode(request()->fullUrlWithQuery(['export' => 'pdf'])) !!};
function handleDocPdfClick() {
    if (_docTableTotal === 0) {
        showPdfConfirm(_docPdfUrl);
    } else {
        window.location.href = _docPdfUrl;
    }
}

function closeUserActivity() {
    document.getElementById('uaDrawerOverlay').classList.remove('open');
    document.getElementById('uaDrawer').classList.remove('open');
    document.body.style.overflow = '';
    closeUaDropdown();
    currentUaUid = null;
}

function openActivityDocDetail(ref, tracking) {
    closeUserActivity();
    openDocDetail(ref, tracking || ref);
}

function renderUserActivity(data) {
    var u = data.user, stats = data.stats, docs = data.documents;

    document.getElementById('uaName').textContent = u.name;
    document.getElementById('uaSubInfo').textContent = u.is_rep ? (u.office || 'No Office Assigned') : u.email;
    var badge = document.getElementById('uaTypeBadge');
    badge.textContent = u.is_rep ? 'Office Rep' : 'Individual';
    badge.className = 'u-type-badge ' + (u.is_rep ? 'rep' : 'ind');

    var compactCount = window.formatCompactCount || function(v) { return String(v); };
    var statsHtml =
        '<div class="ua-stat"><div class="ua-stat-num" style="color:#f59e0b">' + compactCount(stats.pending) + '</div><div class="ua-stat-lbl">Pending</div></div>' +
        '<div class="ua-stat"><div class="ua-stat-num" style="color:#16a34a">' + compactCount(stats.processed) + '</div><div class="ua-stat-lbl">Processed</div></div>' +
        '<div class="ua-stat"><div class="ua-stat-num">' + compactCount(stats.total_docs) + '</div><div class="ua-stat-lbl">Total Handled</div></div>';
    document.getElementById('uaStats').innerHTML = statsHtml;
    document.getElementById('uaDocCount').textContent = '— ' + compactCount(docs.length) + ' record(s)';
    _uaDocCount = docs.length;

    var bodyHtml = '';
    if (!docs.length) {
        bodyHtml = '<div class="empty-state"><i class="fas fa-file-circle-xmark"></i><h3>No Documents</h3><p>No document activity found for this user.</p></div>';
    } else {
        bodyHtml = '<div class="ua-table-scroll"><table style="width:100%;border-collapse:collapse">'
            + '<thead><tr>'
            + '<th style="text-align:left;padding:9px 14px;font-size:10px;font-weight:700;text-transform:uppercase;color:var(--text-muted);border-bottom:1px solid var(--border);background:#f8fafc;white-space:nowrap">Reference</th>'
            + '<th style="text-align:left;padding:9px 14px;font-size:10px;font-weight:700;text-transform:uppercase;color:var(--text-muted);border-bottom:1px solid var(--border);background:#f8fafc">Subject</th>'
            + '<th style="text-align:left;padding:9px 14px;font-size:10px;font-weight:700;text-transform:uppercase;color:var(--text-muted);border-bottom:1px solid var(--border);background:#f8fafc">Status</th>'
            + '<th style="text-align:left;padding:9px 14px;font-size:10px;font-weight:700;text-transform:uppercase;color:var(--text-muted);border-bottom:1px solid var(--border);background:#f8fafc;white-space:nowrap">Last Action</th>'
            + '</tr></thead><tbody>';
        docs.forEach(function(doc) {
            var refValue = JSON.stringify(doc.reference || '');
            var trackingValue = JSON.stringify(doc.tracking || doc.reference || '');
            bodyHtml += '<tr style="border-bottom:1px solid #f1f5f9;cursor:pointer" onclick="openActivityDocDetail(' + refValue + ',' + trackingValue + ')" title="Open document drawer">'
                + '<td style="padding:9px 14px;font-size:12px;font-family:Poppins,sans-serif;font-weight:600;color:var(--primary)">' + escapeHtml(doc.reference) + '</td>'
                + '<td style="padding:9px 14px;font-size:12px;color:var(--text-dark);max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="' + escapeHtml(doc.subject) + '">' + escapeHtml(doc.subject) + '</td>'
                + '<td style="padding:9px 14px"><span class="badge badge-' + escapeHtml(doc.status) + '">' + escapeHtml(doc.status_label) + '</span></td>'
                + '<td style="padding:9px 14px;font-size:11px;color:var(--text-muted);white-space:nowrap">' + escapeHtml(doc.last_action) + '</td>'
                + '</tr>';
        });
        bodyHtml += '</tbody></table></div>';
    }
    document.getElementById('uaBody').innerHTML = bodyHtml;
}

// ─── Date Range Modal ───
function openDateModal() {
    document.getElementById('dateModalOverlay').classList.add('show');
    document.body.style.overflow = 'hidden';
}
function closeDateModal() {
    document.getElementById('dateModalOverlay').classList.remove('show');
    document.body.style.overflow = '';
}
function clearDateModal() {
    document.getElementById('mDateFrom').value = '';
    document.getElementById('mDateTo').value   = '';
    document.getElementById('mDateField').value = 'created_at';
}
function applyDateModal() {
    document.getElementById('hDateField').value = document.getElementById('mDateField').value;
    document.getElementById('hDateFrom').value  = document.getElementById('mDateFrom').value;
    document.getElementById('hDateTo').value    = document.getElementById('mDateTo').value;
    closeDateModal();
    // update trigger button style
    var hasDate = document.getElementById('hDateFrom').value || document.getElementById('hDateTo').value;
    document.getElementById('dateRangeBtn').classList.toggle('has-dates', !!hasDate);
    var form = document.getElementById('reportForm');
    if (form && typeof form.requestSubmit === 'function') form.requestSubmit();
    else if (form) form.submit();
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeDateModal(); closeDrawer(); closeUserActivity(); }
});

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
    var btn = document.getElementById('mobHamBtn');
    if (btn) btn.classList.remove('toggle');
};

function logout(){
    fetch('/api/logout', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}
    }).then(function(){
        window.location.href='/login';
    }).catch(function(){
        window.location.href='/login';
    });
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

function bindDocRows() {
    document.querySelectorAll('tr.doc-row').forEach(function(row) {
        row.setAttribute('tabindex', '0');
        row.setAttribute('role', 'button');
        row.setAttribute('aria-label', 'View routing details');

        row.addEventListener('click', function(e) {
            if (e.target.closest('a,button,input,select,textarea,label')) return;
            var ref = row.getAttribute('data-ref') || '';
            var tracking = row.getAttribute('data-tracking') || ref;
            openDocDetail(ref, tracking);
        });

        row.addEventListener('keydown', function(e) {
            if (e.key !== 'Enter' && e.key !== ' ') return;
            e.preventDefault();
            var ref = row.getAttribute('data-ref') || '';
            var tracking = row.getAttribute('data-tracking') || ref;
            openDocDetail(ref, tracking);
        });
    });
}

function openDocDetail(ref, tracking) {
    ref = (ref || '').toString().trim();
    tracking = (tracking || ref).toString().trim();

    document.getElementById('drTitle').textContent = '-';
    document.getElementById('drRef').textContent = ref || tracking || '-';
    document.getElementById('drTrack').textContent = '';
    document.getElementById('drawerBody').innerHTML = '<div class="drawer-loader"><span class="loading-dots"><span></span></span>Loading details...</div>';
    document.getElementById('drawerOverlay').classList.add('open');
    document.getElementById('docDrawer').classList.add('open');
    document.body.style.overflow = 'hidden';

    window.docTraxFetchJson('/api/track-document', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
        timeoutMs: 15000,
        body: JSON.stringify({
            reference_number: ref,
            tracking_number: tracking
        })
    })
    .then(function(data) {
        if (!data.success || !data.document) {
            document.getElementById('drawerBody').innerHTML = '<div class="drawer-loader"><i class="fas fa-file-circle-question" style="font-size:32px;color:#cbd5e1;margin-bottom:8px"></i>Document not found.</div>';
            return;
        }
        renderDrawer(data.document);
    })
    .catch(function(error) {
        var fallback = docsData[tracking] || docsData[ref];
        if (fallback) {
            renderDrawer({
                subject: fallback.subject || '-',
                reference_number: fallback.reference_number || tracking || ref,
                tracking_number: fallback.tracking_number || tracking || ref,
                status: fallback.status || 'unknown',
                status_label: fallback.status_label || 'Unknown',
                sender_name: fallback.sender_name || '-',
                type: fallback.type || '-',
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
}

function closeDrawer() {
    document.getElementById('drawerOverlay').classList.remove('open');
    document.getElementById('docDrawer').classList.remove('open');
    document.body.style.overflow = '';
}

function renderDrawer(doc) {
    var ref = doc.reference_number || doc.tracking_number || '-';
    var trackingNo = doc.tracking_number || '';

    document.getElementById('drTitle').textContent = doc.subject || '-';
    document.getElementById('drRef').textContent = 'TN · ' + ref;
    document.getElementById('drTrack').textContent = (trackingNo && trackingNo !== ref) ? ('Ref · ' + trackingNo) : '';

    var logs = doc.routing_logs || [];
    var tlHtml = '';
    var prevGroupKey = null;
    if (!logs.length) {
        tlHtml = '<div style="color:var(--text-muted);font-size:13px;padding:4px 0">No routing history yet.</div>';
    } else {
        Array.from(logs).reverse().forEach(function(log, idx) {
            var isLatest = idx === 0;
            var dc = isLatest ? 'c-latest' : dotClass(log.status_after);
            var dotIcon = isLatest ? 'fa-arrow-up' : 'fa-check';
            var fromTo = (log.from_office && log.to_office && log.from_office !== log.to_office) ? (log.from_office + ' -> ' + log.to_office) : '';
            var groupKey = (log.action === 'submitted') ? '__pending__' :
                           (log.action === 'forwarded' ? (log.from_office || 'Unknown') :
                           (log.to_office || log.from_office || 'Unknown'));
            var groupLabel = (groupKey === '__pending__') ? 'Submitted — Pending Physical Submission' : groupKey;
            if (groupKey !== prevGroupKey) {
                prevGroupKey = groupKey;
                tlHtml += '<div class="tl-office-hdr"><div class="tl-dot ' + dc + '" style="margin-right:5px"><i class="fas ' + dotIcon + '" style="font-size:5px"></i></div><span>' + escapeHtml(groupLabel) + '</span></div>';
            }
            tlHtml += '<div class="tl-item">' +
                (log.performed_by ? '<div class="tl-action">' + escapeHtml(log.performed_by) + '</div>' : '') +
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

document.addEventListener('DOMContentLoaded', function() {
    bindDocRows();
});
</script>
</body>
</html>
