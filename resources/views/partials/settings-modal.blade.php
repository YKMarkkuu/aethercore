<div id="settingsModal" class="settings-modal hidden">
    <div class="settings-modal-content">
        <div class="settings-modal-header">
            <h2>⚙️ Settings</h2>
            <button class="settings-modal-close" onclick="closeSettings()">✕</button>
        </div>

        <div class="settings-modal-body">
            <!-- ===== LEFT SIDEBAR (Categories) ===== -->
            <div class="settings-nav">
                <div class="settings-nav-category">Account</div>
                <button class="settings-nav-item active" onclick="switchSettingsTab('account')">Account Info</button>
                <button class="settings-nav-item" onclick="switchSettingsTab('security')">Password & Security</button>
                
                <div class="settings-nav-category">Profile</div>
                <button class="settings-nav-item" onclick="switchSettingsTab('profile')">Profile Settings</button>
                
                <div class="settings-nav-category">Appearance</div>
                <button class="settings-nav-item" onclick="switchSettingsTab('theme')">Theme</button>
                
                <div class="settings-nav-category">Music</div>
                <button class="settings-nav-item" onclick="switchSettingsTab('lastfm')">Last.fm Connection</button>
                
                <div class="settings-nav-category">Data</div>
                <button class="settings-nav-item" onclick="switchSettingsTab('danger')">Delete Account</button>
            </div>

            <!-- ===== RIGHT CONTENT ===== -->
            <div class="settings-content">
                <!-- Account Info -->
                <div id="settings-account" class="settings-tab">
                    <h3>Account Info</h3>
                    <p class="settings-subtitle">View and manage your account details</p>

                    @if(session('success'))
                        <div class="settings-alert success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="settings-alert error">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('settings.account') }}" method="POST">
                        @csrf
                        <div class="settings-group">
                            <label>Username</label>
                            <input type="text" name="username" value="{{ Auth::user()->username }}" class="settings-input">
                            <span class="settings-hint">Letters, numbers, and underscores only. No spaces.</span>
                            @error('username')
                                <div class="settings-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="settings-group">
                            <label>Display Name</label>
                            <input type="text" name="display_name" value="{{ Auth::user()->display_name }}" class="settings-input">
                            <span class="settings-hint">What others see on your profile.</span>
                        </div>

                        <div class="settings-group">
                            <label>Email</label>
                            <input type="email" name="email" value="{{ Auth::user()->email }}" class="settings-input">
                            @if(!Auth::user()->hasVerifiedEmail())
                                <span class="settings-hint" style="color: #c9a840;">⚠️ Email not verified.</span>
                            @endif
                            @error('email')
                                <div class="settings-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="settings-btn">Save Changes</button>
                    </form>
                </div>

                <!-- Password & Security -->
                <div id="settings-security" class="settings-tab hidden">
                    <h3>Password & Security</h3>
                    <p class="settings-subtitle">Keep your account secure</p>

                    <form action="{{ route('settings.account') }}" method="POST">
                        @csrf
                        <div class="settings-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" placeholder="Enter current password" class="settings-input">
                            <span class="settings-hint">Required to change your password.</span>
                            @error('current_password')
                                <div class="settings-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="settings-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" placeholder="Enter new password" class="settings-input">
                            <span class="settings-hint">Minimum 8 characters.</span>
                            @error('new_password')
                                <div class="settings-error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="settings-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="new_password_confirmation" placeholder="Confirm new password" class="settings-input">
                        </div>

                        <button type="submit" class="settings-btn">Change Password</button>
                    </form>
                </div>

                <!-- Profile Settings -->
                <div id="settings-profile" class="settings-tab hidden">
                    <h3>Profile Settings</h3>
                    <p class="settings-subtitle">Manage your profile visibility and preferences</p>

                    <div class="settings-group">
                        <label>Profile Visibility</label>
                        <select class="settings-input">
                            <option>Public</option>
                            <option>Friends Only</option>
                            <option>Private</option>
                        </select>
                    </div>

                    <div class="settings-group">
                        <label>Show Online Status</label>
                        <select class="settings-input">
                            <option>Everyone</option>
                            <option>Friends Only</option>
                            <option>No One</option>
                        </select>
                    </div>

                    <button class="settings-btn">Save Profile Settings</button>
                </div>

                <!-- Theme -->
                <div id="settings-theme" class="settings-tab hidden">
                    <h3>Theme</h3>
                    <p class="settings-subtitle">Customize your AetherCore experience</p>

                    <form action="{{ route('settings.theme') }}" method="POST">
                        @csrf
                        <div class="settings-group">
                            <label>Select Theme</label>
                            <select name="theme" class="settings-input">
                                @foreach(Auth::user()->getAvailableThemes() as $key => $label)
                                    <option value="{{ $key }}" {{ Auth::user()->theme == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <span class="settings-hint">Changes will apply on page refresh.</span>
                        </div>
                        <button type="submit" class="settings-btn">Save Theme</button>
                    </form>
                </div>

                <!-- Last.fm -->
                <div id="settings-lastfm" class="settings-tab hidden">
                    <h3>Last.fm Connection</h3>
                    <p class="settings-subtitle">Connect your Last.fm account to show your music taste</p>

                    <form action="{{ route('settings.lastfm') }}" method="POST">
                        @csrf
                        <div class="settings-group">
                            <label>Last.fm Username</label>
                            <input type="text" name="lastfm_username" placeholder="Enter your Last.fm username" class="settings-input" value="{{ old('lastfm_username', Auth::user()->lastfm_username ?? '') }}">
                            <span class="settings-hint">Your top artists, songs, and albums will appear on your profile.</span>
                            <span class="settings-hint" style="color: #6a6a6a;">📌 You'll need a free account at <a href="https://www.last.fm" target="_blank" style="color: #1a4a9e;">last.fm</a>.</span>
                        </div>
                        <button type="submit" class="settings-btn">Connect Last.fm</button>
                    </form>
                </div>

                <!-- Danger Zone -->
                <div id="settings-danger" class="settings-tab hidden">
                    <h3>Delete Account</h3>
                    <p class="settings-subtitle" style="color: #6a2a2a;">⚠️ This action cannot be undone</p>

                    <div style="background: #f0d8d8; border: 1px solid #c8a0a0; border-radius: 4px; padding: 0.75rem; margin-bottom: 1rem;">
                        <p style="font-size: 0.8rem; color: #6a2a2a; margin: 0;">
                            Deleting your account will permanently remove all your data, including posts, friends, messages, and profile information.
                        </p>
                    </div>

                    <form action="{{ route('settings.delete') }}" method="POST" onsubmit="return confirm('⚠️ Are you sure you want to delete your account? This cannot be undone!')">
                        @csrf
                        @method('DELETE')
                        <div class="settings-group">
                            <label>Confirm with Password</label>
                            <input type="password" name="password" placeholder="Enter your password to confirm" class="settings-input">
                            @error('password')
                                <div class="settings-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="settings-btn settings-btn-danger">Delete Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>