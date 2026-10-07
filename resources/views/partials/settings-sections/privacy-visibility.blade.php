<form action="{{ route('settings.privacy') }}" method="POST">
    @csrf
    <div class="settings-group">
        <select name="visibility" class="settings-input">
            <option value="public" @selected(($user->profile->visibility ?? 'public') === 'public')>Public</option>
            <option value="friends" @selected(($user->profile->visibility ?? 'public') === 'friends')>Friends only</option>
            <option value="private" @selected(($user->profile->visibility ?? 'public') === 'private')>Only me</option>
        </select>
        <button type="submit" class="settings-btn" style="margin-top: 0.4rem;">Save</button>
    </div>
</form>
