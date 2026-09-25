<p class="settings-hint" style="color:#f04747; margin-bottom:0.75rem;">⚠️ This cannot be undone.</p>
<form action="{{ route('settings.delete') }}" method="POST" onsubmit="return confirm('Delete your account? This cannot be undone.')">
    @csrf @method('DELETE')
    <div class="settings-group">
        <input type="password" name="password" placeholder="Enter your password to confirm" class="settings-input">
        @error('password')<div class="settings-error">{{ $message }}</div>@enderror
    </div>
    <button type="submit" class="settings-v2-btn-danger">Delete Account</button>
</form>
