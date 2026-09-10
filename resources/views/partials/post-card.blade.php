@php
    // Lightweight, SAFE markdown — never trusts raw HTML from the
    // textarea. Escapes the whole string FIRST, then only re-introduces
    // a small whitelist of tags via our own hardcoded regex
    // replacements, so the only HTML that can ever appear is HTML we
    // wrote ourselves, never anything a user typed directly.
    //
    // Defined here (not in feed.blade.php) so it's available regardless
    // of which page @includes this partial first.
    if (!function_exists('formatPostContent')) {
        function formatPostContent($content) {
            $escaped = e($content ?? '');

            $escaped = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escaped);
            $escaped = preg_replace('/\*(.+?)\*/s', '<em>$1</em>', $escaped);
            $escaped = preg_replace('/~~(.+?)~~/s', '<del>$1</del>', $escaped);
            $escaped = preg_replace('/`(.+?)`/s', '<code>$1</code>', $escaped);

            return nl2br($escaped);
        }
    }

    $isLiked = $post->isLikedBy(auth()->id());
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