<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document Detail - DepEd DTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root{--primary:#0056b3;--primary-dark:#004494;--bg:#f0f2f5;--border:#e2e8f0;--text-dark:#1b263b;--text-muted:#64748b}
        *{margin:0;padding:0;box-sizing:border-box}
        body{background:var(--bg);font-family:Poppins,sans-serif;min-height:100vh;display:flex;flex-direction:column}
        .sidebar{position:fixed;top:0;left:0;width:240px;height:100vh;background:#0056b3;display:flex;flex-direction:column;z-index:200;transform:translateX(-100%);transition:transform .25s ease}
        .sidebar.open{transform:translateX(0)}
        .sb-brand{padding:22px 20px 18px;border-bottom:1px solid rgba(255,255,255,.12);text-align:center}
        .sb-brand img{width:64px;height:64px;margin-bottom:8px}
        .sb-brand h2{font-size:18px;font-weight:700;color:#fff;margin-bottom:2px}
        .sb-brand small{font-size:11px;color:rgba(255,255,255,.65);display:block}
        .sb-nav{flex:1;padding:12px 0;overflow-y:auto}
        .sb-nav a{display:flex;align-items:center;gap:11px;padding:11px 20px;color:rgba(255,255,255,.78);text-decoration:none;font-size:13px;font-weight:500;transition:background .15s}
        .sb-nav a:hover{background:rgba(255,255,255,.14);color:#fff}
        .sb-nav a i{width:16px;text-align:center}
        .sb-nav .nav-section{padding:10px 20px 4px;font-size:9px;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.4);font-weight:600}
        .sb-footer{padding:14px 20px;border-top:1px solid rgba(255,255,255,.12)}
        .sb-user{display:flex;align-items:center;gap:10px}
        .sb-avatar{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0}
        .sb-user-info small{font-size:10px;color:rgba(255,255,255,.55);display:block}
        .sb-user-info span{font-size:12px;font-weight:600;color:#fff}
        .btn-logout{display:flex;align-items:center;gap:7px;margin-top:8px;padding:8px 14px;background:rgba(255,255,255,.1);border:none;border-radius:8px;color:rgba(255,255,255,.8);font-size:12px;cursor:pointer;font-family:Poppins,sans-serif;width:100%;justify-content:center;transition:background .2s}
        .btn-logout:hover{background:rgba(255,255,255,.2)}
        .main{margin-left:0;padding:60px 28px 60px;flex:1}
        .back-link{display:inline-flex;align-items:center;gap:7px;color:var(--text-muted);text-decoration:none;font-size:13px;margin-bottom:18px;transition:color .2s}
        .back-link:hover{color:var(--primary)}
        .two-col{display:grid;grid-template-columns:1fr 360px;gap:20px;align-items:start}
        .card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.06);overflow:hidden}
        .card-head{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap}
        .card-head h2{font-size:16px;font-weight:700;color:var(--text-dark)}
        .card-head p{font-size:12px;color:var(--text-muted);margin-top:2px}
        .card-body{padding:22px}
        .status-badge{padding:5px 13px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}
        .info-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        .info-item .label{font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);font-weight:600;margin-bottom:4px}
        .info-item .value{font-size:13px;color:var(--text-dark);font-weight:500}
        .info-item.full{grid-column:1/-1}
        /* Timeline */
        .timeline{position:relative;margin-top:4px}
        .timeline::before{content:'';position:absolute;left:7px;top:8px;bottom:8px;width:2px;background:var(--border);z-index:-1}
        .tl-item{position:relative;margin-bottom:20px;padding-left:24px}
        .tl-item:last-child{margin-bottom:0}
        .tl-dot{width:14px;height:14px;border-radius:50%;border:2.5px solid #fff;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0}
        .tl-dot.active{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.done{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.warn{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.danger{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.latest{background:#f59e0b;box-shadow:0 0 0 2px #f59e0b}
        .tl-action{font-size:12px;font-weight:700;color:#1b263b}
        .tl-meta{font-size:12px;color:#64748b;margin:2px 0}
        .tl-remarks{font-size:12px;color:#64748b;background:#f8fafc;border-left:3px solid var(--border);padding:5px 9px;border-radius:4px;margin-top:5px}
        .tl-office-hdr{display:flex;align-items:center;font-size:13px;font-weight:700;color:var(--text-dark);text-transform:none;letter-spacing:0;margin:18px 0 8px -7px;padding-left:7px;padding-bottom:6px;position:relative}
        .tl-office-hdr::after{content:'';position:absolute;left:21px;right:0;bottom:0;height:1.5px;background:var(--border)}
        .tl-office-hdr:first-child{margin-top:0}
        .tl-item.voided{opacity:.45}
        .tl-item.voided .tl-action,.tl-item.voided .tl-meta,.tl-item.voided .tl-remarks{text-decoration:line-through;color:#94a3b8}
        .tl-dot.voided{background:#cbd5e1;box-shadow:0 0 0 2px #cbd5e1}
        .tl-void-badge{display:inline-block;font-size:9px;font-weight:700;letter-spacing:.6px;background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;border-radius:4px;padding:1px 5px;margin-left:4px;vertical-align:middle;text-decoration:none !important}
        /* Action panel */
        .action-section{margin-top:16px}
        .action-section h3{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:12px;padding-bottom:8px;border-bottom:1px solid var(--border)}
        .btn{padding:10px 18px;border:none;border-radius:9px;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:background .2s,opacity .2s;width:100%;margin-bottom:10px}
        .btn:disabled{opacity:.6;cursor:not-allowed}
        .btn-accept{background:#16a34a;color:#fff}
        .btn-accept:hover:not(:disabled){background:#15803d}
        .btn-primary{background:var(--primary);color:#fff}
        .btn-primary:hover:not(:disabled){background:var(--primary-dark)}
        .btn-outline{background:#fff;color:var(--text-dark);border:1.5px solid var(--border)}
        .btn-outline:hover:not(:disabled){border-color:var(--primary);color:var(--primary)}
        label.field-label{font-size:11px;font-weight:600;color:#334155;display:block;margin-bottom:4px;margin-top:10px}
        select.field,textarea.field{width:100%;padding:9px 12px;font-family:Poppins,sans-serif;font-size:12px;border:1.5px solid var(--border);border-radius:8px;outline:none;transition:border-color .2s}
        select.field:focus,textarea.field:focus{border-color:var(--primary)}
        textarea.field{resize:vertical;min-height:70px}
        .divider{border:none;border-top:1px solid var(--border);margin:14px 0}
        .alert-info{background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:10px 14px;font-size:12px;color:#1d4ed8;margin-bottom:12px}
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

        @media(max-width:900px){ .main{padding:60px 14px 60px} .two-col{grid-template-columns:1fr} .site-footer{padding:16px 5%;flex-direction:column;gap:6px;text-align:center} }
        @keyframes spin{to{transform:rotate(360deg)}}
        .spinner{width:14px;height:14px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite}
        .site-footer{margin-left:0;width:100%;background:#fff;border-top:1px solid #e2e8f0;padding:20px 28px;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8}
        .site-footer .footer-left{display:flex;align-items:center;gap:6px}
        .site-footer .footer-right{font-size:11px;color:#b0b8c4}

        /* ─── Accept Modal ─── */
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:300;align-items:center;justify-content:center;padding:16px}
        .modal-overlay.show{display:flex}
        .modal{background:#fff;border-radius:16px;max-width:420px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:modalIn .18s ease}
        @keyframes modalIn{from{opacity:0;transform:scale(.96)}to{opacity:1;transform:scale(1)}}
        .modal-head{padding:22px 24px 12px;display:flex;align-items:center;gap:12px}
        .modal-icon{width:42px;height:42px;border-radius:12px;background:#dcfce7;display:flex;align-items:center;justify-content:center;color:#16a34a;font-size:18px;flex-shrink:0}
        .modal-head h3{font-size:16px;font-weight:700;color:var(--text-dark)}
        .modal-body{padding:0 24px 20px;font-size:13px;color:var(--text-muted);line-height:1.7}
        .modal-foot{padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end}
        .modal-btn{padding:9px 18px;border-radius:10px;font-size:13px;font-weight:600;font-family:Poppins,sans-serif;cursor:pointer;border:1.5px solid var(--border);background:#fff;color:var(--text-dark);transition:all .2s}
        .modal-btn:hover{background:#f1f5f9}
        .modal-btn.success{background:#16a34a;color:#fff;border-color:#16a34a}
        .modal-btn.success:hover{background:#15803d}
        .modal-btn.primary{background:var(--primary);color:#fff;border-color:var(--primary)}
        .modal-btn.primary:hover{background:var(--primary-dark)}
        .modal-btn.warning{background:#d97706;color:#fff;border-color:#d97706}
        .modal-btn.warning:hover{background:#b45309}
        .modal-btn:disabled{opacity:.6;cursor:not-allowed}
        .modal-label{font-size:11px;font-weight:600;color:#334155;display:block;margin-bottom:5px;text-transform:uppercase;letter-spacing:.3px}
        .modal-field{width:100%;box-sizing:border-box;padding:9px 12px;font-family:Poppins,sans-serif;font-size:13px;border:1.5px solid var(--border);border-radius:8px;outline:none;transition:border-color .2s;color:var(--text-dark);background:#fff}
        .modal-field:focus{border-color:var(--primary)}
        .modal-err{font-size:12px;color:#dc2626;margin-top:6px;display:none}
        .modal-err.show{display:block}
    </style>
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
</head>
<body>
@php
    $user = auth()->user();
    $isRep = $user->account_type === 'representative';
    $navOfficeName = $isRep ? ($user->representativeOfficeName() ?? 'Office') : null;
    $navRepName = $isRep ? $user->representativeDisplayName() : $user->name;
    $navDisplayName = $navOfficeName ?? $navRepName;
    $initials = collect(explode(' ', trim($navRepName ?: $user->name)))->filter()->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->implode('');
    $canAct = $document->current_office_id === $user->office_id
        || ($document->status === 'submitted' && $document->submitted_to_office_id === $user->office_id);
    // User is the tagged handler, OR document is unassigned (they can claim it)
    $isHandler = !$document->current_handler_id
        || (int)$document->current_handler_id === (int)$user->id;
    $canAccept = $canAct && $document->status === 'submitted';
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
        <a href="/office/search"><i class="fas fa-chart-line"></i> Reports</a>
        <span class="nav-section">My Documents</span>
        <a href="/submit"><i class="fas fa-paper-plane"></i> Submit Document</a>
        <a href="/my-documents"><i class="fas fa-folder"></i> My Documents</a>
        <a href="/track"><i class="fas fa-search"></i> Track Document</a>
        <span class="nav-section">Account</span>
        <a href="/profile"><i class="fas fa-user-cog"></i> My Profile</a>
        @else
        <span class="nav-section">Office</span>
        <a href="/office/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/office/search" id="reports-nav-link" style="{{ $user->hasReportsAccess() ? '' : 'display:none' }}"><i class="fas fa-chart-line"></i> Reports</a>
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
        <button onclick="logout()" class="btn-logout" style="margin-top:8px"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>
</div>

<div class="main">
    @if(request()->query('from') === 'reports')
    <a href="/office/search" class="back-link" aria-label="Back to Reports" title="Back to Reports" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;"><span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></span></a>
    @else
    <a href="/office/dashboard" class="back-link" aria-label="Back to Dashboard" title="Back to Dashboard" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;"><span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></span></a>
    @endif

    <div class="two-col">
        <!-- Left: Document info + timeline -->
        <div>
            <!-- Document header card -->
            <div class="card" style="margin-bottom:18px">
                <div class="card-head">
                    <div>
                        <h2 style="word-wrap:break-word;overflow-wrap:break-word">{{ $document->subject }}</h2>
                        <p style="font-family:monospace;letter-spacing:.5px">{{ $document->tracking_number }}</p>
                    </div>
                    <span class="status-badge" style="
                        background:{{ $document->statusColor() }}1a;
                        color:{{ $document->statusColor() }};
                        border:1.5px solid {{ $document->statusColor() }}55">
                        {{ $document->statusLabel() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="info-grid">

                        <div class="info-item">
                            <div class="label">Contact Number</div>
                            <div class="value">{{ $document->sender_contact ?: 'No contact provided' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Email Address</div>
                            <div class="value"><!--email_off-->{{ $document->sender_email ?: 'No email provided' }}<!--/email_off--></div>
                        </div>

                        @if($document->description)
                        <div class="info-item full">
                            <div class="label">Description / Remarks</div>
                            <div class="value">{{ $document->description }}</div>
                        </div>
                        @endif
                        <div class="info-item">
                            <div class="label">Date Submitted</div>
                            <div class="value">{{ $document->created_at->format('F d, Y h:i A') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Routing timeline -->
            <div class="card">
                <div class="card-head">
                    <h2><i class="fas fa-history" style="color:var(--primary);margin-right:6px"></i> Routing History</h2>
                </div>
                <div class="card-body">
                    @php
                        $logsAsc = $document->routingLogs->sortBy('created_at')->values();
                        $arrivalMetaByLogId = [];
                        $count = $logsAsc->count();
                        $now = now();
                        $formatDuration = function (int $seconds): string {
                            if ($seconds < 60) return $seconds . 's';
                            $days = intdiv($seconds, 86400);
                            $seconds %= 86400;
                            $hours = intdiv($seconds, 3600);
                            $seconds %= 3600;
                            $minutes = intdiv($seconds, 60);
                            $parts = [];
                            if ($days > 0) $parts[] = $days . 'd';
                            if ($hours > 0) $parts[] = $hours . 'h';
                            if ($minutes > 0) $parts[] = $minutes . 'm';
                            return $parts ? implode(' ', array_slice($parts, 0, 3)) : ($seconds . 's');
                        };

                        $segments = [];
                        for ($i = 0; $i < $count; $i++) {
                            $log = $logsAsc[$i];
                            $isSubmissionPending = $log->action === 'submitted' && $log->status_after === 'submitted';
                            $officeId = null;
                            if (!$isSubmissionPending) {
                                if ($log->action === 'forwarded' && $log->from_office_id) {
                                    $officeId = $log->from_office_id;
                                } else {
                                    $officeId = $log->to_office_id ?: $log->from_office_id;
                                }
                            }
                            if (!$officeId) {
                                continue;
                            }

                            if (empty($segments) || $segments[array_key_last($segments)]['office_id'] !== $officeId) {
                                $segments[] = [
                                    'office_id' => $officeId,
                                    'start_index' => $i,
                                    'end_index' => $i,
                                ];
                            } else {
                                $segments[array_key_last($segments)]['end_index'] = $i;
                            }
                        }

                        $officeNameMap = [];
                        if (!empty($segments)) {
                            $officeIds = collect($segments)->pluck('office_id')->unique()->values();
                            $officeNameMap = \App\Models\Office::query()
                                ->whereIn('id', $officeIds)
                                ->pluck('name', 'id')
                                ->all();
                        }

                        $segmentCount = count($segments);
                        for ($segIndex = 0; $segIndex < $segmentCount; $segIndex++) {
                            $segment = $segments[$segIndex];
                            $nextSegment = $segments[$segIndex + 1] ?? null;

                            $startLog = $logsAsc[$segment['start_index']];
                            $endLog = $logsAsc[$segment['end_index']];
                            $timeInAt = $startLog->created_at;
                            $timeOutAt = $nextSegment ? $endLog->created_at : null;
                            $nextInAt = $nextSegment ? $logsAsc[$nextSegment['start_index']]->created_at : null;

                            $officeDurationSeconds = $nextSegment
                                ? max(0, $timeInAt->diffInSeconds($timeOutAt))
                                : max(0, $timeInAt->diffInSeconds($now));

                            $betweenOfficesSeconds = ($nextSegment && $timeOutAt && $nextInAt)
                                ? max(0, $timeOutAt->diffInSeconds($nextInAt))
                                : null;

                            $arrivalMetaByLogId[$startLog->id] = [
                                'office_name' => $officeNameMap[$segment['office_id']] ?? 'Office',
                                'time_in' => $timeInAt->copy()->setTimezone('Asia/Manila')->format('M d, Y h:i A'),
                                'time_out' => $timeOutAt ? $timeOutAt->copy()->setTimezone('Asia/Manila')->format('M d, Y h:i A') : null,
                                'office_duration' => $formatDuration($officeDurationSeconds),
                                'between_offices' => $betweenOfficesSeconds !== null ? $formatDuration($betweenOfficesSeconds) : null,
                                'next_office' => $nextSegment
                                    ? ($officeNameMap[$nextSegment['office_id']] ?? 'Next Office')
                                    : null,
                            ];
                        }
                    @endphp
                    @if($document->routingLogs->isEmpty())
                        <p style="color:var(--text-muted);font-size:13px">No routing history yet.</p>
                    @else
                        @php
                            $voidedLogIds = [];
                            $logsChron = $document->routingLogs->sortBy('created_at')->values();
                            $revertStatuses = ['in_review', 'on_hold'];
                            foreach ($logsChron as $vIdx => $vLog) {
                                if ($vLog->status_after === 'for_pickup') {
                                    // Check if any later log reverts back to an active status
                                    foreach ($logsChron->slice($vIdx + 1) as $laterLog) {
                                        if (in_array($laterLog->status_after, $revertStatuses)) {
                                            $voidedLogIds[$vLog->id] = true;
                                            break;
                                        }
                                    }
                                }
                            }
                            $tlGroups = [];
                            $tlPrevKey = null;
                            foreach ($document->routingLogs->sortBy('created_at') as $tlLog) {
                                if ($tlLog->action === 'submitted') {
                                    $tlKey = '__pending__'; $tlLabel = 'Submitted — Pending Physical Submission';
                                } elseif ($tlLog->action === 'forwarded') {
                                    $tlKey = 'from_' . ($tlLog->from_office_id ?? '0');
                                    $tlLabel = $tlLog->fromOffice?->name ?? 'Office';
                                } else {
                                    $tlKey = 'to_' . ($tlLog->to_office_id ?? ($tlLog->from_office_id ?? '0'));
                                    $tlLabel = $tlLog->toOffice?->name ?? ($tlLog->fromOffice?->name ?? 'Office');
                                }
                                if ($tlKey !== $tlPrevKey) {
                                    $tlPrevKey = $tlKey;
                                    $tlGroups[] = ['label' => $tlLabel, 'logs' => []];
                                }
                                $tlGroups[array_key_last($tlGroups)]['logs'][] = $tlLog;
                            }
                            $tlGroups = array_reverse($tlGroups);
                            $tlFirst = true;
                        @endphp
                        <div class="timeline">
                        @foreach($tlGroups as $tlGroup)
                            @php
                                $hdrLogs = array_values(array_reverse($tlGroup['logs']));
                                $hdrLog = $hdrLogs[0] ?? null;
                                $hdrS = $hdrLog ? $hdrLog->status_after : 'active';
                                $hdrIsVoided = $hdrLog ? isset($voidedLogIds[$hdrLog->id]) : false;
                                $hdrDc = $hdrIsVoided ? 'voided' : ($tlFirst ? 'latest' : match(true){
                                    in_array($hdrS,['cancelled','returned']) => 'danger',
                                    $hdrS === 'completed' => 'done',
                                    default => 'active'
                                });
                                $hdrIcon = $hdrIsVoided ? 'fa-ban' : ($tlFirst ? 'fa-arrow-up' : 'fa-check');
                            @endphp
                            <div class="tl-office-hdr"><div class="tl-dot {{ $hdrDc }}" style="margin-right:5px"><i class="fas {{ $hdrIcon }}" style="font-size:5px"></i></div><span>{{ $tlGroup['label'] }}</span></div>
                            @foreach(array_reverse($tlGroup['logs']) as $tlLog)
                                @php
                                    $s = $tlLog->status_after;
                                    $isLatest = $tlFirst; $tlFirst = false;
                                    $isVoided = isset($voidedLogIds[$tlLog->id]);
                                    $dc = match(true){
                                        in_array($s,['cancelled','returned']) => 'danger',
                                        $s === 'completed' => 'done',
                                        default => 'active'
                                    };
                                @endphp
                                <div class="tl-item{{ $isVoided ? ' voided' : '' }}">
                                    @if($tlLog->performer)
                                        <div class="tl-action">{{ $tlLog->performer->name }}@if($isVoided) <span class="tl-void-badge">VOIDED</span>@endif</div>
                                    @elseif($isVoided)
                                        <div class="tl-action"><span class="tl-void-badge">VOIDED</span></div>
                                    @endif
                                    <div class="tl-meta"><i class="fas fa-clock" style="margin-right:3px;font-size:10px"></i>{{ $tlLog->created_at->setTimezone('Asia/Manila')->format('M d, Y h:i A') }}</div>
                                    <div class="tl-meta"><i class="fas fa-tasks" style="margin-right:3px;font-size:10px"></i>{{ $tlLog->actionLabel() }}</div>
                                    @php
                                        $tlRemarks = $tlLog->action === 'submitted'
                                            ? 'Document submitted online. Awaiting physical submission to Records Section.'
                                            : $tlLog->remarks;
                                    @endphp
                                    @if($tlRemarks)<div class="tl-remarks">{{ $tlRemarks }}</div>@endif
                                </div>
                            @endforeach
                        @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Action panel -->
        <div>
            <div class="card">
                <div class="card-head">
                    <h2>Actions</h2>
                </div>
                <div class="card-body">
                    @if(!$canAct)
                        <div class="alert-info">
                            <i class="fas fa-info-circle" style="margin-right:5px"></i>
                            This document is currently at <strong>{{ $document->currentOffice?->name ?? 'another office' }}</strong>.
                            You can only act on documents at your office.
                        </div>
                    @elseif($canAct && !$isHandler)
                        <div class="alert-info">
                            <i class="fas fa-tag" style="margin-right:5px"></i>
                            This document is currently tagged to <strong>{{ $document->currentHandler->name }}</strong>.
                            Only the assigned handler can update its status.
                        </div>
                    @else
                        {{-- Accept --}}
                        @if($canAccept)
                            <div class="action-section">
                                <h3><i class="fas fa-check-circle" style="margin-right:5px"></i>Accept Document</h3>
                                <p style="font-size:12px;color:var(--text-muted);margin-bottom:12px">
                                    Confirm receipt of this document at your office.
                                </p>
                                <button class="btn btn-accept" id="acceptBtn" onclick="doAccept()">
                                    <i class="fas fa-check"></i> Accept Document
                                </button>
                            </div>
                            <hr class="divider">
                        @endif

                        @if(($user->isRecords() || $user->isSuperAdmin()) && in_array($document->status, ['in_review','for_pickup']))
                            {{-- Update status — Records / SuperAdmin only --}}
                            <div class="action-section">
                                <h3><i class="fas fa-tag" style="margin-right:5px"></i>Update Status</h3>
                                <p style="font-size:12px;color:var(--text-muted);margin-bottom:12px">Update the current processing status of this document.</p>
                                <button class="btn btn-outline" onclick="openStatusModal()">
                                    <i class="fas fa-edit"></i> Update Status
                                </button>
                            </div>

                            {{-- Confirm Pickup --}}
                            @if($document->status === 'for_pickup')
                                <hr class="divider">
                                <div class="action-section">
                                    <h3>Confirm Pickup</h3>
                                    <p style="font-size:12px;color:var(--text-muted);margin-bottom:12px">
                                        This document is ready for pickup. Confirm once the recipient has actually claimed it.
                                    </p>
                                    <button class="btn" style="background:#ea580c;color:#fff" onclick="openPickupModal()">
                                        Mark as Picked Up
                                    </button>
                                </div>
                            @endif
                        @endif

                        @if(!$canAccept && !(($user->isRecords() || $user->isSuperAdmin()) && in_array($document->status, ['in_review','for_pickup'])))
                            <p style="color:var(--text-muted);font-size:13px;text-align:center;padding:10px 0">
                                No actions available for the current status.
                            </p>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var docId = {{ $document->id }};

function doAccept(){
    document.getElementById('acceptModal').classList.add('show');
}
function closeAcceptModal(){
    document.getElementById('acceptModal').classList.remove('show');
}
async function confirmAccept(){
    var btn = document.getElementById('confirmAcceptBtn');
    btn.disabled = true;
    var res = await apiFetch('/api/office/documents/'+docId+'/accept', {});
    if(res.success){ location.reload(); }
    else{ alert(res.message||'Failed'); btn.disabled = false; closeAcceptModal(); }
}

var STATUS_REMARKS = {
    'for_pickup': ['Document is ready for pickup.'],
    'returned':   ['Returned due to incomplete requirements.','Returned for correction — please resubmit.'],
};
function updateStatusRemarks(){
    var status = document.getElementById('newStatus').value;
    var sel = document.getElementById('statusRemarksSelect');
    var ta  = document.getElementById('statusRemarks');
    ta.style.display='none'; ta.value='';
    sel.innerHTML = '<option value="">Select a remark…</option>';
    (STATUS_REMARKS[status]||[]).forEach(function(r){
        var o=document.createElement('option'); o.value=r; o.textContent=r; sel.appendChild(o);
    });
    var cu=document.createElement('option'); cu.value='__custom'; cu.textContent='Custom Remark…'; sel.appendChild(cu);
    sel.value='';
}
function openStatusModal(){
    document.getElementById('newStatus').value='';
    updateStatusRemarks();
    document.getElementById('statusRemarks').value=''; document.getElementById('statusRemarks').style.display='none';
    hideErr('statusError');
    document.getElementById('statusModal').classList.add('show');
}
function closeStatusModal(){
    document.getElementById('statusModal').classList.remove('show');
}
async function confirmUpdateStatus(){
    var status  = document.getElementById('newStatus').value;
    var remarks = getRemarksValue('statusRemarksSelect','statusRemarks');
    if(!status){ showErr('statusError','Please select a new status.'); return; }
    hideErr('statusError');
    var btn = document.getElementById('confirmStatusBtn');
    btn.disabled = true;
    var res = await apiFetch('/api/office/documents/'+docId+'/status', {status:status,remarks:remarks||null});
    if(res.success){ location.reload(); }
    else{ showErr('statusError', res.message||'Failed to update status.'); btn.disabled = false; }
}

function openPickupModal(){
    document.getElementById('pickupRemarksSelect').value='';
    document.getElementById('pickupRemarks').value=''; document.getElementById('pickupRemarks').style.display='none';
    document.getElementById('pickupModal').classList.add('show');
}
function closePickupModal(){
    document.getElementById('pickupModal').classList.remove('show');
}
async function confirmPickup(){
    var remarks = getRemarksValue('pickupRemarksSelect','pickupRemarks');
    var btn = document.getElementById('confirmPickupBtn');
    btn.disabled = true;
    var res = await apiFetch('/api/office/documents/'+docId+'/status', {status:'completed',remarks:remarks||'Document picked up by recipient.'});
    if(res.success){ location.reload(); }
    else{ alert(res.message||'Failed to confirm pickup.'); btn.disabled = false; closePickupModal(); }
}

function handleRemarksDropdown(selectId, textareaId){
    var sel=document.getElementById(selectId); var ta=document.getElementById(textareaId);
    if(sel.value==='__custom'){ ta.style.display=''; ta.value=''; ta.focus(); }
    else{ ta.style.display='none'; ta.value=''; }
}
function getRemarksValue(selectId, textareaId){
    var sel=document.getElementById(selectId); var ta=document.getElementById(textareaId);
    return sel.value==='__custom' ? ta.value.trim() : sel.value;
}
function showErr(id, msg){ var el=document.getElementById(id); el.textContent=msg; el.classList.add('show'); }
function hideErr(id){ var el=document.getElementById(id); el.textContent=''; el.classList.remove('show'); }

async function apiFetch(url, body){
    try{
        var res = await fetch(url,{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
            body: JSON.stringify(body)
        });
        return await res.json();
    }catch(e){ return {success:false,message:'Network error'}; }
}
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
function logout(){
    var csrf=document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/api/logout',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}})
        .then(function(){window.location.href='/login';})
        .catch(function(){window.location.href='/login';});
}
</script>

    <!-- Accept Modal -->
    <div class="modal-overlay" id="acceptModal" onclick="if(event.target===this)closeAcceptModal()">
        <div class="modal">
            <div class="modal-head">
                <div class="modal-icon"><i class="fas fa-check"></i></div>
                <h3>Accept Document</h3>
            </div>
            <div class="modal-body">
                <p>You are about to accept this document. This will confirm receipt at your office and begin <strong style="color:var(--text-dark)">Processing</strong>.</p>
            </div>
            <div class="modal-foot">
                <button class="modal-btn" onclick="closeAcceptModal()">Cancel</button>
                <button class="modal-btn success" id="confirmAcceptBtn" onclick="confirmAccept()">
                    <i class="fas fa-check"></i> Confirm Accept
                </button>
            </div>
        </div>
    </div>

    <!-- Confirm Pickup Modal -->
    <div class="modal-overlay" id="pickupModal" onclick="if(event.target===this)closePickupModal()">
        <div class="modal" style="max-width:460px">
            <div class="modal-head">
                <h3>Confirm Document Pickup</h3>
            </div>
            <div class="modal-body">
                <p style="margin-bottom:14px">Has the recipient <strong>actually claimed</strong> this document? This will mark the document as <strong style="color:#15803d">Completed</strong>.</p>
                <label class="modal-label">Remarks <span style="color:#94a3b8;font-weight:400">(optional)</span></label>
                <select class="modal-field" id="pickupRemarksSelect" onchange="handleRemarksDropdown('pickupRemarksSelect','pickupRemarks')">
                    <option value="">Select a remark…</option>
                    <option value="Document picked up by recipient.">Document picked up by recipient.</option>
                    <option value="Document claimed by authorized representative.">Document claimed by authorized representative.</option>
                    <option value="__custom">Custom Remark…</option>
                </select>
                <textarea class="modal-field" id="pickupRemarks" placeholder="Type your custom remark..." style="min-height:70px;resize:vertical;display:none;margin-top:8px" data-no-capitalize></textarea>
            </div>
            <div class="modal-foot">
                <button class="modal-btn" onclick="closePickupModal()">No, Not Yet</button>
                <button class="modal-btn success" id="confirmPickupBtn" onclick="confirmPickup()">
                    Yes, Mark as Picked Up
                </button>
            </div>
        </div>
    </div>

    <!-- Update Status Modal -->
    <div class="modal-overlay" id="statusModal" onclick="if(event.target===this)closeStatusModal()">
        <div class="modal" style="max-width:480px">
            <div class="modal-head">
                <div class="modal-icon" style="background:#fffbeb;color:#d97706"><i class="fas fa-tag"></i></div>
                <h3>Update Status</h3>
            </div>
            <div class="modal-body">
                <p style="margin-bottom:14px">Select the new status and optionally add remarks for this update.</p>
                <label class="modal-label">New Status <span style="color:#dc2626">*</span></label>
                <select class="modal-field" id="newStatus" onchange="updateStatusRemarks()">
                    <option value="">Select status...</option>
                    <option value="for_pickup">For Pickup (Ready to Claim)</option>
                    <option value="returned">Return to Sender</option>
                </select>
                <label class="modal-label" style="margin-top:12px">Remarks <span style="color:#94a3b8;font-weight:400">(optional)</span></label>
                <select class="modal-field" id="statusRemarksSelect" onchange="handleRemarksDropdown('statusRemarksSelect','statusRemarks')">
                    <option value="">Select a remark…</option>
                </select>
                <textarea class="modal-field" id="statusRemarks" placeholder="Type your custom remark..." style="min-height:70px;resize:vertical;display:none;margin-top:8px" data-no-capitalize></textarea>
                <div class="modal-err" id="statusError"></div>
            </div>
            <div class="modal-foot">
                <button class="modal-btn" onclick="closeStatusModal()">Cancel</button>
                <button class="modal-btn warning" id="confirmStatusBtn" onclick="confirmUpdateStatus()">
                    <i class="fas fa-save"></i> Confirm Update
                </button>
            </div>
        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-left">
            <span>&copy; {{ date('Y') }} DepEd Document Tracking System</span>
        </div>
        <div class="footer-right">
            Developed by Raymond Bautista
        </div>
    </footer>
</body>
</html>
