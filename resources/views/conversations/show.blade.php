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
            $groupThresholdSeconds = 300;
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
                    && $message->created_at->diffInSeconds($lastTimestamp) <= $groupThresholdSeconds
                    && !$message->reply_to_id;

                $isMine = $message->user_id === auth()->id();
                $msgReactions = $reactionsByMessage[$message->id] ?? ['counts' => [], 'mine' => []];
            @endphp

            @if($isNewDateGroup)
                <div class="chat-divider">{{ $displayDate }}</div>
                @php $lastDate = $messageDate; @endphp
            @endif

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

                    @if($message->reply_to_id)
                        @php $replyTo = $message->replyTo; @endphp
                        <div class="msg-reply-preview" data-jump-to="{{ $message->reply_to_id }}">
                            @if($replyTo)
                                <span class="msg-reply-preview-author">{{ $replyTo->user->display_name }}</span>
                                <span class="msg-reply-preview-text">{{ $replyTo->is_deleted ? 'Message was deleted' : ($replyTo->type === 'shared_post' ? 'Shared a post' : \Illuminate\Support\Str::limit(strip_tags($replyTo->content ?? ''), 80)) }}</span>
                            @else
                                <span class="msg-reply-preview-unavailable">Original message unavailable</span>
                            @endif
                        </div>
                    @endif

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
                            <button type="button" class="msg-reaction-pill @if(in_array($type, $msgReactions['mine'])) msg-reaction-pill-mine @endif" data-reaction-type="{{ $type }}">
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
                                <circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>
                            </svg>
                        </button>
                        <button type="button" class="msg-toolbar-btn msg-reply-btn" title="Reply">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/>
                            </svg>
                        </button>
                        @if($isMine && $message->type !== 'shared_post')
                            <button type="button" class="msg-toolbar-btn msg-edit-btn" title="Edit">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                </svg>
                            </button>
                        @endif
                        @if($isMine)
                            <button type="button" class="msg-toolbar-btn msg-delete-btn" title="Delete">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
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

    <!-- ===== REPLY COMPOSER BAR ===== -->
    <div class="reply-composer-bar hidden" id="replyComposerBar">
        <div class="reply-composer-bar-text">Replying to <strong id="replyComposerTarget"></strong></div>
        <button type="button" class="reply-composer-cancel" id="replyComposerCancel" title="Cancel reply">✕</button>
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
    document.addEventListener('DOMContentLoaded', function () {
        window.ChatThread.init({
            containerId: 'chatMessages',
            formId: 'chatForm',
            inputId: 'chatInput',
            sendBtnId: 'chatSendBtn',
            currentUserId: {{ auth()->id() }},
            endpoints: {
                store: '{{ route('conversations.store', $conversation) }}',
                latest: '{{ route('conversations.latest', $conversation) }}',
                update: (id) => `/messages/${id}`,
                destroy: (id) => `/messages/${id}`,
                react: (id) => `/messages/${id}/react`,
            },
            initialState: {
                lastMessageId: {{ optional($messages->last())->id ?? 0 }},
                lastMessageUserId: {{ $lastUserId !== null ? $lastUserId : 'null' }},
                lastMessageTime: {{ $lastTimestamp ? $lastTimestamp->timestamp * 1000 : 'null' }},
                lastPollTime: '{{ now()->toIso8601String() }}',
            },
            features: { reply: true, sharedPost: true },
            profileUrl: (id) => `/profile/${id}`,
            currentUser: {
                id: {{ auth()->id() }},
                name: @json(auth()->user()->name),
                display_name: @json(auth()->user()->display_name),
                avatar_url: @json(auth()->user()->getAvatarUrl()),
            },
        });
    });
</script>
@endpush

@endsection