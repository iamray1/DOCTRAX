        .change-email-link{margin-top:8px;background:transparent;border:0;color:var(--primary);font-family:inherit;font-size:12px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;padding:0;width:max-content}
        .change-email-link:hover{text-decoration:underline}
        .email-change-note{display:none;margin-top:8px;font-size:12px;color:#16a34a;font-weight:500}
        .email-change-note.show{display:block}
        .form-group input[readonly]{background:#f8fafc;color:#64748b;cursor:not-allowed}
        .email-modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.48);z-index:700;display:none;align-items:center;justify-content:center;padding:20px}
        .email-modal-overlay.show{display:flex}
        .email-modal{width:100%;max-width:430px;background:#fff;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 24px 60px rgba(15,23,42,.22);padding:28px;position:relative}
        .email-modal-close{position:absolute;top:14px;right:14px;width:34px;height:34px;border:0;border-radius:999px;background:#f8fafc;color:#64748b;display:flex;align-items:center;justify-content:center;cursor:pointer}
        .email-modal-close:hover{background:#e2e8f0;color:#0f172a}
        .email-modal-icon{width:48px;height:48px;border-radius:999px;background:#eef4fb;color:var(--primary);display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:16px}
        .email-modal h2{font-size:20px;line-height:1.3;color:#0f172a;margin:0 34px 8px 0;font-weight:700;letter-spacing:0}
        .email-modal p{font-size:13px;line-height:1.7;color:#64748b;margin:0 0 18px}
        .email-code-card{border:1px solid #e2e8f0;background:#f8fafc;border-radius:10px;padding:14px 16px;margin:0 0 18px}
        .email-code-label{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#64748b;margin-bottom:4px}
        .email-code-address{font-size:14px;font-weight:700;color:#0f172a;word-break:break-all}
        .email-code-input{width:100%;height:46px;border:1px solid #cbd5e1;border-radius:8px;padding:0 14px;text-align:center;font-family:inherit;font-size:18px;font-weight:700;letter-spacing:5px;color:#0f172a;outline:none;margin-bottom:8px}
        .email-code-input:focus{border-color:var(--primary);box-shadow:0 0 0 3px rgba(0,86,179,.12)}
        .email-modal-error{display:none;color:#dc2626;font-size:12px;margin:0 0 12px}
        .email-modal-error.show{display:block}
        .email-modal-actions{display:flex;justify-content:flex-end;gap:10px;align-items:center;margin-top:14px}
        .email-modal-secondary{border:1px solid #e2e8f0;background:#fff;color:#334155;border-radius:8px;padding:10px 14px;font-family:inherit;font-size:13px;font-weight:600;cursor:pointer}
        .email-modal-secondary:hover{background:#f8fafc}
        .email-modal-primary{border:0;background:var(--primary);color:#fff;border-radius:8px;padding:10px 16px;font-family:inherit;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:7px}
        .email-modal-primary:hover{background:var(--primary-dark)}
        .email-modal-primary:disabled,.email-modal-secondary:disabled{opacity:.65;cursor:not-allowed}
        .email-code-step{display:none}
        .email-code-step.show{display:block}
        @media(max-width:520px){.email-modal{padding:24px 20px}.email-modal-actions{flex-direction:column-reverse;align-items:stretch}.email-modal-primary,.email-modal-secondary{justify-content:center;width:100%}}
