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
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
</head>
<body class="home-page">
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
            <a href="/" class="nav-link active"><i class="fas fa-home"></i> Home</a>
            <a href="/about-us" class="nav-link"><i class="fas fa-info-circle"></i> About Us</a>
            <a href="/contact-us" class="nav-link"><i class="fas fa-envelope"></i> Contact Us</a>
        </div>
    </nav>

    <div class="main-wrapper">
        <!-- Main Content -->
        <main class="main-content">
            <div class="greeting">
                <img src="{{ asset('images/sdologo.svg') }}" alt="SDO Logo" class="greeting-logo">
                @auth
                @php
                    $u = auth()->user();
                    $gName = explode(' ', trim($u->name))[0];
                @endphp
                <h2>Hello, {{ $gName }}!<br>Choose your transaction.</h2>
                @else
                <h2>Hello, Guest!<br>Choose your transaction.</h2>
                @endauth
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

            <!-- Login Button -->
            @auth
                <a href="/dashboard" class="btn btn-login">
                    Go to Dashboard
                </a>
            @else
                <a href="/login" class="btn btn-login">
                    SignUp / Login
                </a>
            @endauth
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
</body>
</html>
