@extends('layouts.app')

@section('title', 'Messages')
@section('content')

<div class="xp-panel">
    <div class="xp-panel-header">💬 Messages</div>
    <div class="xp-panel-body">
        @forelse($conversations as $conversation)
            @php
                $otherUser = $conversation->getOtherParticipant(Auth::id());
                $lastMessage = $conversation->lastMessage;
            @endphp
            <a href="{{ route('conversations.show', $conversation) }}" class="conversation-item">
                <div class="conversation-avatar">{{ $otherUser->name[0] }}</div>
                <div class="conversation-info">
                    <div class="conversation-name">{{ $otherUser->display_name }}</div>
                    <div class="conversation-preview">
                        @if($lastMessage)
                            {{ Str::limit($lastMessage->content, 30) }}
                        @else
                            No messages yet
                        @endif
                    </div>
                </div>
                <div class="conversation-time">
                    @if($conversation->last_message_at)
                        {{ $conversation->last_message_at->diffForHumans() }}
                    @endif
                </div>
            </a>
        @empty
            <p style="font-size: 0.75rem; color: #6a6a6a; text-align: center; padding: 0.5rem 0;">
                No conversations yet. Start chatting with a friend!
            </p>
        @endforelse
    </div>
</div>

@endsection