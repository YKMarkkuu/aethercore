@extends('layouts.admin')
@section('title', 'Users')
@section('content')

<form method="GET" style="margin-bottom: 1rem; display: flex; gap: 0.5rem;">
    <input type="text" name="search" value="{{ $search }}" placeholder="Search username, name, or email..." class="settings-input" style="max-width: 300px;">
    <button type="submit" class="settings-btn">Search</button>
</form>

<div class="xp-panel">
    <div class="xp-panel-header">Users ({{ $users->total() }})</div>
    <div class="xp-panel-body">
        @foreach($users as $user)
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
                        <span class="xp-action-btn xp-action-btn-warning" style="margin-left: 0.4rem; padding: 0 0.4rem; font-size: 0.6rem;">Suspended until {{ $user->suspended_until->format('M j') }}</span>
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
                            <form action="{{ route('admin.users.suspend', $user) }}" method="POST" style="display: flex; gap: 0.2rem;">
                                @csrf
                                <input type="number" name="days" value="7" min="1" max="365" class="settings-input" style="width: 50px; font-size: 0.65rem; padding: 0.15rem;">
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
        @endforeach
    </div>
</div>

<div style="margin-top: 1rem;">{{ $users->links() }}</div>

@endsection
