<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Representative Dashboard - DepEd DTS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root{--primary:#0056b3;--primary-dark:#004494;--blue-soft:#eff6ff;--slate-dark:#334155;--bg:#f0f2f5;--border:#e2e8f0;--text-dark:#1b263b;--text-muted:#64748b}
        *{margin:0;padding:0;box-sizing:border-box}
        body{background:var(--bg);font-family:Poppins,sans-serif;min-height:100vh;display:flex;flex-direction:column}
        /* Sidebar */
        .sidebar{position:fixed;top:0;left:0;width:240px;height:100vh;background:#0056b3;display:flex;flex-direction:column;z-index:200;transform:translateX(-100%);transition:transform .25s ease}
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
        .btn-logout{display:flex;align-items:center;gap:7px;margin-top:8px;padding:8px 14px;background:rgba(255,255,255,.1);border:none;border-radius:8px;color:rgba(255,255,255,.8);font-size:12px;cursor:pointer;font-family:Poppins,sans-serif;width:100%;justify-content:center;transition:background .2s}
        .btn-logout:hover{background:rgba(255,255,255,.2)}
        /* Main */
        .main{margin-left:0;padding:60px 32px 60px;flex:1}
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
        /* Receive strip */
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
        /* Stats */
        .stats-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;margin-bottom:24px}
        .stat-card{background:#fff;border-radius:12px;padding:20px 22px 18px;box-shadow:none;border:1px solid var(--border);position:relative;overflow:hidden}
        .stat-label{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.8px;margin-bottom:12px}
        .stat-card.c-blue .stat-label{background:#eff6ff;color:#2563eb}
        .stat-card.c-green .stat-label{background:#fffbeb;color:#d97706}
        .stat-card.c-amber .stat-label{background:#fffbeb;color:#d97706}
        .stat-card.c-emerald .stat-label{background:#f1f5f9;color:#64748b}
        .stat-num{font-size:32px;font-weight:800;color:var(--text-dark);line-height:1;letter-spacing:-1px}
        .stat-sub{font-size:11px;color:var(--text-muted);margin-top:6px}
        /* Table card */
        .table-card{background:#fff;border-radius:12px;border:1px solid var(--border);overflow:hidden}
        .dashboard-table-card.has-list{display:flex;flex-direction:column;max-height:clamp(520px,72vh,820px)}
        .dashboard-table-card.has-list .table-card-scroll{display:block;flex:1;min-height:0;overflow:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch}
        .dashboard-table-card.has-list .table-card-scroll thead th{position:sticky;top:0;z-index:2}
        .table-head{padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
        .table-title{font-size:17px;font-weight:700;color:var(--text-dark)}
        .table-doc-count{font-size:11px;color:#94a3b8;font-weight:500;margin-left:8px}
        .doc-update-flash{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:#16a34a;background:#f0fdf4;border:1px solid #bbf7d0;padding:3px 10px;border-radius:20px;opacity:0;transform:translateY(-4px);transition:opacity .4s ease,transform .4s ease;pointer-events:none}
        .doc-update-flash.show{opacity:1;transform:translateY(0)}
        .filters{display:flex;gap:8px;align-items:center;flex-wrap:nowrap}
        .search-wrap{position:relative;display:flex;align-items:center;flex:1;min-width:0}
        .search-wrap i{position:absolute;left:10px;color:#94a3b8;font-size:12px;pointer-events:none;z-index:1}
        .filters input{padding:7px 10px 7px 30px;font-family:Poppins,sans-serif;font-size:12.5px;border:1.5px solid var(--border);border-radius:7px;outline:none;transition:border-color .2s,box-shadow .2s;width:100%;color:var(--text-dark);background:#fff}
        .filters input::placeholder{color:#94a3b8;font-size:12px}
        .filters input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,86,179,.09)}
        .filters select{padding:7px 28px 7px 10px;font-family:Poppins,sans-serif;font-size:12.5px;border:1.5px solid var(--border);border-radius:7px;outline:none;transition:border-color .2s;color:var(--text-dark);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%2394a3b8' d='M5 7L0 2h10z'/%3E%3C/svg%3E") no-repeat right 10px center;-webkit-appearance:none;appearance:none;cursor:pointer;min-width:140px}
        .filters select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,86,179,.09)}
        table{width:100%;border-collapse:collapse}
        th{text-align:left;padding:10px 18px;font-size:10.5px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:#94a3b8;border-bottom:1px solid var(--border);background:#fff}
        td{padding:13px 18px;font-size:13px;color:var(--text-dark);border-bottom:1px solid #f1f5f9;vertical-align:middle}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#f8faff}
        tr.doc-row{cursor:pointer}
        .badge{display:inline-flex;align-items:center;justify-content:center;min-height:22px;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;line-height:1;text-align:center;white-space:nowrap;vertical-align:middle}
        .badge-submitted,
        .badge-received,
        .badge-in_review,
        .badge-forwarded,
        .badge-completed,
        .badge-for_pickup,
        .badge-returned,
        .badge-cancelled{background:#fff7ed;color:#c2410c}
        .btn-accept{padding:5px 11px;background:#16a34a;color:#fff;border:none;border-radius:7px;font-size:11px;font-weight:600;cursor:pointer;font-family:Poppins,sans-serif;transition:background .2s;display:inline-flex;align-items:center;gap:5px}
        .btn-accept:hover{background:#15803d}
        .td-action{width:44px;text-align:center}
        .row-arrow{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:7px;color:#94a3b8;transition:all .15s}
        tr.doc-row:hover .row-arrow{background:var(--primary);color:#fff}
        .cell-ellipsis{display:block;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .queue-panel .table-head{padding:14px 20px;border-bottom:1px solid var(--border);gap:12px;flex-wrap:wrap}
        .queue-panel .table-head-left{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
        .queue-panel table{width:100%;table-layout:fixed}
        .queue-panel .table-card-scroll{overflow-y:auto;overflow-x:hidden;scrollbar-gutter:stable}
        .queue-panel th{padding:10px 10px;background:#fff}
        .queue-panel td{padding:10px 10px;border-bottom:1px solid #f1f5f9}
        .queue-panel tr:last-child td{border-bottom:none}
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
        .queue-panel .td-cta{white-space:nowrap}
        .queue-panel .td-cta .btn-accept{width:100%;justify-content:center;padding:8px 10px;border-radius:8px}
        .mob-cards{display:none;padding:12px}
        .mob-card{background:#fff;border:1px solid var(--border);border-radius:12px;padding:12px;box-shadow:0 1px 4px rgba(0,0,0,.04);cursor:pointer;transition:border-color .15s,box-shadow .15s}
        .mob-card + .mob-card{margin-top:10px}
        .mob-card:hover{border-color:var(--primary);box-shadow:0 2px 8px rgba(0,86,179,.08)}
        .mob-card-top{display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:6px}
        .mob-card-ids{min-width:0}
        .mob-card-ref{font-size:11.5px;font-weight:700;color:var(--primary);font-family:monospace;line-height:1.25}
        .mob-card-track{font-size:10px;color:var(--text-muted);font-family:monospace;margin-top:2px;line-height:1.25}
        .mob-card-arrow{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:6px;color:#94a3b8;font-size:11px;flex-shrink:0;background:#f8fafc}
        .mob-card-subject{font-size:13.5px;font-weight:600;color:var(--text-dark);margin-bottom:8px;line-height:1.3;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .mob-card-meta{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px}
        .mob-card-status{display:inline-flex;align-items:center;gap:5px;min-width:0}
        .mob-card-date{font-size:10.5px;color:var(--text-muted);display:inline-flex;align-items:center;gap:4px;white-space:nowrap}
        .mob-card-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
        .mob-card-item{min-width:0;background:#f8fafc;border:1px solid #e8eef6;border-radius:10px;padding:8px 9px}
        .mob-card-item.full{grid-column:1 / -1}
        .mob-card-k{display:block;font-size:8.8px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:4px}
        .mob-card-v{display:block;min-width:0;font-size:10.8px;font-weight:500;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.3}
        .mob-card-actions{display:flex;gap:8px;margin-top:10px}
        .mob-card-actions .btn-accept{flex:1;justify-content:center;padding:8px 10px;border-radius:10px;font-size:11px}
        .empty-state{text-align:center;padding:50px 20px;color:var(--text-muted)}
        .empty-state i{font-size:40px;color:#cbd5e1;margin-bottom:12px;display:block}
        .empty-state h3{font-size:15px;font-weight:600;color:#94a3b8;margin-bottom:6px}
        .empty-state p{font-size:12px}
        #noResultRow{display:none}
        #noResultRow td{text-align:center;padding:40px;color:var(--text-muted);font-size:13px}
        /* Tracking Drawer (aligned with My Documents) */
        .drawer-overlay{position:fixed;inset:0;background:rgba(0,0,0,.35);z-index:400;opacity:0;pointer-events:none;transition:opacity .25s}
        .drawer-overlay.open{opacity:1;pointer-events:all}
        .drawer{position:fixed;top:0;right:0;height:100vh;width:460px;max-width:100vw;background:#fff;z-index:401;box-shadow:-4px 0 24px rgba(0,0,0,.12);display:flex;flex-direction:column;transform:translateX(100%);transition:transform .28s cubic-bezier(.4,0,.2,1)}
        .drawer.open{transform:translateX(0)}
        .drawer-head{padding:18px 22px;border-bottom:1px solid var(--border);display:flex;align-items:flex-start;gap:12px}
        .drawer-head-info{flex:1;min-width:0}
        .drawer-head h3{font-size:16px;font-weight:700;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:4px}
        .drawer-ref{font-size:13px;color:var(--text-muted);font-family:monospace;letter-spacing:.4px;margin-bottom:2px}
        .drawer-track{font-size:11px;color:var(--text-muted);font-family:monospace;letter-spacing:.4px;margin-bottom:4px}
        .drawer-close{width:32px;height:32px;border-radius:8px;border:1px solid var(--border);background:#f8fafc;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--text-muted);font-size:14px;flex-shrink:0;transition:all .15s}
        .drawer-close:hover{background:#fee2e2;color:#dc2626;border-color:#fca5a5}
        .drawer-body{flex:1;overflow-y:auto}
        .drawer-meta{display:grid;grid-template-columns:1fr 1fr;border-bottom:1px solid var(--border)}
        .dm-item{padding:14px 20px;border-right:1px solid #f1f5f9;border-bottom:1px solid #f1f5f9}
        .dm-item:nth-child(2n){border-right:none}
        .dm-label{font-size:11px;text-transform:uppercase;letter-spacing:.6px;color:#94a3b8;font-weight:600;margin-bottom:3px}
        .dm-value{font-size:14px;color:var(--text-dark);font-weight:500}
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
        .tl-dot.c-danger{background:#22c55e;box-shadow:0 0 0 2px #22c55e}
        .tl-dot.c-latest{background:#f59e0b;box-shadow:0 0 0 2px #f59e0b}
        .tl-action{font-size:12px;font-weight:700;color:#1b263b}
        .tl-meta{font-size:12px;color:#64748b;margin:2px 0}
        .tl-remarks{font-size:12px;color:#64748b;background:#f8fafc;border-left:3px solid var(--border);padding:5px 9px;border-radius:4px;margin-top:5px}
        .tl-office-hdr{display:flex;align-items:center;font-size:13px;font-weight:700;color:var(--text-dark);text-transform:none;letter-spacing:0;margin:18px 0 8px -7px;padding-left:7px;padding-bottom:6px;position:relative}
        .tl-office-hdr::after{content:'';position:absolute;left:21px;right:0;bottom:0;height:1.5px;background:var(--border)}
        .tl-office-hdr:first-child{margin-top:0}
        .drawer-loader{display:flex;align-items:center;justify-content:center;padding:48px;flex-direction:column;gap:12px;color:var(--text-muted);font-size:13px}
        .spin{width:22px;height:22px;border:3px solid #e2e8f0;border-top-color:var(--primary);border-radius:50%;animation:spin .7s linear infinite}
        /* ─── Mobile sidebar ─── */
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

        @media(max-width:900px){
            .main{padding:68px 14px 50px}
            .stats-grid{grid-template-columns:1fr 1fr}
            .drawer{width:100%;max-width:100%}
            .drawer-meta{grid-template-columns:1fr}
            .dm-item{border-right:none}
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
            .stat-num{font-size:26px}
            .dashboard-table-card.has-list{max-height:min(68vh,560px)}
            /* Header */
            .page-header-top{flex-direction:column;align-items:flex-start;gap:10px}
            .live-clock{display:none}
            /* Table head filters */
            .table-head{flex-direction:column;align-items:stretch;gap:10px;padding:14px 16px}
            .filters{gap:6px}
            .filters input{font-size:11px;padding:6px 8px 6px 26px}
            .filters input::placeholder{font-size:10px}
            .filters select{font-size:11px;padding:6px 22px 6px 8px;min-width:100px}
            .dashboard-table-card.has-list .table-card-scroll{display:none!important}
            .dashboard-table-card.has-list .mob-cards{display:block!important;flex:1;min-height:0;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;padding:10px}
            .mob-cards{display:block;padding:10px}
            .table-card{border-radius:10px}
            .mob-card-actions .btn-accept{font-size:10.5px}
        }
        @media(max-width:1024px){
            .dashboard-table-card.has-list .table-card-scroll{display:none!important}
            .dashboard-table-card.has-list .mob-cards{display:block!important;flex:1;min-height:0;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch}
        }
        @media(max-width:420px){
            .mob-card-grid{grid-template-columns:1fr}
            .mob-card-item.full{grid-column:auto}
            .mob-card-actions{flex-direction:column}
        }
        @keyframes spin{to{transform:rotate(360deg)}}
        @keyframes blink-pulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.4);opacity:.35}}
        .blink-dot{display:inline-block;width:9px;height:9px;border-radius:50%;background:#ea580c;animation:blink-pulse 1.2s ease-in-out infinite;vertical-align:middle;flex-shrink:0}
        .pickup-alert strong{color:#9a3412}
        .spinner{display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite}
        .site-footer{margin-left:0;width:100%;background:#fff;border-top:1px solid #e2e8f0;padding:20px 28px;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8}
        .site-footer .footer-left{display:flex;align-items:center;gap:6px}
        .site-footer .footer-right{font-size:11px;color:#b0b8c4}

        /* ─── Accept Modal ─── */
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:300;align-items:center;justify-content:center;padding:16px}
        .modal-overlay.show{display:flex}
        .modal{background:#fff;border-radius:16px;max-width:420px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:modalIn .18s ease}
        @keyframes modalIn{from{opacity:0;transform:scale(.96)}to{opacity:1;transform:scale(1)}}
        .modal-head{padding:22px 24px 12px;display:flex;align-items:center;gap:12px}
        .modal-icon{width:42px;height:42px;border-radius:12px;background:#dcfce7;display:flex;align-items:center;justify-content:center;color:#16a34a;font-size:18px;flex-shrink:0}
        .modal-head h3{font-size:16px;font-weight:700;color:var(--text-dark)}
        .modal-body{padding:0 24px 20px;font-size:13px;color:var(--text-muted);line-height:1.7}
        .modal-foot{padding:16px 24px;border-top:1px solid var(--border);display:flex;gap:10px;justify-content:flex-end}
        .modal-btn{padding:9px 18px;border-radius:10px;font-size:13px;font-weight:600;font-family:Poppins,sans-serif;cursor:pointer;border:1.5px solid var(--border);background:#fff;color:var(--text-dark);transition:all .2s}
        .modal-btn:hover{background:#f1f5f9}
        .modal-btn.success{background:#16a34a;color:#fff;border-color:#16a34a}
        .modal-btn.success:hover{background:#15803d}

        /* ─── QR Scanner Modal ─── */
        .scanner-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:400;align-items:center;justify-content:center;padding:16px}
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
    </style>
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
</head>
<body>
@php
    $user = auth()->user();
    $isRep = $user->account_type === 'representative';
    $navOfficeName = $isRep ? ($user->representativeOfficeName() ?? 'Office') : null;
    $navRepName = $isRep ? $user->representativeDisplayName() : $user->name;
    $navDisplayName = $navOfficeName ?? $navRepName;
    $initials = collect(explode(' ', trim($navRepName ?: $user->name)))->filter()->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->implode('');
    $pickupCount = $stats['for_pickup'] ?? 0;
@endphp

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
        <span class="nav-section">Office</span>
        <a href="/office/dashboard" class="active">
            <i class="fas fa-tachometer-alt"></i> Dashboard
            @if($pickupCount > 0)
                <span style="flex:1"></span>
                <span class="blink-dot" style="width:8px;height:8px;background:#fca311;border:1.5px solid rgba(255,255,255,.45)" title="{{ $pickupCount }} document(s) awaiting pickup confirmation"></span>
            @endif
        </a>
        <a href="/office/search" id="reports-nav-link" style="{{ $user->hasReportsAccess() ? '' : 'display:none' }}"><i class="fas fa-chart-line"></i> Reports</a>
        @if($user->isRecords() || $user->isSuperAdmin())
        <span class="nav-section">Records Section</span>
        <a href="/records/documents"><i class="fas fa-folder-open"></i> All Documents</a>
        @endif
        <span class="nav-section">My Documents</span>
        <a href="/submit"><i class="fas fa-paper-plane"></i> Submit Document</a>
        <a href="/my-documents"><i class="fas fa-folder"></i> My Documents</a>
        <span class="nav-section">Account</span>
        <a href="/profile"><i class="fas fa-user-circle"></i> My Profile</a>
    </nav>
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">{{ $initials }}</div>
            <div class="sb-user-info">
                <small>{{ $navOfficeName ?? 'Office' }}</small>
                <span>{{ $navRepName ?? $navDisplayName }}</span>
            </div>
        </div>
        <button onclick="logout()" class="btn-logout" style="margin-top:8px"><i class="fas fa-sign-out-alt"></i> Logout</button>
    </div>
</div>

<!-- Main content -->
<div class="main">
    <div class="page-header">
        <div class="page-header-top">
            <div>
                <h1>Welcome back, {{ $navRepName ?? $navDisplayName }}!</h1>
                <p>{{ $navOfficeName ?? 'Office' }} &mdash; here's your document queue.</p>
            </div>
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
        </div>
    </div>

    <div class="receive-strip">
        <h2>Receive Document</h2>
        <p class="rs-sub">Enter the 8-character reference number</p>
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
            <button class="btn-receive" id="receiveRefBtn" onclick="receiveByReference()"><i class="fas fa-check"></i> Receive</button>
            <button class="btn-scan-qr" id="scanQrBtn" onclick="openScanner()"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M4 8v-2a2 2 0 0 1 2 -2h2"/><path d="M4 16v2a2 2 0 0 0 2 2h2"/><path d="M16 4h2a2 2 0 0 1 2 2v2"/><path d="M16 20h2a2 2 0 0 0 2 -2v-2"/><path d="M11 12h6"/><path d="M8 8h5"/><path d="M9 16h5"/></svg> Scan Document</button>
        </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card c-amber">
            <div class="stat-label">Processing</div>
            <div class="stat-num" id="stat-in-review">{{ \App\Support\UiNumber::compact($stats['in_review']) }}</div>
            <div class="stat-sub">Being processed</div>
        </div>
        <div class="stat-card c-emerald">
            <div class="stat-label">Completed</div>
            <div class="stat-num" id="stat-completed">{{ \App\Support\UiNumber::compact($stats['completed']) }}</div>
            <div class="stat-sub">Completed and closed</div>
        </div>
    </div>

    <!-- Documents table -->
    <div class="table-card dashboard-table-card queue-panel{{ $documents->isNotEmpty() ? ' has-list' : '' }}">
        <div class="table-head">
            <div class="table-head-left">
                <span class="table-title">Document Queue</span>
                <span class="table-doc-count">{{ \App\Support\UiNumber::compact($documents->count()) }} showing</span>
                <span class="doc-update-flash" id="docUpdateFlash">List updated</span>
            </div>
            <div class="filters">
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Search subject or sender..." data-clearable data-no-capitalize oninput="filterTable()">
                </div>
                <select id="statusFilter" onchange="filterTable()">
                    <option value="">All Statuses</option>
                    <option value="submitted">Submitted</option>
                    <option value="in_review">Processing</option>
                    <option value="for_pickup">For Pickup</option>
                </select>
            </div>
        </div>

        @if($documents->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Documents Yet</h3>
                <p>Documents submitted to or currently held by your office will appear here.</p>
            </div>
        @else
            <div class="table-scroll table-card-scroll">
            <table id="docsTable">
                <colgroup>
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
                        <th>Reference #</th>
                        <th>Tracking #</th>
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
                        $docLookup = $doc->tracking_number ?: $doc->reference_number;
                    @endphp
                    <tr class="doc-row" onclick='openDocDetail(@json($docLookup))' data-status="{{ $doc->status }}" data-search="{{ strtolower(($docLookup ?: '') . ' ' . ($doc->reference_number ?? '') . ' ' . $doc->subject . ' ' . $doc->type . ' ' . $doc->sender_name) }}">
                        <td class="t-ref"><div class="cell-ellipsis" title="{{ $doc->reference_number ?: 'N/A' }}">{{ $doc->reference_number ?: 'N/A' }}</div></td>
                        <td class="t-track"><div class="cell-ellipsis" title="{{ $doc->tracking_number ?: ($doc->reference_number ?: 'N/A') }}">{{ $doc->tracking_number ?: ($doc->reference_number ?: 'N/A') }}</div></td>
                        <td class="t-subject" style="max-width:200px">
                            <div class="cell-ellipsis" style="font-weight:600" title="{{ $doc->subject }}">{{ $doc->subject }}</div>
                        </td>
                        <td class="t-submitted">
                            <div class="cell-ellipsis submission-person" title="{{ $doc->sender_name }}">{{ $doc->sender_name }}</div>
                            <div class="submission-date"><i class="fas fa-calendar-alt"></i>{{ $doc->created_at->format('M d, Y') }}</div>
                        </td>
                        <td class="t-status">
                            <span style="display:inline-flex;align-items:center;gap:5px">
                                <span class="badge badge-{{ $doc->status }}">{{ $doc->statusLabel() }}</span>
                                @if($doc->status === 'for_pickup')
                                    <span class="blink-dot"></span>
                                @endif
                            </span>
                        </td>
                        <td class="td-cta">
                            @if($doc->status === 'submitted')
                                <button class="btn-accept" onclick="event.stopPropagation(); quickAccept({{ $doc->id }}, this)">
                                    <i class="fas fa-check"></i> Accept
                                </button>
                            @endif
                        </td>
                        <td class="td-action"><span class="row-arrow"><i class="fas fa-chevron-right"></i></span></td>
                    </tr>
                @endforeach
                <tr id="noResultRow">
                    <td colspan="7"><i class="fas fa-search" style="margin-right:6px;opacity:.4"></i>No documents match your search or status filter.</td>
                </tr>
                </tbody>
            </table>
            </div>
            <div class="mob-cards">
                @foreach($documents as $doc)
                    @php
                        $docLookup = $doc->tracking_number ?: $doc->reference_number;
                    @endphp
                    <div
                        class="mob-card"
                        onclick='openDocDetail(@json($docLookup))'
                        data-status="{{ $doc->status }}"
                        data-search="{{ strtolower(($docLookup ?: '') . ' ' . ($doc->reference_number ?? '') . ' ' . $doc->subject . ' ' . $doc->type . ' ' . $doc->sender_name) }}"
                    >
                        <div class="mob-card-top">
                            <div class="mob-card-ids">
                                <div class="mob-card-ref">{{ $doc->reference_number ?: 'N/A' }}</div>
                                <div class="mob-card-track">Tracking: {{ $doc->tracking_number ?: ($doc->reference_number ?: 'N/A') }}</div>
                            </div>
                            <span class="mob-card-arrow"><i class="fas fa-chevron-right"></i></span>
                        </div>
                        <div class="mob-card-subject">{{ $doc->subject }}</div>
                        <div class="mob-card-meta">
                            <span class="mob-card-status">
                                <span class="badge badge-{{ $doc->status }}">{{ $doc->statusLabel() }}</span>
                                @if($doc->status === 'for_pickup')
                                    <span class="blink-dot"></span>
                                @endif
                            </span>
                            <span class="mob-card-date"><i class="fas fa-calendar"></i>{{ $doc->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="mob-card-row">
                            <i class="fas fa-user"></i>
                            <span class="cell-ellipsis" title="{{ $doc->sender_name }}">{{ $doc->sender_name }}</span>
                        </div>
                        @if($doc->status === 'submitted')
                        <div class="mob-card-actions">
                            <button class="btn-accept" onclick="event.stopPropagation(); quickAccept({{ $doc->id }}, this)">
                                <i class="fas fa-check"></i> Accept
                            </button>
                        </div>
                        @endif
                    </div>
                @endforeach
                <div class="empty-state" id="noResultMobile" style="display:none">
                    <i class="fas fa-search"></i>
                    <h3>No Results Found</h3>
                    <p>No documents match your search or status filter.</p>
                </div>
            </div>
            @include('partials.shared-pagination', [
                'paginator' => $documents,
                'itemLabel' => 'documents',
            ])
        @endif
    </div>
</div>

<!-- Tracking Drawer -->
<div class="drawer-overlay" id="drawerOverlay" onclick="closeDrawer()"></div>
<div class="drawer" id="docDrawer">
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

@php
    $docDrawerData = [];
    foreach ($documents as $doc) {
        $fallback = [
            'reference_number' => $doc->reference_number ?: $doc->tracking_number,
            'tracking_number' => $doc->tracking_number ?: $doc->reference_number,
            'subject' => $doc->subject,
            'type' => $doc->type,
            'status' => $doc->status,
            'status_label' => $doc->statusLabel(),
            'sender_name' => $doc->sender_name,
            'submitted_to_office' => optional($doc->submittedToOffice)->name,
            'current_office' => optional($doc->currentOffice)->name,
            'current_handler' => optional($doc->currentHandler)->name,
            'date' => optional($doc->created_at)->format('M d, Y'),
        ];
        $primaryKey = $doc->tracking_number ?: $doc->reference_number;
        if ($primaryKey) {
            $docDrawerData[$primaryKey] = $fallback;
        }
        if ($doc->reference_number && $doc->reference_number !== $doc->tracking_number) {
            $docDrawerData[$doc->reference_number] = $fallback;
        }
    }
@endphp
<script type="application/json" id="docsData">@json($docDrawerData)</script>

<script>
var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var docsData = JSON.parse(document.getElementById('docsData').textContent || '{}');

function filterTable(){
    var q      = document.getElementById('searchInput').value.toLowerCase().trim();
    var status = document.getElementById('statusFilter').value;
    var rows = document.querySelectorAll('#docsTable tbody tr.doc-row');
    var cards = document.querySelectorAll('.mob-cards .mob-card');
    var shown = 0;

    rows.forEach(function(item){
        var search = (item.dataset.search || '').toLowerCase();
        var matchSearch = !q || search.includes(q);
        var matchStatus = !status || item.dataset.status === status;
        var visible = matchSearch && matchStatus;
        item.style.display = visible ? '' : 'none';
        if (visible) shown++;
    });

    cards.forEach(function(item){
        var search = (item.dataset.search || '').toLowerCase();
        var matchSearch = !q || search.includes(q);
        var matchStatus = !status || item.dataset.status === status;
        item.style.display = (matchSearch && matchStatus) ? '' : 'none';
    });

    var noResult = document.getElementById('noResultRow');
    if (noResult) noResult.style.display = (shown === 0 && rows.length > 0) ? 'table-row' : 'none';

    var noResultMobile = document.getElementById('noResultMobile');
    if (noResultMobile) noResultMobile.style.display = (shown === 0 && cards.length > 0) ? 'block' : 'none';
}

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
        showReceiveMsg('Reference number is required.', 'err');
        return false;
    }

    showReceiveMsg(pendingMessage || 'Receiving document...', '');
    if(receiveBtn) receiveBtn.disabled = true;
    if(scanBtn) scanBtn.disabled = true;

    try{
        var res = await fetch('/api/office/documents/receive-by-reference', {
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
        showReceiveMsg('Please enter all 8 characters of the reference number.', 'err');
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

var _pendingAcceptId = null, _pendingAcceptBtn = null;
function quickAccept(docId, btn){
    _pendingAcceptId = docId;
    _pendingAcceptBtn = btn;
    document.getElementById('acceptModal').classList.add('show');
}
function closeAcceptModal(){
    document.getElementById('acceptModal').classList.remove('show');
    _pendingAcceptId = null;
    _pendingAcceptBtn = null;
}
async function confirmAccept(){
    var btn = document.getElementById('confirmAcceptBtn');
    btn.disabled = true;
    try{
        var res = await fetch('/api/office/documents/'+_pendingAcceptId+'/accept',{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf},
            body:'{}'
        });
        var data = await res.json();
        if(data.success){ location.reload(); }
        else{
            alert(data.message||'Failed');
            btn.disabled = false;
            closeAcceptModal();
        }
    }catch(e){
        alert('Error. Please try again.');
        btn.disabled = false;
        closeAcceptModal();
    }
}
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
function logout(){
    var csrf=document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    fetch('/api/logout',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}})
        .then(function(){window.location.href='/login';})
        .catch(function(){window.location.href='/login';});
}

function escapeHtml(value){
    return String(value === null || value === undefined ? '' : value)
        .replace(/&/g,'&amp;')
        .replace(/</g,'&lt;')
        .replace(/>/g,'&gt;')
        .replace(/"/g,'&quot;')
        .replace(/'/g,'&#39;');
}
function openDocDetail(ref){
    ref = (ref || '').toString().trim();
    document.getElementById('drTitle').textContent='—';
    document.getElementById('drRef').textContent=ref;
    document.getElementById('drTrack').textContent='';
    document.getElementById('drawerBody').innerHTML='<div class="drawer-loader"><span class="loading-dots"><span></span></span>Loading details...</div>';
    document.getElementById('drawerOverlay').classList.add('open');
    document.getElementById('docDrawer').classList.add('open');
    document.body.style.overflow='hidden';

    window.docTraxFetchJson('/api/track-document',{
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'},
        timeoutMs: 15000,
        body:JSON.stringify({
            reference_number: ref,
            tracking_number: ref
        })
    })
    .then(function(data){
        if(!data.success || !data.document){
            document.getElementById('drawerBody').innerHTML='<div class="drawer-loader">Document not found.</div>';
            return;
        }
        renderDrawer(data.document);
    })
    .catch(function(error){
        var fallback = docsData[ref];
        if (fallback) {
            renderDrawer({
                subject: fallback.subject || '-',
                reference_number: fallback.reference_number || ref,
                tracking_number: fallback.tracking_number || ref,
                status: fallback.status || 'unknown',
                status_label: fallback.status_label || 'Unknown',
                sender_name: fallback.sender_name || '-',
                type: fallback.type || '-',
                submitted_to_office: fallback.submitted_to_office || '-',
                current_office: fallback.current_office || '-',
                current_handler: fallback.current_handler || 'Unassigned',
                date: fallback.date || '-',
                routing_logs: []
            });
            window.showNetworkNotice('Showing basic document details from the current list while the live request is unavailable.', {
                type: 'warning',
                duration: 5000
            });
            return;
        }
        document.getElementById('drawerBody').innerHTML =
            '<div class="drawer-loader">' + escapeHtml(window.describeRequestError(error, 'Could not load tracking details. Please try again.')) + '</div>';
    });
}
function closeDrawer(){
    document.getElementById('drawerOverlay').classList.remove('open');
    document.getElementById('docDrawer').classList.remove('open');
    document.body.style.overflow='';
}
function dotClass(s){
    if(s==='cancelled' || s==='returned') return 'c-danger';
    if(s==='completed') return 'c-done';
    if(s==='forwarded') return 'c-warn';
    return 'c-active';
}
function renderDrawer(doc){
    var ref = doc.reference_number || doc.tracking_number || '-';
    var trackingNo = doc.tracking_number || '';
    document.getElementById('drTitle').textContent = doc.subject || '-';
    document.getElementById('drRef').textContent = 'TN · ' + ref;
    document.getElementById('drTrack').textContent = (trackingNo && trackingNo !== ref) ? ('Ref · ' + trackingNo) : '';

    var logs = Array.isArray(doc.routing_logs) ? doc.routing_logs : [];
    var tlHtml = '';
    if (!logs.length) {
        tlHtml = '<div style="color:var(--text-muted);font-size:13px;padding:4px 0">No routing history yet.</div>';
    } else {
        var prevGroupKey = null;
        logs.slice().reverse().forEach(function(log, idx) {
            var isLatest = idx === 0;
            var dc = isLatest ? 'c-latest' : dotClass(log.status_after);
            var dotIcon = isLatest ? 'fa-arrow-up' : 'fa-check';
            var groupKey = (log.action === 'submitted') ? '__pending__' :
                           (log.action === 'forwarded' ? (log.from_office || 'Unknown') :
                           (log.to_office || log.from_office || 'Unknown'));
            var groupLabel = (groupKey === '__pending__') ? 'Submitted — Pending Physical Submission' : groupKey;
            if (groupKey !== prevGroupKey) {
                prevGroupKey = groupKey;
                tlHtml += '<div class="tl-office-hdr"><div class="tl-dot ' + dc + '" style="margin-right:5px"><i class="fas ' + dotIcon + '" style="font-size:5px"></i></div><span>' + escapeHtml(groupLabel) + '</span></div>';
            }
            tlHtml += '<div class="tl-item">' +
                (log.performed_by ? '<div class="tl-action">' + escapeHtml(log.performed_by) + '</div>' : '') +
                '<div class="tl-meta"><i class="fas fa-clock" style="margin-right:3px;font-size:10px"></i>' + escapeHtml(log.timestamp || '-') + '</div>' +
                '<div class="tl-meta"><i class="fas fa-tasks" style="margin-right:3px;font-size:10px"></i>' + escapeHtml(log.action_label || 'Status Updated') + '</div>' +
                (log.remarks ? '<div class="tl-remarks">' + escapeHtml(log.remarks) + '</div>' : '') +
                '</div>';
        });
    }

    var currentOfficeText = (doc.status === 'submitted')
        ? ('Awaiting physical submission to ' + (doc.submitted_to_office || doc.current_office || 'Records Section'))
        : (doc.current_office || doc.submitted_to_office || '-');

    var currentHandlerText = doc.current_handler || 'Unassigned';

    document.getElementById('drawerBody').innerHTML =
        '<div class="drawer-tl-head"><i class="fas fa-history"></i> Routing History</div>' +
        '<div class="drawer-timeline"><div class="tl">' + tlHtml + '</div></div>';
}

document.addEventListener('keydown', function(e){
    if(e.key === 'Escape'){ closeDrawer(); }
});

// ─── Live stats + smart doc-table refresh (every 30s) ───
(function(){
    var prev = {in_review:null,completed:null};
    var flashTimer = null;

    function flash(){
        var el = document.getElementById('docUpdateFlash');
        if(!el) return;
        clearTimeout(flashTimer);
        el.classList.add('show');
        flashTimer = setTimeout(function(){ el.classList.remove('show'); }, 3000);
    }

    function refreshTable(){
        window.docTraxFetch(window.location.pathname + window.location.search, {
            headers:{'X-Requested-With':'XMLHttpRequest','Accept':'text/html'},
            timeoutMs: 12000
        })
            .then(function(r){
                if (!r.ok) {
                    throw window.createRequestError('server', 'Could not refresh the document list right now.');
                }
                return r.text();
            })
            .then(function(html){
                var parser  = new DOMParser();
                var newDoc  = parser.parseFromString(html, 'text/html');
                var newCard = newDoc.querySelector('.table-card');
                var curCard = document.querySelector('.table-card');
                var newDocsData = newDoc.getElementById('docsData');
                var curDocsData = document.getElementById('docsData');
                if(newCard && curCard){
                    curCard.replaceWith(newCard);
                    if (newDocsData && curDocsData) {
                        curDocsData.textContent = newDocsData.textContent;
                        docsData = JSON.parse(curDocsData.textContent || '{}');
                    }
                    if(typeof filterTable === 'function') filterTable();
                    flash();
                    window.clearStatusNotice('office-dashboard-table');
                }
            })
            .catch(function(){
                window.setStatusNotice('office-dashboard-table', 'Live document list updates are temporarily unavailable. Showing the last loaded list.', {
                    type: 'warning',
                    priority: 20
                });
            });
    }

    // Live stats (silent update every 30s)
    function refreshStats(){
        window.docTraxFetchJson('/api/office-stats',{
            headers:{'Accept':'application/json'},
            timeoutMs: 10000
        })
            .then(function(d){
                var compactCount = window.formatCompactCount || function(v) { return String(v); };
                document.getElementById('stat-in-review').textContent = compactCount(d.in_review);
                document.getElementById('stat-completed').textContent = compactCount(d.completed);
                // Toggle Reports sidebar link in real-time
                var rlink = document.getElementById('reports-nav-link');
                if (rlink) rlink.style.display = d.has_reports_access ? '' : 'none';
                // Only refresh table when counts have actually changed
                if(prev.in_review !== null && (
                    d.in_review !== prev.in_review ||
                    d.completed !== prev.completed
                )) refreshTable();
                prev = {in_review:d.in_review, completed:d.completed};
                window.clearStatusNotice('office-dashboard-stats');
            })
            .catch(function(){
                window.setStatusNotice('office-dashboard-stats', 'Live dashboard updates are temporarily unavailable. Showing the last known counts.', {
                    type: 'warning',
                    priority: 30
                });
            });
    }
    refreshStats(); // seed prev counts immediately on load
    if (window.smartInterval) { window.smartInterval(refreshStats, 30000); }
    else { setInterval(refreshStats, 30000); }
})();

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
                <div class="scanner-hint">Point your camera at the document's QR code to review it before receiving.</div>
                <div id="qr-reader"></div>
                <p class="camera-status" id="cameraStatus">Initializing camera...</p>
            </div>
        </div>
    </div>
    <script src="/js/html5-qrcode.min.js"></script>
    <script src="/js/jsqr.js"></script>
    <script>
    (function(){
        if (window.__docTraxOfficeScanner && typeof window.__docTraxOfficeScanner.cleanup === 'function') {
            try { window.__docTraxOfficeScanner.cleanup(); } catch (e) {}
        }

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
        var scannerDestroyed = false;

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
            window.location.assign('/receive/' + encodeURIComponent(lookup));
        }

        window.openScanner = function() {
            var overlay = scannerOverlay();
            if (!overlay) return;
            scannerDestroyed = false;
            overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
            scanCooldown = false;
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

        function handleScannerKeydown(e) {
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
        }

        function destroyScanner() {
            if (scannerDestroyed) return;
            scannerDestroyed = true;
            scanCooldown = false;
            scanBuffer = '';
            if (scanTimer) {
                clearTimeout(scanTimer);
                scanTimer = null;
            }
            stopCamera();
            var overlay = scannerOverlay();
            if (overlay) overlay.classList.remove('show');
            if (document.body) document.body.style.overflow = '';
            document.removeEventListener('keydown', handleScannerKeydown);
            window.removeEventListener('spa:before-swap', destroyScanner);
            window.removeEventListener('pagehide', destroyScanner);
            if (window.__docTraxOfficeScanner && window.__docTraxOfficeScanner.cleanup === destroyScanner) {
                window.__docTraxOfficeScanner = null;
            }
        }

        window.__docTraxOfficeScanner = { cleanup: destroyScanner };
        window.addEventListener('spa:before-swap', destroyScanner);
        window.addEventListener('pagehide', destroyScanner);
        document.addEventListener('keydown', handleScannerKeydown);
    })();
    </script>

    <!-- Accept Confirmation Modal -->
    <div class="modal-overlay" id="acceptModal" onclick="if(event.target===this)closeAcceptModal()">
        <div class="modal">
            <div class="modal-head">
                <div class="modal-icon"><i class="fas fa-check"></i></div>
                <h3>Accept Document</h3>
            </div>
            <div class="modal-body">
                <p>You are about to accept this document. This will confirm receipt at your office and begin <strong style="color:var(--text-dark)">Processing</strong>.</p>
            </div>
            <div class="modal-foot">
                <button class="modal-btn" onclick="closeAcceptModal()">Cancel</button>
                <button class="modal-btn success" id="confirmAcceptBtn" onclick="confirmAccept()">
                    <i class="fas fa-check"></i> Confirm Accept
                </button>
            </div>
        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-left">
            <span>&copy; {{ date('Y') }} DepEd Document Tracking System</span>
        </div>
        <div class="footer-right">
            Developed by Raymond Bautista
        </div>
    </footer>
</body>
</html>

