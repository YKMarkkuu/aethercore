// Shared report-modal engine. Any "Report" button anywhere on the page
// calls window.openReportModal(type, id) — one copy of the modal (see
// partials.report-modal), reused everywhere, same pattern as the Share
// and Create-Space modals. Loaded on every page (see app.blade.php);
// inert if #reportModal / #reportForm aren't present.
(function () {
    let currentType = null;
    let currentId = null;

    window.openReportModal = function (type, id) {
        currentType = type;
        currentId = id;
        const form = document.getElementById('reportForm');
        if (form) form.reset();
        document.getElementById('reportModal')?.classList.remove('hidden');
    };

    window.closeReportModal = function () {
        document.getElementById('reportModal')?.classList.add('hidden');
        currentType = null;
        currentId = null;
    };

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('reportForm');
        if (!form) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!currentType || !currentId) return;

            const reason = form.querySelector('input[name="reason"]:checked')?.value;
            if (!reason) return;

            const submitBtn = document.getElementById('reportSubmitBtn');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            fetch('/reports', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    reportable_type: currentType,
                    reportable_id: currentId,
                    reason: reason,
                    details: document.getElementById('reportDetails').value.trim(),
                }),
            })
                .then(r => r.ok ? r.json() : r.json().then(data => Promise.reject(data)))
                .then(() => {
                    window.closeReportModal();
                    alert('Thanks — this has been reported to the team.');
                })
                .catch((err) => {
                    alert(err?.error || 'Could not submit that report. Please try again.');
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Report';
                });
        });
    });
})();
