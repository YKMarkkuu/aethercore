@extends('layouts.app')

@section('title', 'Feed')
@section('content')

@php
    // Lightweight, SAFE markdown — never trusts raw HTML from the
    // textarea. Escapes the whole string FIRST, then only re-introduces
    // a small whitelist of tags via our own hardcoded regex
    // replacements, so the only HTML that can ever appear is HTML we
    // wrote ourselves, never anything a user typed directly.
    if (!function_exists('formatPostContent')) {
        function formatPostContent($content) {
            $escaped = e($content ?? '');

            // Order matters: bold (**) before italic (*), so remaining
            // single asterisks after bold is consumed are unambiguous.
            $escaped = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escaped);
            $escaped = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $escaped);
            $escaped = preg_replace('/~~(.+?)~~/s', '<del>$1</del>', $escaped);
            $escaped = preg_replace('/`(.+?)`/s', '<code>$1</code>', $escaped);

            return nl2br($escaped);
        }
    }
@endphp

<!-- ===== CREATE POST ===== -->
<div class="card">
    <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
        <!-- Your Avatar (logged-in user) -->
        <a href="{{ route('profile.show', Auth::user()) }}" class="post-avatar">
            @if(Auth::user()->getAvatarUrl())
                <img src="{{ Auth::user()->getAvatarUrl() }}" alt="Avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
            @else
                {{ Auth::user()->name[0] ?? '?' }}
            @endif
        </a>
        <form action="{{ route('posts.store') }}" method="POST" style="flex: 1;" class="post-composer">
            @csrf
            <div class="post-format-toolbar">
                <button type="button" data-wrap="**" title="Bold"><strong>B</strong></button>
                <button type="button" data-wrap="*" title="Italic"><em>I</em></button>
                <button type="button" data-wrap="~~" title="Strikethrough"><del>S</del></button>
                <button type="button" data-wrap="`" title="Code">&lt;/&gt;</button>
            </div>
            <textarea name="content" class="settings-input" rows="2" maxlength="500" placeholder="What's on your mind, {{ Auth::user()->display_name }}?" style="resize: none;"></textarea>
            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.3rem;">
                <span class="settings-hint" style="margin: 0;">**bold** *italic* ~~strike~~ `code`</span>
                <button type="submit" class="settings-btn">Post</button>
            </div>
        </form>
    </div>
</div>

