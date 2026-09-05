<div id="profilePopup" class="profile-popup hidden">
    <div class="popup-content">
        <!-- Close Button -->
        <button class="popup-close" onclick="toggleProfilePopup()">✕</button>
        
        <!-- Avatar + Name -->
        <div class="popup-header">
            <div class="popup-avatar">
                @if(Auth::user()->profile && Auth::user()->profile->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->profile->avatar) }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                @else
                    {{ Auth::user()->name[0] ?? '?' }}
                @endif
            </div>
            <div class="popup-user-info">
                <div class="popup-name">{{ Auth::user()->display_name }}</div>
                <div class="popup-username">@ {{ Auth::user()->username ?? Auth::user()->name }}</div>
                <div class="popup-status" style="color: {{ Auth::user()->getStatusColor() }};">
                    {{ Auth::user()->getStatusLabel() }}
                </div>
                <div class="popup-pronouns">He/Him</div>
                <div class="popup-bio">AetherCore Developer</div>
            </div>
        </div>

        <hr class="popup-divider">

        <!-- Actions -->
        <div class="popup-actions">
            <a href="{{ route('profile.index') }}" class="popup-action">
                <span>📝</span> Edit Profile
            </a>
            <button class="popup-action" onclick="openSettingsModal()">
                <span>⚙️</span> Settings
            </button>
            <a href="#" class="popup-action">
                <span>🎵</span> Library
            </a>
        </div>

        <hr class="popup-divider">

        <!-- Bottom Actions -->
        <div class="popup-actions-bottom">
            <!-- Set Status Dropdown (Discord-style) -->
            <div class="popup-status-menu">
                <button class="popup-action" onclick="toggleStatusMenu()">
                    <span>●</span> Set Status
                </button>
                <div id="statusMenu" class="status-menu hidden">
                    <form action="{{ route('status.update') }}" method="POST" class="status-form">
                        @csrf
                        <button type="submit" name="status" value="online" class="status-option">
                            <span class="status-dot online">●</span> Online
                        </button>
                        <button type="submit" name="status" value="idle" class="status-option">
                            <span class="status-dot idle">◐</span> Idle
                        </button>
                        <button type="submit" name="status" value="dnd" class="status-option">
                            <span class="status-dot dnd">●</span> Do Not Disturb
                        </button>
                        <button type="submit" name="status" value="offline" class="status-option">
                            <span class="status-dot offline">○</span> Offline
                        </button>
                    </form>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0; width: 100%;">
                @csrf
                <button type="submit" class="popup-action" style="width: 100%; text-align: left; color: #ef4444;">
                    <span>🚪</span> Log Out
                </button>
            </form>
        </div>
    </div>
</div>