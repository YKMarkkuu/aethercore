<!-- ===== CREATE SPACE MODAL ===== -->
<!-- Included once via partials.sidebar-left (so it's available from
     anywhere, same pattern as the Share modal). Don't also embed this
     in spaces/index.blade.php — duplicate ids would break the JS. -->
<div class="settings-modal hidden" id="createSpaceModal">
    <div class="settings-modal-content" style="max-width: 360px; height: auto;">
        <div class="settings-modal-header">
            <h2>Create a Space</h2>
            <button class="settings-modal-close" onclick="document.getElementById('createSpaceModal').classList.add('hidden')">✕</button>
        </div>
        <form action="{{ route('spaces.store') }}" method="POST" enctype="multipart/form-data" style="padding: 1rem;">
            @csrf
            <div style="margin-bottom: 0.6rem;">
                <label style="font-size: 0.65rem; color: #6a6a6a; display: block; margin-bottom: 0.2rem;">Name</label>
                <input type="text" name="name" required maxlength="100" class="settings-input" style="width: 100%;">
            </div>
            <div style="margin-bottom: 0.6rem;">
                <label style="font-size: 0.65rem; color: #6a6a6a; display: block; margin-bottom: 0.2rem;">Description (optional)</label>
                <textarea name="description" maxlength="300" rows="2" class="settings-input" style="width: 100%; resize: none;"></textarea>
            </div>
            <div style="margin-bottom: 0.6rem;">
                <label style="font-size: 0.65rem; color: #6a6a6a; display: block; margin-bottom: 0.2rem;">Icon (optional)</label>
                <input type="file" name="icon" accept="image/*" class="settings-input" style="width: 100%;">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.6rem;">
                <button type="button" class="settings-btn" onclick="document.getElementById('createSpaceModal').classList.add('hidden')">Cancel</button>
                <button type="submit" class="settings-btn">Create</button>
            </div>
        </form>
    </div>
</div>