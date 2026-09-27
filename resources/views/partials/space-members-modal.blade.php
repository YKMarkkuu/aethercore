<!-- ===== SPACE MEMBERS MODAL ===== -->
<!-- Included once from spaces/show.blade.php. Two tabs: Members (existing)
    and Roles (new in this response). Role CRUD reuses this same modal
    shell — no separate modal. -->
<style>
    .members-tab-btn.active { background: var(--accent); color: #ffffff; border-color: var(--accent-dark); }
</style>
<div class="settings-modal hidden" id="membersModal">
    <div class="settings-modal-content" style="max-width: 560px; width: 92%; height: auto; max-height: 82vh; display: flex; flex-direction: column;">
        <div class="settings-modal-header">
            <h2 style="display: flex; align-items: center; gap: 6px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Members — {{ $space->name }}
            </h2>
            <button class="settings-modal-close" onclick="toggleMembersModal()">✕</button>
        </div>

        <div style="display: flex; gap: 0.4rem; padding: 0.5rem 1rem 0;">
            <button type="button" class="settings-btn members-tab-btn active" id="membersTabBtn" onclick="switchMembersTab('members')">Members</button>
            <button type="button" class="settings-btn members-tab-btn" id="rolesTabBtn" onclick="switchMembersTab('roles')">Roles</button>
        </div>

        <div style="padding: 0.75rem 1rem; overflow-y: auto;">
            <div id="membersTabPane">
                @include('partials.space-members.member-list', ['space' => $space])
            </div>
            <div id="rolesTabPane" class="hidden">
                @include('partials.space-members.roles-tab', ['space' => $space])
            </div>
        </div>
    </div>
</div>

<script>
    function toggleMembersModal() {
        document.getElementById('membersModal').classList.toggle('hidden');
    }

    function switchMembersTab(tab) {
        document.getElementById('membersTabPane').classList.toggle('hidden', tab !== 'members');
        document.getElementById('rolesTabPane').classList.toggle('hidden', tab !== 'roles');
        document.getElementById('membersTabBtn').classList.toggle('active', tab === 'members');
        document.getElementById('rolesTabBtn').classList.toggle('active', tab === 'roles');
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
</script>