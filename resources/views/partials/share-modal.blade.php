<!-- ===== SHARE MODAL ===== -->
<!-- Shared by feed.blade.php and profile.blade.php's Posts panel, so
     there's exactly one copy of this to keep in sync. Expects a
     $friends collection to be passed in. -->
<div class="settings-modal hidden" id="shareModal">
    <div class="settings-modal-content" style="max-width: 360px; height: auto; max-height: 70vh;">
        <div class="settings-modal-header">
            <h2 id="shareModalTitle">Share Post</h2>
            <button class="settings-modal-close" onclick="closeShareModal()">✕</button>
        </div>

        <!-- Step 1: choose how to share -->
        <div id="shareStepChoose" style="padding: 1rem; display: flex; flex-direction: column; gap: 0.5rem;">
            <button type="button" class="settings-btn" onclick="showShareStep('repost')">Repost to your feed</button>
            <button type="button" class="settings-btn" onclick="showShareStep('friend')">Send to a friend</button>
        </div>

        <!-- Step 2a: repost with optional caption -->
        <div id="shareStepRepost" class="hidden" style="padding: 1rem;">
            <label style="font-size: 0.65rem; color: #6a6a6a; display: block; margin-bottom: 0.2rem;">Add a caption (optional)</label>
            <textarea id="repostCaption" class="settings-input" rows="2" maxlength="500" style="width: 100%; resize: none;"></textarea>
            <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 0.6rem;">
                <button type="button" class="settings-btn" onclick="showShareStep('choose')">Back</button>
                <button type="button" class="settings-btn" id="confirmRepostBtn" onclick="confirmRepost()">Repost</button>
            </div>
        </div>

        <!-- Step 2b: pick a friend -->
        <div id="shareStepFriend" class="hidden" style="padding: 1rem; overflow-y: auto; max-height: 50vh;">
            @forelse($friends as $friend)
                <button type="button" class="share-friend-option" data-friend-id="{{ $friend->id }}">{{ $friend->name }}</button>
            @empty
                <p style="font-size: 0.75rem; color: #6a6a6a;">You don't have any friends to share with yet.</p>
            @endforelse
            <button type="button" class="settings-btn" style="margin-top: 0.6rem;" onclick="showShareStep('choose')">Back</button>
        </div>
    </div>
</div>