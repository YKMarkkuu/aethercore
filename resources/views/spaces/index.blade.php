@extends('layouts.app')

@section('title', 'Spaces')
@section('content')

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
        <span class="card-header" style="margin: 0;">Your AetherSpaces</span>
        <button type="button" class="settings-btn" onclick="document.getElementById('createSpaceModal').classList.remove('hidden')">+ Create Space</button>
    </div>

    @forelse($spaces as $space)
        <a href="{{ route('spaces.show', $space) }}" class="space-item">
            <div class="space-icon">
                @if($space->getIconUrl())
                    <img src="{{ $space->getIconUrl() }}" alt="{{ $space->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
                @else
                    {{ strtoupper($space->name[0] ?? '?') }}
                @endif
            </div>
            <div>
                <div class="space-name">{{ $space->name }}</div>
                <div class="space-members">{{ $space->members_count }} {{ \Illuminate\Support\Str::plural('member', $space->members_count) }}</div>
            </div>
        </a>
    @empty
        <p style="color: #6a6a6a; font-size: 0.8rem; text-align: center; padding: 1rem 0;">
            You haven't joined any Spaces yet. Create one, or ask a friend to share an invite in the Feed.
        </p>
    @endforelse
</div>

@endsection