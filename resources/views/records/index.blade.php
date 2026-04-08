<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="{{ asset('images/DOCTRAXLOGO.svg') }}" type="image/svg+xml">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Records Section — All Documents</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root{--primary:#0056b3;--primary-dark:#004494;--bg:#f0f2f5;--border:#e2e8f0;--text-dark:#1b263b;--text-muted:#64748b}
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
        /* Stats */
        .stats-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-bottom:28px}
        .stat-card{background:#fff;border-radius:12px;padding:18px 20px 16px;border:1px solid var(--border);position:relative;overflow:hidden}
        .stat-label{display:inline-flex;align-items:center;padding:5px 10px;border-radius:999px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;margin-bottom:12px}
        .stat-card.c-blue .stat-label{background:#eff6ff;color:#2563eb}
        .stat-card.c-amber .stat-label{background:#fffbeb;color:#d97706}
        .stat-card.c-green .stat-label{background:#fffbeb;color:#d97706}
        .stat-card.c-emerald .stat-label{background:#f1f5f9;color:#64748b}
        .stat-card.c-slate .stat-label{background:#f1f5f9;color:#64748b}
        .stat-num{font-size:32px;font-weight:800;color:var(--text-dark);line-height:1;letter-spacing:-1px}
        .stat-sub{font-size:11px;color:var(--text-muted);margin-top:6px}
        /* Table card */
        .table-card{background:#fff;border-radius:12px;border:1px solid var(--border);overflow:hidden}
        .records-table-card.has-list{display:flex;flex-direction:column;max-height:clamp(520px,72vh,820px)}
        .table-head{padding:14px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
        .table-title{font-size:17px;font-weight:700;color:var(--text-dark)}
        .table-doc-count{font-size:12px;color:var(--text-muted);font-weight:400}
        .doc-update-flash{display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:600;color:#16a34a;background:#f0fdf4;border:1px solid #bbf7d0;padding:4px 11px;border-radius:20px;opacity:0;transform:translateY(-4px);transition:opacity .4s ease,transform .4s ease;pointer-events:none}
        .doc-update-flash.show{opacity:1;transform:translateY(0)}
        .filters{display:flex;gap:10px;align-items:center;flex-wrap:nowrap;flex:1 1 720px;justify-content:flex-end;min-width:min(100%,560px)}
        .search-wrap{position:relative;display:flex;align-items:center;flex:1 1 460px;min-width:0;max-width:580px}
        .search-wrap i{position:absolute;left:11px;color:#94a3b8;font-size:13px;pointer-events:none;z-index:1}
        .filters input{padding:8px 12px 8px 34px;font-family:Poppins,sans-serif;font-size:13px;border:1.5px solid var(--border);border-radius:9px;outline:none;transition:border-color .2s,box-shadow .2s;width:100%;color:var(--text-dark);background:#fff}
        .filters input::placeholder{color:#94a3b8;font-size:12px}
        .filters input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,86,179,.1)}
        .filters select{padding:8px 32px 8px 12px;font-family:Poppins,sans-serif;font-size:13px;border:1.5px solid var(--border);border-radius:9px;outline:none;transition:border-color .2s,box-shadow .2s;color:var(--text-dark);background:#fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='10' height='10' viewBox='0 0 10 10'%3E%3Cpath fill='%2394a3b8' d='M5 7L0 2h10z'/%3E%3C/svg%3E") no-repeat right 11px center;-webkit-appearance:none;appearance:none;cursor:pointer;min-width:150px}
        .filters select:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,86,179,.1)}
        .cell-ellipsis{display:block;max-width:100%;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        table{width:100%;border-collapse:collapse}
        th{text-align:left;padding:11px 16px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:var(--text-muted);border-bottom:1px solid var(--border);background:#f8fafc}
        td{padding:12px 16px;font-size:13px;color:var(--text-dark);border-bottom:1px solid #f1f5f9;vertical-align:middle}
        tr:last-child td{border-bottom:none}
        tr:hover td{background:#fafbff}
        tr.doc-row{cursor:pointer}
        .badge{padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.4px}
        .badge-submitted,
        .badge-received,
        .badge-in_review,
        .badge-forwarded,
        .badge-completed,
        .badge-for_pickup,
        .badge-returned,
        .badge-cancelled,
        .badge-archived{background:#fff7ed;color:#c2410c}
        .btn-view{padding:5px 13px;background:var(--primary);color:#fff;border:none;border-radius:7px;font-size:11px;font-weight:600;cursor:pointer;font-family:Poppins,sans-serif;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:background .2s}
        .btn-view:hover{background:var(--primary-dark)}
        .td-action{width:44px;text-align:center}
        .row-arrow{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:7px;color:#94a3b8;transition:all .15s}
        tr.doc-row:hover .row-arrow{background:var(--primary);color:#fff}
        .empty-state{text-align:center;padding:50px 20px;color:var(--text-muted)}
        .empty-state i{font-size:40px;color:#cbd5e1;margin-bottom:12px;display:block}
        .empty-state h3{font-size:15px;font-weight:600;color:#94a3b8;margin-bottom:6px}
        .empty-state p{font-size:12px}
        .pagination-wrap{padding:18px 22px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap;background:linear-gradient(180deg,#fff 0%,#f8fbff 100%)}
        .pagination-meta{display:flex;align-items:center;gap:10px;flex-wrap:wrap;min-width:0}
        .pagination-pill{display:inline-flex;align-items:center;white-space:nowrap}
        .pagination-pill-text{display:inline-flex;align-items:baseline;gap:5px;font-size:12px;line-height:1}
        .pagination-pill-label{color:var(--text-muted);font-weight:600}
        .pagination-pill-value{color:var(--text-dark);font-weight:700;letter-spacing:.01em}
        .pagination-pill-sep{color:#94a3b8;font-weight:700}
        .pagination-summary{font-size:12px;color:var(--text-muted);font-weight:500}
        .pagination-summary strong{color:var(--text-dark)}
        .pagination-actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;flex-wrap:wrap}
        .pagination-links{display:flex;align-items:center;justify-content:flex-end;gap:6px;flex-wrap:wrap}
        .page-link,.page-current,.page-disabled,.page-dots{display:inline-flex;align-items:center;justify-content:center;min-width:40px;height:40px;padding:0 12px;border-radius:12px;font-size:12px;font-weight:700;text-decoration:none;transition:all .18s ease}
        .page-link{border:1px solid #dbe5f0;color:#334155;background:#fff}
        .page-link:hover{border-color:var(--primary-dark);background:var(--primary-dark);color:#fff}
        .page-current{border:1px solid transparent;background:linear-gradient(135deg,#0f61c9 0%,#0056b3 100%);color:#fff}
        .page-disabled{border:1px dashed #dbe3ee;background:#f8fafc;color:#cbd5e1;cursor:not-allowed}
        .page-dots{min-width:auto;height:40px;padding:0 2px;border:none;background:none;color:#94a3b8}
        .page-nav{gap:7px;padding:0 14px}
        .page-nav i{font-size:10px}
        .pagination-jump{display:flex;align-items:center;gap:8px;padding:6px 8px 6px 10px;border:1px solid #dbe5f0;border-radius:12px;background:#fff}
        .pagination-jump label{font-size:11px;font-weight:700;color:var(--text-muted);white-space:nowrap}
        .pagination-jump input{width:58px;height:36px;padding:0 10px;border:1px solid var(--border);border-radius:10px;background:#f8fafc;color:var(--text-dark);font-family:Poppins,sans-serif;font-size:12px;font-weight:600;text-align:center;outline:none}
        .pagination-jump input:focus{border-color:var(--primary);background:#fff;box-shadow:0 0 0 3px rgba(0,86,179,.08)}
        .pagination-jump button{height:36px;padding:0 12px;border:none;border-radius:10px;background:var(--primary);color:#fff;font-family:Poppins,sans-serif;font-size:12px;font-weight:700;cursor:pointer;transition:background .15s}
        .pagination-jump button:hover{background:var(--primary-dark)}
        .records-table-card.has-list .table-card-scroll{display:block;flex:1;min-height:0;overflow:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch}
        .records-table-card.has-list .table-card-scroll thead th{position:sticky;top:0;z-index:2}
        .records-table-card.has-list .pagination-wrap{flex-shrink:0;border-top:1px solid #f1f5f9}
        .table-scroll{display:block}
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
        .mob-card-date{font-size:10.5px;color:var(--text-muted);display:inline-flex;align-items:center;gap:4px;white-space:nowrap}
        .mob-card-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}
        .mob-card-item{min-width:0;background:#f8fafc;border:1px solid #e8eef6;border-radius:10px;padding:8px 9px}
        .mob-card-item.full{grid-column:1 / -1}
        .mob-card-k{display:block;font-size:8.8px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:4px}
        .mob-card-v{display:block;min-width:0;font-size:10.8px;font-weight:500;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;line-height:1.3}
        .mob-card-alert{margin-top:8px;display:inline-flex;align-items:center;gap:5px;padding:5px 8px;border-radius:999px;background:#fef2f2;color:#dc2626;font-size:10px;font-weight:600}
        .mob-card-alert i{font-size:9px}
        /* Drawer */
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
        .tl-dot.c-warn{background:#f59e0b;box-shadow:0 0 0 2px #f59e0b}
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
        .spin{width:22px;height:22px;border:3px solid #e2e8f0;border-top-color:var(--primary);border-radius:50%;animation:spin .7s linear infinite}
        @keyframes spin{to{transform:rotate(360deg)}}
        /* Mobile */
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
        @media(max-width:1024px){
            .records-table-card.has-list .table-card-scroll{display:none!important}
            .records-table-card.has-list .mob-cards{display:block!important;flex:1;min-height:0;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch}
        }
        @media(max-width:900px){
            .main{padding:68px 12px 40px}
            .page-header-top{flex-direction:column;align-items:flex-start;gap:10px}
            .live-clock{display:none}
            .stats-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:8px;margin-bottom:16px}
            .stat-card{min-width:0;padding:11px 10px 10px;border-radius:10px}
            .stat-label{display:block;padding:0;margin-bottom:8px;background:none!important;border-radius:0;font-size:8.5px;line-height:1.2;letter-spacing:.06em}
            .stat-card.c-blue .stat-label{color:#2563eb!important}
            .stat-card.c-amber .stat-label,
            .stat-card.c-green .stat-label{color:#d97706!important}
            .stat-card.c-emerald .stat-label,
            .stat-card.c-slate .stat-label{color:#64748b!important}
            .stat-num{font-size:22px;letter-spacing:-.6px}
            .stat-sub{font-size:9.5px;margin-top:4px;line-height:1.2}
            .table-head{padding:11px 12px 10px;flex-direction:column;align-items:stretch;gap:8px}
            .table-title{font-size:15px}
            .table-doc-count{font-size:11px}
            .filters{gap:6px;flex-wrap:wrap;min-width:0;justify-content:stretch;flex-basis:auto}
            .search-wrap{flex:1 1 100%;max-width:none}
            .filters input{font-size:11px;padding:7px 9px 7px 28px}
            .filters input::placeholder{font-size:10px}
            .filters select{font-size:11px;padding:7px 24px 7px 8px;min-width:0;flex:1 1 100%}
            .records-table-card.has-list{max-height:min(68vh,560px)}
            .records-table-card.has-list .table-card-scroll{display:none!important}
            .records-table-card.has-list .mob-cards{display:block!important;flex:1;min-height:0;overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;padding:10px}
            .mob-cards{display:block;padding:10px}
            .table-card{border-radius:10px}
            .drawer{width:100%;max-width:100%}
            .drawer-meta{grid-template-columns:1fr}
            .dm-item{border-right:none}
            .pagination-wrap{flex-wrap:wrap;justify-content:center;padding:14px}
            .pagination-meta{justify-content:center;text-align:center}
            .pagination-actions{justify-content:center;width:100%}
            .pagination-links{justify-content:center}
            .pagination-jump{justify-content:center}
        }
        @media(max-width:420px){
            .page-link,.page-current,.page-disabled{min-width:36px;height:36px;padding:0 10px;border-radius:10px}
            .page-nav .nav-label{display:none}
            .pagination-jump{width:100%}
            .pagination-jump input{flex:1;max-width:72px}
            .records-table-card.has-list{max-height:min(64vh,520px)}
            .stats-grid{grid-template-columns:repeat(2,minmax(0,1fr))}
            .mob-card-grid{grid-template-columns:1fr}
            .mob-card-item.full{grid-column:auto}
        }
        .site-footer{margin-left:0;width:100%;background:#fff;border-top:1px solid #e2e8f0;padding:20px 28px;display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#94a3b8}
        .site-footer .footer-left{display:flex;align-items:center;gap:6px}
        .site-footer .footer-right{font-size:11px;color:#b0b8c4}
        @media(max-width:900px){.site-footer{padding:16px 5%;flex-direction:column;gap:6px;text-align:center}}
    </style>
    <script src="/js/spa.js" defer></script>
    <script src="/js/form-utils.js" defer></script>
    <script src="/js/request-utils.js" defer></script>
</head>
<body>
@php
    $initials = collect(explode(' ', trim($user->name)))->filter()->map(fn($w)=>strtoupper(substr($w,0,1)))->take(2)->implode('');
    $isSuperAdmin = $user->isSuperAdmin();
    $roleName = $isSuperAdmin ? 'Super Admin' : 'Records Section';
    $displayName = $user->name;
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

<!-- Sidebar -->
<div class="sidebar" id="mainSidebar">
    <div class="sb-brand">
        <img src="{{ asset('images/DOCTRAXLOGO.svg') }}" alt="DOCTRAX Logo">
        <h2>DOCTRAX</h2>
        <small>DepEd Document Tracking System</small>
    </div>
    <nav class="sb-nav">
        @if($isSuperAdmin)
        <span class="nav-section">Overview</span>
        <a href="/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <span class="nav-section">Management</span>
        <a href="/admin/users"><i class="fas fa-users"></i> Users</a>
        <a href="/admin/offices"><i class="fas fa-building"></i> Offices</a>
        <a href="/records/documents" class="active"><i class="fas fa-folder-open"></i> All Documents</a>
        <span class="nav-section">ICT Unit</span>
        <a href="/ict/documents"><i class="fas fa-network-wired"></i> ICT Documents</a>
        <a href="/office/search"><i class="fas fa-chart-line"></i> Reports</a>
        <span class="nav-section">My Documents</span>
        <a href="/submit"><i class="fas fa-paper-plane"></i> Submit Document</a>
        <a href="/my-documents"><i class="fas fa-folder"></i> My Documents</a>
        <a href="/track"><i class="fas fa-search"></i> Track Document</a>
        <span class="nav-section">Account</span>
        <a href="/profile"><i class="fas fa-user-cog"></i> My Profile</a>
        @else
        <span class="nav-section">Office</span>
        <a href="/office/dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="/office/search" id="reports-nav-link" style="{{ $user->hasReportsAccess() ? '' : 'display:none' }}"><i class="fas fa-chart-line"></i> Reports</a>
        <span class="nav-section">{{ $roleName }}</span>
        <a href="/records/documents" class="active">
            <i class="fas fa-folder-open"></i> All Documents
        </a>
        <span class="nav-section">My Documents</span>
        <a href="/submit"><i class="fas fa-paper-plane"></i> Submit Document</a>
        <a href="/my-documents"><i class="fas fa-folder"></i> My Documents</a>
        <a href="/track"><i class="fas fa-search"></i> Track Document</a>
        <span class="nav-section">Account</span>
        <a href="/profile"><i class="fas fa-user-cog"></i> My Profile</a>
        @endif
    </nav>
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">{{ $isSuperAdmin ? strtoupper(substr($user->name, 0, 1)) : $initials }}</div>
            <div class="sb-user-info">
                <small>{{ $roleName }}</small>
                <span>{{ $isSuperAdmin ? explode(' ', $user->name)[0] : $displayName }}</span>
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
                <h1>All Documents</h1>
                <p>View and monitor all documents in DOCTRAX &mdash; {{ $roleName }} Access</p>
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

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card c-blue">
            <div class="stat-label">Total Documents</div>
            <div class="stat-num" id="stat-total">{{ \App\Support\UiNumber::compact($stats['total']) }}</div>
            <div class="stat-sub">All time</div>
        </div>
        <div class="stat-card c-amber">
            <div class="stat-label">Awaiting Receipt</div>
            <div class="stat-num" id="stat-submitted">{{ \App\Support\UiNumber::compact($stats['submitted']) }}</div>
            <div class="stat-sub">Submitted</div>
        </div>
        <div class="stat-card c-green">
            <div class="stat-label">Processing</div>
            <div class="stat-num" id="stat-received">{{ \App\Support\UiNumber::compact($stats['received']) }}</div>
            <div class="stat-sub">In progress</div>
        </div>
        <div class="stat-card c-emerald">
            <div class="stat-label">Completed</div>
            <div class="stat-num" id="stat-completed">{{ \App\Support\UiNumber::compact($stats['completed']) }}</div>
            <div class="stat-sub">Closed out</div>
        </div>
        <div class="stat-card c-slate">
            <div class="stat-label">Archived</div>
            <div class="stat-num" id="stat-archived">{{ \App\Support\UiNumber::compact($stats['archived']) }}</div>
            <div class="stat-sub">Unprocessed</div>
        </div>
    </div>

    <!-- Documents table -->
    <div class="table-card records-table-card{{ $documents->isNotEmpty() ? ' has-list' : '' }}">
        <div class="table-head">
            <div style="display:flex;align-items:center;gap:8px">
                <span class="table-title">Documents</span>
                <span class="table-doc-count" id="docUpdateFlash"></span>
            </div>
            <form method="GET" action="/records/documents" class="filters" id="filterForm" data-live-search>
                <div class="search-wrap">
                    <i class="fas fa-search"></i>
                    <input
                        type="text"
                        id="documentsSearch"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search reference, subject, sender..."
                        data-clearable
                        data-no-capitalize
                    >
                </div>
                <select name="status" id="documentsStatus">
                    <option value="">All Statuses</option>
                    @foreach(\App\Models\Document::FILTER_STATUSES as $key => $label)
                        <option value="{{ $key }}" {{ $status === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>

        @if($documents->isEmpty())
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No Documents Found</h3>
                <p>No documents match your filters. Try adjusting your search or status filter.</p>
            </div>
        @else
            <div class="table-scroll table-card-scroll">
                <table id="docsTable">
                    <thead>
                        <tr>
                            <th>Tracking #</th>
                            <th>Reference #</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Sender</th>
                            <th>Current Office</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="td-action"></th>
                        </tr>
                    </thead>
                    <tbody>
                    @foreach($documents as $doc)
                        @php
                            $docLookup = $doc->tracking_number ?: $doc->reference_number;
                        @endphp
                        <tr class="doc-row" onclick='openDocDetail(@json($docLookup))'>
                            <td style="font-family:monospace;font-size:12px;font-weight:600;color:var(--primary);white-space:nowrap">{{ $doc->tracking_number }}</td>
                            <td style="font-family:monospace;font-size:12px;font-weight:600;white-space:nowrap">{{ $doc->reference_number ?: 'N/A' }}</td>
                            <td style="max-width:200px">
                                <div class="cell-ellipsis" style="font-weight:600" title="{{ $doc->subject }}">{{ $doc->subject }}</div>
                            </td>
                            <td style="font-size:12px;white-space:nowrap">
                                <div class="cell-ellipsis" style="max-width:160px" title="{{ $doc->type }}">{{ $doc->type }}</div>
                            </td>
                            <td style="font-size:12px">
                                <div class="cell-ellipsis" style="max-width:160px" title="{{ $doc->sender_name }}">{{ $doc->sender_name }}</div>
                            </td>
                            <td style="font-size:12px">
                                @if($doc->status === 'submitted')
                                    <span class="cell-ellipsis" style="max-width:170px;color:#d97706;font-style:italic" title="Awaiting receipt">Awaiting receipt</span>
                                @elseif($doc->status === 'archived')
                                    <span class="cell-ellipsis" style="max-width:170px;color:#9ca3af;font-style:italic" title="Unprocessed">Unprocessed</span>
                                @else
                                    <div class="cell-ellipsis" style="max-width:170px" title="{{ $doc->currentOffice->name ?? $doc->submittedToOffice->name ?? '-' }}">{{ $doc->currentOffice->name ?? $doc->submittedToOffice->name ?? '-' }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $doc->status }}">{{ $doc->statusLabel() }}</span>
                                @if($doc->status === 'submitted' && $doc->created_at->diffInDays(now()) >= 5)
                                    <div style="font-size:10px;color:#dc2626;margin-top:3px">
                                        <i class="fas fa-exclamation-circle"></i>
                                        @if($doc->created_at->diffInDays(now()) >= 7)
                                            Will archive soon
                                        @else
                                            {{ 7 - (int) $doc->created_at->diffInDays(now()) }} day(s) left
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td style="font-size:11px;color:var(--text-muted)">{{ $doc->created_at->format('M d, Y') }}</td>
                            <td class="td-action"><span class="row-arrow" aria-hidden="true"><i class="fas fa-chevron-right"></i></span></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mob-cards">
                @foreach($documents as $doc)
                    @php
                        $docLookup = $doc->tracking_number ?: $doc->reference_number;
                    @endphp
                    @php
                        $mobileOfficeText = $doc->status === 'submitted'
                            ? 'Awaiting receipt'
                            : ($doc->status === 'archived'
                                ? 'Unprocessed'
                                : ($doc->currentOffice->name ?? $doc->submittedToOffice->name ?? '-'));
                    @endphp
                    <div class="mob-card" onclick='openDocDetail(@json($docLookup))'>
                        <div class="mob-card-top">
                            <div class="mob-card-ids">
                                <div class="mob-card-ref">{{ $doc->tracking_number }}</div>
                                <div class="mob-card-track">Ref: {{ $doc->reference_number ?: 'N/A' }}</div>
                            </div>
                            <span class="mob-card-arrow"><i class="fas fa-chevron-right"></i></span>
                        </div>
                        <div class="mob-card-subject">{{ $doc->subject }}</div>
                        <div class="mob-card-meta">
                            <span class="badge badge-{{ $doc->status }}">{{ $doc->statusLabel() }}</span>
                            <span class="mob-card-date"><i class="fas fa-calendar"></i>{{ $doc->created_at->format('M d, Y') }}</span>
                        </div>
                        <div class="mob-card-grid">
                            <div class="mob-card-item">
                                <span class="mob-card-k">Type</span>
                                <span class="mob-card-v" title="{{ $doc->type }}">{{ $doc->type }}</span>
                            </div>
                            <div class="mob-card-item">
                                <span class="mob-card-k">Sender</span>
                                <span class="mob-card-v" title="{{ $doc->sender_name }}">{{ $doc->sender_name }}</span>
                            </div>
                            <div class="mob-card-item full">
                                <span class="mob-card-k">Current Office</span>
                                <span class="mob-card-v" title="{{ $mobileOfficeText }}">{{ $mobileOfficeText }}</span>
                            </div>
                        </div>
                        @if($doc->status === 'submitted' && $doc->created_at->diffInDays(now()) >= 5)
                            <div class="mob-card-alert">
                                <i class="fas fa-exclamation-circle"></i>
                                @if($doc->created_at->diffInDays(now()) >= 7)
                                    Will archive soon
                                @else
                                    {{ 7 - (int) $doc->created_at->diffInDays(now()) }} day(s) left
                                @endif
                            </div>
                        @endif
                    </div>
                @endforeach
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

<footer class="site-footer">
    <div class="footer-left">
        <span>&copy; {{ date('Y') }} DepEd Document Tracking System</span>
    </div>
    <div class="footer-right">
        Developed by Raymond Bautista
    </div>
</footer>

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

function escapeHtml(value){
    return String(value === null || value === undefined ? '' : value)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
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
        body:JSON.stringify({ reference_number: ref, tracking_number: ref })
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
    if(s==='cancelled' || s==='returned' || s==='archived') return 'c-danger';
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
    var prevGroupKey = null;
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
        logs.slice().reverse().forEach(function(log, idx) {
            var isLatest = idx === 0;
            var dc = isLatest ? 'c-latest' : dotClass(log.status_after);
            var dotIcon = isLatest ? 'fa-arrow-up' : 'fa-check';
            var fromTo = (log.from_office && log.to_office && log.from_office !== log.to_office) ? (log.from_office + ' -> ' + log.to_office) : '';
            var groupKey = _gk(log);
            var groupLabel = (groupKey === '__pending__') ? 'Submitted — Pending Physical Submission' : groupKey;
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

    var currentOfficeText = (doc.status === 'submitted')
        ? ('Awaiting physical submission to ' + (doc.submitted_to_office || doc.current_office || 'Records Section'))
        : (doc.status === 'archived' ? 'Unprocessed (Archived)' : (doc.current_office || doc.submitted_to_office || '-'));

    document.getElementById('drawerBody').innerHTML =
        '<div class="drawer-tl-head"><i class="fas fa-history"></i> Routing History</div>' +
        '<div class="drawer-timeline"><div class="tl">' + tlHtml + '</div></div>';
}

document.addEventListener('keydown', function(e){ if(e.key === 'Escape') closeDrawer(); });

function toggleSidebar(){
    var s=document.getElementById('mainSidebar');
    var o=document.getElementById('mobOverlay');
    var open=s.classList.toggle('open');
    o.classList.toggle('open',open);
    document.body.style.overflow=open?'hidden':'';
    document.getElementById('mobHamBtn').classList.toggle('toggle',open);
}
function closeSidebar(){
    document.getElementById('mainSidebar').classList.remove('open');
    document.getElementById('mobOverlay').classList.remove('open');
    document.body.style.overflow='';
    var btn=document.getElementById('mobHamBtn');if(btn)btn.classList.remove('toggle');
}
function logout(){
    fetch('/api/logout',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json'}})
        .then(function(){window.location.href='/login';})
        .catch(function(){window.location.href='/login';});
}

// Live stats refresh (silent update every 30s)
(function(){
    function refreshStats(){
        window.docTraxFetchJson('/api/records-stats',{
            headers:{'Accept':'application/json'},
            timeoutMs: 10000
        })
            .then(function(d){
                var compactCount = window.formatCompactCount || function(v) { return String(v); };
                document.getElementById('stat-total').textContent     = compactCount(d.total);
                document.getElementById('stat-submitted').textContent = compactCount(d.submitted);
                document.getElementById('stat-received').textContent  = compactCount(d.received);
                document.getElementById('stat-completed').textContent = compactCount(d.completed);
                document.getElementById('stat-archived').textContent  = compactCount(d.archived);
                // Toggle Reports sidebar link in real-time
                var rlink = document.getElementById('reports-nav-link');
                if (rlink) rlink.style.display = d.has_reports_access ? '' : 'none';
                window.clearStatusNotice('records-dashboard-stats');
            })
            .catch(function(){
                window.setStatusNotice('records-dashboard-stats', 'Live records statistics are temporarily unavailable. Showing the last known totals.', {
                    type: 'warning',
                    priority: 30
                });
            });
    }
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
</body>
</html>
