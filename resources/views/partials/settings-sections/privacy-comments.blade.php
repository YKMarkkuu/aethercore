<div class="settings-group">
    <select name="comment_permission" class="settings-input" data-bulk-field="comment_permission" autocomplete="off">
        <option value="everyone" @selected(($user->profile->comment_permission ?? 'everyone') === 'everyone')>Everyone</option>
        <option value="friends" @selected(($user->profile->comment_permission ?? 'everyone') === 'friends')>Friends only</option>
        <option value="nobody" @selected(($user->profile->comment_permission ?? 'everyone') === 'nobody')>Nobody</option>
    </select>
</div>
