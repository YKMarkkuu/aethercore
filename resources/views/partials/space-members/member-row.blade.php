@php
    // Same permission/hierarchy computation as partials.sidebar-right's
    // member list — deliberately mirrored rather than refactored into a
    // shared helper, to keep this response's diff minimal.
    $memberRole = $space->getUserRole($member->user_id);
    $isTargetOwner = $space->isOwner($member->user_id);
    $targetPosition = $space->getHighestRolePosition($member->user_id);
    $canActOn = !$isTargetOwner && $targetPosition < $actorPosition;
    $canKick = $canActOn && $space->userHasPermission(auth()->id(), \App\Enums\SpacePermission::KICK_MEMBERS);
    $canBan = $canActOn && $space->userHasPermission(auth()->id(), \App\Enums\SpacePermission::BAN_MEMBERS);
    $canAssignRole = $canActOn && $space->userHasPermission(auth()->id(), \App\Enums\SpacePermission::MANAGE_ROLES);
@endphp
<div class="member-row-item" data-member-name="{{ strtolower($member->user->display_name) }}" data-member-role-id="{{ $memberRole->id ?? '' }}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.3rem; background: #f8f5ec; border: 1px solid #d0c8c0; border-radius: 4px; flex-wrap: wrap;">
    <a href="{{ route('profile.show', $member->user) }}" data-user-popover="{{ $member->user->id }}" style="display: flex; align-items: center; gap: 0.4rem; text-decoration: none; color: inherit; flex: 1; min-width: 160px;">
        <div class="space-member-avatar">
            @if($member->user->getAvatarUrl())
                <img src="{{ $member->user->getAvatarUrl() }}" alt="Avatar" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
            @else
                {{ $member->user->name[0] }}
            @endif
        </div>
        <div style="min-width: 0;">
            <div style="font-size: 0.75rem; font-weight: 600; color: #1e1e1e; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                {{ $member->user->display_name }}
                @if($member->role === 'owner')
                    <span class="space-member-owner-badge">Owner</span>
                @endif
            </div>
            @if($memberRole)
                <div style="display: flex; align-items: center; gap: 0.3rem; font-size: 0.6rem; color: #6a6a6a;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: {{ $memberRole->color }}; flex-shrink: 0;"></span>
                    {{ $memberRole->name }}
                </div>
            @endif
        </div>
    </a>

    @if($canKick || $canBan || $canAssignRole)
        <div style="display: flex; gap: 0.3rem; flex-wrap: wrap; align-items: center;">
            @if($canAssignRole)
                <form action="{{ route('space-members.assign-role', [$space, $member->user]) }}" method="POST">
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
                <form action="{{ route('space-members.kick', [$space, $member->user]) }}" method="POST" data-confirm="Kick this member from this Space?" data-confirm-danger="true" data-confirm-title="Kick Member" data-confirm-ok="Kick">
                    @csrf
                    <button type="submit" class="xp-action-btn xp-action-btn-danger" style="font-size: 0.6rem; padding: 0.05rem 0.4rem;">Kick</button>
                </form>
            @endif
            @if($canBan)
                <form action="{{ route('space-members.ban', [$space, $member->user]) }}" method="POST" data-confirm="Ban this member from this Space?" data-confirm-danger="true" data-confirm-title="Ban Member" data-confirm-ok="Ban">
                    @csrf
                    <button type="submit" class="xp-action-btn xp-action-btn-danger" style="font-size: 0.6rem; padding: 0.05rem 0.4rem;">Ban</button>
                </form>
            @endif
        </div>
    @endif
</div>