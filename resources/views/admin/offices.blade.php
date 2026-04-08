<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Office Accounts - DepEd DOCTRAX</title>
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
            --danger: #dc2626;
            --danger-dark: #b91c1c;
            --danger-soft: #fef2f2;
            --danger-soft-2: #fecaca;
            --slate-soft: #f1f5f9;
            --slate-soft-2: #e2e8f0;
            --slate: #475569;
            --slate-dark: #334155;
            --text-dark: #1b263b;
            --text-muted: #64748b;
            --white: #ffffff;
            --bg: #f0f2f5;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
            --shadow-md: 0 4px 12px rgba(0,0,0,.08);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        html { overflow-y: scroll; }
        body { background:var(--bg); font-family:'Poppins',sans-serif; color:var(--text-dark); min-height:100vh; display:flex; flex-direction:column; }

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

        /* ─── Wrapper ─── */
        .dash-wrapper { max-width:1200px; margin:0 auto; padding:28px 5% 60px; flex:1; width:100%; }
        .page-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
        .page-header h1 { font-size:22px; font-weight:700; }
        .page-header p { font-size:13px; color:var(--text-muted); margin-top:2px; }
        .back-link { display:inline-flex; align-items:center; gap:6px; color:var(--text-muted); font-size:13px; text-decoration:none; padding:8px 14px; border:1px solid var(--border); border-radius:8px; background:#fff; transition:all .2s; }
        .back-link:hover { color:var(--primary); border-color:var(--primary); }

        /* ─── Btn Create ─── */
        .btn-create { display:inline-flex; align-items:center; gap:8px; background:var(--primary-gradient); color:#fff; border:none; padding:10px 18px; border-radius:10px; font-size:13px; font-weight:600; font-family:inherit; cursor:pointer; transition:opacity .2s; }
        .btn-create:hover { opacity:.9; }

        /* ─── Panel ─── */
        .panel { background:#fff; border-radius:16px; box-shadow:var(--shadow-sm); border:1px solid var(--border); overflow:hidden; }
        .panel.list-panel.has-list { display:flex; flex-direction:column; max-height:clamp(520px,72vh,820px); }
        .panel.list-panel.has-list .dtable-wrap { flex:1; min-height:0; overflow:auto; overscroll-behavior:contain; -webkit-overflow-scrolling:touch; }
        .panel.list-panel.has-list .dtable-wrap .dtable th { position:sticky; top:0; z-index:2; }
        .panel.list-panel.has-list .mob-cards,
        .panel.list-panel.has-list .empty-state { flex:1; min-height:0; }
        .panel-head { padding:18px 20px; border-bottom:1px solid var(--border); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; }
        .panel-title { font-size:17px; font-weight:700; }

        /* ─── Table ─── */
        .dtable { width:100%; border-collapse:collapse; font-size:13px; }
        .dtable th { padding:11px 14px; text-align:left; font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; background:#f8fafc; border-bottom:1px solid var(--border); }
        .dtable td { padding:13px 14px; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
        .dtable tr:last-child td { border-bottom:none; }
        .dtable tr:hover td { background:#fafbfc; }

        .name-cell { display:flex; flex-direction:column; gap:2px; }
        .name-main { font-weight:600; color:var(--text-dark); }
        .name-sub { font-size:11px; color:var(--text-muted); display:flex; align-items:center; gap:4px; }

        /* ─── Badges ─── */
        .pill { display:inline-flex; align-items:center; padding:3px 10px; border-radius:99px; font-size:11px; font-weight:600; }
        .pill.active { background:var(--blue-soft); color:var(--primary); }
        .pill.pending { background:var(--slate-soft); color:var(--slate-dark); }
        .pill.suspended { background:var(--slate-soft-2); color:var(--slate-dark); }

        /* ─── Actions ─── */
        .action-btns { display:flex; gap:6px; flex-wrap:wrap; }
        .btn-sm { width:30px; height:30px; border:none; border-radius:8px; cursor:pointer; display:flex; align-items:center; justify-content:center; font-size:12px; transition:all .2s; }
        .btn-sm.suspend { background:var(--danger-soft); color:var(--danger); box-shadow:inset 0 0 0 1px rgba(220,38,38,.16); }
        .btn-sm.suspend:hover { background:var(--danger); color:#fff; box-shadow:none; }
        .btn-sm.activate { background:var(--blue-soft); color:var(--primary); }
        .btn-sm.activate:hover { background:var(--primary); color:#fff; }
        .btn-sm.reports-on { background:var(--blue-soft-2); color:var(--primary); }
        .btn-sm.reports-on:hover { background:var(--primary); color:#fff; }
        .btn-sm.reports-off { background:var(--slate-soft); color:var(--slate); }
        .btn-sm.reports-off:hover { background:var(--slate-dark); color:#fff; }
        .btn-sm.transfer { background:var(--slate-soft); color:var(--slate-dark); }
        .btn-sm.transfer:hover { background:var(--slate-dark); color:#fff; }

        .badge-reports { display:inline-flex; align-items:center; gap:4px; padding:2px 8px; border-radius:99px; font-size:10px; font-weight:600; }
        .badge-reports.on { background:var(--blue-soft-2); color:var(--primary); }
        .badge-reports.off { background:var(--slate-soft); color:var(--slate); }

        /* ─── Empty ─── */
        .empty-state { padding:60px 20px; text-align:center; color:var(--text-muted); }
        .empty-state i { font-size:36px; margin-bottom:12px; opacity:.35; }
        .empty-state p { font-size:14px; }

        /* ─── Modal ─── */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,.45); z-index:200; align-items:center; justify-content:center; padding:16px; }
        .modal-overlay.show { display:flex; }
        .modal { background:#fff; border-radius:16px; max-width:480px; width:100%; max-height:min(88vh,720px); display:flex; flex-direction:column; box-shadow:0 20px 60px rgba(0,0,0,.2); }
        .modal-head { padding:20px 24px 0; }
        .modal-head h3 { font-size:16px; font-weight:700; }
        .modal-body { padding:20px 24px; display:flex; flex-direction:column; gap:14px; max-height:70vh; overflow-y:auto; }
        .modal-field label { display:block; font-size:12px; font-weight:600; color:var(--text-muted); margin-bottom:5px; text-transform:uppercase; letter-spacing:.3px; }
        .modal-input { width:100%; box-sizing:border-box; padding:10px 14px; border:1.5px solid var(--border); border-radius:10px; font-size:14px; font-family:inherit; color:var(--text-dark); outline:none; transition:border .2s; background:#fff; }
        .modal-input:focus { border-color:var(--primary); }
        .modal-select-wrap { position:relative; }
        .modal-select-wrap::after { content:'\f078'; font-family:'Font Awesome 6 Free'; font-weight:900; position:absolute; right:14px; top:50%; transform:translateY(-50%); color:#64748b; font-size:12px; pointer-events:none; }
        select.modal-input { appearance:none; -webkit-appearance:none; -moz-appearance:none; padding-right:40px; min-height:46px; line-height:1.35; text-overflow:ellipsis; white-space:nowrap; overflow:hidden; }
        .modal-foot { padding:16px 24px; border-top:1px solid var(--border); display:flex; gap:10px; justify-content:flex-end; }
        .modal-btn { padding:9px 18px; border-radius:10px; font-size:13px; font-weight:600; font-family:inherit; cursor:pointer; border:1px solid var(--border); background:#fff; color:var(--text-dark); transition:all .2s; }
        .modal-btn:hover { background:#f1f5f9; }
        .modal-btn.primary { background:var(--primary-gradient); color:#fff; border:none; }
        .modal-btn.primary:hover { opacity:.9; }
        .modal-btn.danger { background:#dc2626; color:#fff; border:none; }
        .modal-btn.danger:hover { background:#b91c1c; }
        .modal-btn.warning { background:var(--slate-dark); color:#fff; border:none; }
        .modal-btn.warning:hover { background:#243244; }
        .modal-btn.success { background:var(--primary); color:#fff; border:none; }
        .modal-btn.success:hover { background:var(--primary-dark); }
        .status-modal-row { display:block; padding-top:4px; }
        .status-modal-copy { min-width:0; }
        .status-modal-msg { margin-bottom:0; font-size:14px; color:#475569; line-height:1.6; word-break:break-word; }
        .status-modal-sub { font-size:12px; color:#94a3b8; margin-top:6px; line-height:1.55; }

        /* ─── Toast ─── */
        .toast {
            position: fixed;
            top: 80px;
            right: 24px;
            z-index: 300;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 14px 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,.1);
            font-size: 13px;
            font-family: 'Poppins', sans-serif;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
            transform: translateX(calc(100% + 60px));
            transition: transform 0.3s ease;
        }
        .toast.show { transform: translateX(0); }
        .toast.success { border-left: 3px solid var(--primary); }
        .toast.error { border-left: 3px solid #dc2626; }

        /* ─── Inline field errors (matching site-wide style) ─── */
        .modal-input.error { border-color:#dc2626; background:#fef2f2; }
        .field-err { background:#fef2f2; border-left:4px solid #dc2626; color:#dc2626; padding:9px 12px; border-radius:6px; font-size:13px; display:none; align-items:center; gap:8px; margin-top:6px; animation:errIn .2s ease; }
        .field-err.show { display:flex; }
        .field-err i { font-size:14px; flex-shrink:0; }
        @keyframes errIn { from { opacity:0; transform:translateY(-3px); } to { opacity:1; transform:translateY(0); } }

        .dash-footer{width:100%;background:#fff;border-top:1px solid #e2e8f0;padding:20px 5%;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8}
        .footer-left{display:flex;align-items:center;gap:6px}
        .footer-right{font-size:11px;color:#b0b8c4}

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
        .mob-card-office { font-size:12px; font-weight:600; color:var(--primary); margin-top:2px; }
        .mob-card-row { display:flex; justify-content:space-between; align-items:center; padding:5px 0; font-size:12px; color:var(--text-muted); }
        .mob-card-row .label { font-weight:600; text-transform:uppercase; font-size:10px; letter-spacing:.3px; }
        .mob-card-row .value { font-weight:500; color:var(--text-dark); text-align:right; word-break:break-all; }
        .mob-card-actions { display:flex; gap:8px; margin-top:12px; padding-top:12px; border-top:1px solid var(--border); flex-wrap:wrap; }
        .mob-card-actions .btn-sm { width:auto; padding:6px 12px; font-size:11px; font-weight:600; gap:5px; height:auto; border-radius:8px; }
        .mob-card-actions .btn-sm i { font-size:11px; }

        @media(max-width:900px){
            .dash-wrapper{padding:20px 16px 40px}
            .page-header h1{font-size:18px}
            .page-header p{font-size:12px}
            .btn-create{font-size:12px;padding:8px 14px}
            .panel.list-panel.has-list{max-height:min(68vh,560px)}
            .panel .dtable-wrap{display:none}
            .mob-cards{display:block;padding:12px;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch}
            .modal{max-width:95vw;max-height:calc(100dvh - 24px);border-radius:14px}
            .modal-body{padding:16px}
            .modal-head{padding:16px 16px 0}
            .modal-foot{padding:12px 16px;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
            .modal-foot .modal-btn{width:100%;min-height:44px;display:flex;align-items:center;justify-content:center;padding-inline:12px}
            select.modal-input{min-height:50px;font-size:16px}
            .status-modal-msg{font-size:13px}
            .dash-footer{flex-direction:column;gap:6px;text-align:center;padding:16px 5%}
            .toast{right:12px;left:12px;max-width:none}
        }
    </style>
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
</head>
<body>

    @php $csrf = csrf_token(); @endphp

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
        <a href="/admin/offices" class="active"><i class="fas fa-building"></i> Offices</a>
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
    <div class="dash-wrapper">

        <div class="page-header">
            <div>
                <h1>Office Accounts</h1>
                <p>Manage internal DepEd office accounts for document routing</p>
            </div>
            <button class="btn-create" onclick="openCreateModal()">
                <i class="fas fa-plus"></i> Create Office Account
            </button>
        </div>

        <div class="panel list-panel{{ $accounts->count() ? ' has-list' : '' }}">
            <div class="panel-head">
                <div class="panel-title">Office Accounts ({{ $accounts->total() }})</div>
            </div>

            @if($accounts->count() > 0)
            <div class="dtable-wrap">
            <table class="dtable">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Assigned Office</th>
                        <th>Email</th>
                        <th>Mobile</th>
                        <th>Reports</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accounts as $acc)
                    <tr id="acc-row-{{ $acc->id }}">
                        <td>
                            <div class="name-cell">
                                <span class="name-main">{{ $acc->name }}</span>
                            </div>
                        </td>
                        <td>
                            <span style="font-size:13px;font-weight:600;color:var(--primary);" id="acc-office-{{ $acc->id }}">
                                {{ $acc->office->name ?? 'No office assigned' }}
                            </span>
                        </td>
                        <td><!--email_off-->{{ $acc->email }}<!--/email_off--></td>
                        <td>{{ $acc->mobile ?? 'No number provided' }}</td>
                        <td>
                            <span class="badge-reports {{ $acc->has_reports_access ? 'on' : 'off' }}" id="acc-reports-badge-{{ $acc->id }}">
                                @if($acc->has_reports_access)<i class="fas fa-chart-line"></i>@endif
                                {{ $acc->has_reports_access ? 'Enabled' : 'Disabled' }}
                            </span>
                        </td>
                        <td>
                            <span class="pill {{ $acc->status }}">{{ ucfirst($acc->status) }}</span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-sm {{ $acc->has_reports_access ? 'reports-on' : 'reports-off' }}" id="acc-reports-btn-{{ $acc->id }}" onclick="toggleReports({{ $acc->id }}, '{{ addslashes($acc->name) }}')" title="{{ $acc->has_reports_access ? 'Revoke reports access' : 'Grant reports access' }}">
                                    <i class="fas fa-chart-line"></i>
                                </button>
                                <button class="btn-sm transfer" onclick="openTransferModal({{ $acc->id }}, '{{ addslashes($acc->name) }}', {{ $acc->office_id }})" title="Transfer to another office">
                                    <i class="fas fa-exchange-alt"></i>
                                </button>
                                @if($acc->status !== 'suspended')
                                <button class="btn-sm suspend" onclick="confirmStatus({{ $acc->id }}, 'suspended', '{{ addslashes($acc->name) }}')" title="Deactivate">
                                    <i class="fas fa-ban"></i>
                                </button>
                                @else
                                <button class="btn-sm activate" onclick="confirmStatus({{ $acc->id }}, 'active', '{{ addslashes($acc->name) }}')" title="Activate">
                                    <i class="fas fa-check"></i>
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
                @foreach($accounts as $acc)
                <div class="mob-card" id="mob-acc-{{ $acc->id }}">
                    <div class="mob-card-head">
                        <div>
                            <div class="mob-card-name">{{ $acc->name }}</div>
                            <div class="mob-card-office" id="mob-acc-office-{{ $acc->id }}">
                                <i class="fas fa-building" style="font-size:10px;margin-right:3px;"></i>
                                {{ $acc->office->name ?? 'No office assigned' }}
                            </div>
                        </div>
                        <span class="pill {{ $acc->status }}">{{ ucfirst($acc->status) }}</span>
                    </div>
                    <div class="mob-card-row">
                        <span class="label">Email</span>
                        <span class="value"><!--email_off-->{{ $acc->email }}<!--/email_off--></span>
                    </div>
                    <div class="mob-card-row">
                        <span class="label">Mobile</span>
                        <span class="value">{{ $acc->mobile ?? 'No number provided' }}</span>
                    </div>
                    <div class="mob-card-row">
                        <span class="label">Reports</span>
                        <span class="value">
                            <span class="badge-reports {{ $acc->has_reports_access ? 'on' : 'off' }}" id="mob-acc-reports-badge-{{ $acc->id }}">
                                @if($acc->has_reports_access)<i class="fas fa-chart-line"></i>@endif
                                {{ $acc->has_reports_access ? 'Enabled' : 'Disabled' }}
                            </span>
                        </span>
                    </div>
                    <div class="mob-card-actions">
                        <button class="btn-sm {{ $acc->has_reports_access ? 'reports-on' : 'reports-off' }}" id="mob-acc-reports-btn-{{ $acc->id }}" onclick="toggleReports({{ $acc->id }}, '{{ addslashes($acc->name) }}')" title="{{ $acc->has_reports_access ? 'Revoke reports access' : 'Grant reports access' }}">
                            <i class="fas fa-chart-line"></i> Reports
                        </button>
                        <button class="btn-sm transfer" onclick="openTransferModal({{ $acc->id }}, '{{ addslashes($acc->name) }}', {{ $acc->office_id }})" title="Transfer">
                            <i class="fas fa-exchange-alt"></i> Transfer
                        </button>
                        @if($acc->status !== 'suspended')
                        <button class="btn-sm suspend" onclick="confirmStatus({{ $acc->id }}, 'suspended', '{{ addslashes($acc->name) }}')" title="Deactivate">
                            <i class="fas fa-ban"></i> Deactivate
                        </button>
                        @else
                        <button class="btn-sm activate" onclick="confirmStatus({{ $acc->id }}, 'active', '{{ addslashes($acc->name) }}')" title="Activate">
                            <i class="fas fa-check"></i> Activate
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
            @include('partials.shared-pagination', [
                'paginator' => $accounts,
                'itemLabel' => 'office accounts',
            ])
            @else
            <div class="empty-state">
                <i class="fas fa-building"></i>
                <p>No office accounts yet. Create one to get started.</p>
            </div>
            @endif
        </div>

    </div>

    <!-- Create Office Account Modal -->
    <div class="modal-overlay" id="createModal">
        <div class="modal">
            <div class="modal-head">
                <h3><i class="fas fa-building" style="color:var(--primary);margin-right:6px;"></i> Create Office Account</h3>
            </div>
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0 14px;">
                    <div class="modal-field">
                        <label>First Name <span style="color:#dc2626">*</span></label>
                        <input type="text" class="modal-input" id="createFirstName" placeholder="e.g. Juan" maxlength="100" autocomplete="off" oninput="this.value=this.value.replace(/[^a-zA-Z\u00C0-\u024F\s\-\.\x27]/g,'')">
                        <div class="field-err" id="err-createFirstName"><i class="fas fa-exclamation-circle"></i><span></span></div>
                    </div>
                    <div class="modal-field">
                        <label>Last Name <span style="color:#dc2626">*</span></label>
                        <input type="text" class="modal-input" id="createLastName" placeholder="e.g. dela Cruz" maxlength="100" autocomplete="off" oninput="this.value=this.value.replace(/[^a-zA-Z\u00C0-\u024F\s\-\.\x27]/g,'')">
                        <div class="field-err" id="err-createLastName"><i class="fas fa-exclamation-circle"></i><span></span></div>
                    </div>
                </div>
                <div class="modal-field">
                    <label>Middle Name <span style="color:#dc2626" id="createMidRequired">*</span></label>
                    <input type="text" class="modal-input" id="createMiddleName" placeholder="e.g. Santos" maxlength="100" autocomplete="off" oninput="this.value=this.value.replace(/[^a-zA-Z\u00C0-\u024F\s\-\.\x27]/g,'')">
                    <div class="field-err" id="err-createMiddleName"><i class="fas fa-exclamation-circle"></i><span></span></div>
                    <label style="display:flex;align-items:center;gap:6px;margin-top:6px;cursor:pointer;font-size:12px;color:#64748b;font-weight:400;user-select:none;">
                        <input type="checkbox" id="createNoMiddle" onchange="toggleCreateMiddle()" style="width:14px;height:14px;accent-color:#0056b3;cursor:pointer;">
                        I don't have a middle name
                    </label>
                </div>
                <div class="modal-field">
                    <label>Mobile Number <span style="color:var(--text-muted);font-weight:400;">(optional)</span></label>
                    <input type="text" class="modal-input" id="createMobile" placeholder="09XXXXXXXXX" maxlength="11" inputmode="numeric" autocomplete="off" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)">
                    <div class="field-err" id="err-createMobile"><i class="fas fa-exclamation-circle"></i><span></span></div>
                </div>
                <div class="modal-field">
                    <label>Email Address <span style="color:#dc2626">*</span></label>
                    <input type="email" class="modal-input" id="createEmail" placeholder="Email" maxlength="255" autocomplete="email" inputmode="email" autocapitalize="none" autocorrect="off" spellcheck="false">
                    <div class="field-err" id="err-createEmail"><i class="fas fa-exclamation-circle"></i><span></span></div>
                </div>
                <div class="modal-field">
                    <label>Assigned Office <span style="color:#dc2626">*</span></label>
                    <div class="modal-select-wrap"><select class="modal-input" id="createOfficeId" style="box-sizing:border-box" onchange="toggleCustomOffice()">
                        <option value="">— Select office —</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}" data-name="{{ $office->name }}">{{ $office->name }}</option>
                        @endforeach
                        <option value="other">Others (Add New Office)</option>
                    </select></div>
                    <div class="field-err" id="err-createOfficeId"><i class="fas fa-exclamation-circle"></i><span></span></div>
                </div>
                <div class="modal-field" id="customOfficeField" style="display:none">
                    <label>New Office Name <span style="color:#dc2626">*</span></label>
                    <input type="text" class="modal-input" id="createCustomOffice" placeholder="e.g. Accounting Unit" maxlength="255" autocomplete="off">
                    <div class="field-err" id="err-createCustomOffice"><i class="fas fa-exclamation-circle"></i><span></span></div>
                </div>
                <p style="font-size:12px;color:var(--text-muted);margin-top:-4px;">
                    <i class="fas fa-info-circle" style="margin-right:4px;"></i>
                    An activation email will be sent so the user can set their password.
                </p>
            </div>
            <div class="modal-foot">
                <button class="modal-btn" onclick="closeCreateModal()">Cancel</button>
                <button class="modal-btn primary" id="saveCreateBtn" onclick="saveCreate()">
                    <i class="fas fa-plus"></i> Create Account
                </button>
            </div>
        </div>
    </div>

    <!-- Suspend / Activate Confirmation Modal -->
    <div class="modal-overlay" id="statusModal">
        <div class="modal">
            <div class="modal-head">
                <h3 id="statusModalTitle">Deactivate Office Account</h3>
            </div>
            <div class="modal-body">
                <div class="status-modal-row">
                    <div class="status-modal-copy">
                        <p id="statusModalMsg" class="status-modal-msg"></p>
                        <p id="statusModalSub" class="status-modal-sub"></p>
                    </div>
                </div>
            </div>
            <div class="modal-foot">
                <button class="modal-btn" onclick="closeStatusModal()">Cancel</button>
                <button class="modal-btn" id="confirmStatusBtn"></button>
            </div>
        </div>
    </div>

    <!-- Transfer Office Modal -->
    <div class="modal-overlay" id="transferModal">
        <div class="modal">
            <div class="modal-head">
                <h3>Transfer Personnel</h3>
            </div>
            <div class="modal-body">
                <p style="font-size:13px;color:var(--text-muted);margin-bottom:4px;">
                    Transfer <strong id="transferAccName"></strong> to a different office.
                </p>
                <p style="font-size:11px;color:#94a3b8;margin-bottom:8px;">
                    <i class="fas fa-info-circle" style="margin-right:3px;"></i>
                    Past documents and routing history will remain tagged to this user under their previous office.
                </p>
                <div class="modal-field">
                    <label>New Office <span style="color:#dc2626">*</span></label>
                    <div class="modal-select-wrap"><select class="modal-input" id="transferOfficeId" style="box-sizing:border-box">
                        <option value="">— Select new office —</option>
                        @foreach($offices as $office)
                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                        @endforeach
                    </select></div>
                    <div class="field-err" id="err-transferOfficeId"><i class="fas fa-exclamation-circle"></i><span></span></div>
                </div>
            </div>
            <div class="modal-foot">
                <button class="modal-btn" onclick="closeTransferModal()">Cancel</button>
                <button class="modal-btn primary" id="confirmTransferBtn" onclick="submitTransfer()">
                    Transfer
                </button>
            </div>
        </div>
    </div>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

    <script>
    (function() {
        var csrf = '{{ csrf_token() }}';

        function escapeHtml(str) {
            if (!str) return '';
            return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
        }

        function showToast(msg, type) {
            var t = document.getElementById('toast');
            t.textContent = msg;
            t.className = 'toast ' + (type || '') + ' show';
            setTimeout(function() { t.classList.remove('show'); }, 3000);
        }

        function logout() {
            fetch('/api/logout', { method:'POST', headers:{'X-CSRF-TOKEN':csrf} })
                .then(function() { window.location.href = '/login'; })
                .catch(function() { window.location.href = '/login'; });
        }
        window.logout = logout;
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
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeSidebar(); });

        // ─── Create ───
        window.toggleCreateMiddle = function() {
            var cb    = document.getElementById('createNoMiddle');
            var input = document.getElementById('createMiddleName');
            var req   = document.getElementById('createMidRequired');
            var err   = document.getElementById('err-createMiddleName');
            if (cb.checked) {
                input.value = ''; input.disabled = true; input.style.opacity = '0.5';
                input.classList.remove('error');
                if (err) { err.classList.remove('show'); var sp = err.querySelector('span'); if (sp) sp.textContent = ''; }
                if (req) req.style.display = 'none';
            } else {
                input.disabled = false; input.style.opacity = '1';
                if (req) req.style.display = '';
            }
        };
        window.openCreateModal = function() {
            ['createFirstName','createLastName','createMiddleName','createEmail','createMobile'].forEach(function(id){ document.getElementById(id).value = ''; });
            document.getElementById('createOfficeId').value = '';
            var cb = document.getElementById('createNoMiddle'); cb.checked = false;
            var mid = document.getElementById('createMiddleName'); mid.disabled = false; mid.style.opacity = '1';
            var req = document.getElementById('createMidRequired'); if(req) req.style.display = '';
            clearCreateErrors();
            document.getElementById('createModal').classList.add('show');
        };
        window.closeCreateModal = function() {
            document.getElementById('createModal').classList.remove('show');
            clearCreateErrors();
            document.getElementById('customOfficeField').style.display = 'none';
            document.getElementById('createCustomOffice').value = '';
        };

        function clearCreateErrors() {
            ['createFirstName','createLastName','createMiddleName','createEmail','createMobile','createOfficeId','createCustomOffice'].forEach(function(id) {
                var inp = document.getElementById(id);
                var err = document.getElementById('err-' + id);
                if (inp) inp.classList.remove('error');
                if (err) { err.classList.remove('show'); var sp = err.querySelector('span'); if (sp) sp.textContent = ''; }
            });
        }

        function setCreateErr(id, msg) {
            var inp = document.getElementById(id);
            var err = document.getElementById('err-' + id);
            if (inp) inp.classList.add('error');
            if (err) { var sp = err.querySelector('span'); if (sp) sp.textContent = msg; err.classList.add('show'); }
        }

        window.toggleCustomOffice = function() {
            var sel = document.getElementById('createOfficeId');
            var field = document.getElementById('customOfficeField');
            if (sel.value === 'other') {
                field.style.display = '';
                document.getElementById('createCustomOffice').focus();
            } else {
                field.style.display = 'none';
                document.getElementById('createCustomOffice').value = '';
            }
        };

        window.saveCreate = function() {
            clearCreateErrors();
            var firstName  = document.getElementById('createFirstName').value.trim();
            var lastName   = document.getElementById('createLastName').value.trim();
            var middleName = document.getElementById('createMiddleName').value.trim();
            var noMiddle   = document.getElementById('createNoMiddle').checked;
            var email      = document.getElementById('createEmail').value.trim();
            var mobile     = document.getElementById('createMobile').value.trim();
            var officeId   = document.getElementById('createOfficeId').value;
            var officeEl   = document.getElementById('createOfficeId');
            var customOffice = document.getElementById('createCustomOffice').value.trim();
            var isOther    = officeId === 'other';

            var valid = true;

            if (!firstName)                    { setCreateErr('createFirstName', 'First name is required.'); valid = false; }
            else if (/\d/.test(firstName))     { setCreateErr('createFirstName', 'First name must not contain numbers.'); valid = false; }
            if (!lastName)                     { setCreateErr('createLastName', 'Last name is required.'); valid = false; }
            else if (/\d/.test(lastName))      { setCreateErr('createLastName', 'Last name must not contain numbers.'); valid = false; }
            if (!noMiddle) {
                if (!middleName)               { setCreateErr('createMiddleName', 'Enter middle name or tick the checkbox.'); valid = false; }
                else if (/\d/.test(middleName)){ setCreateErr('createMiddleName', 'Middle name must not contain numbers.'); valid = false; }
            }
            if (!email)                        { setCreateErr('createEmail', 'Email address is required.'); valid = false; }
            else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { setCreateErr('createEmail', 'Enter a valid email address.'); valid = false; }
            if (mobile) {
                if (!/^\d+$/.test(mobile))          { setCreateErr('createMobile', 'Mobile must contain digits only.'); valid = false; }
                else if (!mobile.startsWith('09'))  { setCreateErr('createMobile', 'Mobile must start with 09.'); valid = false; }
                else if (mobile.length !== 11)      { setCreateErr('createMobile', 'Mobile must be exactly 11 digits.'); valid = false; }
            }
            if (!officeId)                     { setCreateErr('createOfficeId', 'Please select an assigned office.'); valid = false; }
            if (isOther && !customOffice)       { setCreateErr('createCustomOffice', 'Please enter the office name.'); valid = false; }

            if (!valid) return;

            var repName = (noMiddle || !middleName) ? (firstName + ' ' + lastName) : (firstName + ' ' + middleName + ' ' + lastName);
            var name    = repName;

            var payload = { name:name, email:email, mobile:mobile||null };
            if (isOther) {
                payload.new_office_name = customOffice;
            } else {
                payload.office_id = officeId;
            }

            var btn = document.getElementById('saveCreateBtn');
            btn.disabled = true;

            fetch('/api/admin/offices', {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf, 'Accept':'application/json' },
                body: JSON.stringify(payload)
            })
            .then(function(r) {
                return r.text().then(function(text) {
                    var data = {};
                    if (text) {
                        try {
                            data = JSON.parse(text);
                        } catch (e) {
                            throw new Error('Unexpected server response. Please try again.');
                        }
                    }

                    if (!r.ok && !data.message) {
                        throw new Error('Server error (' + r.status + '). Please try again.');
                    }

                    return data;
                });
            })
            .then(function(data) {
                btn.disabled = false;
                if (data.success) {
                    closeCreateModal();
                    showToast(data.message, 'success');
                    setTimeout(function() { window.location.reload(); }, 900);
                } else {
                    if (data.errors) {
                        var map = { name:'createFirstName', email:'createEmail', mobile:'createMobile', office_id:'createOfficeId', new_office_name:'createCustomOffice' };
                        for (var field in data.errors) {
                            var targetId = map[field] || null;
                            if (targetId) setCreateErr(targetId, data.errors[field][0]);
                        }
                    }
                    showToast(data.message || 'Failed to create account.', 'error');
                }
            })
            .catch(function(err) {
                btn.disabled = false;
                showToast((err && err.message) ? err.message : 'Something went wrong.', 'error');
            });
        };
        document.getElementById('createModal').addEventListener('click', function(e) {
            if (e.target === this) closeCreateModal();
        });

        // ─── Suspend / Activate ───
        var statusTargetId  = null;
        var statusTargetVal = null;

        window.confirmStatus = function(id, status, name) {
            statusTargetId  = id;
            statusTargetVal = status;

            var isSuspend  = status === 'suspended';
            var title      = isSuspend ? 'Deactivate Office Account' : 'Activate Office Account';
            var btnClass   = isSuspend ? 'danger'    : 'success';
            var btnLabel   = isSuspend ? 'Deactivate' : 'Activate';
            var msg        = isSuspend
                ? 'Are you sure you want to deactivate <strong>' + escapeHtml(name) + '</strong>?'
                : 'Are you sure you want to activate <strong>' + escapeHtml(name) + '</strong>?';
            var sub        = isSuspend
                ? 'This office account will be unable to log in. Their past documents remain intact.'
                : 'This office account will regain access to the system.';

            document.getElementById('statusModalTitle').textContent = title;
            document.getElementById('statusModalMsg').innerHTML     = msg;
            document.getElementById('statusModalSub').textContent   = sub;

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
            var btn    = this;
            btn.disabled = true;
            closeStatusModal();

            fetch('/api/admin/users/' + id, {
                method: 'PUT',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf, 'Accept':'application/json' },
                body: JSON.stringify({ status: status })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast(data.message, 'success');
                    setTimeout(function() { window.location.reload(); }, 800);
                } else {
                    showToast(data.message || 'Failed.', 'error');
                }
            })
            .catch(function() { showToast('Something went wrong.', 'error'); });
        });

        // ─── Toggle Reports Access ───
        window.toggleReports = function(id, name) {
            fetch('/api/admin/offices/' + id + '/reports', {
                method: 'PUT',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf, 'Accept':'application/json' },
                body: JSON.stringify({})
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast(data.message, 'success');
                    var on = data.has_reports_access;
                    // Update badge (desktop + mobile)
                    ['acc-reports-badge-', 'mob-acc-reports-badge-'].forEach(function(prefix) {
                        var badge = document.getElementById(prefix + id);
                        if (badge) {
                            badge.className = 'badge-reports ' + (on ? 'on' : 'off');
                            badge.innerHTML = (on ? '<i class="fas fa-chart-line"></i> Enabled' : 'Disabled');
                        }
                    });
                    // Update button style (desktop + mobile)
                    ['acc-reports-btn-', 'mob-acc-reports-btn-'].forEach(function(prefix) {
                        var btn = document.getElementById(prefix + id);
                        if (btn) {
                            btn.className = 'btn-sm ' + (on ? 'reports-on' : 'reports-off');
                            btn.title = on ? 'Revoke reports access' : 'Grant reports access';
                        }
                    });
                } else {
                    showToast(data.message || 'Failed.', 'error');
                }
            })
            .catch(function() { showToast('Something went wrong.', 'error'); });
        };

        // ─── Transfer Personnel ───
        var transferTargetId = null;
        var transferCurrentOffice = null;

        window.openTransferModal = function(id, name, currentOfficeId) {
            transferTargetId = id;
            transferCurrentOffice = currentOfficeId;
            document.getElementById('transferAccName').textContent = name;
            document.getElementById('transferOfficeId').value = '';

            // Clear errors
            var inp = document.getElementById('transferOfficeId');
            var err = document.getElementById('err-transferOfficeId');
            if (inp) inp.classList.remove('error');
            if (err) { err.classList.remove('show'); var sp = err.querySelector('span'); if (sp) sp.textContent = ''; }

            document.getElementById('transferModal').classList.add('show');
        };

        window.closeTransferModal = function() {
            document.getElementById('transferModal').classList.remove('show');
            transferTargetId = null;
            transferCurrentOffice = null;
        };

        document.getElementById('transferModal').addEventListener('click', function(e) {
            if (e.target === this) closeTransferModal();
        });

        window.submitTransfer = function() {
            var officeId = document.getElementById('transferOfficeId').value;

            // Clear errors
            var inp = document.getElementById('transferOfficeId');
            var err = document.getElementById('err-transferOfficeId');
            if (inp) inp.classList.remove('error');
            if (err) { err.classList.remove('show'); var sp = err.querySelector('span'); if (sp) sp.textContent = ''; }

            if (!officeId) {
                if (inp) inp.classList.add('error');
                if (err) { var sp = err.querySelector('span'); if (sp) sp.textContent = 'Please select a new office.'; err.classList.add('show'); }
                return;
            }

            if (parseInt(officeId) === transferCurrentOffice) {
                if (inp) inp.classList.add('error');
                if (err) { var sp = err.querySelector('span'); if (sp) sp.textContent = 'User is already assigned to this office.'; err.classList.add('show'); }
                return;
            }

            var btn = document.getElementById('confirmTransferBtn');
            btn.disabled = true;

            fetch('/api/admin/offices/' + transferTargetId + '/transfer', {
                method: 'PUT',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf, 'Accept':'application/json' },
                body: JSON.stringify({ office_id: officeId })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;
                if (data.success) {
                    showToast(data.message, 'success');
                    closeTransferModal();
                    // Update the office name in the table (desktop + mobile)
                    var officeEl = document.getElementById('acc-office-' + transferTargetId);
                    if (officeEl) officeEl.textContent = data.new_office_name;
                    var mobOfficeEl = document.getElementById('mob-acc-office-' + transferTargetId);
                    if (mobOfficeEl) mobOfficeEl.innerHTML = '<i class="fas fa-building" style="font-size:10px;margin-right:3px;"></i> ' + escapeHtml(data.new_office_name);
                } else {
                    showToast(data.message || 'Transfer failed.', 'error');
                }
            })
            .catch(function() {
                btn.disabled = false;
                showToast('Something went wrong.', 'error');
            });
        };
    })();
    </script>

    <footer class="dash-footer">
        <div class="footer-left">
            <span>&copy; {{ date('Y') }} DepEd Document Tracking System</span>
        </div>
        <div class="footer-right">
            Developed by Raymond Bautista
        </div>
    </footer>

</div><!-- end .main -->
</body>
</html>
