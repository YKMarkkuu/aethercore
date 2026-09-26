<p class="settings-subtitle" style="margin-top: -0.3rem;">People you've blocked can't message you, see your profile, or send friend requests.</p>

@forelse(($settingsBlockedUsers ?? []) as $blocked)
    <div class="device-item">
        <div>
            <div class="device-name">{{ $blocked->name }}</div>
            <div class="device-detail">@ {{ $blocked->username ?? $blocked->name }}</div>
        </div>
        <form action="{{ route('users.unblock', $blocked->id) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="device-logout-btn">Unblock</button>
        </form>
    </div>
@empty
    <p class="settings-hint">You haven't blocked anyone.</p>
@endforelse
