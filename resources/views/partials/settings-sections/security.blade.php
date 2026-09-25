<form action="{{ route('settings.account') }}" method="POST">
    @csrf
    <div class="settings-v2-row">
        <div>
            <div class="settings-v2-row-label">Password</div>
            <div class="settings-v2-row-sub">Last changed — unknown</div>
        </div>
    </div>
    <div class="settings-group">
        <label>Current Password</label>
        <input type="password" name="current_password" placeholder="Enter current password" class="settings-input">
        @error('current_password')<div class="settings-error">{{ $message }}</div>@enderror
    </div>
    <div class="settings-group">
        <label>New Password</label>
        <input type="password" name="new_password" placeholder="Enter new password" class="settings-input">
        <span class="settings-hint">Minimum 8 characters.</span>
    </div>
    <div class="settings-group">
        <label>Confirm New Password</label>
        <input type="password" name="new_password_confirmation" placeholder="Confirm new password" class="settings-input">
    </div>
    <button type="submit" class="settings-v2-btn">Change Password</button>
</form>
