@extends('layouts.app')

@section('title', 'Friends')
@section('content')

<!-- ===== SEARCH BAR ===== -->
<div class="xp-panel">
    <div class="xp-panel-header">Find People</div>
    <div class="xp-panel-body">
        <form action="{{ route('friends.index') }}" method="GET" style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
            <input type="text" name="search" placeholder="Search by username..." value="{{ request('search') }}" class="settings-input" style="flex: 1;">
            <button type="submit" class="settings-btn">Search</button>
            @if(request('search'))
                <a href="{{ route('friends.index') }}" class="settings-btn" style="text-decoration: none; text-align: center;">Clear</a>
            @endif
        </form>
    </div>
</div>

<!-- ===== YOUR FRIENDS ===== -->
<div class="xp-panel">
    <div class="xp-panel-header">Your Friends ({{ $friends->count() }})</div>
    <div class="xp-panel-body">
        @forelse($friends as $friend)
            <div class="xp-friend-item">
                <a href="{{ route('profile.show', $friend) }}" class="xp-friend-link">
                    <span class="xp-friend-avatar">{{ $friend->name[0] }}</span>
                    <span class="xp-friend-name">{{ $friend->name }}</span>
                </a>
                <form action="{{ route('friends.reject', $friend) }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="xp-action-btn xp-action-btn-danger">Unfriend</button>
                </form>
            </div>
        @empty
            <p style="font-size: 0.75rem; color: #6a6a6a; text-align: center; padding: 0.5rem 0;">
                You haven't made any friends yet. Search for people above!
            </p>
        @endforelse
    </div>
</div>

<!-- ===== FRIEND REQUESTS ===== -->
<div class="xp-panel">
    <div class="xp-panel-header">Friend Requests ({{ $requests->count() }})</div>
    <div class="xp-panel-body">
        @forelse($requests as $request)
            <div class="xp-friend-item">
                <a href="{{ route('profile.show', $request) }}" class="xp-friend-link">
                    <span class="xp-friend-avatar">{{ $request->name[0] }}</span>
                    <span class="xp-friend-name">{{ $request->name }}</span>
                </a>
                <div style="display: flex; gap: 0.3rem;">
                    <form action="{{ route('friends.accept', $request) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="xp-action-btn xp-action-btn-success">Accept</button>
                    </form>
                    <form action="{{ route('friends.reject', $request) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="xp-action-btn xp-action-btn-danger">Decline</button>
                    </form>
                </div>
            </div>
        @empty
            <p style="font-size: 0.75rem; color: #6a6a6a; text-align: center; padding: 0.5rem 0;">
                No pending friend requests.
            </p>
        @endforelse
    </div>
</div>

<!-- ===== SEARCH RESULTS (if searching) ===== -->
@if(request('search'))
    <div class="xp-panel">
        <div class="xp-panel-header">Search Results for "{{ request('search') }}"</div>
        <div class="xp-panel-body">
            @forelse($users as $user)
                <div class="xp-friend-item">
                    <a href="{{ route('profile.show', $user) }}" class="xp-friend-link">
                        <span class="xp-friend-avatar">{{ $user->name[0] }}</span>
                        <span class="xp-friend-name">{{ $user->name }}</span>
                    </a>
                    <form action="{{ route('friends.request', $user) }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="xp-action-btn xp-action-btn-primary">Add Friend</button>
                    </form>
                </div>
            @empty
                <p style="font-size: 0.75rem; color: #6a6a6a; text-align: center; padding: 0.5rem 0;">
                    No users found matching "{{ request('search') }}"
                </p>
            @endforelse
        </div>
    </div>
@endif

@endsection