<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo csrf_token(); ?>">
    <title>Create Account - DepEd DTS</title>
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
        
        /* Layout Overrides for Compact Landscape Registration */
        .main-wrapper { 
            max-width: 520px !important; 
            width: 95% !important;
            justify-content: center !important;
            padding-top: 20px;
            padding-bottom: 20px;
            min-height: calc(100vh - 70px);
            margin: 0 auto !important;
        }

        .auth-container { 
            max-width: 500px !important; 
            width: 100%;
            padding: 30px !important;
        } 
        
        .auth-header {
            margin-bottom: 15px !important;
        }

        .auth-header img {
            height: 50px !important;
            margin-bottom: 10px !important;
        }

        .auth-header h2 {
            font-size: 24px !important;
            margin-bottom: 5px !important;
        }

        .auth-header p {
            margin-bottom: 15px !important;
            font-size: 13px !important;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px; /* Reduced gap */
            margin-top: 10px;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }

        .form-group {
            margin-bottom: 0; 
        }

        .form-label {
            font-size: 13px !important;
            margin-bottom: 4px !important;
        }
        
        .form-control {
            padding: 8px 12px !important; /* Compact inputs */
            font-size: 14px !important;
        }

        .account-type-selector {
            margin-bottom: 0 !important;
        }

        .type-option {
            padding: 8px !important;
            font-size: 13px !important;
        }

        .btn-register {
            width: 100% !important;
            padding: 12px !important; /* Balanced padding */
            font-size: 15px !important; /* Readable size */
            border-radius: 8px !important;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .auth-container {
                padding: 20px !important;
            }
            .main-wrapper {
                justify-content: flex-start !important;
            }
        }
        .dash-footer{width:100%;background:#fff;border-top:1px solid #e2e8f0;padding:20px 5%;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8;margin-top:40px}
        .footer-left{display:flex;align-items:center;gap:6px}
        .footer-right{font-size:11px;color:#b0b8c4}
        @media(max-width:768px){.dash-footer{flex-direction:column;gap:6px;text-align:center;padding:16px 5%}}
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
                <a href="/login" class="back-link" aria-label="Back" title="Back" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;margin-bottom:12px;">
                    <span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" /></svg>
                    </span>
                </a>
                <h2>Create Account</h2>
                <p>Complete the form below to register your account.</p>
            </div>

            <!-- Account Type Selector -->
            <div class="form-group">
                <label class="form-label">Account Type</label>
                <div class="account-type-selector">
                    <div class="type-option active" data-type="individual" onclick="setAccountType('individual')">
                        Individual
                    </div>
                    <div class="type-option" data-type="representative" onclick="setAccountType('representative')">
                        Representative / Office
                    </div>
                </div>
            </div>

            <form id="registerForm"yung trac novalidate autocomplete="off">
                <input type="hidden" id="accountType" value="individual">

                <div class="form-grid">
                    <!-- Individual Fields -->
                    <div id="individualFields" class="full-width" style="display: contents;">
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" id="firstName" autocomplete="one-time-code" readonly onfocus="this.removeAttribute('readonly');">
                            <div class="error-alert" id="firstNameError"><i class="fas fa-exclamation-circle"></i><span>Required</span></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="lastName" autocomplete="one-time-code" readonly onfocus="this.removeAttribute('readonly');">
                            <div class="error-alert" id="lastNameError"><i class="fas fa-exclamation-circle"></i><span>Required</span></div>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="middleName" autocomplete="one-time-code" readonly onfocus="this.removeAttribute('readonly');">
                            <div class="error-alert" id="middleNameError"><i class="fas fa-exclamation-circle"></i><span>Required</span></div>
                            <label style="display: flex; align-items: center; gap: 6px; margin-top: 6px; cursor: pointer; font-size: 13px; color: #64748b; font-weight: 400; user-select: none;">
                                <input type="checkbox" id="noMiddleName" onchange="toggleMiddleName()" style="width: 16px; height: 16px; accent-color: #0056b3; cursor: pointer;">
                                I don't have a middle name
                            </label>
                        </div>
                    </div>

                    <!-- Representative Fields -->
                    <div id="representativeFields" class="full-width" style="display: none;">
                        <div class="form-group full-width">
                            <label class="form-label">Office / School Name</label>
                            <select class="form-control" id="officeName">
                                <option value="">Select Office / School Name</option>
                                @foreach (config('representative_offices', []) as $officeOption)
                                    <option value="{{ $officeOption }}">{{ $officeOption }}</option>
                                @endforeach
                            </select>
                            <div class="error-alert" id="officeNameError"><i class="fas fa-exclamation-circle"></i><span>Required</span></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">First Name</label>
                            <input type="text" class="form-control" id="repFirstName" autocomplete="one-time-code" readonly onfocus="this.removeAttribute('readonly');">
                            <div class="error-alert" id="repFirstNameError"><i class="fas fa-exclamation-circle"></i><span>Required</span></div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Last Name</label>
                            <input type="text" class="form-control" id="repLastName" autocomplete="one-time-code" readonly onfocus="this.removeAttribute('readonly');">
                            <div class="error-alert" id="repLastNameError"><i class="fas fa-exclamation-circle"></i><span>Required</span></div>
                        </div>
                        <div class="form-group full-width">
                            <label class="form-label">Middle Name</label>
                            <input type="text" class="form-control" id="repMiddleName" autocomplete="one-time-code" readonly onfocus="this.removeAttribute('readonly');">
                            <div class="error-alert" id="repMiddleNameError"><i class="fas fa-exclamation-circle"></i><span>Required</span></div>
                            <label style="display: flex; align-items: center; gap: 6px; margin-top: 6px; cursor: pointer; font-size: 13px; color: #64748b; font-weight: 400; user-select: none;">
                                <input type="checkbox" id="noRepMiddleName" onchange="toggleRepMiddleName()" style="width: 16px; height: 16px; accent-color: #0056b3; cursor: pointer;">
                                I don't have a middle name
                            </label>
                        </div>
                    </div>

                    <!-- Common Fields -->
                    <div class="form-group">
                        <label class="form-label">Mobile Number</label>
                        <input type="tel" class="form-control" id="mobile" autocomplete="one-time-code" readonly onfocus="this.removeAttribute('readonly');">
                        <div class="error-alert" id="mobileError"><i class="fas fa-exclamation-circle"></i><span>Required</span></div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="regEmail" autocomplete="email" inputmode="email" autocapitalize="none" autocorrect="off" spellcheck="false" readonly onfocus="this.removeAttribute('readonly');">
                        <div class="error-alert" id="regEmailError"><i class="fas fa-exclamation-circle"></i><span>Required</span></div>
                    </div>
                </div>

                <div style="margin-top: 15px; text-align: center;">
                    <button type="button" class="btn btn-primary btn-register" onclick="validateAndOpenModal()">
                        Create Account
                    </button>
                    <div style="text-align: center; margin-top: 10px;">
                        <span style="color: #64748b; font-size: 13px;">Already have an account?</span>
                        <a href="/login" style="color: var(--primary-color); text-decoration: none; font-weight: 600; font-size: 13px; margin-left: 4px;">Sign in</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Privacy Modal -->
    <div class="modal-overlay" id="privacyModal">
        <div class="modal-content privacy-modal">
            <div class="modal-header privacy-header">
                <div class="privacy-header-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                </div>
                <h3>Data Privacy Notice and Consent</h3>
                <p class="privacy-subtitle">(Republic Act No. 10173 &ndash; Data Privacy Act of 2012)</p>
            </div>
            <div class="modal-body privacy-body" id="modalBody" onscroll="checkScroll()">
                <p class="privacy-intro">In compliance with the Data Privacy Act of 2012 (Republic Act No. 10173), its Implementing Rules and Regulations, and the issuances of the National Privacy Commission (NPC), this Notice explains how <strong>CSJDM &ndash; Division Office</strong> (&ldquo;the Office&rdquo;) collects, uses, stores, and protects personal data through its Document Tracking System (&ldquo;the System&rdquo;).</p>

                <div class="privacy-section">
                    <h4><span class="section-number">1</span> Purpose of Collection</h4>
                    <p>CSJDM &ndash; Division Office collects and processes personal data for legitimate and specific purposes, including but not limited to:</p>
                    <ul>
                        <li>Account registration, authentication, and user management</li>
                        <li>Processing, routing, tracking, and monitoring of documents and transactions</li>
                        <li>Sending notifications, alerts, updates, and verification messages (e.g., email, OTP)</li>
                        <li>Maintaining audit trails and activity logs for accountability and security</li>
                        <li>Compliance with legal, regulatory, and institutional requirements</li>
                    </ul>
                </div>

                <div class="privacy-section">
                    <h4><span class="section-number">2</span> Personal Data Collected</h4>
                    <p>Depending on the type of account registered, the System may collect the following information:</p>
                    <ul>
                        <li>Full name (for Individual accounts)</li>
                        <li>Office, department, or institution name (for Representative/Office accounts)</li>
                        <li>Authorized representative name and office/department (if applicable)</li>
                        <li>Email address and mobile number</li>
                        <li>Login credentials and system identifiers</li>
                        <li>Transaction-related data, remarks, attachments, and document metadata</li>
                        <li>Other information voluntarily provided while using the System</li>
                    </ul>
                </div>

                <div class="privacy-section">
                    <h4><span class="section-number">3</span> Legal Basis for Processing</h4>
                    <p>The processing of personal data is carried out based on any of the following, as applicable:</p>
                    <ul>
                        <li>Consent of the data subject</li>
                        <li>Performance of official functions and transactions</li>
                        <li>Compliance with legal obligations</li>
                        <li>Legitimate interests of CSJDM &ndash; Division Office, consistent with the Data Privacy Act</li>
                    </ul>
                </div>

                <div class="privacy-section">
                    <h4><span class="section-number">4</span> Data Sharing and Disclosure</h4>
                    <p>Personal data shall not be shared with unauthorized third parties. Disclosure may only occur when:</p>
                    <ul>
                        <li>Necessary for official system operations and institutional processes</li>
                        <li>Required by law, regulation, or lawful order of a government authority</li>
                        <li>Expressly consented to by the data subject</li>
                    </ul>
                    <p>All authorized personnel and recipients are bound by confidentiality and data protection obligations.</p>
                </div>

                <div class="privacy-section">
                    <h4><span class="section-number">5</span> Data Storage, Security, and Retention</h4>
                    <p>CSJDM &ndash; Division Office implements reasonable and appropriate organizational, physical, and technical security measures to protect personal data against loss, misuse, unauthorized access, alteration, or disclosure.</p>
                    <p>Personal data shall be retained only for as long as necessary to fulfill the stated purposes or as required by applicable laws and records retention policies, after which it shall be securely disposed of.</p>
                </div>

                <div class="privacy-section">
                    <h4><span class="section-number">6</span> Rights of the Data Subject</h4>
                    <p>In accordance with the Data Privacy Act, data subjects have the right to:</p>
                    <ul>
                        <li>Be informed about the processing of their personal data</li>
                        <li>Access their personal data</li>
                        <li>Object to the processing of their data</li>
                        <li>Request correction of inaccurate or incomplete data</li>
                        <li>Request erasure or blocking of data, subject to legal and operational limitations</li>
                        <li>Withdraw consent, where applicable</li>
                        <li>Lodge a complaint with the National Privacy Commission (NPC)</li>
                    </ul>
                    <p>Requests are subject to verification and applicable laws and regulations.</p>
                </div>

                <div class="privacy-section">
                    <h4><span class="section-number">7</span> Contact Information</h4>
                    <p>For questions, concerns, or requests regarding personal data, please contact:</p>
                    <div class="contact-card">
                        <strong>Information Technology Officer I (ITO)</strong><br>
                        CSJDM &ndash; Division Office<br>
                        <span class="contact-placeholder">arthur.francisco@deped.gov.ph</span><br>
                        <span class="contact-placeholder">0999999999999</span>
                    </div>
                </div>

                <div class="privacy-section consent-section">
                    <h4><span class="section-number">8</span> Consent</h4>
                    <p>By clicking &ldquo;<strong>I Agree</strong>&rdquo;, you confirm that you have read and understood this Data Privacy Notice and voluntarily give your consent to the collection, processing, storage, and use of your personal data by CSJDM &ndash; Division Office, in accordance with Republic Act No. 10173.</p>
                </div>
            </div>
            <div class="modal-footer privacy-footer">
                <p class="scroll-hint"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg> Scroll to the bottom to enable &ldquo;I Agree&rdquo;</p>
                <div class="privacy-actions">
                    <button class="btn privacy-btn-decline" onclick="closePrivacyModal()">Decline</button>
                    <button class="btn privacy-btn-agree" id="agreeBtn" disabled onclick="submitForm()">I Agree</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Pre-fill email if redirected from login
        (function() {
            const params = new URLSearchParams(window.location.search);
            const email = params.get('email');
            if (email) {
                const emailInput = document.getElementById('regEmail');
                if (emailInput) emailInput.value = email;
            }
        })();

        // Restrict Input Fields
        document.addEventListener('DOMContentLoaded', () => {
             // Name Fields: Letters, spaces, dots, and hyphens only
             ['firstName', 'middleName', 'lastName', 'repFirstName', 'repMiddleName', 'repLastName'].forEach(id => {
                 const el = document.getElementById(id);
                 if(el) {
                    el.addEventListener('input', function(e) {
                        this.value = this.value.replace(/[^a-zA-Z\s.\-]/g, '');
                    });
                 }
             });

             // Mobile: Numbers only, max 11 digits
             const mobileEl = document.getElementById('mobile');
             if(mobileEl) {
                mobileEl.addEventListener('input', function(e) {
                    // Remove non-numbers
                    this.value = this.value.replace(/[^0-9]/g, '');
                    // Limit to 11 digits
                    if (this.value.length > 11) {
                        this.value = this.value.slice(0, 11);
                    }
                });
             }
        });

        // Init: Pre-fill email from URL if available
        const urlParams = new URLSearchParams(window.location.search);
        const emailParam = urlParams.get('email');
        if (emailParam) {
            document.getElementById('regEmail').value = emailParam;
        }

        function toggleRepMiddleName() {
            const checkbox = document.getElementById('noRepMiddleName');
            const input    = document.getElementById('repMiddleName');
            if (checkbox.checked) {
                input.value = '';
                input.disabled = true;
                input.style.opacity = '0.5';
                input.classList.remove('error');
                const errEl = document.getElementById('repMiddleNameError');
                if (errEl) errEl.classList.remove('show');
            } else {
                input.disabled = false;
                input.style.opacity = '1';
            }
        }

        function toggleMiddleName() {
            const checkbox = document.getElementById('noMiddleName');
            const input = document.getElementById('middleName');
            if (checkbox.checked) {
                input.value = '';
                input.disabled = true;
                input.style.opacity = '0.5';
                // Clear any error on middle name
                input.classList.remove('error');
                const errEl = document.getElementById('middleNameError');
                if (errEl) errEl.classList.remove('show');
            } else {
                input.disabled = false;
                input.style.opacity = '1';
            }
        }

        function setAccountType(type) {
            const options = document.querySelectorAll('.type-option');
            options.forEach(opt => opt.classList.remove('active'));
            document.querySelector(`[data-type="${type}"]`).classList.add('active');
            document.getElementById('accountType').value = type;

            const indiv = document.getElementById('individualFields');
            const rep   = document.getElementById('representativeFields');
            if (type === 'representative') {
                indiv.style.display = 'none';
                rep.style.display   = 'contents';
            } else {
                indiv.style.display = 'contents';
                rep.style.display   = 'none';
            }
        }
        
        function validateAndOpenModal() {
            // Reset errors
            document.querySelectorAll('.form-control').forEach(el => el.classList.remove('error'));
            document.querySelectorAll('.error-alert').forEach(el => el.classList.remove('show'));

            let isValid = true;
            const type = document.getElementById('accountType').value;

            // Helper to check validation
            const check = (id, message) => {
                const el = document.getElementById(id);
                if (!el.value.trim()) {
                    showError(id, message || 'This field is required');
                    isValid = false;
                }
            };

            if (type === 'representative') {
                check('officeName');
                check('repFirstName');
                check('repLastName');
                const noRepMid = document.getElementById('noRepMiddleName').checked;
                if (!noRepMid) {
                    check('repMiddleName');
                }
            } else {
                check('firstName');
                check('lastName');
                const noMid = document.getElementById('noMiddleName').checked;
                if (!noMid) {
                    check('middleName');
                }
            }

            check('mobile');
            // Strict Mobile Length and Prefix Check
            const mobileVal = document.getElementById('mobile').value;
            if (mobileVal) {
                if (mobileVal.length !== 11) {
                    showError('mobile', 'Must be exactly 11 digits');
                    isValid = false;
                } else if (!mobileVal.startsWith('09')) {
                    showError('mobile', 'Must start with 09');
                    isValid = false;
                }
            }

            check('regEmail');

            if (isValid) {
                // Reset scroll and disable agree button each time modal opens
                const modalBody = document.getElementById('modalBody');
                modalBody.scrollTop = 0;
                document.getElementById('agreeBtn').disabled = true;
                document.getElementById('privacyModal').classList.add('show');
            }
        }

        function showError(id, message) {
            const el = document.getElementById(id);
            // Handling for name fields which are slightly different in structure
            const alertBox = document.getElementById(id + 'Error') || el.parentElement.querySelector('.error-alert');
            
            if (alertBox) {
                alertBox.querySelector('span').innerText = message;
                alertBox.classList.add('show');
            }
            el.classList.add('error');
        }

        function closePrivacyModal() {
            document.getElementById('privacyModal').classList.remove('show');
        }

        function checkScroll() {
            const body = document.getElementById('modalBody');
            const btn = document.getElementById('agreeBtn');
            // Check if scrolled near bottom
            if (body.scrollHeight - body.scrollTop <= body.clientHeight + 20) {
                btn.disabled = false;
            }
        }

        async function submitForm() {
            closePrivacyModal();
            
            const btn = document.querySelector('button[onclick="validateAndOpenModal()"]');
            const originalText = btn.innerText;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            btn.innerHTML = '<span class="loading-dots"><span></span></span>';
            btn.disabled = true;

            const type = document.getElementById('accountType').value;
            let name;
            if (type === 'representative') {
                const office   = document.getElementById('officeName').value.trim();
                const repFirst = document.getElementById('repFirstName').value.trim();
                const repMid   = document.getElementById('repMiddleName').value.trim();
                const repLast  = document.getElementById('repLastName').value.trim();
                const noRepMid = document.getElementById('noRepMiddleName').checked;
                const repName  = (noRepMid || !repMid) ? (repFirst + ' ' + repLast) : (repFirst + ' ' + repMid + ' ' + repLast);
                name = office + ' - ' + repName;
            } else {
                const first  = document.getElementById('firstName').value.trim();
                const middle = document.getElementById('middleName').value.trim();
                const last   = document.getElementById('lastName').value.trim();
                const noMid  = document.getElementById('noMiddleName').checked;
                name = (noMid || !middle) ? (first + ' ' + last) : (first + ' ' + middle + ' ' + last);
            }

            const formData = {
                name: name,
                email: document.getElementById('regEmail').value,
                mobile: document.getElementById('mobile').value,
                account_type: type,
            };

            if (type === 'representative') {
                formData.office_name = document.getElementById('officeName').value.trim();
            }
            
            try {
                const response = await fetch('/api/register', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });
                
                let data;
                try {
                    data = await response.json();
                } catch (parseErr) {
                    // Server returned non-JSON (e.g. 500 HTML error page)
                    const text = await response.clone().text();
                    console.error('Server error (non-JSON):', response.status, text.substring(0, 500));
                    alert('Server error (' + response.status + '). Please try again.');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                    return;
                }
                
                if (data.success) {
                    // Show success message instead of redirecting
                    document.getElementById('registerForm').style.display = 'none';
                    document.querySelector('.account-type-selector')?.parentElement?.style && (document.querySelector('.account-type-selector').parentElement.style.display = 'none');
                    
                    // Shrink container for success message
                    const authContainer = document.querySelector('.auth-container');
                    authContainer.style.maxWidth = '450px';
                    authContainer.style.transition = 'max-width 0.3s ease';
                    
                    const header = document.querySelector('.auth-header');
                    header.innerHTML = `
                            <h2>Check Your Email</h2>
                            <p>We've sent an activation link to <strong style="color: #1e293b;">${formData.email}</strong>.<br>Open it to set your password and activate your account.</p>
                            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; padding: 14px 18px; text-align: left; font-size: 13px; color: #92400e; margin-bottom: 20px; margin-top: 20px;">
                                <strong>Note:</strong> The link expires in 60 minutes. If you don't see the email, check your spam folder.
                            </div>
                            <a href="/login" class="btn btn-primary btn-register" style="display: block; text-decoration: none; text-align: center;">
                                Go to Login
                            </a>
                            <div style="margin-top: 14px;">
                                <button type="button" onclick="resendActivation('${formData.email}')" id="resendBtn" style="background: none; border: none; color: #0056b3; font-size: 13px; font-weight: 500; cursor: pointer; text-decoration: underline; font-family: Poppins, sans-serif;">
                                    Didn't receive it? Resend email
                                </button>
                            </div>
                    `;
                } else if (response.status === 422) {
                    // Validation errors
                    const errors = data.errors || {};
                    if (errors.email) {
                        showError('regEmail', errors.email[0]);
                    }
                    if (errors.office_name) {
                        showError('officeName', errors.office_name[0]);
                    }
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                } else {
                    alert(data.message || 'Error creating account');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (err) {
                console.error('Registration error:', err);
                let errorMsg = 'An error occurred. Please try again.';
                if (err.message) errorMsg += '\n\nDetails: ' + err.message;
                alert(errorMsg);
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        }

        // Reset form if user navigates back via browser cache
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                // Page was restored from back/forward cache — reset everything
                document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"]').forEach(el => {
                    el.value = '';
                    el.classList.remove('error');
                    el.style.borderColor = '';
                });
                document.querySelectorAll('.error-alert').forEach(el => el.classList.remove('show'));
                document.querySelectorAll('input[type="checkbox"]').forEach(el => el.checked = false);
                document.getElementById('middleName').disabled = false;
                document.getElementById('middleName').style.opacity = '1';
                document.getElementById('repMiddleName').disabled = false;
                document.getElementById('repMiddleName').style.opacity = '1';
                const officeName = document.getElementById('officeName');
                if (officeName) {
                    officeName.value = '';
                    officeName.classList.remove('error');
                }
                setAccountType('individual');
                const btn = document.querySelector('button[onclick="validateAndOpenModal()"]');
                if (btn) { btn.innerHTML = 'Create Account'; btn.disabled = false; }
            }
        });

        // Init default state
        setAccountType('individual');

        // Resend activation email
        async function resendActivation(email) {
            const btn = document.getElementById('resendBtn');
            const originalText = btn.innerText;
            btn.innerHTML = '<span class="loading-dots"><span></span></span>';
            btn.disabled = true;
            btn.style.opacity = '0.5';

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            try {
                const response = await fetch('/api/resend-activation', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ email: email }),
                });
                const data = await response.json();

                if (data.success) {
                    btn.innerText = 'Email sent!';
                    btn.style.color = '#16a34a';
                    setTimeout(() => {
                        btn.innerText = originalText;
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        btn.style.color = '#0056b3';
                    }, 5000);
                } else {
                    btn.innerText = data.message || 'Failed to resend';
                    btn.style.color = '#dc2626';
                    setTimeout(() => {
                        btn.innerText = originalText;
                        btn.disabled = false;
                        btn.style.opacity = '1';
                        btn.style.color = '#0056b3';
                    }, 4000);
                }
            } catch (err) {
                btn.innerText = 'Error. Try again.';
                btn.style.color = '#dc2626';
                setTimeout(() => {
                    btn.innerText = originalText;
                    btn.disabled = false;
                    btn.style.opacity = '1';
                    btn.style.color = '#0056b3';
                }, 3000);
            }
        }
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
