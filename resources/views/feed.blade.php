@extends('layouts.app')

@section('title', 'Feed')
@section('content')

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
        @include('partials.post-card', ['post' => $post])
    @empty
        <p style="color: #6a6a6a; font-size: 0.8rem; text-align: center; padding: 1rem 0;">No posts yet. Share something!</p>
    @endforelse
</div>

@include('partials.share-modal', ['friends' => $friends])

@endsection