@php
    $actorPosition = $space->getHighestRolePosition(auth()->id());
    $sortedMembers = $space->members
        ->sortByDesc(fn ($member) => $space->getHighestRolePosition($member->user_id))
        ->values();
@endphp

<div style="display: flex; gap: 0.5rem; margin-bottom: 0.6rem; flex-wrap: wrap;">
    <input type="text" id="membersSearchInput" class="settings-input" placeholder="Search members..." style="flex: 1; min-width: 160px;" oninput="filterMembersList()">
    <select id="membersRoleFilter" class="settings-input" style="width: auto;" onchange="filterMembersList()">
        <option value="">All Roles</option>
        @foreach($space->roles as $role)
            <option value="{{ $role->id }}">{{ $role->name }}</option>
        @endforeach
    </select>
</div>

<div id="membersListBody" style="display: flex; flex-direction: column; gap: 0.3rem;">
    @forelse($sortedMembers as $member)
        @include('partials.space-members.member-row', ['space' => $space, 'member' => $member, 'actorPosition' => $actorPosition])
    @empty
        <p style="font-size: 0.75rem; color: #6a6a6a; text-align: center; padding: 1rem 0;">No members found.</p>
    @endforelse
    <p id="membersNoMatches" class="hidden" style="font-size: 0.75rem; color: #6a6a6a; text-align: center; padding: 1rem 0;">No members match your search.</p>
</div>