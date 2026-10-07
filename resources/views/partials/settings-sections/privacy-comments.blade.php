<form action="{{ route('settings.privacy') }}" method="POST">
    @csrf
    <div class="settings-group">
        <select name="comment_permission" class="settings-input" onchange="this.form.requestSubmit()">
            <option value="everyone" @selected(($user->profile->comment_permission ?? 'everyone') === 'everyone')>Everyone</option>
            <option value="friends" @selected(($user->profile->comment_permission ?? 'everyone') === 'friends')>Friends only</option>
            <option value="nobody" @selected(($user->profile->comment_permission ?? 'everyone') === 'nobody')>Nobody</option>
        </select>
    </div>
</form>
