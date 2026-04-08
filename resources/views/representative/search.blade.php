@php
    $user = auth()->user();
    $isRep = $user->account_type === 'representative';
    $navOfficeName = $isRep ? ($user->representativeOfficeName() ?? 'Representative') : null;
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
    <title>Search Documents - DepEd DTS</title>
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
        .main{margin-left:0;padding:60px 28px 50px;flex:1}
        .page-header{margin-bottom:22px}
        .page-header h1{font-size:20px;font-weight:700;color:var(--text-dark)}
        .page-header p{font-size:13px;color:var(--text-muted);margin-top:3px}
        .search-bar-card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.05);padding:20px 22px;margin-bottom:18px}
        .search-form{display:flex;gap:10px;flex-wrap:wrap}
        .search-form input,.search-form select{padding:10px 14px;font-family:Poppins,sans-serif;font-size:13px;border:1.5px solid var(--border);border-radius:9px;outline:none;transition:border-color .2s;flex:1;min-width:160px}
        .search-form input:focus,.search-form select:focus{border-color:var(--primary)}
        .btn-search{padding:10px 22px;background:var(--primary);color:#fff;border:none;border-radius:9px;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:7px;transition:background .2s;white-space:nowrap}
        .btn-search:hover{background:var(--primary-dark)}
        .table-card{background:#fff;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.05);overflow:hidden}
        .table-card.list-table-card.has-list{display:flex;flex-direction:column;max-height:clamp(520px,72vh,820px)}
        .table-card.list-table-card.has-list .table-scroll{flex:1;min-height:0;overflow:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch}
        .table-card.list-table-card.has-list .table-scroll th{position:sticky;top:0;z-index:2}
        .table-card.list-table-card.has-list .empty-state{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center}
        .table-card.list-table-card.has-list .pager-wrap{flex-shrink:0}
        .table-head{padding:16px 22px;border-bottom:1px solid var(--border)}
        .table-head h2{font-size:15px;font-weight:700;color:var(--text-dark)}
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
        .empty-state{text-align:center;padding:50px 20px;color:var(--text-muted)}
        .empty-state i{font-size:40px;color:#cbd5e1;margin-bottom:12px;display:block}
        .empty-state h3{font-size:15px;font-weight:600;color:#94a3b8;margin-bottom:6px}
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

        @media(max-width:900px){ .main{padding:68px 14px 50px} .table-card.list-table-card.has-list{max-height:min(68vh,560px)} .site-footer{padding:16px 5%;flex-direction:column;gap:6px;text-align:center} }
        .site-footer{margin-left:0;width:100%;background:#fff;border-top:1px solid #e2e8f0;padding:20px 28px;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8}
        .site-footer .footer-left{display:flex;align-items:center;gap:6px}
        .site-footer .footer-right{font-size:11px;color:#b0b8c4}

    </style>
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
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
<div class="sidebar" id="mainSidebar">
    <div class="sb-brand">
        <img src="{{ asset('images/DOCTRAXLOGO.svg') }}" alt="DOCTRAX Logo">
        <h2>DOCTRAX</h2>
        <small>DepEd Document Tracking System</small>
    </div>
    <nav class="sb-nav">
        <span class="nav-section">Representative</span>
        <a href="/representative/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/representative/search" class="active"><i class="fas fa-search"></i> Search Documents</a>
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

<div class="main">
    <div class="page-header">
        <h1>Search Documents</h1>
        <p>Search across documents submitted to or currently held by your office.</p>
    </div>

    <div class="search-bar-card">
        <form method="GET" action="/representative/search" class="search-form" data-live-search>
            <input type="text" name="search" value="{{ request('search') }}" data-clearable data-no-capitalize
                   placeholder="Tracking number, subject, sender name...">
            <select name="status">
                <option value="">All Statuses</option>
                @foreach(['submitted','in_review','forwarded','completed','for_pickup','returned'] as $s)
                    <option value="{{ $s }}" {{ request('status') === $s ? 'selected' : '' }}>
                        {{ ucwords(str_replace('_',' ',$s)) }}
                    </option>
                @endforeach
            </select>
            <button type="submit" class="btn-search"><i class="fas fa-search"></i> Search</button>
        </form>
    </div>

    <div class="table-card list-table-card{{ $documents->isNotEmpty() ? ' has-list' : '' }}">
        <div class="table-head">
            <h2>
                <i class="fas fa-list" style="color:var(--primary);margin-right:6px"></i>
                Results
                @if(request('search') || request('status'))
                    <span style="font-size:12px;color:var(--text-muted);font-weight:400;margin-left:8px">
                        — {{ \App\Support\UiNumber::compact($documents->total()) }} document(s) found
                    </span>
                @endif
            </h2>
        </div>

        @if($documents->isEmpty())
            <div class="empty-state">
                <i class="fas fa-search"></i>
                <h3>No Results Found</h3>
                <p>Try a different keyword or clear the filters.</p>
            </div>
        @else
            <div class="table-scroll">
            <table>
                <thead>
                    <tr>
                        <th>Tracking No.</th>
                        <th>Subject</th>
                        <th>Type</th>
                        <th>Sender</th>
                        <th>Status</th>
                        <th>Currently At</th>
                        <th>Last Action</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                @foreach($documents as $doc)
                    <tr>
                        <td style="font-family:monospace;font-size:12px;font-weight:600;color:var(--primary)">
                            {{ $doc->reference_number }}
                        </td>
                        <td style="max-width:180px">
                            <div class="cell-ellipsis" style="font-weight:600" title="{{ $doc->subject }}">{{ $doc->subject }}</div>
                        </td>
                        <td style="font-size:12px"><div class="cell-ellipsis" style="max-width:150px" title="{{ $doc->type }}">{{ $doc->type }}</div></td>
                        <td style="font-size:12px"><div class="cell-ellipsis" style="max-width:160px" title="{{ $doc->sender_name }}">{{ $doc->sender_name }}</div></td>
                        <td>
                            <span class="badge badge-{{ $doc->status }}">{{ $doc->statusLabel() }}</span>
                        </td>
                        <td style="font-size:12px">{{ $doc->currentOffice?->name ?? 'No office assigned' }}</td>
                        <td style="font-size:11px;color:var(--text-muted)">
                            {{ $doc->last_action_at ? \Carbon\Carbon::parse($doc->last_action_at)->diffForHumans() : $doc->created_at->diffForHumans() }}
                        </td>
                        <td>
                            <a href="/representative/documents/{{ $doc->id }}" class="btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    </tr>
                @endforeach
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
