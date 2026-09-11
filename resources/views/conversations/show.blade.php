@extends('layouts.app')

@section('title', 'Chat with ' . $otherUser->display_name)
@section('content')

<div class="chat-container">

    <!-- ===== CHAT HEADER ===== -->
    <div class="chat-header-xp">
        <div class="chat-header-left">
            <div class="chat-avatar-xp">
                @if($otherUser->profile && $otherUser->profile->avatar)
                    <img src="{{ asset('storage/' . $otherUser->profile->avatar) }}" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                @else
                    {{ $otherUser->name[0] }}
                @endif
            </div>
            <div>
                <div class="chat-name-xp">{{ $otherUser->display_name }}</div>
                <div class="chat-status-xp" style="color: {{ $otherUser->getStatusColor() }};">
                    {{ $otherUser->getStatusLabel() }}
                </div>
            </div>
        </div>
        <div class="chat-header-right">
            <a href="{{ route('profile.show', $otherUser) }}" class="chat-icon-btn" title="View Profile">
                <svg viewBox="0 0 24 24" fill="none" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 20c0-4 3.6-6 8-6s8 2 8 6"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- ===== AWAY MESSAGE BANNER ===== -->
    <!-- Old AIM-style touch: their status note shows right in the chat,
         not just buried on their profile. -->
    @if($otherUser->profile->status_message ?? null)
        <div class="chat-away-banner">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            <span><strong>{{ $otherUser->display_name }}'s note:</strong> {{ $otherUser->profile->status_message }}</span>
        </div>
    @endif

    <!-- ===== CHAT MESSAGES ===== -->
    <div class="chat-messages-xp" id="chatMessages">

        @php
            $lastDate = null;
            $lastUserId = null;
            $lastTimestamp = null;
            $groupThresholdSeconds = 300; // 5 minutes — matches the JS-side grouping below
        @endphp

        @forelse($messages as $message)
            @php
                $messageDate = $message->created_at->format('Y-m-d');
                $isToday = $message->created_at->isToday();
                $isYesterday = $message->created_at->isYesterday();
                $displayDate = $isToday ? 'Today' : ($isYesterday ? 'Yesterday' : $message->created_at->format('F j, Y'));

                $isNewDateGroup = $lastDate !== $messageDate;
                $isGrouped = !$isNewDateGroup
                    && $lastUserId === $message->user_id
                    && $lastTimestamp !== null
                    && $message->created_at->diffInSeconds($lastTimestamp) <= $groupThresholdSeconds;

                $isMine = $message->user_id === auth()->id();
                $msgReactions = $reactionsByMessage[$message->id] ?? ['counts' => [], 'mine' => null];
            @endphp

            <!-- Date Divider -->
            @if($isNewDateGroup)
                <div class="chat-divider">{{ $displayDate }}</div>
                @php $lastDate = $messageDate; @endphp
            @endif

            <!-- Message -->
            <div class="chat-message-xp @if($isGrouped) chat-message-grouped @endif" data-message-id="{{ $message->id }}" data-user-id="{{ $message->user_id }}">
                @if($isGrouped)
                    <div class="msg-avatar-spacer">
                        <span class="msg-hover-time">{{ $message->created_at->format('g:i A') }}</span>
                    </div>
                @else
                    <div class="msg-avatar-xp">
                        @if($message->user->profile && $message->user->profile->avatar)
                            <img src="{{ asset('storage/' . $message->user->profile->avatar) }}" alt="Avatar" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
                        @else
                            {{ $message->user->name[0] }}
                        @endif
                    </div>
                @endif
                <div class="msg-bubble-xp">
                    @unless($isGrouped)
                        <div class="msg-header-xp">
                            <span class="msg-username-xp">{{ $message->user->display_name }}</span>
                            <span class="msg-time-xp">{{ $message->created_at->format('g:i A') }}</span>
                        </div>
                    @endunless

                    @if($message->is_deleted)
                        <div class="msg-content-xp msg-content-deleted">This message was deleted</div>
                    @elseif($message->type === 'shared_post' && $message->shared_post_id)
                        @php $sharedPost = $message->sharedPost; @endphp
                        <a href="{{ $sharedPost ? route('profile.show', $sharedPost->user) : '#' }}" class="msg-shared-post-card">
                            @if($sharedPost)
                                <div class="msg-shared-post-header">
                                    <div class="msg-shared-post-avatar">
                                        @if($sharedPost->user->getAvatarUrl())
                                            <img src="{{ $sharedPost->user->getAvatarUrl() }}" alt="Avatar" style="width: 20px; height: 20px; border-radius: 50%; object-fit: cover;">
                                        @else
                                            {{ $sharedPost->user->name[0] }}
                                        @endif
                                    </div>
                                    <span class="msg-shared-post-author">{{ $sharedPost->user->display_name }}</span>
                                </div>
                                <div class="msg-shared-post-excerpt">{{ \Illuminate\Support\Str::limit(strip_tags($sharedPost->content ?? ''), 120) }}</div>
                            @else
                                <span class="msg-shared-post-unavailable">This post is no longer available.</span>
                            @endif
                        </a>
                    @else
                        <div class="msg-content-xp">{{ $message->content }}<span class="msg-edited-tag" @if(!$message->edited_at) style="display:none;" @endif>(edited)</span></div>
                        <input type="text" class="msg-edit-input hidden" maxlength="1000" value="{{ $message->content }}">
                    @endif

                    <div class="msg-reactions" @if(empty($msgReactions['counts'])) style="display:none;" @endif>
                        @foreach($msgReactions['counts'] as $type => $count)
                            <button type="button" class="msg-reaction-pill @if($msgReactions['mine'] === $type) msg-reaction-pill-mine @endif" data-reaction-type="{{ $type }}">
                                @include('partials.reaction-icon', ['type' => $type])
                                <span class="msg-reaction-count">{{ $count }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                @if(!$message->is_deleted)
                    <div class="msg-toolbar">
                        <button type="button" class="msg-toolbar-btn msg-react-btn" title="React">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M8 14s1.5 2 4 2 4-2 4-2"/>
                                <line x1="9" y1="9" x2="9.01" y2="9"/>
                                <line x1="15" y1="9" x2="15.01" y2="9"/>
                            </svg>
                        </button>
                        @if($isMine && $message->type !== 'shared_post')
                            <button type="button" class="msg-toolbar-btn msg-edit-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9"/>
                                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                </svg>
                            </button>
                        @endif
                        @if($isMine)
                            <button type="button" class="msg-toolbar-btn msg-delete-btn" title="Delete">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"/>
                                    <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                </svg>
                            </button>
                        @endif
                        <div class="reaction-picker hidden">
                            @foreach(['like', 'love', 'laugh', 'wow', 'sad'] as $type)
                                <button type="button" class="reaction-picker-btn" data-reaction-type="{{ $type }}">
                                    @include('partials.reaction-icon', ['type' => $type])
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            @php
                $lastUserId = $message->user_id;
                $lastTimestamp = $message->created_at;
            @endphp

        @empty
            <div class="chat-empty-xp">
                <div class="chat-empty-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                    </svg>
                </div>
                <div class="chat-empty-text">No messages yet</div>
                <div class="chat-empty-sub">Say hello to {{ $otherUser->display_name }}!</div>
            </div>
        @endforelse

    </div>

    <!-- ===== CHAT INPUT ===== -->
    <div class="chat-input-xp">
        <form action="{{ route('conversations.store', $conversation) }}" method="POST" class="chat-form-xp" id="chatForm">
            @csrf
            <input type="text" name="content" placeholder="Type a message..." class="chat-input-xp-field" id="chatInput" autocomplete="off" maxlength="1000">
            <button type="submit" class="chat-send-xp" id="chatSendBtn">Send</button>
        </form>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatMessages = document.getElementById('chatMessages');
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const chatSendBtn = document.getElementById('chatSendBtn');
        const currentUserId = {{ auth()->id() }};

        const csrfToken = chatForm.querySelector('input[name="_token"]').value;

        // Grouping state carried over from the server-rendered history,
        // so the first polled/sent message picks up where the page load
        // left off.
        let lastMessageUserId = {{ $lastUserId !== null ? $lastUserId : 'null' }};
        let lastMessageTime = {{ $lastTimestamp ? $lastTimestamp->timestamp * 1000 : 'null' }};
        const GROUP_THRESHOLD_MS = 5 * 60 * 1000;

        // Tracks the highest message id we've rendered ("what's new"),
        // and the server's own clock at our last poll ("what's changed"
        // — catches edits/deletes/reactions on messages we already have).
        let lastMessageId = {{ optional($messages->last())->id ?? 0 }};
        let lastPollTime = '{{ now()->toIso8601String() }}';

        const REACTION_TYPES = ['like', 'love', 'laugh', 'wow', 'sad'];
        const REACTION_ICONS = {
            like: '<path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2h0a3.13 3.13 0 0 1 3 3.88Z"/>',
            love: '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>',
            laugh: '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2.5 4 2.5 4-2.5 4-2.5"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>',
            wow: '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="16" r="1.5"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>',
            sad: '<circle cx="12" cy="12" r="10"/><path d="M16 16.5s-1.5-2-4-2-4 2-4 2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>',
        };

        function reactionIconSvg(type) {
            return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">${REACTION_ICONS[type] || ''}</svg>`;
        }

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function sharedPostCardHtml(sharedPost) {
            if (!sharedPost) {
                return `<a href="#" class="msg-shared-post-card"><span class="msg-shared-post-unavailable">This post is no longer available.</span></a>`;
            }
            const avatarInner = sharedPost.author_avatar
                ? `<img src="${sharedPost.author_avatar}" alt="Avatar" style="width:20px;height:20px;border-radius:50%;object-fit:cover;">`
                : escapeHtml((sharedPost.author_name || '?')[0]);
            return `
                <a href="${sharedPost.profile_url}" class="msg-shared-post-card">
                    <div class="msg-shared-post-header">
                        <div class="msg-shared-post-avatar">${avatarInner}</div>
                        <span class="msg-shared-post-author">${escapeHtml(sharedPost.author_name)}</span>
                    </div>
                    <div class="msg-shared-post-excerpt">${escapeHtml(sharedPost.content_excerpt)}</div>
                </a>
            `;
        }

        function isNearBottom(el, threshold = 80) {
            return el.scrollHeight - el.scrollTop - el.clientHeight < threshold;
        }

        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        if (chatMessages) {
            scrollToBottom();
        }

        function formatTime(ms) {
            return new Date(ms).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        }

        function apiHeaders(extra = {}) {
            return Object.assign({
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            }, extra);
        }

        // ===== TOOLBAR / REACTION PICKER / EDIT WIRING =====
        // Delegated on the container so it works for both server-rendered
        // rows AND rows we append later — no need to re-attach listeners
        // every time a message is added.
        chatMessages.addEventListener('click', function(e) {
            const row = e.target.closest('.chat-message-xp');
            if (!row) return;
            const messageId = row.dataset.messageId;

            // Toggle reaction picker
            if (e.target.closest('.msg-react-btn')) {
                const toolbar = row.querySelector('.msg-toolbar');
                const picker = row.querySelector('.reaction-picker');
                const wasOpen = !picker.classList.contains('hidden');

                // Close any other open pickers first
                document.querySelectorAll('.reaction-picker').forEach(p => p.classList.add('hidden'));
                document.querySelectorAll('.msg-toolbar').forEach(t => t.classList.remove('msg-toolbar-active'));

                if (!wasOpen) {
                    picker.classList.remove('hidden');
                    // Keeps the toolbar visible even if the mouse drifts
                    // outside the message row's hover zone while moving
                    // toward the picker (it renders above the row, which
                    // can overlap the previous message on tightly-grouped
                    // messages).
                    toolbar.classList.add('msg-toolbar-active');
                }
                return;
            }

            // Pick a reaction from the picker
            const pickerBtn = e.target.closest('.reaction-picker-btn');
            if (pickerBtn) {
                sendReaction(messageId, pickerBtn.dataset.reactionType);
                pickerBtn.closest('.reaction-picker').classList.add('hidden');
                return;
            }

            // Quick-tap an existing pill to react with that same type
            const pill = e.target.closest('.msg-reaction-pill');
            if (pill) {
                sendReaction(messageId, pill.dataset.reactionType);
                return;
            }

            // Start editing
            if (e.target.closest('.msg-edit-btn')) {
                startEdit(row);
                return;
            }

            // Delete
            if (e.target.closest('.msg-delete-btn')) {
                if (confirm('Delete this message? This can\'t be undone.')) {
                    deleteMessage(row);
                }
                return;
            }
        });

        // Close any open reaction picker when clicking elsewhere
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.msg-react-btn') && !e.target.closest('.reaction-picker')) {
                document.querySelectorAll('.reaction-picker').forEach(p => p.classList.add('hidden'));
            }
        });

        function sendReaction(messageId, type) {
            fetch(`/messages/${messageId}/react`, {
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

                if (!save) {
                    contentEl.classList.remove('hidden');
                    inputEl.classList.add('hidden');
                    return;
                }

                const newContent = inputEl.value.trim();
                if (!newContent) {
                    contentEl.classList.remove('hidden');
                    inputEl.classList.add('hidden');
                    return;
                }

                fetch(`/messages/${row.dataset.messageId}`, {
                    method: 'PATCH',
                    headers: apiHeaders({ 'Content-Type': 'application/json' }),
                    body: JSON.stringify({ content: newContent }),
                })
                    .then(r => r.ok ? r.json() : Promise.reject())
                    .then(data => {
                        patchMessage(data.message);
                        contentEl.classList.remove('hidden');
                        inputEl.classList.add('hidden');
                    })
                    .catch(() => {
                        alert('Could not save that edit. Please try again.');
                        contentEl.classList.remove('hidden');
                        inputEl.classList.add('hidden');
                    });
            }

            function onKeydown(e) {
                if (e.key === 'Enter') { e.preventDefault(); finishEdit(true); }
                if (e.key === 'Escape') { e.preventDefault(); finishEdit(false); }
            }
            function onBlur() { finishEdit(true); }

            inputEl.addEventListener('keydown', onKeydown);
            inputEl.addEventListener('blur', onBlur);
        }

        function deleteMessage(row) {
            fetch(`/messages/${row.dataset.messageId}`, {
                method: 'DELETE',
                headers: apiHeaders(),
            })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(data => patchMessage(data.message))
                .catch(() => alert('Could not delete that message. Please try again.'));
        }

        /**
         * Updates an EXISTING message row in place — used for our own
         * edit/delete/react actions, and for changes polled in from
         * elsewhere. Never touches grouping/avatar layout, since the
         * message's position and author never change.
         */
        function patchMessage(message) {
            const row = chatMessages.querySelector(`[data-message-id="${message.id}"]`);
            if (!row) return;

            const bubble = row.querySelector('.msg-bubble-xp');

            if (message.is_deleted) {
                bubble.querySelector('.msg-content-xp')?.remove();
                bubble.querySelector('.msg-edit-input')?.remove();
                bubble.querySelector('.msg-shared-post-card')?.remove();
                const tombstone = document.createElement('div');
                tombstone.className = 'msg-content-xp msg-content-deleted';
                tombstone.textContent = 'This message was deleted';
                bubble.insertBefore(tombstone, bubble.querySelector('.msg-reactions'));
                row.querySelector('.msg-toolbar')?.remove();
                bubble.querySelector('.msg-reactions')?.remove();
                return;
            }

            const contentEl = bubble.querySelector('.msg-content-xp');
            if (contentEl) {
                contentEl.childNodes[0].textContent = message.content;
                const editedTag = contentEl.querySelector('.msg-edited-tag');
                if (editedTag) {
                    editedTag.style.display = message.edited_at ? '' : 'none';
                }
            }

            renderReactions(row, message.reactions);
        }

        function renderReactions(row, reactions) {
            const container = row.querySelector('.msg-reactions');
            if (!container) return;

            const counts = reactions?.counts || {};
            const mine = reactions?.mine || null;
            const types = Object.keys(counts);

            if (types.length === 0) {
                container.style.display = 'none';
                container.innerHTML = '';
                return;
            }

            container.style.display = 'flex';
            container.innerHTML = types.map(type => `
                <button type="button" class="msg-reaction-pill ${mine === type ? 'msg-reaction-pill-mine' : ''}" data-reaction-type="${type}">
                    ${reactionIconSvg(type)}
                    <span class="msg-reaction-count">${counts[type]}</span>
                </button>
            `).join('');
        }

        /**
         * Appends a BRAND NEW message to the thread, deciding whether it
         * should stack under the previous message (same user, within the
         * group window) or start a new group with its own avatar/name/time.
         */
        function appendMessage(message, isOwnMessage) {
            const timeMs = message.created_at ? new Date(message.created_at).getTime() : Date.now();
            const isGrouped = message.user.id === lastMessageUserId
                && lastMessageTime !== null
                && (timeMs - lastMessageTime) <= GROUP_THRESHOLD_MS;

            const wasNearBottom = isNearBottom(chatMessages);
            const isMine = message.user.id === currentUserId;

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

            const isSharedPost = message.type === 'shared_post';
            const contentHtml = isSharedPost
                ? sharedPostCardHtml(message.shared_post)
                : `<div class="msg-content-xp"></div><input type="text" class="msg-edit-input hidden" maxlength="1000">`;

            row.innerHTML = `
                ${avatarHtml}
                <div class="msg-bubble-xp">
                    ${isGrouped ? '' : `
                        <div class="msg-header-xp">
                            <span class="msg-username-xp">${message.user.display_name || message.user.name}</span>
                            <span class="msg-time-xp">${message.time}</span>
                        </div>
                    `}
                    ${contentHtml}
                    <div class="msg-reactions" style="display:none;"></div>
                </div>
                <div class="msg-toolbar">
                    <button type="button" class="msg-toolbar-btn msg-react-btn" title="React">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>
                        </svg>
                    </button>
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
                // Set via textContent (not innerHTML) so message content
                // can never be interpreted as HTML/JS — avoids an XSS hole.
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

            if (wasNearBottom || isOwnMessage) {
                scrollToBottom();
            }
        }

        // ===== SEND MESSAGE (AJAX, no full page reload) =====
        chatForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const content = chatInput.value.trim();
            if (!content || chatSendBtn.disabled) return;

            chatSendBtn.disabled = true;

            fetch(chatForm.action, {
                method: 'POST',
                headers: apiHeaders({ 'Content-Type': 'application/json' }),
                body: JSON.stringify({ content }),
            })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to send message');
                    return response.json();
                })
                .then(data => {
                    const message = data.message ?? data;
                    appendMessage(message, true);
                    if (message.id) {
                        lastMessageId = message.id;
                    }
                    chatInput.value = '';
                    chatInput.focus();
                })
                .catch(() => {
                    alert('Message failed to send. Please try again.');
                })
                .finally(() => {
                    chatSendBtn.disabled = false;
                });
        });

        chatInput.focus();

        // ===== RECEIVE MESSAGES + CHANGES: POLLING =====
        // Every 3s: "anything brand new since this id, or anything that
        // changed (edit/delete/reaction) since this server timestamp?"
        function pollForUpdates() {
            const params = new URLSearchParams({ after: lastMessageId, since: lastPollTime });

            fetch(`{{ route('conversations.latest', $conversation) }}?${params.toString()}`, {
                headers: { 'Accept': 'application/json' },
            })
                .then(response => response.ok ? response.json() : Promise.reject())
                .then(data => {
                    (data.messages || []).forEach(message => {
                        if (message.is_new) {
                            // Our own sent messages are already appended
                            // optimistically and lastMessageId already
                            // advanced past them, so a "new" message here
                            // is always from someone else.
                            appendMessage(message, false);
                            lastMessageId = message.id;
                        } else {
                            patchMessage(message);
                        }
                    });
                    if (data.server_time) {
                        lastPollTime = data.server_time;
                    }
                })
                .catch(() => {
                    // Silent — just try again next interval.
                });
        }

        setInterval(pollForUpdates, 3000);
    });
</script>
@endpush

@endsection