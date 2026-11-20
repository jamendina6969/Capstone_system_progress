<script>
(function () {
    if (window.__hydrotechPasswordSecurityInit) return;
    window.__hydrotechPasswordSecurityInit = true;

    function cryptoRandomInt(max) {
        if (window.crypto && window.crypto.getRandomValues) {
            const arr = new Uint32Array(1);
            window.crypto.getRandomValues(arr);
            return arr[0] % max;
        }
        return Math.floor(Math.random() * max);
    }

    window.generateStrongPassword = function (length = 12) {
        const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        const lower = 'abcdefghijkmnopqrstuvwxyz';
        const digits = '23456789';
        const symbols = '@#$%&*!?';
        const all = upper + lower + digits + symbols;

        const chars = [
            upper[cryptoRandomInt(upper.length)],
            upper[cryptoRandomInt(upper.length)],
            lower[cryptoRandomInt(lower.length)],
            lower[cryptoRandomInt(lower.length)],
            digits[cryptoRandomInt(digits.length)],
            digits[cryptoRandomInt(digits.length)],
            symbols[cryptoRandomInt(symbols.length)],
            symbols[cryptoRandomInt(symbols.length)],
        ];

        while (chars.length < length) {
            chars.push(all[cryptoRandomInt(all.length)]);
        }

        for (let i = chars.length - 1; i > 0; i--) {
            const j = cryptoRandomInt(i + 1);
            [chars[i], chars[j]] = [chars[j], chars[i]];
        }

        return chars.join('');
    };

    function showClipboardNotice(input) {
        const form = input.closest('form') || document;
        const notice = form.querySelector('.clipboard-block-notice');
        if (!notice) return;

        notice.classList.remove('hidden');
        clearTimeout(notice.__hideTimer);
        notice.__hideTimer = setTimeout(() => {
            notice.classList.add('hidden');
        }, 3200);
    }

    function setPasswordInputVisibility(inputId, visible) {
        const input = document.getElementById(inputId);
        if (!input) return;
        input.type = visible ? 'text' : 'password';

        const btn = document.querySelector(`[data-toggle-password="${inputId}"]`);
        if (btn) {
            const eyeOpen = btn.querySelector('.eye-open');
            const eyeOff = btn.querySelector('.eye-off');
            if (eyeOpen && eyeOff) {
                eyeOpen.classList.toggle('hidden', visible);
                eyeOff.classList.toggle('hidden', !visible);
            }
        }
    }

    function bindNoClipboard(root) {
        const inputs = (root || document).querySelectorAll('input[data-no-clipboard]');
        inputs.forEach((input) => {
            if (input.dataset.noClipboardBound) return;
            input.dataset.noClipboardBound = '1';

            ['paste', 'copy', 'cut', 'drop'].forEach((evt) => {
                input.addEventListener(evt, function (e) {
                    e.preventDefault();
                    showClipboardNotice(input);
                });
            });

            input.addEventListener('contextmenu', function (e) {
                e.preventDefault();
                showClipboardNotice(input);
            });

            input.addEventListener('keydown', function (e) {
                const key = (e.key || '').toLowerCase();
                if ((e.ctrlKey || e.metaKey) && ['c', 'v', 'x'].includes(key)) {
                    e.preventDefault();
                    showClipboardNotice(input);
                }
            });
        });
    }

    function bindPasswordToggles(root) {
        const buttons = (root || document).querySelectorAll('[data-toggle-password]');
        buttons.forEach((btn) => {
            if (btn.dataset.toggleBound) return;
            btn.dataset.toggleBound = '1';

            btn.addEventListener('click', function () {
                const targetId = btn.getAttribute('data-toggle-password');
                const input = document.getElementById(targetId);
                if (!input) return;
                setPasswordInputVisibility(targetId, input.type === 'password');
            });
        });
    }

    function bindPasswordSuggestions(root) {
        const buttons = (root || document).querySelectorAll('[data-suggest-password]');
        buttons.forEach((btn) => {
            if (btn.dataset.suggestBound) return;
            btn.dataset.suggestBound = '1';

            btn.addEventListener('click', function () {
                const suggested = window.generateStrongPassword(12);
                const boxId = btn.getAttribute('data-suggest-password');
                const targetInputId = btn.getAttribute('data-target-input');
                const confirmInputId = btn.getAttribute('data-confirm-input');

                // Automatically populate the Password field (still fully editable) and reveal it
                if (targetInputId) {
                    const targetInput = document.getElementById(targetInputId);
                    if (targetInput) {
                        targetInput.value = suggested;
                        setPasswordInputVisibility(targetInputId, true);
                        targetInput.dispatchEvent(new Event('input', { bubbles: true }));
                        targetInput.focus();
                    }
                }

                // Clear Confirm Password so the user still manually types confirmation
                if (confirmInputId) {
                    const confirmInput = document.getElementById(confirmInputId);
                    if (confirmInput) {
                        confirmInput.value = '';
                        confirmInput.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }

                const box = document.getElementById(boxId);
                if (box) {
                    const valueEl = box.querySelector('[data-suggested-value]');
                    if (valueEl) {
                        valueEl.textContent = suggested;
                    }
                    box.classList.remove('hidden');
                }
            });
        });
    }

    window.bindPasswordChecklist = function (passwordId, confirmId, checklistId) {
        const passInput = document.getElementById(passwordId);
        const confirmInput = document.getElementById(confirmId);
        const checklist = document.getElementById(checklistId);
        if (!passInput || !checklist) return;

        function update() {
            const val = passInput.value || '';
            const cVal = confirmInput ? (confirmInput.value || '') : '';
            const asciiOnly = /^[\x21-\x7E]+$/.test(val);
            const rules = {
                len: val.length >= 8 && val.length <= 72 && asciiOnly,
                upper: /[A-Z]/.test(val) && asciiOnly,
                lower: /[a-z]/.test(val) && asciiOnly,
                num: /[0-9]/.test(val) && asciiOnly,
                sym: /[\x21-\x2F\x3A-\x40\x5B-\x60\x7B-\x7E]/.test(val) && asciiOnly,
                match: val.length > 0 && cVal.length > 0 && val === cVal,
            };

            Object.keys(rules).forEach((key) => {
                const el = checklist.querySelector(`[data-rule="${key}"]`);
                if (!el) return;
                const ok = rules[key];

                el.classList.remove(
                    'text-emerald-600', 'dark:text-emerald-400',
                    'text-rose-600', 'dark:text-rose-400',
                    'text-gray-400', 'dark:text-slate-500'
                );

                if (ok) {
                    el.classList.add('text-emerald-600', 'dark:text-emerald-400');
                } else if (key === 'match' && cVal.length > 0) {
                    el.classList.add('text-rose-600', 'dark:text-rose-400');
                } else {
                    el.classList.add('text-gray-400', 'dark:text-slate-500');
                }

                const dot = el.querySelector('.rule-icon');
                if (dot) {
                    dot.textContent = ok ? '✓' : '•';
                }
            });
        }

        passInput.addEventListener('input', update);
        if (confirmInput) confirmInput.addEventListener('input', update);
        update();
    };

    window.syncOverridePasswordVisibility = function (config) {
        const selectEl = document.getElementById(config.roleSelectId);
        const sectionEl = document.getElementById(config.sectionId);
        const passInput = document.getElementById(config.inputId);
        const confirmInput = document.getElementById(config.confirmId);
        const hintEl = config.hintId ? document.getElementById(config.hintId) : null;
        if (!selectEl || !sectionEl) return;

        const role = selectEl.value;
        const isPrivileged = role === 'admin' || role === 'manager';

        if (isPrivileged) {
            sectionEl.classList.remove('hidden');

            let isRequired = false;
            if (config.mode === 'add') {
                isRequired = true;
            } else {
                const origRole = selectEl.dataset.originalRole || '';
                const hasExisting = selectEl.dataset.hasActionPassword === '1';
                const wasPrivileged = origRole === 'admin' || origRole === 'manager';
                isRequired = !wasPrivileged && !hasExisting;
            }

            if (passInput) passInput.required = isRequired;
            if (confirmInput) {
                confirmInput.required = isRequired || (passInput && passInput.value.length > 0);
            }
            if (hintEl) {
                hintEl.textContent = isRequired
                    ? '(required for Manager/Admin)'
                    : '(leave blank to keep current)';
            }
        } else {
            sectionEl.classList.add('hidden');
            if (passInput) {
                passInput.required = false;
                passInput.value = '';
            }
            if (confirmInput) {
                confirmInput.required = false;
                confirmInput.value = '';
            }
        }
    };

    window.initHydrotechPasswordSecurity = function (root) {
        bindNoClipboard(root);
        bindPasswordToggles(root);
        bindPasswordSuggestions(root);
    };

    document.addEventListener('DOMContentLoaded', function () {
        window.initHydrotechPasswordSecurity(document);
    });
})();
</script>