<!-- ===== ACTIVITY FEED ===== -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
        <span style="font-weight: 600; font-size: 0.85rem; color: #1e1e1e;">Activity Feed</span>
        <span style="font-size: 0.6rem; color: #6a6a6a;">Latest posts</span>
    </div>

    @forelse($feedPosts as $post)
        @php
            $isLiked = $post->isLikedBy(Auth::id());
            $likeCount = $post->likes->count();
            $comments = $post->comments->sortBy('created_at')->values();
        @endphp
        <div class="post-item" data-post-id="{{ $post->id }}">
            <div class="post-header">
                <!-- Post Author Avatar -->
                <a href="{{ route('profile.show', $post->user) }}" class="post-avatar">
                    @if($post->user->getAvatarUrl())
                        <img src="{{ $post->user->getAvatarUrl() }}" alt="Avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                    @else
                        {{ $post->user->name[0] }}
                    @endif
                </a>
                <a href="{{ route('profile.show', $post->user) }}" class="post-user" style="text-decoration: none;">{{ $post->user->display_name }}</a>
                <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
            </div>

            @if($post->content)
                <div class="post-content">{!! formatPostContent($post->content) !!}</div>
            @endif

            <!-- ===== REPOST: nested original post ===== -->
            @if($post->shared_post_id)
                <div class="post-repost-card">
                    @if($post->sharedPost)
                        <div class="post-header" style="margin-bottom: 0.3rem;">
                            <a href="{{ route('profile.show', $post->sharedPost->user) }}" class="post-avatar" style="width: 24px; height: 24px;">
                                @if($post->sharedPost->user->getAvatarUrl())
                                    <img src="{{ $post->sharedPost->user->getAvatarUrl() }}" alt="Avatar" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                                @else
                                    {{ $post->sharedPost->user->name[0] }}
                                @endif
                            </a>
                            <a href="{{ route('profile.show', $post->sharedPost->user) }}" class="post-user" style="font-size: 0.75rem; text-decoration: none;">{{ $post->sharedPost->user->display_name }}</a>
                            <span class="post-time" style="font-size: 0.55rem;">{{ $post->sharedPost->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="post-content" style="font-size: 0.75rem;">{!! formatPostContent($post->sharedPost->content) !!}</div>
                    @else
                        <p style="font-size: 0.7rem; color: #6a6a6a; font-style: italic; margin: 0;">This post is no longer available.</p>
                    @endif
                </div>
            @endif

            <div class="post-actions">
                <button type="button" class="post-action-btn post-like-btn {{ $isLiked ? 'post-like-btn-active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="{{ $isLiked ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                    </svg>
                    <span class="post-like-count">{{ $likeCount }}</span>
                </button>
                <button type="button" class="post-action-btn post-comment-toggle-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
                    </svg>
                    <span class="post-comment-count">{{ $comments->count() }}</span>
                </button>
                <button type="button" class="post-action-btn post-share-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/>
                        <line x1="8.6" y1="10.6" x2="15.4" y2="6.4"/><line x1="8.6" y1="13.4" x2="15.4" y2="17.6"/>
                    </svg>
                </button>
                @if($post->user_id === Auth::id())
                    <form action="{{ route('posts.destroy', $post) }}" method="POST" style="display: inline; margin-left: auto;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                @endif
            </div>

            <!-- ===== COMMENTS (hidden until toggled) ===== -->
            <div class="post-comments hidden">
                <div class="post-comments-list">
                    @foreach($comments as $comment)
                        <div class="post-comment" data-comment-id="{{ $comment->id }}">
                            <a href="{{ route('profile.show', $comment->user) }}" class="post-comment-avatar">
                                @if($comment->user->getAvatarUrl())
                                    <img src="{{ $comment->user->getAvatarUrl() }}" alt="Avatar" style="width: 22px; height: 22px; border-radius: 50%; object-fit: cover;">
                                @else
                                    {{ $comment->user->name[0] }}
                                @endif
                            </a>
                            <div class="post-comment-body">
                                <a href="{{ route('profile.show', $comment->user) }}" class="post-comment-user">{{ $comment->user->display_name }}</a>
                                <span class="post-comment-content">{{ $comment->content }}</span>
                                <div class="post-comment-meta">
                                    <span>{{ $comment->created_at->diffForHumans() }}</span>
                                    @if($comment->user_id === Auth::id())
                                        <button type="button" class="post-comment-delete">Delete</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <form class="post-comment-form" style="display: flex; gap: 0.4rem; margin-top: 0.4rem;">
                    <input type="text" class="post-comment-input settings-input" placeholder="Write a comment..." maxlength="500" style="flex: 1; font-size: 0.75rem;">
                    <button type="submit" class="settings-btn" style="font-size: 0.7rem; padding: 0.2rem 0.8rem;">Send</button>
                </form>
            </div>
        </div>
    @empty
        <p style="color: #6a6a6a; font-size: 0.8rem; text-align: center; padding: 1rem 0;">No posts yet. Share something!</p>
    @endforelse
</div>

<!-- ===== SHARE MODAL ===== -->
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

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]').value;

        function apiHeaders(extra = {}) {
            return Object.assign({
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            }, extra);
        }

        // ===== FORMATTING TOOLBAR =====
        document.querySelectorAll('.post-composer .post-format-toolbar button').forEach(btn => {
            btn.addEventListener('click', () => {
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
        });

        // ===== LIKE =====
        document.body.addEventListener('click', function(e) {
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
        document.body.addEventListener('click', function(e) {
            const toggleBtn = e.target.closest('.post-comment-toggle-btn');
            if (!toggleBtn) return;
            const postItem = toggleBtn.closest('.post-item');
            postItem.querySelector('.post-comments').classList.toggle('hidden');
        });

        // ===== SUBMIT COMMENT =====
        document.body.addEventListener('submit', function(e) {
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
                    const c = data.comment;
                    const list = postItem.querySelector('.post-comments-list');
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
                    list.appendChild(row);

                    postItem.querySelector('.post-comment-count').textContent =
                        postItem.querySelectorAll('.post-comment').length;

                    input.value = '';
                })
                .catch(() => alert('Could not post that comment. Please try again.'));
        });

        // ===== DELETE COMMENT =====
        document.body.addEventListener('click', function(e) {
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

        document.body.addEventListener('click', function(e) {
            const shareBtn = e.target.closest('.post-share-btn');
            if (!shareBtn) return;
            currentSharePostId = shareBtn.closest('.post-item').dataset.postId;
            showShareStep('choose');
            document.getElementById('shareModal').classList.remove('hidden');
        });

        window.closeShareModal = function() {
            document.getElementById('shareModal').classList.add('hidden');
            currentSharePostId = null;
        };

        window.showShareStep = function(step) {
            document.getElementById('shareStepChoose').classList.toggle('hidden', step !== 'choose');
            document.getElementById('shareStepRepost').classList.toggle('hidden', step !== 'repost');
            document.getElementById('shareStepFriend').classList.toggle('hidden', step !== 'friend');
        };

        window.confirmRepost = function() {
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
                    // Reloads to show the new repost at the top of the
                    // feed — simplest way to render its nested original-
                    // post card correctly without duplicating the whole
                    // Blade template in JS.
                    window.location.reload();
                })
                .catch(() => {
                    alert('Could not repost. Please try again.');
                    btn.disabled = false;
                });
        };

        document.body.addEventListener('click', function(e) {
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
                    setTimeout(closeShareModal, 700);
                })
                .catch(() => {
                    alert('Could not share with that friend. Please try again.');
                    friendBtn.disabled = false;
                    friendBtn.textContent = friendBtn.textContent.replace('Sending...', '');
                });
        });
    });
</script>
@endpush

@endsection