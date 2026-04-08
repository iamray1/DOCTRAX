<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Submit Document - DepEd DOCTRAX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #0056b3;
            --primary-dark: #004494;
            --accent: #fca311;
            --bg: #f0f2f5;
            --white: #ffffff;
            --border: #e2e8f0;
            --text-dark: #1b263b;
            --text-muted: #64748b;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html { overflow-y: scroll; }
        body { background: var(--bg); font-family: 'Poppins', sans-serif; min-height: 100vh; -webkit-font-smoothing: antialiased; }

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
        .dash-wrapper{max-width:760px;width:100%;margin:0 auto;padding:28px 24px 48px;flex:1;}

        /* ─── Page header ─── */
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
        .page-header h1{font-size:22px;font-weight:600;color:var(--text-dark)}
        .page-header p{font-size:14px;color:var(--text-muted);font-weight:400}
        .back-link{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:var(--primary);text-decoration:none;font-weight:500;padding:7px 14px;border-radius:8px;border:1px solid var(--border);background:var(--white);transition:all .15s}
        .back-link:hover{background:#f8fafc;border-color:var(--primary)}

        /* ─── Card ─── */
        .card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.07);overflow:hidden;border:1px solid var(--border)}
        .card-head{background:linear-gradient(135deg,#0056b3,#004494);padding:22px 28px;display:flex;align-items:center;gap:14px;color:#fff}
        .card-head-icon{width:44px;height:44px;background:rgba(255,255,255,.15);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
        .card-head h2{font-size:18px;font-weight:700;margin:0}
        .card-head p{font-size:12px;opacity:.8;margin:2px 0 0}
        .card-body{padding:28px}

        /* ─── Section dividers ─── */
        .section-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--text-muted);margin:22px 0 14px;padding-bottom:6px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:6px}
        .section-label:first-child{margin-top:0}

        /* ─── Form ─── */
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .form-group{margin-bottom:16px}
        .form-group.full{grid-column:1/-1}
        label{display:block;font-size:12px;font-weight:600;color:#334155;margin-bottom:5px}
        label .req{color:#dc2626}
        label .opt{color:#94a3b8;font-weight:400}

        input[type=text],input[type=email],input[type=tel],select,textarea{
            width:100%;padding:10px 13px;font-family:'Poppins',sans-serif;font-size:13px;
            border:1.5px solid var(--border);border-radius:10px;background:#f8fafc;
            color:var(--text-dark);outline:none;transition:border-color .2s,box-shadow .2s
        }
        input:focus,select:focus,textarea:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,86,179,.1);background:#fff}
        input.err,select.err,textarea.err{border-color:#dc2626;box-shadow:0 0 0 3px rgba(220,38,38,.08)}
        select{appearance:none;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");background-repeat:no-repeat;background-position:right 12px center;padding-right:36px;cursor:pointer}
        textarea{resize:vertical;min-height:88px}

        .err-text{display:none;font-size:11px;color:#dc2626;margin-top:4px}
        .err-text.show{display:flex;align-items:center;gap:4px}

        .others-wrap{display:none;margin-top:10px}
        .others-wrap.show{display:block}
        .fixed-office{width:100%;padding:10px 13px;border:1.5px solid #bfdbfe;border-radius:10px;background:#eff6ff;color:#1e3a8a;font-size:13px;font-weight:600}
        .fixed-office small{display:block;margin-top:3px;color:#475569;font-weight:500;font-size:11px}

        /* ─── Auth info banner ─── */
        .auth-info-banner{display:flex;align-items:center;gap:12px;background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;padding:12px 16px;margin-bottom:4px}
        .auth-info-banner i{color:#0056b3;font-size:18px;flex-shrink:0}
        .auth-info-name{font-size:13px;font-weight:600;color:#1b263b}
        .auth-info-email{font-size:11px;color:#64748b;margin-top:2px}

        /* ─── Submit button ─── */
        .btn-submit{width:100%;padding:13px;background:var(--primary);color:#fff;border:none;border-radius:10px;font-family:'Poppins',sans-serif;font-size:14px;font-weight:600;cursor:pointer;transition:background .2s,transform .1s;margin-top:6px;display:flex;align-items:center;justify-content:center;gap:8px}
        .btn-submit:hover{background:var(--primary-dark)}
        .btn-submit:active{transform:scale(.99)}
        .btn-submit:disabled{opacity:.7;cursor:not-allowed}

        /* ─── Success state ─── */
        .success-card{text-align:center;padding:24px 20px}
        .success-icon{width:48px;height:48px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;color:#16a34a;font-size:22px}
        .success-card h2{font-size:18px;font-weight:700;color:var(--text-dark);margin-bottom:4px}
        .success-card p{color:var(--text-muted);font-size:12px;margin-bottom:16px}
        .receipt-layout{width:100%;max-width:680px;margin:10px auto 8px;display:flex;flex-direction:column;gap:8px}
        .receipt-top{
            width:100%;
            display:flex;
            flex-direction:column;
            align-items:stretch;
            gap:6px;
            padding:0;
            background:transparent;
            border:none;
            border-radius:0;
            box-shadow:none
        }
        .tracking-box{width:100%;max-width:none}
        .receipt-qr-panel{
            display:grid;
            grid-template-columns:minmax(0, 320px) 50px;
            grid-template-areas:
                "qr action"
                "caption .";
            align-items:center;
            justify-content:center;
            column-gap:8px;
            row-gap:6px;
            width:min(100%, 378px);
            max-width:100%;
            margin:0 auto
        }
        .tracking-box{
            background:transparent;
            border:none;
            border-radius:0;
            padding:0 4px 2px;
            min-height:0;
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            gap:4px;
            text-align:center;
            box-shadow:none
        }
        .tracking-box::after{
            content:"Present this number or let the office scan the QR below.";
            max-width:100%;
            font-size:13px;
            line-height:1.4;
            color:#64748b;
            margin-top:2px
        }
        .tracking-box small{font-size:clamp(18px, 2.6vw, 22px);text-transform:uppercase;letter-spacing:6px;color:#334155;font-weight:700;margin:0;line-height:1}
        .tracking-number{font-size:clamp(56px, 9vw, 82px);font-weight:700;color:var(--primary);font-family:monospace;letter-spacing:5px;line-height:.95;margin:0;word-break:break-word;text-shadow:none}
        .qr-img{grid-area:qr;display:block;width:100%!important;max-width:320px;height:auto!important;aspect-ratio:1 / 1;object-fit:contain;border:none;border-radius:0;padding:0;background:transparent}
        .qr-caption{grid-area:caption;display:flex;align-items:center;justify-content:center;gap:4px;font-size:11px;color:#64748b;font-weight:500;text-align:center}
        .receipt-save-icon{
            grid-area:action;
            width:56px;
            height:56px;
            flex:0 0 56px;
            border:none;
            border-radius:0;
            background:transparent;
            color:var(--primary);
            display:inline-flex;
            align-items:center;
            justify-content:center;
            justify-self:center;
            align-self:start;
            cursor:pointer;
            margin-top:12px;
            transition:color .2s, transform .15s
        }
        .receipt-save-icon:hover{background:transparent;color:var(--primary-dark)}
        .receipt-save-icon:active{transform:scale(.97)}
        .receipt-save-icon:disabled{opacity:.7;cursor:not-allowed}
        .receipt-save-icon:focus,
        .receipt-save-icon:focus-visible{outline:none;box-shadow:none}
        .receipt-save-icon svg{width:24px;height:24px;display:block;stroke:currentColor}
        .btn-secondary{display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border:1.5px solid var(--border);border-radius:10px;color:var(--text-dark);text-decoration:none;font-size:13px;font-weight:500;cursor:pointer;background:#fff;font-family:'Poppins',sans-serif;transition:border-color .2s}
        .btn-secondary:hover{border-color:var(--primary);color:var(--primary)}

        /* Detail summary */
        .receipt-details{width:100%;background:#fff;border:1px solid #e2e8f0;border-radius:18px;padding:10px 12px;text-align:left;box-shadow:none}
        .receipt-details-label{font-size:10px;text-transform:uppercase;letter-spacing:2px;color:#64748b;font-weight:700;margin-bottom:8px}
        .detail-summary{width:100%;text-align:left;border-collapse:collapse;margin-bottom:14px}
        .detail-summary td{padding:6px 8px;font-size:12px;border-bottom:1px solid #e2e8f0;vertical-align:top}
        .detail-summary td:first-child{font-weight:700;color:#59708b;white-space:nowrap;width:126px;font-size:10.5px;text-transform:uppercase;letter-spacing:.3px}
        .detail-summary td:last-child{color:#1b263b;word-break:break-word;font-weight:500}
        .detail-summary tr:last-child td{border-bottom:none}
        .receipt-details .detail-summary{margin-bottom:0}
        .receipt-notes{width:min(100%, 680px);margin:0 auto}
        .receipt-actions{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin-top:8px}
        .note-box{display:grid;grid-template-columns:30px 112px minmax(0,1fr);align-items:start;column-gap:14px;font-size:12px;line-height:1.65;border-radius:18px;padding:14px 18px;margin-bottom:12px;text-align:left;border:1px solid #e5e7eb;background:#fff;box-shadow:none}
        .note-box i{width:30px;height:30px;border-radius:999px;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px;font-size:13px}
        .note-title{font-weight:700;color:#0f172a;padding-top:4px}
        .note-copy{color:#334155;min-width:0}
        .note-warning{color:#334155;border-left:none}
        .note-warning i{color:#64748b;background:#f8fafc}
        .note-info{color:#334155;border-left:none}
        .note-info i{color:#64748b;background:#f8fafc}

        /* ─── Spinner ─── */
        @keyframes spin{to{transform:rotate(360deg)}}
        .spinner{width:16px;height:16px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite}

        /* ─── Footer ─── */
        .dash-footer{width:100%;background:var(--white);border-top:1px solid var(--border);padding:20px 5%;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8}
        .footer-left{display:flex;align-items:center;gap:6px}
        .footer-right{font-size:11px;color:#b0b8c4}

        /* ─── Animations ─── */
        @keyframes fadeIn{from{opacity:0}to{opacity:1}}
        .anim{animation:fadeIn .25s ease forwards}

        /* ─── Responsive ─── */
        @media(max-width:900px){
            .dash-wrapper{padding:20px 16px 40px}
            .page-header{flex-direction:column;align-items:flex-start;gap:12px}
            .dash-footer{flex-direction:column;gap:6px;text-align:center;padding:16px 5%}
            .success-card{padding:18px 12px}
            .success-card h2{font-size:16px}
            .receipt-layout{gap:8px}
            .receipt-top{gap:6px}
            .tracking-box{width:100%}
            .tracking-box{padding:0}
            .tracking-box::after{max-width:100%;font-size:11px}
            .tracking-box small{font-size:clamp(18px, 2.6vw, 22px);letter-spacing:6px}
            .tracking-number{font-size:clamp(56px, 9vw, 82px);letter-spacing:5px}
            .receipt-qr-panel{grid-template-columns:minmax(0,320px) 48px;column-gap:6px;row-gap:6px;width:min(100%,374px)}
            .qr-img{width:100%!important;max-width:320px;padding:0}
            .receipt-save-icon{width:48px;height:48px;flex-basis:48px;border-radius:0;margin-top:10px}
            .receipt-save-icon svg{width:22px;height:22px}
            .detail-summary td{padding:4px 6px;font-size:10.5px}
            .detail-summary td:first-child{width:88px;font-size:9px}
            .receipt-details{padding:10px}
            .note-box{grid-template-columns:26px 88px minmax(0,1fr);column-gap:10px;font-size:11px;padding:12px 14px}
            .note-box i{width:26px;height:26px}
            .note-title{padding-top:3px}
        }
        @media(max-width:580px){.form-row{grid-template-columns:1fr}.card-body{padding:20px}}

        /* ─── Toast ─── */
        .toast{position:fixed;top:24px;right:24px;z-index:300;background:var(--white);border:1px solid var(--border);border-radius:10px;padding:14px 20px;box-shadow:0 8px 24px rgba(0,0,0,0.1);font-size:13px;font-family:'Poppins',sans-serif;color:var(--text-dark);display:flex;align-items:center;gap:8px;min-width:240px;transform:translateY(-20px) translateX(120%);transition:transform .3s ease}
        .toast.show{transform:translateY(0) translateX(0)}
        .toast.success{border-left:3px solid #16a34a}
        .toast.error{border-left:3px solid #dc2626}
        .toast-icon{font-size:16px}
        .toast.success .toast-icon{color:#16a34a}
        .toast.error .toast-icon{color:#dc2626}
    </style>
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="{{ asset('js/request-utils.js') }}?v={{ filemtime(public_path('js/request-utils.js')) }}" defer></script>
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
        <a href="/submit" class="active"><i class="fas fa-paper-plane"></i> Submit Document</a>
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

        <div class="page-header anim">
            <div>
                <h1>Submit Document</h1>
                <p>Fill in the details to generate a Tracking Number</p>
            </div>
            <a href="/dashboard" class="back-link" aria-label="Back to Dashboard" title="Back to Dashboard" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;"><span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></span></a>
        </div>

        <!-- Form state -->
        <div id="formState" class="anim">
            <div class="card">
                <div class="card-head">
                    <div class="card-head-icon"><i class="fas fa-file-alt"></i></div>
                    <div>
                        <h2>Submit Document</h2>
                        <p>Fill in the details below to generate a Tracking Number for your document.</p>
                    </div>
                </div>
                <div class="card-body">

                    <!-- Submitter Information -->
                    <div class="section-label"><i class="fas fa-user"></i> Submitter Information</div>
                    <div class="auth-info-banner">
                        <i class="fas fa-user-check"></i>
                        <div>
                            <div class="auth-info-name">{{ $user->name }}</div>
                            <div class="auth-info-email"><!--email_off-->{{ $user->email }}<!--/email_off--></div>
                        </div>
                    </div>

                    <!-- Document Details -->
                    <div class="section-label"><i class="fas fa-file-invoice"></i> Document Details</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Document Type <span class="req">*</span></label>
                            <select id="docType" onchange="toggleOthers()">
                                <option value="" disabled selected>Select document type</option>
                                <option>Transcript of Records (TOR)</option>
                                <option>Certificate of Employment</option>
                                <option>Service Record</option>
                                <option>Leave Application</option>
                                <option>Memorandum</option>
                                <option>Letter / Endorsement</option>
                                <option>Voucher / Payroll</option>
                                <option>Report / Compliance</option>
                                <option>Request / Petition</option>
                                <option value="Others">Others</option>
                            </select>
                            <div class="err-text" id="errDocType"><i class="fas fa-exclamation-circle"></i> Please select a document type</div>
                        </div>
                        <div class="form-group">
                            <label>Submit To</label>
                            <div class="fixed-office">
                                {{ $recordsOfficeName ?? 'Records Section' }}
                                <small>All submissions are automatically routed to Records Section first.</small>
                            </div>
                        </div>
                    </div>

                    <div class="others-wrap" id="othersWrap">
                        <div class="form-group">
                            <label>Please specify <span class="req">*</span></label>
                            <input type="text" id="othersSpecify" placeholder="e.g. Certificate of Enrollment" autocomplete="off">
                            <div class="err-text" id="errOthers"><i class="fas fa-exclamation-circle"></i> Please specify the document type</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Subject / Title <span class="req">*</span></label>
                        <input type="text" id="subject" placeholder="Brief description of your document" autocomplete="off" data-no-capitalize>
                        <div class="err-text" id="errSubject"><i class="fas fa-exclamation-circle"></i> Subject is required</div>
                    </div>

                    <div class="form-group">
                        <label>Additional Remarks <span class="opt">(Optional)</span></label>
                        <textarea id="description" placeholder="Any additional information, purpose, or special instructions..." data-no-capitalize></textarea>
                    </div>

                    <button class="btn-submit" id="submitBtn" onclick="submitDocument()">
                        <i class="fas fa-paper-plane"></i> Generate Tracking Number
                    </button>
                </div>
            </div>
        </div>

        <!-- Success state -->
        <div id="successState" style="display:none;">
            <div class="card" data-receipt-root>
                <div class="success-card">
                    <div class="success-icon"><i class="fas fa-check-circle"></i></div>
                    <h2>Document Submitted Successfully!</h2>
                    <p>Your document has been logged and is now awaiting acceptance by the Records Section.</p>

                    <div class="receipt-layout">
                        <div class="receipt-top">
                            <div class="tracking-box">
                                <small>Tracking Number</small>
                                <div class="tracking-number" id="generatedCode"></div>
                            </div>
                            <div id="qrBox" class="receipt-qr-panel" style="display:none">
                                <img id="qrImg" alt="QR Code" class="qr-img">
                                <button class="receipt-save-icon" type="button" data-save-receipt-image data-receipt-icon-button aria-label="Save receipt image" title="Save receipt image">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-download"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2 -2v-2" /><path d="M7 11l5 5l5 -5" /><path d="M12 4l0 12" /></svg>
                                </button>
                                <div class="qr-caption"><i class="fas fa-qrcode" style="margin-right:3px"></i>Scan to receive</div>
                            </div>
                        </div>

                    </div>

                    <div class="receipt-notes">
                        <div class="note-box note-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <div class="note-title">Important:</div>
                            <div class="note-copy">Please save this receipt image or take a screenshot. You will need your Tracking Number to track, follow up, or claim your document.</div>
                        </div>
                        <div class="note-box note-info">
                            <i class="fas fa-info-circle"></i>
                            <div class="note-title">Please note:</div>
                            <div class="note-copy">Documents that are not received by the office within <strong>7 days</strong> of submission will be automatically archived. Make sure to follow up if needed.</div>
                        </div>
                    </div>

                    <div class="receipt-details">
                        <div class="receipt-details-label">Document Details</div>
                        <table class="detail-summary" id="detailSummary">
                            <tr><td>Submitted By</td><td id="dSender"></td></tr>
                            <tr><td>Document Type</td><td id="dType"></td></tr>
                            <tr><td>Subject</td><td id="dSubject"></td></tr>
                            <tr><td>Remarks</td><td id="dRemarks"></td></tr>
                            <tr><td>Submitted To</td><td id="dOffice"></td></tr>
                            <tr><td>Date Submitted</td><td id="dDate"></td></tr>
                        </table>
                    </div>

                    <div class="receipt-actions">
                        <button class="btn-submit" style="width:auto;padding:10px 22px;" onclick="window.location.href='/track?ref='+document.getElementById('generatedCode').textContent">
                            <i class="fas fa-search"></i> Track This Document
                        </button>
                        <button class="btn-submit" style="width:auto;padding:10px 22px;background:#059669;" onclick="window.location.href='/my-documents'">
                            <i class="fas fa-folder"></i> My Documents
                        </button>
                        <button class="btn-secondary" onclick="resetForm()">
                            <i class="fas fa-plus"></i> Submit Another
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

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

    <script>
    (function () {
        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function showToast(msg, type) {
            var toast = document.getElementById('toast');
            var icon  = document.getElementById('toastIcon');
            document.getElementById('toastMsg').textContent = msg;
            toast.className = 'toast ' + type + ' show';
            icon.className = 'fas toast-icon ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle');
            setTimeout(function() { toast.classList.remove('show'); }, 3200);
        }

        window.toggleOthers = function () {
            var val = document.getElementById('docType').value;
            document.getElementById('othersWrap').classList.toggle('show', val === 'Others');
            if (val !== 'Others') document.getElementById('othersSpecify').value = '';
        };

        function clearErrors() {
            document.querySelectorAll('.err-text').forEach(function(e) { e.classList.remove('show'); });
            document.querySelectorAll('input,select,textarea').forEach(function(e) { e.classList.remove('err'); });
        }

        function fieldErr(inputId, errId) {
            var el = document.getElementById(inputId);
            var er = document.getElementById(errId);
            if (el) el.classList.add('err');
            if (er) er.classList.add('show');
        }

        window.submitDocument = async function () {
            clearErrors();
            var valid = true;

            var docType   = document.getElementById('docType').value;
            var othersVal = document.getElementById('othersSpecify').value.trim();
            var subject   = document.getElementById('subject').value.trim();
            var description = document.getElementById('description').value.trim();

            if (!docType)   { fieldErr('docType', 'errDocType'); valid = false; }
            if (docType === 'Others' && !othersVal) { fieldErr('othersSpecify', 'errOthers'); valid = false; }
            if (!subject)   { fieldErr('subject', 'errSubject'); valid = false; }
            if (!valid) return;

            var finalType = docType === 'Others' ? othersVal : docType;
            var btn = document.getElementById('submitBtn');
            btn.disabled = true;

            try {
                var res = await fetch('/api/submit-document', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify({ type: finalType, subject: subject, description: description || null }),
                });
                var data = await res.json();

                if (data.success) {
                    document.getElementById('formState').style.display    = 'none';
                    document.getElementById('successState').style.display = 'block';
                    document.getElementById('generatedCode').textContent  = data.reference_number || '-';
                if (data.reference_number) {
                    document.getElementById('qrImg').src = '/qr/' + encodeURIComponent(data.reference_number);
                    document.getElementById('qrBox').style.display = '';
                }
                    if (data.details) {
                        document.getElementById('dSender').textContent  = data.details.sender_name || '-';
                        document.getElementById('dType').textContent    = data.details.type || '-';
                        document.getElementById('dSubject').textContent = data.details.subject || '-';
                        document.getElementById('dRemarks').textContent = data.details.description || 'No remarks provided';
                        document.getElementById('dOffice').textContent  = data.details.submitted_to || '-';
                        document.getElementById('dDate').textContent    = data.details.date || '-';
                    }
                } else {
                    showToast(data.message || 'Submission failed. Please try again.', 'error');
                    btn.disabled = false;
                }
            } catch (err) {
                showToast('System error. Please try again.', 'error');
                btn.disabled = false;
            }
        };

        window.resetForm = function () {
            document.getElementById('formState').style.display    = 'block';
            document.getElementById('successState').style.display = 'none';
            document.getElementById('docType').value = '';
            document.getElementById('othersSpecify').value = '';
            document.getElementById('subject').value = '';
            document.getElementById('description').value = '';
            document.getElementById('othersWrap').classList.remove('show');
            clearErrors();
            // re-trigger form-utils on fresh inputs
            document.getElementById('docType').dispatchEvent(new Event('change'));
        };

        function clearErrors() {
            document.querySelectorAll('.err-text').forEach(function(e) { e.classList.remove('show'); });
            document.querySelectorAll('input,select,textarea').forEach(function(e) { e.classList.remove('err'); });
        }

        // ─── Sidebar ───
        window.toggleSidebar = function () {
            var s = document.getElementById('mainSidebar');
            var o = document.getElementById('mobOverlay');
            var open = s.classList.toggle('open');
            o.classList.toggle('open', open);
            document.body.style.overflow = open ? 'hidden' : '';
            document.getElementById('mobHamBtn').classList.toggle('toggle', open);
        };
        window.closeSidebar = function () {
            document.getElementById('mainSidebar').classList.remove('open');
            document.getElementById('mobOverlay').classList.remove('open');
            document.body.style.overflow = '';
            var btn = document.getElementById('mobHamBtn'); if (btn) btn.classList.remove('toggle');
        };

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeSidebar();
        });

        // ─── Logout ───
        window.logout = function () {
            fetch('/api/logout', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            }).then(function () { window.location.href = '/login'; })
              .catch(function () { window.location.href = '/login'; });
        };
    })();
    </script>
</div><!-- end .main -->
</body>
</html>
