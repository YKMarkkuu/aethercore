<aside class="right-sidebar">
    @if(isset($space))
        <!-- ===== SPACE MEMBERS ===== -->
        <!-- $space is only set when this sidebar is rendered as part of
             spaces/show.blade.php — see SpaceController::show(). Blade
             shares data up through extends and back down through
             include, so this "just works" without any extra wiring. -->
        <div class="space-members-header">Members — {{ $space->members->count() }}</div>
        @foreach($space->members as $member)
            @php
                $memberRole = $space->getUserRole($member->user_id);
                $isTargetOwner = $space->isOwner($member->user_id);
                $actorPosition = $space->getHighestRolePosition(auth()->id());
                $targetPosition = $space->getHighestRolePosition($member->user_id);
                $canActOn = !$isTargetOwner && $targetPosition < $actorPosition;
                $canKick = $canActOn && $space->userHasPermission(auth()->id(), \App\Enums\SpacePermission::KICK_MEMBERS);
                $canBan = $canActOn && $space->userHasPermission(auth()->id(), \App\Enums\SpacePermission::BAN_MEMBERS);
                $canAssignRole = $canActOn && $space->userHasPermission(auth()->id(), \App\Enums\SpacePermission::MANAGE_ROLES);
            @endphp
            <div class="space-member-item" style="flex-direction: column; align-items: stretch; gap: 0.2rem;">
                <a href="{{ route('profile.show', $member->user) }}" data-user-popover="{{ $member->user->id }}" style="display: flex; align-items: center; gap: 0.4rem; text-decoration: none; color: inherit;">
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

                @if($memberRole)
                    <span style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.6rem; color: #6a6a6a; padding-left: 32px;">
                        <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $memberRole->color }}; flex-shrink: 0;"></span>
                        {{ $memberRole->name }}
                    </span>
                @endif

                @if($canKick || $canBan || $canAssignRole)
                    <div style="display: flex; gap: 0.3rem; flex-wrap: wrap; padding-left: 32px;">
                        @if($canAssignRole)
                            <form action="{{ route('space-members.assign-role', [$space, $member->user]) }}" method="POST" style="display: flex; gap: 0.2rem; align-items: center;">
                                @csrf
                                <select name="role_id" class="settings-input" style="width: auto; font-size: 0.6rem; padding: 0.1rem 0.3rem;" onchange="this.form.requestSubmit()">
                                    @foreach($space->roles as $role)
                                        @if($role->position < $actorPosition)
                                            <option value="{{ $role->id }}" @selected($memberRole && $memberRole->id === $role->id)>{{ $role->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </form>
                        @endif
                        @if($canKick)
                            <form action="{{ route('space-members.kick', [$space, $member->user]) }}" method="POST" onsubmit="return confirm('Kick {{ $member->user->display_name }} from this Space?')">
                                @csrf
                                <button type="submit" class="xp-action-btn xp-action-btn-danger" style="font-size: 0.6rem; padding: 0.05rem 0.4rem;">Kick</button>
                            </form>
                        @endif
                        @if($canBan)
                            <form action="{{ route('space-members.ban', [$space, $member->user]) }}" method="POST" onsubmit="return confirm('Ban {{ $member->user->display_name }} from this Space?')">
                                @csrf
                                <button type="submit" class="xp-action-btn xp-action-btn-danger" style="font-size: 0.6rem; padding: 0.05rem 0.4rem;">Ban</button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
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
        <div class="right-profile-bio" id="rightProfileBio">{{ Auth::user()->profile->bio ?? '' }}</div>

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