@extends('layouts.app')

@section('title', $user->name . "'s Profile")
@section('content')

<div class="xp-profile">

    <!-- ===== PROFILE HEADER ===== -->
    <div class="xp-profile-header">
        <div class="xp-profile-cover">
            <div class="xp-profile-avatar">
                {{ $user->name[0] ?? '?' }}
            </div>
        </div>
        <div class="xp-profile-info">
            <h1 class="xp-profile-name">{{ $user->name }}</h1>
            <div class="xp-profile-status">Online</div>
            <div class="xp-profile-bio">{{ $user->profile->bio ?? 'Welcome to AetherCore!' }}</div>
                <!-- ===== FRIEND ACTION BUTTON ===== -->
    <div class="xp-profile-actions">
        @if(auth()->id() !== $user->id)
            @php
                $isFriend = auth()->user()->isFriendWith($user->id);
                $isPending = auth()->user()->friends()->where('friend_id', $user->id)->wherePivot('status', 'pending')->exists();
                $isRequested = $user->friends()->where('friend_id', auth()->id())->wherePivot('status', 'pending')->exists();
            @endphp

            @if($isFriend)
                <span class="xp-action-btn xp-action-btn-success">Friends</span>
                <form action="{{ route('friends.reject', $user) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="xp-action-btn xp-action-btn-danger">Unfriend</button>
                </form>
            @elseif($isPending)
                <span class="xp-action-btn xp-action-btn-warning">Request Sent</span>
                <form action="{{ route('friends.reject', $user) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="xp-action-btn xp-action-btn-danger">Cancel</button>
                </form>
            @elseif($isRequested)
                <form action="{{ route('friends.accept', $user) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="xp-action-btn xp-action-btn-success">Accept Request</button>
                </form>
                <form action="{{ route('friends.reject', $user) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="xp-action-btn xp-action-btn-danger">Decline</button>
                </form>
            @else
                <form action="{{ route('friends.request', $user) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="xp-action-btn xp-action-btn-primary">Add Friend</button>
                </form>
            @endif
        @endif
    </div>
        </div>
    </div>

    <!-- ===== PROFILE STATS ===== -->
    <div class="xp-profile-stats">
        <div class="xp-stat-box">
            <span class="xp-stat-number">{{ $user->posts()->count() }}</span>
            <span class="xp-stat-label">Posts</span>
        </div>
        <div class="xp-stat-box">
            <span class="xp-stat-number">{{ $user->friends()->count() }}</span>
            <span class="xp-stat-label">Friends</span>
        </div>
        <div class="xp-stat-box">
            <span class="xp-stat-number">0</span>
            <span class="xp-stat-label">Artists</span>
        </div>
        <div class="xp-stat-box">
            <span class="xp-stat-number">0</span>
            <span class="xp-stat-label">Albums</span>
        </div>
        <div class="xp-stat-box">
            <span class="xp-stat-number">0</span>
            <span class="xp-stat-label">Playlists</span>
        </div>
    </div>

    <!-- ===== TWO-COLUMN LAYOUT ===== -->
    <div class="xp-profile-body">

        <!-- ===== LEFT COLUMN: About / Info ===== -->
        <div class="xp-profile-left">
            <div class="xp-panel">
                <div class="xp-panel-header">About</div>
                <div class="xp-panel-body">
                    <div class="xp-info-row">
                        <span class="xp-info-label">Display Name</span>
                        <span class="xp-info-value">{{ $user->profile->display_name ?? $user->name }}</span>
                    </div>
                    <div class="xp-info-row">
                        <span class="xp-info-label">Joined</span>
                        <span class="xp-info-value">{{ $user->created_at->format('F Y') }}</span>
                    </div>
                    <div class="xp-info-row">
                        <span class="xp-info-label">Location</span>
                        <span class="xp-info-value">Not set</span>
                    </div>
                </div>
            </div>

            <div class="xp-panel">
                <div class="xp-panel-header">Friends</div>
                <div class="xp-panel-body">
                    <div style="display: flex; flex-wrap: wrap; gap: 0.3rem;">
                        @forelse($user->friends()->take(8)->get() as $friend)
                            <a href="{{ route('profile.show', $friend) }}" class="xp-friend-badge">
                                {{ $friend->name }}
                            </a>
                        @empty
                            <span style="font-size: 0.75rem; color: #6a6a6a;">No friends yet</span>
                        @endforelse
                    </div>
                    @if($user->friends()->count() > 8)
                        <div style="margin-top: 0.3rem; font-size: 0.65rem; color: #6a6a6a;">
                            + {{ $user->friends()->count() - 8 }} more
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- ===== RIGHT COLUMN: Posts ===== -->
        <div class="xp-profile-right">
            <div class="xp-panel">
                <div class="xp-panel-header">Posts</div>
                <div class="xp-panel-body">

                    @if(auth()->id() === $user->id)
                        <form action="{{ route('posts.store') }}" method="POST" style="margin-bottom: 0.75rem;">
                            @csrf
                            <textarea name="content" class="settings-input" rows="2" placeholder="Share something..." style="resize: none;"></textarea>
                            <button type="submit" class="settings-btn" style="margin-top: 0.3rem;">Post</button>
                        </form>
                        <hr class="xp-divider">
                    @endif

                    @forelse($user->posts as $post)
                        <div class="xp-post">
                            <div class="xp-post-header">
                                <span class="xp-post-user">{{ $user->name }}</span>
                                <span class="xp-post-time">{{ $post->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="xp-post-content">{{ $post->content }}</div>
                            <div class="xp-post-actions">
                                <span>Like</span>
                                <span>Comment</span>
                                <span>Share</span>
                                @if(auth()->id() === $post->user_id)
                                    <form action="{{ route('posts.destroy', $post) }}" method="POST" style="display: inline; margin-left: auto;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="xp-delete-btn">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p style="font-size: 0.75rem; color: #6a6a6a; text-align: center; padding: 1rem 0;">
                            No posts yet
                        </p>
                    @endforelse

                </div>
            </div>
        </div>

    </div>
</div>

@endsection