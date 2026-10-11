<div class="settings-group">
    <select name="visibility" class="settings-input" data-bulk-field="visibility" autocomplete="off">
        <option value="public" @selected(($user->profile->visibility ?? 'public') === 'public')>Public</option>
        <option value="friends" @selected(($user->profile->visibility ?? 'public') === 'friends')>Friends only</option>
        <option value="private" @selected(($user->profile->visibility ?? 'public') === 'private')>Only me</option>
    </select>
</div>
