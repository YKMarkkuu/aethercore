<form action="{{ route('settings.privacy') }}" method="POST">
    @csrf
    <div class="settings-group">
        <select name="show_status_to" class="settings-input" onchange="this.form.submit()">
            <option value="everyone" @selected(($user->profile->show_status_to ?? 'everyone') === 'everyone')>Everyone</option>
            <option value="friends" @selected(($user->profile->show_status_to ?? 'everyone') === 'friends')>Friends only</option>
            <option value="nobody" @selected(($user->profile->show_status_to ?? 'everyone') === 'nobody')>Nobody</option>
        </select>
    </div>
</form>
