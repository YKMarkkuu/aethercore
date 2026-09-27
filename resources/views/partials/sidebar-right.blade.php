<aside class="right-sidebar">
    @if(isset($space))
        <!-- ===== SPACE MEMBERS ===== -->
        @php
            $membersByRole = $space->members->groupBy(function ($member) use ($space) {
                return $space->getUserRole($member->user_id)?->id;
            });
            $spaceRoles = $space->roles->sortByDesc('position');
        @endphp
        <div class="space-members-header">Members — {{ $space->members->count() }}</div>
        @foreach($spaceRoles as $role)
            @php
                $roleMembers = $membersByRole->get($role->id, collect());
            @endphp
            @if($roleMembers->isNotEmpty())
                <div class="space-member-group" style="margin-bottom: 0.45rem;">
                    <div class="space-member-group-header" style="display: flex; align-items: center; gap: 0.25rem; margin-bottom: 0.2rem; font-size: 0.6rem; text-transform: uppercase; color: #6a6a6a; font-weight: 700;">
                        <svg viewBox="0 0 24 24" width="8" height="8" aria-hidden="true">
                            <circle cx="12" cy="12" r="7" fill="{{ $role->color }}"/>
                        </svg>
                        {{ $role->is_owner ? 'Owner' : $role->name }} ({{ $roleMembers->count() }})
                    </div>
                    @foreach($roleMembers as $member)
                        @php
                            $status = $member->user->getEffectiveStatus();
                        @endphp
                        <a href="{{ route('profile.show', $member->user) }}" class="space-member-item" data-space-member-row data-member-id="{{ $member->user->id }}" data-user-popover="{{ $member->user->id }}">
                            <div class="space-member-avatar" style="position: relative;">
                                @if($member->user->getAvatarUrl())
                                    <img src="{{ $member->user->getAvatarUrl() }}" alt="Avatar" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                                @else
                                    {{ $member->user->name[0] }}
                                @endif
                                <span class="space-member-status status-icon-{{ $status }}" style="position: absolute; right: -2px; bottom: -2px; box-sizing: content-box; width: 10px; height: 10px; border: 2px solid #ffffff; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                    <svg viewBox="0 0 24 24" width="10" height="10" aria-hidden="true"><circle cx="12" cy="12" r="7" fill="currentColor"/></svg>
                                </span>
                            </div>
                            <span class="space-member-name">{{ $member->user->display_name }}</span>
                            @if($space->isOwner($member->user_id))
                                <span class="space-member-owner-badge">Owner</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
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

@if(isset($space))
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rows = Array.from(document.querySelectorAll('[data-space-member-row]'));
            if (rows.length === 0) return;

            const statusClasses = ['status-icon-online', 'status-icon-idle', 'status-icon-dnd', 'status-icon-offline'];
            const endpoint = @json(url('/now-playing'));

            function pollSpaceMembersPresence() {
                rows.forEach(row => {
                    const statusIcon = row.querySelector('.space-member-status');
                    if (!statusIcon) return;

                    fetch(endpoint + '/' + encodeURIComponent(row.dataset.memberId), {
                        headers: { 'Accept': 'application/json' },
                    })
                        .then(response => response.ok ? response.json() : Promise.reject())
                        .then(data => {
                            const status = statusClasses.some(className => className === 'status-icon-' + data.status)
                                ? data.status
                                : 'offline';
                            statusIcon.classList.remove(...statusClasses);
                            statusIcon.classList.add('status-icon-' + status);
                        })
                        .catch(() => { /* Try again on the next poll. */ });
                });
            }

            pollSpaceMembersPresence();
            setInterval(pollSpaceMembersPresence, 15000);
        });
    </script>
    @endpush
@endif