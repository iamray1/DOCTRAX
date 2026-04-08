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
    <link rel="stylesheet" href="/css/styles.css">
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
    <style>
        .about-wrapper { max-width:680px; margin:0 auto; padding:40px 20px 60px; flex:1; width:100%; }
        .about-hero { text-align:center; margin-bottom:20px; }
        .about-hero h2 { font-size:clamp(20px,4vw,28px); font-weight:700; color:var(--primary-color); margin:0 0 6px; }
        .about-hero p { font-size:clamp(13px,2.5vw,15px); color:#355075; max-width:480px; margin:0 auto; line-height:1.7; }
        .about-card { background:#fff; border-radius:18px; box-shadow:0 14px 34px rgba(0,86,179,.10); padding:28px 32px; margin-bottom:24px; border:1px solid rgba(0,86,179,.14); text-align:left; }
        .about-item { display:flex; align-items:flex-start; gap:16px; padding:18px 0; border-bottom:1px solid rgba(0,86,179,.10); text-align:left; }
        .about-item:last-child { border-bottom:none; }
        .about-icon { width:46px; height:46px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; box-shadow:inset 0 0 0 1px rgba(0,86,179,.08); }
        .about-detail { width:100%; padding-top:2px; }
        .about-detail h3 { font-size:14px; font-weight:700; color:var(--primary-color); margin-bottom:6px; }
        .about-detail p { font-size:13px; color:#355075; line-height:1.7; margin:0; }
        .about-steps { list-style:none; padding:0; margin:10px 0 0; max-width:none; text-align:left; }
        .about-steps li { padding:5px 0; display:flex; align-items:flex-start; gap:10px; color:#355075; font-size:13px; line-height:1.7; }
        .about-steps li i { color:var(--primary-color); margin-top:4px; flex-shrink:0; font-size:12px; }
        @media(max-width:600px) {
            .about-wrapper { padding:24px 14px 40px; }
            .about-card { padding:20px 18px; }
            .about-item { flex-direction:column; gap:12px; }
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="nav-content">
            <div class="brand-text">
                <span class="brand-subtitle">Department of Education</span>
                <h1>CITY OF SAN JOSE DEL MONTE</h1>
                <span class="brand-caption">Document Tracking System &mdash; DOCTRAX</span>
            </div>
        </div>
        <button class="nav-hamburger" id="navHamburger" onclick="document.getElementById('navLinks').classList.toggle('open');this.classList.toggle('open')" aria-label="Menu">
            <i class="fas fa-bars"></i>
        </button>
        <div class="nav-links" id="navLinks">
            <a href="/" class="nav-link"><i class="fas fa-home"></i> Home</a>
            <a href="/about-us" class="nav-link active"><i class="fas fa-info-circle"></i> About Us</a>
            <a href="/contact-us" class="nav-link"><i class="fas fa-envelope"></i> Contact Us</a>
        </div>
    </nav>

    <div class="about-wrapper">
        <div class="about-hero">
            <h2>About Us</h2>
            <p>Track, route, and monitor documents more clearly through DOCTRAX.</p>
        </div>

        <div class="about-card">
            <div class="about-item">
                <div class="about-icon" style="background:rgba(0,86,179,.1);color:var(--primary-color)">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="about-detail">
                    <h3>About DOCTRAX</h3>
                    <p>DOCTRAX is the document tracking system of the Schools Division Office of City of San Jose del Monte, Bulacan. It gives clients and personnel a clearer way to submit documents, monitor progress, and receive updates online.</p>
                </div>
            </div>
            <div class="about-item">
                <div class="about-icon" style="background:rgba(0,86,179,.16);color:var(--primary-color)">
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
