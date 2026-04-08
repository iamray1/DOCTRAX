<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title>Login - DepEd DTS</title>
    <!-- Preconnect for faster font loading -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/auth.css">
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
    <style>
        .container { background: transparent; box-shadow: none; animation: none; }

        @media (max-width: 600px) {
            .main-wrapper {
                padding-left: 10px;
                padding-right: 10px;
            }
            .auth-header h2 {
                font-size: 18px;
            }
            .auth-header p {
                font-size: 12px;
            }
        }
        .dash-footer{width:100%;background:#fff;border-top:1px solid #e2e8f0;padding:20px 5%;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8;margin-top:40px}
        .footer-left{display:flex;align-items:center;gap:6px}
        .footer-right{font-size:11px;color:#b0b8c4}
        @media(max-width:768px){.dash-footer{flex-direction:column;gap:6px;text-align:center;padding:16px 5%}}
        #email.email-locked::-ms-clear { display: none; }
        #email.email-locked::-webkit-search-cancel-button { display: none; }
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
            <a href="/about-us" class="nav-link"><i class="fas fa-info-circle"></i> About Us</a>
            <a href="/contact-us" class="nav-link"><i class="fas fa-envelope"></i> Contact Us</a>
        </div>
    </nav>

    <div class="main-wrapper">
        <div class="auth-container">
            <div class="auth-header">
                <a href="/" class="back-link" aria-label="Back" title="Back" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;margin-bottom:12px;">
                    <span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                    </span>
                </a>
                <h2>Enter your email to continue</h2>
                <p>Log in to DepEd Document Tracking System using your email. If you don't have an account yet, you'll be prompted to create one.</p>
            </div>

            <form id="loginForm" novalidate autocomplete="off">
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" id="email" class="form-control" placeholder="ex: myname@example.com" autocomplete="email" inputmode="email" autocapitalize="none" autocorrect="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly');">
                    <div id="emailDisplay" style="display:none; width:100%; padding:12px 16px; border:2px solid #e2e8f0; border-radius:8px; font-size:16px; font-family:inherit; background:#f1f5f9; color:#64748b; box-sizing:border-box;"></div>

                    <!-- Custom Error Alert -->
                    <div class="error-alert" id="emailErrorAlert">
                        <i class="fas fa-exclamation-circle"></i>
                        <span id="emailErrorText">Server error</span>
                    </div>
                </div>

                <div class="form-group hidden" id="passwordGroup">
                    <label class="form-label">Password</label>
                    <div style="position: relative;">
                        <input type="password" id="password" class="form-control" placeholder="Enter your password" style="padding-right: 45px;" autocomplete="one-time-code" readonly onfocus="this.removeAttribute('readonly');">
                        <button type="button" onclick="togglePassword('password')" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: #64748b; padding: 0; display: flex; align-items: center; justify-content: center;">
                            <span id="icon-password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-eye"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 4c4.29 0 7.863 2.429 10.665 7.154l.22 .379l.045 .1l.03 .083l.014 .055l.014 .082l.011 .1v.11l-.014 .111a.992 .992 0 0 1 -.026 .11l-.039 .108l-.036 .075l-.016 .03c-2.764 4.836 -6.3 7.38 -10.555 7.499l-.313 .004c-4.396 0 -8.037 -2.549 -10.868 -7.504a1 1 0 0 1 0 -.992c2.831 -4.955 6.472 -7.504 10.868 -7.504zm0 5a3 3 0 1 0 0 6a3 3 0 0 0 0 -6" /></svg>
                            </span>
                        </button>
                    </div>
                    <div style="text-align:right; margin-top:6px;">
                        <a href="/forgot-password" style="font-size:12px; color:#64748b; text-decoration:none; font-weight:400;">Forgot password?</a>
                    </div>

                    <!-- Custom Error Alert -->
                    <div class="error-alert" id="passwordErrorAlert">
                        <i class="fas fa-exclamation-circle"></i>
                        <span id="passwordErrorText">Invalid password</span>
                    </div>
                </div>

                <div style="margin-top: 20px;">
                    <button type="submit" class="btn btn-primary btn-block" id="submitBtn" data-no-auto-loading>
                        Proceed
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function() {
        // Password Toggle Logic
        const eyeShow = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor" class="icon icon-tabler icons-tabler-filled icon-tabler-eye"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 4c4.29 0 7.863 2.429 10.665 7.154l.22 .379l.045 .1l.03 .083l.014 .055l.014 .082l.011 .1v.11l-.014 .111a.992 .992 0 0 1 -.026 .11l-.039 .108l-.036 .075l-.016 .03c-2.764 4.836 -6.3 7.38 -10.555 7.499l-.313 .004c-4.396 0 -8.037 -2.549 -10.868 -7.504a1 1 0 0 1 0 -.992c2.831 -4.955 6.472 -7.504 10.868 -7.504zm0 5a3 3 0 1 0 0 6a3 3 0 0 0 0 -6" /></svg>`;
        const eyeOff = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-eye-off"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10.585 10.587a2 2 0 0 0 2.829 2.828" /><path d="M16.681 16.673a8.717 8.717 0 0 1 -4.681 1.327c-3.6 0 -6.6 -2 -9 -6c1.272 -2.12 2.712 -3.678 4.32 -4.674m2.86 -1.146a9.055 9.055 0 0 1 1.82 -.18c3.6 0 6.6 2 9 6c-.666 1.11 -1.379 2.067 -2.138 2.87" /><path d="M3 3l18 18" /></svg>`;

        function togglePassword(id) {
            const input = document.getElementById(id);
            const iconSpan = document.getElementById('icon-' + id);
            if (input.type === 'password') {
                input.type = 'text';
                iconSpan.innerHTML = eyeOff;
            } else {
                input.type = 'password';
                iconSpan.innerHTML = eyeShow;
            }
        }

        const form = document.getElementById('loginForm');
        const emailInput = document.getElementById('email');
        const emailDisplay = document.getElementById('emailDisplay');
        const passwordInput = document.getElementById('password');
        const passwordGroup = document.getElementById('passwordGroup');
        const submitBtn = document.getElementById('submitBtn');

        function getCsrfToken() {
            const tokenNode = document.querySelector('meta[name="csrf-token"]');
            return tokenNode ? tokenNode.getAttribute('content') : '';
        }

        function redirectToFreshLogin() {
            const url = new URL('/login', window.location.origin);
            url.searchParams.set('session', 'expired');
            if (nextPath) {
                url.searchParams.set('next', nextPath);
            }
            window.location.replace(url.toString());
        }

        async function parseAuthResponse(response) {
            if (response.status === 419) {
                redirectToFreshLogin();
                throw new Error('SESSION_EXPIRED');
            }

            const contentType = response.headers.get('content-type') || '';

            if (contentType.includes('application/json')) {
                return response.json();
            }

            const text = await response.text();

            try {
                return JSON.parse(text);
            } catch (error) {
                throw new Error('INVALID_RESPONSE');
            }
        }

        function getSafeNextPath() {
            const next = new URLSearchParams(window.location.search).get('next');
            if (!next) return null;

            // Allow only internal app paths to avoid open redirects.
            if (next.startsWith('/') && !next.startsWith('//')) {
                return next;
            }

            return null;
        }

        const nextPath = getSafeNextPath();

        let step = 1; // 1: Email check, 2: Password
        let confirmedEmail = '';

        // Back button resets the form to step 1
        const backLink = document.querySelector('.auth-header a[href="/"]');
        if (backLink) {
            backLink.addEventListener('click', function() {
                step = 1;
                passwordGroup.classList.add('hidden');
                passwordInput.value = '';
                emailInput.value = '';
                hideError('email');
                hideError('password');
                emailInput.disabled = false;
                emailInput.classList.remove('email-locked');
                emailInput.style.display = '';
                emailDisplay.style.display = 'none';
                emailDisplay.textContent = '';
                confirmedEmail = '';
                // restore the clear-input-btn
                const clearBtn = emailInput.parentElement ? emailInput.parentElement.querySelector('.clear-input-btn') : null;
                if (clearBtn) clearBtn.style.display = '';
                submitBtn.innerText = 'Proceed';
                submitBtn.disabled = false;
            });
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideError('email');
            hideError('password');

            if (step === 1) {
                const emailVal = emailInput.value.trim();
                if (!emailVal) {
                    showError('email', 'Email is required');
                    return;
                }
                if (!validateEmail(emailVal)) {
                    showError('email', 'Please enter a valid email address');
                    return;
                }

                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="loading-dots"><span></span></span>';

                try {
                    const response = await fetch('/api/check-email', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ email: emailVal })
                    });
                    const data = await parseAuthResponse(response);

                    if (data.exists) {
                        if (data.pending) {
                            showError('email', data.message || 'Your account is pending activation. Check your email.');
                            submitBtn.innerText = 'Proceed';
                            submitBtn.disabled = false;
                            return;
                        }
                        if (data.suspended) {
                            showError('email', data.message || 'Your account has been deactivated. Please contact the administrator.');
                            submitBtn.innerText = 'Proceed';
                            submitBtn.disabled = false;
                            return;
                        }
                        passwordGroup.classList.remove('hidden');
                        passwordGroup.style.animation = 'fadeIn 0.5s ease';
                        confirmedEmail = emailVal;
                        emailDisplay.textContent = emailVal;
                        emailDisplay.style.display = 'block';
                        emailInput.value = ''; // clear so browser X button disappears
                        emailInput.style.display = 'none';
                        emailInput.disabled = true;
                        // hide the custom clear-input-btn injected by form-utils
                        const clearBtn = emailInput.parentElement.querySelector('.clear-input-btn');
                        if (clearBtn) clearBtn.style.display = 'none';
                        passwordInput.focus();
                        submitBtn.innerText = 'Login';
                        submitBtn.disabled = false;
                        step = 2;
                    } else {
                        window.location.href = '/register?email=' + encodeURIComponent(emailVal);
                    }
                } catch (error) {
                    if (error && error.message === 'SESSION_EXPIRED') {
                        return;
                    }
                    console.error('Check email error:', error);
                    showError('email', 'Server error. Please try again.');
                    submitBtn.innerText = 'Proceed';
                    submitBtn.disabled = false;
                }

            } else {
                if (!passwordInput.value) {
                    showError('password', 'Please enter your password');
                    return;
                }

                submitBtn.innerHTML = '<span class="loading-dots"><span></span></span>';
                submitBtn.disabled = true;

                try {
                    const response = await fetch('/api/login', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': getCsrfToken()
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({
                            email: confirmedEmail,
                            password: passwordInput.value
                        })
                    });
                    const data = await parseAuthResponse(response);

                    if (data.success) {
                        window.location.href = nextPath || '/dashboard';
                    } else if (data.throttled) {
                        let secs = data.retry_after || 60;
                        showError('password', data.message);
                        submitBtn.disabled = true;
                        const countdown = setInterval(() => {
                            secs--;
                            if (secs <= 0) {
                                clearInterval(countdown);
                                submitBtn.innerText = 'Login';
                                submitBtn.disabled = false;
                                hideError('password');
                            } else {
                                submitBtn.innerText = 'Try again in ' + secs + 's';
                            }
                        }, 1000);
                        submitBtn.innerText = 'Try again in ' + secs + 's';
                    } else if (data.pending) {
                        showError('password', data.message || 'Account not yet activated. Check your email.');
                        submitBtn.innerText = 'Login';
                        submitBtn.disabled = false;
                    } else if (data.suspended) {
                        showError('password', data.message || 'Your account has been deactivated. Please contact the administrator.');
                        submitBtn.innerText = 'Login';
                        submitBtn.disabled = false;
                    } else {
                        showError('password', data.message || 'Incorrect password. Please try again.');
                        submitBtn.innerText = 'Login';
                        submitBtn.disabled = false;
                    }
                } catch (error) {
                    if (error && error.message === 'SESSION_EXPIRED') {
                        return;
                    }
                    console.error('Login error:', error);
                    showError('password', 'System error occurred. Please try again.');
                    submitBtn.innerText = 'Login';
                    submitBtn.disabled = false;
                }
            }
        });

        function validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
        }

        function showError(field, message) {
            const alert = document.getElementById(field + 'ErrorAlert');
            if (alert) {
                document.getElementById(field + 'ErrorText').innerText = message;
                alert.style.display = 'flex';
            }
            document.getElementById(field).style.borderColor = '#dc2626';
        }

        function hideError(field) {
            const alert = document.getElementById(field + 'ErrorAlert');
            if (alert) { alert.style.display = 'none'; }
            document.getElementById(field).style.borderColor = '#e2e8f0';
        }

        const sessionState = new URLSearchParams(window.location.search).get('session');
        if (sessionState === 'expired') {
            showError('email', 'Your session expired. Please sign in again.');

            const params = new URLSearchParams(window.location.search);
            params.delete('session');
            const cleanUrl = window.location.pathname
                + (params.toString() ? '?' + params.toString() : '')
                + window.location.hash;

            if (window.history && typeof window.history.replaceState === 'function') {
                window.history.replaceState({}, '', cleanUrl);
            }
        }

        // Expose togglePassword globally for onclick handler
        window.togglePassword = togglePassword;
    })();
    </script>
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

