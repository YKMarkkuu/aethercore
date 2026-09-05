@extends('layouts.app')

@section('title', 'Friends')
@section('content')

<div class="friends-tab-container">

    <!-- ===== SEARCH BAR ===== -->
    <div class="friends-search-panel">
        <div class="xp-panel-header">Find People</div>
        <div class="xp-panel-body">
            <form action="{{ route('friends.index') }}" method="GET" class="friends-search-form">
                <input type="text" name="search" placeholder="Search by username..." value="{{ request('search') }}" class="friends-search-input">
                <button type="submit" class="friends-search-btn">Search</button>
                @if(request('search'))
                    <a href="{{ route('friends.index') }}" class="friends-clear-btn">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <!-- ===== YOUR FRIENDS ===== -->
    <div class="friends-list-panel">
        <div class="xp-panel-header">Your Friends ({{ $friends->count() }})</div>
        <div class="xp-panel-body">
            @forelse($friends as $friend)
                <div class="friends-list-item">
                    <a href="{{ route('profile.show', $friend) }}" class="xp-friend-link">
                        <span class="xp-friend-avatar">
                            @if($friend->profile && $friend->profile->avatar)
                                <img src="{{ asset('storage/' . $friend->profile->avatar) }}" alt="Avatar">
                            @else
                                {{ $friend->name[0] }}
                            @endif
                        </span>
                        <span class="xp-friend-name">{{ $friend->display_name }}</span>
                    </a>
                    <form action="{{ route('friends.reject', $friend->id) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="xp-action-btn xp-action-btn-danger">Unfriend</button>
                    </form>
                </div>
            @empty
                <div class="friends-empty-state">
                    <span class="empty-icon">👥</span>
                    <div class="empty-text">No friends yet</div>
                    <div class="empty-sub">Search for people to connect with!</div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ===== FRIEND REQUESTS ===== -->
    <div class="friend-requests-panel">
        <div class="xp-panel-header">Friend Requests ({{ $requests->count() }})</div>
        <div class="xp-panel-body">
            @forelse($requests as $request)
                <div class="friend-request-item">
                    <a href="{{ route('profile.show', $request) }}" class="xp-friend-link">
                        <span class="xp-friend-avatar">
                            @if($request->profile && $request->profile->avatar)
                                <img src="{{ asset('storage/' . $request->profile->avatar) }}" alt="Avatar">
                            @else
                                {{ $request->name[0] }}
                            @endif
                        </span>
                        <span class="xp-friend-name">{{ $request->display_name }}</span>
                    </a>
                    <div class="request-actions">
                        <form action="{{ route('friends.accept', $request->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="request-btn request-btn-accept">Accept</button>
                        </form>
                        <form action="{{ route('friends.reject', $request->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="request-btn request-btn-decline">Decline</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="friends-empty-state">
                    <div class="empty-text">No pending requests</div>
                    <div class="empty-sub">You're all caught up!</div>
                </div>
            @endforelse
        </div>
    </div>

    <!-- ===== SEARCH RESULTS ===== -->
    @if(request('search'))
        <div class="search-results-panel">
            <div class="xp-panel-header">Search Results for "{{ request('search') }}"</div>
            <div class="xp-panel-body">
                @forelse($users as $user)
                    <div class="search-result-item">
                        <a href="{{ route('profile.show', $user) }}" class="xp-friend-link">
                            <span class="xp-friend-avatar">
                                @if($user->profile && $user->profile->avatar)
                                    <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="Avatar">
                                @else
                                    {{ $user->name[0] }}
                                @endif
                            </span>
                            <span class="xp-friend-name">{{ $user->display_name }}</span>
                        </a>
                        <form action="{{ route('friends.request', $user->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="xp-action-btn xp-action-btn-primary">Add Friend</button>
                        </form>
                    </div>
                @empty
                    <div class="friends-empty-state">
                        <span class="empty-icon">🔍</span>
                        <div class="empty-text">No users found</div>
                        <div class="empty-sub">Try a different search term</div>
                    </div>
                @endforelse
            </div>
        </div>
    @endif

</div>

@endsection