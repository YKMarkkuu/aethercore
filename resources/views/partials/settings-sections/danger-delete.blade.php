<p class="settings-hint" style="color:#a03030; margin-bottom:0.75rem;">⚠️ This cannot be undone.</p>
<form action="{{ route('settings.delete') }}" method="POST" data-confirm="Delete your account? This cannot be undone." data-confirm-danger="true" data-confirm-title="Delete Account" data-confirm-ok="Delete Account">
    @csrf @method('DELETE')
    <div class="settings-group">
        <input type="password" name="password" placeholder="Enter your password to confirm" class="settings-input">
        @error('password')<div class="settings-error">{{ $message }}</div>@enderror
    </div>
    <button type="submit" class="settings-btn settings-btn-danger">Delete Account</button>
</form>
