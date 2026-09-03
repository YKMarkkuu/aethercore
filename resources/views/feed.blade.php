@extends('layouts.app')

@section('title', 'Feed')
@section('content')

<!-- ===== CREATE POST ===== -->
<div class="card">
    <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
        <div class="post-avatar">{{ Auth::user()->name[0] ?? '?' }}</div>
        <form action="{{ route('posts.store') }}" method="POST" style="flex: 1;">
            @csrf
            <textarea name="content" class="settings-input" rows="2" placeholder="What's on your mind, {{ Auth::user()->name }}?" style="resize: none;"></textarea>
            <div style="display: flex; justify-content: flex-end; margin-top: 0.3rem;">
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
        <div class="post-item">
            <div class="post-header">
                <div class="post-avatar">{{ $post->user->name[0] }}</div>
                <span class="post-user">{{ $post->user->name }}</span>
                <span class="post-time">{{ $post->created_at->diffForHumans() }}</span>
            </div>
            <div class="post-content">{{ $post->content }}</div>
            <div class="post-actions">
                <span>Like</span>
                <span>Comment</span>
                <span>Share</span>
                @if($post->user_id === Auth::id())
                    <form action="{{ route('posts.destroy', $post) }}" method="POST" style="display: inline; margin-left: auto;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="delete-btn">Delete</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <p style="color: #6a6a6a; font-size: 0.8rem; text-align: center; padding: 1rem 0;">No posts yet. Share something!</p>
    @endforelse
</div>

@endsection