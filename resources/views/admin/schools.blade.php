<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>School Management - DepEd DOCTRAX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root{--primary:#0056b3;--primary-dark:#004494;--bg:#f0f2f5;--white:#fff;--border:#e2e8f0;--text:#1b263b;--muted:#64748b;--danger:#dc2626;--soft:#eff6ff;--shadow:0 4px 12px rgba(0,0,0,.08)}
        *{margin:0;padding:0;box-sizing:border-box} html{overflow-y:scroll} body{background:var(--bg);font-family:'Poppins',sans-serif;color:var(--text);min-height:100vh;display:flex;flex-direction:column}
        button,input,select,textarea{font-family:inherit}
        .mob-topbar{display:flex;position:sticky;top:0;z-index:100;background:#0056b3;padding:14px 18px;align-items:center;justify-content:space-between;gap:14px;box-shadow:0 2px 8px rgba(0,0,0,.1)}
        .mob-hamburger{background:none;border:none;cursor:pointer;display:flex;flex-direction:column;gap:5px;padding:4px}.mob-hamburger span{height:2px;width:24px;background:#fff;border-radius:2px}
        .mob-brand{flex:1;display:flex;flex-direction:column;color:#fff;gap:4px}.mob-brand .brand-subtitle{font-size:11px;opacity:.88;text-transform:uppercase;letter-spacing:2px}.mob-brand h1{font-size:20px;line-height:1.08}.mob-brand .brand-caption{font-size:12px;opacity:.9}
        .mob-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:199}.mob-overlay.open{display:block}
        .sidebar{position:fixed;top:0;left:0;width:240px;height:100vh;background:#0056b3;display:flex;flex-direction:column;z-index:200;transform:translateX(-100%);transition:transform .28s cubic-bezier(.4,0,.2,1)} .sidebar.open{transform:translateX(0)}
        .sb-brand{padding:22px 20px 18px;border-bottom:1px solid rgba(255,255,255,.12);text-align:center}.sb-brand img{width:64px;height:64px;margin-bottom:8px}.sb-brand h2{font-size:18px;font-weight:700;color:#fff}.sb-brand small{font-size:11px;color:rgba(255,255,255,.65)}
        .sb-nav{flex:1;padding:12px 0;overflow-y:auto}.sb-nav a{display:flex;align-items:center;gap:11px;padding:11px 20px;color:rgba(255,255,255,.78);text-decoration:none;font-size:13px;font-weight:500}.sb-nav a:hover,.sb-nav a.active{background:rgba(255,255,255,.14);color:#fff}.sb-nav .nav-section{padding:10px 20px 4px;font-size:9px;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.4);font-weight:600}
        .sb-footer{padding:14px 20px;border-top:1px solid rgba(255,255,255,.12)}.sb-user{display:flex;align-items:center;gap:10px}.sb-avatar{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700}.sb-user-info small{font-size:10px;color:rgba(255,255,255,.55);display:block}.sb-user-info span{font-size:12px;font-weight:600;color:#fff}.btn-logout{display:flex;align-items:center;gap:7px;margin-top:8px;padding:8px 14px;background:rgba(255,255,255,.1);border:none;border-radius:8px;color:rgba(255,255,255,.8);font-size:12px;cursor:pointer;width:100%;justify-content:center}
        .dash-wrapper{max-width:1200px;width:100%;margin:0 auto;padding:24px 16px 40px;flex:1 0 auto}.page-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;gap:12px;flex-wrap:wrap}.page-header h1{font-size:22px}.page-header p{font-size:14px;color:var(--muted)}
        .btn-primary{display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#0056b3 0%,#004494 100%);color:#fff;border:none;padding:10px 18px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer}
        .filters{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:18px}.field{padding:10px 14px;border:1px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;background:#fff}.field.grow{flex:1 1 280px}
        .panel{background:#fff;border:1px solid var(--border);border-radius:14px;box-shadow:var(--shadow);overflow:hidden}.panel-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;gap:12px}.panel-title{font-size:17px;font-weight:700}.panel-badge{background:var(--soft);color:var(--primary);padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600}
        .table-wrap{overflow:auto}.dtable{width:100%;border-collapse:collapse}.dtable th{padding:12px 16px;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.4px;color:#94a3b8;background:#fafbfc}.dtable td{padding:14px 16px;border-top:1px solid #f1f5f9;font-size:13px;vertical-align:middle}.name-main{font-weight:600}
        .pill{display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:600}.pill.active{background:var(--soft);color:var(--primary)}.pill.inactive{background:#f1f5f9;color:#475569}
        .stats{display:flex;flex-direction:column;gap:3px;font-size:12px;color:var(--muted)} .stats strong{color:var(--text)}
        .action-btns{display:flex;gap:6px;flex-wrap:wrap}.btn-sm{padding:6px 10px;border-radius:8px;border:1px solid var(--border);background:#fff;font-size:12px;cursor:pointer;display:inline-flex;align-items:center;gap:5px}.btn-sm.activate{color:#0f766e;border-color:#99f6e4}.btn-sm.activate:hover{background:#f0fdfa}.btn-sm.suspend{color:#991b1b;border-color:#fecaca}.btn-sm.suspend:hover{background:#fef2f2}
        .empty-state{padding:48px 20px;text-align:center;color:#94a3b8}.empty-state i{font-size:34px;display:block;margin-bottom:10px}
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:220;align-items:center;justify-content:center;padding:16px}.modal-overlay.show{display:flex}.modal{background:#fff;border-radius:16px;max-width:430px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.2);overflow:hidden}.modal-head{padding:18px 22px;border-bottom:1px solid var(--border)}.modal-body{padding:18px 22px}.modal-foot{padding:14px 22px;border-top:1px solid var(--border);display:flex;justify-content:flex-end;gap:10px}.modal-input{width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit}.modal-label{display:block;font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.3px;margin-bottom:6px}.modal-err{margin-top:6px;color:var(--danger);font-size:12px;display:none}.modal-err.show{display:block}.modal-btn{padding:9px 16px;border-radius:8px;border:1px solid var(--border);background:#fff;font-size:13px;cursor:pointer}.modal-btn.primary{background:linear-gradient(135deg,#0056b3 0%,#004494 100%);color:#fff;border:none}.modal-btn.danger{background:var(--danger);color:#fff;border:none}
        .toast{position:fixed;top:78px;right:20px;z-index:300;background:#fff;border:1px solid var(--border);border-radius:8px;padding:14px 18px;box-shadow:0 8px 24px rgba(0,0,0,.1);font-size:13px;transform:translateX(calc(100% + 60px));transition:transform .3s ease}.toast.show{transform:translateX(0)}.toast.success{border-left:3px solid var(--primary)}.toast.error{border-left:3px solid var(--danger)}
        .dash-footer{width:100%;background:#fff;border-top:1px solid var(--border);padding:20px 5%;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8;flex-shrink:0}.footer-right{font-size:11px;color:#b0b8c4}
        .mob-cards{display:none}
        .mob-card{background:#fff;border-radius:10px;border:1px solid var(--border);padding:12px;margin-bottom:8px;display:flex;flex-direction:column;gap:1px}
        .mob-card:last-child{margin-bottom:0}
        .mob-card-head{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:flex-start;gap:8px;margin-bottom:5px}
        .mob-card-name{font-size:13px;font-weight:700;color:var(--text);line-height:1.28;word-break:break-word;overflow-wrap:anywhere}
        .mob-card-row{display:grid;grid-template-columns:minmax(92px,1fr) auto;align-items:start;padding:2px 0;font-size:11.5px;color:var(--muted);gap:8px}
        .mob-card-row .label{font-weight:600;text-transform:uppercase;font-size:10px;letter-spacing:.3px}
        .mob-card-row .value{font-weight:600;color:var(--text);text-align:right;line-height:1.25;white-space:normal;word-break:break-word;overflow-wrap:anywhere;max-width:140px}
        .mob-card-actions{display:flex;justify-content:flex-end;gap:8px;margin-top:7px;padding-top:7px;border-top:1px solid var(--border)}
        @media (max-width:900px){
            .dash-wrapper{padding:18px 14px 32px}
            .page-header h1{font-size:19px}
            .page-header p{font-size:12px}
            .filters{gap:8px}
            .filters .field,.filters .btn-sm{flex:1 1 100%;width:100%}
            .panel-head{padding:14px 14px}
            .table-wrap{display:none}
            .mob-cards{display:block;padding:10px}
            .mob-card .pill{padding:3px 8px;font-size:10.5px;line-height:1.2}
            .mob-card-actions .btn-sm{justify-content:center;padding:6px 9px;font-size:11.5px;line-height:1.2}
            .modal{max-width:95vw}
            .modal-body,.modal-head,.modal-foot{padding-left:16px;padding-right:16px}
            .dash-footer{flex-direction:column;gap:6px;text-align:center;padding:16px 5%}
            .toast{right:12px;left:12px;max-width:none}
        }
        @media (max-width:480px){
            .mob-card{padding:10px}
            .mob-card-head{grid-template-columns:minmax(0,1fr) auto}
            .mob-card-head .pill{margin-left:0}
            .mob-card-row{grid-template-columns:minmax(88px,1fr) auto;gap:6px}
            .mob-card-row .value{max-width:120px;text-align:right}
        }
    </style>
    <script src="{{ asset('js/spa.js') }}?v={{ filemtime(public_path('js/spa.js')) }}" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
</head>
<body>
@php $csrf = csrf_token(); @endphp
<div class="mob-topbar">
    <button class="mob-hamburger" id="mobHamBtn" type="button" onclick="toggleSidebar()" aria-label="Menu"><span></span><span></span><span></span></button>
    <div class="mob-brand">
        <span class="brand-subtitle">Department of Education</span>
        <h1>CITY OF SAN JOSE DEL MONTE</h1>
        <span class="brand-caption">Document Tracking System &mdash; DOCTRAX</span>
    </div>
</div>
<div class="mob-overlay" id="mobOverlay" onclick="closeSidebar()"></div>
<div class="sidebar" id="mainSidebar">
    <div class="sb-brand">
        <img src="{{ asset('images/DOCTRAXLOGO.svg') }}" alt="DOCTRAX Logo">
        <h2>DOCTRAX</h2>
        <small>DepEd Document Tracking System</small>
    </div>
    <nav class="sb-nav">
        <span class="nav-section">Overview</span>
        <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <span class="nav-section">Management</span>
        <a href="/admin/users"><i class="fas fa-users"></i> Users</a>
        <a href="/admin/offices"><i class="fas fa-building"></i> Offices</a>
        <a href="/admin/schools" class="active"><i class="fas fa-school"></i> Schools</a>
        <a href="/records/documents"><i class="fas fa-folder-open"></i> All Documents</a>
        <span class="nav-section">ICT Unit</span>
        <a href="/ict/documents"><i class="fas fa-network-wired"></i> ICT Documents</a>
        <a href="/office/search"><i class="fas fa-chart-line"></i> Reports</a>
        <span class="nav-section">My Documents</span>
        <a href="/submit"><i class="fas fa-paper-plane"></i> Submit Document</a>
        <a href="/my-documents"><i class="fas fa-folder"></i> My Documents</a>
        <a href="/track"><i class="fas fa-search"></i> Track Document</a>
        <span class="nav-section">Account</span>
        <a href="/profile"><i class="fas fa-user-cog"></i> My Profile</a>
    </nav>
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
            <div class="sb-user-info">
                <small>Super Admin</small>
                <span>{{ explode(' ', $user->name)[0] }}</span>
            </div>
        </div>
        <button onclick="logout()" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>
</div>
<div class="dash-wrapper">
    <div class="page-header">
        <div>
            <h1>School Management</h1>
            <p>Control which schools appear in representative signup and school assignment.</p>
        </div>
        <button class="btn-primary" onclick="openCreateModal()"><i class="fas fa-plus"></i> Add School</button>
    </div>
    <form class="filters" method="GET" action="/admin/schools" id="searchForm" data-live-search>
        <input type="text" class="field grow" id="schoolsSearch" name="search" placeholder="Search school name or code..." value="{{ $filters['search'] }}" data-no-capitalize>
        <select class="field" id="schoolsStatus" name="status">
            <option value="">All Status</option>
            <option value="active" {{ $filters['status'] === 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ $filters['status'] === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        @if($filters['search'] || $filters['status'])
            <button type="button" class="btn-sm" onclick="clearSchoolFilters()"><i class="fas fa-rotate-left"></i> Clear</button>
        @endif
    </form>
    <div class="panel">
        <div class="panel-head">
            <div class="panel-title">Available Schools</div>
            <span class="panel-badge">{{ \App\Support\UiNumber::compact($schools->total()) }} total</span>
        </div>
        @if($schools->count() > 0)
        <div class="table-wrap">
            <table class="dtable">
                <thead>
                    <tr>
                        <th>School</th>
                        <th>Status</th>
                        <th>Representatives</th>
                        <th>Documents</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($schools as $school)
                    @php
                        $assignedReps = (int) ($school->users_count ?? 0);
                        $legacyReps = (int) ($school->legacy_users_count ?? 0);
                    @endphp
                    <tr>
                        <td><span class="name-main">{{ $school->name }}</span></td>
                        <td><span class="pill {{ $school->is_active ? 'active' : 'inactive' }}">{{ $school->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td><div class="stats"><span><strong>{{ $assignedReps }}</strong> assigned</span>@if($legacyReps > 0)<span><strong>{{ $legacyReps }}</strong> legacy</span>@endif</div></td>
                        <td><div class="stats"><span><strong>{{ $school->submitted_documents_count ?? 0 }}</strong> submitted-to</span><span><strong>{{ $school->current_documents_count ?? 0 }}</strong> in queue</span></div></td>
                        <td>
                            <div class="action-btns">
                                <button class="btn-sm {{ $school->is_active ? 'suspend' : 'activate' }}" onclick="openStatusModal({{ $school->id }}, '{{ addslashes($school->name) }}', {{ $school->is_active ? 'true' : 'false' }})"><i class="fas {{ $school->is_active ? 'fa-ban' : 'fa-check' }}"></i> {{ $school->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mob-cards">
            @foreach($schools as $school)
            @php
                $assignedReps = (int) ($school->users_count ?? 0);
                $legacyReps = (int) ($school->legacy_users_count ?? 0);
            @endphp
            <div class="mob-card">
                <div class="mob-card-head">
                    <div class="mob-card-name">{{ $school->name }}</div>
                    <span class="pill {{ $school->is_active ? 'active' : 'inactive' }}">{{ $school->is_active ? 'Active' : 'Inactive' }}</span>
                </div>
                <div class="mob-card-row"><span class="label">Assigned Reps</span><span class="value">{{ $assignedReps }}</span></div>
                @if($legacyReps > 0)
                <div class="mob-card-row"><span class="label">Legacy Reps</span><span class="value">{{ $legacyReps }}</span></div>
                @endif
                <div class="mob-card-row"><span class="label">Submitted Docs</span><span class="value">{{ $school->submitted_documents_count ?? 0 }}</span></div>
                <div class="mob-card-row"><span class="label">Queue Docs</span><span class="value">{{ $school->current_documents_count ?? 0 }}</span></div>
                <div class="mob-card-actions">
                    <button class="btn-sm {{ $school->is_active ? 'suspend' : 'activate' }}" onclick="openStatusModal({{ $school->id }}, '{{ addslashes($school->name) }}', {{ $school->is_active ? 'true' : 'false' }})"><i class="fas {{ $school->is_active ? 'fa-ban' : 'fa-check' }}"></i> {{ $school->is_active ? 'Deactivate' : 'Activate' }}</button>
                </div>
            </div>
            @endforeach
        </div>
        @include('partials.shared-pagination', ['paginator' => $schools, 'itemLabel' => 'schools'])
        @else
        <div class="empty-state"><i class="fas fa-school"></i><p>No schools found.</p></div>
        @endif
    </div>
</div>
<div class="modal-overlay" id="createModal"><div class="modal"><div class="modal-head"><h3>Add School</h3></div><div class="modal-body"><label class="modal-label">School Name</label><input type="text" class="modal-input" id="createSchoolName" maxlength="255" placeholder="e.g. Sample Elementary School"><div class="modal-err" id="createSchoolErr">School name is required.</div></div><div class="modal-foot"><button class="modal-btn" onclick="closeCreateModal()">Cancel</button><button class="modal-btn primary" id="saveCreateBtn">Save</button></div></div></div>
<div class="modal-overlay" id="statusModal"><div class="modal"><div class="modal-head"><h3 id="statusModalTitle">Deactivate School</h3></div><div class="modal-body"><p id="statusModalMsg"></p></div><div class="modal-foot"><button class="modal-btn" onclick="closeStatusModal()">Cancel</button><button class="modal-btn primary" id="confirmStatusBtn">Confirm</button></div></div></div>
<div class="toast" id="toast"></div>
<footer class="dash-footer"><div>&copy; {{ date('Y') }} DepEd Document Tracking System</div><div class="footer-right">Developed by Raymond Bautista</div></footer>
<script>
(function(){var csrf=document.querySelector('meta[name="csrf-token"]').getAttribute('content');var statusSchoolId=null;var statusTarget=null;
function showToast(msg,type){var t=document.getElementById('toast');t.textContent=msg;t.className='toast '+type+' show';setTimeout(function(){t.classList.remove('show');},3200);}
function request(url,method,body){return fetch(url,{method:method,headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},body:body?JSON.stringify(body):undefined}).then(function(r){return r.json();});}
function showError(id,msg){var el=document.getElementById(id);if(!el)return;el.textContent=msg;el.classList.add('show');}
function clearError(id){var el=document.getElementById(id);if(el)el.classList.remove('show');}
window.openCreateModal=function(){document.getElementById('createSchoolName').value='';clearError('createSchoolErr');document.getElementById('createModal').classList.add('show');};window.closeCreateModal=function(){document.getElementById('createModal').classList.remove('show');};
document.getElementById('saveCreateBtn').addEventListener('click',function(){var name=document.getElementById('createSchoolName').value.trim();if(!name){showError('createSchoolErr','School name is required.');return;}var btn=this;btn.disabled=true;request('/api/admin/schools','POST',{name:name}).then(function(data){btn.disabled=false;if(data.success){closeCreateModal();showToast(data.message,'success');setTimeout(function(){window.location.reload();},800);}else{showError('createSchoolErr',data.message||'Failed to add school.');}}).catch(function(){btn.disabled=false;showError('createSchoolErr','Something went wrong.');});});
window.openStatusModal=function(id,name,isActive){statusSchoolId=id;statusTarget=!isActive;document.getElementById('statusModalTitle').textContent=isActive?'Deactivate School':'Activate School';document.getElementById('statusModalMsg').textContent=isActive?('Deactivate '+name+'? It will disappear from representative signup and assignment, but attached records stay intact.'):('Activate '+name+'? It will appear again in representative signup and assignment.');var confirmBtn=document.getElementById('confirmStatusBtn');confirmBtn.textContent=isActive?'Deactivate':'Activate';confirmBtn.className='modal-btn '+(isActive?'danger':'primary');document.getElementById('statusModal').classList.add('show');};window.closeStatusModal=function(){statusSchoolId=null;statusTarget=null;document.getElementById('statusModal').classList.remove('show');};
document.getElementById('confirmStatusBtn').addEventListener('click',function(){if(!statusSchoolId||statusTarget===null)return;var btn=this;btn.disabled=true;request('/api/admin/schools/'+statusSchoolId,'PUT',{is_active:statusTarget}).then(function(data){btn.disabled=false;if(data.success){closeStatusModal();showToast(data.message,'success');setTimeout(function(){window.location.reload();},800);}else{showToast(data.message||'Failed to update school.','error');}}).catch(function(){btn.disabled=false;showToast('Something went wrong.','error');});});
document.getElementById('createModal').addEventListener('click',function(e){if(e.target===this)closeCreateModal();});document.getElementById('statusModal').addEventListener('click',function(e){if(e.target===this)closeStatusModal();});
window.toggleSidebar=function(){var s=document.getElementById('mainSidebar');var o=document.getElementById('mobOverlay');var open=s.classList.toggle('open');o.classList.toggle('open',open);};window.closeSidebar=function(){document.getElementById('mainSidebar').classList.remove('open');document.getElementById('mobOverlay').classList.remove('open');};window.clearSchoolFilters=function(){var form=document.getElementById('searchForm');if(!form)return;document.getElementById('schoolsSearch').value='';document.getElementById('schoolsStatus').value='';if(typeof form.requestSubmit==='function')form.requestSubmit();else form.submit();};
window.logout=function(){fetch('/api/logout',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}}).then(function(){window.location.href='/login';}).catch(function(){window.location.href='/login';});};})();
</script>
</body>
</html>
