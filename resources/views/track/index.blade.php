<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Track Document - DepEd DTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root{--primary:#0056b3;--primary-dark:#004494;--bg:#f0f2f5;--border:#e2e8f0;--text-dark:#1b263b;--text-muted:#64748b}
        *{margin:0;padding:0;box-sizing:border-box}
        html{overflow-y:scroll}
        body{background:var(--bg);font-family:Poppins,sans-serif;min-height:100vh;display:flex;flex-direction:column}
        .navbar{background:linear-gradient(135deg,#0056b3,#004494);padding:14px clamp(18px,2vw,28px);display:flex;justify-content:space-between;align-items:center;gap:14px;box-shadow:0 2px 8px rgba(0,0,0,.12);position:sticky;top:0;z-index:100;flex-shrink:0}
        .brand-text{display:flex;flex-direction:column;gap:4px;min-width:0}
        .brand-subtitle{font-size:clamp(10px,1vw,11px);font-weight:500;color:rgba(255,255,255,.9);text-transform:uppercase;letter-spacing:2.4px;line-height:1.1}
        .navbar h1{font-size:clamp(18px,2.2vw,22px);font-weight:700;color:#fff;margin:0;line-height:1.08}
        .brand-caption{font-size:clamp(11px,1.2vw,13px);font-weight:300;color:rgba(255,255,255,.92);line-height:1.18;white-space:normal}
        .nav-links{display:flex;align-items:center;gap:4px;flex-shrink:0}
        .nav-link{color:rgba(255,255,255,.85);text-decoration:none;font-size:13px;font-weight:500;padding:8px 13px;border-radius:8px;transition:background .2s,color .2s;display:flex;align-items:center;gap:6px;white-space:nowrap}
        .nav-link:hover{background:rgba(255,255,255,.15);color:#fff}
        .nav-link.active{background:rgba(255,255,255,.18);color:#fff}
        .nav-hamburger{display:none;background:none;border:none;cursor:pointer;padding:6px;color:#fff;font-size:20px;z-index:101;align-items:center;justify-content:center;transition:transform .2s}
        .nav-hamburger.open{transform:rotate(90deg)}
        .page{max-width:700px;margin:0 auto;padding:32px 16px 60px;flex:1;width:100%;display:flex;flex-direction:column;justify-content:center;transition:justify-content .3s}
        .page.has-result{justify-content:flex-start}
        .search-card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.07);padding:28px;margin-bottom:22px;text-align:center}
        .search-card h2{font-size:17px;font-weight:700;color:var(--text-dark);margin-bottom:6px;display:flex;align-items:center;justify-content:center;gap:8px}
        .search-card p{font-size:12px;color:var(--text-muted);margin-bottom:20px}
        .ref-boxes-row{display:flex;align-items:center;gap:7px;flex:1;min-width:0;flex-wrap:nowrap}
        .ref-box{flex:1;min-width:0;height:clamp(42px,10vw,60px);text-align:center;font-size:clamp(16px,4vw,24px);font-weight:700;font-family:'Poppins',sans-serif;border:1.5px solid #e2e8f0;border-radius:8px;outline:none;text-transform:uppercase;background:#f8fafc;transition:border-color .2s,box-shadow .2s,background .2s;color:#1e293b;padding:0;caret-color:var(--primary)}
        .ref-box:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,86,179,.13);background:#fff}
        .ref-box.filled{background:#fff;border-color:#94a3b8}
        .ref-sep{font-size:18px;color:#cbd5e1;user-select:none;padding:0 2px}
        .search-main{width:100%;display:flex;align-items:center;justify-content:center;gap:8px;margin-bottom:0}
        .search-center{width:100%;margin:0 auto}
        .btn-clear-x{width:36px;height:36px;border:1.5px solid #e2e8f0;border-radius:50%;background:#f8fafc;color:#94a3b8;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;flex-shrink:0;padding:0}
        .btn-clear-x:hover{background:#fee2e2;color:#dc2626;border-color:#fca5a5}
        .search-btn-wrap{display:flex;justify-content:center;margin-top:18px}
        .btn-track{width:100%;height:clamp(44px,10vw,60px);padding:0 32px;background:var(--primary);color:#fff;border:none;border-radius:8px;font-family:Poppins,sans-serif;font-size:clamp(13px,2.5vw,14px);font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:background .2s}
        .btn-track:hover{background:var(--primary-dark)}
        .btn-track:disabled{opacity:.7;cursor:not-allowed}
        .search-alert{margin-top:12px;padding:8px 12px;border-radius:7px;font-size:12px;display:none;align-items:center;gap:8px;animation:rcvFadeIn .2s ease-out;width:100%}
        .search-alert.show{display:flex}
        .search-alert.err{background:#fef2f2;border-left:3px solid #dc2626;color:#b91c1c}
        .search-alert i{font-size:13px;flex-shrink:0}
        .search-alert span{line-height:1.4}
        @keyframes rcvFadeIn{from{opacity:0;transform:translateY(-3px)}to{opacity:1;transform:translateY(0)}}
        .result-card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.07);overflow:hidden;display:none}
        .result-card.show{display:block}
        .doc-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:flex-start;gap:12px;flex-wrap:wrap}
        .doc-header>div:first-child{min-width:0;flex:1}
        .doc-title{font-size:15px;font-weight:700;color:var(--text-dark);margin-bottom:3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .doc-ref{font-size:11px;color:var(--text-muted);font-family:monospace;letter-spacing:.5px}
        .doc-meta-line{font-size:11px;color:#475569;margin-top:5px;line-height:1.4}
        .doc-meta-line strong{color:#1e293b}
        .status-badge{padding:5px 12px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap}
        .meta-grid{display:grid;grid-template-columns:1fr 1fr}
        .meta-item{padding:14px 22px;border-right:1px solid var(--border);border-bottom:1px solid var(--border)}
        .meta-item:nth-child(2n){border-right:none}
        .meta-label{font-size:10px;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);font-weight:600;margin-bottom:4px}
        .meta-value{font-size:13px;color:var(--text-dark);font-weight:500}
        .timeline-section{padding:22px 24px}
        .timeline-title{font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:18px;display:flex;align-items:center;gap:6px}
        .tl{position:relative;z-index:0}
        .tl::before{content:'';position:absolute;left:7px;top:8px;bottom:8px;width:2px;background:var(--border);z-index:0;pointer-events:none}
        .tl-item{position:relative;z-index:1;margin-bottom:22px;padding-left:24px}
        .tl-item:last-child{margin-bottom:0}
        .tl-dot{width:16px;height:16px;border-radius:50%;border:2.5px solid #fff;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0}
        .tl-dot.active{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.done{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.warn{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.danger{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.latest{background:#f59e0b;box-shadow:0 0 0 2px #f59e0b}
        .tl-action{font-size:12px;font-weight:700;color:#1b263b}
        .tl-meta{font-size:12px;color:#64748b;margin:2px 0}
        .tl-remarks{font-size:12px;color:#64748b;background:#f8fafc;border-left:3px solid var(--border);padding:5px 9px;border-radius:4px;margin-top:5px}
        .tl-office-hdr{display:flex;align-items:center;font-size:13px;font-weight:700;color:var(--text-dark);text-transform:none;letter-spacing:0;margin:18px 0 8px -7px;padding-left:7px;padding-bottom:6px;position:relative;z-index:1}
        .tl-office-hdr::after{content:'';position:absolute;left:21px;right:0;bottom:0;height:1.5px;background:var(--border)}
        .tl-office-hdr:first-child{margin-top:0}
        .msg-box{text-align:center;padding:40px 20px}
        .msg-box i{font-size:38px;color:#cbd5e1;margin-bottom:12px;display:block}
        .msg-box h3{font-size:16px;color:var(--text-dark);font-weight:700;margin-bottom:6px}
        .msg-box p{font-size:12px;color:var(--text-muted)}
        @keyframes spin{to{transform:rotate(360deg)}}
        .spinner{width:15px;height:15px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite}
        @media(max-width:640px){
            .navbar{padding:14px 4%;position:relative;flex-wrap:wrap}
            .nav-hamburger{display:flex;order:-1}
            .brand-text{flex:1;min-width:0}
            .navbar h1{font-size:clamp(18px,4.8vw,22px);line-height:1.08}
            .brand-subtitle{font-size:clamp(10px,2.4vw,11px);letter-spacing:2.4px}
            .brand-caption{font-size:clamp(11px,2.9vw,13px);line-height:1.18}
            .nav-links{display:none;position:absolute;top:100%;right:0;left:0;background:linear-gradient(135deg,#004494,#003378);flex-direction:column;padding:6px 0;box-shadow:0 8px 24px rgba(0,0,0,.18);z-index:100}
            .nav-links.open{display:flex}
            .nav-link{width:100%;padding:13px 20px;border-radius:0;font-size:13px;border-bottom:1px solid rgba(255,255,255,.08)}
            .nav-link:last-child{border-bottom:none}
            .nav-link:hover{background:rgba(255,255,255,.1)}
            .meta-grid{grid-template-columns:1fr}
            .meta-item{border-right:none}
            .page{padding:16px 10px 40px}
            .search-card{padding:18px 14px}
            .search-card h2{font-size:15px}
            .search-card p{font-size:11px;margin-bottom:14px}
            .ref-boxes-row{gap:4px}
            .ref-sep{font-size:14px;padding:0 1px}
            .btn-clear-x{width:30px;height:30px;font-size:12px}
            .search-btn-wrap{margin-top:14px}
            .btn-track{height:48px;font-size:13px}
            .my-docs-head{padding:12px 14px}
            .my-doc-row{padding:10px 14px;gap:10px}
            .my-doc-badge{font-size:9px;padding:2px 8px}
            .doc-header{padding:16px 14px}
            .doc-title{font-size:13px}
            .doc-ref{font-size:10px}
            .meta-item{padding:10px 14px}
            .meta-label{font-size:9px}
            .meta-value{font-size:12px}
            .timeline-section{padding:16px 14px}
            .timeline-title{font-size:11px}
            .tl-action{font-size:11px}
            .tl-meta{font-size:11px}
            .tl-remarks{font-size:11px}
            .msg-box{padding:28px 14px}
            .msg-box i{font-size:30px}
            .msg-box h3{font-size:14px}
        }
        /* my docs section */
        .my-docs-card{background:#fff;border-radius:16px;box-shadow:0 4px 24px rgba(0,0,0,.07);overflow:hidden;margin-bottom:22px}
        .my-docs-head{padding:16px 22px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center}
        .my-docs-head h3{font-size:14px;font-weight:700;color:var(--text-dark);display:flex;align-items:center;gap:8px}
        .my-docs-head span{font-size:11px;color:var(--text-muted)}
        .my-doc-row{display:flex;align-items:center;gap:14px;padding:12px 22px;border-bottom:1px solid var(--border);cursor:pointer;transition:background .15s;text-decoration:none}
        .my-doc-row:last-child{border-bottom:none}
        .my-doc-row:hover{background:#f8fafc}
        .my-doc-icon{width:34px;height:34px;border-radius:8px;background:#eff6ff;color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:13px;flex-shrink:0}
        .my-doc-info{flex:1;min-width:0}
        .my-doc-subject{font-size:13px;font-weight:600;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .my-doc-ref{font-size:11px;color:var(--text-muted);font-family:monospace;letter-spacing:.3px;margin-top:1px}
        .my-doc-badge{padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;white-space:nowrap;flex-shrink:0}
        .my-doc-arr{color:#cbd5e1;font-size:12px;flex-shrink:0}
        .my-docs-empty{padding:28px;text-align:center;color:var(--text-muted);font-size:13px}
        .dash-footer{width:100%;background:#fff;border-top:1px solid #e2e8f0;padding:20px 5%;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8;margin-top:40px}
        .footer-left{display:flex;align-items:center;gap:6px}
        .footer-right{font-size:11px;color:#b0b8c4}
        @media(max-width:768px){.dash-footer{flex-direction:column;gap:6px;text-align:center;padding:16px 5%}}
    </style>
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
</head>
<body>
<nav class="navbar">
    <div class="brand-text">
        <span class="brand-subtitle">Department of Education</span>
        <h1>CITY OF SAN JOSE DEL MONTE</h1>
        <span class="brand-caption">Document Tracking System &mdash; DOCTRAX</span>
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
<div class="page">
    <div class="search-card">
        <h2><i class="fas fa-search" style="color:var(--primary)"></i> Track Your Document</h2>
        <p>Enter your 8-character Tracking Number to view the current status and full routing history.</p>
        <div class="search-center">
            <div class="search-main">
                <div class="ref-boxes-row" id="refBoxes">
                    <input type="text" maxlength="1" class="ref-box" data-idx="0" data-no-clearable data-no-capitalize autocomplete="off">
                    <input type="text" maxlength="1" class="ref-box" data-idx="1" data-no-clearable data-no-capitalize autocomplete="off">
                    <input type="text" maxlength="1" class="ref-box" data-idx="2" data-no-clearable data-no-capitalize autocomplete="off">
                    <input type="text" maxlength="1" class="ref-box" data-idx="3" data-no-clearable data-no-capitalize autocomplete="off">
                    <span class="ref-sep">&mdash;</span>
                    <input type="text" maxlength="1" class="ref-box" data-idx="4" data-no-clearable data-no-capitalize autocomplete="off">
                    <input type="text" maxlength="1" class="ref-box" data-idx="5" data-no-clearable data-no-capitalize autocomplete="off">
                    <input type="text" maxlength="1" class="ref-box" data-idx="6" data-no-clearable data-no-capitalize autocomplete="off">
                    <input type="text" maxlength="1" class="ref-box" data-idx="7" data-no-clearable data-no-capitalize autocomplete="off">
                </div>
                <button type="button" class="btn-clear-x" onclick="clearRefBoxes()" title="Clear">&#10005;</button>
            </div>
            <div class="search-alert" id="searchAlert"><i class="fas fa-exclamation-circle"></i><span></span></div>
        </div>
        <div class="search-btn-wrap">
            <button class="btn-track" id="trackBtn" onclick="trackDoc()">
                <i class="fas fa-search"></i> Track
            </button>
        </div>
    </div>
    @if(!is_null($myDocs))
    <div class="my-docs-card">
        <div class="my-docs-head">
            <h3><i class="fas fa-folder-open" style="color:var(--primary)"></i> My Submitted Documents</h3>
            <span>Click a row to track it</span>
        </div>
        @if($myDocs->isEmpty())
            <div class="my-docs-empty"><i class="fas fa-inbox" style="font-size:24px;color:#cbd5e1;display:block;margin-bottom:8px"></i>You have no submitted documents yet.</div>
        @else
            @foreach($myDocs as $doc)
            <a class="my-doc-row" href="#" data-tracking="{{ $doc->reference_number }}">
                <div class="my-doc-icon"><i class="fas fa-file-alt"></i></div>
                <div class="my-doc-info">
                    <div class="my-doc-subject">{{ $doc->subject }}</div>
                    <div class="my-doc-ref">{{ $doc->reference_number }}</div>
                </div>
                <div class="my-doc-badge" style="background:{{ $doc->statusColor() }}1a;color:{{ $doc->statusColor() }};border:1.5px solid {{ $doc->statusColor() }}55">{{ $doc->statusLabel() }}</div>
                <i class="fas fa-chevron-right my-doc-arr"></i>
            </a>
            @endforeach
        @endif
    </div>
    @endif
    <div class="result-card" id="notFoundCard">
        <div class="msg-box">
            <i class="fas fa-file-circle-question"></i>
            <h3>Tracking Number Not Found</h3>
            <p>The tracking number you entered does not match any document in our records.<br>Please double-check and try again.</p>
        </div>
    </div>
    <div class="result-card" id="resultCard">
        <div class="doc-header">
            <div>
                <div class="doc-title" id="rDocTitle"></div>
                <div class="doc-ref"   id="rDocRef"></div>
            </div>
            <div class="status-badge" id="rStatusBadge"></div>
        </div>

        <div class="timeline-section">
            <div class="timeline-title"><i class="fas fa-history"></i> Routing History</div>
            <div class="tl" id="rTimeline"></div>
        </div>
    </div>
</div>
<script>
(function(){
    var csrf=document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var boxes=document.querySelectorAll('.ref-box');
    var alertEl=document.getElementById('searchAlert');
    var stateCard=document.getElementById('notFoundCard');
    var stateIcon=stateCard ? stateCard.querySelector('i') : null;
    var stateTitle=stateCard ? stateCard.querySelector('h3') : null;
    var stateBody=stateCard ? stateCard.querySelector('p') : null;
    var defaultStateBody='The tracking number you entered does not match any document in our records.<br>Please double-check and try again.';
    var TRACK_AUTO_LOOKUP_DELAY = 350;
    var TRACK_LOOKUP_COOLDOWN_MS = 1200;
    var _trackLookupTimer = null;
    var _trackLookupInFlight = false;
    var _lastTrackedLookup = '';
    var _lastTrackedAt = 0;

    function queueTrackLookup(prefilledLookup){
        clearTimeout(_trackLookupTimer);
        _trackLookupTimer = setTimeout(function(){
            window.trackDoc(prefilledLookup);
        }, TRACK_AUTO_LOOKUP_DELAY);
    }

    /* ── ref-box logic (type, paste, backspace, arrow keys) ── */
    boxes.forEach(function(box,i){
        box.addEventListener('input',function(){
            var v=box.value.replace(/[^A-Za-z0-9]/g,'');
            box.value=v.toUpperCase();
            if(v) box.classList.add('filled'); else box.classList.remove('filled');
            if(v&&i<boxes.length-1) boxes[i+1].focus();
            if(getRef().length===8) queueTrackLookup();
        });
        box.addEventListener('keydown',function(e){
            if(e.key==='Backspace'&&!box.value&&i>0){e.preventDefault();boxes[i-1].focus();boxes[i-1].value='';boxes[i-1].classList.remove('filled');}
            if(e.key==='ArrowLeft'&&i>0){e.preventDefault();boxes[i-1].focus();}
            if(e.key==='ArrowRight'&&i<boxes.length-1){e.preventDefault();boxes[i+1].focus();}
            if(e.key==='Enter') trackDoc();
        });
        box.addEventListener('paste',function(e){
            e.preventDefault();
            var txt=(e.clipboardData||window.clipboardData).getData('text').replace(/[^A-Za-z0-9]/g,'').toUpperCase();
            for(var j=0;j<boxes.length;j++){
                boxes[j].value=txt[j]||'';
                if(boxes[j].value) boxes[j].classList.add('filled'); else boxes[j].classList.remove('filled');
            }
            if(txt.length>=8){boxes[boxes.length-1].focus();queueTrackLookup();}
            else if(txt.length>0) boxes[Math.min(txt.length,boxes.length-1)].focus();
        });
        box.addEventListener('focus',function(){box.select();});
    });

    function getRef(){
        var r='';boxes.forEach(function(b){r+=b.value;});return r.toUpperCase();
    }
    function setRef(val){
        val=val.replace(/[^A-Za-z0-9]/g,'').toUpperCase();
        for(var j=0;j<boxes.length;j++){
            boxes[j].value=val[j]||'';
            if(boxes[j].value) boxes[j].classList.add('filled'); else boxes[j].classList.remove('filled');
        }
    }
    window.clearRefBoxes=function(){
        boxes.forEach(function(b){b.value='';b.classList.remove('filled');});
        boxes[0].focus();
        alertEl.classList.remove('show');
        if (stateCard) stateCard.classList.remove('show');
        document.getElementById('resultCard').classList.remove('show');
        document.querySelector('.page').classList.remove('has-result');
    };

    function showAlert(msg){
        alertEl.querySelector('span').textContent=msg;
        alertEl.classList.add('show','err');
    }

    function showResultState(kind, message) {
        if (!stateCard) return;

        if (kind === 'error') {
            if (stateIcon) stateIcon.className = 'fas fa-wifi';
            if (stateTitle) stateTitle.textContent = 'Connection Problem';
            if (stateBody) stateBody.innerHTML = esc(message || 'Could not load tracking details. Please try again.');
        } else {
            if (stateIcon) stateIcon.className = 'fas fa-file-circle-question';
            if (stateTitle) stateTitle.textContent = 'Tracking Number Not Found';
            if (stateBody) stateBody.innerHTML = defaultStateBody;
        }

        stateCard.classList.add('show');
        document.querySelector('.page').classList.add('has-result');
    }

    function dotClass(s){
        if(s==='cancelled'||s==='returned')return 'danger';
        if(s==='completed')return 'done';
        if(s==='forwarded')return 'warn';
        return 'active';
    }
    function esc(value){
        return String(value === null || value === undefined ? '' : value)
            .replace(/&/g,'&amp;')
            .replace(/</g,'&lt;')
            .replace(/>/g,'&gt;')
            .replace(/"/g,'&quot;')
            .replace(/'/g,'&#39;');
    }
    window.trackDoc=async function(prefilledLookup){
        clearTimeout(_trackLookupTimer);
        alertEl.classList.remove('show');
        var hasPrefilled = typeof prefilledLookup === 'string' && prefilledLookup.trim() !== '';
        var ref = hasPrefilled ? prefilledLookup.trim().toUpperCase() : getRef();
        if(!hasPrefilled && ref.length<8){showAlert('Please enter the full 8-character tracking number.');return;}
        if(ref === ''){showAlert('Please enter a tracking or reference number.');return;}
        var now = Date.now();
        if (_trackLookupInFlight && ref === _lastTrackedLookup) return;
        if (ref === _lastTrackedLookup && (now - _lastTrackedAt) < TRACK_LOOKUP_COOLDOWN_MS) return;
        var btn=document.getElementById('trackBtn');
        btn.disabled = true;
        if (stateCard) stateCard.classList.remove('show');
        document.getElementById('resultCard').classList.remove('show');
        _trackLookupInFlight = true;
        _lastTrackedLookup = ref;
        _lastTrackedAt = now;
        try{
            var data=await window.docTraxFetchJson('/api/track-document',{
                method:'POST',
                headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                timeoutMs: 15000,
                body:JSON.stringify({
                    reference_number:ref,
                    tracking_number:ref
                })
            });
            if(!data.success||!data.document){showResultState('not_found');}
            else{renderResult(data.document);}
        }catch(e){showResultState('error', window.describeRequestError(e, 'Could not load tracking details. Please try again.'));}
        finally{
            _trackLookupInFlight = false;
            btn.disabled = false;
        }
    };
    function renderResult(doc){
        document.getElementById('rDocTitle').textContent=doc.subject;
        document.getElementById('rDocRef').textContent=doc.reference_number || doc.tracking_number || '-';
        var badge=document.getElementById('rStatusBadge');
        badge.textContent=doc.status_label;
        badge.style.background=(doc.status_color||'#6b7280')+'1a';
        badge.style.color=doc.status_color||'#6b7280';
        badge.style.border='1.5px solid '+(doc.status_color||'#6b7280')+'55';

        var tl=document.getElementById('rTimeline');
        tl.innerHTML='';
        var logs=doc.routing_logs||[];
        if(!logs.length){tl.innerHTML='<div style="color:var(--text-muted);font-size:13px">No routing history yet.</div>';}
        else{
            var prevGroupKey = null;
            Array.from(logs).reverse().forEach(function(log, idx){
                var isLatest = idx === 0;
                var dc = isLatest ? 'latest' : dotClass(log.status_after);
                var dotIcon = isLatest ? 'fa-arrow-up' : 'fa-check';
                var groupKey = (log.action === 'submitted') ? '__pending__' :
                               (log.action === 'forwarded' ? (log.from_office || 'Unknown') :
                               (log.to_office || log.from_office || 'Unknown'));
                var groupLabel = (groupKey === '__pending__') ? 'Submitted — Pending Physical Submission' : groupKey;
                if (groupKey !== prevGroupKey) {
                    prevGroupKey = groupKey;
                    var hdr = document.createElement('div');
                    hdr.className = 'tl-office-hdr';
                    hdr.innerHTML = '<div class="tl-dot '+dc+'" style="margin-right:5px"><i class="fas '+dotIcon+'" style="font-size:5px"></i></div><span>' + esc(groupLabel) + '</span>';
                    tl.appendChild(hdr);
                }
                var item=document.createElement('div');item.className='tl-item';
                item.innerHTML=
                    (log.performed_by?'<div class="tl-action">'+esc(log.performed_by)+'</div>':'')+
                    '<div class="tl-meta"><i class="fas fa-clock" style="margin-right:3px;font-size:10px"></i>'+esc(log.timestamp||'-')+'</div>'+
                    '<div class="tl-meta"><i class="fas fa-tasks" style="margin-right:3px;font-size:10px"></i>'+esc(log.action_label||'Status Updated')+'</div>'+
                    (log.remarks?'<div class="tl-remarks">'+esc(log.remarks)+'</div>':'');
                tl.appendChild(item);
            });
        }
        document.getElementById('resultCard').classList.add('show');
        document.querySelector('.page').classList.add('has-result');
    }
    window.quickTrack=function(ref){
        setRef(ref);
        document.getElementById('refBoxes').scrollIntoView({behavior:'smooth',block:'center'});
        queueTrackLookup();
    };

    // Bind my-doc-row clicks via data-tracking attribute (avoids inline onclick quote issues)
    document.querySelectorAll('.my-doc-row[data-tracking]').forEach(function(row){
        row.addEventListener('click', function(e){
            e.preventDefault();
            var tn = row.getAttribute('data-tracking');
            if(tn) quickTrack(tn);
        });
    });

    var urlRef=new URLSearchParams(window.location.search).get('ref');
    if(urlRef){
        var lookup = urlRef.trim().toUpperCase();
        // Keep the visual 8-box UX for short codes while still supporting full QR values.
        if (/^[A-Z0-9]{8}$/.test(lookup)) {
            setRef(lookup);
        }
        window.trackDoc(lookup);
    }
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

