<form action="{{ route('settings.privacy') }}" method="POST">
    @csrf
    <div class="settings-group">
        <select name="visibility" class="settings-input" onchange="this.form.submit()">
            <option value="public" @selected(($user->profile->visibility ?? 'public') === 'public')>Public — anyone</option>
            <option value="friends" @selected(($user->profile->visibility ?? 'public') === 'friends')>Friends only</option>
            <option value="private" @selected(($user->profile->visibility ?? 'public') === 'private')>Private — only you</option>
        </select>
    </div>
</form>
