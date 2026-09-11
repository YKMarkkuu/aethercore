@extends('layouts.app')

@section('title', $space->name)
@section('content')

<div class="space-layout">

    <!-- ===== CHANNEL SIDEBAR ===== -->
    <div class="space-channel-sidebar">
        <div class="space-sidebar-header">
            <div class="space-icon" style="width: 32px; height: 32px; font-size: 0.8rem;">
                @if($space->getIconUrl())
                    <img src="{{ $space->getIconUrl() }}" alt="{{ $space->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
                @else
                    {{ strtoupper($space->name[0] ?? '?') }}
                @endif
            </div>
            <span class="space-sidebar-title">{{ $space->name }}</span>
        </div>

        <div class="space-channel-list">
            @foreach($space->channels as $ch)
                <a href="{{ route('spaces.channel', [$space, $ch]) }}" class="space-channel-link {{ $activeChannel && $activeChannel->id === $ch->id ? 'active' : '' }}">
                    # {{ $ch->name }}
                </a>
            @endforeach

            @if($isOwner)
                <button type="button" class="space-add-channel-btn" onclick="document.getElementById('addChannelForm').classList.toggle('hidden')">+ Add Channel</button>
                <form id="addChannelForm" class="hidden" action="{{ route('space-channels.store', $space) }}" method="POST" style="padding: 0.3rem 0.4rem;">
                    @csrf
                    <input type="text" name="name" placeholder="channel-name" pattern="[a-z0-9\-]+" title="lowercase letters, numbers, and hyphens only" required class="settings-input" style="width: 100%; font-size: 0.7rem; margin-bottom: 0.3rem;">
                    <button type="submit" class="settings-btn" style="width: 100%; font-size: 0.65rem;">Create</button>
                </form>
            @endif
        </div>

        <div class="space-sidebar-footer">
            <form action="{{ route('spaces.share', $space) }}" method="POST">
                @csrf
                <button type="submit" class="settings-btn" style="width: 100%; font-size: 0.65rem; margin-bottom: 0.3rem;">Share to Feed</button>
            </form>
            @if($isOwner)
                <form action="{{ route('spaces.destroy', $space) }}" method="POST" onsubmit="return confirm('Delete this Space? This can\'t be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="delete-btn" style="font-size: 0.65rem;">Delete Space</button>
                </form>
            @else
                <form action="{{ route('spaces.leave', $space) }}" method="POST">
                    @csrf
                    <button type="submit" class="delete-btn" style="font-size: 0.65rem;">Leave Space</button>
                </form>
            @endif
        </div>
    </div>

    <!-- ===== CHAT ===== -->
    <div class="chat-container" style="flex: 1;">
        @if($activeChannel)
            <div class="chat-header-xp">
                <div class="chat-header-left">
                    <div class="chat-name-xp"># {{ $activeChannel->name }}</div>
                </div>
            </div>

            <div class="chat-messages-xp" id="spaceChatMessages">
                @php
                    $lastUserId = null;
                    $lastTimestamp = null;
                    $groupThresholdSeconds = 300;
                @endphp
                @forelse($messages as $message)
                    @php
                        $isGrouped = $lastUserId === $message->user_id
                            && $lastTimestamp !== null
                            && $message->created_at->diffInSeconds($lastTimestamp) <= $groupThresholdSeconds;
                    @endphp
                    <div class="chat-message-xp @if($isGrouped) chat-message-grouped @endif" data-message-id="{{ $message->id }}">
                        @if($isGrouped)
                            <div class="msg-avatar-spacer">
                                <span class="msg-hover-time">{{ $message->created_at->format('g:i A') }}</span>
                            </div>
                        @else
                            <div class="msg-avatar-xp">
                                @if($message->user->getAvatarUrl())
                                    <img src="{{ $message->user->getAvatarUrl() }}" alt="Avatar" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
                                @else
                                    {{ $message->user->name[0] }}
                                @endif
                            </div>
                        @endif
                        <div class="msg-bubble-xp">
                            @unless($isGrouped)
                                <div class="msg-header-xp">
                                    <a href="{{ route('profile.show', $message->user) }}" class="msg-username-xp" style="text-decoration: none;">{{ $message->user->display_name }}</a>
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
                        <div class="chat-empty-text">No messages yet</div>
                        <div class="chat-empty-sub">Say something in #{{ $activeChannel->name }}!</div>
                    </div>
                @endforelse
            </div>

            <div class="chat-input-xp">
                <form class="chat-form-xp" id="spaceChatForm">
                    <input type="text" class="chat-input-xp-field" id="spaceChatInput" placeholder="Message #{{ $activeChannel->name }}" autocomplete="off" maxlength="1000">
                    <button type="submit" class="chat-send-xp" id="spaceChatSendBtn">Send</button>
                </form>
            </div>
        @else
            <div class="chat-empty-xp">
                <div class="chat-empty-text">No channels yet</div>
            </div>
        @endif
    </div>

