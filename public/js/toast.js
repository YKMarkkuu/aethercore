// Minimal toast stack — replaces the "flash message after reload" pattern
// (session('success')/session('error') banners) now that most actions
// don't reload the page anymore. Used by ajax-forms.js and can be called
// directly: window.Toast.show('Saved!', 'success').
(function () {
    let stack;

    function style() {
        if (document.getElementById('toastStyles')) return;
        const tag = document.createElement('style');
        tag.id = 'toastStyles';
        tag.textContent = `
            #toastStack { position: fixed; bottom: 16px; right: 16px; z-index: 100000;
                display: flex; flex-direction: column; gap: 8px; max-width: 90vw; }
            .xp-toast { padding: 0.5rem 0.9rem; border-radius: 4px; font-size: 0.8rem;
                box-shadow: 2px 2px 8px rgba(0,0,0,0.2); max-width: 300px;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                opacity: 0; transform: translateY(6px); transition: opacity .15s ease, transform .15s ease; }
            .xp-toast.xp-toast-in { opacity: 1; transform: translateY(0); }
            .xp-toast-success { background: #d4e8d4; border: 2px solid #8ab88a; color: #1e4a1e; }
            .xp-toast-error { background: #f0d8d8; border: 2px solid #c8a0a0; color: #6a2a2a; }
            #toastConfirmOverlay { position: fixed; inset: 0; z-index: 100001;
                background: rgba(0,0,0,0.35); display: flex; align-items: center;
                justify-content: center; opacity: 0; transition: opacity .12s ease; }
            #toastConfirmOverlay.xp-confirm-in { opacity: 1; }
            .xp-confirm-dialog { min-width: 320px; max-width: 480px; width: 90vw;
                background: #f0edd8; border: 2px solid #1a4a9e;
                box-shadow: 3px 3px 0 rgba(0,0,0,0.3), 0 0 0 2px #ffffff inset;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                transform: scale(0.96); transition: transform .12s ease; }
            .xp-confirm-dialog.xp-confirm-in { transform: scale(1); }
            .xp-confirm-titlebar { background: linear-gradient(180deg, #245edb 0%, #3a7bd5 50%, #1a4a9e 100%);
                color: #ffffff; padding: 0.35rem 0.6rem; font-size: 0.75rem; font-weight: 700;
                display: flex; align-items: center; justify-content: space-between; }
            .xp-confirm-titlebar-title { display: flex; align-items: center; gap: 0.4rem; }
            .xp-confirm-body { padding: 0.9rem 1rem; font-size: 0.8rem;
                color: #1e1e1e; line-height: 1.4; }
            .xp-confirm-actions { padding: 0 1rem 0.9rem; display: flex;
                justify-content: flex-end; gap: 0.4rem; }
            .xp-confirm-btn { padding: 0.3rem 0.9rem; font-size: 0.75rem;
                font-family: inherit; font-weight: 600; cursor: pointer;
                border: 2px solid #b0a8a0; background: linear-gradient(180deg, #f0edd8 0%, #d4d0c8 100%);
                color: #1e1e1e; box-shadow: inset 1px 1px 0 rgba(255,255,255,0.5); }
            .xp-confirm-btn:hover { background: linear-gradient(180deg, #e0dcd0 0%, #c8c4bc 100%); }
            .xp-confirm-btn-danger { border-color: #c8a0a0;
                background: linear-gradient(180deg, #f0d8d8 0%, #e0c0c0 100%); color: #6a2a2a; }
            .xp-confirm-btn-danger:hover { background: linear-gradient(180deg, #e8d0d0 0%, #d8b0b0 100%); }
        `;
        document.head.appendChild(tag);
    }

    function ensureStack() {
        if (stack) return stack;
        style();
        stack = document.createElement('div');
        stack.id = 'toastStack';
        document.body.appendChild(stack);
        return stack;
    }

    function show(message, type) {
        if (!message) return;
        const el = document.createElement('div');
        el.className = 'xp-toast xp-toast-' + (type === 'error' ? 'error' : 'success');
        el.textContent = message;
        ensureStack().appendChild(el);
        requestAnimationFrame(() => el.classList.add('xp-toast-in'));

        setTimeout(() => {
            el.classList.remove('xp-toast-in');
            setTimeout(() => el.remove(), 200);
        }, 3200);
    }

    function confirmDialog(message, options) {
        options = options || {};
        return new Promise((resolve) => {
            style();
            const overlay = document.createElement('div');
            overlay.id = 'toastConfirmOverlay';
            overlay.setAttribute('role', 'dialog');
            overlay.setAttribute('aria-modal', 'true');

            const dialog = document.createElement('div');
            dialog.className = 'xp-confirm-dialog';
            dialog.setAttribute('tabindex', '-1');

            const titlebar = document.createElement('div');
            titlebar.className = 'xp-confirm-titlebar';
            const titleText = document.createElement('span');
            titleText.className = 'xp-confirm-titlebar-title';
            titleText.textContent = options.title || 'AetherCore';
            titlebar.appendChild(titleText);

            const body = document.createElement('div');
            body.className = 'xp-confirm-body';
            body.textContent = message;

            const actions = document.createElement('div');
            actions.className = 'xp-confirm-actions';

            const cancelBtn = document.createElement('button');
            cancelBtn.type = 'button';
            cancelBtn.className = 'xp-confirm-btn';
            cancelBtn.textContent = options.cancelText || 'Cancel';

            const okBtn = document.createElement('button');
            okBtn.type = 'button';
            okBtn.className = 'xp-confirm-btn' + (options.danger ? ' xp-confirm-btn-danger' : '');
            okBtn.textContent = options.confirmText || (options.danger ? 'Delete' : 'OK');

            actions.appendChild(cancelBtn);
            actions.appendChild(okBtn);
            dialog.appendChild(titlebar);
            dialog.appendChild(body);
            dialog.appendChild(actions);
            overlay.appendChild(dialog);
            document.body.appendChild(overlay);

            requestAnimationFrame(() => {
                overlay.classList.add('xp-confirm-in');
                dialog.classList.add('xp-confirm-in');
                okBtn.focus();
            });

            function close(result) {
                overlay.classList.remove('xp-confirm-in');
                dialog.classList.remove('xp-confirm-in');
                document.removeEventListener('keydown', onKey);
                setTimeout(() => { overlay.remove(); resolve(result); }, 130);
            }

            function onKey(e) {
                if (e.key === 'Escape') { e.preventDefault(); close(false); }
                else if (e.key === 'Enter' && document.activeElement === okBtn) {
                    e.preventDefault(); close(true);
                }
            }

            okBtn.addEventListener('click', () => close(true));
            cancelBtn.addEventListener('click', () => close(false));
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) close(false);
            });
            document.addEventListener('keydown', onKey);
        });
    }

    window.Toast = { show, confirm: confirmDialog };

    // Global handler: any <form data-confirm="Message"> intercepts its own
    // submission, shows the XP confirm dialog, and submits only on OK.
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form.matches('form[data-confirm]')) return;
        if (form.dataset.confirmed === 'true') return;

        e.preventDefault();
        const danger = form.dataset.confirmDanger === 'true';
        window.Toast.confirm(form.dataset.confirm, {
            danger: danger,
            title: form.dataset.confirmTitle || 'Confirm',
            confirmText: form.dataset.confirmOk,
            cancelText: form.dataset.confirmCancel,
        }).then((ok) => {
            if (!ok) return;
            form.dataset.confirmed = 'true';
            form.submit();
        });
    }, true);
})();
