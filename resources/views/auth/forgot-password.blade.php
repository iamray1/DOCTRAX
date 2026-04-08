<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title>Forgot Password - DepEd DTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/auth.css">
    <script src="/js/request-utils.js" defer></script>
    <style>
        .container { background: transparent; box-shadow: none; animation: none; }
        .nav-content { justify-content: flex-start; }
        .brand-text { align-items: flex-start; text-align: left; }

        @media (max-width: 600px) {
            .main-wrapper { padding-left: 10px; padding-right: 10px; }
            .auth-header h2 { font-size: 18px; }
            .auth-header p { font-size: 12px; }
        }

        /* ─── Success state ─── */
        .sent-card {
            text-align: left;
            padding: 8px 0 4px;
            display: none;
        }
        .sent-card .sent-top {
            display: flex;
            justify-content: flex-start;
            margin-bottom: 12px;
        }
        .sent-card h3 { font-size: 17px; font-weight: 700; color: #1e293b; margin: 0 0 8px; }
        .sent-card p { font-size: 13px; color: #64748b; line-height: 1.6; margin: 0 0 20px; }
        .sent-card .sent-email {
            display: block;
            width: 100%;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            color: #0056b3;
            margin-bottom: 20px;
            text-align: left;
            word-break: break-word;
        }
        /* ─── Resend cooldown ─── */
        .resend-row {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            flex-wrap: wrap;
            gap: 4px;
            text-align: left;
            margin-top: 14px;
            font-size: 12px;
            color: #94a3b8;
        }
        .resend-row button {
            background: none; border: none; color: #0056b3; font-size: 12px;
            font-family: inherit; font-weight: 600; cursor: pointer; padding: 0;
            text-decoration: underline;
        }
        .resend-row button:disabled { color: #94a3b8; text-decoration: none; cursor: not-allowed; }
    </style>
    <script src="/js/spa.js" defer></script>
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
            <a href="/about-us" class="nav-link"><i class="fas fa-info-circle"></i> About Us</a>
            <a href="/contact-us" class="nav-link"><i class="fas fa-envelope"></i> Contact Us</a>
        </div>
    </nav>

    <div class="main-wrapper">
        <div class="auth-container">
            <div class="auth-header" id="formHeader">
                <a href="/login" class="back-link"  style="margin-bottom: 12px;display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;" aria-label="Back to login" title="Back to login"><span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></span></a>
                <h2>Forgot your password?</h2>
                <p>Enter the email address associated with your account and we'll send you a reset link.</p>
            </div>

            <!-- Error alert -->
            <div id="errorAlert" class="error-alert" style="display:none; margin-bottom:14px;">
                <i class="fas fa-exclamation-circle"></i>
                <span id="errorText"></span>
            </div>

            <!-- Form -->
            <form id="forgotForm" novalidate autocomplete="off">
                <div class="form-group">
                    <label class="form-label">Email address</label>
                          <input type="email" id="emailInput" class="form-control"
                           placeholder="ex: myname@example.com"
                              autocomplete="email" inputmode="email" autocapitalize="none" autocorrect="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly');">
                    <div class="error-alert" id="emailErr" style="display:none; margin-top:8px;">
                        <i class="fas fa-exclamation-circle"></i>
                        <span id="emailErrText"></span>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                        Send Reset Link
                    </button>
                </div>
            </form>

            <!-- Success state (hidden until sent) -->
            <div class="sent-card" id="sentCard">
                <div class="sent-top">
                    <a href="/login" class="back-link" aria-label="Back to Login" title="Back to Login" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;"><span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></span></a>
                </div>
                <h3>Check your email</h3>
                <p>We sent a password reset link to:</p>
                <div class="sent-email" id="sentEmail"></div>
                <p style="margin-bottom:20px;">Didn't get it? Check your spam folder or resend below.</p>
                <div class="resend-row">
                    <button id="resendBtn" onclick="resend()">Resend email</button>
                    <span id="cooldownText"></span>
                </div>
            </div>
        </div>
    </div>

    <script>
    (function () {
        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var lastEmail = '';
        var cooldownTimer = null;
        var submitCooldownTimer = null;
        var submitCooldownRemaining = 0;

        function formatCountdown(seconds) {
            seconds = Math.max(0, parseInt(seconds, 10) || 0);
            var minutes = Math.floor(seconds / 60);
            var secs = seconds % 60;
            if (minutes <= 0) return secs + 's';
            return minutes + 'm ' + String(secs).padStart(2, '0') + 's';
        }

        function showError(elId, msg) {
            var el = document.getElementById(elId);
            el.querySelector('span').textContent = msg;
            el.style.display = 'flex';
        }

        function hideError(elId) {
            document.getElementById(elId).style.display = 'none';
        }

        function clearSubmitCooldown() {
            if (submitCooldownTimer) {
                clearInterval(submitCooldownTimer);
                submitCooldownTimer = null;
            }
            submitCooldownRemaining = 0;
            var submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Send Reset Link';
            }
        }

        function startSubmitCooldown(seconds) {
            var submitBtn = document.getElementById('submitBtn');
            clearSubmitCooldown();

            submitCooldownRemaining = Math.max(1, parseInt(seconds, 10) || 60);
            showError('emailErr', 'Too many attempts. Please try again in ' + formatCountdown(submitCooldownRemaining) + '.');

            if (!submitBtn) return;

            submitBtn.disabled = true;
            submitBtn.innerText = 'Try again in ' + formatCountdown(submitCooldownRemaining);

            submitCooldownTimer = setInterval(function () {
                submitCooldownRemaining--;

                if (submitCooldownRemaining <= 0) {
                    clearSubmitCooldown();
                    hideError('emailErr');
                    return;
                }

                showError('emailErr', 'Too many attempts. Please try again in ' + formatCountdown(submitCooldownRemaining) + '.');
                submitBtn.innerText = 'Try again in ' + formatCountdown(submitCooldownRemaining);
            }, 1000);
        }

        document.getElementById('forgotForm').addEventListener('submit', function (e) {
            e.preventDefault();

            if (submitCooldownRemaining > 0) {
                showError('emailErr', 'Too many attempts. Please try again in ' + formatCountdown(submitCooldownRemaining) + '.');
                return;
            }

            hideError('errorAlert');
            hideError('emailErr');

            var email = document.getElementById('emailInput').value.trim();

            if (!email) { showError('emailErr', 'Email address is required.'); return; }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showError('emailErr', 'Enter a valid email address.'); return; }

            sendRequest(email, false);
        });

        function sendRequest(email, isResend) {
            var btn = isResend ? document.getElementById('resendBtn') : document.getElementById('submitBtn');
            var originalText = btn.innerHTML;
            btn.disabled = true;
            if (!isResend) btn.innerHTML = '<span class="loading-dots"><span></span></span>';

            fetch('/api/forgot-password', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: email })
            })
            .then(function (r) {
                var retryAfterHeader = r.headers.get('Retry-After');
                var resetHeader = r.headers.get('X-RateLimit-Reset');
                var retryAfter = parseInt(retryAfterHeader || '', 10);

                if ((!retryAfter || retryAfter < 1) && resetHeader) {
                    var resetAt = parseInt(resetHeader, 10);
                    if (resetAt) {
                        retryAfter = Math.max(1, resetAt - Math.floor(Date.now() / 1000));
                    }
                }

                return r.json().then(function (d) {
                    return { ok: r.ok, status: r.status, data: d, retryAfter: retryAfter };
                });
            })
            .then(function (res) {
                if (!isResend) btn.innerHTML = originalText;

                if (res.ok) {
                    lastEmail = email;
                    showSentCard(email);
                    startCooldown(60);
                } else {
                    if (!isResend) {
                        btn.disabled = false;
                        var validationMessage =
                            (res.data && res.data.errors && res.data.errors.email && res.data.errors.email[0]) ||
                            (res.data && res.data.message) ||
                            'Something went wrong. Try again.';

                        if (res.status === 429 || res.status === 422 || (res.data && res.data.throttled)) {
                            showError('emailErr', validationMessage);
                            if (res.status === 429 || (res.data && res.data.throttled)) {
                                startSubmitCooldown((res.data && res.data.retry_after) || res.retryAfter || 60);
                            }
                        } else {
                            showError('errorAlert', validationMessage);
                        }
                    } else {
                        startCooldown((res.data && res.data.retry_after) || res.retryAfter || 30);
                    }
                }
            })
            .catch(function () {
                if (!isResend) {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                    showError('errorAlert', 'Network error. Please try again.');
                }
            });
        }

        function showSentCard(email) {
            document.getElementById('formHeader').style.display = 'none';
            document.getElementById('forgotForm').style.display = 'none';
            document.getElementById('errorAlert').style.display = 'none';
            document.getElementById('sentEmail').textContent = email;
            document.getElementById('sentCard').style.display = 'block';
        }

        window.resend = function () {
            if (!lastEmail) return;
            sendRequest(lastEmail, true);
        };

        function startCooldown(seconds) {
            var btn = document.getElementById('resendBtn');
            var txt = document.getElementById('cooldownText');
            btn.disabled = true;
            if (cooldownTimer) clearInterval(cooldownTimer);
            var remaining = seconds;
            txt.textContent = ' — wait ' + remaining + 's';
            cooldownTimer = setInterval(function () {
                remaining--;
                if (remaining <= 0) {
                    clearInterval(cooldownTimer);
                    btn.disabled = false;
                    txt.textContent = '';
                } else {
                    txt.textContent = ' — wait ' + remaining + 's';
                }
            }, 1000);
        }
    })();
    </script>
</body>
</html>
