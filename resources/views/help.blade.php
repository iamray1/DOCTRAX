<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Help &amp; Guide - DepEd DOCTRAX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
    <style>
        .help-wrapper { max-width: 760px; margin: 0 auto; padding: 40px 20px 60px; flex: 1; width: 100%; }
        .help-hero { text-align: center; margin-bottom: 20px; }
        .help-hero h2 { font-size: clamp(20px, 4vw, 28px); font-weight: 700; color: var(--primary-color); margin: 0 0 6px; }
        .help-hero p { font-size: clamp(13px, 2.5vw, 15px); color: #355075; max-width: 560px; margin: 0 auto; line-height: 1.7; }
        .help-card { background: #fff; border-radius: 18px; box-shadow: 0 14px 34px rgba(0,86,179,.10); padding: 28px 32px; margin-bottom: 24px; border: 1px solid rgba(0,86,179,.14); text-align: left; }
        .help-item { display: flex; align-items: flex-start; gap: 16px; padding: 18px 0; border-bottom: 1px solid rgba(0,86,179,.10); }
        .help-item:last-child { border-bottom: none; }
        .help-icon { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; box-shadow: inset 0 0 0 1px rgba(0,86,179,.08); }
        .help-detail h3 { font-size: 14px; font-weight: 700; color: var(--primary-color); margin: 0 0 6px; }
        .help-detail p { font-size: 13px; color: #355075; line-height: 1.7; margin: 0; }
        .help-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 12px; }
        .help-btn { display: inline-flex; align-items: center; gap: 8px; text-decoration: none; font-size: 12px; font-weight: 600; padding: 10px 14px; border-radius: 10px; border: 1px solid rgba(0,86,179,.2); color: var(--primary-color); background: #f8fbff; transition: background .2s, transform .2s; }
        .help-btn:hover { background: #eef6ff; transform: translateY(-1px); }
        @media (max-width: 600px) {
            .help-wrapper { padding: 24px 14px 40px; }
            .help-card { padding: 20px 18px; }
            .help-item { flex-direction: column; gap: 12px; }
            .help-actions { flex-direction: column; }
            .help-btn { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
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
            <a href="/about-us" class="nav-link"><i class="fas fa-info-circle"></i> About Us</a>
            <a href="/contact-us" class="nav-link"><i class="fas fa-envelope"></i> Contact Us</a>
            <a href="/help" class="nav-link active"><i class="fas fa-circle-question"></i> Help &amp; Guide</a>
        </div>
    </nav>

    <div class="help-wrapper">
        <div class="help-hero">
            <h2>Help &amp; Guide</h2>
            <p>Need help with document submission, tracking, or account access? Start with these quick guides.</p>
        </div>

        <div class="help-card">
            <div class="help-item">
                <div class="help-icon" style="background: rgba(0,86,179,.10); color: var(--primary-color);">
                    <i class="fas fa-paper-plane"></i>
                </div>
                <div class="help-detail">
                    <h3>How To Submit A Document</h3>
                    <p>Go to Submit Document, fill out all required fields, then keep your tracking number for follow-ups.</p>
                    <div class="help-actions">
                        <a href="/submit" class="help-btn"><i class="fas fa-paper-plane"></i> Open Submit</a>
                    </div>
                </div>
            </div>

            <div class="help-item">
                <div class="help-icon" style="background: rgba(0,86,179,.14); color: var(--primary-color);">
                    <i class="fas fa-search"></i>
                </div>
                <div class="help-detail">
                    <h3>How To Track Your Document</h3>
                    <p>Use your tracking number or document control number on the Track page to see the latest status and route history.</p>
                    <div class="help-actions">
                        <a href="/track" class="help-btn"><i class="fas fa-magnifying-glass"></i> Open Track</a>
                    </div>
                </div>
            </div>

            <div class="help-item">
                <div class="help-icon" style="background: rgba(0,86,179,.18); color: var(--primary-color);">
                    <i class="fas fa-user-lock"></i>
                </div>
                <div class="help-detail">
                    <h3>Need Account Assistance?</h3>
                    <p>If you cannot access your account or need activation support, use Forgot Password or contact the Division Office.</p>
                    <div class="help-actions">
                        <a href="/login" class="help-btn"><i class="fas fa-right-to-bracket"></i> Go To Login</a>
                        <a href="/forgot-password" class="help-btn"><i class="fas fa-key"></i> Forgot Password</a>
                    </div>
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
