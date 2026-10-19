(() => {
    'use strict';
    const panel = document.querySelector('[data-verification-timer]');
    if (!panel) return;

    const expiresAt = Number(panel.dataset.expiresAt);
    const resendAt = Number(panel.dataset.resendAt);
    const serverNow = Number(panel.dataset.serverNow);
    const startedAt = performance.now();
    const expiry = panel.querySelector('#code-expiry');
    const resend = panel.querySelector('[data-resend-code]');
    const verify = panel.querySelector('[data-verify-code]');
    const format = seconds => `${String(Math.floor(seconds / 60)).padStart(2, '0')}:${String(seconds % 60).padStart(2, '0')}`;

    const update = () => {
        const currentTime = serverNow + Math.floor((performance.now() - startedAt) / 1000);
        const remaining = Math.max(0, expiresAt - currentTime);
        const resendRemaining = Math.max(0, resendAt - currentTime);
        expiry.textContent = remaining > 0
            ? panel.dataset.expiryLabel.replace(':time', format(remaining))
            : panel.dataset.expiredLabel;
        verify.disabled = remaining === 0;
        resend.disabled = remaining === 0 || resendRemaining > 0;
        resend.textContent = remaining > 0 && resendRemaining > 0
            ? panel.dataset.resendWaitLabel.replace(':time', format(resendRemaining))
            : panel.dataset.resendLabel;
        return remaining > 0;
    };

    if (update()) {
        const interval = setInterval(() => {
            if (!update()) clearInterval(interval);
        }, 1000);
        document.addEventListener('visibilitychange', update);
    }
})();
