@extends('layouts.admin')
@section('title', 'Users')
@section('content')

<div style="display: flex; gap: 0.5rem; margin-bottom: 0.75rem;">
    @foreach(['all' => 'All', 'active' => 'Active', 'suspended' => 'Suspended', 'banned' => 'Banned'] as $key => $label)
        <a href="{{ route('admin.users.index', array_merge(request()->only('search'), ['filter' => $key])) }}"
           class="settings-btn {{ $filter === $key ? 'settings-btn-danger' : '' }}"
           style="text-decoration: none;">{{ $label }}</a>
    @endforeach
</div>

<form method="GET" style="margin-bottom: 1rem; display: flex; gap: 0.5rem;">
    <input type="hidden" name="filter" value="{{ $filter }}">
    <input type="text" name="search" value="{{ $search }}" placeholder="Search username, name, or email..." class="settings-input" style="max-width: 300px;">
    <button type="submit" class="settings-btn">Search</button>
    @if($search)
        <a href="{{ route('admin.users.index', ['filter' => $filter]) }}" class="settings-btn" style="text-decoration: none;">Clear</a>
    @endif
</form>

<div class="xp-panel">
    <div class="xp-panel-header">Users ({{ $users->total() }})</div>
    <div class="xp-panel-body">
        @forelse($users as $user)
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e0dcd0; padding: 0.5rem 0.3rem; gap: 0.5rem; flex-wrap: wrap;">
                <div>
                    <a href="{{ route('profile.show', $user) }}" style="font-weight: 600; color: #1e1e1e; text-decoration: none;">{{ $user->display_name }}</a>
                    <span style="font-size: 0.7rem; color: #6a6a6a;">@ {{ $user->username }} · {{ $user->email }} · {{ $user->posts_count }} posts</span>
                    @if($user->role === 'admin')
                        <span class="xp-top-badge" style="margin-left: 0.4rem;">Admin</span>
                    @endif
                    @if($user->banned_at)
                        <span class="xp-action-btn xp-action-btn-danger" style="margin-left: 0.4rem; padding: 0 0.4rem; font-size: 0.6rem;">Banned</span>
                    @elseif($user->isSuspended())
                        <span class="xp-action-btn xp-action-btn-warning" style="margin-left: 0.4rem; padding: 0 0.4rem; font-size: 0.6rem;">
                            Suspended until {{ $user->suspended_until->format('M j') }} ({{ $user->suspended_until->diffForHumans(null, true) }} left)
                        </span>
                    @endif
                </div>

                @if($user->role !== 'admin')
                    <div style="display: flex; gap: 0.3rem; flex-wrap: wrap; align-items: center;">
                        @if($user->banned_at)
                            <form action="{{ route('admin.users.unban', $user) }}" method="POST">
                                @csrf
                                <button class="settings-btn" style="font-size: 0.65rem;">Unban</button>
                            </form>
                        @else
                            <form action="{{ route('admin.users.ban', $user) }}" method="POST" onsubmit="return confirm('Ban {{ $user->display_name }}? They will be logged out immediately.')">
                                @csrf
                                <button class="settings-btn settings-btn-danger" style="font-size: 0.65rem;">Ban</button>
                            </form>
                        @endif

                        @if($user->isSuspended())
                            <form action="{{ route('admin.users.unsuspend', $user) }}" method="POST">
                                @csrf
                                <button class="settings-btn" style="font-size: 0.65rem;">Unsuspend</button>
                            </form>
                        @else
                            <form action="{{ route('admin.users.suspend', $user) }}" method="POST" style="display: flex; gap: 0.3rem; align-items: center;">
                                @csrf
                                <select name="days" class="settings-input" style="width: auto; font-size: 0.65rem; padding: 0.15rem 0.3rem;">
                                    <option value="1">1 day</option>
                                    <option value="3">3 days</option>
                                    <option value="7" selected>7 days</option>
                                    <option value="14">14 days</option>
                                    <option value="30">30 days</option>
                                    <option value="90">90 days</option>
                                </select>
                                <button class="settings-btn" style="font-size: 0.65rem;">Suspend</button>
                            </form>
                        @endif

                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Delete {{ $user->display_name }}? This is soft-deleted and can be restored later.')">
                            @csrf
                            @method('DELETE')
                            <button class="settings-btn settings-btn-danger" style="font-size: 0.65rem;">Delete</button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <p style="font-size: 0.8rem; color: #6a6a6a; text-align: center; padding: 1rem 0;">No users match this filter.</p>
        @endforelse
    </div>
</div>

<div style="margin-top: 1rem;">{{ $users->links() }}</div>

@endsection