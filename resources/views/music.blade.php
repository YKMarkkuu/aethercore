@extends('layouts.app')

@section('title', 'Music')
@section('content')
<div class="card">
    <div class="card-header">🎵 Your Library</div>
    <p style="color: #8888aa;">Your saved artists, albums, and playlists will appear here.</p>
    <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 1rem;">
        <div style="background: #1a1a2e; padding: 1.5rem; border-radius: 12px; flex: 1; min-width: 150px; text-align: center;">
            <div style="font-size: 2rem;">🎤</div>
            <div style="font-weight: 600; margin-top: 0.5rem;">0</div>
            <div style="color: #8888aa; font-size: 0.8rem;">Artists</div>
        </div>
        <div style="background: #1a1a2e; padding: 1.5rem; border-radius: 12px; flex: 1; min-width: 150px; text-align: center;">
            <div style="font-size: 2rem;">💿</div>
            <div style="font-weight: 600; margin-top: 0.5rem;">0</div>
            <div style="color: #8888aa; font-size: 0.8rem;">Albums</div>
        </div>
        <div style="background: #1a1a2e; padding: 1.5rem; border-radius: 12px; flex: 1; min-width: 150px; text-align: center;">
            <div style="font-size: 2rem;">📋</div>
            <div style="font-weight: 600; margin-top: 0.5rem;">0</div>
            <div style="color: #8888aa; font-size: 0.8rem;">Playlists</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">🔥 Top Tracks</div>
    <p style="color: #555;">Connect your music service to see your top tracks!</p>
</div>
@endsection