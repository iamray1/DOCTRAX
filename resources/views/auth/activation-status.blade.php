<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title>Account Activation - DepEd DTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/auth.css">
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
    <style>
        .container { background: transparent; box-shadow: none; animation: none; }
        .main-wrapper { justify-content: center; padding-top: 40px; padding-bottom: 40px; }

        .status-card {
            width: 100%;
            max-width: 440px;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            font-family: 'Poppins', sans-serif;
            animation: cardIn 0.5s ease-out;
        }



        .status-body {
            padding: 36px 32px 32px;
            text-align: center;
        }

        .status-icon {
            width: 68px;
            height: 68px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            position: relative;
        }
        .status-icon.success {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            color: #2563eb;
        }
        .status-icon.warning {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            color: #2563eb;
        }
        .status-icon i { font-size: 28px; }

        .status-card h2 {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 8px 0;
        }

        .status-card .status-desc {
            font-size: 14px;
            color: #64748b;
            line-height: 1.6;
            margin: 0 0 24px 0;
        }

        .info-box {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            padding: 12px 14px;
            text-align: left;
            font-size: 13px;
            color: #1e40af;
            margin-bottom: 24px;
        }

        .warning-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 14px;
            text-align: left;
            font-size: 13px;
            color: #475569;
            margin-bottom: 24px;
        }

        /* Resend = highlighted blue button */
        .btn-resend {
            display: block;
            width: 100%;
            padding: 12px 24px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            background: #2563eb;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-align: center;
            box-sizing: border-box;
        }
        .btn-resend:hover:not(:disabled) {
            background: #1d4ed8;
        }
        .btn-resend:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Go to Login = plain outline */
        .btn-secondary-action {
            display: block;
            width: 100%;
            padding: 11px 24px;
            font-family: 'Poppins', sans-serif;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            background: #fff;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            box-sizing: border-box;
        }
        .btn-secondary-action:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        /* Already-active login = blue filled */
        .btn-primary-action {
            display: block;
            width: 100%;
            padding: 12px 24px;
            font-family: 'Poppins', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #fff;
            background: #2563eb;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            text-align: center;
            box-sizing: border-box;
        }
        .btn-primary-action:hover {
            background: #1d4ed8;
        }

        .btn-stack {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        /* Resend feedback */
        .resend-feedback {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 8px;
            margin-bottom: 14px;
            animation: fadeSlideIn 0.3s ease;
        }
        .resend-feedback.success-msg {
            display: flex;
            background: #eff6ff;
            color: #1e40af;
            border: 1px solid #bfdbfe;
        }
        .resend-feedback.error-msg {
            display: flex;
            background: #f8fafc;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .cooldown-timer {
            font-size: 12px;
            color: #64748b;
            margin-top: 6px;
            text-align: center;
        }
        .cooldown-timer span {
            font-weight: 600;
            color: #475569;
        }

        @keyframes cardIn {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(-6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 600px) {
            .status-body { padding: 28px 20px 24px; }
            .main-wrapper { padding-top: 20px; padding-left: 10px; padding-right: 10px; }
            .status-card { max-width: 100%; border-radius: 12px; }
            .status-icon { width: 56px; height: 56px; }
            .status-icon i { font-size: 24px; }
            .status-card h2 { font-size: 18px; }
            .status-card .status-desc { font-size: 13px; }
            .info-box, .warning-box { font-size: 12px; padding: 10px 12px; }
            .btn-resend { font-size: 13px; padding: 11px 20px; }
            .btn-secondary-action { font-size: 12px; padding: 10px 20px; }
            .btn-primary-action { font-size: 13px; padding: 11px 20px; }
        }

        @media (max-width: 400px) {
            .status-body { padding: 22px 16px 20px; }
            .main-wrapper { padding-left: 6px; padding-right: 6px; }
            .status-card h2 { font-size: 16px; }
            .status-card .status-desc { font-size: 12px; }
        }
        .dash-footer{width:100%;background:#fff;border-top:1px solid #e2e8f0;padding:20px 5%;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8;margin-top:40px}
        .footer-left{display:flex;align-items:center;gap:6px}
        .footer-right{font-size:11px;color:#b0b8c4}
        @media(max-width:768px){.dash-footer{flex-direction:column;gap:6px;text-align:center;padding:16px 5%}}
    </style>
</head>
<body>
    <!-- Navbar -->
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
        </div>
    </nav>

    <div class="main-wrapper">
        <div class="status-card">

            @if($status === 'already_active')
                <div class="status-body">
                    <div class="status-icon success">
                        <i class="fas fa-check"></i>
                    </div>
                    <h2>Already Activated</h2>
                    <p class="status-desc">Your account has already been activated. You can log in to start using the system.</p>

                    <div class="info-box">{{ $message }}</div>

                    <a href="/login" class="btn-primary-action">Continue to Login</a>
                </div>

            @elseif($status === 'invalid')
                <div class="status-body">
                    <div class="status-icon warning">
                        <i class="fas fa-exclamation"></i>
                    </div>
                    <h2>Link Invalid or Expired</h2>
                    <p class="status-desc">This activation link is no longer valid. Request a new one below.</p>

                    <div class="warning-box">{{ $message }}</div>

                    <div class="resend-feedback" id="resendFeedback">
                        <i id="feedbackIcon"></i>
                        <span id="feedbackText"></span>
                    </div>

                    <div class="btn-stack">
                        @if(!empty($email))
                            <button class="btn-resend" id="resendBtn" onclick="resendActivation()">
                                <span id="resendBtnText">Resend Activation Email</span>
                            </button>
                            <p class="cooldown-timer" id="cooldownTimer" style="display: none;">
                                You can resend in <span id="cooldownSeconds">60</span>s
                            </p>
                        @endif

                        <a href="/login" class="btn-secondary-action">Go to Login</a>
                    </div>
                </div>
            @endif

        </div>
    </div>

    @if(!empty($email))
    <script>
        let cooldownActive = false;

        async function resendActivation() {
            if (cooldownActive) return;

            const btn = document.getElementById('resendBtn');
            const btnText = document.getElementById('resendBtnText');
            const feedback = document.getElementById('resendFeedback');
            const feedbackIcon = document.getElementById('feedbackIcon');
            const feedbackText = document.getElementById('feedbackText');
            const cooldownEl = document.getElementById('cooldownTimer');
            const cooldownSec = document.getElementById('cooldownSeconds');
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            feedback.className = 'resend-feedback';
            feedback.style.display = 'none';

            btn.disabled = true;
            btnText.innerHTML = '<span class="loading-dots"><span></span></span>';

            try {
                const response = await fetch('/api/resend-activation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ email: "{{ $email ?? '' }}" }),
                });
                const data = await response.json();

                if (data.success) {
                    feedback.className = 'resend-feedback success-msg';
                    feedbackIcon.className = 'fas fa-check-circle';
                    feedbackText.textContent = 'Activation email sent! Check your inbox.';

                    btnText.textContent = 'Email Sent';
                    startCooldown(60, cooldownEl, cooldownSec, btn, btnText);
                } else {
                    feedback.className = 'resend-feedback error-msg';
                    feedbackIcon.className = 'fas fa-exclamation-circle';
                    feedbackText.textContent = data.message || 'Unable to resend. Please try again.';

                    btn.disabled = false;
                    btnText.textContent = 'Resend Activation Email';
                }
            } catch (err) {
                feedback.className = 'resend-feedback error-msg';
                feedbackIcon.className = 'fas fa-exclamation-circle';
                feedbackText.textContent = 'Network error. Please check your connection.';

                btn.disabled = false;
                btnText.textContent = 'Resend Activation Email';
            }
        }

        function startCooldown(seconds, timerEl, secEl, btn, btnText) {
            cooldownActive = true;
            timerEl.style.display = 'block';
            let remaining = seconds;
            secEl.textContent = remaining;

            const interval = setInterval(() => {
                remaining--;
                secEl.textContent = remaining;

                if (remaining <= 0) {
                    clearInterval(interval);
                    cooldownActive = false;
                    timerEl.style.display = 'none';
                    btn.disabled = false;
                    btnText.textContent = 'Resend Activation Email';
                }
            }, 1000);
        }
    </script>
    @endif
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
