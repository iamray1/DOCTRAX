.submission-header-actions{display:flex;align-items:stretch;gap:10px;position:relative;flex-shrink:0}
.submission-header-actions .live-clock{flex-shrink:0}
.submission-notification-wrap{position:relative;flex-shrink:0;z-index:1;display:flex}
.submission-notification-wrap.open{z-index:2000}
@keyframes submission-bell-ring{0%{transform:rotate(0)}14%{transform:rotate(16deg)}28%{transform:rotate(-14deg)}42%{transform:rotate(10deg)}56%{transform:rotate(-7deg)}70%{transform:rotate(4deg)}100%{transform:rotate(0)}}
@keyframes submission-badge-pop{0%,100%{transform:scale(1)}45%{transform:scale(1.16)}}
.submission-notif-btn{position:relative;width:58px;min-height:100%;border:1px solid var(--border);border-radius:8px;background:#fff;color:var(--text-dark);display:inline-flex;align-items:center;justify-content:center;cursor:pointer;font-size:16px;transition:background .15s,border-color .15s,color .15s,box-shadow .15s}
.submission-notif-btn:hover i{animation:submission-bell-ring .72s ease both;transform-origin:50% 8%}
.submission-notif-btn:hover .submission-notif-badge{animation:submission-badge-pop .42s ease both}
.submission-notification-wrap.open .submission-notif-btn{background:#f8faff;border-color:rgba(0,86,179,.35);color:var(--primary);box-shadow:0 3px 12px rgba(15,23,42,.06)}
.submission-notif-badge{position:absolute;top:-7px;right:-7px;min-width:19px;height:19px;padding:0 5px;border-radius:999px;background:#ea580c;color:#fff;border:2px solid var(--bg);font-size:10px;font-weight:800;line-height:1;display:flex;align-items:center;justify-content:center}
.submission-notif-panel{position:fixed;top:0;left:16px;width:min(390px,calc(100vw - 32px));max-height:calc(100vh - 32px);background:#fff;border:1px solid var(--border);border-radius:10px;box-shadow:0 18px 50px rgba(15,23,42,.18);z-index:2000;opacity:0;transform:translateY(-6px);pointer-events:none;overflow:hidden;display:flex;flex-direction:column;transition:opacity .16s ease,transform .16s ease}
.submission-notification-wrap.open .submission-notif-panel,
.submission-notif-panel.open{opacity:1;transform:translateY(0);pointer-events:auto}
.submission-notif-head{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px 16px;border-bottom:1px solid #eef2f7}
.submission-notif-title{font-size:14px;font-weight:800;color:var(--text-dark)}
.submission-notif-count{background:#fff7ed;color:#c2410c;font-size:11px;font-weight:800;padding:2px 8px;border-radius:999px;white-space:nowrap}
.submission-notif-list{max-height:min(52vh,410px);overflow-y:auto;overscroll-behavior:contain;-webkit-overflow-scrolling:touch;scrollbar-gutter:stable;flex:1 1 auto;min-height:0}
.submission-notif-list::-webkit-scrollbar{width:8px}
.submission-notif-list::-webkit-scrollbar-thumb{background:#cbd5e1;border-radius:999px}
.submission-notif-list::-webkit-scrollbar-track{background:transparent}
.submission-notif-item{display:flex;gap:11px;padding:13px 16px;text-decoration:none;color:inherit;border-bottom:1px solid #f1f5f9;background:#fff;transition:background .15s}
.submission-notif-item:last-child{border-bottom:0}
.submission-notif-item:hover,.submission-notif-item:focus{background:#f8faff;outline:none}
.submission-notif-icon{width:32px;height:32px;border-radius:8px;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:13px}
.submission-notif-icon.received,.submission-notif-icon.in_review,.submission-notif-icon.on_hold{background:#fff7ed;color:#c2410c}
.submission-notif-icon.for_pickup{background:#fff7ed;color:#ea580c}
.submission-notif-icon.returned,.submission-notif-icon.cancelled{background:#fef2f2;color:#dc2626}
.submission-notif-icon.completed{background:#f0fdf4;color:#16a34a}
.submission-notif-icon.archived{background:#f1f5f9;color:#64748b}
.submission-notif-body{min-width:0;flex:1}
.submission-notif-top{display:flex;align-items:center;gap:8px;min-width:0;margin-bottom:2px}
.submission-notif-ref{font-family:monospace;font-size:12px;font-weight:800;color:var(--primary);letter-spacing:.3px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;min-width:0}
.submission-notif-status{display:inline-flex;align-items:center;gap:5px;padding:2px 7px;border-radius:999px;background:#eef2ff;color:#4338ca;font-size:10px;font-weight:800;white-space:nowrap;flex-shrink:0}
.submission-notif-status.received,.submission-notif-status.in_review,.submission-notif-status.on_hold,.submission-notif-status.for_pickup{background:#fff7ed;color:#c2410c}
.submission-notif-status.returned,.submission-notif-status.cancelled{background:#fef2f2;color:#dc2626}
.submission-notif-status.completed{background:#f0fdf4;color:#16a34a}
.submission-notif-status.archived{background:#f1f5f9;color:#64748b}
.submission-notif-subject{font-size:13px;font-weight:700;color:var(--text-dark);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.submission-notif-meta{display:flex;align-items:center;gap:8px;margin-top:4px;color:#64748b;font-size:11px;min-width:0}
.submission-notif-meta span{min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.submission-notif-view{display:inline-flex;align-items:center;gap:5px;margin-top:7px;color:var(--primary);font-size:11px;font-weight:800}
.submission-notif-empty{padding:24px 16px;text-align:center;color:var(--text-muted);font-size:13px}
.submission-notif-empty i{display:block;margin-bottom:8px;font-size:24px;color:#cbd5e1}
.submission-notif-note{padding:9px 16px;border-top:1px solid #eef2f7;background:#fff7ed;color:#9a3412;font-size:11px;font-weight:700;text-align:center}
.submission-notif-footer{display:flex;align-items:center;justify-content:center;gap:6px;padding:12px 16px;border-top:1px solid #eef2f7;text-decoration:none;color:var(--primary);font-size:12px;font-weight:800;background:#fbfdff}
.submission-notif-footer:hover{background:#f1f7ff}
@media (max-width:768px){
    .submission-header-actions{width:100%;align-items:stretch;justify-content:flex-end}
    .submission-header-actions .live-clock{flex:1;width:auto;min-width:0;padding:10px 12px;gap:10px}
    .submission-notification-wrap{flex:0 0 auto;align-self:stretch;min-height:44px}
    .top-actions .submission-notif-btn,
    .submission-notif-btn{width:50px;height:auto;min-height:44px;align-self:stretch}
    .top-actions .submission-notif-panel,
    .submission-notif-panel{right:auto;width:calc(100vw - 24px);max-height:calc(100vh - 24px)}
    .submission-notif-list{max-height:none}
}
@media (max-width:420px){
    .submission-header-actions .clock-sep,
    .submission-header-actions .clock-date-display{display:none}
}
