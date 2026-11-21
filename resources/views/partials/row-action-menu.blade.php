{{-- Shared Floating Portal Row Action (Ellipsis ⋯) Menu --}}
<div id="globalRowActionPortal"
     class="hidden fixed z-[92] min-w-[185px] max-w-[240px] bg-white dark:bg-[#10141D] border-2 border-slate-200 dark:border-[#1e2536] rounded-2xl shadow-2xl py-1.5 overflow-hidden">
</div>

<script>
(function () {
    if (window.__hydrotechRowActionPortalInit) return;
    window.__hydrotechRowActionPortalInit = true;

    let activeTrigger = null;

    function getPortal() {
        return document.getElementById('globalRowActionPortal');
    }

    function closeRowActionMenu() {
        const portal = getPortal();
        if (portal) {
            portal.classList.add('hidden');
            portal.innerHTML = '';
        }
        activeTrigger = null;
    }

    function toneClasses(tone) {
        switch (tone) {
            case 'emerald':
                return 'text-emerald-700 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-950/40';
            case 'rose':
                return 'text-rose-600 dark:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/40';
            case 'blue':
                return 'text-blue-700 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-950/40';
            case 'amber':
                return 'text-amber-700 dark:text-amber-400 hover:bg-amber-50 dark:hover:bg-amber-950/40';
            default:
                return 'text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-[#161B26]';
        }
    }

    window.openRowActionMenu = function (event, triggerBtn) {
        if (event) {
            event.preventDefault();
            event.stopPropagation();
        }

        const portal = getPortal();
        if (!portal || !triggerBtn) return;

        if (activeTrigger === triggerBtn && !portal.classList.contains('hidden')) {
            closeRowActionMenu();
            return;
        }

        const wrapper = triggerBtn.closest('[data-row-actions]');
        const sourceContainer = wrapper ? wrapper.querySelector('.row-action-items') : null;
        if (!sourceContainer) return;

        const sourceNodes = Array.from(sourceContainer.querySelectorAll('button, a'));
        if (sourceNodes.length === 0) return;

        portal.innerHTML = '';
        sourceNodes.forEach(function (src) {
            if (src.hasAttribute('data-skip-menu')) return;

            const isDisabled = src.disabled || src.hasAttribute('disabled');
            const tone = src.dataset.tone || 'default';
            const label = (src.dataset.label || src.textContent || '').trim();
            if (!label) return;

            const itemBtn = document.createElement('button');
            itemBtn.type = 'button';
            itemBtn.disabled = isDisabled;
            itemBtn.className = `w-full flex items-center justify-between gap-2.5 px-4 py-2.5 text-left text-xs font-semibold transition-colors ${isDisabled ? 'opacity-40 cursor-not-allowed text-slate-400' : toneClasses(tone) + ' cursor-pointer'}`;
            itemBtn.textContent = label;

            if (!isDisabled) {
                itemBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    closeRowActionMenu();

                    if (src.tagName === 'A') {
                        const href = src.getAttribute('href');
                        const target = src.getAttribute('target');
                        if (href) {
                            if (target === '_blank') {
                                window.open(href, '_blank', 'noopener,noreferrer');
                            } else {
                                window.location.href = href;
                            }
                        }
                    } else {
                        src.click();
                    }
                });
            }

            portal.appendChild(itemBtn);
        });

        // Reveal off-screen first to measure true height/width, then clamp inside viewport
        portal.style.visibility = 'hidden';
        portal.classList.remove('hidden');

        const rect = triggerBtn.getBoundingClientRect();
        const menuW = portal.offsetWidth || 195;
        const menuH = portal.offsetHeight || 120;
        const pad = 10;

        let left = rect.right - menuW;
        if (left < pad) left = pad;
        if (left + menuW > window.innerWidth - pad) left = Math.max(pad, window.innerWidth - menuW - pad);

        let top = rect.bottom + 6;
        if (top + menuH > window.innerHeight - pad) {
            top = Math.max(pad, rect.top - menuH - 6);
        }

        portal.style.left = `${left}px`;
        portal.style.top = `${top}px`;
        portal.style.visibility = 'visible';
        activeTrigger = triggerBtn;
    };

    window.closeRowActionMenu = closeRowActionMenu;

    document.addEventListener('click', function (e) {
        const portal = getPortal();
        if (!portal || portal.classList.contains('hidden')) return;
        if (!portal.contains(e.target) && (!activeTrigger || !activeTrigger.contains(e.target))) {
            closeRowActionMenu();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeRowActionMenu();
    });

    window.addEventListener('scroll', closeRowActionMenu, true);
    window.addEventListener('resize', closeRowActionMenu);
})();
</script>
