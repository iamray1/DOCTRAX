<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>About Us - DepEd DOCTRAX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #0056b3;
            --primary-dark: #004494;
            --primary-gradient: linear-gradient(135deg, #0056b3 0%, #004494 100%);
            --text-dark: #1b263b;
            --text-muted: #64748b;
            --white: #ffffff;
            --bg: #f0f2f5;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.06);
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        html { overflow-y:scroll; }
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

        /* ─── Main ─── */
        .main{margin-left:0;flex:1;display:flex;flex-direction:column}

        /* ─── Content ─── */
        .about-wrapper { max-width:680px; width:100%; margin:0 auto; padding:28px 24px 48px; flex:1; }
        .back-link { display:inline-flex; align-items:center; gap:6px; color:var(--text-muted); font-size:13px; text-decoration:none; margin-bottom:18px; transition:color .15s; }
        .back-link:hover { color:var(--primary); }
        .about-hero { text-align:center; margin-bottom:20px; }
        .about-hero h2 { font-size:clamp(20px,4vw,28px); font-weight:700; color:var(--primary); margin:0 0 6px; }
        .about-hero p { font-size:clamp(13px,2.5vw,15px); color:#355075; max-width:480px; margin:0 auto; line-height:1.7; }
        .about-card { background:#fff; border-radius:18px; box-shadow:0 14px 34px rgba(0,86,179,.10); padding:28px 32px; margin-bottom:24px; border:1px solid rgba(0,86,179,.14); text-align:left; }
        .about-item { display:flex; align-items:flex-start; gap:16px; padding:18px 0; border-bottom:1px solid rgba(0,86,179,.10); text-align:left; }
        .about-item:last-child { border-bottom:none; }
        .about-icon { width:46px; height:46px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; box-shadow:inset 0 0 0 1px rgba(0,86,179,.08); }
        .about-detail { width:100%; padding-top:2px; }
        .about-detail h3 { font-size:14px; font-weight:700; color:var(--primary); margin-bottom:6px; }
        .about-detail p { font-size:13px; color:#355075; line-height:1.7; margin:0; }
        .about-steps { list-style:none; padding:0; margin:10px 0 0; max-width:none; text-align:left; }
        .about-steps li { padding:5px 0; display:flex; align-items:flex-start; gap:10px; color:#355075; font-size:13px; line-height:1.7; }
        .about-steps li i { color:var(--primary); margin-top:4px; flex-shrink:0; font-size:12px; }

        /* ─── Footer ─── */
        .site-footer{width:100%;background:#fff;border-top:1px solid #e2e8f0;padding:20px 5%;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8;margin-top:auto}
        .footer-right{font-size:11px;color:#b0b8c4}

        @media(max-width:900px){
            .site-footer{flex-direction:column;gap:6px;text-align:center;padding:16px 5%}
        }
        @media(max-width:600px) {
            .about-wrapper { padding:24px 14px 40px; }
            .about-card { padding:20px 18px; }
            .about-item { flex-direction:column; gap:12px; }
        }
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

<!-- ─── Main Content ─── -->
<div class="main">

<div class="about-wrapper">

    @if(request()->query('from') === 'profile')
    <a href="/profile" class="back-link" aria-label="Back to Profile" title="Back to Profile" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;"><span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></span></a>
    @else
    <a href="/dashboard" class="back-link" aria-label="Back to Dashboard" title="Back to Dashboard" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;"><span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></span></a>
    @endif

    <div class="about-hero">
        <h2>About Us</h2>
        <p>Track, route, and monitor documents more clearly through DOCTRAX.</p>
    </div>

    <div class="about-card">
        <div class="about-item">
            <div class="about-icon" style="background:rgba(0,86,179,.1);color:var(--primary)">
                <i class="fas fa-info-circle"></i>
            </div>
            <div class="about-detail">
                <h3>About DOCTRAX</h3>
                <p>DOCTRAX is the document tracking system of the Schools Division Office of City of San Jose del Monte, Bulacan. It gives clients and personnel a clearer way to submit documents, monitor progress, and receive updates online.</p>
            </div>
        </div>
        <div class="about-item">
            <div class="about-icon" style="background:rgba(0,86,179,.16);color:var(--primary)">
                <i class="fas fa-route"></i>
            </div>
            <div class="about-detail">
                <h3>How DOCTRAX Works</h3>
                <ul class="about-steps">
                    <li><i class="fas fa-check"></i><span>Submit a document through the system and receive a tracking number.</span></li>
                    <li><i class="fas fa-check"></i><span>Use that tracking number to check the document's progress online.</span></li>
                    <li><i class="fas fa-check"></i><span>Office personnel receive, route, and update the document in the system.</span></li>
                    <li><i class="fas fa-check"></i><span>Once processing is complete, the document is marked as completed.</span></li>
                </ul>
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

</div><!-- /.main -->

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
