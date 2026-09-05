<aside class="right-sidebar">
    <!-- Profile Avatar -->
    <div class="right-profile-avatar">
        @if(Auth::user()->profile && Auth::user()->profile->avatar)
            <img src="{{ asset('storage/' . Auth::user()->profile->avatar) }}" alt="Avatar" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover;">
        @else
            {{ Auth::user()->name[0] ?? '?' }}
        @endif
    </div>
    
    <!-- Profile Name & Status -->
    <div class="right-profile-name">{{ Auth::user()->display_name }}</div>
    <div class="right-profile-status" style="color: {{ Auth::user()->getStatusColor() }};">
        {{ Auth::user()->getStatusLabel() }}
    </div>
    <div class="right-profile-bio">{{ Auth::user()->profile->bio ?? 'Welcome to AetherCore!' }}</div>

    <hr class="right-profile-divider">

    <!-- Stats -->
    <div class="xp-right-stats">
        <div class="stat-row">
            <div class="stat-item">
                <div class="stat-number">0</div>
                <div class="stat-label">Artists</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">0</div>
                <div class="stat-label">Albums</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">0</div>
                <div class="stat-label">Playlists</div>
            </div>
        </div>
    </div>

    <hr class="right-profile-divider">

    <!-- Friends & Spaces Count -->
    <div style="text-align: center; font-size: 0.7rem; color: #6a6a6a;">
        <span style="font-weight: 600; color: #1e1e1e;">{{ Auth::user()->getFriends()->count() }}</span> Friends
        &bull;
        <span style="font-weight: 600; color: #1e1e1e;">0</span> Spaces
    </div>
</aside>