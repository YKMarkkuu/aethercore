// Loaded on every page (see app.blade.php). Everything here is
// event-delegated on document.body and only acts when the relevant
// elements are actually present, so this is inert on pages with no
// posts (chat, settings, etc.) — no per-page wiring needed.

document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    function apiHeaders(extra = {}) {
        return Object.assign({
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        }, extra);
    }

    /**
     * Builds one comment row DOM element from a comment object (used by
     * both the "I just submitted a comment" handler and the live-poll
     * refresh below) — one template, so they can never drift out of sync
     * with each other the way feed.blade.php and profile.blade.php once did.
     */
    function buildCommentRow(c) {
        const row = document.createElement('div');
        row.className = 'post-comment';
        row.dataset.commentId = c.id;
        row.innerHTML = `
            <a href="${c.user.profile_url}" class="post-comment-avatar">${c.user.avatar_url ? `<img src="${c.user.avatar_url}" alt="Avatar" style="width:22px;height:22px;border-radius:50%;object-fit:cover;">` : ''}</a>
            <div class="post-comment-body">
                <a href="${c.user.profile_url}" class="post-comment-user"></a>
                <span class="post-comment-content"></span>
                <div class="post-comment-meta">
                    <span>${c.time}</span>
                    <button type="button" class="post-comment-delete">Delete</button>
                </div>
            </div>
        `;
        row.querySelector('.post-comment-avatar').textContent = c.user.avatar_url ? '' : (c.user.display_name || '?')[0];
        row.querySelector('.post-comment-user').textContent = c.user.display_name;
        row.querySelector('.post-comment-content').textContent = c.content;
        return row;
    }

    // ===== FORMATTING TOOLBAR =====
    document.body.addEventListener('click', function (e) {
        const btn = e.target.closest('.post-composer .post-format-toolbar button');
        if (!btn) return;

        const wrap = btn.dataset.wrap;
        const textarea = btn.closest('.post-composer').querySelector('textarea');
        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const selected = textarea.value.slice(start, end);
        const before = textarea.value.slice(0, start);
        const after = textarea.value.slice(end);
        textarea.value = before + wrap + selected + wrap + after;
        textarea.focus();
        const cursor = selected ? end + wrap.length * 2 : start + wrap.length;
        textarea.setSelectionRange(cursor, cursor);
    });

    // ===== LIKE =====
    document.body.addEventListener('click', function (e) {
        const likeBtn = e.target.closest('.post-like-btn');
        if (!likeBtn) return;

        const postItem = likeBtn.closest('.post-item');
        const postId = postItem.dataset.postId;

        fetch(`/posts/${postId}/like`, {
            method: 'POST',
            headers: apiHeaders(),
        })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(data => {
                likeBtn.classList.toggle('post-like-btn-active', data.liked);
                likeBtn.querySelector('svg').setAttribute('fill', data.liked ? 'currentColor' : 'none');
                likeBtn.querySelector('.post-like-count').textContent = data.count;
            })
            .catch(() => alert('Could not like that post. Please try again.'));
    });

    // ===== COMMENT TOGGLE =====
    document.body.addEventListener('click', function (e) {
        const toggleBtn = e.target.closest('.post-comment-toggle-btn');
        if (!toggleBtn) return;
        const postItem = toggleBtn.closest('.post-item');
        postItem.querySelector('.post-comments').classList.toggle('hidden');
    });

    // ===== SUBMIT COMMENT =====
    document.body.addEventListener('submit', function (e) {
        const form = e.target.closest('.post-comment-form');
        if (!form) return;
        e.preventDefault();

        const postItem = form.closest('.post-item');
        const postId = postItem.dataset.postId;
        const input = form.querySelector('.post-comment-input');
        const content = input.value.trim();
        if (!content) return;

        fetch(`/posts/${postId}/comments`, {
            method: 'POST',
            headers: apiHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ content }),
        })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(data => {
                const list = postItem.querySelector('.post-comments-list');
                list.appendChild(buildCommentRow(data.comment));

                postItem.querySelector('.post-comment-count').textContent =
                    postItem.querySelectorAll('.post-comment').length;

                input.value = '';
            })
            .catch(() => alert('Could not post that comment. Please try again.'));
    });

    // ===== DELETE COMMENT =====
    document.body.addEventListener('click', function (e) {
        const deleteBtn = e.target.closest('.post-comment-delete');
        if (!deleteBtn) return;
        if (!confirm('Delete this comment?')) return;

        const commentRow = deleteBtn.closest('.post-comment');
        const postItem = deleteBtn.closest('.post-item');
        const commentId = commentRow.dataset.commentId;

        fetch(`/comments/${commentId}`, {
            method: 'DELETE',
            headers: apiHeaders(),
        })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(() => {
                commentRow.remove();
                postItem.querySelector('.post-comment-count').textContent =
                    postItem.querySelectorAll('.post-comment').length;
            })
            .catch(() => alert('Could not delete that comment. Please try again.'));
    });

    // ===== SHARE MODAL =====
    let currentSharePostId = null;
    const shareModal = document.getElementById('shareModal');

    document.body.addEventListener('click', function (e) {
        const shareBtn = e.target.closest('.post-share-btn');
        if (!shareBtn || !shareModal) return;
        currentSharePostId = shareBtn.closest('.post-item').dataset.postId;
        window.showShareStep('choose');
        shareModal.classList.remove('hidden');
    });

    window.closeShareModal = function () {
        if (shareModal) shareModal.classList.add('hidden');
        currentSharePostId = null;
    };

    window.showShareStep = function (step) {
        document.getElementById('shareStepChoose')?.classList.toggle('hidden', step !== 'choose');
        document.getElementById('shareStepRepost')?.classList.toggle('hidden', step !== 'repost');
        document.getElementById('shareStepFriend')?.classList.toggle('hidden', step !== 'friend');
    };

    window.confirmRepost = function () {
        const caption = document.getElementById('repostCaption').value.trim();
        const btn = document.getElementById('confirmRepostBtn');
        btn.disabled = true;

        fetch(`/posts/${currentSharePostId}/repost`, {
            method: 'POST',
            headers: apiHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ content: caption }),
        })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(() => {
                // Reloads to show the new repost at the top of the feed
                // — simplest way to render its nested original-post card
                // correctly without duplicating this whole partial in JS.
                window.location.reload();
            })
            .catch(() => {
                alert('Could not repost. Please try again.');
                btn.disabled = false;
            });
    };

    document.body.addEventListener('click', function (e) {
        const friendBtn = e.target.closest('.share-friend-option');
        if (!friendBtn) return;

        friendBtn.disabled = true;
        friendBtn.textContent = 'Sending...';

        fetch(`/posts/${currentSharePostId}/share-to-chat`, {
            method: 'POST',
            headers: apiHeaders({ 'Content-Type': 'application/json' }),
            body: JSON.stringify({ friend_id: friendBtn.dataset.friendId }),
        })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(() => {
                friendBtn.textContent = 'Sent!';
                setTimeout(window.closeShareModal, 700);
            })
            .catch(() => {
                alert('Could not share with that friend. Please try again.');
                friendBtn.disabled = false;
                friendBtn.textContent = friendBtn.textContent.replace('Sending...', '');
            });
    });

    // ===== LIVE POLLING: likes & comments from other people =====
    // Every few seconds, refetch current like/comment state for exactly
    // the posts on screen. Simpler than the chat's "since timestamp"
    // approach since this only ever covers a small, fixed, already-
    // rendered set of post ids rather than an open-ended growing list.
    const postItemsOnPage = document.querySelectorAll('.post-item');
    if (postItemsOnPage.length > 0) {
        const postIds = Array.from(postItemsOnPage).map(el => el.dataset.postId);

        function pollPostUpdates() {
            fetch('/posts/updates', {
                method: 'POST',
                headers: apiHeaders({ 'Content-Type': 'application/json' }),
                body: JSON.stringify({ post_ids: postIds }),
            })
                .then(r => r.ok ? r.json() : Promise.reject())
                .then(data => {
                    (data.posts || []).forEach(postData => {
                        const postItem = document.querySelector(`.post-item[data-post-id="${postData.id}"]`);
                        if (!postItem) return;

                        // Like state
                        const likeBtn = postItem.querySelector('.post-like-btn');
                        likeBtn.classList.toggle('post-like-btn-active', postData.liked_by_me);
                        likeBtn.querySelector('svg').setAttribute('fill', postData.liked_by_me ? 'currentColor' : 'none');
                        likeBtn.querySelector('.post-like-count').textContent = postData.like_count;

                        // Comments — only touch the DOM if the actual set
                        // of comment ids changed, so we're not re-rendering
                        // (and losing scroll position within) an unchanged
                        // list every single poll cycle.
                        const list = postItem.querySelector('.post-comments-list');
                        const currentIds = Array.from(list.querySelectorAll('.post-comment'))
                            .map(el => el.dataset.commentId)
                            .join(',');
                        const incomingIds = postData.comments.map(c => String(c.id)).join(',');

                        if (currentIds !== incomingIds) {
                            list.innerHTML = '';
                            postData.comments.forEach(c => list.appendChild(buildCommentRow(c)));
                        }

                        postItem.querySelector('.post-comment-count').textContent = postData.comments.length;
                    });
                })
                .catch(() => {
                    // Silent — just try again next interval.
                });
        }

        setInterval(pollPostUpdates, 5000);
    }
});