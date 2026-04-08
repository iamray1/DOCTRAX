<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title>Set Password - DepEd DTS</title>
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

        /* Password toggle */
        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 44px;
        }

        .toggle-pw {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #94a3b8;
            padding: 4px;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }
        .toggle-pw:hover { color: #475569; }

        /* Strength section — appears below password field */
        .strength-section {
            margin-top: -6px;
            margin-bottom: 20px;
        }

        .strength-bar-row {
            display: flex;
            gap: 4px;
            margin-bottom: 10px;
        }

        .strength-segment {
            flex: 1;
            height: 4px;
            border-radius: 99px;
            background: #e2e8f0;
            transition: background-color 0.35s ease;
        }

        .strength-label {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .strength-label-text {
            font-size: 12px;
            font-weight: 600;
            color: #64748b;
        }

        .strength-value {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            color: #94a3b8;
            transition: color 0.3s;
        }

        /* Requirement checklist */
        .req-list {
            list-style: none;
            margin: 0;
            padding: 0;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 6px 16px;
        }

        .req-list li {
            display: flex;
            align-items: center;
            gap: 7px;
            font-size: 12px;
            color: #94a3b8;
            transition: color 0.25s;
        }

        .req-list li .req-icon {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 1.5px solid #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: transparent;
            transition: all 0.25s;
            flex-shrink: 0;
        }

        .req-list li.met {
            color: #059669;
        }

        .req-list li.met .req-icon {
            background: #059669;
            border-color: #059669;
            color: #fff;
        }

        /* Match indicator under confirm field */
        .match-hint {
            font-size: 12px;
            margin-top: 6px;
            display: none;
            align-items: center;
            gap: 5px;
        }
        .match-hint.show { display: flex; }
        .match-hint.no-match { color: #dc2626; }
        .match-hint.matched { color: #059669; }

        /* Submit button */
        .btn-set-pw {
            width: 100%;
            padding: 13px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 4px;
        }

        .btn-set-pw:hover:not(:disabled) {
            background: #004494;
        }

        .btn-set-pw:disabled {
            background: #94a3b8;
            cursor: not-allowed;
        }

        /* Inline alerts */
        .sp-alert {
            padding: 10px 14px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: fadeIn 0.3s ease;
        }

        .sp-alert.success {
            background-color: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .sp-alert.danger {
            background-color: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        /* Footer */
        .sp-footer {
            margin-top: 22px;
            text-align: center;
            font-size: 13px;
            color: #64748b;
        }

        .sp-footer a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
        }

        .sp-footer a:hover {
            text-decoration: underline;
        }

        /* Mobile */
        @media (max-width: 600px) {
            .main-wrapper {
                padding-left: 10px;
                padding-right: 10px;
            }

            .req-list {
                grid-template-columns: 1fr;
                gap: 5px;
            }

            .auth-header h2 {
                font-size: 18px;
            }
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
            <a href="/about-us" class="nav-link"><i class="fas fa-info-circle"></i> About Us</a>
            <a href="/contact-us" class="nav-link"><i class="fas fa-envelope"></i> Contact Us</a>
        </div>
    </nav>

    <div class="main-wrapper">
        <div class="auth-container">
            <div class="auth-header">
                <a href="/login" style="display: inline-flex; align-items: center; gap: 6px; color: #64748b; text-decoration: none; font-size: 13px; font-weight: 500; margin-bottom: 12px; transition: color 0.2s;" onmouseover="this.style.color='#0056b3'" onmouseout="this.style.color='#64748b'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                    Back
                </a>
                <h2>Set your password</h2>
                <p>Create a strong password to activate your account.</p>
            </div>

            <!-- Alerts -->
            <div id="success-message" class="sp-alert success" style="display: none;">
                <i class="fas fa-check-circle"></i> <span>Password set successfully! Redirecting…</span>
            </div>
            <div id="error-message" class="sp-alert danger" style="display: none;">
                <i class="fas fa-exclamation-circle"></i> <span></span>
            </div>

            <form id="set-password-form" method="POST" action="/api/activate" autocomplete="off">
                @csrf
                <input type="hidden" name="token" value="{{ $token ?? '' }}">
                <input type="hidden" name="email" value="{{ $email ?? '' }}">

                <!-- New Password -->
                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password" required autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
                        <button type="button" class="toggle-pw" onclick="togglePw('password', this)">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Strength meter -->
                <div class="strength-section" id="strengthSection">
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
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Repeat password" required autocomplete="off" readonly onfocus="this.removeAttribute('readonly');">
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
                    Set Password
                </button>
            </form>

            <div class="sp-footer">
                Already have an account? <a href="/login">Sign in</a>
            </div>
        </div>
    </div>

    <script>
        function togglePw(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'far fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'far fa-eye';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const pw = document.getElementById('password');
            const confirm = document.getElementById('password_confirmation');
            const submitBtn = document.getElementById('submit-btn');
            const strengthSection = document.getElementById('strengthSection');
            const strengthValue = document.getElementById('strengthValue');
            const matchHint = document.getElementById('matchHint');
            const matchIcon = document.getElementById('matchIcon');
            const matchText = document.getElementById('matchText');

            const segments = [
                document.getElementById('seg1'),
                document.getElementById('seg2'),
                document.getElementById('seg3'),
                document.getElementById('seg4'),
                document.getElementById('seg5')
            ];

            const reqs = {
                length:  { regex: /.{8,}/,       el: document.getElementById('req-length') },
                upper:   { regex: /[A-Z]/,        el: document.getElementById('req-upper') },
                lower:   { regex: /[a-z]/,        el: document.getElementById('req-lower') },
                number:  { regex: /[0-9]/,        el: document.getElementById('req-number') },
                special: { regex: /[^A-Za-z0-9]/, el: document.getElementById('req-special') }
            };

            const colors = {
                weak:   '#ef4444',
                fair:   '#f59e0b',
                good:   '#3b82f6',
                strong: '#10b981'
            };

            function update() {
                const val = pw.value;
                const conf = confirm.value;

                // Strength section is always visible

                let passed = 0;
                for (const key in reqs) {
                    const met = reqs[key].regex.test(val);
                    reqs[key].el.classList.toggle('met', met);
                    if (met) passed++;
                }

                // Determine color & label
                let color, label;
                if (passed <= 1)      { color = colors.weak;   label = 'Weak'; }
                else if (passed <= 2) { color = colors.weak;   label = 'Weak'; }
                else if (passed <= 3) { color = colors.fair;   label = 'Fair'; }
                else if (passed <= 4) { color = colors.good;   label = 'Good'; }
                else                  { color = colors.strong;  label = 'Strong'; }

                // Fill segments
                segments.forEach((seg, i) => {
                    seg.style.backgroundColor = i < passed ? color : '#e2e8f0';
                });

                strengthValue.textContent = label;
                strengthValue.style.color = color;

                // Match hint
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

                // Enable submit only when all 5 requirements met + passwords match
                submitBtn.disabled = !(passed === 5 && val === conf && conf !== '');
            }

            pw.addEventListener('input', update);
            confirm.addEventListener('input', update);

            // Form submit
            const form = document.getElementById('set-password-form');
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                if (pw.value !== confirm.value) return;

                const btn = submitBtn;
                const originalText = btn.innerHTML;
                const errorMsg = document.getElementById('error-message');
                const successMsg = document.getElementById('success-message');

                errorMsg.style.display = 'none';
                successMsg.style.display = 'none';

                btn.disabled = true;
                btn.innerHTML = '<span class="loading-dots"><span></span></span>';

                try {
                    const formData = new FormData(this);
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const headers = {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    };
                    if (csrfMeta) headers['X-CSRF-TOKEN'] = csrfMeta.getAttribute('content');

                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData,
                        headers: headers
                    });

                    const data = await response.json();

                    if (response.ok) {
                        successMsg.style.display = 'flex';
                        setTimeout(() => { window.location.href = '/login'; }, 1500);
                    } else {
                        throw new Error(data.message || 'Failed to set password.');
                    }
                } catch (err) {
                    errorMsg.querySelector('span').textContent = err.message;
                    errorMsg.style.display = 'flex';
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            });
        });
    </script>
</body>
</html>
