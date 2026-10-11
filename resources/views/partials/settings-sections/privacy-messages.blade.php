<div class="settings-group">
    <select name="dm_permission" class="settings-input" data-bulk-field="dm_permission" autocomplete="off">
        <option value="everyone" @selected(($user->profile->dm_permission ?? 'everyone') === 'everyone')>Everyone</option>
        <option value="friends" @selected(($user->profile->dm_permission ?? 'everyone') === 'friends')>Friends only</option>
        <option value="nobody" @selected(($user->profile->dm_permission ?? 'everyone') === 'nobody')>Nobody</option>
    </select>
</div>
