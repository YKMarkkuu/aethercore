// Heartbeat: proves this tab is open, and tells the server whether the
// person has actually touched anything recently, so idle/offline can be
// computed server-side and shown live to everyone else via the existing
// polling endpoints (now-playing-batch, now-playing).
//
// Also exposes window.PresenceCache — a tiny localStorage wrapper so
// pages can hydrate friend/self presence INSTANTLY on load (before the
// first poll resolves) instead of flashing blank -> fetch -> populate
// on every full-page navigation (the sidebar tabs are real page loads,
// not client-side view swaps).
(function () {
    const CACHE_PREFIX = 'presence:';
    const CACHE_MAX_AGE_MS = 60000; // stale cache past this age is ignored

    window.PresenceCache = {
        get(key) {
            try {
                const raw = localStorage.getItem(CACHE_PREFIX + key);
                if (!raw) return null;
                const parsed = JSON.parse(raw);
                if (Date.now() - parsed.ts > CACHE_MAX_AGE_MS) return null;
                return parsed.value;
            } catch (e) {
                return null;
            }
        },
        set(key, value) {
            try {
                localStorage.setItem(CACHE_PREFIX + key, JSON.stringify({ value, ts: Date.now() }));
            } catch (e) {
                // localStorage full/unavailable — presence just won't
                // pre-hydrate next load, no functional loss.
            }
        },
    };

    const meta = document.querySelector('meta[name="csrf-token"]');
    if (!meta) return;
    const csrfToken = meta.content;

    const HEARTBEAT_INTERVAL_MS = 20000;
    const ACTIVE_WINDOW_MS = 4 * 60 * 1000; // "active" for 4 min after last interaction

    let lastInteraction = Date.now();
    ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'wheel'].forEach(evt => {
        document.addEventListener(evt, () => { lastInteraction = Date.now(); }, { passive: true });
    });
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') lastInteraction = Date.now();
    });

    const LABELS = { online: 'Online', idle: 'Idle', dnd: 'Do Not Disturb', offline: 'Offline' };
    const COLORS = { online: '#4ade80', idle: '#fbbf24', dnd: '#ef4444', offline: '#6b7280' };

    function applyOwnStatus(status) {
        // Only elements that are always "you" — the mini-profile and its
        // popup live in the sidebar on every page and never show anyone
        // else's status.
        document.querySelectorAll('.mini-profile-status, .popup-status').forEach(el => {
            el.textContent = LABELS[status] || 'Online';
            el.style.color = COLORS[status] || COLORS.online;
        });
        window.PresenceCache.set('self:status', status);
    }

    // Hydrate our own status immediately from cache, before the first
    // heartbeat response lands.
    const cachedSelf = window.PresenceCache.get('self:status');
    if (cachedSelf) applyOwnStatus(cachedSelf);

    function sendHeartbeat() {
        const active = document.visibilityState === 'visible'
            && (Date.now() - lastInteraction) < ACTIVE_WINDOW_MS;

        fetch('/presence/heartbeat', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ active }),
            keepalive: true,
        })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(data => applyOwnStatus(data.status))
            .catch(() => { /* silent — try again next interval */ });
    }

    sendHeartbeat();
    setInterval(sendHeartbeat, HEARTBEAT_INTERVAL_MS);

    window.addEventListener('pagehide', function () {
        const data = new FormData();
        data.append('_token', csrfToken);
        navigator.sendBeacon('/presence/offline', data);
    });
})();