<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Receive Document - DepEd DTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root{--primary:#0056b3;--primary-dark:#004494;--bg:#f0f2f5;--border:#e2e8f0;--text-dark:#1b263b;--text-muted:#64748b}
        *{margin:0;padding:0;box-sizing:border-box}
        body{background:var(--bg);font-family:Poppins,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .receive-container{width:100%;max-width:480px}
        .brand{text-align:center;margin-bottom:24px}
        .brand h1{font-size:20px;font-weight:700;color:var(--text-dark)}
        .card{background:#fff;border-radius:14px;border:1px solid var(--border);box-shadow:0 4px 20px rgba(0,0,0,.06);overflow:hidden}
        .card-head{background:linear-gradient(135deg,#0056b3 0%,#004494 100%);padding:20px 24px;color:#fff}
        .card-head h2{font-size:16px;font-weight:700;margin:0}
        .card-body{padding:24px}
        /* Loading state */
        .loading-state{text-align:center;padding:30px 20px}
        .loading-spinner{width:40px;height:40px;border:3px solid var(--border);border-top-color:var(--primary);border-radius:50%;animation:spin .8s linear infinite;margin:0 auto 16px}
        @keyframes spin{to{transform:rotate(360deg)}}
        .loading-state p{font-size:13px;color:var(--text-muted)}
        /* Error state */
        .error-state{text-align:center;padding:24px 20px;display:none}
        .error-state i{font-size:36px;color:#dc2626;margin-bottom:12px;display:block}
        .error-state h3{font-size:15px;font-weight:700;color:var(--text-dark);margin-bottom:6px}
        .error-state p{font-size:13px;color:var(--text-muted);line-height:1.5}
        .error-state .btn-back{margin-top:18px;display:inline-flex;align-items:center;gap:6px;padding:10px 22px;border:none;border-radius:8px;background:var(--primary);color:#fff;font-family:Poppins,sans-serif;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;transition:background .2s}
        .error-state .btn-back:hover{background:var(--primary-dark)}
        /* Doc info */
        .doc-info{display:none}
        .doc-info-row{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f1f5f9;font-size:13px}
        .doc-info-row:last-child{border-bottom:none}
        .doc-info-row .label{color:var(--text-muted);font-weight:500}
        .doc-info-row .value{color:var(--text-dark);font-weight:600;text-align:right;max-width:55%;word-break:break-word}
        .status-pill{display:inline-block;padding:3px 10px;border-radius:50px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px}
        .confirm-msg{background:#eff6ff;border-radius:8px;padding:14px;font-size:13px;color:#1e40af;margin:18px 0;line-height:1.5}
        .confirm-msg i{margin-right:6px}
        .btn-row{display:flex;gap:10px;justify-content:flex-end}
        .btn{padding:10px 22px;border-radius:8px;font-size:13px;font-weight:600;font-family:Poppins,sans-serif;cursor:pointer;border:1.5px solid var(--border);background:#fff;color:var(--text-dark);transition:all .2s;text-decoration:none;display:inline-flex;align-items:center;gap:6px}
        .btn:hover{background:#f1f5f9}
        .btn.confirm{background:var(--primary);color:#fff;border-color:var(--primary)}
        .btn.confirm:hover{background:var(--primary-dark);border-color:var(--primary-dark)}
        .btn:disabled{opacity:.5;cursor:not-allowed}
        /* Success state */
        .success-state{text-align:center;padding:24px 20px;display:none}
        .success-state i{font-size:42px;color:var(--primary);margin-bottom:12px;display:block}
        .success-state h3{font-size:16px;font-weight:700;color:var(--text-dark);margin-bottom:6px}
        .success-state p{font-size:13px;color:var(--text-muted);line-height:1.5}
        .success-state .btn{margin-top:18px}
        /* Toast */
        .toast{position:fixed;bottom:32px;left:50%;transform:translateX(-50%) translateY(80px);background:#1e293b;color:#fff;padding:12px 22px;border-radius:10px;font-size:13px;font-weight:500;z-index:9999;display:flex;align-items:center;gap:10px;opacity:0;transition:all .35s ease;pointer-events:none;max-width:90vw;box-shadow:0 8px 24px rgba(0,0,0,.18)}
        .toast.show{opacity:1;transform:translateX(-50%) translateY(0);pointer-events:auto}
        .toast.success{background:var(--primary)}
        .toast.error{background:#dc2626}
    </style>
</head>
<body>
    <div class="receive-container">
        <div class="brand">
            <h1>DOCTRAX</h1>
        </div>
        <div class="card">
            <div class="card-head">
                <div>
                    <h2>Receive Document</h2>
                </div>
            </div>
        <div class="card-body">
            <!-- Loading -->
            <div class="loading-state" id="loadingState">
                <div class="loading-spinner"></div>
                <p>Looking up document <strong>{{ strtoupper($tracking) }}</strong>...</p>
            </div>
            <!-- Error -->
            <div class="error-state" id="errorState">
                <i class="fas fa-exclamation-triangle"></i>
                <h3 id="errTitle">Document Not Found</h3>
                <p id="errMsg">The tracking number <strong>{{ strtoupper($tracking) }}</strong> could not be found in the system.</p>
                <a href="{{ $backUrl ?? '/office/dashboard' }}" class="btn-back" aria-label="Go to Dashboard" title="Go to Dashboard" style="display:inline-flex;align-items:center;justify-content:center;gap:0;padding:0;border:none;background:transparent;border-radius:0;box-shadow:none;color:#0f172a;text-decoration:none;line-height:1.2;width:auto;"><span aria-hidden="true" style="width:38px;height:38px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 38px;border-radius:999px;background:linear-gradient(135deg,#0f4fd6 0%,#1f8ef1 100%);color:#fff;box-shadow:none;"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:block"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M5 12l14 0"></path><path d="M5 12l6 6"></path><path d="M5 12l6 -6"></path></svg></span></a>
            </div>
            <!-- Doc info + confirm -->
            <div class="doc-info" id="docInfo">
                <div class="doc-info-row"><span class="label">Tracking No.</span><span class="value" id="dTrack">—</span></div>
                <div class="doc-info-row"><span class="label">Reference No.</span><span class="value" id="dRef">—</span></div>
                <div class="doc-info-row"><span class="label">Subject</span><span class="value" id="dSubject">—</span></div>
                <div class="doc-info-row"><span class="label">Type</span><span class="value" id="dType">—</span></div>
                <div class="doc-info-row"><span class="label">Sender</span><span class="value" id="dSender">—</span></div>
                <div class="doc-info-row"><span class="label">Status</span><span class="value" id="dStatus">—</span></div>
                <div class="doc-info-row"><span class="label">Current Office</span><span class="value" id="dOffice">—</span></div>
                <div class="doc-info-row"><span class="label">Submitted</span><span class="value" id="dDate">—</span></div>
                <div class="confirm-msg">
                    <i class="fas fa-question-circle"></i>
                    Do you want to <strong>receive this document</strong> into your office? This will update the document's status and assign you as the handler.
                </div>
                <div class="btn-row">
                    <a href="{{ $backUrl ?? '/office/dashboard' }}" class="btn"><i class="fas fa-times"></i> Cancel</a>
                    <button class="btn confirm" id="confirmBtn" onclick="confirmReceive()"><i class="fas fa-check"></i> Confirm Receive</button>
                </div>
            </div>
            <!-- Success -->
            <div class="success-state" id="successState">
                <i class="fas fa-check-circle"></i>
                <h3>Document Received!</h3>
                <p id="successMsg">The document has been received into your office.</p>
                <a href="{{ $backUrl ?? '/office/dashboard' }}" class="btn confirm">Go to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<div class="toast" id="toast"><i class="fas toast-icon" id="toastIcon"></i><span id="toastMsg"></span></div>

<script src="/js/request-utils.js"></script>
<script>
(function(){
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var tracking = @json($tracking);
    var receiveEndpoint = @json($receiveEndpoint ?? '/api/office/documents/receive-by-reference');

    function escapeHtml(val) {
        return String(val == null ? '' : val).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function showToast(msg, type) {
        var t = document.getElementById('toast');
        var ic = document.getElementById('toastIcon');
        document.getElementById('toastMsg').textContent = msg;
        t.className = 'toast show ' + (type || 'success');
        ic.className = 'fas toast-icon ' + (type === 'error' ? 'fa-times-circle' : 'fa-check-circle');
        clearTimeout(window._toastTimer);
        window._toastTimer = setTimeout(function(){ t.classList.remove('show'); }, 3500);
    }

    // Lookup document on page load
    window.docTraxFetchJson('/api/track-document', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
        timeoutMs: 15000,
        body: JSON.stringify({ tracking_number: tracking })
    })
    .then(function(data) {
        document.getElementById('loadingState').style.display = 'none';
        if (!data.success || !data.document) {
            document.getElementById('errorState').style.display = 'block';
            return;
        }
        var doc = data.document;
        document.getElementById('dTrack').textContent = doc.tracking_number || '—';
        document.getElementById('dRef').textContent = doc.reference_number || '—';
        document.getElementById('dSubject').textContent = doc.subject || '—';
        document.getElementById('dType').textContent = doc.type || '—';
        document.getElementById('dSender').textContent = doc.sender_name || '—';
        document.getElementById('dStatus').innerHTML = '<span class="status-pill" style="background:' + escapeHtml(doc.status_color) + '22;color:' + escapeHtml(doc.status_color) + '">' + escapeHtml(doc.status_label) + '</span>';
        document.getElementById('dOffice').textContent = doc.current_office || '—';
        document.getElementById('dDate').textContent = doc.submitted_at || '—';
        document.getElementById('docInfo').style.display = 'block';
    })
    .catch(function(error) {
        document.getElementById('loadingState').style.display = 'none';
        document.getElementById('errTitle').textContent = 'Connection Error';
        document.getElementById('errMsg').textContent = window.describeRequestError(error, 'Could not connect to the server. Please try again.');
        document.getElementById('errorState').style.display = 'block';
    });

    // Confirm receive
    window.confirmReceive = function() {
        var btn = document.getElementById('confirmBtn');
        btn.disabled = true;

        window.docTraxFetchJson(receiveEndpoint, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
            timeoutMs: 15000,
            body: JSON.stringify({ tracking_number: tracking })
        })
        .then(function(data) {
            btn.disabled = false;
            if (data.success) {
                document.getElementById('docInfo').style.display = 'none';
                document.getElementById('successMsg').textContent = data.message || 'Document received successfully!';
                document.getElementById('successState').style.display = 'block';
                showToast(data.message || 'Document received!', 'success');
            } else {
                showToast(data.message || 'Failed to receive document.', 'error');
            }
        })
        .catch(function(error) {
            btn.disabled = false;
            showToast(window.describeRequestError(error, 'Network error. Please try again.'), 'error');
        });
    };
})();
</script>
</body>
</html>
