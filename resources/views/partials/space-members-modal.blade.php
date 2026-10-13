<!-- ===== SPACE SETTINGS MODAL ===== -->
<!-- Included once from spaces/show.blade.php (members only; non-members
     get the join prompt instead). Tabs: General (owner only), Members,
     Roles, Danger Zone. Every tab is a [data-tab] button whose pane is
     #{tab}TabPane, so switchMembersTab() is one loop. -->
@php
    $isSpaceOwner = $space->isOwner(auth()->id());
    $defaultTab = $isSpaceOwner ? 'general' : 'members';
@endphp
<style>
    .members-tab-btn.active { background: var(--accent); color: #ffffff; border-color: var(--accent-dark); }
</style>
<div class="settings-modal hidden" id="membersModal">
    <div class="settings-modal-content" style="max-width: 800px; width: 90%; height: auto; max-height: 82vh; display: flex; flex-direction: column;">
        <div class="settings-modal-header">
            <h2 style="display: flex; align-items: center; gap: 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                Settings — {{ $space->name }}
            </h2>
            <button class="settings-modal-close" onclick="toggleMembersModal()">✕</button>
        </div>

        <div style="display: flex; gap: 0.4rem; padding: 0.5rem 1rem 0; flex-wrap: wrap;">
            @if($isSpaceOwner)
                <button type="button" class="settings-btn members-tab-btn {{ $defaultTab === 'general' ? 'active' : '' }}" data-tab="general" onclick="switchMembersTab('general')">General</button>
            @endif
            <button type="button" class="settings-btn members-tab-btn {{ $defaultTab === 'members' ? 'active' : '' }}" data-tab="members" onclick="switchMembersTab('members')">Members</button>
            <button type="button" class="settings-btn members-tab-btn" data-tab="roles" onclick="switchMembersTab('roles')">Roles</button>
            <button type="button" class="settings-btn members-tab-btn" data-tab="danger" onclick="switchMembersTab('danger')">Danger Zone</button>
        </div>

        <div style="padding: 0.75rem 1rem; overflow-y: auto;">
            @if($isSpaceOwner)
                <div id="generalTabPane" class="{{ $defaultTab === 'general' ? '' : 'hidden' }}">
                    @include('partials.space-members.general-tab', ['space' => $space])
                </div>
            @endif
            <div id="membersTabPane" class="{{ $defaultTab === 'members' ? '' : 'hidden' }}">
                @include('partials.space-members.member-list', ['space' => $space])
            </div>
            <div id="rolesTabPane" class="hidden">
                @include('partials.space-members.roles-tab', ['space' => $space])
            </div>
            <div id="dangerTabPane" class="hidden">
                @include('partials.space-members.danger-tab', ['space' => $space])
            </div>
            @include('partials.space-members.role-delete-confirm')
        </div>
    </div>
</div>

<script>
    function toggleMembersModal() {
        document.getElementById('membersModal').classList.toggle('hidden');
    }

    // One loop over the rendered [data-tab] buttons. A tab that isn't
    // rendered for this viewer (General, for non-owners) simply has no
    // button, so it can never be activated.
    function switchMembersTab(tab) {
        document.querySelectorAll('#membersModal [data-tab]').forEach(btn => {
            const on = btn.dataset.tab === tab;
            btn.classList.toggle('active', on);
            document.getElementById(btn.dataset.tab + 'TabPane')?.classList.toggle('hidden', !on);
        });
    }

    // Combines the search box and role dropdown into one filter pass over
    // the already-rendered rows — no fetch, since the full member list is
    // server-rendered into the modal on page load.
    function filterMembersList() {
        const search = (document.getElementById('membersSearchInput')?.value || '').trim().toLowerCase();
        const roleId = document.getElementById('membersRoleFilter')?.value || '';
        const rows = document.querySelectorAll('#membersListBody .member-row-item');
        let visibleCount = 0;

        rows.forEach(row => {
            const matchesSearch = !search || row.dataset.memberName.includes(search);
            const matchesRole = !roleId || row.dataset.memberRoleId === roleId;
            const visible = matchesSearch && matchesRole;
            row.style.display = visible ? '' : 'none';
            if (visible) visibleCount++;
        });

        document.getElementById('membersNoMatches')?.classList.toggle('hidden', visibleCount !== 0);
    }

    // key is either a numeric SpaceRole id or the string 'new' (the
    // create-role editor).
    function toggleRoleEditor(key) {
        document.getElementById('roleEditor-' + key)?.classList.toggle('hidden');
    }

    function openRoleDeleteConfirm(roleId, roleName, previewUrl, destroyUrl) {
        fetch(previewUrl, { headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(data => {
                document.getElementById('roleDeleteConfirmTitle').textContent = 'Delete Role — ' + roleName;
                document.getElementById('roleDeleteConfirmWarning').textContent = data.member_count > 0
                    ? data.member_count + ' member' + (data.member_count === 1 ? '' : 's') + ' will be reassigned.'
                    : 'No members currently hold this role.';

                const membersWrap = document.getElementById('roleDeleteConfirmMembers');
                membersWrap.replaceChildren();

                data.members.forEach(m => {
                    const row = document.createElement('div');
                    row.style.cssText = 'display:flex;align-items:center;gap:0.4rem;font-size:0.7rem;color:#1e1e1e;';

                    const avatar = document.createElement('span');
                    avatar.style.cssText = 'width:20px;height:20px;border-radius:50%;background:linear-gradient(135deg,#3a7bd5,#1a4a9e);display:flex;align-items:center;justify-content:center;color:#fff;font-size:0.55rem;font-weight:700;flex-shrink:0;overflow:hidden;';
                    if (m.avatar_url) {
                        const img = document.createElement('img');
                        img.src = m.avatar_url;
                        img.alt = '';
                        img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
                        avatar.appendChild(img);
                    } else {
                        avatar.textContent = (m.display_name || '?').charAt(0).toUpperCase();
                    }
                    row.appendChild(avatar);

                    const name = document.createElement('span');
                    name.textContent = m.display_name;
                    row.appendChild(name);

                    membersWrap.appendChild(row);
                });

                if (data.member_count > data.members.length) {
                    const more = document.createElement('div');
                    more.style.cssText = 'font-size:0.65rem;color:#6a6a6a;';
                    more.textContent = '+ ' + (data.member_count - data.members.length) + ' more';
                    membersWrap.appendChild(more);
                }

                const reassignWrap = document.getElementById('roleDeleteConfirmReassignWrap');
                const select = document.getElementById('roleDeleteConfirmReassignSelect');
                const hiddenInput = document.getElementById('roleDeleteConfirmReassignInput');
                select.replaceChildren();

                if (data.member_count > 0) {
                    reassignWrap.classList.remove('hidden');

                    if (data.default_role) {
                        const opt = document.createElement('option');
                        opt.value = data.default_role.id;
                        opt.textContent = data.default_role.name + ' (default)';
                        select.appendChild(opt);
                    }
                    data.available_roles.forEach(r => {
                        if (data.default_role && r.id === data.default_role.id) return;
                        const opt = document.createElement('option');
                        opt.value = r.id;
                        opt.textContent = r.name;
                        select.appendChild(opt);
                    });

                    hiddenInput.value = select.value;
                    select.onchange = () => { hiddenInput.value = select.value; };
                } else {
                    reassignWrap.classList.add('hidden');
                    hiddenInput.value = '';
                }

                document.getElementById('roleDeleteConfirmForm').action = destroyUrl;
                document.getElementById('roleDeleteConfirmModal').classList.remove('hidden');
            })
            .catch(() => {
                window.Toast?.show('Could not load role deletion details. Please try again.', 'error');
            });
    }

    function closeRoleDeleteConfirm() {
        document.getElementById('roleDeleteConfirmModal').classList.add('hidden');
    }

    // ajax-forms.js owns the actual submission; close the confirm modal as it starts.
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('roleDeleteConfirmForm')?.addEventListener('submit', function () {
            closeRoleDeleteConfirm();
        });
    });
</script>
