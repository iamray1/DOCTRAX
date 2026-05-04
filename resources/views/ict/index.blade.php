<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ICT Unit — DOCTRAX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root{--primary:#0056b3;--primary-dark:#004494;--blue-soft:#eff6ff;--slate-dark:#334155;--bg:#f0f2f5;--border:#e2e8f0;--text-dark:#1b263b;--text-muted:#64748b}
        *{margin:0;padding:0;box-sizing:border-box}
        body{background:var(--bg);font-family:'Poppins',sans-serif;min-height:100vh;display:flex;flex-direction:column}

        /* ─── Receive Strip ─── */
        .receive-strip{background:#fff;border:1px solid var(--border);border-radius:12px;padding:22px 24px;margin-bottom:24px}
        .receive-strip h2{font-size:20px;font-weight:700;color:var(--text-dark);margin:0 0 6px}
        .receive-strip p.rs-sub{font-size:13px;color:var(--text-muted);margin:0 0 18px}
        .rs-main{width:100%;display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:center;gap:8px;margin-bottom:0;min-width:0}
        .ref-boxes-row{display:flex;align-items:center;gap:7px;flex:1;min-width:0;flex-wrap:nowrap}
        .ref-box{flex:1;min-width:0;height:clamp(60px,5.8vw,72px);text-align:center;font-size:clamp(21px,2.2vw,26px);font-weight:700;font-family:'Poppins',sans-serif;border:1.5px solid #cbd5e1;border-radius:8px;outline:none;text-transform:uppercase;background:#f8fafc;transition:border-color .2s,box-shadow .2s,background .2s;color:#1e293b;padding:0;caret-color:var(--primary);box-shadow:0 0 0 1px rgba(203,213,225,.75)}
        .ref-box:focus{border-color:var(--primary);box-shadow:0 0 0 1px rgba(0,86,179,.28),0 0 0 4px rgba(0,86,179,.13);background:#fff}
        .ref-box.filled{background:#fff;border-color:#94a3b8;box-shadow:0 0 0 1px rgba(148,163,184,.42)}
        .ref-sep{font-size:18px;color:#cbd5e1;user-select:none;padding:0 2px}
        .btn-clear-x{width:36px;height:36px;border:1.5px solid #e2e8f0;border-radius:50%;background:#f8fafc;color:#94a3b8;font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;flex-shrink:0;padding:0}
        .rs-center{width:100%;margin:0 auto}
        .rs-btn-wrap{display:flex;justify-content:center;margin-top:18px;gap:12px}
        .btn-receive{flex:1;height:clamp(54px,5.6vw,60px);padding:0 clamp(16px,2.8vw,32px);border:none;border-radius:8px;background:var(--slate-dark);color:#fff;font-family:'Poppins',sans-serif;font-size:clamp(13px,1.7vw,14px);font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:background .2s}
        .btn-receive:hover{background:#243244}
        .btn-receive:active{background:#1e293b}
        .btn-receive:disabled{opacity:.5;cursor:not-allowed}
        .btn-scan-qr{flex:1;height:clamp(54px,5.6vw,60px);padding:0 clamp(16px,2.8vw,32px);border:none;border-radius:8px;background:var(--primary);color:#fff;font-family:'Poppins',sans-serif;font-size:clamp(13px,1.7vw,14px);font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:7px;transition:background .2s;text-decoration:none}
        .btn-scan-qr:hover{background:var(--primary-dark)}
        .btn-scan-qr:active{background:#003976}
        .btn-scan-qr svg{width:18px;height:18px;flex-shrink:0}
        .receive-alert{margin-top:12px;padding:8px 12px;border-radius:7px;font-size:12px;display:none;align-items:center;gap:8px;animation:rcvFadeIn .2s ease-out;width:100%}
        .receive-alert.show{display:flex}
        .receive-alert.err{background:#fef2f2;border-left:3px solid #dc2626;color:#b91c1c}
        .receive-alert.ok{background:var(--blue-soft);border-left:3px solid var(--primary);color:var(--primary-dark)}
        .receive-alert i{font-size:13px;flex-shrink:0}
        .receive-alert span{line-height:1.4}
        @keyframes rcvFadeIn{from{opacity:0;transform:translateY(-3px)}to{opacity:1;transform:translateY(0)}}
        @media(max-width:900px){
            .receive-strip{padding:16px 18px}
            .receive-strip h2{font-size:15px}
            .rs-main{gap:0;grid-template-columns:minmax(0,1fr)}
            .ref-boxes-row{gap:3px}
            .ref-box{height:clamp(52px,13vw,58px);font-size:clamp(17px,4.4vw,19px)}
            .ref-sep{font-size:13px;padding:0 1px}
            .btn-clear-x{display:none}
            .rs-btn-wrap .btn-receive{flex:1 1 0;min-width:0;width:auto;height:48px;padding:0 12px;font-size:12.5px;white-space:nowrap}
            .rs-btn-wrap .btn-scan-qr{flex:1 1 0;min-width:0;width:auto;height:48px;padding:0 12px;font-size:12.5px;white-space:nowrap}
            .rs-btn-wrap{flex-direction:row;gap:8px}
        }
        /* ─── QR Scanner Modal ─── */
        .scanner-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:500;align-items:center;justify-content:center;padding:16px}
        .scanner-overlay.show{display:flex}
        .scanner-modal{background:#fff;border-radius:16px;max-width:440px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.25);animation:modalIn .18s ease;max-height:90vh;overflow-y:auto}
        .scanner-modal-head{display:flex;align-items:center;justify-content:space-between;padding:18px 22px;border-bottom:1px solid var(--border)}
        .scanner-modal-head h3{font-size:15px;font-weight:700;color:var(--text-dark)}
        .scanner-close{width:32px;height:32px;border:none;background:#f1f5f9;border-radius:8px;font-size:16px;color:#64748b;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .2s}
        .scanner-close:hover{background:#e2e8f0}
        .scanner-body{padding:20px 22px}
        .scanner-hint{font-size:12px;color:var(--text-muted);margin-bottom:14px;text-align:left}
        #qr-reader{width:100%;border-radius:8px;overflow:hidden}
        #qr-reader video{border-radius:8px}
        .camera-status{text-align:left;padding:10px 0 4px;font-size:12px;color:var(--text-muted)}
        .camera-status .cam-steps{margin:4px 0 8px;padding-left:16px;font-size:11.5px;line-height:1.7}
        .btn-cam-retry{margin-top:6px;padding:6px 16px;background:var(--primary);color:#fff;border:none;border-radius:6px;font-size:12px;cursor:pointer;font-weight:600}
        .btn-cam-retry:hover{background:var(--primary-dark)}

        /* Page header */
        .page-header{margin-bottom:28px;padding-bottom:20px;border-bottom:1px solid var(--border)}
        .page-header-top{display:flex;align-items:center;justify-content:space-between;gap:16px}
        .page-header h1{font-size:19px;font-weight:700;color:var(--text-dark);letter-spacing:-.2px}
        .page-header p{font-size:12.5px;color:var(--text-muted);margin-top:4px}
        .live-clock{display:flex;align-items:center;gap:14px;background:#fff;padding:10px 18px;border-radius:8px;border:1px solid var(--border);flex-shrink:0}
        .clock-time-display{font-size:18px;font-weight:600;color:var(--text-dark);font-variant-numeric:tabular-nums;line-height:1;white-space:nowrap}
        #c-h,#c-m{display:inline-block;width:2ch;text-align:center}
        .clock-time-display .seconds{font-size:14px;color:#9ca3af;font-weight:600;display:inline-block;width:2ch;text-align:center}
        .clock-time-display .period{font-size:11px;font-weight:600;color:var(--text-muted);margin-left:3px;vertical-align:top}
        .clock-sep{width:1px;height:28px;background:var(--border)}
        .clock-date-display{font-size:13px;color:var(--text-muted);font-weight:400;line-height:1.4}
        .clock-date-display .day{font-weight:600;color:var(--text-dark);display:block}

        /* ─── Panel & Office Section ─── */
        .panel{background:#fff;border-radius:10px;border:1px solid var(--border);overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.06)}
        .office-section{display:block}
        .anim{animation:fadeIn .3s ease-out}
        @keyframes fadeIn{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:translateY(0)}}


        /* ─── Sidebar ─── */
        .sidebar{position:fixed;top:0;left:0;width:240px;height:100vh;background:#0056b3;display:flex;flex-direction:column;z-index:200;transform:translateX(-100%);transition:transform .28s cubic-bezier(.4,0,.2,1)}
        .sidebar.open{transform:translateX(0)}
        .sb-brand{padding:22px 20px 18px;border-bottom:1px solid rgba(255,255,255,.12);text-align:center}
        .sb-brand img{width:64px;height:64px;margin-bottom:8px}
        .sb-brand h2{font-size:18px;font-weight:700;color:#fff;margin-bottom:2px}
        .sb-brand small{font-size:11px;color:rgba(255,255,255,.65);display:block}
        .sb-nav{flex:1;padding:12px 0;overflow-y:auto}
        .sb-nav a{display:flex;align-items:center;gap:11px;padding:11px 20px;color:rgba(255,255,255,.78);text-decoration:none;font-size:13px;font-weight:500;transition:background .15s,color .15s}
        .sb-nav a:hover,.sb-nav a.active{background:rgba(255,255,255,.14);color:#fff}
        .sb-nav a i{width:16px;text-align:center}
        .sb-nav .nav-section{padding:10px 20px 4px;font-size:9px;text-transform:uppercase;letter-spacing:1px;color:rgba(255,255,255,.4);font-weight:600}
        .sb-footer{padding:14px 20px;border-top:1px solid rgba(255,255,255,.12)}
        .sb-user{display:flex;align-items:center;gap:10px}
        .sb-avatar{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.15);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;font-weight:700;flex-shrink:0}
        .sb-user-info small{font-size:10px;color:rgba(255,255,255,.55);display:block}
        .sb-user-info span{font-size:12px;font-weight:600;color:#fff}
        .btn-logout{display:flex;align-items:center;gap:7px;margin-top:8px;padding:8px 14px;background:rgba(255,255,255,.1);border:none;border-radius:8px;color:rgba(255,255,255,.8);font-size:12px;cursor:pointer;font-family:'Poppins',sans-serif;width:100%;justify-content:center;transition:background .2s}
        .btn-logout:hover{background:rgba(220,38,38,.75)}

        /* ─── Mobile topbar ─── */
        .mob-topbar{display:flex;position:sticky;top:0;z-index:100;background:#0056b3;padding:14px 18px;align-items:center;justify-content:space-between;gap:14px;box-shadow:0 2px 8px rgba(0,0,0,.1)}
        .mob-hamburger{background:none;border:none;cursor:pointer;display:flex;flex-direction:column;gap:5px;z-index:1001;user-select:none;padding:4px}
        .mob-hamburger span{height:2px;width:24px;background:#fff;border-radius:2px;transition:all .4s ease}
        .mob-hamburger.toggle span:nth-child(1){transform:rotate(-45deg) translate(-4px,5px)}
        .mob-hamburger.toggle span:nth-child(2){opacity:0}
        .mob-hamburger.toggle span:nth-child(3){transform:rotate(45deg) translate(-4px,-5px)}
        .mob-brand{flex:1;display:flex;flex-direction:column;color:#fff;gap:4px}
        .mob-brand .brand-subtitle{font-size:clamp(10px,2.4vw,11px);font-weight:500;opacity:.88;text-transform:uppercase;letter-spacing:2.4px;line-height:1.1}
        .mob-brand h1{font-size:clamp(18px,4.8vw,22px);font-weight:700;margin:0;line-height:1.08}
        .mob-brand .brand-caption{font-size:clamp(11px,2.9vw,13px);font-weight:300;opacity:.9;line-height:1.18}
        .mob-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:199}
        .mob-overlay.open{display:block}

        /* ─── Main ─── */
        .main{margin-left:0;flex:1;display:flex;flex-direction:column}
        .dash-wrapper{max-width:1200px;width:100%;margin:0 auto;padding:28px 24px 48px;flex:1}

        /* ─── Page header ─── */
        .page-header{margin-bottom:24px}
        .page-header-top{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap}
        .page-header-text h1{font-size:19px;font-weight:700;color:var(--text-dark);letter-spacing:-.2px}
        .page-header-text p{font-size:12.5px;color:var(--text-muted);margin-top:3px}
        .ict-badge{display:inline-flex;align-items:center;gap:6px;background:#eff6ff;color:#2563eb;border:1px solid #bfdbfe;padding:5px 12px;border-radius:20px;font-size:11px;font-weight:600;letter-spacing:.3px;margin-top:8px}

        /* ─── Stats ─── */
        .stats-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:24px}
        .stat-card{background:#fff;border-radius:12px;padding:18px 18px 16px;box-shadow:none;border:1px solid var(--border);position:relative;overflow:hidden}
        .stat-label{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;margin-bottom:12px}
        .stat-card.c-blue .stat-label{background:#eff6ff;color:#2563eb}
        .stat-card.c-amber .stat-label{background:#fffbeb;color:#d97706}
        .stat-card.c-green .stat-label{background:#fffbeb;color:#d97706}
        .stat-card.c-emerald .stat-label{background:#f1f5f9;color:#64748b}
        .stat-num{font-size:28px;font-weight:800;color:var(--text-dark);line-height:1;letter-spacing:-1px}
        .stat-sub{font-size:11px;color:var(--text-muted);margin-top:6px}

        /* ─── Table card ─── */
        .table-card{background:#fff;border-radius:12px;border:1px solid var(--border);overflow:hidden}
        .dashboard-table-card.has-list{display:flex;flex-direction:column;max-height:clamp(520px,72vh,820px)}
        .dashboard-table-card.has-list .table-card-scroll{display:block;flex:1;min-height:0;overflow:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch}
        .dashboard-table-card.has-list .table-card-scroll thead th{position:sticky;top:0;z-index:2}
        .dashboard-table-card.has-list .pagination-bar{flex-shrink:0}
        .table-head{padding:16px 20px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
        .table-head-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px}
        .table-title{font-size:14px;font-weight:700;color:var(--text-dark)}
        .table-count{font-size:12px;color:var(--text-muted)}
        .filters{display:flex;flex-direction:column;gap:8px;margin-top:0}
        .filter-row{display:flex;gap:8px;align-items:center}
        .search-wrap{position:relative;display:flex;align-items:center;flex:1;min-width:0}
        .search-wrap i{position:absolute;left:11px;color:#94a3b8;font-size:13px;pointer-events:none;z-index:1}
        .search-wrap .queue-search-input{padding-right:38px}
        .queue-search-clear{position:absolute;right:9px;top:50%;transform:translateY(-50%);width:20px;height:20px;border:none;border-radius:999px;background:#e2e8f0;color:#64748b;display:none;align-items:center;justify-content:center;cursor:pointer;font-family:'Poppins',sans-serif;font-size:14px;line-height:1;padding:0;z-index:3;transition:all .15s}
        .queue-search-clear:hover{background:#cbd5e1;color:#334155}
        .queue-search-clear.show{display:inline-flex}
        .filters input{padding:8px 12px 8px 34px;font-family:'Poppins',sans-serif;font-size:13px;border:1.5px solid var(--border);border-radius:8px;outline:none;transition:border-color .2s,box-shadow .2s;width:100%;color:var(--text-dark);background:#fff}
        .filters input::placeholder{color:#94a3b8;font-size:12px}
        .filters input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,86,179,.1)}
        .filters select{padding:8px 32px 8px 12px;font-family:'Poppins',sans-serif;font-size:13px;border:1.5px solid var(--border);border-radius:8px;outline:none;transition:border-color .2s;color:var(--text-dark);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%2394a3b8' d='M5 7L0 2h10z'/%3E%3C/svg%3E") no-repeat right 11px center;-webkit-appearance:none;appearance:none;cursor:pointer;flex:1;min-width:0}
        .filters select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,86,179,.1)}
        .btn-clear{padding:8px 14px;border:1.5px solid var(--border);border-radius:8px;background:#f8fafc;color:var(--text-muted);font-size:12px;font-family:'Poppins',sans-serif;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:5px;white-space:nowrap}
        .btn-clear:hover{background:#e2e8f0;color:var(--text-dark)}
        .bulk-actions{display:flex;align-items:center;gap:8px;flex-wrap:nowrap;overflow-x:auto;scrollbar-width:thin;max-width:100%}
        .bulk-count{font-size:11px;font-weight:600;color:var(--text-muted);background:#f8fafc;border:1px solid var(--border);border-radius:999px;padding:6px 10px;white-space:nowrap;flex:0 0 auto}
        .bulk-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:7px 10px;border-radius:8px;border:1px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;font-size:11px;font-weight:700;font-family:'Poppins',sans-serif;cursor:pointer;transition:all .15s;white-space:nowrap;flex:0 0 auto;line-height:1;min-height:32px}
        .bulk-btn:hover:not(:disabled){background:#dbeafe;border-color:#93c5fd}
        .bulk-btn.warning{border-color:#fed7aa;background:#fff7ed;color:#c2410c}
        .bulk-btn.warning:hover:not(:disabled){background:#ffedd5;border-color:#fdba74}
        .bulk-btn.neutral{border-color:var(--border);background:#fff;color:var(--text-muted)}
        .bulk-btn.neutral:hover:not(:disabled){background:#f8fafc;color:var(--text-dark)}
        .bulk-btn:disabled{opacity:.48;cursor:not-allowed}

        /* ─── Table ─── */
        table{width:100%;border-collapse:collapse}
        th{text-align:left;padding:11px 16px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted);border-bottom:1px solid var(--border);background:#f8fafc}
        td{padding:12px 16px;font-size:13px;color:var(--text-dark);border-bottom:1px solid #f1f5f9;vertical-align:middle}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#fafbff}
        .t-num{font-size:12px;font-weight:600;font-family:monospace;letter-spacing:.4px;color:var(--primary)}
        .t-num-sub{font-size:10px;color:var(--text-muted);display:block;font-family:monospace;margin-top:1px}
        .t-date{font-size:12px;color:var(--text-muted)}
        .badge{padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px}
        .badge-submitted,
        .badge-received,
        .badge-in_review,
        .badge-on_hold,
        .badge-forwarded,
        .badge-completed,
        .badge-for_pickup,
        .badge-returned,
        .badge-cancelled,
        .badge-archived{background:#fff7ed;color:#c2410c}
        .td-action{text-align:center}
        .btn-view{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;background:var(--primary);color:#fff;border:none;border-radius:7px;font-size:11px;font-weight:600;cursor:pointer;font-family:'Poppins',sans-serif;text-decoration:none;transition:background .2s}
        .btn-view:hover{background:var(--primary-dark)}
        .btn-manage{display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:6px 10px;border-radius:7px;border:1px solid #bfdbfe;background:#eff6ff;color:#1d4ed8;font-size:11px;font-weight:600;cursor:pointer;font-family:'Poppins',sans-serif;text-decoration:none;transition:all .15s;white-space:nowrap}
        .btn-manage:hover{background:#dbeafe;border-color:#93c5fd;color:#1e40af}
        tr.doc-row{cursor:pointer}
        .cell-ellipsis{display:block;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .table-doc-count{font-size:11px;color:#94a3b8;font-weight:500}
        .queue-panel .table-head{padding:14px 20px}
        .queue-panel .table-head-left{display:flex;align-items:center;gap:8px;flex-wrap:wrap;flex:0 0 auto}
        .queue-panel .filters{display:flex;flex-direction:row;gap:8px;align-items:center;justify-content:flex-end;flex-wrap:nowrap;flex:1 1 760px;min-width:0}
        .queue-panel .filter-row{display:flex;align-items:center;gap:8px;min-width:0}
        .queue-panel .filter-row.filter-search{flex:1 1 320px}
        .queue-panel .filter-row.filter-status{flex:0 0 160px}
        .queue-panel .filter-row.bulk-actions{flex:0 0 auto}
        .queue-panel .search-wrap{flex:1 1 320px;max-width:none}
        .queue-panel .filters select{flex:1 1 160px;min-width:0}
        .queue-panel .btn-clear{display:none}
        .queue-panel table{width:100%;table-layout:fixed}
        .queue-panel .table-card-scroll{overflow-y:auto;overflow-x:hidden;scrollbar-gutter:stable}
        .queue-panel th{padding:10px 10px;background:#fff}
        .queue-panel td{padding:10px 10px;border-bottom:1px solid #f1f5f9}
        .queue-panel tr:last-child td{border-bottom:none}
        .queue-panel .col-select{width:44px}
        .queue-panel .col-ref{width:15%}
        .queue-panel .col-track{width:18%}
        .queue-panel .col-subject{width:23%}
        .queue-panel .col-submitted{width:20%}
        .queue-panel .col-status{width:12%}
        .queue-panel .col-cta{width:92px}
        .queue-panel .col-action{width:44px}
        .queue-panel .t-ref,.queue-panel .t-track{font-family:monospace;font-size:12px;font-weight:600;white-space:nowrap}
        .queue-panel .t-ref{color:var(--primary)}
        .queue-panel .t-track{color:var(--text-dark)}
        .queue-panel .t-status{white-space:nowrap;min-width:0}
        .queue-panel .submission-person{font-size:12px;color:var(--text-dark);font-weight:500}
        .queue-panel .submission-date{display:inline-flex;align-items:center;gap:5px;margin-top:4px;font-size:11px;color:#94a3b8;white-space:nowrap}
        .queue-panel .submission-date i{font-size:10px}
        .queue-panel .td-cta{white-space:nowrap;text-align:center}
        .queue-panel .td-cta .btn-manage{justify-content:center;margin-bottom:0}
        .queue-panel .td-action{width:44px;text-align:center}
        .td-select{text-align:center;width:44px}
        .bulk-doc-check{width:15px;height:15px;accent-color:var(--primary);cursor:pointer}
        .bulk-doc-check:disabled{cursor:not-allowed;opacity:.35}
        .mob-cards{display:none;padding:12px}
        .mob-card{width:100%;min-width:0;overflow:hidden;background:#fff;border:1px solid var(--border);border-radius:12px;padding:12px;box-shadow:0 1px 4px rgba(0,0,0,.04);cursor:pointer;transition:border-color .15s,box-shadow .15s}
        .mob-card + .mob-card{margin-top:10px}
        .mob-card:hover{border-color:var(--primary);box-shadow:0 2px 8px rgba(0,86,179,.08)}
        .mob-card-top{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:6px}
        .mob-card-top-actions{display:inline-flex;align-items:center;gap:6px;flex-shrink:0}
        .mob-card-top-actions .bulk-doc-check{flex:0 0 auto;margin-top:2px}
        .mob-card-ids{flex:1 1 auto;min-width:0;overflow:hidden}
        .mob-card-ref{font-size:11.5px;font-weight:700;color:var(--primary);font-family:monospace;line-height:1.25;white-space:normal;overflow-wrap:anywhere;word-break:break-word}
        .mob-card-track{font-size:10px;color:var(--text-muted);font-family:monospace;margin-top:2px;line-height:1.25;white-space:normal;overflow-wrap:anywhere;word-break:break-word}
        .mob-card-arrow{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:6px;color:#94a3b8;font-size:11px;flex-shrink:0;background:#f8fafc}
        .mob-card-subject{font-size:13.5px;font-weight:600;color:var(--text-dark);margin-bottom:8px;line-height:1.35;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;overflow-wrap:anywhere;word-break:break-word}
        .mob-card-meta{display:flex;align-items:flex-start;justify-content:space-between;gap:8px;margin-bottom:8px;flex-wrap:wrap}
        .mob-card-status{display:inline-flex;align-items:center;gap:5px;min-width:0;max-width:100%;flex:1 1 180px;flex-wrap:wrap}
        .mob-card-meta > .badge,.mob-card-status .badge{max-width:100%;min-height:0;justify-content:flex-start;text-align:left;white-space:normal;line-height:1.25;padding:4px 8px;overflow-wrap:anywhere;word-break:break-word}
        .mob-card-date{font-size:10.5px;color:var(--text-muted);display:inline-flex;align-items:center;gap:4px;white-space:nowrap;flex:0 0 auto;line-height:1.4}
        .mob-card-actions{display:flex;gap:8px;margin-top:10px}
        .mob-card-actions .btn-manage{justify-content:center;font-size:11px}
        .mob-card-row{display:flex;align-items:flex-start;gap:8px;margin-top:10px;font-size:12px;color:var(--text-muted);line-height:1.4}
        .mob-card-row i{font-size:11px;opacity:.75;flex:0 0 auto;margin-top:3px}
        .mob-card-row .cell-ellipsis{min-width:0;white-space:normal;overflow:visible;text-overflow:clip;overflow-wrap:anywhere;word-break:break-word}
        .mob-card.hidden-row{display:none}

        /* ─── Drawer ─── */
        .drawer-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:998;display:none;backdrop-filter:blur(2px)}
        .drawer-overlay.open{display:block}
        .doc-drawer{position:fixed;top:0;right:-480px;width:480px;max-width:100vw;height:100vh;background:#fff;z-index:999;box-shadow:-4px 0 32px rgba(0,0,0,.14);transition:right .28s cubic-bezier(.4,0,.2,1);display:flex;flex-direction:column}
        .doc-drawer.open{right:0}
        .drawer-head{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;gap:12px;flex-shrink:0}
        .drawer-head-info{flex:1;min-width:0}
        .drawer-head h3{font-size:16px;font-weight:700;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:4px}
        .drawer-ref{font-size:13px;color:var(--text-muted);font-family:monospace;letter-spacing:.4px;margin-bottom:2px}
        .drawer-track{font-size:11px;color:var(--text-muted);font-family:monospace;letter-spacing:.4px;margin-bottom:4px}
        .drawer-close{width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:#f8fafc;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:14px;flex-shrink:0;transition:all .15s}
        .drawer-close:hover{background:#fee2e2;color:#dc2626;border-color:#fca5a5}
        .drawer-body{flex:1;overflow-y:auto}
        .drawer-tl-head{padding:14px 20px 6px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);display:flex;align-items:center;gap:6px}
        .drawer-timeline{padding:10px 20px 24px}
        .tl{position:relative}
        .tl::before{content:'';position:absolute;left:7px;top:8px;bottom:8px;width:2px;background:var(--border);z-index:-1}
        .tl-item{position:relative;margin-bottom:20px;padding-left:24px}
        .tl-item:last-child{margin-bottom:0}
        .tl-dot{width:16px;height:16px;border-radius:50%;border:2.5px solid #fff;display:flex;align-items:center;justify-content:center;color:#fff;flex-shrink:0}
        .tl-dot.c-active{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.c-done{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.c-warn{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.c-danger{background:#dc2626;box-shadow:0 0 0 2px #dc2626}
        .tl-dot.c-latest{background:#f59e0b;box-shadow:0 0 0 2px #f59e0b}
        .tl-action{font-size:12px;font-weight:700;color:#1b263b}
        .tl-meta{font-size:12px;color:#64748b;margin:2px 0}
        .tl-remarks{font-size:12px;color:#64748b;background:#f8fafc;border-left:3px solid var(--border);padding:5px 9px;border-radius:4px;margin-top:5px}
        .tl-office-hdr{display:flex;align-items:center;font-size:13px;font-weight:700;color:var(--text-dark);text-transform:none;letter-spacing:0;margin:18px 0 8px -7px;padding-left:7px;padding-bottom:6px;position:relative}
        .tl-office-hdr::after{content:'';position:absolute;left:21px;right:0;bottom:0;height:1.5px;background:var(--border)}
        .tl-office-hdr:first-child{margin-top:0}
        .tl-dur{font-size:10px;font-weight:600;color:#6366f1;background:#eef2ff;border:1px solid #c7d2fe;border-radius:20px;padding:1px 8px;text-transform:none;letter-spacing:0;white-space:nowrap;flex-shrink:0;margin-left:auto}
        .drawer-loader{display:flex;align-items:center;justify-content:center;padding:48px;flex-direction:column;gap:12px;color:var(--text-muted);font-size:13px}
        .d-section{margin-bottom:18px}
        .d-section-title{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;margin-bottom:8px;display:flex;align-items:center;gap:6px}
        .d-row{display:flex;gap:12px;margin-bottom:8px;align-items:flex-start}
        .d-label{font-size:11px;font-weight:600;color:var(--text-muted);width:110px;flex-shrink:0;padding-top:1px}
        .d-val{font-size:12.5px;color:var(--text-dark);flex:1;word-break:break-word}
        .d-log-item{border-left:2px solid var(--border);padding:6px 0 6px 12px;margin-bottom:6px}
        .d-log-action{font-size:12px;font-weight:600;color:var(--text-dark)}
        .d-log-meta{font-size:11px;color:var(--text-muted);margin-top:2px}

        /* ─── Modal ─── */
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;display:none;align-items:center;justify-content:center}
        .modal-overlay.open{display:flex}
        .modal{background:#fff;border-radius:14px;padding:28px;width:340px;max-width:92vw;box-shadow:0 8px 40px rgba(0,0,0,.18)}
        .modal h3{font-size:15px;font-weight:700;color:var(--text-dark);margin-bottom:8px}
        .modal p{font-size:13px;color:var(--text-muted);margin-bottom:20px;line-height:1.6}
        .modal-btns{display:flex;gap:10px;justify-content:flex-end}
        .modal-btns button{padding:9px 20px;border-radius:9px;font-size:13px;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;border:none;transition:background .2s;white-space:nowrap}
        .btn-cancel-modal{background:#f1f5f9;color:var(--text-dark)}
        .btn-cancel-modal:hover{background:#e2e8f0}
        .btn-confirm-modal{background:#16a34a;color:#fff}
        .btn-confirm-modal:hover{background:#15803d}
        .modal-label{font-size:11px;font-weight:600;color:#334155;display:block;margin-bottom:5px;text-transform:uppercase;letter-spacing:.3px}
        .modal-field{width:100%;box-sizing:border-box;padding:9px 12px;font-family:'Poppins',sans-serif;font-size:13px;border:1.5px solid var(--border);border-radius:8px;outline:none;transition:border-color .2s;color:var(--text-dark);background:#fff}
        .modal-field:focus{border-color:var(--primary)}
        .modal-err{font-size:12px;color:#dc2626;margin-top:6px;display:none}
        .modal-err.show{display:block}
        .queue-toast{position:fixed;top:22px;right:22px;z-index:1300;background:#fff;border:1px solid var(--border);border-left:4px solid #16a34a;border-radius:10px;padding:13px 16px;box-shadow:0 14px 36px rgba(15,23,42,.16);font-size:13px;font-weight:600;color:var(--text-dark);max-width:min(360px,calc(100vw - 32px));white-space:pre-line;transform:translateX(calc(100% + 36px));opacity:0;transition:transform .22s ease,opacity .22s ease}
        .queue-toast.show{transform:translateX(0);opacity:1}
        .queue-toast.error{border-left-color:#dc2626}
        .btn{padding:10px 18px;border-radius:9px;font-size:13px;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;border:none;transition:background .2s;display:inline-flex;align-items:center;justify-content:center;gap:7px}
        .btn:disabled{opacity:.6;cursor:not-allowed}
        .btn-primary{background:var(--primary);color:#fff;width:100%}
        .btn-primary:hover:not(:disabled){background:var(--primary-dark)}

        /* ─── Pagination ─── */
        .pagination-bar{padding:14px 20px;display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;border-top:1px solid var(--border);font-size:12px;color:var(--text-muted)}
        .pagination-links{display:flex;gap:4px}
        .page-btn{display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;padding:0 8px;border-radius:7px;font-size:12px;font-weight:600;text-decoration:none;border:1.5px solid var(--border);color:var(--text-dark);background:#fff;transition:all .15s;cursor:pointer}
        .page-btn:hover{background:var(--primary);color:#fff;border-color:var(--primary)}
        .page-btn.active{background:var(--primary);color:#fff;border-color:var(--primary)}
        .page-btn.disabled{opacity:.4;pointer-events:none}

        /* ─── Empty ─── */
        .empty-state{padding:56px 20px;text-align:center;color:var(--text-muted)}
        .empty-state i{font-size:40px;color:#cbd5e1;margin-bottom:12px;display:block}
        .empty-state h3{font-size:15px;font-weight:600;color:#94a3b8;margin-bottom:6px}
        .empty-state p{font-size:12px}

        /* ─── Footer ─── */
        .site-footer{margin-left:0;width:100%;background:#fff;border-top:1px solid #e2e8f0;padding:20px 28px;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8}
        .site-footer .footer-left{display:flex;align-items:center;gap:6px}
        .site-footer .footer-right{font-size:11px;color:#b0b8c4}

        /* ─── Responsive ─── */
        @media(max-width:900px){
            .dash-wrapper{padding:20px 16px 40px}
            .stats-grid{grid-template-columns:1fr 1fr}
            .page-header-top{flex-direction:column;align-items:flex-start;gap:10px}
            .live-clock{display:none}
            .receive-strip{padding:16px 18px}
            .receive-strip h2{font-size:15px}
            .rs-main{gap:0;grid-template-columns:minmax(0,1fr)}
            .ref-boxes-row{gap:3px}
            .ref-box{height:clamp(52px,13vw,58px);font-size:clamp(17px,4.4vw,19px)}
            .ref-sep{font-size:13px;padding:0 1px}
            .btn-clear-x{display:none}
            .rs-btn-wrap .btn-receive{flex:1 1 0;min-width:0;width:auto;height:48px;padding:0 12px;font-size:12.5px;white-space:nowrap}
            .rs-btn-wrap .btn-scan-qr{flex:1 1 0;min-width:0;width:auto;height:48px;padding:0 12px;font-size:12.5px;white-space:nowrap}
            .rs-btn-wrap{flex-direction:row;gap:8px}
            .stat-card{padding:14px 14px}
            .stat-num{font-size:24px}
            .stat-label{font-size:10px;margin-bottom:6px}
            .site-footer{flex-direction:column;gap:6px;text-align:center;padding:16px 5%}
            .dashboard-table-card.has-list{max-height:none}
            .table-head{flex-direction:column;align-items:stretch;gap:10px;padding:14px 16px}
            .filters{gap:6px}
            .queue-panel .filters{flex-direction:column;align-items:stretch;justify-content:stretch;flex:0 1 auto;width:100%}
            .queue-panel .filter-row{display:flex;gap:6px;align-items:stretch;width:100%;flex:0 0 auto;min-height:0}
            .queue-panel .filter-row.filter-search,
            .queue-panel .filter-row.filter-status,
            .queue-panel .filter-row.bulk-actions{flex:0 0 auto}
            .queue-panel .search-wrap{flex:1 1 100%;max-width:none}
            .queue-panel .filters select{flex:1 1 auto;min-width:0}
            .bulk-actions{width:100%;display:grid;grid-template-columns:repeat(4,minmax(0,1fr));justify-content:stretch;overflow:visible}
            .bulk-actions .bulk-btn,
            .bulk-actions .bulk-count{width:100%;min-width:0}
            .filters input{font-size:11px;padding:6px 8px 6px 26px}
            .filters input::placeholder{font-size:10px}
            .filters select{font-size:11px;padding:6px 22px 6px 8px;min-width:100px}
            .dashboard-table-card.has-list .table-card-scroll{display:none!important}
            .dashboard-table-card.has-list .mob-cards{display:block!important;flex:none;min-height:0;overflow:visible;overscroll-behavior:auto;-webkit-overflow-scrolling:touch;padding:10px}
            .mob-cards{display:block;padding:10px}
            .table-card{border-radius:10px}
            .mob-card{padding:11px}
            .mob-card-actions .btn-manage{width:100%;min-height:38px;font-size:10.5px}
        }
        @media(max-width:1024px){
            .dashboard-table-card.has-list{max-height:none}
            .dashboard-table-card.has-list .table-card-scroll{display:none!important}
            .dashboard-table-card.has-list .mob-cards{display:block!important;flex:none;min-height:0;overflow:visible;overscroll-behavior:auto;-webkit-overflow-scrolling:touch}
        }
        @media(max-width:480px){
            .stats-grid{grid-template-columns:1fr}
            .mob-card-meta{display:grid;grid-template-columns:minmax(0,1fr) auto;align-items:start}
            .mob-card-status{flex:initial}
            .mob-card-actions{flex-direction:column}
            .bulk-actions{display:flex;flex-wrap:wrap;gap:6px;align-items:center;justify-content:stretch}
            .bulk-actions .bulk-btn,
            .bulk-actions .bulk-count{flex:1 1 calc(50% - 6px);min-width:0;padding:7px 6px}
            .bulk-actions .bulk-btn{font-size:10px;gap:4px}
            .bulk-actions .bulk-label{font-size:0}
            .bulk-actions .bulk-label::after{content:attr(data-short);font-size:10px}
            .bulk-actions .bulk-count{font-size:0;text-align:center}
            .bulk-actions .bulk-count::after{content:attr(data-count) " sel";font-size:10px}
        }
        @include('partials.submission-notifications-styles')
    </style>
    <script src="/js/auth-guard.js"></script>
    <script src="{{ asset('js/spa.js') }}?v={{ filemtime(public_path('js/spa.js')) }}" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js"></script>
</head>
<body>

<!-- Mobile top bar -->
<div class="mob-topbar">
    <button class="mob-hamburger" id="mobHamBtn" type="button" onclick="toggleSidebar()" aria-label="Menu"><span></span><span></span><span></span></button>
    <div class="mob-brand">
        <span class="brand-subtitle">Department of Education</span>
        <h1>CITY OF SAN JOSE DEL MONTE</h1>
        <span class="brand-caption">Document Tracking System &mdash; DOCTRAX</span>
    </div>
</div>
<div class="mob-overlay" id="mobOverlay" onclick="closeSidebar()"></div>

<!-- ─── Sidebar ─── -->
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
        <a href="/admin/schools"><i class="fas fa-school"></i> Schools</a>
        @unless($user->isSuperAdmin())
        <a href="/admin/documents"><i class="fas fa-folder-open"></i> Documents</a>
        @endunless
        @if($user->isSuperAdmin())
        <a href="/records/documents"><i class="fas fa-folder-open"></i> All Documents</a>
        <span class="nav-section">ICT Unit</span>
        <a href="/ict/documents" class="active"><i class="fas fa-network-wired"></i> ICT Documents</a>
        <a href="/office/search"><i class="fas fa-chart-line"></i> Reports</a>
        @endif
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
                <small>{{ $user->isSuperAdmin() ? 'Super Admin' : 'Admin' }}</small>
                <span>{{ explode(' ', $user->name)[0] }}</span>
            </div>
        </div>
        <button onclick="logout()" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>
</div>

<!-- ─── Main ─── -->
<div class="main">
<div class="dash-wrapper">

    <div class="page-header">
        <div class="page-header-top">
            <div>
                <h1>Welcome back, {{ $user->name }}!</h1>
                <p>{{ $user->isSuperAdmin() ? 'Information and Communications Technology Unit' : ($office?->name ?? 'Office') }} &mdash; here's your document queue.</p>
            </div>
            <div class="submission-header-actions">
                <div class="live-clock">
                    <div class="clock-time-display">
                        <span id="c-h">--</span>:<span id="c-m">--</span>:<span class="seconds" id="c-s">--</span>
                        <span class="period" id="c-p">--</span>
                    </div>
                    <div class="clock-sep"></div>
                    <div class="clock-date-display">
                        <span class="day" id="c-day">Loading...</span>
                        <span id="c-date"></span>
                    </div>
                </div>
                @include('partials.submission-notifications')
            </div>
        </div>
    </div>

    <div class="receive-strip">
        <h2>Receive Document</h2>
        <p class="rs-sub">Enter the 8-character tracking number</p>
        <div class="rs-center">
            <div class="rs-main">
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
            <div class="receive-alert" id="receiveRefMsg"><i class="fas fa-exclamation-circle"></i><span></span></div>
        </div>
        <div class="rs-btn-wrap">
            <button class="btn-receive" id="receiveRefBtn" onclick="window.receiveByReference()"><i class="fas fa-check"></i> Receive</button>
            <button class="btn-scan-qr" id="scanQrBtn" onclick="openScanner()"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 8v-2a2 2 0 0 1 2 -2h2"/><path d="M4 16v2a2 2 0 0 0 2 2h2"/><path d="M16 4h2a2 2 0 0 1 2 2v2"/><path d="M16 20h2a2 2 0 0 0 2 -2v-2"/><path d="M11 12h6"/><path d="M8 8h5"/><path d="M9 16h5"/></svg> Scan Document</button>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card c-blue">
            <div class="stat-label">Queue</div>
            <div class="stat-num" id="stat-active">{{ \App\Support\UiNumber::compact($stats['active']) }}</div>
            <div class="stat-sub">Received by your office</div>
        </div>
        <div class="stat-card c-amber">
            <div class="stat-label">Processing</div>
            <div class="stat-num" id="stat-in-review">{{ \App\Support\UiNumber::compact($stats['in_review']) }}</div>
            <div class="stat-sub">Being processed</div>
        </div>
        <div class="stat-card c-emerald">
            <div class="stat-label">Completed</div>
            <div class="stat-num" id="stat-completed">{{ \App\Support\UiNumber::compact($stats['completed']) }}</div>
            <div class="stat-sub">Resolved documents</div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="table-card dashboard-table-card queue-panel{{ $documents->count() ? ' has-list' : '' }}" id="document-queue">
        <div class="table-head">
            <div class="table-head-left">
                <span class="table-title">Document Queue</span>
                <span class="table-doc-count">{{ \App\Support\UiNumber::compact($documents->count()) }} showing</span>
            </div>
            <div class="filters">
                <div class="filter-row filter-search">
                    <div class="search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" id="tblSearch" class="queue-search-input" placeholder="Search tracking, document control, subject, sender, type…" data-no-clearable data-no-capitalize oninput="filterTable()">
                        <button type="button" class="queue-search-clear" id="queueSearchClear" aria-label="Clear search" title="Clear search">&times;</button>
                    </div>
                </div>
                <div class="filter-row filter-status">
                    <select id="tblStatus" onchange="filterTable()">
                        <option value="">All Statuses</option>
                        <option value="in_review">Processing</option>
                        <option value="for_pickup">For Pick up</option>
                        <option value="returned">For Return</option>
                        <option value="processed">Processed</option>
                    </select>
                    <button class="btn-clear" onclick="document.getElementById('tblSearch').value='';document.getElementById('tblStatus').value='';filterTable()"><i class="fas fa-rotate-left"></i> Clear</button>
                </div>
                <div class="filter-row bulk-actions">
                    <button type="button" class="bulk-btn neutral" id="bulkSelectVisibleBtn" onclick="clearBulkSelection()" disabled><i class="fas fa-square"></i><span class="bulk-label" data-short="Clear">Clear Selection</span></button>
                    <span class="bulk-count" id="bulkSelectedCount" data-count="0">0 selected</span>
                    <button type="button" class="bulk-btn" id="bulkStatusBtn" onclick="openBulkStatusModal()" disabled><i class="fas fa-tags"></i><span class="bulk-label" data-short="Status">Update Status</span></button>
                    <button type="button" class="bulk-btn warning" id="bulkEndBtn" onclick="openBulkEndModal()" disabled><i class="fas fa-check-circle"></i><span class="bulk-label" data-short="End">End Transaction</span></button>
                </div>
            </div>
        </div>

        @if($documents->count())
        <div class="table-scroll table-card-scroll">
        <table id="docTable">
            <colgroup>
                <col class="col-select">
                <col class="col-ref">
                <col class="col-track">
                <col class="col-subject">
                <col class="col-submitted">
                <col class="col-status">
                <col class="col-cta">
                <col class="col-action">
            </colgroup>
            <thead>
                <tr>
                    <th class="td-select"></th>
                    <th>Tracking #</th>
                    <th>Document Control #</th>
                    <th>Subject</th>
                    <th>Submitted</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th class="td-action"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $doc)
                @php
                    $sender = $doc->user ? $doc->user->name : ($doc->sender_name ?? 'Guest');
                    $canActOnDoc = $user->office_id
                        && (((int) $doc->current_office_id === (int) $user->office_id)
                            || ($doc->status === 'submitted' && (int) $doc->submitted_to_office_id === (int) $user->office_id));
                    $canHandleDoc = !$doc->current_handler_id || (int) $doc->current_handler_id === (int) $user->id;
                    $canStatusUpdateDoc = !$doc->isExternal() || $user->isRecords();
                    $bulkDisabledTitle = !$canStatusUpdateDoc
                        ? 'Only Records Section can update outside-submitted documents'
                        : (!$canHandleDoc ? 'Only the assigned handler can update this document' : 'This document is not eligible for bulk status update');
                    $canManageDoc = $canActOnDoc && $canHandleDoc && $canStatusUpdateDoc && in_array($doc->status, ['in_review', 'for_pickup', 'returned'], true);
                    $canBulkAct = ($canActOnDoc && $canHandleDoc) || (!$user->office_id && (int) $doc->current_handler_id === (int) $user->id);
                    $canBulkSelect = $canBulkAct && $canStatusUpdateDoc && in_array($doc->status, ['received', 'in_review', 'on_hold', 'for_pickup', 'returned'], true);
                    $canBulkEnd = $canBulkSelect && $doc->canCompleteTransactionFromCurrentStatus();
                    $endRemarksType = $doc->isInternalOfficeSubmission() ? 'office' : 'release';
                @endphp
                <tr class="doc-row" data-search="{{ strtolower(trim(($doc->reference_number ?? '').' '.($doc->tracking_number ?? '').' '.$doc->subject.' '.$sender.' '.($doc->type ?? ''))) }}" data-status="{{ $doc->status }}" onclick='openDocDetail("{{ $doc->reference_number ?: $doc->tracking_number }}")'>
                    <td class="td-select" onclick="event.stopPropagation()">
                        <input type="checkbox" class="bulk-doc-check" value="{{ $doc->id }}" data-status="{{ $doc->status }}" data-can-end="{{ $canBulkEnd ? '1' : '0' }}" data-end-remarks="{{ $endRemarksType }}" onchange="handleBulkCheckChange(this)" {{ $canBulkSelect ? '' : 'disabled' }} title="{{ $canBulkSelect ? 'Select document' : $bulkDisabledTitle }}">
                    </td>
                    <td class="t-ref"><div class="cell-ellipsis" title="{{ $doc->reference_number ?: 'N/A' }}">{{ $doc->reference_number ?: 'N/A' }}</div></td>
                    <td class="t-track"><div class="cell-ellipsis" title="{{ $doc->tracking_number ?: ($doc->reference_number ?: 'N/A') }}">{{ $doc->tracking_number ?: ($doc->reference_number ?: 'N/A') }}</div></td>
                    <td class="t-subject" style="max-width:200px"><div class="cell-ellipsis" style="font-weight:600" title="{{ $doc->subject }}">{{ $doc->subject }}</div></td>
                    <td class="t-submitted">
                        <div class="cell-ellipsis submission-person" title="{{ $sender }}">{{ $sender }}</div>
                        <div class="submission-date"><i class="fas fa-calendar-alt"></i>{{ $doc->created_at->format('M d, Y') }}</div>
                    </td>
                    <td class="t-status">
                        <span class="badge badge-{{ $doc->status }}">{{ $doc->statusLabelWithOffice() }}</span>
                    </td>
                    <td class="td-cta" onclick="event.stopPropagation()">
                        @if($canManageDoc)
                            <a class="btn-manage" href="/office/documents/{{ $doc->id }}?from=ict-queue" title="Manage document">
                                <i class="fas fa-pen"></i> Manage
                            </a>
                        @endif
                    </td>
                    <td class="td-action"><span class="row-arrow"><i class="fas fa-chevron-right"></i></span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        <div class="mob-cards">
            @foreach($documents as $doc)
            @php
                $sender = $doc->user ? $doc->user->name : ($doc->sender_name ?? 'Guest');
                $canActOnDoc = $user->office_id
                    && (((int) $doc->current_office_id === (int) $user->office_id)
                        || ($doc->status === 'submitted' && (int) $doc->submitted_to_office_id === (int) $user->office_id));
                $canHandleDoc = !$doc->current_handler_id || (int) $doc->current_handler_id === (int) $user->id;
                $canStatusUpdateDoc = !$doc->isExternal() || $user->isRecords();
                $bulkDisabledTitle = !$canStatusUpdateDoc
                    ? 'Only Records Section can update outside-submitted documents'
                    : (!$canHandleDoc ? 'Only the assigned handler can update this document' : 'This document is not eligible for bulk status update');
                $canManageDoc = $canActOnDoc && $canHandleDoc && $canStatusUpdateDoc && in_array($doc->status, ['in_review', 'for_pickup', 'returned'], true);
                $canBulkAct = ($canActOnDoc && $canHandleDoc) || (!$user->office_id && (int) $doc->current_handler_id === (int) $user->id);
                $canBulkSelect = $canBulkAct && $canStatusUpdateDoc && in_array($doc->status, ['received', 'in_review', 'on_hold', 'for_pickup', 'returned'], true);
                $canBulkEnd = $canBulkSelect && $doc->canCompleteTransactionFromCurrentStatus();
                $endRemarksType = $doc->isInternalOfficeSubmission() ? 'office' : 'release';
            @endphp
            <div class="mob-card" data-search="{{ strtolower(trim(($doc->reference_number ?? '').' '.($doc->tracking_number ?? '').' '.$doc->subject.' '.$sender.' '.($doc->type ?? ''))) }}" data-status="{{ $doc->status }}" onclick='openDocDetail("{{ $doc->reference_number ?: $doc->tracking_number }}")'>
                <div class="mob-card-top">
                    <div class="mob-card-ids">
                        <div class="mob-card-ref">{{ $doc->reference_number ?: 'N/A' }}</div>
                        <div class="mob-card-track">Document Control #: {{ $doc->tracking_number ?: ($doc->reference_number ?: 'N/A') }}</div>
                    </div>
                    <span class="mob-card-top-actions">
                        <input type="checkbox" class="bulk-doc-check" value="{{ $doc->id }}" data-status="{{ $doc->status }}" data-can-end="{{ $canBulkEnd ? '1' : '0' }}" data-end-remarks="{{ $endRemarksType }}" onclick="event.stopPropagation()" onchange="handleBulkCheckChange(this)" {{ $canBulkSelect ? '' : 'disabled' }} title="{{ $canBulkSelect ? 'Select document' : $bulkDisabledTitle }}">
                        <span class="mob-card-arrow"><i class="fas fa-chevron-right"></i></span>
                    </span>
                </div>
                <div class="mob-card-subject">{{ $doc->subject }}</div>
                <div class="mob-card-meta">
                    <span class="badge badge-{{ $doc->status }}">{{ $doc->statusLabelWithOffice() }}</span>
                    <span class="mob-card-date"><i class="fas fa-calendar"></i>{{ $doc->created_at->format('M d, Y') }}</span>
                </div>
                <div class="mob-card-row">
                    <i class="fas fa-user"></i>
                    <span class="cell-ellipsis" title="{{ $sender }}">{{ $sender }}</span>
                </div>
                @if($canManageDoc)
                <div class="mob-card-actions">
                    <a class="btn-manage" href="/office/documents/{{ $doc->id }}?from=ict-queue" onclick="event.stopPropagation()" title="Manage document">
                        <i class="fas fa-pen"></i> Manage
                    </a>
                </div>
                @endif
            </div>
            @endforeach
            <div class="empty-state" id="noResultsMobile" style="display:none">
                <i class="fas fa-search"></i>
                <h3>No results</h3>
                <p>No documents match your filter.</p>
            </div>
        </div>
        @include('partials.shared-pagination', [
            'paginator' => $documents,
            'itemLabel' => 'documents',
        ])

        <div class="empty-state" id="noResults" style="display:none">
            <i class="fas fa-search"></i>
            <h3>No results</h3>
            <p>No documents match your filter.</p>
        </div>

        @else
        <div class="empty-state" id="emptyState">
            <i class="fas fa-network-wired"></i>
            <h3>No documents found</h3>
            <p>No documents are currently in your office queue.</p>
        </div>
        @endif
    </div>

</div><!-- end dash-wrapper -->

<!-- ─── Document Drawer ─── -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>
<div class="doc-drawer" id="docDrawer">
    <div class="drawer-head">
        <div class="drawer-head-info">
            <h3 id="drTitle">—</h3>
            <div class="drawer-ref" id="drRef">—</div>
            <div class="drawer-track" id="drTrack"></div>
        </div>
        <button class="drawer-close" onclick="closeDrawer()"><i class="fas fa-times"></i></button>
    </div>
    <div class="drawer-body" id="drawerBody">
        <div class="drawer-loader"><span class="loading-dots"><span></span></span>Loading details...</div>
    </div>
</div>

<!-- ─── Accept Modal ─── -->
<!-- ─── End Transaction Modal ─── -->
<div class="modal-overlay" id="ictPickupModal">
    <div class="modal">
        <h3><i class="fas fa-check-circle" style="color:#0056b3;margin-right:6px"></i>End Transaction</h3>
        <p>Are you sure you want to end this transaction? This action cannot be undone.</p>
        <div style="margin-bottom:14px">
            <label style="font-size:12px;font-weight:600;color:#334155;display:block;margin-bottom:6px">Remarks (Optional)</label>
            <textarea id="ictPickupRemarks" style="width:100%;padding:9px 12px;font-family:Poppins,sans-serif;font-size:12px;border:1.5px solid var(--border);border-radius:8px;resize:vertical;min-height:60px;outline:none" placeholder="Add any remarks about the transaction completion..."></textarea>
        </div>
        <div class="modal-btns">
            <button class="btn-cancel-modal" onclick="closeIctPickupModal()">Cancel</button>
            <button class="btn-confirm-modal" id="confirmIctPickupBtn" onclick="confirmIctPickup()"><i class="fas fa-check"></i> End Transaction</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="bulkStatusModal" onclick="if(event.target===this)closeBulkStatusModal()">
    <div class="modal" style="width:420px">
        <h3><i class="fas fa-tags" style="color:#d97706;margin-right:6px"></i>Confirm Status Update</h3>
        <p><span id="bulkStatusCount">0</span> selected document(s) will be updated after you confirm.</p>
        <div style="margin-bottom:12px">
            <label class="modal-label">New Status <span style="color:#dc2626">*</span></label>
            <select class="modal-field" id="bulkNewStatus" onchange="updateBulkStatusRemarks()">
                <option value="">Select status...</option>
                <option value="for_pickup">For Pick up</option>
                <option value="returned">For Return</option>
            </select>
        </div>
        <div style="margin-bottom:14px">
            <label class="modal-label">Remarks <span style="color:#94a3b8;font-weight:400">(optional)</span></label>
            <select class="modal-field" id="bulkStatusRemarksSelect" onchange="handleBulkRemarksDropdown('bulkStatusRemarksSelect','bulkStatusRemarks')">
                <option value="">Select a remark...</option>
            </select>
            <textarea class="modal-field" id="bulkStatusRemarks" placeholder="Type your custom remark..." style="min-height:70px;resize:vertical;display:none;margin-top:8px" data-no-capitalize></textarea>
            <div class="modal-err" id="bulkStatusError"></div>
        </div>
        <div class="modal-btns">
            <button class="btn-cancel-modal" onclick="closeBulkStatusModal()">Cancel</button>
            <button class="btn-confirm-modal" id="confirmBulkStatusBtn" onclick="confirmBulkStatus()"><i class="fas fa-save"></i> Confirm Update</button>
        </div>
    </div>
</div>

<div class="modal-overlay" id="bulkEndModal" onclick="if(event.target===this)closeBulkEndModal()">
    <div class="modal" style="width:420px">
        <h3><i class="fas fa-check-circle" style="color:#0056b3;margin-right:6px"></i>Confirm End Transaction</h3>
        <p><span id="bulkEndCount">0</span> selected document(s) will be marked as completed after you confirm.</p>
        <div style="margin-bottom:14px">
            <label class="modal-label">Remarks <span style="color:#94a3b8;font-weight:400">(optional)</span></label>
            <select class="modal-field" id="bulkEndRemarksSelect" onchange="handleBulkRemarksDropdown('bulkEndRemarksSelect','bulkEndRemarks')">
                <option value="">Select a remark...</option>
                <option value="Document picked up by recipient.">Document picked up by recipient.</option>
                <option value="Document claimed by authorized representative.">Document claimed by authorized representative.</option>
                <option value="__custom">Custom Remark...</option>
            </select>
            <textarea class="modal-field" id="bulkEndRemarks" placeholder="Type your custom remark..." style="min-height:70px;resize:vertical;display:none;margin-top:8px" data-no-capitalize></textarea>
            <div class="modal-err" id="bulkEndError"></div>
        </div>
        <div class="modal-btns">
            <button class="btn-cancel-modal" onclick="closeBulkEndModal()">Cancel</button>
            <button class="btn-confirm-modal" id="confirmBulkEndBtn" onclick="confirmBulkEnd()"><i class="fas fa-check"></i> End Transaction</button>
        </div>
    </div>
</div>

</div><!-- end .main -->

<div class="queue-toast" id="queueToast" role="status" aria-live="polite"></div>

    <footer class="site-footer">
        <div class="footer-left">
            <span>&copy; {{ date('Y') }} DepEd Document Tracking System</span>
        </div>
        <div class="footer-right">
            Developed by Raymond Bautista
        </div>
    </footer>

<script>
var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var currentUserId = {{ (int) $user->id }};
var queueServerFilterReady = false;
var queueServerState = {
    search: @json(trim((string) request()->query('queue_search', ''))),
    status: @json(trim((string) request()->query('queue_status', '')))
};
var BULK_STATUS_REMARKS = {
    for_pickup: ['Document is ready for pickup.'],
    returned: ['Returned due to incomplete requirements.', 'Returned for correction - please resubmit.']
};
var BULK_END_REMARKS = {
    office: [
        'Document processed and completed.',
        'Request approved and completed.',
        'Action taken and transaction closed.'
    ],
    release: [
        'Document picked up by recipient.',
        'Document claimed by authorized representative.'
    ],
    mixed: [
        'Document processed and completed.',
        'Action taken and transaction closed.'
    ]
};

function getQueueFilterState(){
    var search = document.getElementById('tblSearch');
    var status = document.getElementById('tblStatus');
    return {
        search: search ? search.value.trim() : '',
        status: status ? status.value : ''
    };
}

function queueStateMatchesServer(state){
    state = state || getQueueFilterState();
    return (state.search || '') === (queueServerState.search || '')
        && (state.status || '') === (queueServerState.status || '');
}

function getQueueFilterTargetUrl(state){
    state = state || getQueueFilterState();
    var url = new URL(window.location.href);
    url.searchParams.delete('page');
    if (state.search) url.searchParams.set('queue_search', state.search);
    else url.searchParams.delete('queue_search');
    if (state.status) url.searchParams.set('queue_status', state.status);
    else url.searchParams.delete('queue_status');
    return url.pathname + url.search + url.hash;
}

function scheduleQueueServerFilter(){
    if (!queueServerFilterReady) return;
    var state = getQueueFilterState();
    clearTimeout(window._queueServerFilterTimer);
    if (queueStateMatchesServer(state)) return;
    window._queueServerFilterTimer = setTimeout(function(){
        var latestState = getQueueFilterState();
        if (queueStateMatchesServer(latestState)) return;
        window.location.assign(getQueueFilterTargetUrl(latestState));
    }, 450);
}

function setQueuePaginationForFilter(disabled){
    document.querySelectorAll('.pg-shared__link').forEach(function(link){
        if (disabled) {
            if (!link.dataset.filterHref) link.dataset.filterHref = link.getAttribute('href') || '';
            link.removeAttribute('href');
            link.setAttribute('aria-disabled', 'true');
            link.style.pointerEvents = 'none';
            link.style.opacity = '.55';
        } else if (link.dataset.filterHref) {
            link.setAttribute('href', link.dataset.filterHref);
            delete link.dataset.filterHref;
            link.removeAttribute('aria-disabled');
            link.style.pointerEvents = '';
            link.style.opacity = '';
        }
    });
}

function syncQueueFilterUrl(){
    if (!window.history || !window.history.replaceState) return;

    var state = getQueueFilterState();
    var url = new URL(window.location.href);
    if (state.search) url.searchParams.set('queue_search', state.search);
    else url.searchParams.delete('queue_search');
    if (state.status) url.searchParams.set('queue_status', state.status);
    else url.searchParams.delete('queue_status');

    var nextUrl = url.pathname + url.search + url.hash;
    if (nextUrl !== window.location.pathname + window.location.search + window.location.hash) {
        window.history.replaceState(window.history.state, '', nextUrl);
    }
}

function updateManageLinks(){
    var state = getQueueFilterState();
    var currentParams = new URLSearchParams(window.location.search);
    document.querySelectorAll('.btn-manage[href*="/office/documents/"]').forEach(function(link){
        var url = new URL(link.getAttribute('href'), window.location.origin);
        if (currentParams.has('page')) url.searchParams.set('page', currentParams.get('page') || '1');
        else url.searchParams.delete('page');
        if (state.search) url.searchParams.set('queue_search', state.search);
        else url.searchParams.delete('queue_search');
        if (state.status) url.searchParams.set('queue_status', state.status);
        else url.searchParams.delete('queue_status');
        link.setAttribute('href', url.pathname + url.search);
    });
}

function restoreQueueFilterState(){
    var params = new URLSearchParams(window.location.search);
    var search = document.getElementById('tblSearch');
    var status = document.getElementById('tblStatus');
    if (search && params.has('queue_search')) search.value = params.get('queue_search') || '';
    if (status && params.has('queue_status')) status.value = params.get('queue_status') || '';
}

function updateQueueSearchClear(){
    var search = document.getElementById('tblSearch');
    var btn = document.getElementById('queueSearchClear');
    if (search && btn) btn.classList.toggle('show', search.value.length > 0);
}

function clearQueueSearch(){
    var search = document.getElementById('tblSearch');
    if (!search) return;
    search.value = '';
    search.focus();
    filterTable();
}
window.clearQueueSearch = clearQueueSearch;

document.addEventListener('click', function(e){
    var btn = e.target.closest ? e.target.closest('#queueSearchClear') : null;
    if (!btn) return;
    e.preventDefault();
    e.stopPropagation();
    clearQueueSearch();
});

function getBulkChecks(){
    return Array.prototype.slice.call(document.querySelectorAll('.bulk-doc-check'));
}

function isBulkCheckVisible(chk){
    var row = chk.closest('tr.doc-row');
    if (row) return row.style.display !== 'none' && row.offsetParent !== null;
    var card = chk.closest('.mob-card');
    if (card) return card.style.display !== 'none' && !card.classList.contains('hidden-row') && card.offsetParent !== null;
    return chk.offsetParent !== null;
}

function uniqueChecks(checks){
    var seen = {};
    return checks.filter(function(chk){
        if (seen[chk.value]) return false;
        seen[chk.value] = true;
        return true;
    });
}

function getVisibleBulkChecks(){
    return uniqueChecks(getBulkChecks().filter(function(chk){
        return !chk.disabled && isBulkCheckVisible(chk);
    }));
}

function getSelectedBulkChecks(){
    return uniqueChecks(getBulkChecks().filter(function(chk){
        return chk.checked && !chk.disabled && isBulkCheckVisible(chk);
    }));
}

function getSelectedDocIds(){
    return getSelectedBulkChecks().map(function(chk){ return parseInt(chk.value, 10); }).filter(Boolean);
}

function selectedAllHaveStatus(status){
    var selected = getSelectedBulkChecks();
    return selected.length > 0 && selected.every(function(chk){
        return (chk.dataset.status || '') === status;
    });
}

function selectedCanOnlyEnd(){
    return selectedAllHaveStatus('for_pickup') || selectedAllHaveStatus('returned');
}

function selectedEndRemarksType(){
    var selected = getSelectedBulkChecks();
    var types = {};
    selected.forEach(function(chk){
        types[chk.dataset.endRemarks || 'release'] = true;
    });
    var keys = Object.keys(types);
    return keys.length === 1 ? keys[0] : 'mixed';
}

function selectedStatusCount(){
    var statuses = {};
    getSelectedBulkChecks().forEach(function(chk){
        statuses[chk.dataset.status || ''] = true;
    });
    return Object.keys(statuses).length;
}

function canBulkUpdateSelected(){
    var selected = getSelectedBulkChecks();
    return selected.length > 0 && selectedStatusCount() === 1 && !selectedCanOnlyEnd();
}

function refreshBulkStatusOptions(){
    var select = document.getElementById('bulkNewStatus');
    if (!select) return;

    Array.prototype.slice.call(select.options).forEach(function(option){
        if (!option.value) return;
        if (!option.dataset.label) option.dataset.label = option.textContent;

        var alreadyCurrent = selectedAllHaveStatus(option.value);
        option.disabled = alreadyCurrent;
        option.textContent = alreadyCurrent
            ? option.dataset.label + ' (already current)'
            : option.dataset.label;

        if (alreadyCurrent && select.value === option.value) {
            select.value = '';
        }
    });
}

function syncBulkCheck(docId, checked){
    getBulkChecks().forEach(function(chk){
        if (chk.value === String(docId) && !chk.disabled) chk.checked = checked;
    });
}

function handleBulkCheckChange(chk){
    syncBulkCheck(chk.value, chk.checked);
    updateBulkState();
}

function clearBulkSelection(){
    getBulkChecks().forEach(function(chk){
        if (!chk.disabled && chk.checked) syncBulkCheck(chk.value, false);
    });
    updateBulkState();
}

function bulkButtonHtml(iconClass, label, shortLabel){
    return '<i class="' + iconClass + '"></i><span class="bulk-label" data-short="' + shortLabel + '">' + label + '</span>';
}

function updateBulkState(){
    var selected = getSelectedBulkChecks();
    var selectedCount = selected.length;
    var endEligibleCount = selected.filter(function(chk){ return chk.dataset.canEnd === '1'; }).length;
    var countEl = document.getElementById('bulkSelectedCount');
    var statusBtn = document.getElementById('bulkStatusBtn');
    var endBtn = document.getElementById('bulkEndBtn');
    var selectVisibleBtn = document.getElementById('bulkSelectVisibleBtn');

    if (countEl) {
        countEl.textContent = selectedCount + ' selected';
        countEl.dataset.count = selectedCount;
    }
    if (statusBtn) {
        statusBtn.disabled = !canBulkUpdateSelected();
        statusBtn.title = selectedCount > 0 && selectedCanOnlyEnd()
            ? 'These selected documents can only be ended.'
            : (selectedCount > 0 && selectedStatusCount() > 1
                ? 'Select documents with the same status only'
                : '');
    }
    if (endBtn) {
        endBtn.disabled = selectedCount === 0 || endEligibleCount !== selectedCount;
        endBtn.title = selectedCount > 0 && endEligibleCount !== selectedCount
            ? 'Select eligible office-to-office, For Pick up, or For Return documents only'
            : '';
    }
    if (selectVisibleBtn) {
        selectVisibleBtn.innerHTML = bulkButtonHtml('fas fa-square', 'Clear Selection', 'Clear');
        selectVisibleBtn.disabled = selectedCount === 0;
    }
}

function handleBulkRemarksDropdown(selectId, textareaId){
    var sel = document.getElementById(selectId);
    var ta = document.getElementById(textareaId);
    if (!sel || !ta) return;
    if (sel.value === '__custom') { ta.style.display = ''; ta.value = ''; ta.focus(); }
    else { ta.style.display = 'none'; ta.value = ''; }
}

function getBulkRemarksValue(selectId, textareaId){
    var sel = document.getElementById(selectId);
    var ta = document.getElementById(textareaId);
    if (!sel) return '';
    return sel.value === '__custom' && ta ? ta.value.trim() : sel.value;
}

function showBulkErr(id, msg){
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = msg;
    el.classList.add('show');
}

function hideBulkErr(id){
    var el = document.getElementById(id);
    if (!el) return;
    el.textContent = '';
    el.classList.remove('show');
}

function showQueueToast(message, type){
    var toast = document.getElementById('queueToast');
    if (!toast) return;
    toast.textContent = message || '';
    toast.className = 'queue-toast ' + (type === 'error' ? 'error ' : '') + 'show';
    clearTimeout(window._queueToastTimer);
    window._queueToastTimer = setTimeout(function(){
        toast.classList.remove('show');
    }, 3200);
}

function updateBulkStatusRemarks(){
    refreshBulkStatusOptions();
    var status = document.getElementById('bulkNewStatus').value;
    var sel = document.getElementById('bulkStatusRemarksSelect');
    var ta = document.getElementById('bulkStatusRemarks');
    if (!sel || !ta) return;
    ta.style.display = 'none';
    ta.value = '';
    sel.innerHTML = '<option value="">Select a remark...</option>';
    (BULK_STATUS_REMARKS[status] || []).forEach(function(remark){
        var opt = document.createElement('option');
        opt.value = remark;
        opt.textContent = remark;
        sel.appendChild(opt);
    });
    var custom = document.createElement('option');
    custom.value = '__custom';
    custom.textContent = 'Custom Remark...';
    sel.appendChild(custom);
    sel.value = '';
}

function updateBulkEndRemarks(){
    var remarksType = selectedEndRemarksType();
    var remarks = BULK_END_REMARKS[remarksType] || BULK_END_REMARKS.release;
    var sel = document.getElementById('bulkEndRemarksSelect');
    var ta = document.getElementById('bulkEndRemarks');
    if (!sel || !ta) return;

    ta.style.display = 'none';
    ta.value = '';
    sel.innerHTML = '<option value="">Select a remark...</option>';
    remarks.forEach(function(remark){
        var opt = document.createElement('option');
        opt.value = remark;
        opt.textContent = remark;
        sel.appendChild(opt);
    });
    var custom = document.createElement('option');
    custom.value = '__custom';
    custom.textContent = 'Custom Remark...';
    sel.appendChild(custom);
    sel.value = '';
}

function openBulkStatusModal(){
    var selected = getSelectedBulkChecks();
    if (!selected.length) { showQueueToast('Please select at least one document.', 'error'); return; }
    if (selectedCanOnlyEnd()) { showQueueToast('Selected documents can only be ended.', 'error'); return; }
    if (!canBulkUpdateSelected()) { showQueueToast('Select documents with the same status only.', 'error'); return; }
    document.getElementById('bulkStatusCount').textContent = selected.length;
    document.getElementById('bulkNewStatus').value = '';
    refreshBulkStatusOptions();
    updateBulkStatusRemarks();
    hideBulkErr('bulkStatusError');
    document.getElementById('bulkStatusModal').classList.add('open');
}

function closeBulkStatusModal(){
    document.getElementById('bulkStatusModal').classList.remove('open');
}

function openBulkEndModal(){
    var selected = getSelectedBulkChecks();
    var endEligible = selected.filter(function(chk){ return chk.dataset.canEnd === '1'; });
    if (!selected.length) { showQueueToast('Please select at least one document.', 'error'); return; }
    if (endEligible.length !== selected.length) {
        showQueueToast('Select eligible office-to-office, For Pick up, or For Return documents only.', 'error');
        return;
    }
    document.getElementById('bulkEndCount').textContent = selected.length;
    updateBulkEndRemarks();
    hideBulkErr('bulkEndError');
    document.getElementById('bulkEndModal').classList.add('open');
}

function closeBulkEndModal(){
    document.getElementById('bulkEndModal').classList.remove('open');
}

function summarizeBulkResponse(data){
    var msg = data.message || 'Bulk action finished.';
    if (data.failures && data.failures.length) {
        msg += '\n\n' + data.failures.slice(0, 4).map(function(f){ return '- ' + f.message; }).join('\n');
        if (data.failures.length > 4) msg += '\n- More skipped documents not shown.';
    }
    return msg;
}

async function postBulkStatus(status, remarks){
    var ids = getSelectedDocIds();
    var res = await fetch('/api/ict/documents/bulk-status', {
        method: 'POST',
        headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
        body: JSON.stringify({document_ids: ids, status: status, remarks: remarks || null})
    });
    return await res.json();
}

async function confirmBulkStatus(){
    var status = document.getElementById('bulkNewStatus').value;
    var remarks = getBulkRemarksValue('bulkStatusRemarksSelect', 'bulkStatusRemarks');
    if (!status) { showBulkErr('bulkStatusError', 'Please select a new status.'); return; }
    if (selectedAllHaveStatus(status)) { showBulkErr('bulkStatusError', 'Selected document(s) already have that status.'); return; }
    hideBulkErr('bulkStatusError');
    var btn = document.getElementById('confirmBulkStatusBtn');
    btn.disabled = true;
    try {
        var data = await postBulkStatus(status, remarks);
        if (data.success) {
            closeBulkStatusModal();
            showQueueToast(summarizeBulkResponse(data), 'success');
            setTimeout(function(){ location.reload(); }, 900);
        } else {
            showBulkErr('bulkStatusError', summarizeBulkResponse(data));
            btn.disabled = false;
        }
    } catch (e) {
        showBulkErr('bulkStatusError', 'Network error. Please try again.');
        btn.disabled = false;
    }
}

async function confirmBulkEnd(){
    var selected = getSelectedBulkChecks();
    if (!selected.length || selected.some(function(chk){ return chk.dataset.canEnd !== '1'; })) {
        showBulkErr('bulkEndError', 'Select eligible office-to-office, For Pick up, or For Return documents only.');
        return;
    }
    var remarks = getBulkRemarksValue('bulkEndRemarksSelect', 'bulkEndRemarks');
    var btn = document.getElementById('confirmBulkEndBtn');
    btn.disabled = true;
    try {
        var data = await postBulkStatus('completed', remarks);
        if (data.success) {
            closeBulkEndModal();
            showQueueToast(summarizeBulkResponse(data), 'success');
            setTimeout(function(){ location.reload(); }, 900);
        } else {
            showBulkErr('bulkEndError', summarizeBulkResponse(data));
            btn.disabled = false;
        }
    } catch (e) {
        showBulkErr('bulkEndError', 'Network error. Please try again.');
        btn.disabled = false;
    }
}

    // ─── Sidebar ───
    window.toggleSidebar = function() {
        var s = document.getElementById('mainSidebar');
        var o = document.getElementById('mobOverlay');
        var open = s.classList.toggle('open');
        o.classList.toggle('open', open);
        document.body.style.overflow = open ? 'hidden' : '';
        document.getElementById('mobHamBtn').classList.toggle('toggle', open);
    };
    window.closeSidebar = function() {
        document.getElementById('mainSidebar').classList.remove('open');
        document.getElementById('mobOverlay').classList.remove('open');
        document.body.style.overflow = '';
        var btn = document.getElementById('mobHamBtn'); if (btn) btn.classList.remove('toggle');
    };

    // ─── Logout ───
    window.logout = window.performLogout || function() {
        window.location.replace('/login');
    };

    function showReceiveMsg(message, kind){
        var el = document.getElementById('receiveRefMsg');
        if(!el) return;
        var icon = el.querySelector('i');
        var span = el.querySelector('span');
        if(!message){ el.classList.remove('show','ok','err'); return; }
        span.textContent = message;
        el.className = 'receive-alert show ' + (kind || '');
        if(icon) icon.className = kind === 'ok' ? 'fas fa-check-circle' : 'fas fa-exclamation-circle';
    }

    /* ─── Segmented Reference Box Logic ─── */
    (function(){
        var container = document.getElementById('refBoxes');
        if(!container) return;
        var boxes = container.querySelectorAll('.ref-box');

        boxes.forEach(function(box){
            box.addEventListener('input', function(){
                this.value = this.value.replace(/[^A-Za-z0-9]/g,'').toUpperCase();
                this.classList.toggle('filled', this.value.length > 0);
                if(this.value.length === 1){
                    var next = container.querySelector('[data-idx="'+(parseInt(this.dataset.idx)+1)+'"]');
                    if(next) next.focus();
                }
            });
            box.addEventListener('keydown', function(e){
                if(e.key === 'Backspace' && !this.value){
                    var prev = container.querySelector('[data-idx="'+(parseInt(this.dataset.idx)-1)+'"]');
                    if(prev){ prev.focus(); prev.select(); }
                }
                if(e.key === 'Enter'){
                    e.preventDefault();
                    receiveByReference();
                }
                if(e.key === 'ArrowLeft'){
                    var prev2 = container.querySelector('[data-idx="'+(parseInt(this.dataset.idx)-1)+'"]');
                    if(prev2) prev2.focus();
                }
                if(e.key === 'ArrowRight'){
                    var next2 = container.querySelector('[data-idx="'+(parseInt(this.dataset.idx)+1)+'"]');
                    if(next2) next2.focus();
                }
            });
            box.addEventListener('paste', function(e){
                e.preventDefault();
                var paste = (e.clipboardData.getData('text')||'').replace(/[^A-Za-z0-9]/g,'').toUpperCase();
                var startIdx = parseInt(this.dataset.idx);
                for(var i=0; i<paste.length && startIdx+i<boxes.length; i++){
                    boxes[startIdx+i].value = paste[i];
                    boxes[startIdx+i].classList.add('filled');
                }
                var lastIdx = Math.min(startIdx+paste.length, boxes.length)-1;
                boxes[lastIdx].focus();
            });
            box.addEventListener('focus', function(){ this.select(); });
        });
    })();

    function getRefValue(){
        var boxes = document.querySelectorAll('#refBoxes .ref-box');
        var val = '';
        boxes.forEach(function(b){ val += b.value; });
        return val.trim().toUpperCase();
    }

    function clearRefBoxes(){
        var boxes = document.querySelectorAll('#refBoxes .ref-box');
        boxes.forEach(function(b){ b.value=''; b.classList.remove('filled'); });
        if(boxes.length) boxes[0].focus();
        showReceiveMsg('', '');
    }

    async function submitReceiveLookup(lookupValue, pendingMessage){
        var receiveBtn = document.getElementById('receiveRefBtn');
        var scanBtn = document.getElementById('scanQrBtn');
        var lookup = String(lookupValue || '').trim().toUpperCase();

        if(!lookup){
            showReceiveMsg('Tracking number is required.', 'err');
            return false;
        }

        showReceiveMsg(pendingMessage || 'Receiving document...', '');
        if(receiveBtn) receiveBtn.disabled = true;
        if(scanBtn) scanBtn.disabled = true;

        try{
            var res = await fetch('/api/ict/receive-by-reference', {
                method: 'POST',
                headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
                body: JSON.stringify({
                    reference_number: lookup,
                    tracking_number: lookup
                })
            });
            var data = await res.json();

            if(data.success){
                showReceiveMsg(data.message || 'Document received successfully.', 'ok');
                setTimeout(function(){ location.reload(); }, 700);
                return true;
            }

            showReceiveMsg(data.message || 'Failed to receive document.', 'err');
        }catch(e){
            showReceiveMsg('Network error. Please try again.', 'err');
        }

        if(receiveBtn) receiveBtn.disabled = false;
        if(scanBtn) scanBtn.disabled = false;
        return false;
    }

    async function receiveByReference(){
        var ref = getRefValue();

        if(ref.length < 8){
            showReceiveMsg('Please enter all 8 characters of the tracking number.', 'err');
            var boxes = document.querySelectorAll('#refBoxes .ref-box');
            for(var i=0;i<boxes.length;i++){
                if(!boxes[i].value){ boxes[i].focus(); break; }
            }
            return;
        }

        return submitReceiveLookup(ref, 'Receiving document...');
    }

    window.clearRefBoxes = clearRefBoxes;
    window.submitReceiveLookup = submitReceiveLookup;
    window.receiveByReference = receiveByReference;

    var _ictPickupDocId = null;
    window.openIctPickupModal = function(docId) {
        _ictPickupDocId = docId;
        document.getElementById('ictPickupRemarks').value = '';
        document.getElementById('ictPickupModal').classList.add('open');
    };
    window.closeIctPickupModal = function() {
        document.getElementById('ictPickupModal').classList.remove('open');
        _ictPickupDocId = null;
    };
    window.confirmIctPickup = function() {
        if (!_ictPickupDocId) return;
        var btn = document.getElementById('confirmIctPickupBtn');
        var remarks = document.getElementById('ictPickupRemarks').value.trim();
        btn.disabled = true;
        fetch('/api/ict/documents/' + _ictPickupDocId + '/status', {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
            body: JSON.stringify({status:'completed',remarks:remarks})
        }).then(function(r){return r.json();}).then(function(d) {
            if (d.success) {
                closeIctPickupModal();
                showQueueToast('Transaction ended successfully.', 'success');
                setTimeout(function(){ location.reload(); }, 600);
            } else {
                showQueueToast(d.message || 'Could not end transaction.', 'error');
                btn.disabled = false;
            }
        }).catch(function() {
            showQueueToast('Network error.', 'error');
            btn.disabled = false;
        });
    };
    window.escapeAttr = function(str) {
        if (str === null || str === undefined) return '';
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    };

    // ─── Client-side Filter ───
    window.filterTable = function() {
        var q = (document.getElementById('tblSearch').value || '').toLowerCase();
        var s = document.getElementById('tblStatus').value || '';
        var rows = document.querySelectorAll('#docTable tbody tr.doc-row');
        var cards = document.querySelectorAll('.mob-cards .mob-card');
        var visible = 0;
        rows.forEach(function(tr) {
            var ds = (tr.getAttribute('data-search') || '').toLowerCase();
            var dstat = (tr.getAttribute('data-status') || '').toLowerCase();
            var statusMatch = !s || (s === 'processed'
                ? dstat === 'completed'
                : dstat === s);
            var show = (!q || ds.indexOf(q) > -1) && statusMatch;
            tr.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        cards.forEach(function(card) {
            var ds = (card.getAttribute('data-search') || '').toLowerCase();
            var dstat = (card.getAttribute('data-status') || '').toLowerCase();
            var statusMatch = !s || (s === 'processed'
                ? dstat === 'completed'
                : dstat === s);
            var show = (!q || ds.indexOf(q) > -1) && statusMatch;
            card.classList.toggle('hidden-row', !show);
        });
        var nr = document.getElementById('noResults');
        if (nr) nr.style.display = (visible === 0 && rows.length > 0) ? 'block' : 'none';
        var nrMobile = document.getElementById('noResultsMobile');
        if (nrMobile) nrMobile.style.display = (visible === 0 && cards.length > 0) ? 'block' : 'none';
        setQueuePaginationForFilter(!!(q || s) && !queueStateMatchesServer());
        updateQueueSearchClear();
        syncQueueFilterUrl();
        updateManageLinks();
        updateBulkState();
        scheduleQueueServerFilter();
    };
    restoreQueueFilterState();
    filterTable();
    queueServerFilterReady = true;

    // ─── Document Drawer ───
    window.openDocDetail = function(trackingNumber) {
        trackingNumber = String(trackingNumber || '').trim().toUpperCase();
        if (!trackingNumber) return;
        document.getElementById('drTitle').textContent = '-';
        document.getElementById('drRef').textContent = trackingNumber;
        document.getElementById('drTrack').textContent = '';
        document.getElementById('drawerBody').innerHTML = '<div class="drawer-loader"><span class="loading-dots"><span></span></span>Loading details...</div>';
        document.getElementById('docDrawer').classList.add('open');
        document.getElementById('drawerOverlay').classList.add('open');
        document.body.style.overflow = 'hidden';
        window.docTraxFetchJson('/api/internal/track-document', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
            timeoutMs: 15000,
            body: JSON.stringify({
                reference_number: trackingNumber,
                tracking_number: trackingNumber
            })
        }).then(function(d) {
            if (!d.success || !d.document) {
                document.getElementById('drawerBody').innerHTML = '<div class="drawer-loader">' + escapeHtml(d.message || 'Document not found.') + '</div>';
                return;
            }
            renderDrawer(d.document);
        }).catch(function(error){
            var message = window.describeRequestError
                ? window.describeRequestError(error, 'Could not load document.')
                : 'Could not load document.';
            document.getElementById('drawerBody').innerHTML = '<div class="drawer-loader">' + escapeHtml(message) + '</div>';
        });
    };
    window.closeDrawer = function() {
        document.getElementById('docDrawer').classList.remove('open');
        document.getElementById('drawerOverlay').classList.remove('open');
        document.body.style.overflow = '';
    };
    function escapeHtml(s) {
        if (!s) return '';
        var d = document.createElement('div');
        d.appendChild(document.createTextNode(s));
        return d.innerHTML;
    }
    function dotClass(s){
        var st = String(s || '').toLowerCase();
        if(st === 'cancelled' || /return|resubmit/.test(st)) return 'c-danger';
        if(s==='completed') return 'c-done';
        if(s==='forwarded') return 'c-warn';
        return 'c-active';
    }
    function renderDrawer(doc){
        var ref = doc.reference_number || doc.tracking_number || '-';
        var trackingNo = doc.tracking_number || '';
        document.getElementById('drTitle').textContent = doc.subject || '-';
        document.getElementById('drRef').textContent = 'Tracking # ' + ref;
        document.getElementById('drTrack').textContent = (trackingNo && trackingNo !== ref) ? ('Document Control # ' + trackingNo) : '';

        var logs = Array.isArray(doc.routing_logs) ? doc.routing_logs : [];
        var tlHtml = '';
        if (!logs.length) {
            tlHtml = '<div style="color:var(--text-muted);font-size:13px;padding:4px 0">No routing history yet.</div>';
        } else {
            function _gk(log) {
                return (log.action === 'submitted') ? '__pending__' :
                       (log.action === 'forwarded' ? (log.from_office || 'Unknown') :
                       (log.to_office || log.from_office || 'Unknown'));
            }
            var segDurations = [];
            logs.forEach(function(log) {
                if (log.office_duration_human != null) {
                    segDurations.push({ key: _gk(log), dur: log.office_duration_human });
                }
            });
            var segDurIdx = segDurations.length - 1;
            var prevGroupKey = null;
            logs.slice().reverse().forEach(function(log, idx) {
                var isLatest = idx === 0;
                var dc;
                var latestStatus = String(doc.status || '').toLowerCase();
                if (isLatest) {
                    if (latestStatus === 'completed') dc = 'c-done';
                    else if (/return|resubmit/.test(latestStatus)) dc = 'c-danger';
                    else dc = 'c-latest';
                } else {
                    dc = dotClass(log.status_after);
                }
                var dotIcon = isLatest ? 'fa-arrow-up' : 'fa-check';
                var groupKey = _gk(log);
                var groupLabel = (groupKey === '__pending__') ? 'Submitted — Pending Physical Submission' : (((log.action === 'archived' || log.status_after === 'archived') && groupKey === 'Unknown') ? 'Archived' : groupKey);
                if (groupKey !== prevGroupKey) {
                    prevGroupKey = groupKey;
                    var dur = null;
                    if (segDurIdx >= 0 && segDurations[segDurIdx] && segDurations[segDurIdx].key === groupKey) {
                        dur = segDurations[segDurIdx--].dur;
                    }
                    tlHtml += '<div class="tl-office-hdr"><div class="tl-dot ' + dc + '" style="margin-right:5px"><i class="fas ' + dotIcon + '" style="font-size:5px"></i></div><span>' + escapeHtml(groupLabel) + '</span>' + (dur ? '<span class="tl-dur"><i class="fas fa-hourglass-half" style="margin-right:4px;font-size:9px"></i>' + escapeHtml(dur) + '</span>' : '') + '</div>';
                }
                tlHtml += '<div class="tl-item">' +
                    (log.performed_by ? '<div class="tl-action">' + escapeHtml(log.performed_by) + '</div>' : '') +
                    '<div class="tl-meta"><i class="fas fa-clock" style="margin-right:3px;font-size:10px"></i>' + escapeHtml(log.timestamp || '-') + '</div>' +
                    '<div class="tl-meta"><i class="fas fa-tasks" style="margin-right:3px;font-size:10px"></i>' + escapeHtml(log.action_label || 'Status Updated') + '</div>' +
                    (log.remarks ? '<div class="tl-remarks">' + escapeHtml(log.remarks) + '</div>' : '') +
                    '</div>';
            });
        }

        var currentHandlerId = doc.current_handler_id ? parseInt(doc.current_handler_id, 10) : null;
        var directOfficeEnd = doc.is_external === false && ['received','in_review','on_hold'].indexOf(doc.status) !== -1;
        var canEndFromDrawer = (['for_pickup','returned'].indexOf(doc.status) !== -1 || directOfficeEnd)
            && doc.is_external === false
            && (!currentHandlerId || currentHandlerId === currentUserId);
        var endCopy = directOfficeEnd
            ? 'End this office-to-office transaction once your office has finished processing it.'
            : 'This document is ready for release or return. End the transaction once the recipient has actually claimed it.';

        document.getElementById('drawerBody').innerHTML =
            '<div class="drawer-tl-head"><i class="fas fa-history"></i> Routing History</div>' +
            '<div class="drawer-timeline"><div class="tl">' + tlHtml + '</div></div>' +
            (canEndFromDrawer ? '<div style="padding:20px"><div class="action-section" style="border-top:1px solid var(--border);padding-top:16px"><h3 style="font-size:13px;font-weight:600;color:var(--text-dark);margin-bottom:8px">End Transaction</h3><p style="font-size:12px;color:var(--text-muted);margin-bottom:12px">' + escapeHtml(endCopy) + '</p><button class="btn btn-primary" onclick="openIctPickupModal(' + escapeAttr(doc.id) + ')">End Transaction</button></div></div>' : '');
    }

    // ─── Live Stats (silent update every 30s) ───
    var _ictStatKeys = ['active','in_review','completed'];

    function refreshStats() {
        fetch('/api/ict-stats', {headers:{'Accept':'application/json','X-CSRF-TOKEN':csrf}})
            .then(function(r){return r.json();})
            .then(function(d) {
                _ictStatKeys.forEach(function(k) {
                    var el = document.getElementById('stat-' + k.replace('_','-'));
                    if (el && d[k] !== undefined) {
                        var compactCount = window.formatCompactCount || function(v) { return String(v); };
                        el.textContent = compactCount(d[k]);
                    }
                });
            }).catch(function(){});
    }
    if (window.smartInterval) { window.smartInterval(refreshStats, 30000); }
    else { setInterval(refreshStats, 30000); }

// ─── Live clock ───
(function(){
    var clockInterval;
    function tick(){
        var n=new Date();
        var h=n.getHours(),m=n.getMinutes(),s=n.getSeconds();
        var p=h>=12?'PM':'AM',h12=h%12||12;
        var el=document.getElementById('c-h');if(!el){clearInterval(clockInterval);return;}
        el.textContent=String(h12).padStart(2,'0');
        document.getElementById('c-m').textContent=String(m).padStart(2,'0');
        document.getElementById('c-s').textContent=String(s).padStart(2,'0');
        document.getElementById('c-p').textContent=p;
        var days=['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
        var mos=['January','February','March','April','May','June','July','August','September','October','November','December'];
        document.getElementById('c-day').textContent=days[n.getDay()];
        document.getElementById('c-date').textContent=mos[n.getMonth()]+' '+n.getDate()+', '+n.getFullYear();
    }
    tick();clockInterval=setInterval(tick,1000);
})();
</script>

    <!-- QR Scanner Modal -->
    <div class="scanner-overlay" id="scannerOverlay" onclick="if(event.target===this)closeScanner()">
        <div class="scanner-modal">
            <div class="scanner-modal-head">
                <h3>Scan Document QR Code</h3>
                <button class="scanner-close" onclick="closeScanner()">&#10005;</button>
            </div>
            <div class="scanner-body">
                <div class="scanner-hint">Point your camera at the document's QR code to receive it directly on this dashboard.</div>
                <div id="qr-reader"></div>
                <p class="camera-status" id="cameraStatus">Initializing camera...</p>
            </div>
        </div>
    </div>
    <script src="/js/html5-qrcode.min.js"></script>
    <script src="/js/jsqr.js"></script>
    <script>
    (function(){
        var html5QrCode = null;
        var scannerRunning = false;
        var activeStream = null;
        var scanLoopTimer = null;
        var barcodeDetector = null;
        var previewVideo = null;
        var scanCooldown = false;
        var scanBuffer = '';
        var scanTimer = null;
        var SCAN_IDLE_MS = 80;

        function scannerOverlay(){
            return document.getElementById('scannerOverlay');
        }

        function statusEl(){
            return document.getElementById('cameraStatus');
        }

        function showStatus(message, isHtml){
            var el = statusEl();
            if (!el) return;
            if (isHtml) { el.innerHTML = message; } else { el.textContent = message; }
            el.style.display = 'block';
        }

        function showPermissionDenied(){
            var localhostUrl = (location.hostname === '127.0.0.1')
                ? location.href.replace('127.0.0.1', 'localhost')
                : null;
            var msg = '<strong style="color:#dc2626;">&#9888; Camera blocked.</strong> ';
            if (localhostUrl) {
                msg += 'Your browser blocked camera for <strong>127.0.0.1</strong>. '
                    + '<a href="' + localhostUrl + '" style="color:#0056b3;font-weight:700;">Open on localhost instead</a> '
                    + '(same app, camera will work there).';
            } else {
                msg += 'Click the <strong>lock icon</strong> in the address bar → Camera → <strong>Allow</strong>, then '
                    + '<button class="btn-cam-retry" onclick="window.retryCamera()" style="padding:2px 10px;">Retry</button>.';
            }
            showStatus(msg, true);
        }

        function isScannerOpen(){
            var overlay = scannerOverlay();
            return !!(overlay && overlay.classList.contains('show'));
        }

        function onDecodeSuccess(decodedText) {
            if (scanCooldown) return;
            scanCooldown = true;
            setTimeout(function(){ scanCooldown = false; }, 2000);
            processScannedText(decodedText);
        }

        function fillRefBoxes(tracking){
            if (!/^[A-Z0-9]{1,8}$/.test(tracking)) return;
            var boxes = document.querySelectorAll('#refBoxes .ref-box');
            if (!boxes.length) return;
            for (var i = 0; i < boxes.length; i++) {
                boxes[i].value = '';
                boxes[i].classList.remove('filled');
            }
            for (var j = 0; j < boxes.length && j < tracking.length; j++) {
                boxes[j].value = tracking[j];
                boxes[j].classList.add('filled');
            }
        }

        function normalizeScannedLookup(text) {
            var raw = String(text || '').trim();
            if (!raw) return '';

            try {
                var parsed = new URL(raw, window.location.origin);
                var receiveMatch = parsed.pathname.match(/\/receive\/([A-Za-z0-9\-]+)/i);
                if (receiveMatch) {
                    raw = receiveMatch[1];
                } else {
                    var lookupParam = parsed.searchParams.get('ref')
                        || parsed.searchParams.get('tracking')
                        || parsed.searchParams.get('reference');
                    if (lookupParam) raw = lookupParam;
                }
            } catch (e) {}

            var fallbackMatch = raw.match(/\/receive\/([A-Za-z0-9\-]+)/i);
            if (fallbackMatch) raw = fallbackMatch[1];

            raw = raw.trim().toUpperCase();
            if (!raw) return '';

            var compact = raw.replace(/[^A-Z0-9]/g, '');
            if (/^[A-Z0-9]{8}$/.test(compact)) {
                return compact;
            }

            return raw.replace(/[^A-Z0-9\-]/g, '').replace(/^-+|-+$/g, '');
        }

        function processScannedText(text) {
            var lookup = normalizeScannedLookup(text);
            if (!lookup || lookup.length < 8) return;

            window.closeScanner();
            fillRefBoxes(lookup);

            if (typeof window.submitReceiveLookup !== 'function') {
                showStatus('Scanner receive handler is unavailable. Please refresh the page.');
                return;
            }

            window.submitReceiveLookup(lookup, 'Receiving scanned document...');
        }

        window.openScanner = function() {
            var overlay = scannerOverlay();
            if (!overlay) return;
            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
            scanBuffer = '';
            if (scanTimer) {
                clearTimeout(scanTimer);
                scanTimer = null;
            }
            stopCamera();
            startCamera();
        };

        window.closeScanner = function() {
            var overlay = scannerOverlay();
            if (overlay) overlay.classList.remove('show');
            document.body.style.overflow = '';
            stopCamera();
        };

        function readerEl() {
            return document.getElementById('qr-reader');
        }

        function clearReader() {
            var el = readerEl();
            if (el) el.innerHTML = '';
            previewVideo = null;
        }

        function isPermDenied(e) {
            var s = String(e || '').toLowerCase();
            return s.indexOf('notallowed') !== -1 || s.indexOf('permission') !== -1 || s.indexOf('denied') !== -1;
        }

        function normalizeCameraError(err) {
            var raw = String((err && (err.name || err.message)) || err || '').toLowerCase();
            if (raw.indexOf('notallowed') !== -1 || raw.indexOf('permission') !== -1 || raw.indexOf('denied') !== -1) return 'denied';
            if (raw.indexOf('notfound') !== -1 || raw.indexOf('devicesnotfound') !== -1 || raw.indexOf('overconstrained') !== -1 || raw.indexOf('constraint') !== -1) return 'notfound';
            if (raw.indexOf('notreadable') !== -1 || raw.indexOf('trackstart') !== -1 || raw.indexOf('could not start') !== -1 || raw.indexOf('device in use') !== -1 || raw.indexOf('in use') !== -1) return 'busy';
            if (raw.indexOf('security') !== -1 || raw.indexOf('secure') !== -1) return 'security';
            return raw || 'unknown';
        }

        function getCameraStream(constraints) {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                return Promise.reject(new Error('GET_USER_MEDIA_UNAVAILABLE'));
            }
            return navigator.mediaDevices.getUserMedia({ video: constraints, audio: false });
        }

        function listVideoInputs() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) {
                return Promise.resolve([]);
            }
            return navigator.mediaDevices.enumerateDevices()
                .then(function(devices) {
                    return (devices || []).filter(function(device) {
                        return device && device.kind === 'videoinput';
                    });
                })
                .catch(function() {
                    return [];
                });
        }

        function cameraScore(device, isMobile) {
            var label = String((device && device.label) || '').toLowerCase();
            var score = 0;

            if (isMobile) {
                if (/back|rear|environment|world|traseira|trasera|externa/.test(label)) score += 50;
                if (/front|user|selfie|facetime|integrated|frontal|frente/.test(label)) score -= 25;
            } else {
                if (/usb|external|rear|back|environment/.test(label)) score += 20;
                if (/integrated|front|facetime|user/.test(label)) score += 5;
            }

            return score;
        }

        function buildCameraAttempts(isMobile, devices) {
            var attempts = [];
            var seen = {};
            var hdHint = { width: { ideal: 1280 }, height: { ideal: 720 } };

            function addAttempt(constraints) {
                var key = typeof constraints === 'boolean'
                    ? ('bool:' + constraints)
                    : JSON.stringify(constraints);
                if (seen[key]) return;
                seen[key] = true;
                attempts.push(constraints);
            }

            if (isMobile) {
                addAttempt({
                    facingMode: { ideal: 'environment' },
                    width: hdHint.width,
                    height: hdHint.height
                });
            }

            (devices || []).slice().sort(function(a, b) {
                return cameraScore(b, isMobile) - cameraScore(a, isMobile);
            }).forEach(function(device) {
                if (!device.deviceId) return;
                addAttempt({
                    deviceId: { exact: device.deviceId },
                    width: hdHint.width,
                    height: hdHint.height
                });
            });

            addAttempt(true);
            addAttempt({
                facingMode: 'user',
                width: hdHint.width,
                height: hdHint.height
            });

            return attempts;
        }


        function attachPreview(stream) {
            clearReader();
            var el = readerEl();
            if (!el) return Promise.reject(new Error('QR_READER_MISSING'));
            var video = document.createElement('video');
            video.setAttribute('autoplay', '');
            video.setAttribute('muted', '');
            video.setAttribute('playsinline', '');
            video.muted = true;
            video.srcObject = stream;
            video.style.width = '100%';
            video.style.display = 'block';
            video.style.borderRadius = '8px';
            el.appendChild(video);
            previewVideo = video;
            return video.play().catch(function() {}).then(function(){ return video; });
        }

        function startDetectLoop() {
            if (typeof jsQR === 'undefined') {
                showStatus('QR library not loaded. Please refresh the page.');
                return;
            }
            var canvas = document.createElement('canvas');
            var ctx = canvas.getContext('2d');
            function scanFrame() {
                if (!scannerRunning || !previewVideo) return;
                if (previewVideo.readyState < 2 || !previewVideo.videoWidth) {
                    if (scannerRunning) scanLoopTimer = setTimeout(scanFrame, 200);
                    return;
                }
                try {
                    canvas.width = previewVideo.videoWidth;
                    canvas.height = previewVideo.videoHeight;
                    ctx.drawImage(previewVideo, 0, 0);
                    var imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                    var code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'dontInvert' });
                    if (code && code.data) {
                        onDecodeSuccess(code.data);
                        return;
                    }
                } catch (e) {}
                if (scannerRunning) scanLoopTimer = setTimeout(scanFrame, 150);
            }
            scanLoopTimer = setTimeout(scanFrame, 600);
        }

        window.retryCamera = function() {
            stopCamera();
            startCamera();
        };

        function startCamera() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showStatus('Camera not available. Please use Chrome or Edge.');
                return;
            }

            function doStart() {
                showStatus('Requesting camera access...');
                var isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                listVideoInputs().then(function(devices) {
                    var attempts = buildCameraAttempts(isMobile, devices);
                    var seenFailures = {};

                    function finishFailure() {
                        if (seenFailures.security) {
                            showStatus('Camera access requires HTTPS or localhost. Open this app on localhost or HTTPS and try again.');
                            return;
                        }
                        if (seenFailures.notfound) {
                            showStatus('No available camera was found on this device. Allow camera access, then reopen the scanner to try another camera.');
                            return;
                        }
                        showStatus('Camera could not start. Close any app using your webcam (Zoom, Teams, OBS), then reload the page.');
                    }

                    function tryNext(idx) {
                        if (idx >= attempts.length) {
                            finishFailure();
                            return;
                        }
                        getCameraStream(attempts[idx])
                            .then(function(stream) {
                                activeStream = stream;
                                return attachPreview(stream);
                            })
                            .then(function() {
                                scannerRunning = true;
                                showStatus('Camera live. Point it at a QR code.');
                                startDetectLoop();
                            })
                            .catch(function(err) {
                                var kind = normalizeCameraError(err);
                                seenFailures[kind] = true;
                                if (kind === 'denied') { showPermissionDenied(); return; }
                                if (kind === 'busy') { showStatus('Camera is in use by another app. Close Zoom, Teams, or OBS and retry.'); return; }
                                tryNext(idx + 1);
                            });
                    }

                    tryNext(0);
                });
            }

            doStart();
        }

        function stopCamera() {
            scannerRunning = false;
            if (scanLoopTimer) {
                clearTimeout(scanLoopTimer);
                scanLoopTimer = null;
            }
            if (activeStream) {
                activeStream.getTracks().forEach(function(track) { track.stop(); });
                activeStream = null;
            }
            if (html5QrCode) {
                try { html5QrCode.stop(); } catch (e) {}
                try { html5QrCode.clear(); } catch (e2) {}
            }
            clearReader();
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isScannerOpen()) {
                e.preventDefault();
                window.closeScanner();
                return;
            }

            if (!isScannerOpen()) return;
            if (e.ctrlKey || e.altKey || e.metaKey) return;

            if (e.key === 'Enter') {
                if (scanBuffer.length) {
                    var payload = scanBuffer;
                    scanBuffer = '';
                    if (scanTimer) {
                        clearTimeout(scanTimer);
                        scanTimer = null;
                    }
                    processScannedText(payload);
                }
                return;
            }

            if (e.key.length === 1) {
                scanBuffer += e.key;
                if (scanTimer) clearTimeout(scanTimer);
                scanTimer = setTimeout(function(){
                    if (scanBuffer.length >= 6) processScannedText(scanBuffer);
                    scanBuffer = '';
                    scanTimer = null;
                }, SCAN_IDLE_MS);
            }
        });
    })();
    </script>
    @include('partials.submission-notifications-script')
</body>
</html>
