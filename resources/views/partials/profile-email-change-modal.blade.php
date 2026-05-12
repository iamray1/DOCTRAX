<div class="email-modal-overlay" id="emailChangeModal" aria-hidden="true">
    <div class="email-modal" role="dialog" aria-modal="true" aria-labelledby="emailChangeTitle">
        <button type="button" class="email-modal-close" id="emailChangeClose" aria-label="Close email verification">
            <i class="fas fa-times"></i>
        </button>

        <div class="email-modal-icon">
            <i class="fas fa-envelope-open-text"></i>
        </div>
        <h2 id="emailChangeTitle">First, let's make sure it's you</h2>
        <p>Before we make any changes, we'll just need a quick confirmation.</p>

        <div class="email-code-card">
            <div class="email-code-label">Email a code</div>
            <div class="email-code-address" id="emailChangeCurrentEmail"><!--email_off-->{{ $user->email }}<!--/email_off--></div>
        </div>

        <div id="emailChangeStartStep">
            <div class="email-modal-actions">
                <button type="button" class="email-modal-secondary" id="emailChangeCancel">Cancel</button>
                <button type="button" class="email-modal-primary" id="emailSendCodeBtn">
                    <i class="fas fa-paper-plane"></i> Email a code
                </button>
            </div>
        </div>

        <div class="email-code-step" id="emailChangeCodeStep">
            <input type="text" class="email-code-input" id="emailChangeCode" inputmode="numeric" maxlength="6" autocomplete="one-time-code" placeholder="000000">
            <div class="email-modal-error" id="emailChangeError"></div>
            <div class="email-modal-actions">
                <button type="button" class="email-modal-secondary" id="emailResendCodeBtn">Resend code</button>
                <button type="button" class="email-modal-primary" id="emailVerifyCodeBtn">
                    <i class="fas fa-check"></i> Verify code
                </button>
            </div>
        </div>
    </div>
</div>
