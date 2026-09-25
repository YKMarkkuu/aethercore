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

    window.Toast = { show };
})();
