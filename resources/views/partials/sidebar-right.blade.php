<aside class="right-sidebar">
    @if(isset($space))
        <!-- ===== SPACE MEMBERS ===== -->
        <!-- $space is only set when this sidebar is rendered as part of
             spaces/show.blade.php — see SpaceController::show(). Blade
             shares data up through extends and back down through
             include, so this "just works" without any extra wiring. -->
        <div class="space-members-header">Members — {{ $space->members->count() }}</div>
        @foreach($space->members as $member)
            <a href="{{ route('profile.show', $member->user) }}" class="space-member-item">
                <div class="space-member-avatar">
                    @if($member->user->getAvatarUrl())
                        <img src="{{ $member->user->getAvatarUrl() }}" alt="Avatar" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                    @else
                        {{ $member->user->name[0] }}
                    @endif
                </div>
                <span class="space-member-name">{{ $member->user->display_name }}</span>
                @if($member->role === 'owner')
                    <span class="space-member-owner-badge">Owner</span>
                @endif
            </a>
        @endforeach
    @else
        <!-- ===== DEFAULT: YOUR PROFILE CARD ===== -->
        <div class="right-profile-avatar">
            @if(Auth::user()->profile && Auth::user()->profile->avatar)
                <img src="{{ asset('storage/' . Auth::user()->profile->avatar) }}" alt="Avatar" style="width: 72px; height: 72px; border-radius: 50%; object-fit: cover;">
            @else
                {{ Auth::user()->name[0] ?? '?' }}
            @endif
        </div>
        
        <div class="right-profile-name">{{ Auth::user()->display_name }}</div>
        <div class="right-profile-status" style="color: {{ Auth::user()->getStatusColor() }};">
            {{ Auth::user()->getStatusLabel() }}
        </div>
        <div class="right-profile-bio">{{ Auth::user()->profile->bio ?? 'Welcome to AetherCore!' }}</div>

        <hr class="right-profile-divider">

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
            <span style="font-weight: 600; color: #1e1e1e;">{{ $mySpaces->count() ?? 0 }}</span> Spaces
        </div>
    @endif
</aside>