<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Document Detail — All Documents</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="/js/request-utils.js" defer></script>

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
        .timeline{position:relative;margin-top:4px}
        .timeline::before{content:'';position:absolute;left:7px;top:8px;bottom:8px;width:2px;background:var(--border);z-index:-1}
        .tl-item{position:relative;margin-bottom:20px;padding-left:24px}
        .tl-item:last-child{margin-bottom:0}
        .tl-dot{width:14px;height:14px;border-radius:50%;border:2.5px solid #fff;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0}
        .tl-dot.active{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.done{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.latest{background:#f59e0b;box-shadow:0 0 0 2px #f59e0b}
        .tl-dot.danger{background:#dc2626;box-shadow:0 0 0 2px #dc2626}
        .tl-action{font-size:12px;font-weight:700;color:#1b263b}
        .tl-meta{font-size:12px;color:#64748b;margin:2px 0}
        .tl-remarks{font-size:12px;color:#64748b;background:#f8fafc;border-left:3px solid var(--border);padding:5px 9px;border-radius:4px;margin-top:5px}
        .tl-office-hdr{display:flex;align-items:center;font-size:13px;font-weight:700;color:var(--text-dark);text-transform:none;letter-spacing:0;margin:18px 0 8px -7px;padding-left:7px;padding-bottom:6px;position:relative}
        .tl-office-hdr::after{content:'';position:absolute;left:21px;right:0;bottom:0;height:1.5px;background:var(--border)}
        .tl-office-hdr:first-child{margin-top:0}
        .tl-dur{font-size:10px;font-weight:600;color:#6366f1;background:#eef2ff;border:1px solid #c7d2fe;border-radius:20px;padding:1px 8px;text-transform:none;letter-spacing:0;white-space:nowrap;flex-shrink:0;margin-left:auto}
        .archive-badge{display:inline-flex;align-items:center;gap:6px;background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;padding:8px 14px;border-radius:8px;font-size:12px;font-weight:600;margin-bottom:14px}
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
        @media(max-width:900px){ .main{padding-top:52px} .two-col{grid-template-columns:1fr} }
        .site-footer{margin-left:0;width:100%;background:#fff;border-top:1px solid #e2e8f0;padding:20px 28px;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8}
        .site-footer .footer-left{display:flex;align-items:center;gap:6px}
        .site-footer .footer-right{font-size:11px;color:#b0b8c4}
        @media(max-width:900px){.site-footer{padding:16px 5%;flex-direction:column;gap:6px;text-align:center}}
    </style>
    <script src="/js/spa.js" defer></script>
</head>
<body>
@php
    $initials = collect(explode(' ', trim($user->name)))->filter()->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->implode('');
    $isSuperAdmin = $user->isSuperAdmin();
    $roleName = $isSuperAdmin ? 'Super Admin' : 'Records Section';
    $displayName = $user->name;
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

<!-- Sidebar -->
<div class="sidebar" id="mainSidebar">
    <div class="sb-brand">
        <img src="{{ asset('images/DOCTRAXLOGO.svg') }}" alt="DOCTRAX Logo">
        <h2>DOCTRAX</h2>
        <small>DepEd Document Tracking System</small>
    </div>
    <nav class="sb-nav">
        @if($user->isRepresentative() && $user->office_id && !$user->isAdmin())
        <span class="nav-section">Office</span>
        <a href="/office/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/office/search" id="reports-nav-link" style="{{ $user->hasReportsAccess() ? '' : 'display:none' }}"><i class="fas fa-chart-line"></i> Reports</a>
        @endif
        <span class="nav-section">{{ $roleName }}</span>
        <a href="/records/documents"><i class="fas fa-folder-open"></i> All Documents</a>
        @if($isSuperAdmin)
            <span class="nav-section">Administration</span>
            <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Admin Dashboard</a>
            <a href="/admin/users"><i class="fas fa-users"></i> Users</a>
            <a href="/admin/offices"><i class="fas fa-building"></i> Offices</a>
            <span class="nav-section">ICT Unit</span>
            <a href="/ict/documents"><i class="fas fa-network-wired"></i> ICT Documents</a>
        @endif
        <span class="nav-section">My Documents</span>
        <a href="/submit"><i class="fas fa-paper-plane"></i> Submit Document</a>
        <a href="/my-documents"><i class="fas fa-folder"></i> My Documents</a>
        <span class="nav-section">Account</span>
        <a href="/profile"><i class="fas fa-user-circle"></i> My Profile</a>
    </nav>
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">{{ $initials }}</div>
            <div class="sb-user-info">
                <small>{{ $roleName }}</small>
                <span>{{ $displayName }}</span>
            </div>
        </div>
        <button onclick="logout()" class="btn-logout" style="margin-top:8px"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>
</div>

<div class="main">
    <a href="/records/documents" class="back-link" aria-label="Back to All Documents" title="Back to All Documents" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;"><span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></span></a>

    @if($document->status === 'archived')
        <div class="archive-badge">
            <i class="fas fa-archive"></i>
            This document was auto-archived on {{ $document->archived_at ? $document->archived_at->format('M d, Y h:i A') : 'N/A' }} — it was not received within 7 days of submission.
        </div>
    @endif

    <div class="two-col">
        <!-- Left: Document info + timeline -->
        <div>
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
                            <div class="label">Tracking Number</div>
                            <div class="value" style="font-family:monospace">{{ $document->reference_number ?: 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Internal Reference</div>
                            <div class="value" style="font-family:monospace">{{ $document->tracking_number }}</div>
                        </div>

                        <div class="info-item">
                            <div class="label">Sender Contact</div>
                            <div class="value">{{ $document->sender_contact ?? 'No contact provided' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Date Submitted</div>
                            <div class="value">{{ $document->created_at->format('M d, Y h:i A') }}</div>
                        </div>
                        <div class="info-item">
                            <div class="label">Last Action</div>
                            <div class="value">{{ $document->last_action_at ? $document->last_action_at->format('M d, Y h:i A') : 'No activity yet' }}</div>
                        </div>
                        @if($document->description)
                        <div class="info-item full">
                            <div class="label">Description</div>
                            <div class="value">{{ $document->description }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Routing History -->
            <div class="card">
                <div class="card-head">
                    <h2><i class="fas fa-history" style="color:var(--primary);margin-right:6px"></i> Routing History</h2>
                </div>
                <div class="card-body">
                    @php
                        $timelineLogs = $document->routingLogs
                            ->sortBy(function ($log) {
                                return sprintf('%s-%010d', $log->created_at?->format('YmdHis') ?? '00000000000000', (int) $log->id);
                            })
                            ->values();
                        $segmentDurationsByStartLogId = [];
                        $timelineNow = now();
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
                        $logCount = $timelineLogs->count();
                        for ($i = 0; $i < $logCount; $i++) {
                            $log = $timelineLogs->get($i);
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

                        $segmentCount = count($segments);
                        for ($segIndex = 0; $segIndex < $segmentCount; $segIndex++) {
                            $segment = $segments[$segIndex];
                            $nextSegment = $segments[$segIndex + 1] ?? null;
                            $startLog = $timelineLogs->get($segment['start_index']);
                            $timeInAt = $startLog->created_at;
                            $nextInAt = $nextSegment ? $timelineLogs->get($nextSegment['start_index'])->created_at : null;
                            $officeDurationSeconds = $nextInAt !== null
                                ? max(0, $timeInAt->diffInSeconds($nextInAt))
                                : max(0, $timeInAt->diffInSeconds($timelineNow));

                            $segmentDurationsByStartLogId[$startLog->id] = $formatDuration((int) $officeDurationSeconds);
                        }
                    @endphp
                    @if($document->routingLogs->isEmpty())
                        <p style="color:var(--text-muted);font-size:13px">No routing history yet.</p>
                    @else
                        @php
                            $tlGroups = [];
                            $tlPrevKey = null;
                            foreach ($timelineLogs as $tlLog) {
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
                                    $hdrLog = $tlGroup['logs'][0] ?? null;
                                    $hdrS = $hdrLog ? $hdrLog->status_after : 'active';
                                    $hdrDuration = $hdrLog ? ($segmentDurationsByStartLogId[$hdrLog->id] ?? null) : null;
                                    $hdrDc = $tlFirst ? 'latest' : match(true){
                                        in_array($hdrS,['cancelled','returned','archived']) => 'danger',
                                        $hdrS === 'completed' => 'done',
                                        default => 'active'
                                    };
                                    $hdrIcon = $tlFirst ? 'fa-arrow-up' : 'fa-check';
                                @endphp
                                <div class="tl-office-hdr"><div class="tl-dot {{ $hdrDc }}" style="margin-right:5px"><i class="fas {{ $hdrIcon }}" style="font-size:5px"></i></div><span>{{ $tlGroup['label'] }}</span>@if($hdrDuration)<span class="tl-dur"><i class="fas fa-hourglass-half" style="margin-right:4px;font-size:9px"></i>{{ $hdrDuration }}</span>@endif</div>
                                @foreach(array_reverse($tlGroup['logs']) as $tlLog)
                                    @php
                                        $s = $tlLog->status_after;
                                        $isLatest = $tlFirst; $tlFirst = false;
                                        $dc = match(true){
                                            in_array($s,['cancelled','returned','archived']) => 'danger',
                                            $s === 'completed' => 'done',
                                            default => 'active'
                                        };
                                    @endphp
                                    <div class="tl-item">
                                        @if($tlLog->performer)
                                            <div class="tl-action">{{ $tlLog->performer->name }}</div>
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

        <!-- Right: Summary card -->
        <div>
            <div class="card">
                <div class="card-head">
                    <h2>Document Summary</h2>
                </div>
                <div class="card-body">
                    <div style="font-size:13px;color:var(--text-muted);line-height:1.8">
                        <div style="margin-bottom:12px">
                            <strong style="color:var(--text-dark)">Submitted by:</strong> {{ $document->sender_name }}
                        </div>
                        <div style="margin-bottom:12px">
                            <strong style="color:var(--text-dark)">Status:</strong>
                            <span style="color:{{ $document->statusColor() }};font-weight:600">{{ $document->statusLabel() }}</span>
                        </div>
                        <div style="margin-bottom:12px">
                            <strong style="color:var(--text-dark)">Age:</strong>
                            {{ $document->created_at->diffForHumans() }}
                        </div>
                        @if($document->user)
                        <div style="margin-bottom:12px">
                            <strong style="color:var(--text-dark)">Registered User:</strong> {{ $document->user->name }}
                            <div style="font-size:11px;color:#94a3b8"><!--email_off-->{{ $document->user->email }}<!--/email_off--></div>
                        </div>
                        @endif
                        <div style="margin-bottom:12px">
                            <strong style="color:var(--text-dark)">Routing Steps:</strong> {{ $document->routingLogs->count() }}
                        </div>
                    </div>

                    @if($document->status === 'submitted')
                        <div style="margin-top:16px;padding:12px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:12px;color:#92400e">
                            <i class="fas fa-clock" style="margin-right:4px"></i>
                            This document is still awaiting receipt.
                            @if($document->created_at->diffInDays(now()) >= 5)
                                <strong>It will be auto-archived in {{ max(0, 7 - $document->created_at->diffInDays(now())) }} day(s) if not received.</strong>
                            @endif
                        </div>
                    @endif

                    @if($document->status === 'archived')
                        <div style="margin-top:16px;padding:12px 14px;background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;font-size:12px;color:#991b1b">
                            <i class="fas fa-archive" style="margin-right:4px"></i>
                            This document was archived as <strong>unprocessed</strong> because it was not received within 7 days of submission.
                        </div>
                    @endif
                </div>
            </div>
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

<script>
function toggleSidebar(){
    var s=document.getElementById('mainSidebar');
    var o=document.getElementById('mobOverlay');
    var open=s.classList.toggle('open');
    o.classList.toggle('open',open);
    document.body.style.overflow=open?'hidden':'';
    document.getElementById('mobHamBtn').classList.toggle('toggle',open);
}
function closeSidebar(){
    document.getElementById('mainSidebar').classList.remove('open');
    document.getElementById('mobOverlay').classList.remove('open');
    document.body.style.overflow='';
    var btn=document.getElementById('mobHamBtn');if(btn)btn.classList.remove('toggle');
}
function logout(){
    var csrf=document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/api/logout',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}})
        .then(function(){window.location.href='/login';})
        .catch(function(){window.location.href='/login';});
}
</script>
</body>
</html>
