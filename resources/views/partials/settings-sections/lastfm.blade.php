<form action="{{ route('settings.lastfm') }}" method="POST">
    @csrf
    <div class="settings-group">
        <label>Last.fm Username</label>
        <input type="text" name="lastfm_username" class="settings-input" value="{{ old('lastfm_username', $user->lastfm_username ?? '') }}">
    </div>
    <button type="submit" class="settings-v2-btn">Connect Last.fm</button>
</form>
