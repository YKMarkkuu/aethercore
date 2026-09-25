<form action="{{ route('settings.theme') }}" method="POST">
    @csrf
    <div class="settings-group">
        <select name="theme" class="settings-input" onchange="this.form.submit()">
            @foreach($user->getAvailableThemes() as $key => $label)
                <option value="{{ $key }}" @selected($user->theme == $key)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</form>
