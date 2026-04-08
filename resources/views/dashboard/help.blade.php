<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Help &amp; Guide - DepEd DOCTRAX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="/js/request-utils.js" defer></script>


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
        .main { margin-left: 0; flex: 1; display: flex; flex-direction: column; }

        /* ─── Main wrapper ─── */
        .dash-wrapper { max-width: 780px; width: 100%; margin: 0 auto; padding: 32px 28px 48px; flex: 1; }

        /* ─── Page header ─── */
        .page-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 32px;
        }
        .page-header h1 { font-size: 24px; font-weight: 700; color: var(--text-dark); }
        .page-header p  { font-size: 13px; color: var(--text-muted); font-weight: 400; margin-top: 2px; }

        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; color: var(--primary); text-decoration: none; font-weight: 500;
            padding: 7px 14px; border-radius: 8px; border: 1px solid var(--border);
            background: var(--white); transition: all 0.15s; white-space: nowrap;
        }
        .back-link:hover { background: #f8fafc; border-color: var(--primary); }

        /* ─── TOC / Quick-nav ─── */
        .toc-card {
            background: var(--white); border: 1px solid var(--border);
            border-radius: 12px; box-shadow: var(--shadow-sm);
            padding: 18px 22px; margin-bottom: 24px;
        }
        .toc-card h3 {
            font-size: 11px; font-weight: 600; color: var(--text-muted);
            text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 12px;
        }
        .toc-list { display: flex; flex-wrap: wrap; gap: 8px; }
        .toc-item {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 7px 14px; border-radius: 8px;
            background: #f1f5f9; color: var(--primary);
            font-size: 12px; font-weight: 500; text-decoration: none;
            border: 1px solid transparent; transition: all 0.15s;
        }
        .toc-item:hover { background: var(--primary); color: #fff; }
        .toc-item i { font-size: 11px; }

        /* ─── Help sections ─── */
        .help-section {
            background: var(--white); border: 1px solid var(--border);
            border-radius: 12px; box-shadow: var(--shadow-sm);
            overflow: hidden; margin-bottom: 24px; scroll-margin-top: 96px;
        }
        .help-section-head {
            display: flex; align-items: center; gap: 14px;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border); background: #fafbfc;
        }
        .help-section-icon {
            width: 40px; height: 40px; border-radius: 10px;
            background: #eff6ff; color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 17px; flex-shrink: 0;
        }
        .help-section-head h2 { font-size: 16px; font-weight: 600; color: var(--text-dark); }
        .help-section-head p  { font-size: 12px; color: var(--text-muted); font-weight: 400; margin-top: 2px; }

        .help-section-body { padding: 28px; }

        /* ─── Step list ─── */
        .step-list { display: flex; flex-direction: column; gap: 22px; }
        .step-item { display: flex; gap: 16px; align-items: flex-start; }
        .step-num {
            width: 30px; height: 30px; border-radius: 50%;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; flex-shrink: 0; margin-top: 0;
        }
        .step-content { flex: 1; min-width: 0; }
        .step-content h4 { font-size: 14px; font-weight: 600; color: var(--text-dark); margin-bottom: 4px; }
        .step-content p  { font-size: 13px; color: var(--text-muted); line-height: 1.6; }

        /* ─── Status cards (replaces table for better readability) ─── */
        .status-cards { display: flex; flex-direction: column; gap: 12px; }
        .status-card-item {
            display: flex; gap: 14px; align-items: flex-start;
            padding: 16px 18px; border-radius: 10px;
            border: 1px solid var(--border); background: #fafbfc;
            transition: box-shadow 0.15s;
        }
        .status-card-item:hover { box-shadow: var(--shadow-sm); }
        .status-card-badge { flex-shrink: 0; padding-top: 2px; }
        .status-card-body { flex: 1; min-width: 0; }
        .status-card-body h4 { font-size: 13px; font-weight: 600; color: var(--text-dark); margin-bottom: 3px; }
        .status-card-body p  { font-size: 12px; color: var(--text-muted); line-height: 1.5; margin: 0; }
        .status-card-body .action-note {
            display: inline-block; margin-top: 5px; font-size: 11px;
            font-weight: 600; color: var(--primary); text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .status-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 12px; border-radius: 6px;
            font-size: 11px; font-weight: 600; white-space: nowrap;
        }
        .st-submitted  { background: #eff6ff; color: #1d4ed8; }
        .st-in_review  { background: #fef9c3; color: #854d0e; }
        .st-completed  { background: #f0fdf4; color: #15803d; }
        .st-returned   { background: #fee2e2; color: #b91c1c; }
        .st-archived   { background: #f1f5f9; color: #64748b; }

        /* ─── Flow diagram ─── */
        .flow-diagram {
            display: flex; align-items: center; justify-content: center;
            gap: 0; padding: 20px 0 8px; flex-wrap: wrap;
        }
        .flow-step {
            display: flex; align-items: center; gap: 0;
        }
        .flow-label {
            padding: 6px 16px; border-radius: 8px; font-size: 12px; font-weight: 600;
        }
        .flow-arrow {
            color: #cbd5e1; font-size: 14px; margin: 0 8px; flex-shrink: 0;
        }

        /* ─── FAQ ─── */
        .faq-list { display: flex; flex-direction: column; gap: 0; }
        .faq-item { border-bottom: 1px solid var(--border); }
        .faq-item:last-child { border-bottom: none; }
        .faq-q {
            display: flex; justify-content: space-between; align-items: center;
            padding: 16px 0; gap: 12px; cursor: pointer;
        }
        .faq-q span { font-size: 13px; font-weight: 500; color: var(--text-dark); line-height: 1.4; }
        .faq-q i { color: var(--text-muted); font-size: 11px; flex-shrink: 0; transition: transform 0.2s; }
        .faq-item.open .faq-q i { transform: rotate(180deg); }
        .faq-a {
            display: none; padding: 0 0 16px; font-size: 13px;
            color: var(--text-muted); line-height: 1.65;
        }
        .faq-item.open .faq-a { display: block; }

        /* ─── Contact ─── */
        .contact-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .contact-card {
            display: flex; align-items: flex-start; gap: 14px;
            padding: 16px; border-radius: 10px;
            border: 1px solid var(--border); background: #fafbfc;
        }
        .contact-icon {
            width: 36px; height: 36px; border-radius: 8px;
            background: #eff6ff; color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; flex-shrink: 0;
        }
        .contact-label { font-size: 10px; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
        .contact-value { font-size: 13px; color: var(--text-dark); font-weight: 500; word-break: break-word; }
        .contact-sub   { font-size: 11px; color: var(--text-muted); margin-top: 2px; }


        /* ─── Footer ─── */
        .dash-footer {
            background: #fff; border-top: 1px solid var(--border);
            padding: 18px 5%; display: flex;
            justify-content: space-between; align-items: center;
            margin-top: auto;
        }
        .footer-left { display: flex; align-items: center; gap: 6px; }
        .footer-left span { font-size: 12px; color: #94a3b8; }
        .footer-right { font-size: 11px; color: #b0b8c4; }

        /* ─── Responsive ─── */
        @media (max-width: 640px) {
            .dash-wrapper { padding: 20px 16px 40px; }
            .page-header { flex-direction: column; align-items: flex-start; gap: 10px; }
            .page-header h1 { font-size: 20px; }
            .back-link { font-size: 12px; padding: 6px 12px; }
            .toc-card { padding: 14px 16px; }
            .toc-list { gap: 6px; }
            .toc-item { font-size: 11px; padding: 6px 10px; gap: 5px; }
            .help-section-head { padding: 16px 18px; gap: 12px; }
            .help-section-icon { width: 34px; height: 34px; font-size: 15px; }
            .help-section-head h2 { font-size: 15px; }
            .help-section-body { padding: 20px 18px; }
            .step-list { gap: 18px; }
            .step-item { gap: 12px; }
            .step-num { width: 26px; height: 26px; font-size: 12px; }
            .step-content h4 { font-size: 13px; }
            .step-content p { font-size: 12px; }
            .status-card-item { padding: 14px 16px; gap: 12px; }
            .status-card-body h4 { font-size: 12px; }
            .status-card-body p { font-size: 11px; }
            .status-badge { font-size: 10px; padding: 3px 10px; }
            .flow-label { font-size: 11px; padding: 5px 12px; }
            .flow-arrow { font-size: 12px; margin: 0 4px; }
            .faq-q span { font-size: 12px; }
            .faq-a { font-size: 12px; }
            .contact-grid { grid-template-columns: 1fr; }
            .contact-card { padding: 14px; }
            .dash-footer { flex-direction: column; gap: 6px; text-align: center; padding: 16px 5%; }
        }

        /* ─── Anim ─── */
        .anim { animation: fadeUp 0.3s ease both; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
    </style>
    <script src="/js/spa.js" defer></script>
</head>
<body>

    @php
        $isAdminUser = $user->isAdmin();
        $isSuperAdminUser = $user->isSuperAdmin();
        $isRep = ($user->account_type ?? '') === 'representative';
        $isOfficeUser = $isRep && $user->office_id && !$isAdminUser;
        $isSelfRepUser = $isRep && !$user->office_id && !$isAdminUser;

        $navOfficeName = $isRep ? $user->representativeOfficeName() : '';
        $navRepName = $isRep ? $user->representativeDisplayName() : '';

        $displayFirst = explode(' ', trim(($navRepName ?: $user->name) ?? 'User'))[0] ?? 'User';
        $officeLabel = $navOfficeName ?: 'Office';
        $officePerson = $navRepName ?: $displayFirst;
        $navDisplayRole = $isAdminUser ? ($isSuperAdminUser ? 'Super Admin' : 'Admin') : ucfirst($user->role ?? 'User');
        $helpBackUrl = $isOfficeUser ? '/office/dashboard' : '/dashboard';

        $sidebarNameSource = $isOfficeUser ? $officePerson : ($isSelfRepUser ? ($navRepName ?: $displayFirst) : $displayFirst);
        $sidebarInitials = '';
        foreach (preg_split('/\s+/', trim($sidebarNameSource)) as $part) {
            if ($part === '') {
                continue;
            }
            $sidebarInitials .= strtoupper(substr($part, 0, 1));
            if (strlen($sidebarInitials) >= 2) {
                break;
            }
        }
        $sidebarInitials = $sidebarInitials ?: strtoupper(substr($displayFirst, 0, 1));
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
        @if($isAdminUser)
        <span class="nav-section">Overview</span>
        <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <span class="nav-section">Management</span>
        <a href="/admin/users"><i class="fas fa-users"></i> Users</a>
        <a href="/admin/offices"><i class="fas fa-building"></i> Offices</a>
        @unless($isSuperAdminUser)
        <a href="/admin/documents"><i class="fas fa-folder-open"></i> Documents</a>
        @endunless
        @if($isSuperAdminUser)
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
        <a href="/help" class="active"><i class="fas fa-circle-question"></i> Help</a>
        @elseif($isOfficeUser)
        <span class="nav-section">Office</span>
        <a href="/office/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/office/search" style="{{ $user->hasReportsAccess() ? '' : 'display:none' }}"><i class="fas fa-chart-line"></i> Reports</a>
        @if($user->isRecords())
        <span class="nav-section">Records Section</span>
        <a href="/records/documents"><i class="fas fa-folder-open"></i> All Documents</a>
        @endif
        <span class="nav-section">My Documents</span>
        <a href="/submit"><i class="fas fa-paper-plane"></i> Submit Document</a>
        <a href="/my-documents"><i class="fas fa-folder"></i> My Documents</a>
        <span class="nav-section">Account</span>
        <a href="/profile"><i class="fas fa-user-cog"></i> My Profile</a>
        <a href="/help" class="active"><i class="fas fa-circle-question"></i> Help</a>
        @else
        <span class="nav-section">Overview</span>
        <a href="/dashboard"><i class="fas fa-th-large"></i> Dashboard</a>
        <span class="nav-section">Documents</span>
        <a href="/submit"><i class="fas fa-paper-plane"></i> Submit Document</a>
        <a href="/my-documents"><i class="fas fa-folder-open"></i> My Documents</a>
        <a href="/track"><i class="fas fa-search"></i> Track Document</a>
        <span class="nav-section">Account</span>
        <a href="/profile"><i class="fas fa-user-cog"></i> My Profile</a>
        <a href="/help" class="active"><i class="fas fa-circle-question"></i> Help</a>
        @endif
    </nav>
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">{{ $sidebarInitials }}</div>
            <div class="sb-user-info">
                @if($isOfficeUser)
                <small>{{ $officeLabel }}</small>
                <span>{{ $officePerson }}</span>
                @elseif($isSelfRepUser)
                <small>{{ $navRepName ?: 'Representative' }}</small>
                <span>{{ $navOfficeName ?: 'Representative' }}</span>
                @else
                <small>{{ $navDisplayRole }}</small>
                <span>{{ $displayFirst }}</span>
                @endif
            </div>
        </div>
        <button onclick="logout()" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>
</div>

<!-- ─── Main Content ─── -->
<div class="main">

    <!-- ─── Content ─── -->
    <div class="dash-wrapper">

        <div class="page-header anim">
            <div>
                <h1>Help &amp; Guide</h1>
                <p>Everything you need to know about using DOCTRAX</p>
            </div>
            @if(request()->query('from') === 'profile')
            <a href="/profile" class="back-link" aria-label="Back to Profile" title="Back to Profile" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;"><span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></span></a>
            @else
            <a href="{{ $helpBackUrl }}" class="back-link" aria-label="Back to Dashboard" title="Back to Dashboard" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;"><span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></span></a>
            @endif
        </div>

        <!-- Quick nav -->
        <div class="toc-card anim">
            <h3><i class="fas fa-list"></i>&ensp;Jump to Section</h3>
            <div class="toc-list">
                <a href="#submit"  class="toc-item"><i class="fas fa-file-upload"></i> Submitting Documents</a>
                <a href="#track"   class="toc-item"><i class="fas fa-search"></i> Tracking a Document</a>
                <a href="#status"  class="toc-item"><i class="fas fa-tags"></i> Document Statuses</a>
                <a href="#account" class="toc-item"><i class="fas fa-user-cog"></i> Account &amp; Profile</a>
                <a href="#faq"     class="toc-item"><i class="fas fa-question-circle"></i> FAQs</a>
                <a href="#contact" class="toc-item"><i class="fas fa-envelope"></i> Contact</a>
            </div>
        </div>

        <!-- ─── 1. Submitting Documents ─── -->
        <div class="help-section anim" id="submit">
            <div class="help-section-head">
                <div class="help-section-icon">
                    <i class="fas fa-file-upload"></i>
                </div>
                <div>
                    <h2>Submitting a Document</h2>
                    <p>How to send a document to the DepEd Division Office</p>
                </div>
            </div>
            <div class="help-section-body">
                <div class="step-list">
                    <div class="step-item">
                        <div class="step-num">1</div>
                        <div class="step-content">
                            <h4>Go to Submit Document</h4>
                            <p>From your dashboard, click the <strong>Submit Document</strong> quick action, or navigate directly to <strong>/submit</strong>.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-num">2</div>
                        <div class="step-content">
                            <h4>Fill in the document details</h4>
                            <p>Select a <strong>Document Type</strong> from the dropdown (e.g. Transcript of Records, Service Record, Memorandum, etc.), enter the <strong>Subject / Title</strong>, and optionally add any <strong>Additional Remarks</strong>. All starred fields are required. All submissions are automatically routed to the <strong>Records Section</strong> first.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-num">3</div>
                        <div class="step-content">
                            <h4>Submit and save your tracking number</h4>
                            <p>Click <strong>Submit Document</strong>. A unique 8-character tracking number (e.g., <strong>A7K3M9PB</strong>) will be generated and shown on screen. Copy or note it down — you will need it to track your document later. The tracking number is also shown in your <strong>My Documents</strong> list.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-num">4</div>
                        <div class="step-content">
                            <h4>Physically deliver the document</h4>
                            <p>Bring the physical copy to the DepEd Division Office. The office representative will accept and process it in the system, which will update your document's status automatically.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── 2. Tracking a Document ─── -->
        <div class="help-section anim" id="track">
            <div class="help-section-head">
                <div class="help-section-icon">
                    <i class="fas fa-search"></i>
                </div>
                <div>
                    <h2>Tracking a Document</h2>
                    <p>Check where your document currently is in the process</p>
                </div>
            </div>
            <div class="help-section-body">
                <div class="step-list">
                    <div class="step-item">
                        <div class="step-num">1</div>
                        <div class="step-content">
                            <h4>Option A — My Documents (logged in)</h4>
                            <p>Click <strong>My Documents</strong> in the navigation bar. All documents you have submitted are listed with their current status and the office handling them.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-num">2</div>
                        <div class="step-content">
                            <h4>Option B — Public Track page</h4>
                            <p>Go to <strong>/track</strong> (no login required). Enter your <strong>8-character tracking number</strong> exactly as it appears (e.g., <em>A7K3M9PB</em>) and click <strong>Track Document</strong>. You will see the full routing history. The tracking number is case-insensitive.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-num">3</div>
                        <div class="step-content">
                            <h4>Read the routing log</h4>
                            <p>The track result shows a routing log timeline — each entry shows which office handled the document, what action was taken, and when it happened.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── 3. Document Statuses ─── -->
        <div class="help-section anim" id="status">
            <div class="help-section-head">
                <div class="help-section-icon">
                    <i class="fas fa-tags"></i>
                </div>
                <div>
                    <h2>Document Statuses Explained</h2>
                    <p>What each status label means for your document</p>
                </div>
            </div>
            <div class="help-section-body">
                <div class="flow-diagram">
                    <div class="flow-step">
                        <span class="flow-label st-submitted">Submitted</span>
                    </div>
                    <i class="fas fa-chevron-right flow-arrow"></i>
                    <div class="flow-step">
                        <span class="flow-label st-in_review">Processing</span>
                    </div>
                    <i class="fas fa-chevron-right flow-arrow"></i>
                    <div class="flow-step">
                        <span class="flow-label st-completed">Completed</span>
                    </div>
                </div>

                <div class="status-cards">
                    <div class="status-card-item">
                        <div class="status-card-badge"><span class="status-badge st-submitted"><i class="fas fa-circle" style="font-size:6px"></i> Submitted</span></div>
                        <div class="status-card-body">
                            <h4>Awaiting acceptance</h4>
                            <p>Your document has been registered in the system and is awaiting acceptance by the receiving office.</p>
                            <span class="action-note"><i class="fas fa-hand-point-right" style="margin-right:4px"></i> Deliver the physical document to the office</span>
                        </div>
                    </div>
                    <div class="status-card-item">
                        <div class="status-card-badge"><span class="status-badge st-in_review"><i class="fas fa-circle" style="font-size:6px"></i> Processing</span></div>
                        <div class="status-card-body">
                            <h4>Being processed</h4>
                            <p>The office has received your document and is actively processing it. Remarks may be added by the office during this step.</p>
                            <span class="action-note"><i class="fas fa-clock" style="margin-right:4px"></i> No action needed — office is handling it</span>
                        </div>
                    </div>
                    <div class="status-card-item">
                        <div class="status-card-badge"><span class="status-badge st-completed"><i class="fas fa-circle" style="font-size:6px"></i> Completed</span></div>
                        <div class="status-card-body">
                            <h4>Transaction complete</h4>
                            <p>Processing is finished. The document has been officially released back to the requesting party.</p>
                            <span class="action-note"><i class="fas fa-check" style="margin-right:4px"></i> Done — no further action</span>
                        </div>
                    </div>
                    <div class="status-card-item">
                        <div class="status-card-badge"><span class="status-badge st-returned"><i class="fas fa-circle" style="font-size:6px"></i> Returned</span></div>
                        <div class="status-card-body">
                            <h4>Cancelled with revision</h4>
                            <p>Sent back for corrections or missing requirements. You will need to fix the issues and resubmit.</p>
                            <span class="action-note"><i class="fas fa-redo" style="margin-right:4px"></i> Check remarks, correct, and resubmit</span>
                        </div>
                    </div>
                    <div class="status-card-item">
                        <div class="status-card-badge"><span class="status-badge st-archived"><i class="fas fa-circle" style="font-size:6px"></i> Archived</span></div>
                        <div class="status-card-body">
                            <h4>Auto-archived</h4>
                            <p>The physical copy was not delivered to the office within the required time period, so the document was automatically archived.</p>
                            <span class="action-note"><i class="fas fa-plus" style="margin-right:4px"></i> Submit a new document if still needed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── 4. Account & Profile ─── -->
        <div class="help-section anim" id="account">
            <div class="help-section-head">
                <div class="help-section-icon">
                    <i class="fas fa-user-cog"></i>
                </div>
                <div>
                    <h2>Account &amp; Profile</h2>
                    <p>Managing your personal information and password</p>
                </div>
            </div>
            <div class="help-section-body">
                <div class="step-list">
                    <div class="step-item">
                        <div class="step-num">1</div>
                        <div class="step-content">
                            <h4>Updating your name and contact number</h4>
                            <p>Go to <strong>My Profile</strong> from the sidebar. Edit your <strong>First Name</strong>, <strong>Middle Name</strong>, <strong>Last Name</strong>, <strong>Email</strong>, and <strong>Mobile Number</strong>, then click <strong>Save Changes</strong>.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-num">2</div>
                        <div class="step-content">
                            <h4>Changing your password</h4>
                            <p>On the same <strong>My Profile</strong> page, scroll to the <strong>Change Password</strong> section. Enter your <strong>current password</strong>, then your <strong>new password</strong> (min. 8 characters, must include uppercase, lowercase, and a number). Confirm and click <strong>Change Password</strong>.</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-num">3</div>
                        <div class="step-content">
                            <h4>Forgot your password?</h4>
                            <p>On the <strong>Login</strong> page, click the <strong>Forgot password?</strong> link below the password field. Enter your registered email address and a password reset link will be sent to your inbox (valid for 60 minutes).</p>
                        </div>
                    </div>
                    <div class="step-item">
                        <div class="step-num">4</div>
                        <div class="step-content">
                            <h4>Account activation</h4>
                            <p>New accounts require email verification. After registering, check your inbox for an <strong>Account Activation</strong> email and click the link to activate your account before logging in. Check your spam or junk folder if you don't see the email within a few minutes.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ─── 5. FAQ ─── -->
        <div class="help-section anim" id="faq">
            <div class="help-section-head">
                <div class="help-section-icon">
                    <i class="fas fa-question-circle"></i>
                </div>
                <div>
                    <h2>Frequently Asked Questions</h2>
                    <p>Common questions and their answers</p>
                </div>
            </div>
            <div class="help-section-body">
                <div class="faq-list">

                    <div class="faq-item">
                        <div class="faq-q" onclick="toggleFaq(this)">
                            <span>Do I need to create an account to track a document?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-a">No. You can use the public <strong>Track Document</strong> page at <strong>/track</strong> without logging in. You only need the 8-character tracking number that was given to you when the document was submitted.</div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-q" onclick="toggleFaq(this)">
                            <span>Where can I find my tracking number?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-a">Your tracking number is displayed immediately after you submit a document through the system. It is also listed in <strong>My Documents</strong> if you submitted while logged in. If you submitted manually at the office, ask the office representative for your tracking slip.</div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-q" onclick="toggleFaq(this)">
                            <span>Can I edit or cancel a document I already submitted?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-a"><strong>Editing</strong> a submitted document is not supported. Documents that remain unprocessed (not received by any office) for <strong>7 days</strong> are automatically archived by the system. If you need to cancel a document before that, please contact the DepEd Division Office directly.</div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-q" onclick="toggleFaq(this)">
                            <span>How long does document processing take?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-a">Processing time depends on the document type and workload of the Division Office. You can monitor real-time status changes through the system. For urgent matters, contact the office directly.</div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-q" onclick="toggleFaq(this)">
                            <span>I didn't receive my activation email. What should I do?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-a">Check your <strong>spam or junk mail</strong> folder first. If it is not there, contact the system administrator to manually activate your account or resend the activation link.</div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-q" onclick="toggleFaq(this)">
                            <span>Can I submit multiple documents at once?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-a">Each submission covers one document entry. For multiple documents, please submit them one at a time so each receives its own unique tracking number and can be monitored individually.</div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-q" onclick="toggleFaq(this)">
                            <span>My document status hasn't changed in days. What do I do?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-a">Status updates are made by office representatives when they take action on a document. If there has been no update for an extended period, contact the DepEd Division Office directly using the contact information below.</div>
                    </div>

                    <div class="faq-item">
                        <div class="faq-q" onclick="toggleFaq(this)">
                            <span>Is my personal information secure?</span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-a">Yes. DOCTRAX stores data on secure servers. Passwords are encrypted and never stored in plain text. Only you and authorized DepEd personnel can access your document information.</div>
                    </div>

                </div>
            </div>
        </div>

        <!-- ─── 6. Contact ─── -->
        <div class="help-section anim" id="contact">
            <div class="help-section-head">
                <div class="help-section-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <h2>Contact &amp; Support</h2>
                    <p>Reach the DepEd Division Office for assistance</p>
                </div>
            </div>
            <div class="help-section-body">
                <div class="contact-grid">
                    <div class="contact-card">
                        <div class="contact-icon"><i class="fas fa-map-marker-alt"></i></div>
                        <div>
                            <div class="contact-label">Address</div>
                            <div class="contact-value">DepEd Division Office</div>
                            <div class="contact-sub">Schools Division of City of San Jose del Monte</div>
                        </div>
                    </div>
                    <div class="contact-card">
                        <div class="contact-icon"><i class="fas fa-phone"></i></div>
                        <div>
                            <div class="contact-label">Phone</div>
                            <div class="contact-value">Contact number available at the Division Office</div>
                            <div class="contact-sub">Mon – Fri, 8:00 AM – 4:00 PM</div>
                        </div>
                    </div>
                    <div class="contact-card">
                        <div class="contact-icon"><i class="fas fa-envelope"></i></div>
                        <div>
                            <div class="contact-label">Email</div>
                            <div class="contact-value">arthur.francisco@deped.gov.ph</div>
                            <div class="contact-sub">For document-related concerns</div>
                        </div>
                    </div>
                    <div class="contact-card">
                        <div class="contact-icon"><i class="fas fa-clock"></i></div>
                        <div>
                            <div class="contact-label">Office Hours</div>
                            <div class="contact-value">Monday to Friday</div>
                            <div class="contact-sub">8:00 AM – 4:00 PM</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ─── Footer ─── -->
    <footer class="dash-footer">
        <div class="footer-left">
            <span>&copy; {{ date('Y') }} DepEd Document Tracking System</span>
        </div>
        <div class="footer-right">Developed by Raymond Bautista</div>
    </footer>

</div><!-- /.main -->

<script>
(function() {
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ─── Sidebar toggle ───
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
        fetch('/api/logout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        }).then(function() { window.location.href = '/login'; })
          .catch(function() { window.location.href = '/login'; });
    };

    // ─── FAQ accordion ───
    window.toggleFaq = function(el) {
        var item = el.closest('.faq-item');
        var wasOpen = item.classList.contains('open');
        // Close all
        document.querySelectorAll('.faq-item.open').forEach(function(i) { i.classList.remove('open'); });
        // Toggle current
        if (!wasOpen) item.classList.add('open');
    };

    // ─── Smooth scroll for TOC links ───
    function getHelpScrollOffset() {
        return window.innerWidth <= 900 ? 88 : 28;
    }

    function scrollToHelpSection(hash, updateHash) {
        if (!hash || hash.charAt(0) !== '#') return false;

        var target = document.querySelector(hash);
        if (!target) return false;

        var top = target.getBoundingClientRect().top + window.pageYOffset - getHelpScrollOffset();
        window.scrollTo({
            top: Math.max(0, top),
            behavior: 'smooth'
        });

        if (updateHash) {
            if (history && typeof history.replaceState === 'function') {
                history.replaceState(history.state, '', hash);
            } else {
                window.location.hash = hash;
            }
        }

        return true;
    }

    document.querySelectorAll('.toc-item').forEach(function(a) {
        a.addEventListener('click', function(e) {
            var hash = this.getAttribute('href');
            if (scrollToHelpSection(hash, true)) {
                e.preventDefault();
            }
        });
    });

    if (window.location.hash) {
        setTimeout(function() {
            scrollToHelpSection(window.location.hash, false);
        }, 60);
    }

    window.addEventListener('hashchange', function() {
        scrollToHelpSection(window.location.hash, false);
    });
})();
</script>
</body>
</html>
