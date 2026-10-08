<form action="{{ route('settings.theme') }}" method="POST" data-ajax-reset="false">
    @csrf
    <div class="settings-group">
        <select name="theme" class="settings-input">
            @foreach($user->getAvailableThemes() as $key => $label)
                <option value="{{ $key }}" @selected($user->theme == $key)>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="settings-btn" style="margin-top: 0.4rem;">Apply</button>
    </div>
</form>