</div>

@if($activeChannel)
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const chatMessages = document.getElementById('spaceChatMessages');
        const chatForm = document.getElementById('spaceChatForm');
        const chatInput = document.getElementById('spaceChatInput');
        const chatSendBtn = document.getElementById('spaceChatSendBtn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        const channelId = {{ $activeChannel->id }};
        const currentUserId = {{ auth()->id() }};

        let lastMessageUserId = {{ $lastUserId !== null ? $lastUserId : 'null' }};
        let lastMessageTime = {{ $lastTimestamp ? $lastTimestamp->timestamp * 1000 : 'null' }};
        let lastMessageId = {{ optional($messages->last())->id ?? 0 }};
        const GROUP_THRESHOLD_MS = 5 * 60 * 1000;

        function isNearBottom(el, threshold = 80) {
            return el.scrollHeight - el.scrollTop - el.clientHeight < threshold;
        }
        function scrollToBottom() {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        scrollToBottom();

        function appendMessage(message, isOwnMessage) {
            const timeMs = message.created_at ? new Date(message.created_at).getTime() : Date.now();
            const isGrouped = message.user.id === lastMessageUserId
                && lastMessageTime !== null
                && (timeMs - lastMessageTime) <= GROUP_THRESHOLD_MS;

            const wasNearBottom = isNearBottom(chatMessages);

            const row = document.createElement('div');
            row.className = 'chat-message-xp' + (isGrouped ? ' chat-message-grouped' : '');
            row.dataset.messageId = message.id;

            let avatarHtml;
            if (isGrouped) {
                avatarHtml = `<div class="msg-avatar-spacer"><span class="msg-hover-time">${message.time}</span></div>`;
            } else if (message.user.avatar_url) {
                avatarHtml = `<div class="msg-avatar-xp"><img src="${message.user.avatar_url}" alt="Avatar" style="width:36px;height:36px;border-radius:50%;object-fit:cover;"></div>`;
            } else {
                avatarHtml = `<div class="msg-avatar-xp">${(message.user.display_name || message.user.name || '?')[0]}</div>`;
            }

            row.innerHTML = `
                ${avatarHtml}
                <div class="msg-bubble-xp">
                    ${isGrouped ? '' : `
                        <div class="msg-header-xp">
                            <a href="/profile/${message.user.id}" class="msg-username-xp" style="text-decoration:none;"></a>
                            <span class="msg-time-xp">${message.time}</span>
                        </div>
                    `}
                    <div class="msg-content-xp"></div>
                </div>
            `;

            if (!isGrouped) {
                row.querySelector('.msg-username-xp').textContent = message.user.display_name || message.user.name;
            }
            // textContent, not innerHTML — never let message content be
            // interpreted as HTML/JS.
            row.querySelector('.msg-content-xp').textContent = message.content;

            chatMessages.appendChild(row);

            lastMessageUserId = message.user.id;
            lastMessageTime = timeMs;

            if (wasNearBottom || isOwnMessage) {
                scrollToBottom();
            }
        }

        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const content = chatInput.value.trim();
            if (!content || chatSendBtn.disabled) return;

            chatSendBtn.disabled = true;

            fetch(`/space-channels/${channelId}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ content }),
            })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(data => {
                    appendMessage(data.message, true);
                    lastMessageId = data.message.id;
                    chatInput.value = '';
                    chatInput.focus();
                })
                .catch(() => alert('Message failed to send. Please try again.'))
                .finally(() => { chatSendBtn.disabled = false; });
        });

        chatInput.focus();

        function pollForNewMessages() {
            fetch(`/space-channels/${channelId}/messages/latest?after=${lastMessageId}`, {
                headers: { 'Accept': 'application/json' },
            })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(data => {
                    (data.messages || []).forEach(message => {
                        appendMessage(message, false);
                        lastMessageId = message.id;
                    });
                })
                .catch(() => { /* silent, try again next interval */ });
        }

        setInterval(pollForNewMessages, 3000);
    });
</script>
@endpush
@endif

@endsection