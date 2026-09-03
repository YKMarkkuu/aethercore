@extends('layouts.app')

@section('title', 'Spaces')
@section('content')
<div class="card">
    <div class="card-header">💬 Your AetherSpaces</div>
    @forelse($spaces ?? [] as $space)
        <a href="#" class="space-item" style="display: flex; align-items: center; gap: 1rem; padding: 0.75rem; background: #0e0e1a; border-radius: 12px; margin-bottom: 0.5rem; text-decoration: none; color: inherit;">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: #6C63FF; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                {{ $space->icon ?? '✦' }}
            </div>
            <div>
                <div style="font-weight: 600;">{{ $space->name ?? 'AetherCore Hub' }}</div>
                <div style="font-size: 0.75rem; color: #555;">{{ $space->members_count ?? 0 }} members</div>
            </div>
        </a>
    @empty
        <p style="color: #555;">You haven't joined any spaces yet.</p>
    @endforelse
    <button style="margin-top: 1rem; background: #1e1e38; border: 1px dashed #6C63FF; color: #a0a0b8; padding: 0.75rem; border-radius: 12px; width: 100%; cursor: pointer; font-size: 0.9rem;">+ Create AetherSpace</button>
</div>
@endsection