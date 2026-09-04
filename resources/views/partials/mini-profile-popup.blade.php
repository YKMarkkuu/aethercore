<div id="profilePopup" class="profile-popup hidden">
    <div class="popup-content">
        <!-- Close Button -->
        <button class="popup-close" onclick="toggleProfilePopup()">✕</button>
        
        <!-- Avatar + Name -->
        <div class="popup-header">
            <div class="popup-avatar">{{ Auth::user()->name[0] ?? '?' }}</div>
            <div class="popup-user-info">
                <div class="popup-name">{{ Auth::user()->display_name }}</div>
                <div class="popup-username">@ {{ Auth::user()->username ?? Auth::user()->name }}</div>
                <div class="popup-status">🟢 Online</div>
                <div class="popup-pronouns">He/Him</div>
                <div class="popup-bio">✨ AetherCore Developer</div>
            </div>
        </div>

        <hr class="popup-divider">

        <!-- Actions -->
        <div class="popup-actions">
            <a href="{{ route('profile.index') }}" class="popup-action">
                <span>📝</span> Edit Profile
            </a>
            <button class="popup-action" onclick="openSettings()">
                <span>⚙️</span> Settings
            </button>
            <a href="#" class="popup-action">
                <span>🎵</span> Library
            </a>
        </div>

        <hr class="popup-divider">

        <!-- Bottom Actions -->
        <div class="popup-actions-bottom">
            <button class="popup-action">
                <span>🌙</span> Set Status
            </button>
            <form method="POST" action="{{ route('logout') }}" style="margin: 0; width: 100%;">
                @csrf
                <button type="submit" class="popup-action" style="width: 100%; text-align: left; color: #ef4444;">
                    <span>🚪</span> Log Out
                </button>
            </form>
        </div>
    </div>
</div>