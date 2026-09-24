<div id="profilePopup" class="profile-popup hidden">
    <button type="button" class="popup-close" onclick="toggleProfilePopup()">✕</button>

    <div class="popup-banner" @if(Auth::user()->getBannerUrl()) style="background-image:url('{{ Auth::user()->getBannerUrl() }}')" @endif></div>

    <div class="popup-avatar-wrap">
        <div class="popup-avatar">
            @if(Auth::user()->getAvatarUrl())
                <img src="{{ Auth::user()->getAvatarUrl() }}" alt="Avatar">
            @else
                {{ Auth::user()->name[0] ?? '?' }}
            @endif
        </div>
    </div>
    <div class="popup-user-info">
        <div class="popup-name">{{ Auth::user()->display_name }}</div>
        <div class="popup-username">@ {{ Auth::user()->username ?? Auth::user()->name }}</div>
        <div class="popup-status" id="popupStatusLine">
            <span class="status-dot-mini status-dot-{{ Auth::user()->getEffectiveStatus() }}" id="popupStatusDot"></span>
            <span id="popupStatusLabel">{{ Auth::user()->getStatusLabel() }}</span>
        </div>
    </div>

    @if(Auth::user()->profile->status_message ?? null)
        <div class="popup-note">"{{ Auth::user()->profile->status_message }}"</div>
    @endif

    <hr class="popup-divider">

    <div class="popup-actions">
        <a href="{{ route('profile.index') }}" class="popup-action">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            Edit Profile
        </a>
        <button type="button" class="popup-action" onclick="openSettingsModal()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            Settings
        </button>
        @if(Auth::user()->isAdmin())
            <a href="{{ route('admin.reports.index') }}" class="popup-action">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Admin Dashboard
            </a>
        @endif
    </div>

    <hr class="popup-divider">

    <div class="popup-actions-bottom">
        <div class="popup-status-menu">
            <button class="popup-action" onclick="toggleStatusMenu()">
                <span class="popup-action-icon status-icon-{{ Auth::user()->getEffectiveStatus() }}" id="popupSetStatusIcon">
                    <svg viewBox="0 0 24 24" width="16" height="16"><circle cx="12" cy="12" r="7" fill="currentColor"/></svg>
                </span>
                Set Status
            </button>
            <div id="statusMenu" class="status-menu hidden">
                <div class="status-form">
                    <button type="button" class="status-option" data-set-status="online">
                        <span class="popup-action-icon status-icon-online"><svg viewBox="0 0 24 24" width="16" height="16"><circle cx="12" cy="12" r="7" fill="currentColor"/></svg></span>Online
                    </button>
                    <button type="button" class="status-option" data-set-status="idle">
                        <span class="popup-action-icon status-icon-idle"><svg viewBox="0 0 24 24" width="16" height="16"><circle cx="12" cy="12" r="7" fill="currentColor"/></svg></span>Idle
                    </button>
                    <button type="button" class="status-option" data-set-status="dnd">
                        <span class="popup-action-icon status-icon-dnd"><svg viewBox="0 0 24 24" width="16" height="16"><circle cx="12" cy="12" r="7" fill="currentColor"/></svg></span>Do Not Disturb
                    </button>
                    <button type="button" class="status-option" data-set-status="offline">
                        <span class="popup-action-icon status-icon-offline"><svg viewBox="0 0 24 24" width="16" height="16"><circle cx="12" cy="12" r="7" fill="currentColor"/></svg></span>Offline
                    </button>
                </div>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" style="margin: 0; width: 100%;">
            @csrf
            <button type="submit" class="popup-action popup-action-danger">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                Log Out
            </button>
        </form>
    </div>
</div>