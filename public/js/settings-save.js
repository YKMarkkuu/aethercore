// Settings bulk-save controller (Apply / OK / Cancel bar).
// Finds every [data-settings] root, tracks [data-bulk-field] controls
// against their saved ("original") values, and PATCHes only the dirty ones
// to /settings/preferences. Exposes window.SettingsSave.
(function () {
    'use strict';

    const DEFAULT_URL = '/settings/preferences';
    const controllers = [];

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content;
    }

    function prefersReducedMotion() {
        return !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches);
    }

    // Page mode = the /settings route, where layouts/app.blade.php does not
    // render #settingsModal.
    function isPageMode() {
        return !document.getElementById('settingsModal');
    }

    // ---------- value helpers ----------
    function readValue(el) {
        return el.type === 'checkbox' ? String(el.checked) : el.value;
    }

    function writeValue(el, value) {
        if (el.type === 'checkbox') {
            el.checked = value === 'true';
        } else {
            el.value = value;
        }
    }

    // The server-rendered value (defaultSelected / defaultChecked), not
    // whatever the browser may have restored from form history or bfcache.
    function serverValue(el) {
        if (el.type === 'checkbox') return String(el.defaultChecked);
        if (el.tagName === 'SELECT') {
            const opt = Array.from(el.options).find(o => o.defaultSelected) || el.options[0];
            return opt ? opt.value : '';
        }
        return el.defaultValue;
    }

    // ---------- live theme swap ----------
    function applyTheme(theme) {
        const existing = document.getElementById('themeStylesheet');

        if (theme === 'aethercore') {
            existing?.remove();
            return;
        }

        if (existing) {
            existing.href = existing.href.replace(/[^/]+\.css(\?.*)?$/, theme + '.css');
            return;
        }

        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.id = 'themeStylesheet';
        link.href = '/css/themes/' + theme + '.css';
        document.head.appendChild(link);
    }

    // ---------- per-root controller ----------
    function createController(root) {
        const modal = document.getElementById('settingsModal');
        const inModal = !!modal && modal.contains(root);
        const pageMode = isPageMode();
        const url = root.dataset.preferencesUrl || DEFAULT_URL;

        const fields = Array.from(root.querySelectorAll('[data-bulk-field]'));
        const statusEl = root.querySelector('[data-savebar-status]');
        const applyBtn = root.querySelector('[data-savebar-apply]');
        const cancelBtn = root.querySelector('[data-savebar-cancel]');
        const okBtn = root.querySelector('[data-savebar-ok]');

        let busy = false;
        let confirming = false;

        fields.forEach(f => { f.dataset.original = serverValue(f); });

        if (pageMode) {
            if (okBtn) okBtn.style.display = 'none';
            if (cancelBtn) cancelBtn.textContent = 'Revert';
        }

        // ----- dirty tracking -----
        function isFieldDirty(f) {
            return readValue(f) !== f.dataset.original;
        }

        function dirtyFields() {
            return fields.filter(isFieldDirty);
        }

        function isDirty() {
            return dirtyFields().length > 0;
        }

        function updateMarkers(dirty) {
            root.querySelectorAll('.is-dirty').forEach(el => {
                if (el.classList.contains('settings-section')
                    || el.classList.contains('settings-sublink')
                    || el.classList.contains('settings-group')) {
                    el.classList.remove('is-dirty');
                }
            });

            dirty.forEach(f => {
                const section = f.closest('.settings-section');
                if (!section) return;
                section.classList.add('is-dirty');

                root.querySelectorAll('.settings-sublink').forEach(link => {
                    if (link.dataset.scrollTarget === section.id) link.classList.add('is-dirty');
                });

                const cat = section.closest('[data-category-content]')?.dataset.categoryContent;
                if (cat) {
                    root.querySelector('.settings-group[data-group="' + cat + '"]')?.classList.add('is-dirty');
                }
            });
        }

        function refresh() {
            const dirty = dirtyFields();
            const n = dirty.length;

            root.dataset.dirty = n > 0 ? 'true' : 'false';
            root.dataset.dirtyCount = String(n);

            if (statusEl) {
                if (busy) statusEl.textContent = 'Applying…';
                else if (n === 0) statusEl.textContent = 'All changes applied';
                else if (n === 1) statusEl.textContent = '1 unsaved change';
                else statusEl.textContent = n + ' unsaved changes';
                statusEl.style.fontWeight = (!busy && n > 0) ? '700' : '';
                statusEl.style.color = (!busy && n > 0) ? 'var(--accent-dark)' : '';
            }

            if (applyBtn) applyBtn.disabled = busy || n === 0;
            updateMarkers(dirty);
        }

        function setBusy(value) {
            busy = value;
            root.style.cursor = value ? 'progress' : '';
            [applyBtn, okBtn, cancelBtn].forEach(btn => {
                if (!btn) return;
                if (value) btn.disabled = true;
                else if (btn !== applyBtn) btn.disabled = false;
            });
            if (applyBtn) applyBtn.textContent = value ? 'Applying…' : 'Apply';
            refresh();
        }

        // ----- inline errors -----
        function errorAnchor(field) {
            return field.closest('.settings-toggle-row') || field;
        }

        function clearError(field) {
            const key = field.dataset.bulkField;
            root.querySelectorAll('.settings-error[data-bulk-error]').forEach(el => {
                if (el.dataset.bulkError === key) el.remove();
            });
        }

        function clearAllErrors() {
            root.querySelectorAll('.settings-error[data-bulk-error]').forEach(el => el.remove());
        }

        function showErrors(errors) {
            let first = null;

            Object.keys(errors || {}).forEach(key => {
                const name = key.replace(/^settings\./, '');
                const field = fields.find(f => f.dataset.bulkField === name);
                if (!field) return;

                clearError(field);
                const msg = document.createElement('div');
                msg.className = 'settings-error';
                msg.dataset.bulkError = name;
                msg.setAttribute('role', 'alert');
                msg.textContent = [].concat(errors[key])[0];
                errorAnchor(field).insertAdjacentElement('afterend', msg);

                if (!first) first = field;
            });

            return first;
        }

        function jumpTo(field) {
            const section = field.closest('.settings-section');
            const cat = section?.closest('[data-category-content]')?.dataset.categoryContent;

            // settings-modal.js owns category switching; drive it through its own button.
            if (cat) root.querySelector('[data-group-toggle="' + cat + '"]')?.click();

            requestAnimationFrame(() => {
                section?.scrollIntoView({ behavior: prefersReducedMotion() ? 'auto' : 'smooth', block: 'start' });
                field.focus({ preventScroll: true });
            });
        }

        // ----- apply -----
        async function apply() {
            if (busy) return false;

            const dirty = dirtyFields();
            if (dirty.length === 0) return true;

            const settings = {};
            const sent = {};
            dirty.forEach(f => {
                const key = f.dataset.bulkField;
                sent[key] = f;
                settings[key] = f.type === 'checkbox' ? f.checked : f.value;
            });

            clearAllErrors();
            setBusy(true);

            try {
                const response = await fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken(),
                    },
                    body: JSON.stringify({ settings }),
                    credentials: 'same-origin',
                });

                const data = await response.json().catch(() => ({}));

                if (response.ok) {
                    const saved = data.saved || settings;

                    // Adopt the server's values as the new baseline for the
                    // keys we sent. Never touch the control's current value,
                    // so an edit made mid-flight stays dirty.
                    Object.keys(sent).forEach(key => {
                        const value = key in saved ? saved[key] : settings[key];
                        sent[key].dataset.original = String(value);
                    });

                    if ('theme' in saved) applyTheme(saved.theme);

                    window.Toast?.show('Settings saved.', 'success');
                    return true;
                }

                if (response.status === 422) {
                    const first = showErrors(data.errors);
                    window.Toast?.show("Some settings couldn't be saved.", 'error');
                    if (first) jumpTo(first);
                    return false;
                }

                if (response.status === 401 || response.status === 419) {
                    window.Toast?.show('Your session expired. Please refresh the page.', 'error');
                    return false;
                }

                window.Toast?.show("Couldn't save settings. Please try again.", 'error');
                return false;
            } catch (err) {
                window.Toast?.show("Couldn't save settings. Please try again.", 'error');
                return false;
            } finally {
                setBusy(false);
            }
        }

        // ----- revert / close -----
        function revertAll() {
            fields.forEach(f => writeValue(f, f.dataset.original));
            clearAllErrors();
            refresh();
        }

        function closeModal() {
            modal?.classList.add('hidden');
        }

        async function requestClose() {
            if (busy || confirming) return;

            if (isDirty()) {
                confirming = true;
                const ok = await window.Toast.confirm('You have unsaved changes. Discard them?', {
                    danger: true,
                    title: 'Unsaved Changes',
                    confirmText: 'Discard',
                    cancelText: 'Keep Editing',
                });
                confirming = false;
                if (!ok) return;
                revertAll();
            }

            closeModal();
        }

        // ----- events -----
        function onFieldChange(e) {
            const field = e.target.closest ? e.target.closest('[data-bulk-field]') : null;
            if (!field) return;
            clearError(field);
            refresh();
        }

        root.addEventListener('input', onFieldChange);
        root.addEventListener('change', onFieldChange);

        // Keep the bar's bulk/non-bulk state in sync with the active category.
        // settings-modal.js switches categories but doesn't touch these attributes.
        root.addEventListener('click', e => {
            const btn = e.target.closest('[data-group-toggle]');
            if (!btn) return;
            const group = btn.closest('[data-group]');
            root.dataset.activeCategory = btn.dataset.groupToggle;
            root.dataset.activeBulk = group && group.dataset.bulk === 'true' ? 'true' : 'false';
        });

        applyBtn?.addEventListener('click', () => { apply(); });

        cancelBtn?.addEventListener('click', () => {
            if (pageMode || !inModal) revertAll();
            else requestClose();
        });

        okBtn?.addEventListener('click', async () => {
            if (busy) return;
            if (isDirty()) {
                const ok = await apply();
                if (ok && inModal) closeModal();
            } else if (inModal) {
                closeModal();
            }
        });

        refresh();

        return { root, inModal, isDirty, apply, revert: revertAll, requestClose, refresh };
    }

    // ---------- init ----------
    function init() {
        document.querySelectorAll('[data-settings]').forEach(root => {
            if (root.dataset.saveInit === 'true') return;
            root.dataset.saveInit = 'true';
            controllers.push(createController(root));
        });

        const modal = document.getElementById('settingsModal');
        const modalController = controllers.find(c => c.inModal);

        if (modal && modalController) {
            // Backdrop click: only when the press also started on the backdrop,
            // so dragging a text selection out of the dialog doesn't close it.
            let downOnBackdrop = false;
            modal.addEventListener('mousedown', e => { downOnBackdrop = e.target === modal; });
            modal.addEventListener('click', e => {
                if (e.target === modal && downOnBackdrop) modalController.requestClose();
                downOnBackdrop = false;
            });

            document.addEventListener('keydown', e => {
                if (e.key !== 'Escape') return;
                if (modal.classList.contains('hidden')) return;
                // A Toast.confirm dialog owns Esc while it is open.
                if (document.getElementById('toastConfirmOverlay')) return;
                // So does any other modal stacked on top of settings.
                if (document.querySelector('.settings-modal:not(.hidden):not(#settingsModal)')) return;
                modalController.requestClose();
            });
        }

        if (isPageMode()) {
            window.addEventListener('beforeunload', e => {
                if (controllers.some(c => c.isDirty())) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        }

        // bfcache restore can leave controls at stale values; recompute.
        window.addEventListener('pageshow', e => {
            if (e.persisted) controllers.forEach(c => c.refresh());
        });
    }

    window.SettingsSave = {
        isDirty() {
            return controllers.some(c => c.isDirty());
        },
        requestClose() {
            const c = controllers.find(x => x.inModal);
            if (!c) return false;
            c.requestClose();
            return true;
        },
        apply() {
            const c = controllers.find(x => x.inModal) || controllers[0];
            return c ? c.apply() : Promise.resolve(true);
        },
        revert() {
            controllers.forEach(c => c.revert());
        },
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
