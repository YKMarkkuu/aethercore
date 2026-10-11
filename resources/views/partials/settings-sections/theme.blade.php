<div class="settings-group">
    <select name="theme" class="settings-input" data-bulk-field="theme" autocomplete="off">
        @foreach($user->getAvailableThemes() as $key => $label)
            <option value="{{ $key }}" @selected($user->theme == $key)>{{ $label }}</option>
        @endforeach
    </select>
</div>
