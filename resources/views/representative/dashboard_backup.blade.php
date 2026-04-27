<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Representative Dashboard - DepEd DTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root{--primary:#0056b3;--primary-dark:#004494;--bg:#f0f2f5;--border:#e2e8f0;--text-dark:#1b263b;--text-muted:#64748b}
        *{margin:0;padding:0;box-sizing:border-box}
        body{background:var(--bg);font-family:Poppins,sans-serif;min-height:100vh;display:flex;flex-direction:column}
        /* Sidebar */
        .sidebar{position:fixed;top:0;left:0;width:240px;height:100vh;background:#0056b3;display:flex;flex-direction:column;z-index:200;transform:translateX(-100%);transition:transform .25s ease}
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
        .btn-logout:hover{background:rgba(255,255,255,.2)}
        /* Main */
        .main{margin-left:0;padding:60px 28px 50px;flex:1}
        .page-header{margin-bottom:24px}
        .page-header-top{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap}
        .page-header-top h1{font-size:20px;font-weight:700;color:var(--text-dark)}
        .page-header-top p{font-size:13px;color:var(--text-muted);margin-top:3px}
        .live-clock{display:flex;align-items:center;gap:14px;background:#fff;padding:10px 18px;border-radius:8px;border:1px solid var(--border);flex-shrink:0}
        .clock-time-display{font-size:18px;font-weight:600;color:var(--text-dark);font-variant-numeric:tabular-nums;line-height:1;white-space:nowrap}
        #c-h,#c-m{display:inline-block;width:2ch;text-align:center}
        .clock-time-display .seconds{font-size:14px;color:#9ca3af;font-weight:600;display:inline-block;width:2ch;text-align:center}
        .clock-time-display .period{font-size:11px;font-weight:600;color:var(--text-muted);margin-left:3px;vertical-align:top}
        .clock-sep{width:1px;height:28px;background:var(--border)}
        .clock-date-display{font-size:13px;color:var(--text-muted);font-weight:400;line-height:1.4}
        .clock-date-display .day{font-weight:600;color:var(--text-dark);display:block}
        /* Stats */
        .stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:28px}
        .stat-card{background:#fff;border-radius:14px;padding:18px 20px;box-shadow:0 2px 12px rgba(0,0,0,.05);display:flex;align-items:center;gap:14px}
        .stat-icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
        .stat-info h3{font-size:22px;font-weight:700;color:var(--text-dark);line-height:1}
        .stat-info p{font-size:11px;color:var(--text-muted);margin-top:3px}
        /* Table card */
        .table-card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.05);overflow:hidden}
        .dashboard-table-card.has-list{display:flex;flex-direction:column;max-height:clamp(520px,72vh,820px)}
        .dashboard-table-card.has-list .table-scroll{flex:1;min-height:0;overflow:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch}
        .dashboard-table-card.has-list .table-scroll thead th{position:sticky;top:0;z-index:2}
        .dashboard-table-card.has-list .empty-state{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center}
        .table-head{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
        .table-title{font-size:17px;font-weight:700;color:var(--text-dark)}
        .filters{display:flex;gap:10px;align-items:center;flex-wrap:nowrap}
        .search-wrap{position:relative;display:flex;align-items:center;flex:1;min-width:0}
        .search-wrap i{position:absolute;left:11px;color:#94a3b8;font-size:13px;pointer-events:none;z-index:1}
        .filters input{padding:8px 12px 8px 34px;font-family:Poppins,sans-serif;font-size:13px;border:1.5px solid var(--border);border-radius:9px;outline:none;transition:border-color .2s,box-shadow .2s;width:100%;color:var(--text-dark);background:#fff}
        .filters input::placeholder{color:#94a3b8;font-size:12px}
        .filters input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,86,179,.1)}
        .filters select{padding:8px 32px 8px 12px;font-family:Poppins,sans-serif;font-size:13px;border:1.5px solid var(--border);border-radius:9px;outline:none;transition:border-color .2s,box-shadow .2s;color:var(--text-dark);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%2394a3b8' d='M5 7L0 2h10z'/%3E%3C/svg%3E") no-repeat right 11px center;-webkit-appearance:none;appearance:none;cursor:pointer;min-width:150px}
        .filters select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,86,179,.1)}
        table{width:100%;border-collapse:collapse}
        th{text-align:left;padding:11px 16px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted);border-bottom:1px solid var(--border);background:#f8fafc}
        td{padding:12px 16px;font-size:13px;color:var(--text-dark);border-bottom:1px solid #f1f5f9;vertical-align:middle}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#fafbff}
        .cell-ellipsis{display:block;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .badge{padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px}
        .badge-submitted,
        .badge-received,
        .badge-in_review,
        .badge-forwarded,
        .badge-completed,
        .badge-for_pickup,
        .badge-returned,
        .badge-cancelled{background:#fff7ed;color:#c2410c}
        .btn-view{padding:5px 13px;background:var(--primary);color:#fff;border:none;border-radius:7px;font-size:11px;font-weight:600;cursor:pointer;font-family:Poppins,sans-serif;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:background .2s}
        .btn-view:hover{background:var(--primary-dark)}
        .btn-accept{padding:5px 11px;background:#16a34a;color:#fff;border:none;border-radius:7px;font-size:11px;font-weight:600;cursor:pointer;font-family:Poppins,sans-serif;transition:background .2s;display:inline-flex;align-items:center;gap:5px}
        .btn-accept:hover{background:#15803d}
        .empty-state{text-align:center;padding:50px 20px;color:var(--text-muted)}
        .empty-state i{font-size:40px;color:#cbd5e1;margin-bottom:12px;display:block}
        .empty-state h3{font-size:15px;font-weight:600;color:#94a3b8;margin-bottom:6px}
        .empty-state p{font-size:12px}
        /* ─── Mobile sidebar ─── */
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
        @media(max-width:900px){
            .main{padding:68px 14px 50px}
            .stats-grid{grid-template-columns:1fr 1fr}
            .page-header-top{flex-direction:column;align-items:flex-start;gap:10px}
            .live-clock{display:none}
            .dashboard-table-card.has-list{max-height:min(68vh,560px)}
            .table-head{padding:14px 16px;flex-direction:column;align-items:stretch;gap:8px}
            .table-title{font-size:15px}
            .filters{flex-direction:column;gap:6px}
            .filters input{font-size:12px;padding:8px 12px 8px 32px}
            .filters input::placeholder{font-size:11px}
            .filters select{font-size:12px;padding:8px 28px 8px 10px;min-width:0;width:100%}
            .table-scroll{overflow-x:auto;-webkit-overflow-scrolling:touch}
            table{min-width:700px}
            th{padding:9px 12px;font-size:9px}
            td{padding:10px 12px;font-size:12px}
            .btn-view{font-size:10px;padding:4px 10px}
            .btn-accept{font-size:10px;padding:4px 9px}
        }
        @keyframes spin{to{transform:rotate(360deg)}}
        .spinner{display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite}
        .site-footer{margin-left:0;width:100%;background:#fff;border-top:1px solid #e2e8f0;padding:20px 28px;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8}
        .site-footer .footer-left{display:flex;align-items:center;gap:6px}
        .site-footer .footer-right{font-size:11px;color:#b0b8c4}
        @media(max-width:900px){.site-footer{padding:16px 5%;flex-direction:column;gap:6px;text-align:center}}
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
    </style>
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
</head>
<body>
@php
    $user = auth()->user();
    $isRep = $user->account_type === 'representative';
    $navOfficeName = $isRep ? ($user->representativeOfficeName() ?? 'Representative') : null;
    $navRepName = $isRep ? $user->representativeDisplayName() : $user->name;
    $navDisplayName = $navOfficeName ?? $navRepName;
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
<!-- Sidebar -->
<div class="sidebar" id="mainSidebar">
    <div class="sb-brand">
        <img src="{{ asset('images/DOCTRAXLOGO.svg') }}" alt="DOCTRAX Logo">
        <h2>DOCTRAX</h2>
        <small>DepEd Document Tracking System</small>
    </div>
    <nav class="sb-nav">
        <span class="nav-section">Representative</span>
        <a href="/representative/dashboard" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/representative/search"><i class="fas fa-search"></i> Search Documents</a>
        <span class="nav-section">Account</span>
        <a href="/profile"><i class="fas fa-user-circle"></i> My Profile</a>
    </nav>
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">{{ $initials }}</div>
            <div class="sb-user-info">
                <small>{{ $navOfficeName ?? 'Representative' }}</small>
                <span>{{ $navRepName ?? $navDisplayName }}</span>
            </div>
        </div>
        <button onclick="logout()" class="btn-logout" style="margin-top:8px"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>
</div>

<!-- Main content -->
<div class="main">
    <div class="page-header">
        <div class="page-header-top">
            <div>
                <h1>Welcome back, {{ $navRepName ?? $navDisplayName }}!</h1>
                <p>{{ $navOfficeName ?? 'Office' }} &mdash; here's your document queue.</p>
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

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff;color:#2563eb"><i class="fas fa-inbox"></i></div>
            <div class="stat-info">
                <h3>{{ \App\Support\UiNumber::compact($stats['incoming']) }}</h3>
                <p>Incoming / Pending</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff;color:#2563eb"><i class="fas fa-microscope"></i></div>
            <div class="stat-info">
                <h3>{{ \App\Support\UiNumber::compact($stats['in_review']) }}</h3>
                <p>Processing</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#eff6ff;color:#2563eb"><i class="fas fa-flag-checkered"></i></div>
            <div class="stat-info">
                <h3>{{ \App\Support\UiNumber::compact($stats['completed']) }}</h3>
                <p>Completed</p>
            </div>
        </div>
    </div>

    <!-- Documents table -->
    <div class="table-card dashboard-table-card{{ $documents->isNotEmpty() ? ' has-list' : '' }}">
        <div class="table-head">
            <span class="table-title">Document Queue</span>
            <div class="filters">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search subject or sender..." data-clearable data-no-capitalize oninput="filterTable()">
                </div>
                <select id="statusFilter" onchange="filterTable()">
                    <option value="">All Statuses</option>
                    <option value="submitted">Submitted</option>
                    <option value="in_review">Processing</option>
                    <option value="forwarded">Forwarded</option>
                    <option value="completed">Completed</option>
                    <option value="for_pickup">For Pickup</option>
                    <option value="returned">Returned</option>
                </select>
            </div>
        </div>

        @if($documents->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Documents Yet</h3>
                <p>Documents submitted to or currently held by your office will appear here.</p>
            </div>
        @else
            <div class="table-scroll">
            <table id="docsTable">
                <thead>
                    <tr>
                        <th>Tracking No.</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Sender</th>
                        <th>Status</th>
                        <th>Last Action</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($documents as $doc)
                    <tr data-status="{{ $doc->status }}" data-search="{{ strtolower($doc->subject . ' ' . $doc->sender_name) }}">
                        <td style="font-family:monospace;font-size:12px;font-weight:600;color:var(--primary)">
                            {{ $doc->reference_number }}
                        </td>
                        <td style="max-width:200px">
                            <div class="cell-ellipsis" style="font-weight:600" title="{{ $doc->subject }}">{{ $doc->subject }}</div>
                        </td>
                        <td style="font-size:12px;white-space:nowrap"><div class="cell-ellipsis" style="max-width:150px" title="{{ $doc->type }}">{{ $doc->type }}</div></td>
                        <td style="font-size:12px"><div class="cell-ellipsis" style="max-width:160px" title="{{ $doc->sender_name }}">{{ $doc->sender_name }}</div></td>
                        <td>
                            <span class="badge badge-{{ $doc->status }}">
                                {{ $doc->statusLabel() }}
                            </span>
                        </td>
                        <td style="font-size:11px;color:var(--text-muted)">
                            {{ $doc->last_action_at ? \Carbon\Carbon::parse($doc->last_action_at)->diffForHumans() : $doc->created_at->diffForHumans() }}
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;align-items:center">
                                @if(in_array($doc->status, ['submitted', 'forwarded']))
                                    <button class="btn-accept" onclick="quickAccept({{ $doc->id }}, this)">
                                        <i class="fas fa-check"></i> Accept
                                    </button>
                                @endif
                                <a href="/representative/documents/{{ $doc->id }}" class="btn-view">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        @endif
    </div>
</div>

<script>
var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function filterTable(){
    var q      = document.getElementById('searchInput').value.toLowerCase();
    var status = document.getElementById('statusFilter').value;
    document.querySelectorAll('#docsTable tbody tr').forEach(function(row){
        var matchSearch = !q || row.dataset.search.includes(q);
        var matchStatus = !status || row.dataset.status === status;
        row.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });
}

var _pendingAcceptId = null, _pendingAcceptBtn = null;
function quickAccept(docId, btn){
    _pendingAcceptId = docId;
    _pendingAcceptBtn = btn;
    document.getElementById('acceptModal').classList.add('show');
}
function closeAcceptModal(){
    document.getElementById('acceptModal').classList.remove('show');
    _pendingAcceptId = null;
    _pendingAcceptBtn = null;
}
async function confirmAccept(){
    var btn = document.getElementById('confirmAcceptBtn');
    btn.disabled = true;
    try{
        var res = await fetch('/api/representative/documents/'+_pendingAcceptId+'/accept',{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
            body:'{}'
        });
        var data = await res.json();
        if(data.success){ location.reload(); }
        else{
            alert(data.message||'Failed');
            btn.disabled = false;
            closeAcceptModal();
        }
    }catch(e){
        alert('Error. Please try again.');
        btn.disabled = false;
        closeAcceptModal();
    }
}
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
// ─── Live clock ───
(function(){
    var clockInterval;
    function tick(){
        var n=new Date();
        var h=n.getHours(),m=n.getMinutes(),s=n.getSeconds();
        var p=h>=12?'PM':'AM',h12=h%12||12;
        var el=document.getElementById('c-h');if(!el){clearInterval(clockInterval);return;}
        el.textContent=String(h12).padStart(2,'0');
        document.getElementById('c-m').textContent=String(m).padStart(2,'0');
        document.getElementById('c-s').textContent=String(s).padStart(2,'0');
        document.getElementById('c-p').textContent=p;
        var days=['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        var mos=['January','February','March','April','May','June','July','August','September','October','November','December'];
        document.getElementById('c-day').textContent=days[n.getDay()];
        document.getElementById('c-date').textContent=mos[n.getMonth()]+' '+n.getDate()+', '+n.getFullYear();
    }
    tick();clockInterval=setInterval(tick,1000);
})();
</script>

    <!-- Accept Confirmation Modal -->
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
