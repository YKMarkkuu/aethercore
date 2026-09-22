// Shared friend-presence poller: hydrates instantly from cache, then
// polls now-playing-batch and patches both the DOM and the cache.
// Used by the sidebar friends list AND the profile page's Top 8 Friends
// panel — previously each page hand-rolled its own near-identical copy
// of this fetch/patch logic.
(function () {
    function hydrateRow(row, cached) {
        if (!cached) return;
        const statusEl = row.querySelector('.friend-status');
        const dotEl = row.querySelector('.status-dot');
        if (statusEl && cached.label) {
            statusEl.textContent = cached.label;
            statusEl.style.color = cached.color;
        }
        if (dotEl && cached.status) dotEl.className = 'status-dot ' + cached.status;
        if (cached.status) row.dataset.status = cached.status;

        const npEl = row.querySelector('.friend-now-playing');
        const npText = row.querySelector('.friend-now-playing-text');
        if (npEl && npText) {
            if (cached.nowPlaying) {
                npText.textContent = cached.nowPlaying;
                npEl.style.display = 'flex';
            } else {
                npEl.style.display = 'none';
            }
        }
    }

    function initFriendPresence(listId, options) {
        const list = document.getElementById(listId);
        if (!list) return;

        const rows = Array.from(list.querySelectorAll('[data-friend-id]'));
        if (rows.length === 0) return;

        const ids = rows.map(row => row.dataset.friendId);
        const rank = { online: 0, idle: 1, dnd: 2, offline: 3 };
        const reorder = options && options.reorder;

        // Hydrate immediately from whatever we last knew, before the
        // network round trip, so switching pages/tabs never shows a
        // blank -> populate flash.
        rows.forEach(row => {
            hydrateRow(row, window.PresenceCache.get('friend:' + row.dataset.friendId));
        });
        if (reorder) {
            rows.slice().sort((a, b) => (rank[a.dataset.status] ?? 0) - (rank[b.dataset.status] ?? 0))
                .forEach(row => list.appendChild(row));
        }

        function poll() {
            const params = new URLSearchParams();
            ids.forEach(id => params.append('ids[]', id));

            fetch('/now-playing-batch?' + params.toString(), { headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(data => {
                    const nowPlaying = data.now_playing || {};
                    const status = data.status || {};

                    rows.forEach(row => {
                        const id = row.dataset.friendId;
                        const s = status[id];
                        const np = nowPlaying[id] ? (nowPlaying[id].name + ' — ' + nowPlaying[id].artist) : null;

                        hydrateRow(row, {
                            status: s ? s.status : row.dataset.status,
                            label: s ? s.label : null,
                            color: s ? s.color : null,
                            nowPlaying: np,
                        });

                        window.PresenceCache.set('friend:' + id, {
                            status: s ? s.status : row.dataset.status,
                            label: s ? s.label : undefined,
                            color: s ? s.color : undefined,
                            nowPlaying: np,
                        });
                    });

                    if (reorder) {
                        rows.slice().sort((a, b) => (rank[a.dataset.status] ?? 0) - (rank[b.dataset.status] ?? 0))
                            .forEach(row => list.appendChild(row));
                    }
                })
                .catch(() => { /* silent — try again next interval */ });
        }

        poll();
        setInterval(poll, (options && options.intervalMs) || 15000);
    }

    window.PresenceFriends = { init: initFriendPresence };
})();