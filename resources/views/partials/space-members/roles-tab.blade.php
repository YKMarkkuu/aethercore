@php
    $canManageRoles = $space->userHasPermission(auth()->id(), \App\Enums\SpacePermission::MANAGE_ROLES);
    $actorPosition = $space->getHighestRolePosition(auth()->id());
@endphp

@if($canManageRoles)
    <button type="button" class="settings-btn" style="margin-bottom: 0.6rem; display: inline-flex; align-items: center; gap: 4px;" onclick="toggleRoleEditor('new')">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Create Role
    </button>
    <div id="roleEditor-new" class="hidden" style="margin-bottom: 0.75rem; background: #f8f5ec; border: 1px solid #d0c8c0; border-radius: 4px; padding: 0.5rem;">
        @include('partials.space-members.role-editor', ['space' => $space, 'role' => null, 'actorPosition' => $actorPosition])
    </div>
@endif

<div style="display: flex; flex-direction: column; gap: 0.3rem;">
    @foreach($space->roles as $role)
        @php
            $memberCount = $role->members()->count();
            $permCount = count($role->permissions ?? []);
            $isOwnerRole = $role->is_owner;
            $isEditable = $canManageRoles && !$isOwnerRole && $role->position < $actorPosition;
        @endphp
        <div class="role-row-item" style="background: #f8f5ec; border: 1px solid #d0c8c0; border-radius: 4px;">
            @if($isEditable)
                <button type="button" onclick="toggleRoleEditor({{ $role->id }})" style="display: flex; align-items: center; gap: 0.5rem; width: 100%; box-sizing: border-box; padding: 0.4rem 0.5rem; background: none; border: none; cursor: pointer; font-family: inherit; text-align: left;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $role->color }}; flex-shrink: 0;"></span>
                    <span style="flex: 1; font-size: 0.75rem; font-weight: 600; color: #1e1e1e;">{{ $role->name }}</span>
                    <span style="font-size: 0.6rem; color: #6a6a6a;">{{ $memberCount }} {{ \Illuminate\Support\Str::plural('member', $memberCount) }} · {{ $permCount }} perms</span>
                </button>
                <div id="roleEditor-{{ $role->id }}" class="hidden" style="padding: 0 0.5rem 0.5rem;">
                    @include('partials.space-members.role-editor', ['space' => $space, 'role' => $role, 'actorPosition' => $actorPosition])
                </div>
            @else
                <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.4rem 0.5rem;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $role->color }}; flex-shrink: 0;"></span>
                    <span style="flex: 1; font-size: 0.75rem; font-weight: 600; color: #1e1e1e;">{{ $role->name }}</span>
                    <span style="font-size: 0.6rem; color: #6a6a6a;">{{ $memberCount }} {{ \Illuminate\Support\Str::plural('member', $memberCount) }} · {{ $permCount }} perms</span>
                </div>
            @endif
        </div>
    @endforeach
</div>