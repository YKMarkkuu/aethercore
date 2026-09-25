@php
    $user = $user ?? Auth::user();

    $categories = [
        'account' => [
            'label' => 'Account',
            'subs' => [
                'account-info' => 'Profile Info',
                'security' => 'Password & Security',
                'sessions' => 'Sessions & Devices',
            ],
        ],
        'privacy' => [
            'label' => 'Privacy',
            'subs' => [
                'privacy-visibility' => 'Profile Visibility',
                'privacy-messages' => 'Direct Messages',
                'privacy-status' => 'Activity Status',
            ],
        ],
        'profile' => [
            'label' => 'Profile',
            'subs' => ['profile-settings' => 'Profile Settings'],
        ],
        'appearance' => [
            'label' => 'Appearance',
            'subs' => ['theme' => 'Theme'],
        ],
        'music' => [
            'label' => 'Music',
            'subs' => ['lastfm' => 'Last.fm Connection'],
        ],
        'data' => [
            'label' => 'Your Data',
            'subs' => ['data-export' => 'Download Your Data'],
        ],
        'danger' => [
            'label' => 'Danger Zone',
            'subs' => ['danger-delete' => 'Delete Account'],
        ],
    ];
@endphp

<div class="settings-nav" data-settings-nav>
    @foreach($categories as $catKey => $cat)
        <div class="settings-accordion-group" data-accordion-key="{{ $catKey }}">
            <button type="button" class="settings-nav-category-btn" data-accordion-toggle="{{ $catKey }}">
                <span>{{ $cat['label'] }}</span>
                <span class="settings-accordion-arrow">▾</span>
            </button>
            <div class="settings-subnav hidden" data-accordion-panel="{{ $catKey }}">
                @foreach($cat['subs'] as $subKey => $subLabel)
                    <button type="button" class="settings-nav-item" data-settings-target="{{ $subKey }}">{{ $subLabel }}</button>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

