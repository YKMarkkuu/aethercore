<form action="{{ route('settings.account') }}" method="POST">
    @csrf
    <div class="settings-group">
        <label>Username</label>
        <input type="text" name="username" value="{{ $user->username }}" class="settings-input">
        <span class="settings-hint">Letters, numbers, and underscores only. No spaces.</span>
        @error('username')<div class="settings-error">{{ $message }}</div>@enderror
    </div>
    <div class="settings-group">
        <label>Display Name</label>
        <input type="text" name="display_name" value="{{ $user->display_name }}" class="settings-input">
    </div>
    <div class="settings-group">
        <label>Email</label>
        <input type="email" name="email" value="{{ $user->email }}" class="settings-input">
        @if(!$user->hasVerifiedEmail())
            <span class="settings-hint" style="color:#faa61a;">⚠️ Email not verified.</span>
        @endif
        @error('email')<div class="settings-error">{{ $message }}</div>@enderror
    </div>
    <button type="submit" class="settings-v2-btn">Save Changes</button>
</form>
