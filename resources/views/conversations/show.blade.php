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
            <a href="{{ route('profile.show', $otherUser) }}" class="chat-icon-btn" title="View Profile">👤</a>
        </div>
    </div>

    <!-- ===== CHAT MESSAGES ===== -->
    <div class="chat-messages-xp" id="chatMessages">

        @php
            $lastDate = null;
        @endphp

        @forelse($messages as $message)
            @php
                $messageDate = $message->created_at->format('Y-m-d');
                $isToday = $message->created_at->isToday();
                $isYesterday = $message->created_at->isYesterday();
                $displayDate = $isToday ? 'Today' : ($isYesterday ? 'Yesterday' : $message->created_at->format('F j, Y'));
            @endphp

            <!-- Date Divider -->
            @if($lastDate !== $messageDate)
                <div class="chat-divider">{{ $displayDate }}</div>
                @php $lastDate = $messageDate; @endphp
            @endif

            <!-- Message (EVERY message shows avatar, name, time, and content) -->
            <div class="chat-message-xp">
                <div class="msg-avatar-xp">
                    @if($message->user->profile && $message->user->profile->avatar)
                        <img src="{{ asset('storage/' . $message->user->profile->avatar) }}" alt="Avatar" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">
                    @else
                        {{ $message->user->name[0] }}
                    @endif
                </div>
                <div class="msg-bubble-xp">
                    <div class="msg-header-xp">
                        <span class="msg-username-xp">{{ $message->user->display_name }}</span>
                        <span class="msg-time-xp">{{ $message->created_at->format('g:i A') }}</span>
                    </div>
                    <div class="msg-content-xp">{{ $message->content }}</div>
                </div>
            </div>

        @empty
            <div class="chat-empty-xp">
                <div class="chat-empty-icon">💬</div>
                <div class="chat-empty-text">No messages yet</div>
                <div class="chat-empty-sub">Say hello to {{ $otherUser->display_name }}!</div>
            </div>
        @endforelse

    </div>

    <!-- ===== CHAT INPUT ===== -->
    <div class="chat-input-xp">
        <form action="{{ route('conversations.store', $conversation) }}" method="POST" class="chat-form-xp">
            @csrf
            <input type="text" name="content" placeholder="Type a message..." class="chat-input-xp-field" autocomplete="off">
            <button type="submit" class="chat-send-xp">Send</button>
        </form>
    </div>

</div>

<script>
        @push('scripts')
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.15.3/dist/echo.iife.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatMessages = document.getElementById('chatMessages');
            
            // Auto-scroll to bottom
            if (chatMessages) {
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }

            // Initialize Echo
            window.Echo = new Echo({
                broadcaster: 'reverb',
                key: '{{ env("REVERB_APP_KEY") }}',
                wsHost: '{{ env("REVERB_HOST", "localhost") }}',
                wsPort: '{{ env("REVERB_PORT", 8080) }}',
                forceTLS: false,
                enabledTransports: ['ws', 'wss'],
            });

            // Listen for new messages
            window.Echo.channel('conversation.{{ $conversation->id }}')
                .listen('NewMessageEvent', (e) => {
                    const message = e;
                    
                    // Create message HTML
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'chat-message-xp';
                    messageDiv.innerHTML = `
                        <div class="msg-avatar-xp">${message.user.name[0]}</div>
                        <div class="msg-bubble-xp">
                            <div class="msg-header-xp">
                                <span class="msg-username-xp">${message.user.display_name}</span>
                                <span class="msg-time-xp">${message.time}</span>
                            </div>
                            <div class="msg-content-xp">${message.content}</div>
                        </div>
                    `;
                    
                    chatMessages.appendChild(messageDiv);
                    chatMessages.scrollTop = chatMessages.scrollHeight;
                });
        });
    </script>
    @endpush
</script>

@endsection