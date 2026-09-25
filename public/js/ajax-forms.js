// ============================================================
// SITE-WIDE AJAX FORM ENGINE
// ============================================================
// Any plain <form method="POST|PUT|PATCH|DELETE"> on any page now submits
// over fetch instead of doing a full navigation — IF the controller it
// posts to responds with JSON (i.e. it checks $request->wantsJson()).
//
// This is deliberately a progressive enhancement, not a rewrite:
//   - Forms already handled by dedicated JS (chat, comments, reports,
//     share/create-space modals) are left completely alone, because
//     those scripts call e.preventDefault() first and this script bails
//     out the moment it sees that.
//   - A controller that hasn't been updated yet just keeps returning a
//     redirect/HTML response — this script detects that (no JSON content
//     type) and falls back to a normal navigation, so behavior NEVER
//     regresses while controllers get updated one at a time.
//   - A submit button is disabled for the duration of its own request,
//     which is the client-side half of the abuse safety net (the
//     `actions` rate limiter on the server is the other half).
//
// Server response contract — every field is optional, mix and match:
//   {
//     "message": "Friend request sent!",   // shown as a toast
//     "type": "success" | "error",          // toast color, default success
//     "redirect": "/spaces/12",             // full navigation
//     "remove": true,                       // removes the nearest
//                                            // [data-ajax-item] ancestor
//                                            // of the form (e.g. delete
//                                            // a row/card without reload)
//     "html": "<div>...</div>"              // replaces the innerHTML of
//                                            // the nearest [data-ajax-target]
//                                            // ancestor, or the element
//                                            // matching data-ajax-target
//                                            // on the form itself
//   }
//
// Opt a specific form out entirely with data-no-ajax.
// ============================================================
(function () {
    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content;
    }

    function setBusy(form, busy) {
        const submitBtn = form.querySelector('[type="submit"]');
        if (submitBtn) submitBtn.disabled = busy;
        form.classList.toggle('ajax-busy', busy);
    }

    document.addEventListener('submit', function (e) {
        // Another script already owns this form (chat, comments, reports,
        // repost/share modals, etc.) — do nothing.
        if (e.defaultPrevented) return;

        const form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (form.hasAttribute('data-no-ajax')) return;
        if (form.target) return;

        const spoofed = form.querySelector('input[name="_method"]')?.value;
        const method = (spoofed || form.method || 'GET').toUpperCase();
        if (method === 'GET') return; // search/filter forms navigate normally

        e.preventDefault();

        const formData = new FormData(form);
        setBusy(form, true);

        fetch(form.getAttribute('action') || window.location.href, {
            method: form.method.toUpperCase() === 'GET' ? 'POST' : form.method,
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
        })
            .then(async (response) => {
                const contentType = response.headers.get('content-type') || '';

                if (!contentType.includes('application/json')) {
                    // Controller doesn't support JSON here yet — behave
                    // exactly like a normal form submit always has.
                    window.location.href = response.url || window.location.href;
                    return;
                }

                const data = await response.json().catch(() => ({}));

                if (!response.ok) {
                    const msg = data.message
                        || (data.errors ? Object.values(data.errors).flat().join(' ') : null)
                        || 'Something went wrong. Please try again.';
                    window.Toast?.show(msg, 'error');
                    return;
                }

                if (data.message) {
                    window.Toast?.show(data.message, data.type || 'success');
                }

                if (data.remove) {
                    form.closest('[data-ajax-item]')?.remove();
                }

                if (data.html) {
                    const target = form.closest('[data-ajax-target]')
                        || (form.dataset.ajaxTarget ? document.querySelector(form.dataset.ajaxTarget) : null);
                    if (target) target.innerHTML = data.html;
                }

                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }

                if (!data.remove && !data.html && form.dataset.ajaxReset !== 'false') {
                    form.reset();
                }
            })
            .catch(() => {
                window.Toast?.show('Network error — please try again.', 'error');
            })
            .finally(() => setBusy(form, false));
    });
})();
