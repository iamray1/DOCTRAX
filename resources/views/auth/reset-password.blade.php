<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title>Reset Password - DepEd DTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <link rel="stylesheet" href="/css/auth.css">
    <style>
        .container { background: transparent; box-shadow: none; animation: none; }

        .password-wrapper { position: relative; }
        .password-wrapper .form-control { padding-right: 44px; }
        .toggle-pw {
            position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: #94a3b8;
            padding: 4px; display: flex; align-items: center; transition: color 0.2s;
        }
        .toggle-pw:hover { color: #475569; }

        .strength-section { margin-top: -6px; margin-bottom: 20px; }
        .strength-bar-row { display: flex; gap: 4px; margin-bottom: 10px; }
        .strength-segment {
            flex: 1; height: 4px; border-radius: 99px;
            background: #e2e8f0; transition: background-color 0.35s ease;
        }
        .strength-label {
            display: flex; justify-content: space-between;
            align-items: center; margin-bottom: 10px;
        }
        .strength-label-text { font-size: 12px; font-weight: 600; color: #64748b; }
        .strength-value {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.4px; color: #94a3b8; transition: color 0.3s;
        }
        .req-list {
            list-style: none; margin: 0; padding: 0;
            display: grid; grid-template-columns: 1fr 1fr; gap: 6px 16px;
        }
        .req-list li {
            display: flex; align-items: center; gap: 7px;
            font-size: 12px; color: #94a3b8; transition: color 0.25s;
        }
        .req-list li .req-icon {
            width: 16px; height: 16px; border-radius: 50%;
            border: 1.5px solid #cbd5e1; display: flex; align-items: center;
            justify-content: center; font-size: 8px; color: transparent;
            transition: all 0.25s; flex-shrink: 0;
        }
        .req-list li.met { color: #059669; }
        .req-list li.met .req-icon { background: #059669; border-color: #059669; color: #fff; }

        .match-hint {
            font-size: 12px; margin-top: 6px; display: none; align-items: center; gap: 5px;
        }
        .match-hint.show { display: flex; }
        .match-hint.no-match { color: #dc2626; }
        .match-hint.matched { color: #059669; }

        .btn-set-pw {
            width: 100%; padding: 13px; background: var(--primary-color);
            color: white; border: none; border-radius: 8px;
            font-size: 15px; font-weight: 600; font-family: inherit;
            cursor: pointer; transition: background 0.2s; margin-top: 4px;
        }
        .btn-set-pw:hover:not(:disabled) { background: #004494; }
        .btn-set-pw:disabled { background: #94a3b8; cursor: not-allowed; }

        .sp-alert {
            padding: 10px 14px; border-radius: 8px; margin-bottom: 16px;
            font-size: 13px; display: flex; align-items: center; gap: 10px;
            animation: fadeIn 0.3s ease;
        }
        .sp-alert.success { background-color: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .sp-alert.danger  { background-color: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        /* Invalid / expired token state */
        .invalid-card {
            text-align: center; padding: 8px 0 4px;
        }
        .invalid-card .inv-icon {
            width: 56px; height: 56px; border-radius: 50%;
            background: #fef2f2; border: 2px solid #fecaca;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px; font-size: 24px; color: #dc2626;
        }
        .invalid-card h3 { font-size: 17px; font-weight: 700; color: #1e293b; margin: 0 0 8px; }
        .invalid-card p { font-size: 13px; color: #64748b; line-height: 1.6; margin: 0 0 20px; }
        .btn-link-style {
            width: 100%; padding: 12px; background: #0056b3; color: #fff;
            border: none; border-radius: 8px; font-size: 14px; font-weight: 600;
            font-family: inherit; cursor: pointer; text-decoration: none;
            display: inline-block; transition: background 0.2s; text-align: center;
        }
        .btn-link-style:hover { background: #004494; color: #fff; }

        .sp-footer { margin-top: 22px; text-align: center; font-size: 13px; color: #64748b; }
        .sp-footer a { color: var(--primary-color); text-decoration: none; font-weight: 600; }
        .sp-footer a:hover { text-decoration: underline; }

        @media (max-width: 600px) {
            .main-wrapper { padding-left: 10px; padding-right: 10px; }
            .req-list { grid-template-columns: 1fr; gap: 5px; }
            .auth-header h2 { font-size: 18px; }
        }
    </style>
    <script src="/js/spa.js" defer></script>
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
        </div>
    </nav>

    <div class="main-wrapper">
        <div class="auth-container">

            @if($invalid ?? false)
            {{-- ─── Invalid / expired token ─── --}}
            <div class="invalid-card">
                <div class="inv-icon"><i class="fas fa-link-slash"></i></div>
                <h3>Link expired or invalid</h3>
                <p>This password reset link is invalid or has already been used. Reset links expire after 60 minutes.</p>
                <a href="/forgot-password" class="btn-link-style">Request a New Link</a>
            </div>

            @else
            {{-- ─── Valid token — show the form ─── --}}
            <div class="auth-header">
                <h2>Set a new password</h2>
                <p>Create a strong password for <strong><!--email_off-->{{ $email }}<!--/email_off--></strong>. You'll be signed in automatically after.</p>
            </div>

            <div id="success-message" class="sp-alert success" style="display: none;">
                <i class="fas fa-check-circle"></i> <span>Password updated! Redirecting to login…</span>
            </div>
            <div id="error-message" class="sp-alert danger" style="display: none;">
                <i class="fas fa-exclamation-circle"></i> <span></span>
            </div>

            <form id="reset-form" autocomplete="off" novalidate>
                <input type="hidden" id="token" value="{{ $token }}">
                <input type="hidden" id="email" value="{{ $email }}">

                <!-- New Password -->
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" class="form-control"
                               placeholder="Enter new password"
                               autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                        <button type="button" class="toggle-pw" onclick="togglePw('password', this)">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Strength meter -->
                <div class="strength-section">
                    <div class="strength-label">
                        <span class="strength-label-text">Password strength</span>
                        <span class="strength-value" id="strengthValue">Weak</span>
                    </div>
                    <div class="strength-bar-row">
                        <div class="strength-segment" id="seg1"></div>
                        <div class="strength-segment" id="seg2"></div>
                        <div class="strength-segment" id="seg3"></div>
                        <div class="strength-segment" id="seg4"></div>
                        <div class="strength-segment" id="seg5"></div>
                    </div>
                    <ul class="req-list">
                        <li id="req-length"><span class="req-icon"><i class="fas fa-check"></i></span> 8+ characters</li>
                        <li id="req-upper"><span class="req-icon"><i class="fas fa-check"></i></span> Uppercase</li>
                        <li id="req-lower"><span class="req-icon"><i class="fas fa-check"></i></span> Lowercase</li>
                        <li id="req-number"><span class="req-icon"><i class="fas fa-check"></i></span> Number</li>
                        <li id="req-special"><span class="req-icon"><i class="fas fa-check"></i></span> Symbol</li>
                    </ul>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password_confirmation" class="form-control"
                               placeholder="Repeat password"
                               autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                        <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation', this)">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <div class="match-hint" id="matchHint">
                        <i id="matchIcon"></i>
                        <span id="matchText"></span>
                    </div>
                </div>

                <button type="submit" id="submit-btn" class="btn-set-pw" disabled>
                    Update Password
                </button>
            </form>

            <div class="sp-footer">
                Remember it now? <a href="/login">Sign in</a>
            </div>
            @endif

        </div>
    </div>

    <script>
        function togglePw(id, btn) {
            var input = document.getElementById(id);
            var icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text'; icon.className = 'far fa-eye-slash';
            } else {
                input.type = 'password'; icon.className = 'far fa-eye';
            }
        }

        @if(!($invalid ?? false))
        (function () {
            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var pw = document.getElementById('password');
            var confirm = document.getElementById('password_confirmation');
            var submitBtn = document.getElementById('submit-btn');
            var strengthValue = document.getElementById('strengthValue');
            var matchHint = document.getElementById('matchHint');
            var matchIcon = document.getElementById('matchIcon');
            var matchText = document.getElementById('matchText');

            var segments = ['seg1','seg2','seg3','seg4','seg5'].map(function(id){ return document.getElementById(id); });

            var reqs = {
                length:  { regex: /.{8,}/,       el: document.getElementById('req-length') },
                upper:   { regex: /[A-Z]/,        el: document.getElementById('req-upper') },
                lower:   { regex: /[a-z]/,        el: document.getElementById('req-lower') },
                number:  { regex: /[0-9]/,        el: document.getElementById('req-number') },
                special: { regex: /[^A-Za-z0-9]/, el: document.getElementById('req-special') }
            };

            var colors = { weak:'#ef4444', fair:'#f59e0b', good:'#3b82f6', strong:'#10b981' };

            function update() {
                var val  = pw.value;
                var conf = confirm.value;
                var passed = 0;

                for (var key in reqs) {
                    var met = reqs[key].regex.test(val);
                    reqs[key].el.classList.toggle('met', met);
                    if (met) passed++;
                }

                var color, label;
                if (passed <= 2)      { color = colors.weak;   label = 'Weak'; }
                else if (passed <= 3) { color = colors.fair;   label = 'Fair'; }
                else if (passed <= 4) { color = colors.good;   label = 'Good'; }
                else                  { color = colors.strong; label = 'Strong'; }

                segments.forEach(function(seg, i) {
                    seg.style.backgroundColor = i < passed ? color : '#e2e8f0';
                });
                strengthValue.textContent = label;
                strengthValue.style.color = color;

                if (conf.length > 0) {
                    matchHint.classList.add('show');
                    if (val === conf) {
                        matchHint.className = 'match-hint show matched';
                        matchIcon.className = 'fas fa-check-circle';
                        matchText.textContent = 'Passwords match';
                        confirm.style.borderColor = '';
                    } else {
                        matchHint.className = 'match-hint show no-match';
                        matchIcon.className = 'fas fa-times-circle';
                        matchText.textContent = 'Passwords do not match';
                        confirm.style.borderColor = '#dc2626';
                    }
                } else {
                    matchHint.classList.remove('show');
                    confirm.style.borderColor = '';
                }

                submitBtn.disabled = !(passed === 5 && val === conf && conf !== '');
            }

            pw.addEventListener('input', update);
            confirm.addEventListener('input', update);

            document.getElementById('reset-form').addEventListener('submit', function (e) {
                e.preventDefault();
                if (pw.value !== confirm.value) return;

                var errorMsg   = document.getElementById('error-message');
                var successMsg = document.getElementById('success-message');
                errorMsg.style.display = 'none';
                successMsg.style.display = 'none';

                var btn = submitBtn;
                var orig = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<span class="loading-dots"><span></span></span>';

                fetch('/api/reset-password', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        token: document.getElementById('token').value,
                        email: document.getElementById('email').value,
                        password: pw.value,
                        password_confirmation: confirm.value
                    })
                })
                .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
                .then(function (res) {
                    if (res.ok && res.data.success) {
                        successMsg.style.display = 'flex';
                        setTimeout(function () { window.location.href = '/login'; }, 1800);
                    } else {
                        errorMsg.querySelector('span').textContent = res.data.message || 'Failed to reset password.';
                        errorMsg.style.display = 'flex';
                        btn.disabled = false;
                        btn.innerHTML = orig;
                    }
                })
                .catch(function () {
                    errorMsg.querySelector('span').textContent = 'Network error. Please try again.';
                    errorMsg.style.display = 'flex';
                    btn.disabled = false;
                    btn.innerHTML = orig;
                });
            });
        })();
        @endif
    </script>
</body>
</html>
