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
            @endphp

            <!-- Date Divider -->
            @if($isNewDateGroup)
                <div class="chat-divider">{{ $displayDate }}</div>
                @php $lastDate = $messageDate; @endphp
            @endif

            <!-- Message -->
            <div class="chat-message-xp @if($isGrouped) chat-message-grouped @endif">
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
                    <div class="msg-content-xp">{{ $message->content }}</div>
                </div>
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
            <input type="text" name="content" placeholder="Type a message..." class="chat-input-xp-field" id="chatInput" autocomplete="off">
            <button type="submit" class="chat-send-xp" id="chatSendBtn">Send</button>
        </form>
    </div>

</div>

@push('scripts')
<script src="https://js.pusher.com/7.2/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const chatMessages = document.getElementById('chatMessages');
        const chatForm = document.getElementById('chatForm');
        const chatInput = document.getElementById('chatInput');
        const chatSendBtn = document.getElementById('chatSendBtn');
        const currentUserId = {{ auth()->id() }};

        // Grouping state carried over from the server-rendered history,
        // so the first live message picks up where the page load left off.
        let lastMessageUserId = {{ $lastUserId !== null ? $lastUserId : 'null' }};
        let lastMessageTime = {{ $lastTimestamp ? $lastTimestamp->timestamp * 1000 : 'null' }};
        const GROUP_THRESHOLD_MS = 5 * 60 * 1000;

        function isNearBottom(el, threshold = 80) {
            return el.scrollHeight - el.scrollTop - el.clientHeight < threshold;
        }

        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Auto-scroll to bottom on page load
        if (chatMessages) {
            scrollToBottom();
        }

        function formatTime(ms) {
            return new Date(ms).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
        }

        /**
         * Appends a message to the thread, deciding whether it should
         * stack under the previous message (same user, within the group
         * window) or start a new group with its own avatar/name/time.
         *
         * NOTE: for messages arriving live over the socket, grouping only
         * checks "same user as last" rather than the full time-window
         * check the initial page load does — the broadcast payload
         * doesn't currently include a raw timestamp to compare against,
         * only a pre-formatted time string. Close enough for a single
         * open session; ask me to wire through the raw timestamp in the
         * broadcast event if you want full parity.
         */
        function appendMessage({ userId, name, avatarInitial, avatarUrl, time, timeMs, content }, isOwnMessage) {
            const isGrouped = userId === lastMessageUserId
                && lastMessageTime !== null
                && timeMs !== undefined
                && (timeMs - lastMessageTime) <= GROUP_THRESHOLD_MS;

            const wasNearBottom = isNearBottom(chatMessages);

            const row = document.createElement('div');
            row.className = 'chat-message-xp' + (isGrouped ? ' chat-message-grouped' : '');

            let avatarHtml;
            if (isGrouped) {
                avatarHtml = `<div class="msg-avatar-spacer"><span class="msg-hover-time">${time}</span></div>`;
            } else if (avatarUrl) {
                avatarHtml = `<div class="msg-avatar-xp"><img src="${avatarUrl}" alt="Avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;"></div>`;
            } else {
                avatarHtml = `<div class="msg-avatar-xp"></div>`;
            }

            row.innerHTML = `
                ${avatarHtml}
                <div class="msg-bubble-xp">
                    ${isGrouped ? '' : `
                        <div class="msg-header-xp">
                            <span class="msg-username-xp"></span>
                            <span class="msg-time-xp"></span>
                        </div>
                    `}
                    <div class="msg-content-xp"></div>
                </div>
            `;

            if (!isGrouped) {
                row.querySelector('.msg-username-xp').textContent = name;
                row.querySelector('.msg-time-xp').textContent = time;
                if (!avatarUrl) {
                    row.querySelector('.msg-avatar-xp').textContent = avatarInitial;
                }
            }
            // Set via textContent (not innerHTML) so message content can
            // never be interpreted as HTML/JS — avoids an XSS hole.
            row.querySelector('.msg-content-xp').textContent = content;

            chatMessages.appendChild(row);

            lastMessageUserId = userId;
            lastMessageTime = timeMs !== undefined ? timeMs : Date.now();

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
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': chatForm.querySelector('input[name="_token"]').value,
                },
                body: JSON.stringify({ content }),
            })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to send message');
                    return response.json();
                })
                .then(data => {
                    const message = data.message ?? data;
                    appendMessage({
                        userId: currentUserId,
                        name: message.user?.display_name ?? message.user?.name ?? 'You',
                        avatarInitial: (message.user?.name ?? 'Y')[0],
                        avatarUrl: message.user?.avatar_url ?? null,
                        time: message.time ?? formatTime(Date.now()),
                        timeMs: message.created_at ? new Date(message.created_at).getTime() : Date.now(),
                        content: message.content ?? content,
                    }, true);
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

        // ===== REAL-TIME: RECEIVE MESSAGES FROM THE OTHER USER =====
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env("REVERB_APP_KEY") }}',
            wsHost: '{{ env("REVERB_HOST", "localhost") }}',
            wsPort: '{{ env("REVERB_PORT", 8080) }}',
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
        });

        window.Echo.channel('conversation.{{ $conversation->id }}')
            .listen('NewMessageEvent', (e) => {
                // Skip our own message — it was already appended
                // optimistically when we sent it above.
                if (e.user?.id === currentUserId) return;

                appendMessage({
                    userId: e.user?.id,
                    name: e.user?.display_name ?? e.user?.name,
                    avatarInitial: (e.user?.name ?? '?')[0],
                    avatarUrl: e.user?.avatar_url ?? null,
                    time: e.time,
                    timeMs: e.created_at ? new Date(e.created_at).getTime() : Date.now(),
                    content: e.content,
                }, false);
            });
    });
</script>
@endpush

@endsection