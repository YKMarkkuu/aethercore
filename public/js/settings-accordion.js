// Drives both the settings modal and the full /settings page — one
// engine, since partials.settings-content is the single shared copy of
// the markup for both. Category header click expands its subheader
// list; subheader click swaps the visible content panel. Deep-links via
// #settings-<key> or #<category>/<subkey> also work.
(function () {
    function openCategory(key, exclusive) {
        document.querySelectorAll('[data-accordion-panel]').forEach(panel => {
            const isTarget = panel.dataset.accordionPanel === key;
            if (exclusive) panel.classList.toggle('hidden', !isTarget);
            else if (isTarget) panel.classList.toggle('hidden');
            panel.closest('.settings-accordion-group')?.classList.toggle('active', !panel.classList.contains('hidden'));
        });
    }

    function showTarget(target) {
        document.querySelectorAll('.settings-tab').forEach(t => t.classList.add('hidden'));
        document.getElementById('settings-' + target)?.classList.remove('hidden');
        document.querySelectorAll('[data-settings-target]').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.settingsTarget === target);
        });
    }

    function init(root) {
        root.querySelectorAll('[data-accordion-toggle]').forEach(btn => {
            btn.addEventListener('click', () => openCategory(btn.dataset.accordionToggle, false));
        });
        root.querySelectorAll('[data-settings-target]').forEach(btn => {
            btn.addEventListener('click', () => showTarget(btn.dataset.settingsTarget));
        });

        // Default: expand the first category and show its first panel.
        const firstGroup = root.querySelector('.settings-accordion-group');
        const firstSub = root.querySelector('[data-settings-target]');
        if (firstGroup) openCategory(firstGroup.dataset.accordionKey, true);
        if (firstSub) showTarget(firstSub.dataset.settingsTarget);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-settings-nav]').forEach(nav => init(nav.parentElement));
    });
})();
