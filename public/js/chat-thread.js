// Shared chat-thread engine for BOTH DM chat (conversations/show.blade.php)
// and Space channel chat (spaces/show.blade.php). Handles: send, poll,
// append/patch DOM, edit, delete (tombstone), reactions, and optional
// reply-to threading / shared-post cards behind feature flags. Behavior
// lives ONLY here — a future feature (mentions, pins, whatever) gets
// written once and both surfaces pick it up by turning the flag on.
//
// Usage: window.ChatThread.init({ ...config }) — see spaces/show.blade.php
// or conversations/show.blade.php for a worked example.
(function () {
    const REACTION_TYPES = ['like', 'love', 'laugh', 'wow', 'sad'];
    const REACTION_ICONS = {
        like: '<path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2h0a3.13 3.13 0 0 1 3 3.88Z"/>',
        love: '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
        laugh: '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2.5 4 2.5 4-2.5 4-2.5"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>',
        wow: '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="16" r="1.5"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>',
        sad: '<circle cx="12" cy="12" r="10"/><path d="M16 16.5s-1.5-2-4-2-4 2-4 2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>',
    };
    const GROUP_THRESHOLD_MS = 5 * 60 * 1000;

    function reactionIconSvg(type) {
        return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">${REACTION_ICONS[type] || ''}</svg>`;
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function init(config) {
        const {
            containerId, formId, inputId, sendBtnId,
            currentUserId, endpoints, initialState,
            features = {}, profileUrl, pollIntervalMs = 3000,
        } = config;

        const chatMessages = document.getElementById(containerId);
        const chatForm = document.getElementById(formId);
        const chatInput = document.getElementById(inputId);
        const chatSendBtn = document.getElementById(sendBtnId);
        if (!chatMessages || !chatForm || !chatInput || !chatSendBtn) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        let lastMessageUserId = initialState.lastMessageUserId;
        let lastMessageTime = initialState.lastMessageTime;
        let lastMessageId = initialState.lastMessageId;
        let lastPollTime = initialState.lastPollTime;

        let replyBar, replyBarTarget, replyBarCancel, currentReplyTo = null;
        if (features.reply) {
            replyBar = document.getElementById('replyComposerBar');
            replyBarTarget = document.getElementById('replyComposerTarget');
            replyBarCancel = document.getElementById('replyComposerCancel');
            replyBarCancel?.addEventListener('click', clearReplyTarget);
        }

        function setReplyTarget(id, authorName, excerpt) {
            currentReplyTo = { id, authorName };
            if (replyBarTarget) replyBarTarget.textContent = authorName + (excerpt ? ' — ' + excerpt : '');
            replyBar?.classList.remove('hidden');
            chatInput.focus();
        }
        function clearReplyTarget() {
            currentReplyTo = null;
            replyBar?.classList.add('hidden');
        }

        function apiHeaders(extra = {}) {
            return Object.assign({ 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }, extra);
        }

        function isNearBottom(el, threshold = 80) {
            return el.scrollHeight - el.scrollTop - el.clientHeight < threshold;
        }
        function scrollToBottom() { chatMessages.scrollTop = chatMessages.scrollHeight; }
        scrollToBottom();

        function usernameHtml(message) {
            const name = escapeHtml(message.user.display_name || message.user.name);
            if (profileUrl) {
                return `<a href="${profileUrl(message.user.id)}" class="msg-username-xp" style="text-decoration:none;">${name}</a>`;
            }
            return `<span class="msg-username-xp">${name}</span>`;
        }

        function replyPreviewHtml(replyTo) {
            if (!features.reply || !replyTo) return '';
            if (!replyTo.id) {
                return `<div class="msg-reply-preview"><span class="msg-reply-preview-unavailable">Original message unavailable</span></div>`;
            }
            const text = replyTo.is_deleted ? 'Message was deleted' : (replyTo.content_excerpt || '');
            return `<div class="msg-reply-preview" data-jump-to="${replyTo.id}">
                <span class="msg-reply-preview-author">${escapeHtml(replyTo.author_name)}</span>
                <span class="msg-reply-preview-text">${escapeHtml(text)}</span>
            </div>`;
        }

        function sharedPostCardHtml(sharedPost) {
            if (!sharedPost) {
                return `<a href="#" class="msg-shared-post-card"><span class="msg-shared-post-unavailable">This post is no longer available.</span></a>`;
            }
            const avatarInner = sharedPost.author_avatar
                ? `<img src="${sharedPost.author_avatar}" alt="Avatar" style="width:20px;height:20px;border-radius:50%;object-fit:cover;">`
                : escapeHtml((sharedPost.author_name || '?')[0]);
            return `<a href="${sharedPost.profile_url}" class="msg-shared-post-card">
                <div class="msg-shared-post-header">
                    <div class="msg-shared-post-avatar">${avatarInner}</div>
                    <span class="msg-shared-post-author">${escapeHtml(sharedPost.author_name)}</span>
                </div>
                <div class="msg-shared-post-excerpt">${escapeHtml(sharedPost.content_excerpt)}</div>
            </a>`;
        }

        // ===== toolbar / reaction / edit / delete / reply-jump wiring =====
        chatMessages.addEventListener('click', function (e) {
            if (features.reply) {
                const replyPreview = e.target.closest('.msg-reply-preview');
                if (replyPreview && replyPreview.dataset.jumpTo) {
                    const targetRow = chatMessages.querySelector(`[data-message-id="${replyPreview.dataset.jumpTo}"]`);
                    if (targetRow) {
                        targetRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        targetRow.classList.add('msg-highlight-flash');
                        setTimeout(() => targetRow.classList.remove('msg-highlight-flash'), 1200);
                    }
                    return;
                }
            }

            const row = e.target.closest('.chat-message-xp');
            if (!row) return;
            const messageId = row.dataset.messageId;

            if (e.target.closest('.msg-react-btn')) {
                const toolbar = row.querySelector('.msg-toolbar');
                const picker = row.querySelector('.reaction-picker');
                const wasOpen = !picker.classList.contains('hidden');
                document.querySelectorAll('.reaction-picker').forEach(p => p.classList.add('hidden'));
                document.querySelectorAll('.msg-toolbar').forEach(t => t.classList.remove('msg-toolbar-active'));
                if (!wasOpen) { picker.classList.remove('hidden'); toolbar.classList.add('msg-toolbar-active'); }
                return;
            }

            const pickerBtn = e.target.closest('.reaction-picker-btn');
            if (pickerBtn) {
                sendReaction(messageId, pickerBtn.dataset.reactionType);
                pickerBtn.closest('.reaction-picker').classList.add('hidden');
                return;
            }

            const pill = e.target.closest('.msg-reaction-pill');
            if (pill) { sendReaction(messageId, pill.dataset.reactionType); return; }

            if (features.reply && e.target.closest('.msg-reply-btn')) {
                const usernameEl = row.querySelector('.msg-username-xp');
                const authorName = usernameEl ? usernameEl.textContent : 'them';
                const contentEl = row.querySelector('.msg-content-xp');
                const excerpt = contentEl && contentEl.childNodes[0] ? contentEl.childNodes[0].textContent.slice(0, 60) : '';
                setReplyTarget(messageId, authorName, excerpt);
                return;
            }

            if (e.target.closest('.msg-edit-btn')) { startEdit(row); return; }

            if (e.target.closest('.msg-delete-btn')) {
                if (confirm('Delete this message? This can\'t be undone.')) deleteMessage(row);
                return;
            }
        });

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.msg-react-btn') && !e.target.closest('.reaction-picker')) {
                document.querySelectorAll('.reaction-picker').forEach(p => p.classList.add('hidden'));
            }
        });

        function sendReaction(messageId, type) {
            fetch(endpoints.react(messageId), {
                method: 'POST',
                headers: apiHeaders({ 'Content-Type': 'application/json' }),
                body: JSON.stringify({ type }),
            })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(data => patchMessage(data.message))
                .catch(() => alert('Could not react to that message. Please try again.'));
        }

        function startEdit(row) {
            const contentEl = row.querySelector('.msg-content-xp');
            const inputEl = row.querySelector('.msg-edit-input');
            if (!contentEl || !inputEl) return;
            contentEl.classList.add('hidden');
            inputEl.classList.remove('hidden');
            inputEl.value = contentEl.childNodes[0] ? contentEl.childNodes[0].textContent : '';
            inputEl.focus();
            inputEl.setSelectionRange(inputEl.value.length, inputEl.value.length);

            function finishEdit(save) {
                inputEl.removeEventListener('keydown', onKeydown);
                inputEl.removeEventListener('blur', onBlur);
                if (!save) { contentEl.classList.remove('hidden'); inputEl.classList.add('hidden'); return; }
                const newContent = inputEl.value.trim();
                if (!newContent) { contentEl.classList.remove('hidden'); inputEl.classList.add('hidden'); return; }

                fetch(endpoints.update(row.dataset.messageId), {
                    method: 'PATCH',
                    headers: apiHeaders({ 'Content-Type': 'application/json' }),
                    body: JSON.stringify({ content: newContent }),
                })
                    .then(r => r.ok ? r.json() : Promise.reject())
                    .then(data => { patchMessage(data.message); contentEl.classList.remove('hidden'); inputEl.classList.add('hidden'); })
                    .catch(() => { alert('Could not save that edit. Please try again.'); contentEl.classList.remove('hidden'); inputEl.classList.add('hidden'); });
            }
            function onKeydown(e) { if (e.key === 'Enter') { e.preventDefault(); finishEdit(true); } if (e.key === 'Escape') { e.preventDefault(); finishEdit(false); } }
            function onBlur() { finishEdit(true); }
            inputEl.addEventListener('keydown', onKeydown);
            inputEl.addEventListener('blur', onBlur);
        }

        function deleteMessage(row) {
            fetch(endpoints.destroy(row.dataset.messageId), { method: 'DELETE', headers: apiHeaders() })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(data => patchMessage(data.message))
                .catch(() => alert('Could not delete that message. Please try again.'));
        }

        function patchMessage(message) {
            const row = chatMessages.querySelector(`[data-message-id="${message.id}"]`);
            if (!row) return;
            const bubble = row.querySelector('.msg-bubble-xp');

            if (message.is_deleted) {
                bubble.querySelector('.msg-content-xp')?.remove();
                bubble.querySelector('.msg-edit-input')?.remove();
                bubble.querySelector('.msg-shared-post-card')?.remove();
                bubble.querySelector('.msg-reply-preview')?.remove();
                const tombstone = document.createElement('div');
                tombstone.className = 'msg-content-xp msg-content-deleted';
                tombstone.textContent = 'This message was deleted';
                bubble.insertBefore(tombstone, bubble.querySelector('.msg-reactions'));
                row.querySelector('.msg-toolbar')?.remove();
                bubble.querySelector('.msg-reactions')?.remove();
                return;
            }

            const contentEl = bubble.querySelector('.msg-content-xp');
            if (contentEl && contentEl.childNodes[0]) {
                contentEl.childNodes[0].textContent = message.content;
                const editedTag = contentEl.querySelector('.msg-edited-tag');
                if (editedTag) editedTag.style.display = message.edited_at ? '' : 'none';
            }

            renderReactions(row, message.reactions);
        }

        function renderReactions(row, reactions) {
            const container = row.querySelector('.msg-reactions');
            if (!container) return;
            const counts = reactions?.counts || {};
            const mine = reactions?.mine || null;
            const types = Object.keys(counts);
            if (types.length === 0) { container.style.display = 'none'; container.innerHTML = ''; return; }
            container.style.display = 'flex';
            container.innerHTML = types.map(type => `
                <button type="button" class="msg-reaction-pill ${mine === type ? 'msg-reaction-pill-mine' : ''}" data-reaction-type="${type}">
                    ${reactionIconSvg(type)}
                    <span class="msg-reaction-count">${counts[type]}</span>
                </button>`).join('');
        }

        function appendMessage(message, isOwnMessage) {
            const timeMs = message.created_at ? new Date(message.created_at).getTime() : Date.now();
            const isGrouped = message.user.id === lastMessageUserId
                && lastMessageTime !== null
                && (timeMs - lastMessageTime) <= GROUP_THRESHOLD_MS
                && !(features.reply && message.reply_to); // a reply always starts its own group

            const wasNearBottom = isNearBottom(chatMessages);
            const isMine = message.user.id === currentUserId;
            const isSharedPost = features.sharedPost && message.type === 'shared_post';

            const row = document.createElement('div');
            row.className = 'chat-message-xp' + (isGrouped ? ' chat-message-grouped' : '');
            row.dataset.messageId = message.id;
            row.dataset.userId = message.user.id;

            let avatarHtml;
            if (isGrouped) {
                avatarHtml = `<div class="msg-avatar-spacer"><span class="msg-hover-time">${message.time}</span></div>`;
            } else if (message.user.avatar_url) {
                avatarHtml = `<div class="msg-avatar-xp"><img src="${message.user.avatar_url}" alt="Avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;"></div>`;
            } else {
                avatarHtml = `<div class="msg-avatar-xp">${(message.user.display_name || message.user.name || '?')[0]}</div>`;
            }

            const pickerButtonsHtml = REACTION_TYPES.map(type =>
                `<button type="button" class="reaction-picker-btn" data-reaction-type="${type}">${reactionIconSvg(type)}</button>`
            ).join('');

            const contentHtml = isSharedPost
                ? sharedPostCardHtml(message.shared_post)
                : `${replyPreviewHtml(message.reply_to)}<div class="msg-content-xp"></div><input type="text" class="msg-edit-input hidden" maxlength="1000">`;

            row.innerHTML = `
                ${avatarHtml}
                <div class="msg-bubble-xp">
                    ${isGrouped ? '' : `<div class="msg-header-xp">${usernameHtml(message)}<span class="msg-time-xp">${message.time}</span></div>`}
                    ${contentHtml}
                    <div class="msg-reactions" style="display:none;"></div>
                </div>
                <div class="msg-toolbar">
                    <button type="button" class="msg-toolbar-btn msg-react-btn" title="React">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>
                        </svg>
                    </button>
                    ${features.reply ? `
                    <button type="button" class="msg-toolbar-btn msg-reply-btn" title="Reply">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/>
                        </svg>
                    </button>` : ''}
                    ${isMine && !isSharedPost ? `
                        <button type="button" class="msg-toolbar-btn msg-edit-btn" title="Edit">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                            </svg>
                        </button>
                    ` : ''}
                    ${isMine ? `
                        <button type="button" class="msg-toolbar-btn msg-delete-btn" title="Delete">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                            </svg>
                        </button>
                    ` : ''}
                    <div class="reaction-picker hidden">${pickerButtonsHtml}</div>
                </div>
            `;

            if (!isSharedPost) {
                row.querySelector('.msg-content-xp').append(document.createTextNode(message.content || ''));
                const editedTag = document.createElement('span');
                editedTag.className = 'msg-edited-tag';
                editedTag.textContent = '(edited)';
                editedTag.style.display = message.edited_at ? '' : 'none';
                row.querySelector('.msg-content-xp').appendChild(editedTag);
                row.querySelector('.msg-edit-input').value = message.content || '';
            }

            chatMessages.appendChild(row);
            renderReactions(row, message.reactions);

            lastMessageUserId = message.user.id;
            lastMessageTime = timeMs;

            if (wasNearBottom || isOwnMessage) scrollToBottom();
        }

        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const content = chatInput.value.trim();
            if (!content || chatSendBtn.disabled) return;
            chatSendBtn.disabled = true;

            const body = { content };
            if (features.reply && currentReplyTo) body.reply_to_id = currentReplyTo.id;

            fetch(endpoints.store, {
                method: 'POST',
                headers: apiHeaders({ 'Content-Type': 'application/json' }),
                body: JSON.stringify(body),
            })
                .then(r => { if (!r.ok) throw new Error(); return r.json(); })
                .then(data => {
                    const message = data.message ?? data;
                    appendMessage(message, true);
                    if (message.id) lastMessageId = message.id;
                    chatInput.value = '';
                    chatInput.focus();
                    if (features.reply) clearReplyTarget();
                })
                .catch(() => alert('Message failed to send. Please try again.'))
                .finally(() => { chatSendBtn.disabled = false; });
        });

        chatInput.focus();

        function pollForUpdates() {
            const params = new URLSearchParams({ after: lastMessageId, since: lastPollTime });
            fetch(`${endpoints.latest}?${params.toString()}`, { headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(data => {
                    (data.messages || []).forEach(message => {
                        if (message.is_new) { appendMessage(message, false); lastMessageId = message.id; }
                        else { patchMessage(message); }
                    });
                    if (data.server_time) lastPollTime = data.server_time;
                })
                .catch(() => {});
        }

        setInterval(pollForUpdates, pollIntervalMs);

        return { appendMessage, patchMessage };
    }

    window.ChatThread = { init };
})();