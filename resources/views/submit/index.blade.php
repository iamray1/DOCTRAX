<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Submit Document - DepEd DTS</title>
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
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background: var(--bg); font-family: 'Poppins', sans-serif; min-height: 100vh; display: flex; flex-direction: column; }

        /* Navbar */
        .navbar { background: linear-gradient(135deg, #0056b3, #004494); padding: 14px clamp(18px, 2vw, 28px); display: flex; justify-content: space-between; align-items: center; gap: 14px; box-shadow: 0 2px 8px rgba(0,0,0,.12); }
        .brand-text { display: flex; flex-direction: column; gap: 4px; min-width: 0; }
        .brand-subtitle { font-size: clamp(10px, 1vw, 11px); font-weight: 500; color: rgba(255,255,255,.9); text-transform: uppercase; letter-spacing: 2.4px; line-height: 1.1; }
        .navbar h1 { font-size: clamp(18px, 2.2vw, 22px); font-weight: 700; color: #fff; margin: 0; line-height: 1.08; }
        .brand-caption { font-size: clamp(11px, 1.2vw, 13px); font-weight: 300; color: rgba(255,255,255,.92); line-height: 1.18; white-space: normal; }
        .nav-links{display:flex;align-items:center;gap:4px;flex-shrink:0}
        .nav-link{color:rgba(255,255,255,.85);text-decoration:none;font-size:13px;font-weight:500;padding:8px 13px;border-radius:8px;transition:background .2s,color .2s;display:flex;align-items:center;gap:6px;white-space:nowrap}
        .nav-link:hover{background:rgba(255,255,255,.15);color:#fff}
        .nav-link.active{background:rgba(255,255,255,.18);color:#fff}
        .nav-hamburger{display:none;background:none;border:none;cursor:pointer;padding:6px;color:#fff;font-size:20px;z-index:101;align-items:center;justify-content:center;transition:transform .2s}
        .nav-hamburger.open{transform:rotate(90deg)}

        /* Page wrapper */
        .page { max-width: 680px; margin: 36px auto; padding: 0 16px 60px; flex: 1; width: 100%; }

        /* Card */
        .card { background: #fff; border-radius: 16px; box-shadow: 0 4px 24px rgba(0,0,0,.07); overflow: hidden; }
        .card-head { background: linear-gradient(135deg, #0056b3, #004494); padding: 22px 28px; display: flex; align-items: center; gap: 14px; color: #fff; }
        .card-head-icon { width: 44px; height: 44px; background: rgba(255,255,255,.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
        .card-head h2 { font-size: clamp(14px, 3.5vw, 18px); font-weight: 700; margin: 0; }
        .card-head p { font-size: clamp(10px, 2.2vw, 12px); opacity: .8; margin: 2px 0 0; }
        .card-body { padding: 28px; }

        /* Section dividers */
        .section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin: 22px 0 14px; padding-bottom: 6px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 6px; }
        .section-label:first-child { margin-top: 0; }

        /* Form groups */
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { margin-bottom: 16px; }
        .form-group.full { grid-column: 1 / -1; }
        label { display: block; font-size: clamp(11px, 2.5vw, 12px); font-weight: 600; color: #334155; margin-bottom: 5px; }
        label .req { color: #dc2626; }
        label .opt { color: #94a3b8; font-weight: 400; }

        input[type=text], input[type=email], input[type=tel], select, textarea {
            width: 100%; padding: 10px 13px; font-family: 'Poppins', sans-serif; font-size: clamp(12px, 2.5vw, 13px);
            border: 1.5px solid var(--border); border-radius: 10px; background: #f8fafc;
            color: var(--text-dark); outline: none; transition: border-color .2s, box-shadow .2s;
        }
        input:focus, select:focus, textarea:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0,86,179,.1); background: #fff;
        }
        input.err, select.err, textarea.err { border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,.08); }
        select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; cursor: pointer; }
        textarea { resize: vertical; min-height: 88px; }

        .err-text { display: none; font-size: 11px; color: #dc2626; margin-top: 4px; }
        .err-text.show { display: flex; align-items: center; gap: 4px; }

        /* Others input */
        .others-wrap { display: none; margin-top: 10px; }
        .others-wrap.show { display: block; }
        .fixed-office {
            width: 100%;
            padding: 10px 13px;
            border: 1.5px solid #bfdbfe;
            border-radius: 10px;
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 13px;
            font-weight: 600;
        }
        .fixed-office small {
            display: block;
            margin-top: 3px;
            color: #475569;
            font-weight: 500;
            font-size: 11px;
        }

        /* Submit button */
        .btn-submit { width: 100%; padding: 13px; background: var(--primary); color: #fff; border: none; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: clamp(12px, 2.8vw, 14px); font-weight: 600; cursor: pointer; transition: background .2s, transform .1s; margin-top: 6px; display: flex; align-items: center; justify-content: center; gap: 8px; }
        .btn-submit:hover { background: var(--primary-dark); }
        .btn-submit:active { transform: scale(.99); }
        .btn-submit:disabled { opacity: .7; cursor: not-allowed; }

        /* Success state */
        .success-card { text-align: center; padding: 24px 20px; }
        .success-icon { width: 48px; height: 48px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; color: #16a34a; font-size: 22px; }
        .success-card h2 { font-size: 18px; font-weight: 700; color: var(--text-dark); margin-bottom: 4px; }
        .success-card p { color: var(--text-muted); font-size: 12px; margin-bottom: 16px; }
        .receipt-layout { width: 100%; max-width: 680px; margin: 10px auto 8px; display: flex; flex-direction: column; gap: 8px; }
        .receipt-top {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            gap: 6px;
            padding: 0;
            background: transparent;
            border: none;
            border-radius: 0;
            box-shadow: none;
        }
        .tracking-box { width: 100%; max-width: none; }
        .receipt-qr-panel {
            display: grid;
            grid-template-columns: minmax(0, 320px) 50px;
            grid-template-areas:
                "qr action"
                "caption .";
            align-items: center;
            justify-content: center;
            column-gap: 8px;
            row-gap: 6px;
            width: min(100%, 378px);
            max-width: 100%;
            margin: 0 auto;
        }
        .tracking-box {
            background: transparent;
            border: none;
            border-radius: 0;
            padding: 0 4px 2px;
            min-height: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            text-align: center;
            box-shadow: none;
        }
        .tracking-box::after {
            content: "Present this number or let the office scan the QR below.";
            max-width: 100%;
            font-size: 13px;
            line-height: 1.4;
            color: #64748b;
            margin-top: 2px;
        }
        .tracking-box small {
            font-size: clamp(18px, 2.6vw, 22px);
            text-transform: uppercase;
            letter-spacing: 6px;
            color: #334155;
            font-weight: 700;
            margin: 0;
            line-height: 1;
        }
        .tracking-number {
            font-size: clamp(56px, 9vw, 82px);
            font-weight: 700;
            color: var(--primary);
            font-family: monospace;
            letter-spacing: 5px;
            line-height: .95;
            margin: 0;
            word-break: break-word;
            text-shadow: none;
        }
        .qr-img {
            grid-area: qr;
            display: block;
            width: 100% !important;
            max-width: 320px;
            height: auto !important;
            aspect-ratio: 1 / 1;
            object-fit: contain;
            border: none;
            border-radius: 0;
            padding: 0;
            background: transparent;
        }
        .qr-caption { grid-area: caption; display: flex; align-items: center; justify-content: center; gap: 4px; font-size: 11px; color: #64748b; font-weight: 500; text-align: center; }
        .receipt-save-icon {
            grid-area: action;
            width: 56px;
            height: 56px;
            flex: 0 0 56px;
            border: none;
            border-radius: 0;
            background: transparent;
            color: var(--primary);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            justify-self: center;
            align-self: start;
            cursor: pointer;
            margin-top: 12px;
            transition: color .2s, transform .15s;
        }
        .receipt-save-icon:hover { background: transparent; color: var(--primary-dark); }
        .receipt-save-icon:active { transform: scale(.97); }
        .receipt-save-icon:disabled { opacity: .7; cursor: not-allowed; }
        .receipt-save-icon:focus,
        .receipt-save-icon:focus-visible { outline: none; box-shadow: none; }
        .receipt-save-icon svg { width: 24px; height: 24px; display: block; stroke: currentColor; }
        .btn-secondary { display: inline-flex; align-items: center; gap: 6px; padding: 10px 20px; border: 1.5px solid var(--border); border-radius: 10px; color: var(--text-dark); text-decoration: none; font-size: 13px; font-weight: 500; cursor: pointer; background: #fff; font-family: 'Poppins', sans-serif; transition: border-color .2s; }
        .btn-secondary:hover { border-color: var(--primary); color: var(--primary); }

        /* Detail summary */
        .receipt-details { width: 100%; background: #fff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 10px 12px; text-align: left; box-shadow: none; }
        .receipt-details-label { font-size: 10px; text-transform: uppercase; letter-spacing: 2px; color: #64748b; font-weight: 700; margin-bottom: 8px; }
        .detail-summary { width:100%; text-align:left; border-collapse:collapse; margin-bottom:14px; }
        .detail-summary td { padding:6px 8px; font-size:12px; border-bottom:1px solid #e2e8f0; vertical-align:top; }
        .detail-summary td:first-child { font-weight:700; color:#59708b; white-space:nowrap; width:126px; font-size:10.5px; text-transform:uppercase; letter-spacing:.3px; }
        .detail-summary td:last-child { color:#1b263b; word-break:break-word; font-weight:500; }
        .detail-summary tr:last-child td { border-bottom:none; }
        .receipt-details .detail-summary { margin-bottom: 0; }
        .receipt-notes { width: min(100%, 680px); margin: 0 auto; }
        .receipt-actions { display:flex; gap:10px; justify-content:center; flex-wrap:wrap; margin-top:8px; }
        .note-box { display:grid; grid-template-columns:30px 112px minmax(0,1fr); align-items:start; column-gap:14px; font-size:12px; line-height:1.65; border-radius:18px; padding:14px 18px; margin-bottom:12px; text-align:left; border:1px solid #e5e7eb; background:#fff; box-shadow:none; }
        .note-box i { width:30px; height:30px; border-radius:999px; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px; font-size:13px; }
        .note-title { font-weight:700; color:#0f172a; padding-top:4px; }
        .note-copy { color:#334155; min-width:0; }
        .note-warning { color:#334155; border-left:none; }
        .note-warning i { color:#64748b; background:#f8fafc; }
        .note-info { color:#334155; border-left:none; }
        .note-info i { color:#64748b; background:#f8fafc; }

        /* Auth info banner */
        .auth-info-banner { display:flex; align-items:center; gap:12px; background:#eff6ff; border:1.5px solid #bfdbfe; border-radius:10px; padding:12px 16px; margin-bottom:4px; }
        .auth-info-banner i { color:#0056b3; font-size:18px; flex-shrink:0; }
        .auth-info-name { font-size:13px; font-weight:600; color:#1b263b; }
        .auth-info-email { font-size:11px; color:#64748b; margin-top:2px; }

        /* Spinner */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,.4); border-top-color: #fff; border-radius: 50%; animation: spin .7s linear infinite; }

        @media (max-width: 640px) {
            .navbar{padding:14px 4%;position:relative;flex-wrap:wrap}
            .nav-hamburger{display:flex;order:-1}
            .brand-text{flex:1;min-width:0}
            .navbar h1{font-size:clamp(18px,4.8vw,22px);line-height:1.08}
            .brand-subtitle{font-size:clamp(10px,2.4vw,11px);letter-spacing:2.4px}
            .brand-caption{font-size:clamp(11px,2.9vw,13px);line-height:1.18}
            .nav-links{display:none;position:absolute;top:100%;right:0;left:0;background:linear-gradient(135deg,#004494,#003378);flex-direction:column;padding:6px 0;box-shadow:0 8px 24px rgba(0,0,0,.18);z-index:100}
            .nav-links.open{display:flex}
            .nav-link{width:100%;padding:13px 20px;border-radius:0;font-size:13px;border-bottom:1px solid rgba(255,255,255,.08)}
            .nav-link:last-child{border-bottom:none}
            .nav-link:hover{background:rgba(255,255,255,.1)}
            .form-row{grid-template-columns:1fr}
            .card-body{padding:20px}
            .page{margin:20px auto;padding:0 10px 40px}
            .card-head{padding:16px 18px;gap:10px}
            .card-head-icon{width:36px;height:36px;font-size:16px}
            .card-head h2{font-size:14px}
            .card-head p{font-size:10px}
            .btn-submit{padding:12px}
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
            .auth-info-banner{padding:10px 12px;gap:8px}
            .auth-info-name{font-size:12px}
            .section-label{font-size:10px;margin:16px 0 10px}
        }
        .dash-footer{width:100%;background:#fff;border-top:1px solid #e2e8f0;padding:20px 5%;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8;margin-top:40px}
        .footer-left{display:flex;align-items:center;gap:6px}
        .footer-right{font-size:11px;color:#b0b8c4}
        @media(max-width:768px){.dash-footer{flex-direction:column;gap:6px;text-align:center;padding:16px 5%}}

        /* ─── Toast ─── */
        .toast{position:fixed;top:24px;right:24px;z-index:300;background:#fff;border:1px solid #e2e8f0;border-radius:10px;padding:14px 20px;box-shadow:0 8px 24px rgba(0,0,0,0.1);font-size:13px;font-family:'Poppins',sans-serif;color:#1b263b;display:flex;align-items:center;gap:8px;min-width:240px;transform:translateY(-20px) translateX(120%);transition:transform .3s ease}
        .toast.show{transform:translateY(0) translateX(0)}
        .toast.success{border-left:3px solid #16a34a}
        .toast.error{border-left:3px solid #dc2626}
        .toast-icon{font-size:16px}
        .toast.success .toast-icon{color:#16a34a}
        .toast.error .toast-icon{color:#dc2626}

        /* Existing account prompt modal */
        .signin-modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.45);display:none;align-items:center;justify-content:center;padding:14px;z-index:400}
        .signin-modal-overlay.show{display:flex}
        .signin-modal{width:100%;max-width:460px;background:#fff;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 20px 50px rgba(2,6,23,.22);overflow:hidden}
        .signin-modal-head{padding:16px 18px;background:#eff6ff;border-bottom:1px solid #dbeafe;display:flex;align-items:center;gap:10px}
        .signin-modal-head i{color:#1d4ed8;font-size:18px}
        .signin-modal-head h3{font-size:15px;color:#1e3a8a;font-weight:700}
        .signin-modal-body{padding:16px 18px}
        .signin-modal-body p{font-size:13px;color:#334155;line-height:1.6;margin-bottom:6px}
        .signin-modal-email{font-size:12px;color:#0f172a;font-weight:700;word-break:break-word;margin-bottom:10px}
        .signin-modal-actions{display:flex;gap:8px;justify-content:flex-end;padding-top:8px}
        .btn-modal-secondary,.btn-modal-primary{font-family:'Poppins',sans-serif;font-size:12px;font-weight:600;border-radius:9px;padding:9px 14px;cursor:pointer}
        .btn-modal-secondary{background:#fff;border:1.5px solid #cbd5e1;color:#334155}
        .btn-modal-primary{background:#0056b3;border:1.5px solid #0056b3;color:#fff}
        .btn-modal-primary:hover{background:#004494;border-color:#004494}
    </style>
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="{{ asset('js/request-utils.js') }}?v={{ filemtime(public_path('js/request-utils.js')) }}" defer></script>
</head>
<body>
    <nav class="navbar">
        <div class="brand-text">
            <span class="brand-subtitle">Department of Education</span>
            <h1>CITY OF SAN JOSE DEL MONTE</h1>
            <span class="brand-caption">Document Tracking System &mdash; DOCTRAX</span>
        </div>
        <button class="nav-hamburger" id="navHamburger" onclick="document.getElementById('navLinks').classList.toggle('open');this.classList.toggle('open')" aria-label="Menu">
            <i class="fas fa-bars"></i>
        </button>
        <div class="nav-links" id="navLinks">
            <a href="/" class="nav-link"><i class="fas fa-home"></i> Home</a>
            <a href="/about-us" class="nav-link"><i class="fas fa-info-circle"></i> About Us</a>
            <a href="/contact-us" class="nav-link"><i class="fas fa-envelope"></i> Contact Us</a>
        </div>
    </nav>

    <div class="page">

        <!-- Form state -->
        <div id="formState">
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
                    @auth
                    <div class="auth-info-banner">
                        <i class="fas fa-user-check"></i>
                        <div>
                            <div class="auth-info-name">{{ auth()->user()->name }}</div>
                            <div class="auth-info-email"><!--email_off-->{{ auth()->user()->email }}<!--/email_off--></div>
                        </div>
                    </div>
                    @else
                    <div class="form-row">
                        <div class="form-group">
                            <label>First Name <span class="req">*</span></label>
                            <input type="text" id="senderFirstName" placeholder="e.g. Juan" autocomplete="off">
                            <div class="err-text" id="errSenderFirstName"><i class="fas fa-exclamation-circle"></i> First name is required</div>
                        </div>
                        <div class="form-group">
                            <label>Last Name <span class="req">*</span></label>
                            <input type="text" id="senderLastName" placeholder="e.g. Dela Cruz" autocomplete="off">
                            <div class="err-text" id="errSenderLastName"><i class="fas fa-exclamation-circle"></i> Last name is required</div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Contact Number <span class="opt">(Optional)</span></label>
                            <input type="tel" id="senderContact" placeholder="e.g. 09171234567" autocomplete="off" inputmode="numeric" maxlength="11">
                            <div class="err-text" id="errSenderContact"><i class="fas fa-exclamation-circle"></i> Contact number must start with 09 and contain 11 digits</div>
                        </div>
                        <div class="form-group">
                            <label>Email Address <span class="req">*</span></label>
                            <input type="email" id="senderEmail" placeholder="e.g. juan@example.com" autocomplete="email" inputmode="email" autocapitalize="none" autocorrect="off" spellcheck="false">
                            <div style="font-size:11px;color:#64748b;margin-top:4px;">Use the same email when creating your account so your submitted documents can be linked automatically.</div>
                            <div class="err-text" id="errSenderEmail"><i class="fas fa-exclamation-circle"></i> Please enter a valid email address</div>
                        </div>
                    </div>
                    @endauth

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
                        <button class="btn-submit" style="width:auto;padding:10px 22px;" onclick="document.getElementById('trackingInput')?.focus();window.location.href='/track?ref='+document.getElementById('generatedCode').textContent">
                            <i class="fas fa-search"></i> Track This Document
                        </button>
                        @if(auth()->check())
                        <button class="btn-submit" style="width:auto;padding:10px 22px;background:#059669;" onclick="window.location.href='/my-documents'">
                            <i class="fas fa-folder"></i> My Documents
                        </button>
                        @endif
                        <button class="btn-secondary" onclick="window.location.reload()">
                            <i class="fas fa-plus"></i> Submit Another
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="signin-modal-overlay" id="signinModal">
        <div class="signin-modal" role="dialog" aria-modal="true" aria-labelledby="signinModalTitle">
            <div class="signin-modal-head">
                <i class="fas fa-user-lock"></i>
                <h3 id="signinModalTitle">Account Found</h3>
            </div>
            <div class="signin-modal-body">
                <p>This email is already registered. Please sign in to continue submitting documents.</p>
                <div class="signin-modal-email" id="signinModalEmail"></div>
                <p>Your current form details are saved and will stay here after you sign in.</p>
                <div class="signin-modal-actions">
                    <button type="button" class="btn-modal-secondary" onclick="closeSigninModal()">Cancel</button>
                    <button type="button" class="btn-modal-primary" onclick="goToLoginFromSubmit()">Sign In</button>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const isLoggedIn = {{ auth()->check() ? 'true' : 'false' }};
        const guestSubmitDraftKey = 'doctrax.public_submit_draft';

        function showToast(msg, type) {
            var toast = document.getElementById('toast');
            var icon  = document.getElementById('toastIcon');
            document.getElementById('toastMsg').textContent = msg;
            toast.className = 'toast ' + type + ' show';
            icon.className = 'fas toast-icon ' + (type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle');
            setTimeout(function() { toast.classList.remove('show'); }, 3200);
        }

        window.toggleOthers = function () {
            const val = document.getElementById('docType').value;
            document.getElementById('othersWrap').classList.toggle('show', val === 'Others');
            if (val !== 'Others') document.getElementById('othersSpecify').value = '';
        };

        function clearErrors() {
            document.querySelectorAll('.err-text').forEach(e => e.classList.remove('show'));
            document.querySelectorAll('input,select,textarea').forEach(e => e.classList.remove('err'));
        }

        function normalizeContact(value) {
            return String(value || '').replace(/\D/g, '').slice(0, 11);
        }

        function isValidContact(value) {
            return /^09\d{9}$/.test(value);
        }

        function getGuestDraft() {
            return {
                sender_first_name: document.getElementById('senderFirstName')?.value.trim() || '',
                sender_last_name: document.getElementById('senderLastName')?.value.trim() || '',
                sender_contact: document.getElementById('senderContact')?.value.trim() || '',
                sender_email: document.getElementById('senderEmail')?.value.trim().toLowerCase() || '',
                type: document.getElementById('docType')?.value || '',
                others: document.getElementById('othersSpecify')?.value.trim() || '',
                subject: document.getElementById('subject')?.value.trim() || '',
                description: document.getElementById('description')?.value.trim() || '',
            };
        }

        function saveGuestDraft() {
            if (isLoggedIn) return;
            try {
                localStorage.setItem(guestSubmitDraftKey, JSON.stringify(getGuestDraft()));
            } catch (_) {}
        }

        function restoreGuestDraft() {
            let raw = null;
            try {
                raw = localStorage.getItem(guestSubmitDraftKey);
            } catch (_) {
                raw = null;
            }
            if (!raw) return;

            let draft = null;
            try {
                draft = JSON.parse(raw);
            } catch (_) {
                return;
            }
            if (!draft || typeof draft !== 'object') return;

            // After sign-in, guest-only submitter fields are hidden, but document fields should still be restored.
            if (!isLoggedIn) {
                if (draft.sender_first_name) document.getElementById('senderFirstName').value = draft.sender_first_name;
                if (draft.sender_last_name) document.getElementById('senderLastName').value = draft.sender_last_name;
                if (draft.sender_contact) document.getElementById('senderContact').value = normalizeContact(draft.sender_contact);
                if (draft.sender_email) document.getElementById('senderEmail').value = draft.sender_email;
            }
            if (draft.type) {
                document.getElementById('docType').value = draft.type;
                window.toggleOthers();
            }
            if (draft.others) document.getElementById('othersSpecify').value = draft.others;
            if (draft.subject) document.getElementById('subject').value = draft.subject;
            if (draft.description) document.getElementById('description').value = draft.description;
        }

        function removeGuestDraft() {
            try {
                localStorage.removeItem(guestSubmitDraftKey);
            } catch (_) {}
        }

        window.closeSigninModal = function () {
            document.getElementById('signinModal').classList.remove('show');
        };

        window.goToLoginFromSubmit = function () {
            saveGuestDraft();
            window.location.href = '/login?next=' + encodeURIComponent('/submit');
        };

        function showSigninModal(email, message) {
            document.getElementById('signinModalEmail').textContent = email || '';
            if (message) {
                const bodyP = document.querySelector('#signinModal .signin-modal-body p');
                if (bodyP) bodyP.textContent = message;
            }
            document.getElementById('signinModal').classList.add('show');
        }

        // Name fields: letters, spaces, dots, and hyphens only (no numbers)
        ['senderFirstName', 'senderLastName'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', function() {
                    this.value = this.value.replace(/[^a-zA-Z\s.\-]/g, '');
                    saveGuestDraft();
                });
            }
        });

        var contactEl = document.getElementById('senderContact');
        if (contactEl) {
            contactEl.addEventListener('input', function () {
                this.value = normalizeContact(this.value);
                saveGuestDraft();
            });
        }

        ['senderEmail', 'othersSpecify', 'subject', 'description', 'docType'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', saveGuestDraft);
            el.addEventListener('change', saveGuestDraft);
        });

        function fieldErr(inputId, errId) {
            document.getElementById(inputId)?.classList.add('err');
            document.getElementById(errId)?.classList.add('show');
        }

        window.submitDocument = async function () {
            clearErrors();
            let valid = true;

            // Only collect sender info from fields if guest
            let senderFirstName = null, senderLastName = null, senderContact = null, senderEmail = null;
            if (!isLoggedIn) {
                senderFirstName = document.getElementById('senderFirstName').value.trim();
                senderLastName  = document.getElementById('senderLastName').value.trim();
                senderContact   = normalizeContact(document.getElementById('senderContact').value.trim()) || null;
                document.getElementById('senderContact').value = senderContact || '';
                senderEmail     = document.getElementById('senderEmail').value.trim().toLowerCase();
                if (!senderFirstName) { fieldErr('senderFirstName', 'errSenderFirstName'); valid = false; }
                if (!senderLastName)  { fieldErr('senderLastName', 'errSenderLastName'); valid = false; }
                if (senderContact && !isValidContact(senderContact)) {
                    fieldErr('senderContact', 'errSenderContact'); valid = false;
                }
                if (!senderEmail || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(senderEmail)) {
                    fieldErr('senderEmail', 'errSenderEmail'); valid = false;
                }
            }

            const docType     = document.getElementById('docType').value;
            const othersVal   = document.getElementById('othersSpecify').value.trim();
            const subject     = document.getElementById('subject').value.trim();
            const description = document.getElementById('description').value.trim();

            if (!docType)   { fieldErr('docType', 'errDocType'); valid = false; }
            if (docType === 'Others' && !othersVal) { fieldErr('othersSpecify', 'errOthers'); valid = false; }
            if (!subject)   { fieldErr('subject', 'errSubject'); valid = false; }
            if (!valid) return;

            const finalType = docType === 'Others' ? othersVal : docType;
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;

            if (!isLoggedIn) {
                saveGuestDraft();
                try {
                    const emailCheckRes = await fetch('/api/check-email', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                        body: JSON.stringify({ email: senderEmail })
                    });
                    const emailCheck = await emailCheckRes.json();
                    if (emailCheck.exists) {
                        showSigninModal(senderEmail, emailCheck.message || 'This email is already registered. Please sign in to continue submitting documents.');
                        btn.disabled = false;
                        return;
                    }
                } catch (_) {
                    // Continue and let submit endpoint validate if email-check is temporarily unreachable.
                }
            }

            const payload = {
                type: finalType,
                subject: subject,
                description: description || null,
            };
            if (!isLoggedIn) {
                payload.sender_first_name = senderFirstName;
                payload.sender_last_name  = senderLastName;
                payload.sender_contact    = senderContact;
                payload.sender_email      = senderEmail;
            }

            try {
                const res  = await fetch('/api/submit-document', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
                    body: JSON.stringify(payload),
                });
                const data = await res.json();

                if (data.success) {
                    removeGuestDraft();
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
                    if (data.requires_login) {
                        saveGuestDraft();
                        showSigninModal(senderEmail, data.message || 'This email is already registered. Please sign in to continue submitting documents.');
                    } else {
                        showToast(data.message || 'Submission failed. Please try again.', 'error');
                    }
                    btn.disabled = false;
                }
            } catch (err) {
                showToast('System error. Please try again.', 'error');
                btn.disabled = false;
            }
        };

        restoreGuestDraft();

        var signinModalEl = document.getElementById('signinModal');
        if (signinModalEl) {
            signinModalEl.addEventListener('click', function (e) {
                if (e.target === signinModalEl) {
                    closeSigninModal();
                }
            });
        }
    })();
    </script>
    <!-- Toast -->
    <div class="toast" id="toast">
        <i class="fas toast-icon" id="toastIcon"></i>
        <span id="toastMsg"></span>
    </div>
    <footer class="dash-footer">
        <div class="footer-left">
            <span>&copy; {{ date('Y') }} DepEd Document Tracking System</span>
        </div>
        <div class="footer-right">
            Developed by Raymond Bautista
        </div>
    </footer>
</body>
</html>
