<form action="{{ route('settings.privacy') }}" method="POST">
    @csrf
    <div class="settings-group">
        <select name="comment_permission" class="settings-input">
            <option value="everyone" @selected(($user->profile->comment_permission ?? 'everyone') === 'everyone')>Everyone</option>
            <option value="friends" @selected(($user->profile->comment_permission ?? 'everyone') === 'friends')>Friends only</option>
            <option value="nobody" @selected(($user->profile->comment_permission ?? 'everyone') === 'nobody')>Nobody</option>
        </select>
        <button type="submit" class="settings-btn" style="margin-top: 0.4rem;">Save</button>
    </div>
</form>
