(() => {
    'use strict';
    const panel = document.querySelector('[data-verification-timer]');
    if (!panel) return;

    const digits = Array.from(panel.querySelectorAll('.verification-digit'));
    const code = panel.querySelector('#verification-code');
    const syncCode = () => { code.value = digits.map((digit) => digit.value).join(''); };
    const distribute = (index, text) => {
        const numbers = text.replace(/\D/g, '').slice(0, digits.length - index);
        digits[index].value = '';
        [...numbers].forEach((number, offset) => { digits[index + offset].value = number; });
        syncCode();
        if (numbers.length) digits[Math.min(index + numbers.length, digits.length - 1)].focus();
    };
    digits.forEach((digit, index) => {
        digit.addEventListener('input', () => distribute(index, digit.value));
        digit.addEventListener('paste', (event) => {
            event.preventDefault();
            distribute(index, event.clipboardData.getData('text'));
        });
        digit.addEventListener('focus', () => digit.select());
        digit.addEventListener('keydown', (event) => {
            if (event.key === 'Backspace' && digit.value === '' && index > 0) {
                event.preventDefault();
                digits[index - 1].value = '';
                digits[index - 1].focus();
                syncCode();
            } else if (event.key === 'ArrowLeft' && index > 0) {
                event.preventDefault();
                digits[index - 1].focus();
            } else if (event.key === 'ArrowRight' && index < digits.length - 1) {
                event.preventDefault();
                digits[index + 1].focus();
            }
        });
    });

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
