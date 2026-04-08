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
            --text-dark: #1b263b;
            --text-muted: #64748b;
            --white: #ffffff;
            --bg: #f0f2f5;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        html { overflow-y:scroll; }
        body { background:var(--bg); font-family:'Poppins',sans-serif; color:var(--text-dark); min-height:100vh; display:flex; flex-direction:column; }

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

        /* ─── Wrapper ─── */
        .dash-wrapper { max-width:1140px; width:100%; margin:0 auto; padding:28px 24px 48px; flex:1; }

        /* ─── Page header ─── */
        .page-header { display:flex; align-items:center; gap:8px; margin-bottom:6px; }
        .page-header h1 { font-size:20px; font-weight:700; color:var(--text-dark); }
        .page-sub { font-size:14px; color:var(--text-muted); margin-bottom:24px; }
        .back-link { display:inline-flex; align-items:center; gap:6px; color:var(--text-muted); font-size:13px; text-decoration:none; margin-bottom:18px; transition:color .15s; }
        .back-link:hover { color:var(--primary); }

        /* ─── Search bar ─── */
        .search-card { background:#fff; border-radius:12px; border:1px solid var(--border); box-shadow:var(--shadow-sm); padding:18px 22px; margin-bottom:20px; display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
        .search-wrap { position:relative; flex:1; min-width:200px; }
        .search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:14px; pointer-events:none; }
        .search-wrap input { width:100%; padding:10px 14px 10px 38px; font-family:Poppins,sans-serif; font-size:13px; border:1.5px solid var(--border); border-radius:9px; outline:none; color:var(--text-dark); background:#fff; transition:border-color .2s,box-shadow .2s; }
        .search-wrap input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(0,86,179,.1); }
        .search-wrap input::placeholder { color:#94a3b8; }
        .status-select { padding:10px 36px 10px 14px; font-family:Poppins,sans-serif; font-size:13px; border:1.5px solid var(--border); border-radius:9px; outline:none; color:var(--text-dark); background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%2394a3b8' d='M5 7L0 2h10z'/%3E%3C/svg%3E") no-repeat right 12px center; -webkit-appearance:none; appearance:none; cursor:pointer; min-width:160px; transition:border-color .2s,box-shadow .2s; }
        .status-select:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(0,86,179,.1); }
        .search-count { font-size:12px; color:var(--text-muted); white-space:nowrap; }

        /* ─── Table card ─── */
        .table-card { background:#fff; border-radius:12px; border:1px solid var(--border); box-shadow:var(--shadow-sm); overflow:hidden; }
        .table-card.list-table-card.has-list { display:flex; flex-direction:column; max-height:clamp(520px,72vh,820px); }
        .table-card.list-table-card.has-list .table-scroll { flex:1; min-height:0; overflow:auto; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; }
        .table-card.list-table-card.has-list .table-scroll th { position:sticky; top:0; z-index:2; }
        .table-card.list-table-card.has-list .empty-state { flex:1; display:flex; flex-direction:column; align-items:center; justify-content:center; }
        .table-card.list-table-card.has-list .pagination-bar { flex-shrink:0; }
        .table-head { padding:16px 22px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; }
        .table-title { font-size:17px; font-weight:700; color:var(--text-dark); }
        table { width:100%; border-collapse:collapse; }
        th { text-align:left; padding:11px 18px; font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.6px; color:var(--text-muted); border-bottom:1px solid var(--border); background:#fafbfc; }
        td { padding:13px 18px; font-size:13px; color:var(--text-dark); border-bottom:1px solid #f1f5f9; vertical-align:middle; }
        tr:last-child td { border-bottom:none; }
        tbody tr { transition:background .1s; cursor:pointer; }
        tbody tr:hover td { background:#f8fafc; }
        .td-action { width:44px; text-align:center; }
        .row-arrow { display:inline-flex; align-items:center; justify-content:center; width:28px; height:28px; border-radius:7px; color:#94a3b8; transition:all .15s; }
        tbody tr:hover .row-arrow { background:var(--primary); color:#fff; }
        tbody tr.hidden-row { display:none; }

        .t-num { font-weight:700; color:var(--primary); font-size:12.5px; white-space:nowrap; }
        .t-num-sub { font-size:11px; color:var(--text-muted); font-family:monospace; margin-top:2px; }
        .t-subject { font-weight:500; max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .t-date { font-size:12px; color:var(--text-muted); white-space:nowrap; }
        .t-office { font-size:12px; color:var(--text-muted); max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .t-type { font-size:12px; color:var(--text-muted); }
        .cell-ellipsis { display:block; max-width:100%; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

        /* ─── Pills ─── */
        .pill { display:inline-block; padding:3px 10px; border-radius:99px; font-size:11px; font-weight:600; white-space:nowrap; }
        .pill-submitted,
        .pill-received,
        .pill-in_review,
        .pill-forwarded,
        .pill-completed,
        .pill-for_pickup,
        .pill-on_hold,
        .pill-returned,
        .pill-cancelled,
        .pill-archived { background:#fff7ed; color:#c2410c; }
        .pill-returned   { background:#fef2f2; color:#dc2626; }
        .pill-cancelled  { background:#f8fafc; color:#64748b; }

        /* ─── Drawer action bar ─── */
        .drawer-action-bar { padding:16px 20px; border-top:2px solid #fed7aa; background:#fff7ed; flex-shrink:0; }
        .pickup-notice { font-size:12.5px; color:#9a3412; margin-bottom:12px; display:flex; align-items:flex-start; gap:8px; line-height:1.5; }
        .pickup-notice i { margin-top:2px; flex-shrink:0; }
        .btn-confirm-pickup { width:100%; padding:11px 16px; background:#ea580c; color:#fff; border:none; border-radius:9px; font-family:Poppins,sans-serif; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; transition:background .2s; }
        .btn-confirm-pickup:hover { background:#c2410c; }
        .btn-confirm-pickup:disabled { opacity:.6; cursor:not-allowed; }

        /* Cancel action bar */
        .cancel-notice { font-size:12.5px; color:#991b1b; margin-bottom:12px; display:flex; align-items:flex-start; gap:8px; line-height:1.5; }
        .cancel-notice i { margin-top:2px; flex-shrink:0; }

        .modal-actions .modal-danger { background:#dc2626; color:#fff; border-color:#dc2626; }
        .modal-actions .modal-danger:hover { background:#b91c1c; }
        .modal-actions .modal-danger:disabled { opacity:.6; cursor:not-allowed; }

        /* ─── Pickup confirm modal ─── */
        .modal-backdrop { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:500; align-items:center; justify-content:center; padding:16px; }
        .modal-backdrop.show { display:flex; }
        .modal-box { background:#fff; border-radius:16px; max-width:420px; width:100%; padding:28px 24px; box-shadow:0 20px 60px rgba(0,0,0,.2); text-align:center; animation:modalIn .18s ease; }
        @keyframes modalIn { from{opacity:0;transform:scale(.96)} to{opacity:1;transform:scale(1)} }
        .modal-icon-wrap { width:52px; height:52px; border-radius:14px; display:inline-flex; align-items:center; justify-content:center; font-size:22px; margin-bottom:14px; background:#fff7ed; color:#ea580c; }
        .modal-box h3 { font-size:17px; font-weight:700; color:var(--text-dark); margin-bottom:8px; }
        .modal-box p { font-size:13px; color:var(--text-muted); line-height:1.6; margin-bottom:22px; }
        .modal-actions { display:flex; gap:10px; justify-content:center; }
        .modal-actions button { padding:9px 20px; border-radius:9px; font-size:13px; font-weight:600; cursor:pointer; font-family:Poppins,sans-serif; border:1.5px solid var(--border); background:#fff; color:var(--text-dark); transition:all .2s; }
        .modal-actions button:hover { background:#f1f5f9; }
        .modal-actions .modal-confirm { background:#ea580c; color:#fff; border-color:#ea580c; }
        .modal-actions .modal-confirm:hover { background:#c2410c; }
        .modal-actions .modal-confirm:disabled { opacity:.6; cursor:not-allowed; }

        /* ─── Empty state ─── */
        .empty-state { padding:60px 20px; text-align:center; color:var(--text-muted); }
        .empty-state i { font-size:40px; color:#cbd5e1; margin-bottom:14px; display:block; }
        .empty-state h3 { font-size:15px; font-weight:600; color:#94a3b8; margin-bottom:6px; }
        .empty-state p { font-size:13px; }

        /* ─── Pagination ─── */
        .pagination-bar { padding:14px 22px; border-top:1px solid var(--border); display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; }
        .pagination-bar span { font-size:12px; color:var(--text-muted); }
        .pagination-links { display:flex; gap:4px; }
        .page-btn { display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:7px; font-size:12px; font-weight:600; color:var(--text-dark); text-decoration:none; background:#fff; border:1px solid var(--border); transition:all .15s; }
        .page-btn:hover { background:#f1f5f9; border-color:#cbd5e1; }
        .page-btn.active { background:var(--primary); color:#fff; border-color:var(--primary); }
        .page-btn.disabled { opacity:.4; pointer-events:none; }

        /* ─── Footer ─── */
        .dash-footer { width:100%; background:#fff; border-top:1px solid #e2e8f0; padding:20px 5%; display:flex; justify-content:space-between; align-items:center; font-size:12px; color:#94a3b8; margin-top:auto; }
        .footer-right { font-size:11px; color:#b0b8c4; }
        @media(max-width:768px) { .dash-footer { flex-direction:column; gap:6px; text-align:center; padding:16px 5%; } }

        /* ─── No result row ─── */
        #noResultRow { display:none; }
        #noResultRow td { text-align:center; padding:40px; color:var(--text-muted); font-size:13px; }

        /* ─── Detail Drawer ─── */
        .drawer-overlay { position:fixed; inset:0; background:rgba(0,0,0,.35); z-index:400; opacity:0; pointer-events:none; transition:opacity .25s; }
        .drawer-overlay.open { opacity:1; pointer-events:all; }
        .drawer { position:fixed; top:0; right:0; height:100vh; width:460px; max-width:100vw; background:#fff; z-index:401; box-shadow:-4px 0 24px rgba(0,0,0,.12); display:flex; flex-direction:column; transform:translateX(100%); transition:transform .28s cubic-bezier(.4,0,.2,1); }
        .drawer.open { transform:translateX(0); }
        .drawer-head { padding:18px 22px; border-bottom:1px solid var(--border); display:flex; align-items:flex-start; gap:12px; }
        .drawer-head-info { flex:1; min-width:0; }
        .drawer-head h3 { font-size:16px; font-weight:700; color:var(--text-dark); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-bottom:4px; }
        .drawer-ref { font-size:13px; color:var(--text-muted); font-family:monospace; letter-spacing:.4px; margin-bottom:2px; }
        .drawer-track { font-size:11px; color:var(--text-muted); font-family:monospace; letter-spacing:.4px; margin-bottom:4px; }
        .drawer-close { width:32px; height:32px; border-radius:8px; border:1px solid var(--border); background:#f8fafc; cursor:pointer; display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-size:14px; flex-shrink:0; transition:all .15s; }
        .drawer-close:hover { background:#fee2e2; color:#dc2626; border-color:#fca5a5; }
        .drawer-meta { display:grid; grid-template-columns:1fr 1fr; border-bottom:1px solid var(--border); }
        .dm-item { padding:14px 20px; border-right:1px solid #f1f5f9; border-bottom:1px solid #f1f5f9; }
        .dm-item:nth-child(2n) { border-right:none; }
        .dm-label { font-size:11px; text-transform:uppercase; letter-spacing:.6px; color:#94a3b8; font-weight:600; margin-bottom:3px; }
        .dm-value { font-size:14px; color:var(--text-dark); font-weight:500; }
        .drawer-tl-head { padding:14px 20px 6px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.8px; color:var(--text-muted); display:flex; align-items:center; gap:6px; }
        .drawer-body { flex:1; overflow-y:auto; }
        .drawer-timeline { padding:10px 20px 24px; }
        .tl { position:relative; }
        .tl::before { content:''; position:absolute; left:7px; top:8px; bottom:8px; width:2px; background:var(--border); z-index:-1; }
        .tl-item { position:relative; margin-bottom:20px; padding-left:24px; }
        .tl-item:last-child { margin-bottom:0; }
        .tl-dot { width:16px; height:16px; border-radius:50%; border:2.5px solid #fff; display:flex; align-items:center; justify-content:center; color:#fff; flex-shrink:0; }
        .tl-dot.c-active  { background:#22c55e; box-shadow:0 0 0 2px #22c55e; }
        .tl-dot.c-done    { background:#22c55e; box-shadow:0 0 0 2px #22c55e; }
        .tl-dot.c-warn    { background:#22c55e; box-shadow:0 0 0 2px #22c55e; }
        .tl-dot.c-danger  { background:#22c55e; box-shadow:0 0 0 2px #22c55e; }
        .tl-dot.c-latest  { background:#f59e0b; box-shadow:0 0 0 2px #f59e0b; }
        .tl-action { font-size:12px; font-weight:700; color:#1b263b; }
        .tl-meta { font-size:12px; color:#64748b; margin:2px 0; }
        .tl-remarks { font-size:12px; color:#64748b; background:#f8fafc; border-left:3px solid var(--border); padding:5px 9px; border-radius:4px; margin-top:5px; }
        .tl-office-hdr{display:flex;align-items:center;font-size:13px;font-weight:700;color:var(--text-dark);text-transform:none;letter-spacing:0;margin:18px 0 8px -7px;padding-left:7px;padding-bottom:6px;position:relative}
        .tl-office-hdr::after{content:'';position:absolute;left:21px;right:0;bottom:0;height:1.5px;background:var(--border)}
        .tl-office-hdr:first-child{margin-top:0}
        .tl-item.voided{opacity:.45}
        .tl-item.voided .tl-action,.tl-item.voided .tl-meta,.tl-item.voided .tl-remarks{text-decoration:line-through;color:#94a3b8}
        .tl-dot.c-voided{background:#cbd5e1;box-shadow:0 0 0 2px #cbd5e1}
        .tl-void-badge{display:inline-block;font-size:9px;font-weight:700;letter-spacing:.6px;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;border-radius:4px;padding:1px 5px;margin-left:4px;vertical-align:middle;text-decoration:none !important}
        .drawer-loader { display:flex; align-items:center; justify-content:center; padding:48px; flex-direction:column; gap:12px; color:var(--text-muted); font-size:13px; }
        .spin { width:22px; height:22px; border:3px solid #e2e8f0; border-top-color:var(--primary); border-radius:50%; animation:spin .7s linear infinite; }
        @keyframes spin { to { transform:rotate(360deg); } }

        @media(max-width:768px) {
            .dash-wrapper { padding:20px 16px 40px; }
            .search-card { flex-direction:column; align-items:stretch; }
            .status-select { min-width:unset; }
            .table-card.list-table-card.has-list { max-height:min(68vh,560px); }
            th:nth-child(3), td:nth-child(3),
            th:nth-child(5), td:nth-child(5),
            th:nth-child(6), td:nth-child(6) { display:none; }
            .t-subject { max-width:140px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        }
    </style>
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
</head>
<body>
@php
    $isAdmin = $user->isAdmin();
    $isRep = $user->isRepresentative();
    $isOfficeRep = $isRep && !empty($user->office_id);
    $isSelfRep = $isRep && !$isOfficeRep && !$isAdmin;
    $repName = $isSelfRep ? $user->representativeDisplayName() : '';
    $repOfficeName = $isSelfRep ? ($user->representativeOfficeName() ?? 'Representative') : '';
    $sidebarNameSource = trim($repName ?: $user->name);
    $initials = collect(explode(' ', $sidebarNameSource))->filter()->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->implode('');
    $firstName = explode(' ', $sidebarNameSource)[0] ?? 'User';
    $backUrl = $isAdmin ? '/dashboard' : ($isOfficeRep ? '/office/dashboard' : '/dashboard');
    $roleBadge = $user->isSuperAdmin() ? 'Super Admin' : ($isAdmin ? 'Admin' : ($isRep ? 'Representative' : ucfirst($user->role ?? 'User')));
    $sidebarMeta = $isSelfRep ? ($repName ?: 'Representative') : $roleBadge;
    $sidebarName = $isSelfRep ? ($repOfficeName ?: 'Representative') : $firstName;
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
        <a href="{{ $backUrl }}"><i class="fas fa-th-large"></i> Dashboard</a>
        <span class="nav-section">Documents</span>
        <a href="/submit"><i class="fas fa-paper-plane"></i> Submit Document</a>
        <a href="/my-documents" class="active"><i class="fas fa-folder-open"></i> My Documents</a>
        <a href="/track"><i class="fas fa-search"></i> Track Document</a>
        <span class="nav-section">Account</span>
        <a href="/profile"><i class="fas fa-user-cog"></i> My Profile</a>
    </nav>
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">{{ $initials }}</div>
            <div class="sb-user-info">
                <small>{{ $sidebarMeta }}</small>
                <span>{{ $sidebarName }}</span>
            </div>
        </div>
        <button onclick="logout()" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>
</div>

<!-- ─── Main Content ─── -->
<div class="main">

<div class="dash-wrapper">

    <a href="{{ $backUrl }}" class="back-link" aria-label="Back to Dashboard" title="Back to Dashboard" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;"><span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></span></a>

    <div class="page-header">
        <h1>My Documents</h1>
    </div>
    <p class="page-sub">All documents you have submitted through the system.</p>

    <!-- Search & Filter -->
    <div class="search-card">
        <div class="search-wrap">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Search by tracking no., subject, or type..." data-clearable data-no-capitalize
                   value="{{ $search }}" oninput="filterDocs()">
        </div>
        <select class="status-select" id="statusFilter" onchange="filterDocs(true)">
            <option value="">All Statuses</option>
            @foreach(\App\Models\Document::FILTER_STATUSES as $key => $label)
                <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <span class="search-count" id="resultCount"></span>
    </div>

    <!-- Documents Table -->
    <div class="table-card list-table-card{{ $documents->count() ? ' has-list' : '' }}">
        <div class="table-head">
            <span class="table-title">Results <span id="totalCount" style="font-weight:400;color:var(--text-muted)">({{ \App\Support\UiNumber::compact($documents->total()) }})</span></span>
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
                <thead>
                    <tr>
                        <th>Tracking No.</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Current Office</th>
                        <th>Date Submitted</th>
                        <th class="td-action"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $doc)
                    @php
                        $docRef = $doc->reference_number ?: $doc->tracking_number;
                        $docTracking = $doc->tracking_number ?: $doc->reference_number;
                    @endphp
                    <tr class="doc-row"
                        data-search="{{ strtolower(trim(($docRef ?: '') . ' ' . ($docTracking ?: '') . ' ' . ($doc->subject ?? '') . ' ' . ($doc->type ?? ''))) }}"
                        data-status="{{ $doc->status }}"
                        data-ref="{{ $docRef }}"
                        data-tracking="{{ $docTracking }}">
                        <td>
                            <span class="t-num">{{ $docRef ?: 'N/A' }}</span>
                        </td>
                        <td class="t-subject" title="{{ $doc->subject }}">{{ $doc->subject }}</td>
                        <td class="t-type"><div class="cell-ellipsis" style="max-width:160px" title="{{ $doc->type }}">{{ $doc->type }}</div></td>
                        <td>
                            <span class="pill pill-{{ $doc->status }}">{{ $doc->statusLabel() }}</span>
                        </td>
                        <td class="t-office">
                            @if($doc->status === 'submitted')
                                <div class="cell-ellipsis" title="{{ 'Awaiting physical submission to ' . ($doc->submittedToOffice->name ?? 'Records Section') }}">{{ 'Awaiting physical submission to ' . ($doc->submittedToOffice->name ?? 'Records Section') }}</div>
                            @else
                                <div class="cell-ellipsis" title="{{ $doc->currentOffice->name ?? $doc->submittedToOffice->name ?? 'No office assigned' }}">{{ $doc->currentOffice->name ?? $doc->submittedToOffice->name ?? 'No office assigned' }}</div>
                            @endif
                        </td>
                        <td class="t-date">{{ $doc->created_at->format('M d, Y') }}</td>
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
                    <tr id="noResultRow">
                        <td colspan="7"><i class="fas fa-search" style="margin-right:6px;opacity:.4"></i>No documents match your search.</td>
                    </tr>
                </tbody>
            </table>
            </div>
            @include('partials.shared-pagination', [
                'paginator' => $documents,
                'itemLabel' => 'documents',
            ])
        @endif
    </div>

</div>

<!-- ─── Detail Drawer ─── -->
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
        <div class="drawer-loader" id="drawerLoader">
            <span class="loading-dots"><span></span></span>
            Loading details...
        </div>
    </div>
    <div class="drawer-action-bar" id="drawerActionBar" style="display:none">
        <div class="pickup-notice">
            <i class="fas fa-box-open"></i>
            Your document is ready for pickup. Please confirm once you have physically received it.
        </div>
        <button class="btn-confirm-pickup" id="drawerPickupBtn" onclick="openPickupModal()">
            <i class="fas fa-check-circle"></i> Confirm I Received This Document
        </button>
    </div>

</div>

<!-- ─── Pickup Confirmation Modal ─── -->
<div class="modal-backdrop" id="pickupModal">
    <div class="modal-box">
        <div class="modal-icon-wrap"><i class="fas fa-box-open"></i></div>
        <h3>Confirm Document Receipt</h3>
        <p>Are you sure you have physically received this document? This action cannot be undone and will mark the document as <strong>Completed</strong>.</p>
        <div class="modal-actions">
            <button onclick="closePickupModal()">Cancel</button>
            <button class="modal-confirm" id="pickupConfirmBtn" onclick="submitPickupConfirm()">
                <i class="fas fa-check"></i> Yes, I Received It
            </button>
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

<script type="application/json" id="docsData">
    @php
        $docData = [];
        foreach($documents as $doc) {
            $entry = [
                'reference_number' => $doc->reference_number ?: $doc->tracking_number,
                'tracking_number' => $doc->tracking_number,
                'subject' => $doc->subject,
                'type' => $doc->type ?? 'General',
                'status' => $doc->status,
                'status_label' => $doc->statusLabel(),
                'sender_name' => $doc->sender_name ?? ($doc->user->name ?? 'Guest'),
                'submitted_to_office' => optional($doc->submittedToOffice)->name ?? 'Records Section',
                'current_office' => optional($doc->currentOffice)->name ?? optional($doc->submittedToOffice)->name ?? 'No office assigned',
                'current_handler' => 'Unassigned',
                'date' => $doc->created_at?->format('M d, Y h:i A') ?? '-',
            ];
            $docData[$doc->tracking_number] = $entry;
            if ($doc->reference_number && $doc->reference_number !== $doc->tracking_number) {
                $docData[$doc->reference_number] = $entry;
            }
        }
    @endphp
    @json($docData)
</script>

<script>
var _searchTimer = null;
var _lastSearchSyncAt = 0;
var SEARCH_URL_SYNC_DELAY = 700;
var SEARCH_URL_MIN_INTERVAL = 1200;
var docsData = JSON.parse(document.getElementById('docsData').textContent || '{}');

function filterDocs(immediate) {
    var q      = document.getElementById('searchInput').value.toLowerCase().trim();
    var status = document.getElementById('statusFilter').value;
    var rows   = document.querySelectorAll('#docsTable tbody tr:not(#noResultRow)');
    var shown  = 0;

    rows.forEach(function(row) {
        var matchSearch = !q || (row.dataset.search && row.dataset.search.includes(q));
        var matchStatus = !status || row.dataset.status === status;
        var visible = matchSearch && matchStatus;
        row.classList.toggle('hidden-row', !visible);
        if (visible) shown++;
    });

    var noResult = document.getElementById('noResultRow');
    if (noResult) noResult.style.display = (shown === 0 && rows.length > 0) ? 'table-row' : 'none';

    var countEl = document.getElementById('resultCount');
    if (countEl) {
        if (q || status) {
            var compactCount = window.formatCompactCount || function(v) { return String(v); };
            countEl.textContent = compactCount(shown) + ' result' + (shown !== 1 ? 's' : '');
        } else {
            countEl.textContent = '';
        }
    }

    scheduleFilterSync(q, status, !!immediate);
}

function syncFiltersToUrl(q, status) {
    var params = new URLSearchParams();
    if (q)      params.set('search', q);
    if (status) params.set('status', status);
    // page resets to 1 on new filter (don't carry page param)

    var newUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '');
    // Navigate so the server applies the filter to the full dataset
    if (newUrl !== window.location.pathname + window.location.search) {
        window.location.href = newUrl;
    }
}

function scheduleFilterSync(q, status, immediate) {
    clearTimeout(_searchTimer);

    var runSync = function() {
        var now = Date.now();
        var remaining = SEARCH_URL_MIN_INTERVAL - (now - _lastSearchSyncAt);

        if (remaining > 0) {
            _searchTimer = setTimeout(runSync, remaining);
            return;
        }

        _lastSearchSyncAt = Date.now();
        syncFiltersToUrl(q, status);
    };

    if (immediate) {
        runSync();
        return;
    }

    _searchTimer = setTimeout(runSync, SEARCH_URL_SYNC_DELAY);
}

function logout() {
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/api/logout', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrf, 'Accept':'application/json' }
    }).then(function() {
        window.location.href = '/login';
    }).catch(function() {
        window.location.href = '/login';
    });
}

function bindDocRows() {
    var rows = document.querySelectorAll('#docsTable tbody tr.doc-row');
    rows.forEach(function(row) {
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

// ─── Document Detail Drawer ───
var _csrfNode = document.querySelector('meta[name="csrf-token"]');
var _csrf = _csrfNode ? _csrfNode.getAttribute('content') : '';

function openDocDetail(ref, tracking) {
    ref = (ref || '').toString().trim();
    tracking = (tracking || ref).toString().trim();
    document.getElementById('drTitle').textContent  = '—';
    document.getElementById('drRef').textContent    = ref || tracking || '-';
    document.getElementById('drTrack').textContent  = '';
    document.getElementById('drawerBody').innerHTML =
        '<div class="drawer-loader" id="drawerLoader"><span class="loading-dots"><span></span></span>Loading details…</div>';
    document.getElementById('drawerOverlay').classList.add('open');
    document.getElementById('docDrawer').classList.add('open');
    document.body.style.overflow = 'hidden';

    window.docTraxFetchJson('/api/track-document', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' },
        timeoutMs: 15000,
        body: JSON.stringify({
            reference_number: ref,
            tracking_number: tracking || ref
        })
    })
    .then(function(data) {
        if (!data.success || !data.document) {
            document.getElementById('drawerBody').innerHTML =
                '<div class="drawer-loader"><i class="fas fa-file-circle-question" style="font-size:32px;color:#cbd5e1;margin-bottom:8px"></i>Document not found.</div>';
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

function dotClass(s) {
    if (s === 'cancelled' || s === 'returned') return 'c-danger';
    if (s === 'completed') return 'c-done';
    if (s === 'forwarded') return 'c-warn';
    return 'c-active';
}

function escapeHtml(value) {
    return String(value === null || value === undefined ? '' : value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

var _currentDrawerRef = null;

function renderDrawer(doc) {
    _currentDrawerRef = doc.reference_number || doc.tracking_number || '-';
    document.getElementById('drTitle').textContent = doc.subject || '-';
    var refNo = doc.reference_number || doc.tracking_number || '-';
    document.getElementById('drRef').textContent = refNo;
    document.getElementById('drTrack').textContent = '';

    // Show/hide pickup action bar
    var actionBar = document.getElementById('drawerActionBar');
    var pickupBtn = document.getElementById('drawerPickupBtn');
    if (doc.status === 'for_pickup') {
        actionBar.style.display = '';
        pickupBtn.innerHTML = '<i class="fas fa-check-circle"></i> Confirm I Received This Document';
        pickupBtn.disabled = false;
    } else {
        actionBar.style.display = 'none';
    }



    var logs = doc.routing_logs || [];
    var tlHtml = '';
    if (!logs.length) {
        tlHtml = '<div style="color:var(--text-muted);font-size:13px;padding:4px 0">No routing history yet.</div>';
    } else {
        // Mark voided logs: for_pickup entries followed by a revert to an active status
        var voidedIds = new Set();
        var revertStatuses = ['in_review', 'on_hold'];
        // Sort chronologically (oldest first) for pattern detection
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
                           (log.action === 'forwarded' ? (log.from_office || 'Unknown') :
                           (log.to_office || log.from_office || 'Unknown'));
            var groupLabel = (groupKey === '__pending__') ? 'Submitted — Pending Physical Submission' : groupKey;
            if (groupKey !== prevGroupKey) {
                prevGroupKey = groupKey;
                tlHtml += '<div class="tl-office-hdr"><div class="tl-dot ' + dc + '" style="margin-right:5px"><i class="fas ' + dotIcon + '" style="font-size:5px"></i></div><span>' + escapeHtml(groupLabel) + '</span></div>';
            }
            var voidBadge = isVoided ? ' <span class="tl-void-badge">VOIDED</span>' : '';
            var performerHtml = log.performed_by ? '<div class="tl-action">' + escapeHtml(log.performed_by) + voidBadge + '</div>' : (isVoided ? '<div class="tl-action">' + voidBadge + '</div>' : '');
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

// ─── Pickup confirmation ───
function openPickupModal() {
    document.getElementById('pickupModal').classList.add('show');
}
function closePickupModal() {
    document.getElementById('pickupModal').classList.remove('show');
    document.getElementById('pickupConfirmBtn').disabled = false;
}
function submitPickupConfirm() {
    if (!_currentDrawerRef) return;
    var btn = document.getElementById('pickupConfirmBtn');
    btn.disabled = true;
    document.getElementById('drawerPickupBtn').disabled = true;
    fetch('/api/documents/' + encodeURIComponent(_currentDrawerRef) + '/confirm-pickup', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' },
        body: '{}'
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            closePickupModal();
            closeDrawer();
            window.location.reload();
        } else {
            alert(d.message || 'Failed. Please try again.');
            closePickupModal();
            document.getElementById('drawerPickupBtn').disabled = false;
        }
    })
    .catch(function() {
        alert('Something went wrong. Please try again.');
        closePickupModal();
        document.getElementById('drawerPickupBtn').disabled = false;
    });
}

document.addEventListener('keydown', function(e) { if (e.key === 'Escape') { closePickupModal(); closeDrawer(); closeSidebar(); } });



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

// Run on load to show count if filters pre-filled from URL
document.addEventListener('DOMContentLoaded', function() {
    bindDocRows();
    var q = document.getElementById('searchInput').value;
    var s = document.getElementById('statusFilter').value;
    if (q || s) filterDocs();
});

// Also run immediately if DOM already ready (SPA swap re-executes this script after DOMContentLoaded has fired)
if (document.readyState !== 'loading') {
    bindDocRows();
    var _q2 = document.getElementById('searchInput');
    var _s2 = document.getElementById('statusFilter');
    if ((_q2 && _q2.value) || (_s2 && _s2.value)) filterDocs();
}
</script>
</body>
</html>