<div class="settings-content" data-settings-content>

    @if(session('success'))
        <div class="settings-alert success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="settings-alert error">{{ session('error') }}</div>
    @endif

    {{-- ===== ACCOUNT: PROFILE INFO ===== --}}
    <div id="settings-account-info" class="settings-tab hidden">
        <h3>Profile Info</h3>
        <p class="settings-subtitle">Your username, display name, and email address.</p>

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
                    <span class="settings-hint" style="color: #c9a840;">⚠️ Email not verified.</span>
                @endif
                @error('email')<div class="settings-error">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="settings-btn">Save Changes</button>
        </form>
    </div>

    {{-- ===== ACCOUNT: SECURITY ===== --}}
    <div id="settings-security" class="settings-tab hidden">
        <h3>Password & Security</h3>
        <p class="settings-subtitle">Keep your account secure.</p>

        <form action="{{ route('settings.account') }}" method="POST">
            @csrf
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
            <button type="submit" class="settings-btn">Change Password</button>
        </form>
    </div>

    {{-- ===== ACCOUNT: SESSIONS ===== --}}
    <div id="settings-sessions" class="settings-tab hidden">
        <h3>Sessions & Devices</h3>
        <p class="settings-subtitle">See where you're logged in, and log out other sessions.</p>

        @if(!($sessionDriverSupported ?? false))
            <p class="settings-hint">Session management isn't available yet on this server — it requires the database session driver.</p>
        @else
            @forelse(($settingsSessions ?? []) as $s)
                <div class="device-item">
                    <div>
                        <div class="device-name">
                            {{ \Illuminate\Support\Str::limit($s->user_agent ?? 'Unknown device', 60) }}
                            @if($s->id === ($currentSessionId ?? null))
                                <span class="device-status">This device</span>
                            @endif
                        </div>
                        <div class="device-detail">{{ $s->ip_address }} · last active {{ \Carbon\Carbon::createFromTimestamp($s->last_activity)->diffForHumans() }}</div>
                    </div>
                    @if($s->id !== ($currentSessionId ?? null))
                        <form action="{{ route('settings.sessions.destroy', $s->id) }}" method="POST">
                            @csrf @method('DELETE')
                            <button type="submit" class="device-logout-btn">Log Out</button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="settings-hint">No other active sessions found.</p>
            @endforelse
        @endif
    </div>

    {{-- ===== PRIVACY: VISIBILITY ===== --}}
    <div id="settings-privacy-visibility" class="settings-tab hidden">
        <h3>Profile Visibility</h3>
        <p class="settings-subtitle">Who can view your profile.</p>

        <form action="{{ route('settings.privacy') }}" method="POST">
            @csrf
            <div class="settings-group">
                <select name="visibility" class="settings-input" onchange="this.form.submit()">
                    <option value="public" @selected(($user->profile->visibility ?? 'public') === 'public')>Public — anyone</option>
                    <option value="friends" @selected(($user->profile->visibility ?? 'public') === 'friends')>Friends only</option>
                    <option value="private" @selected(($user->profile->visibility ?? 'public') === 'private')>Private — only you</option>
                </select>
            </div>
        </form>
    </div>

    {{-- ===== PRIVACY: MESSAGES ===== --}}
    <div id="settings-privacy-messages" class="settings-tab hidden">
        <h3>Direct Messages</h3>
        <p class="settings-subtitle">Who can send you a DM.</p>

        <form action="{{ route('settings.privacy') }}" method="POST">
            @csrf
            <div class="settings-group">
                <select name="dm_permission" class="settings-input" onchange="this.form.submit()">
                    <option value="everyone" @selected(($user->profile->dm_permission ?? 'everyone') === 'everyone')>Everyone</option>
                    <option value="friends" @selected(($user->profile->dm_permission ?? 'everyone') === 'friends')>Friends only</option>
                    <option value="nobody" @selected(($user->profile->dm_permission ?? 'everyone') === 'nobody')>Nobody</option>
                </select>
            </div>
        </form>
    </div>

    {{-- ===== PRIVACY: STATUS ===== --}}
    <div id="settings-privacy-status" class="settings-tab hidden">
        <h3>Activity Status</h3>
        <p class="settings-subtitle">Who can see whether you're online and what you're listening to.</p>

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
    </div>

    {{-- ===== PROFILE SETTINGS (placeholder kept, unchanged behavior) ===== --}}
    <div id="settings-profile-settings" class="settings-tab hidden">
        <h3>Profile Settings</h3>
        <p class="settings-subtitle">Use the Privacy tab for visibility, messaging, and status controls.</p>
        <a href="{{ route('profile.index') }}" class="settings-btn" style="text-decoration:none;">Edit Profile</a>
    </div>

    {{-- ===== THEME ===== --}}
    <div id="settings-theme" class="settings-tab hidden">
        <h3>Theme</h3>
        <p class="settings-subtitle">Customize your AetherCore experience.</p>

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
    </div>

    {{-- ===== LAST.FM ===== --}}
    <div id="settings-lastfm" class="settings-tab hidden">
        <h3>Last.fm Connection</h3>
        <form action="{{ route('settings.lastfm') }}" method="POST">
            @csrf
            <div class="settings-group">
                <label>Last.fm Username</label>
                <input type="text" name="lastfm_username" class="settings-input" value="{{ old('lastfm_username', $user->lastfm_username ?? '') }}">
            </div>
            <button type="submit" class="settings-btn">Connect Last.fm</button>
        </form>
    </div>

    {{-- ===== DATA EXPORT ===== --}}
    <div id="settings-data-export" class="settings-tab hidden">
        <h3>Download Your Data</h3>
        <p class="settings-subtitle">Get a copy of your account, profile, and posts as a JSON file.</p>
        <a href="{{ route('settings.export') }}" class="settings-btn" style="text-decoration:none;" data-no-ajax>Download My Data</a>
    </div>

    {{-- ===== DANGER ZONE ===== --}}
    <div id="settings-danger-delete" class="settings-tab hidden">
        <h3>Delete Account</h3>
        <p class="settings-subtitle" style="color: #6a2a2a;">⚠️ This cannot be undone.</p>
        <form action="{{ route('settings.delete') }}" method="POST" onsubmit="return confirm('Delete your account? This cannot be undone.')">
            @csrf @method('DELETE')
            <div class="settings-group">
                <input type="password" name="password" placeholder="Enter your password to confirm" class="settings-input">
                @error('password')<div class="settings-error">{{ $message }}</div>@enderror
            </div>
            <button type="submit" class="settings-btn settings-btn-danger">Delete Account</button>
        </form>
    </div>

</div>
