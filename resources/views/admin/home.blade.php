<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>DepEd Document Tracking System</title>
    <!-- Preconnect for faster font loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">

    <style>
        /* ─── Hide the public navbar — replaced by admin mob-topbar + sidebar ─── */
        .navbar { display:none !important; }

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
        .mob-topbar{display:flex;position:sticky;top:0;z-index:100;background:#0056b3;padding:12px 16px;align-items:center;justify-content:space-between;gap:12px;box-shadow:0 2px 8px rgba(0,0,0,.1);width:100%;align-self:stretch}
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
    </style>

    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
</head>
<body class="home-page">

    <!-- Mobile top bar (hamburger on LEFT) -->
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

    <!-- Original welcome page content (unchanged from welcome.blade.php) -->
    <div class="main-wrapper">
        <!-- Main Content -->
        <main class="main-content">
            <div class="greeting">
                <img src="{{ asset('images/sdologo.svg') }}" alt="SDO Logo" class="greeting-logo">
                @php
                    $gName = explode(' ', trim($user->name))[0];
                @endphp
                <h2>Hello, {{ $gName }}!<br>Choose your transaction.</h2>
                <p>Welcome to the official document portal.</p>
            </div>

            <!-- Action Buttons -->
            <div class="button-group">
                <a href="/track" class="btn btn-primary">
                    <i class="fas fa-search icon"></i>
                    <span>TRACK<br>DOCUMENT</span>
                </a>
                <a href="/submit" class="btn btn-primary">
                    <i class="fas fa-file-upload icon"></i>
                    <span>SUBMIT<br>DOCUMENT</span>
                </a>
            </div>

            <!-- Dashboard Button -->
            <a href="/dashboard" class="btn btn-login">
                Go to Dashboard
            </a>
        </main>
    </div>
    <footer class="dash-footer">
        <div class="footer-left">
            <span>&copy; {{ date('Y') }} DepEd Document Tracking System</span>
        </div>
        <div class="footer-right">
            Developed by Raymond Bautista
        </div>
    </footer>

<script>
(function(){
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    window.logout = function() {
        fetch('/api/logout', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } })
        .then(function() { window.location.href = '/login'; })
        .catch(function() { window.location.href = '/login'; });
    };
})();

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
</script>
</body>
</html>
