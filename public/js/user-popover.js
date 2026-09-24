// Discord-style mini profile popover. Any element with
// data-user-popover="{userId}" opens this instead of navigating
// straight to the profile — clicking the avatar inside, or "View Full
// Profile", is what actually goes there. Loaded on every page (see
// app.blade.php); inert if #userPopover isn't present.
(function () {
    let openForUserId = null;
    let pollTimer = null;

    function el() { return document.getElementById('userPopover'); }

    function close() {
        el()?.classList.add('hidden');
        openForUserId = null;
        clearInterval(pollTimer);
        pollTimer = null;
    }

    function position(anchor) {
        const card = el();
        card.style.visibility = 'hidden';
        card.classList.remove('hidden');

        const rect = anchor.getBoundingClientRect();
        const cardRect = card.getBoundingClientRect();

        let left = rect.left;
        let top = rect.bottom + 8;

        if (left + cardRect.width > window.innerWidth - 12) {
            left = Math.max(12, window.innerWidth - cardRect.width - 12);
        }
        if (top + cardRect.height > window.innerHeight - 12) {
            top = rect.top - cardRect.height - 8;
        }
        if (top < 12) top = 12;

        card.style.left = left + 'px';
        card.style.top = top + 'px';
        card.style.visibility = 'visible';
    }

    function escapeHtml(str) {
        const d = document.createElement('div');
        d.textContent = str ?? '';
        return d.innerHTML;
    }

    function buildActions(data) {
        if (data.is_self) {
            return `<a href="${data.profile_url}" class="user-popover-btn user-popover-btn-primary">View Full Profile</a>`;
        }

        const rows = [];
        if (data.is_blocking) {
            rows.push(`<button type="button" class="user-popover-btn" data-action="unblock" data-user="${data.id}">Unblock</button>`);
        } else if (data.is_friend) {
            rows.push(`<a href="/conversations/start/${data.id}" class="user-popover-btn user-popover-btn-primary">Message</a>`);
        } else if (data.request_sent) {
            rows.push(`<button type="button" class="user-popover-btn" disabled>Request Sent</button>`);
        } else if (data.request_received) {
            rows.push(`<button type="button" class="user-popover-btn user-popover-btn-primary" data-action="accept" data-user="${data.id}">Accept Request</button>`);
        } else {
            rows.push(`<button type="button" class="user-popover-btn user-popover-btn-primary" data-action="add-friend" data-user="${data.id}">Add Friend</button>`);
        }
        rows.push(`<a href="${data.profile_url}" class="user-popover-btn">View Full Profile</a>`);
        return rows.join('');
    }

    function renderStatusOnly(data) {
        const card = el();
        const statusEl = card.querySelector('#userPopoverStatus');
        if (statusEl) {
            statusEl.innerHTML = `<span class="status-dot-mini status-dot-${data.status}"></span>${escapeHtml(data.status_label)}`;
        }
    }

    function render(data) {
        const card = el();
        card.querySelector('#userPopoverBanner').style.backgroundImage =
            data.banner_url ? `url('${data.banner_url}')` : 'none';

        const avatarLink = card.querySelector('#userPopoverAvatarLink');
        avatarLink.href = data.profile_url;
        card.querySelector('#userPopoverAvatar').innerHTML = data.avatar_url
            ? `<img src="${data.avatar_url}" alt="Avatar">`
            : `<span>${escapeHtml((data.display_name || '?')[0])}</span>`;

        card.querySelector('#userPopoverName').textContent = data.display_name;
        card.querySelector('#userPopoverUsername').textContent = '@' + data.username;
        renderStatusOnly(data);

        const note = card.querySelector('#userPopoverNote');
        if (data.status_message) {
            note.textContent = '"' + data.status_message + '"';
            note.classList.remove('hidden');
        } else {
            note.classList.add('hidden');
        }

        const bio = card.querySelector('#userPopoverBio');
        bio.textContent = data.bio || '';
        bio.classList.toggle('hidden', !data.bio);

        card.querySelector('#userPopoverActions').innerHTML = buildActions(data);
    }

    function open(userId, anchor) {
        if (openForUserId === userId && !el().classList.contains('hidden')) {
            close();
            return;
        }
        openForUserId = userId;
        clearInterval(pollTimer);

        fetch(`/users/${userId}/popover`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(data => {
                if (openForUserId !== userId) return;
                render(data);
                position(anchor);

                // Cheap: only re-fetch status while the card stays open,
                // rather than the full popover payload (banner/bio/etc
                // don't change on a 15s cadence).
                pollTimer = setInterval(() => {
                    fetch(`/users/${userId}/popover`, { headers: { 'Accept': 'application/json' } })
                        .then(r => r.ok ? r.json() : Promise.reject())
                        .then(fresh => { if (openForUserId === userId) renderStatusOnly(fresh); })
                        .catch(() => {});
                }, 15000);
            })
            .catch(close);
    }

    function handleAction(btn) {
        const userId = btn.dataset.user;
        const action = btn.dataset.action;
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const routes = {
            'add-friend': `/friends/request/${userId}`,
            'accept': `/friends/accept/${userId}`,
            'unblock': `/users/${userId}/block`,
        };
        const anchor = document.querySelector(`[data-user-popover="${userId}"]`);

        btn.disabled = true;
        fetch(routes[action], {
            method: action === 'unblock' ? 'DELETE' : 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        })
            .then(() => open(userId, anchor || el()))
            .catch(() => { btn.disabled = false; });
    }

    document.addEventListener('click', function (e) {
        const trigger = e.target.closest('[data-user-popover]');
        if (trigger) {
            e.preventDefault();
            open(trigger.dataset.userPopover, trigger);
            return;
        }
        const actionBtn = e.target.closest('#userPopover [data-action]');
        if (actionBtn) { handleAction(actionBtn); return; }
        if (e.target.closest('#userPopoverClose')) { close(); return; }
        if (!e.target.closest('#userPopover')) close();
    });

    window.addEventListener('scroll', () => { if (openForUserId) close(); }, true);
})();
