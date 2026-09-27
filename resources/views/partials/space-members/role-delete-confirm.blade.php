<!-- ===== ROLE DELETE CONFIRM MODAL ===== -->
<!-- Single shared instance (not one per role) — populated dynamically
     from the roles-tab delete-preview endpoint. Included once from
     space-members-modal.blade.php so it survives #rolesTabPane's
     innerHTML refreshes rather than living inside the pane that gets
     replaced. Sits above membersModal (higher z-index) since it opens
     from within it. -->
<div class="settings-modal hidden" id="roleDeleteConfirmModal" style="z-index: 100000;">
    <div class="settings-modal-content" style="max-width: 420px; width: 92%; height: auto;">
        <div class="settings-modal-header">
            <h2 id="roleDeleteConfirmTitle">Delete Role</h2>
            <button class="settings-modal-close" onclick="closeRoleDeleteConfirm()">✕</button>
        </div>
        <div style="padding: 1rem;">
            <p id="roleDeleteConfirmWarning" style="font-size: 0.8rem; color: #1e1e1e; margin-bottom: 0.6rem;"></p>

            <div id="roleDeleteConfirmMembers" style="display: flex; flex-direction: column; gap: 0.3rem; margin-bottom: 0.75rem;"></div>

            <div id="roleDeleteConfirmReassignWrap" class="hidden">
                <label style="font-size: 0.65rem; color: #6a6a6a; display: block; margin-bottom: 0.2rem;">Move these members to</label>
                <select id="roleDeleteConfirmReassignSelect" class="settings-input"></select>
            </div>
        </div>
        <div style="padding: 0.6rem 1rem; border-top: 1px solid #b0a8a0; display: flex; justify-content: flex-end; gap: 0.5rem;">
            <button type="button" class="settings-btn" onclick="closeRoleDeleteConfirm()">Cancel</button>
            <form id="roleDeleteConfirmForm" method="POST" style="margin: 0;">
                @csrf
                @method('DELETE')
                <input type="hidden" name="reassign_to" id="roleDeleteConfirmReassignInput">
                <button type="submit" class="settings-btn settings-btn-danger">Delete Role</button>
            </form>
        </div>
    </div>
</div>