<script>
(function () {
    if (window.__hydrotechGlobalInputGuardsInit) return;
    window.__hydrotechGlobalInputGuardsInit = true;

    const EMOJI_RE = /(?:[\u{1F1E6}-\u{1F1FF}]|[0-9#*]\u{FE0F}?\u{20E3}|[\u{1F300}-\u{1FAFF}\u{2600}-\u{27BF}\u{2300}-\u{23FF}\u{2B00}-\u{2BFF}\u{FE0F}\u{200D}\u{20E3}])/gu;
    const NON_ALPHA_NAME_RE = /[^\p{L}\s.'-]/gu;
    const NON_ASCII_PASS_RE = /[^\x21-\x7E]/g;

    const TEXT_INPUT_TYPES = new Set(['text', 'search', 'email', 'password', 'tel', 'url', '']);

    function isEditableTextControl(el) {
        if (!el || el.readOnly || el.disabled) return false;
        if (el.tagName === 'TEXTAREA') return true;
        if (el.tagName === 'INPUT') {
            return TEXT_INPUT_TYPES.has((el.type || '').toLowerCase());
        }
        return false;
    }

    function isNameField(el) {
        if (!el || el.tagName !== 'INPUT') return false;
        return el.hasAttribute('data-alpha-name')
            || el.name === 'firstname'
            || el.name === 'lastname';
    }

    function isAsciiPasswordField(el) {
        return !!(el && el.tagName === 'INPUT' && el.hasAttribute('data-ascii-password'));
    }

    let toastEl = null;
    let toastTimer = null;
    function showInputGuardToast(message) {
        if (!toastEl) {
            toastEl = document.createElement('div');
            toastEl.setAttribute('role', 'status');
            toastEl.style.cssText =
                'position:fixed;bottom:20px;right:20px;z-index:99999;padding:10px 16px;border-radius:12px;' +
                'font-size:12px;font-weight:600;background:#1e293b;color:#fef3c7;border:1px solid #f59e0b;' +
                'box-shadow:0 10px 25px rgba(0,0,0,0.25);transition:opacity 0.2s ease;pointer-events:none;';
            document.body.appendChild(toastEl);
        }
        toastEl.textContent = message;
        toastEl.style.opacity = '1';
        toastEl.style.display = 'block';
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () {
            if (toastEl) {
                toastEl.style.opacity = '0';
                setTimeout(function () {
                    if (toastEl && toastEl.style.opacity === '0') toastEl.style.display = 'none';
                }, 220);
            }
        }, 2600);
    }

    function sanitizeControlValue(el) {
        if (!isEditableTextControl(el)) return;
        const original = el.value;
        if (!original) return;

        let cleaned = original;
        let blockedReason = '';

        // 1. Block emojis globally on every form field in the system
        EMOJI_RE.lastIndex = 0;
        if (EMOJI_RE.test(cleaned)) {
            EMOJI_RE.lastIndex = 0;
            cleaned = cleaned.replace(EMOJI_RE, '');
            blockedReason = 'Emojis are not allowed in form fields.';
        }

        // 2. Block numbers & invalid symbols in First Name / Last Name
        if (isNameField(el)) {
            NON_ALPHA_NAME_RE.lastIndex = 0;
            if (NON_ALPHA_NAME_RE.test(cleaned)) {
                NON_ALPHA_NAME_RE.lastIndex = 0;
                cleaned = cleaned.replace(NON_ALPHA_NAME_RE, '');
                blockedReason = blockedReason || 'Numbers and special symbols are not allowed in name fields.';
            }
        }

        // 3. Enforce printable ASCII (no spaces, no emojis, no non-ASCII) on strict password fields
        if (isAsciiPasswordField(el)) {
            NON_ASCII_PASS_RE.lastIndex = 0;
            if (NON_ASCII_PASS_RE.test(cleaned)) {
                NON_ASCII_PASS_RE.lastIndex = 0;
                cleaned = cleaned.replace(NON_ASCII_PASS_RE, '');
                blockedReason = blockedReason || 'Passwords may only use standard letters, numbers, and keyboard symbols (no spaces or emojis).';
            }
        }

        if (cleaned !== original) {
            const pos = el.selectionStart;
            const diff = original.length - cleaned.length;
            el.value = cleaned;
            if (typeof pos === 'number' && el.setSelectionRange && el.type !== 'email') {
                const nextPos = Math.max(0, pos - diff);
                try { el.setSelectionRange(nextPos, nextPos); } catch (e) {}
            }
            if (blockedReason) showInputGuardToast(blockedReason);
        }
    }

    // Intercept beforeinput when supported for instant zero-flicker prevention
    document.addEventListener('beforeinput', function (e) {
        const el = e.target;
        if (!isEditableTextControl(el) || typeof e.data !== 'string' || !e.data) return;

        EMOJI_RE.lastIndex = 0;
        if (EMOJI_RE.test(e.data)) {
            e.preventDefault();
            showInputGuardToast('Emojis are not allowed in form fields.');
            return;
        }

        if (isNameField(el)) {
            NON_ALPHA_NAME_RE.lastIndex = 0;
            if (NON_ALPHA_NAME_RE.test(e.data)) {
                e.preventDefault();
                showInputGuardToast('Numbers and special symbols are not allowed in name fields.');
                return;
            }
        }

        if (isAsciiPasswordField(el)) {
            NON_ASCII_PASS_RE.lastIndex = 0;
            if (NON_ASCII_PASS_RE.test(e.data)) {
                e.preventDefault();
                showInputGuardToast('Passwords may only use standard letters, numbers, and keyboard symbols (no spaces or emojis).');
            }
        }
    }, true);

    // Fallback for IME / paste / autofill / browsers without beforeinput data
    document.addEventListener('input', function (e) {
        sanitizeControlValue(e.target);
    }, true);
})();
</script>
