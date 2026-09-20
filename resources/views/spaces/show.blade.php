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
                        $isMine = $message->user_id === auth()->id();
                        $msgReactions = $reactionsByMessage[$message->id] ?? ['counts' => [], 'mine' => []];
                    @endphp
                    <div class="chat-message-xp @if($isGrouped) chat-message-grouped @endif" data-message-id="{{ $message->id }}" data-user-id="{{ $message->user_id }}">
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

                            @if($message->reply_to_id)
                                @php $replyTo = $message->replyTo; @endphp
                                <div class="msg-reply-preview" data-jump-to="{{ $message->reply_to_id }}">
                                    @if($replyTo)
                                        <span class="msg-reply-preview-author">{{ $replyTo->user->display_name }}</span>
                                        <span class="msg-reply-preview-text">{{ $replyTo->is_deleted ? 'Message was deleted' : \Illuminate\Support\Str::limit(strip_tags($replyTo->content ?? ''), 80) }}</span>
                                    @else
                                        <span class="msg-reply-preview-unavailable">Original message unavailable</span>
                                    @endif
                                </div>
                            @endif

                            @if($message->is_deleted)
                                <div class="msg-content-xp msg-content-deleted">This message was deleted</div>
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
                                        <circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>
                                    </svg>
                                </button>
                                <button type="button" class="msg-toolbar-btn msg-reply-btn" title="Reply">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/>
                                    </svg>
                                </button>
                                @if($isMine)
                                    <button type="button" class="msg-toolbar-btn msg-edit-btn" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                        </svg>
                                    </button>
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
                        <div class="chat-empty-text">No messages yet</div>
                        <div class="chat-empty-sub">Say something in #{{ $activeChannel->name }}!</div>
                    </div>
                @endforelse
            </div>

            <!-- ===== REPLY COMPOSER BAR (hidden until a reply is picked) ===== -->
            <div class="reply-composer-bar hidden" id="replyComposerBar">
                <div class="reply-composer-bar-text">Replying to <strong id="replyComposerTarget"></strong></div>
                <button type="button" class="reply-composer-cancel" id="replyComposerCancel" title="Cancel reply">✕</button>
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
        window.ChatThread.init({
            containerId: 'spaceChatMessages',
            formId: 'spaceChatForm',
            inputId: 'spaceChatInput',
            sendBtnId: 'spaceChatSendBtn',
            currentUserId: {{ auth()->id() }},
            endpoints: {
                store: '{{ route('space-messages.store', $activeChannel) }}',
                latest: '{{ route('space-messages.latest', $activeChannel) }}',
                update: (id) => `/space-messages/${id}`,
                destroy: (id) => `/space-messages/${id}`,
                react: (id) => `/space-messages/${id}/react`,
            },
            initialState: {
                lastMessageId: {{ optional($messages->last())->id ?? 0 }},
                lastMessageUserId: {{ $lastUserId !== null ? $lastUserId : 'null' }},
                lastMessageTime: {{ $lastTimestamp ? $lastTimestamp->timestamp * 1000 : 'null' }},
                lastPollTime: '{{ now()->toIso8601String() }}',
            },
            features: { reply: true, sharedPost: false },
            profileUrl: (id) => `/profile/${id}`,
            currentUser: {
                id: {{ auth()->id() }},
                name: @json(auth()->user()->name),
                display_name: @json(auth()->user()->display_name ?? auth()->user()->name),
                avatar_url: @json(auth()->user()->getAvatarUrl()),
            },
        });
    });
</script>
@endpush
@endif

@endsection