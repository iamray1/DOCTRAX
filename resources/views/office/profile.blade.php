<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Account Settings - DepEd DOCTRAX</title>
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
        .dash-wrapper { max-width: 800px; width: 100%; margin: 0 auto; padding: 28px 24px 48px; flex: 1; }

        .page-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 28px;
        }
        .page-header h1 { font-size: 22px; font-weight: 600; color: var(--text-dark); }
        .page-header p { font-size: 14px; color: var(--text-muted); font-weight: 400; }

        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; color: var(--primary); text-decoration: none; font-weight: 500;
            padding: 7px 14px; border-radius: 8px; border: 1px solid var(--border);
            background: var(--white); transition: all 0.15s;
        }
        .back-link:hover { background: #f8fafc; border-color: var(--primary); }

        /* ─── Profile Card ─── */
        .profile-card {
            background: var(--white);
            border-radius: 10px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .profile-header {
            background: var(--primary-gradient);
            padding: 28px 28px 24px;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .profile-avatar {
            width: 64px; height: 64px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 24px;
            flex-shrink: 0;
            border: 3px solid rgba(255,255,255,0.3);
        }

        .profile-meta h2 {
            color: #fff; font-size: 18px; font-weight: 600; margin-bottom: 2px;
        }
        .profile-meta p {
            color: rgba(255,255,255,0.8); font-size: 13px;
        }
        .profile-meta .role-badge {
            display: inline-block;
            margin-top: 6px;
            padding: 2px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            background: rgba(255,255,255,0.2);
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .profile-info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0;
        }

        .info-item {
            padding: 16px 28px;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-item:nth-child(odd) { border-right: 1px solid #f1f5f9; }

        .info-label {
            font-size: 11px; font-weight: 600; color: #94a3b8;
            text-transform: uppercase; letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 14px; color: var(--text-dark); font-weight: 500;
        }

        /* ─── Section Panel ─── */
        .section-panel {
            background: var(--white);
            border-radius: 10px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .section-head {
            padding: 16px 28px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-title {
            font-size: 15px; font-weight: 600; color: var(--text-dark);
            display: flex; align-items: center; gap: 8px;
        }

        .section-title i { color: var(--primary); font-size: 14px; }

        .section-body { padding: 24px 28px; }

        /* ─── Info Links ─── */
        .info-links-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
        .info-link-tile { display: flex; flex-direction: column; align-items: center; gap: 8px; padding: 20px 12px; border-radius: 12px; border: 1.5px solid var(--border); background: #f8fafc; text-decoration: none; color: var(--text-dark); font-size: 13px; font-weight: 600; transition: all .2s; }
        .info-link-tile i { font-size: 20px; color: var(--primary); }
        .info-link-tile:hover { border-color: var(--primary); background: #eef4fb; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,86,179,.1); }

        /* ─── Form ─── */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .form-row.full { grid-template-columns: 1fr; }

        .form-group { display: flex; flex-direction: column; }

        .form-group label {
            font-size: 12px; font-weight: 600; color: var(--text-muted);
            margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.3px;
        }

        .form-group input {
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            color: var(--text-dark);
            outline: none;
            transition: border-color 0.15s;
            background: var(--white);
        }
        .form-group input:focus { border-color: var(--primary); }

        .form-group input.error { border-color: #dc2626; background: #fef2f2; }

        .field-err {
            background: #fef2f2;
            border-left: 4px solid #dc2626;
            color: #dc2626;
            padding: 9px 12px;
            border-radius: 6px;
            font-size: 13px;
            display: none;
            align-items: center;
            gap: 8px;
            margin-top: 6px;
            animation: errIn .2s ease;
        }
        .field-err.show { display: flex; }
        .field-err i { font-size: 14px; flex-shrink: 0; }
        @keyframes errIn { from { opacity: 0; transform: translateY(-3px); } to { opacity: 1; transform: translateY(0); } }
        .form-hint { font-size: 11px; color: #94a3b8; margin-top: 4px; }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 8px;
        }

        .btn-save {
            padding: 10px 24px;
            background: var(--primary);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: flex; align-items: center; gap: 6px;
            transition: background 0.15s;
        }
        .btn-save:hover { background: var(--primary-dark); }
        .btn-save:disabled { opacity: 0.6; cursor: not-allowed; }

        .btn-cancel {
            padding: 10px 20px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 8px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.15s;
        }
        .btn-cancel:hover { background: #f8fafc; color: var(--text-dark); }

        /* ─── Toast ─── */
        .toast {
            position: fixed; top: 24px; right: 24px; z-index: 300;
            background: var(--white); border: 1px solid var(--border);
            border-radius: 10px; padding: 14px 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            font-size: 13px; font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            display: flex; align-items: center; gap: 8px;
            min-width: 240px;
            transform: translateY(-20px) translateX(120%); transition: transform 0.3s ease;
        }
        .toast.show { transform: translateY(0) translateX(0); }
        .toast.success { border-left: 3px solid #16a34a; }
        .toast.error { border-left: 3px solid #dc2626; }
        .toast-icon { font-size: 16px; }
        .toast.success .toast-icon { color: #16a34a; }
        .toast.error .toast-icon { color: #dc2626; }

        /* ─── Password Strength ─── */
        .pw-strength {
            margin-top: 6px;
            height: 3px;
            border-radius: 3px;
            background: #e2e8f0;
            overflow: hidden;
        }

        .pw-strength-bar {
            height: 100%;
            border-radius: 3px;
            width: 0;
            transition: width 0.3s, background 0.3s;
        }

        .pw-hint {
            font-size: 11px; color: #94a3b8; margin-top: 4px;
        }

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
        }
        .footer-left { display: flex; align-items: center; gap: 6px; }
        .footer-right { font-size: 11px; color: #b0b8c4; }

        /* ─── Animations ─── */
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .anim { animation: fadeIn 0.25s ease forwards; }

        /* ─── Responsive ─── */
        @media (max-width: 900px) {
            .dash-wrapper { padding: 20px 16px 40px; }
            .dash-footer { flex-direction: column; gap: 6px; text-align: center; padding: 16px 5%; }
        }
        @media (max-width: 768px) {
            .page-header { flex-direction: column; align-items: flex-start; gap: 12px; }
            .profile-info-grid { grid-template-columns: 1fr; }
            .info-item:nth-child(odd) { border-right: none; }
            .form-row { grid-template-columns: 1fr; }
            .profile-header { padding: 22px 20px 18px; }
            .section-body { padding: 20px; }
            .section-head { padding: 14px 20px; }
            .info-links-grid { grid-template-columns: 1fr; }
        }
    </style>
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
</head>
<body>

@php
    $isRep = ($user->account_type ?? '') === 'representative';
    $navOfficeName = $isRep ? ($user->representativeOfficeName() ?? 'Office') : '';
    $navRepName = $isRep ? $user->representativeDisplayName() : $user->name;
    $repParts  = explode(' ', trim($navRepName));
    $repFirst  = count($repParts) ? array_shift($repParts) : '';
    $repLast   = count($repParts) ? array_pop($repParts)   : '';
    $repMiddle = implode(' ', $repParts);
    $hasMiddle = $repMiddle !== '';
    $navDisplayName = $isRep ? $navOfficeName : $navRepName;
    $navDisplayRole = $isRep ? 'Office' : ucfirst($user->role ?? 'User');
    $initials = collect(explode(' ', trim($navRepName ?: $user->name)))->filter()->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->implode('');
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
        <span class="nav-section">Office</span>
        <a href="/office/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/office/search" id="reports-nav-link" style="{{ $user->hasReportsAccess() ? '' : 'display:none' }}"><i class="fas fa-chart-line"></i> Reports</a>
        @if($user->isRecords() || $user->isSuperAdmin())
        <span class="nav-section">Records Section</span>
        <a href="/records/documents"><i class="fas fa-folder-open"></i> All Documents</a>
        @endif
        <span class="nav-section">My Documents</span>
        <a href="/submit"><i class="fas fa-paper-plane"></i> Submit Document</a>
        <a href="/my-documents"><i class="fas fa-folder"></i> My Documents</a>
        <span class="nav-section">Account</span>
        <a href="/profile" class="active"><i class="fas fa-user-cog"></i> My Profile</a>
    </nav>
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">{{ $initials }}</div>
            <div class="sb-user-info">
                <small>{{ $navOfficeName ?: 'Office' }}</small>
                <span>{{ $navRepName ?: $navDisplayName }}</span>
            </div>
        </div>
        <button onclick="logout()" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>
</div>

<!-- ─── Main ─── -->
<div class="main">
<div class="dash-wrapper">

    <div class="page-header anim">
        <div>
            <h1>Account Settings</h1>
            <p>Manage your personal information and password</p>
        </div>
        <a href="/office/dashboard" class="back-link" aria-label="Back to Dashboard" title="Back to Dashboard" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;"><span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></span></a>
    </div>

    <!-- Profile Card -->
    <div class="profile-card anim">
        <div class="profile-header">
            <div class="profile-avatar" id="profileAvatar">{{ $initials }}</div>
            <div class="profile-meta">
                @if($isRep && $navOfficeName)
                    <h2 id="profileName">{{ $navOfficeName }}</h2>
                    @if($navRepName)
                        <p><i class="fas fa-user" style="margin-right:4px"></i>{{ $navRepName }}</p>
                    @endif
                @else
                    <h2 id="profileName">{{ $user->name }}</h2>
                @endif
                <p><!--email_off-->{{ $user->email }}<!--/email_off--></p>
                <span class="role-badge">{{ $navDisplayRole }}</span>
            </div>
        </div>
        <div class="profile-info-grid">
            @if($isRep && $navOfficeName)
            <div class="info-item">
                <div class="info-label">Office / Institution</div>
                <div class="info-value" id="infoName">{{ $navOfficeName }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Contact Person</div>
                <div class="info-value">{{ $navRepName ?: 'No name provided' }}</div>
            </div>
            @else
            <div class="info-item">
                <div class="info-label">Full Name</div>
                <div class="info-value" id="infoName">{{ $user->name }}</div>
            </div>
            @endif
            <div class="info-item">
                <div class="info-label">Email Address</div>
                <div class="info-value" id="infoEmail"><!--email_off-->{{ $user->email }}<!--/email_off--></div>
            </div>
            <div class="info-item">
                <div class="info-label">Mobile Number</div>
                <div class="info-value" id="infoMobile">{{ $user->mobile ?? 'No number provided' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Account Status</div>
                <div class="info-value" style="color:{{ $user->status === 'active' ? '#16a34a' : '#9a3412' }}">{{ ucfirst($user->status) }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Account Type</div>
                <div class="info-value">{{ $isRep ? 'Office Account' : 'Individual' }}</div>
            </div>
            <div class="info-item">
                <div class="info-label">Member Since</div>
                <div class="info-value">{{ $user->created_at->format('F j, Y') }}</div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Form -->
    <div class="section-panel anim">
        <div class="section-head">
            <div class="section-title"><i class="fas fa-edit"></i> Edit Profile</div>
        </div>
        <div class="section-body">
            <form id="profileForm" novalidate>
                @if($navOfficeName)
                <div class="form-row full">
                    <div class="form-group">
                        <label>Office / Institution</label>
                        <input type="text" value="{{ $navOfficeName }}" disabled style="background:#f8fafc;color:#64748b;cursor:not-allowed;">
                        <div style="font-size:11px;color:#94a3b8;margin-top:3px;">Assigned by administrator &mdash; cannot be changed here</div>
                    </div>
                </div>
                @endif
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name <span style="color:#dc2626">*</span></label>
                        <input type="text" id="firstName"
                               value="{{ $repFirst }}"
                               maxlength="100" autocomplete="off"
                               oninput="this.value=this.value.replace(/[^a-zA-Z\u00C0-\u024F\s\-\.\x27]/g,'')">
                        <div class="field-err" id="err-firstName"><i class="fas fa-exclamation-circle"></i><span></span></div>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name <span style="color:#dc2626">*</span></label>
                        <input type="text" id="lastName"
                               value="{{ $repLast }}"
                               maxlength="100" autocomplete="off"
                               oninput="this.value=this.value.replace(/[^a-zA-Z\u00C0-\u024F\s\-\.\x27]/g,'')">
                        <div class="field-err" id="err-lastName"><i class="fas fa-exclamation-circle"></i><span></span></div>
                    </div>
                </div>
                <div class="form-row full">
                    <div class="form-group">
                        <label for="middleName">
                            Middle Name
                            <span style="color:#dc2626" id="midAsterisk">{{ $hasMiddle ? '*' : '' }}</span>
                        </label>
                        <input type="text" id="middleName"
                               value="{{ $repMiddle }}"
                               maxlength="100" autocomplete="off"
                               oninput="this.value=this.value.replace(/[^a-zA-Z\u00C0-\u024F\s\-\.\x27]/g,'')"
                               {{ !$hasMiddle ? 'disabled style="opacity:.5"' : '' }}>
                        <div class="field-err" id="err-middleName"><i class="fas fa-exclamation-circle"></i><span></span></div>
                        <label style="display:flex;align-items:center;gap:6px;margin-top:8px;cursor:pointer;font-size:12px;color:#64748b;font-weight:400;user-select:none;">
                            <input type="checkbox" id="noMiddleName" onchange="toggleMiddle()"
                                   style="width:14px;height:14px;accent-color:#0056b3;cursor:pointer;"
                                   {{ !$hasMiddle ? 'checked' : '' }}>
                            I don't have a middle name
                        </label>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="email">Email Address <span style="color:#dc2626">*</span></label>
                        <input type="email" id="email"
                               value="{{ $user->email }}"
                               maxlength="255" autocomplete="email" inputmode="email" autocapitalize="none" autocorrect="off" spellcheck="false">
                        <div class="field-err" id="err-email"><i class="fas fa-exclamation-circle"></i><span></span></div>
                    </div>
                    <div class="form-group">
                        <label for="mobile">Mobile <span style="color:var(--text-muted);font-weight:400">(optional)</span></label>
                        <input type="text" id="mobile"
                               value="{{ $user->mobile ?? '' }}"
                               placeholder="09XXXXXXXXX"
                               maxlength="11" inputmode="numeric" autocomplete="off"
                               oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,11)">
                        <div class="field-err" id="err-mobile"><i class="fas fa-exclamation-circle"></i><span></span></div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-save" id="btnSaveProfile" data-no-auto-loading>
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Password -->
    <div class="section-panel anim">
        <div class="section-head">
            <div class="section-title"><i class="fas fa-lock"></i> Change Password</div>
        </div>
        <div class="section-body">
            <form id="passwordForm" novalidate>
                <div class="form-row full">
                    <div class="form-group">
                        <label for="current_password">Current Password <span style="color:#dc2626">*</span></label>
                        <input type="password" id="current_password" autocomplete="current-password">
                        <div class="field-err" id="err-current_password"><i class="fas fa-exclamation-circle"></i><span></span></div>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="password">New Password <span style="color:#dc2626">*</span></label>
                        <input type="password" id="password" autocomplete="new-password">
                        <div class="pw-strength"><div class="pw-strength-bar" id="pwBar"></div></div>
                        <div class="pw-hint" id="pwHint">Min 8 chars, uppercase, lowercase, number</div>
                        <div class="field-err" id="err-password"><i class="fas fa-exclamation-circle"></i><span></span></div>
                    </div>
                    <div class="form-group">
                        <label for="password_confirmation">Confirm New Password <span style="color:#dc2626">*</span></label>
                        <input type="password" id="password_confirmation" autocomplete="new-password">
                        <div class="field-err" id="err-password_confirmation"><i class="fas fa-exclamation-circle"></i><span></span></div>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-save" id="btnChangePw" data-no-auto-loading>
                        <i class="fas fa-key"></i> Change Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Information Links -->
    <div class="section-panel anim">
        <div class="section-head">
            <div class="section-title"><i class="fas fa-info-circle"></i> Information</div>
        </div>
        <div class="section-body">
            <div class="info-links-grid">
                <a href="/help?from=profile" class="info-link-tile">
                    <i class="fas fa-question-circle"></i>
                    <span>Help</span>
                </a>
                <a href="/about-us?from=profile" class="info-link-tile">
                    <i class="fas fa-info-circle"></i>
                    <span>About Us</span>
                </a>
                <a href="/contact-us?from=profile" class="info-link-tile">
                    <i class="fas fa-envelope"></i>
                    <span>Contact Us</span>
                </a>
            </div>
        </div>
    </div>

</div><!-- end dash-wrapper -->

<!-- Toast -->
<div class="toast" id="toast">
    <i class="fas toast-icon" id="toastIcon"></i>
    <span id="toastMsg"></span>
</div>

<footer class="dash-footer">
    <div class="footer-left">
        <span>&copy; {{ date('Y') }} DepEd Document Tracking System</span>
    </div>
    <div class="footer-right">Developed by Raymond Bautista</div>
</footer>

</div><!-- end .main -->

<script>
(function() {
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var officeName = @json($navOfficeName);

    // ─── Toast ───
    function showToast(msg, type) {
        var toast = document.getElementById('toast');
        var icon  = document.getElementById('toastIcon');
        document.getElementById('toastMsg').textContent = msg;
        toast.className = 'toast ' + type + ' show';
        icon.className = 'fas toast-icon ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle');
        setTimeout(function() { toast.classList.remove('show'); }, 3200);
    }

    // ─── Error helpers ───
    function clearErrors() {
        document.querySelectorAll('[id^="err-"]').forEach(function(el) {
            var sp = el.querySelector('span'); if (sp) sp.textContent = '';
            el.classList.remove('show');
        });
        document.querySelectorAll('input.error').forEach(function(el) { el.classList.remove('error'); });
    }

    function setErr(id, msg) {
        var el  = document.getElementById('err-' + id);
        var inp = document.getElementById(id);
        if (el) { var sp = el.querySelector('span'); if (sp) sp.textContent = msg; el.classList.add('show'); }
        if (inp) inp.classList.add('error');
    }

    function showFieldErrors(errors) {
        for (var field in errors) {
            setErr(field, errors[field][0]);
        }
    }

    // ─── Toggle Middle Name ───
    window.toggleMiddle = function() {
        var cb    = document.getElementById('noMiddleName');
        var input = document.getElementById('middleName');
        var ast   = document.getElementById('midAsterisk');
        var errEl = document.getElementById('err-middleName');
        if (cb.checked) {
            input.value = ''; input.disabled = true; input.style.opacity = '0.5';
            input.classList.remove('error');
            if (errEl) { errEl.classList.remove('show'); var sp = errEl.querySelector('span'); if (sp) sp.textContent = ''; }
            if (ast) ast.textContent = '';
        } else {
            input.disabled = false; input.style.opacity = '1';
            if (ast) ast.textContent = '*';
            input.focus();
        }
    };

    // ─── Save Profile ───
    document.getElementById('profileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();

        var firstName  = document.getElementById('firstName').value.trim();
        var lastName   = document.getElementById('lastName').value.trim();
        var middleName = document.getElementById('middleName').value.trim();
        var noMiddle   = document.getElementById('noMiddleName').checked;
        var emailVal   = document.getElementById('email').value.trim();
        var mobileVal  = document.getElementById('mobile').value.trim();
        var valid = true;

        if (!firstName)                      { setErr('firstName',  'First name is required.');                   valid = false; }
        else if (/\d/.test(firstName))       { setErr('firstName',  'First name must not contain numbers.');      valid = false; }
        if (!lastName)                       { setErr('lastName',   'Last name is required.');                     valid = false; }
        else if (/\d/.test(lastName))        { setErr('lastName',   'Last name must not contain numbers.');        valid = false; }
        if (!noMiddle) {
            if (!middleName)                 { setErr('middleName', 'Enter your middle name or tick the checkbox.'); valid = false; }
            else if (/\d/.test(middleName))  { setErr('middleName', 'Middle name must not contain numbers.');      valid = false; }
        }
        if (!emailVal)                       { setErr('email',  'Email address is required.');                     valid = false; }
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) { setErr('email', 'Enter a valid email address.'); valid = false; }
        if (mobileVal) {
            if (!/^\d+$/.test(mobileVal))          { setErr('mobile', 'Mobile must contain digits only.');        valid = false; }
            else if (!mobileVal.startsWith('09'))   { setErr('mobile', 'Mobile must start with 09.');             valid = false; }
            else if (mobileVal.length !== 11)       { setErr('mobile', 'Mobile must be exactly 11 digits.');      valid = false; }
        }
        if (!valid) return;

        var repName  = (noMiddle || !middleName) ? (firstName + ' ' + lastName) : (firstName + ' ' + middleName + ' ' + lastName);
        var fullName = repName;

        var btn = document.getElementById('btnSaveProfile');
        btn.disabled = true;

        fetch('/api/profile', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({ name: fullName, email: emailVal, mobile: mobileVal || null })
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(res) {
            btn.disabled = false;
            if (res.ok && res.data.success) {
                showToast(res.data.message, 'success');
                var u = res.data.user;
                document.getElementById('infoEmail').textContent  = u.email;
                document.getElementById('infoMobile').textContent = u.mobile || 'No number provided';
                // Update avatar & sidebar
                var namePart = u.name.trim();
                var parts = namePart.split(/\s+/).filter(Boolean);
                var newInitials = parts.map(function(w){ return w[0].toUpperCase(); }).slice(0,2).join('');
                document.getElementById('profileAvatar').textContent = newInitials;
                var sbAv = document.querySelector('.sb-avatar');
                if (sbAv) sbAv.textContent = newInitials;
                var sbName = document.querySelector('.sb-user-info span');
                if (sbName) sbName.textContent = namePart;
            } else {
                if (res.data.errors) showFieldErrors(res.data.errors);
                showToast(res.data.message || 'Failed to update profile.', 'error');
            }
        })
        .catch(function() {
            btn.disabled = false;
            showToast('Something went wrong. Please try again.', 'error');
        });
    });

    // ─── Change Password ───
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();

        var currPw = document.getElementById('current_password').value || '';
        var newPw  = document.getElementById('password').value || '';
        var confPw = document.getElementById('password_confirmation').value || '';
        var valid  = true;

        if (!currPw.trim())        { setErr('current_password',    'Current password is required.');                valid = false; }
        if (!newPw.trim())         { setErr('password',            'New password is required.');                    valid = false; }
        else if (newPw.length < 8) { setErr('password',            'Must be at least 8 characters.');              valid = false; }
        else if (!/[A-Z]/.test(newPw)) { setErr('password',        'Must include at least one uppercase letter.'); valid = false; }
        else if (!/[a-z]/.test(newPw)) { setErr('password',        'Must include at least one lowercase letter.'); valid = false; }
        else if (!/[0-9]/.test(newPw)) { setErr('password',        'Must include at least one number.');           valid = false; }
        if (!confPw.trim())        { setErr('password_confirmation','Please confirm your new password.');           valid = false; }
        else if (newPw && confPw !== newPw) { setErr('password_confirmation', 'Passwords do not match.');          valid = false; }
        if (!valid) return;

        var btn = document.getElementById('btnChangePw');
        btn.disabled = true;

        fetch('/api/profile/password', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            body: JSON.stringify({
                current_password: currPw,
                password: newPw,
                password_confirmation: confPw
            })
        })
        .then(function(r) { return r.json().then(function(d) { return { ok: r.ok, data: d }; }); })
        .then(function(res) {
            btn.disabled = false;
            if (res.ok && res.data.success) {
                showToast(res.data.message, 'success');
                document.getElementById('passwordForm').reset();
                document.getElementById('pwBar').style.width = '0';
                document.getElementById('pwHint').textContent = 'Min 8 chars, uppercase, lowercase, number';
            } else {
                if (res.data.errors) showFieldErrors(res.data.errors);
                showToast(res.data.message || 'Failed to change password.', 'error');
            }
        })
        .catch(function() {
            btn.disabled = false;
            showToast('Something went wrong. Please try again.', 'error');
        });
    });

    // ─── Password Strength ───
    document.getElementById('password').addEventListener('input', function() {
        var pw   = this.value;
        var bar  = document.getElementById('pwBar');
        var hint = document.getElementById('pwHint');
        if (!pw) { bar.style.width = '0'; hint.textContent = 'Min 8 chars, uppercase, lowercase, number'; return; }
        var score = 0;
        if (pw.length >= 8)          score++;
        if (/[a-z]/.test(pw))        score++;
        if (/[A-Z]/.test(pw))        score++;
        if (/[0-9]/.test(pw))        score++;
        if (/[^a-zA-Z0-9]/.test(pw)) score++;
        bar.style.width = (score / 5 * 100) + '%';
        if (score <= 1)      { bar.style.background = '#dc2626'; hint.textContent = 'Weak'; }
        else if (score <= 2) { bar.style.background = '#f59e0b'; hint.textContent = 'Fair'; }
        else if (score <= 3) { bar.style.background = '#f59e0b'; hint.textContent = 'Good'; }
        else if (score <= 4) { bar.style.background = '#16a34a'; hint.textContent = 'Strong'; }
        else                 { bar.style.background = '#16a34a'; hint.textContent = 'Very strong'; }
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
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closeSidebar(); });

    window.logout = function() {
        fetch('/api/logout', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
        }).then(function() { window.location.href = '/login'; })
          .catch(function() { window.location.href = '/login'; });
    };
})();
</script>
</body>
</html>
