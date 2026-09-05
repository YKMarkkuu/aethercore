@extends('layouts.app')

@section('title', 'Settings')
@section('content')

@php
    $user = $user ?? Auth::user();
@endphp

@if(session('success'))
    <div style="background: #d4e8d4; border: 2px solid #8ab88a; border-radius: 4px; padding: 0.5rem 1rem; margin-bottom: 1rem; color: #1e4a1e;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background: #f0d8d8; border: 2px solid #c8a0a0; border-radius: 4px; padding: 0.5rem 1rem; margin-bottom: 1rem; color: #6a2a2a;">
        {{ session('error') }}
    </div>
@endif

<!-- ===== ACCOUNT SETTINGS ===== -->
<div class="xp-panel" style="margin-bottom: 0.75rem;">
    <div class="xp-panel-header">Account</div>
    <div class="xp-panel-body">
        <form action="{{ route('settings.account') }}" method="POST">
            @csrf
            <div class="settings-group">
                <label class="settings-label">Username</label>
                <input type="text" name="username" value="{{ old('username', $user->username) }}" class="settings-input">
                <span class="settings-hint">Letters, numbers, and underscores only. No spaces.</span>
                @error('username')
                    <div class="settings-error">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="settings-group">
                <label class="settings-label">Display Name</label>
                <input type="text" name="display_name" value="{{ old('display_name', $user->display_name) }}" class="settings-input">
                <span class="settings-hint">This is what others see on your profile.</span>
                @error('display_name')
                    <div class="settings-error">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="settings-group">
                <label class="settings-label">Email</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="settings-input">
                @if(!$user->hasVerifiedEmail())
                    <span class="settings-hint" style="color: #c9a840;">⚠️ Email not verified.</span>
                @endif
                @error('email')
                    <div class="settings-error">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="settings-group">
                <label class="settings-label">Current Password</label>
                <input type="password" name="current_password" placeholder="Enter current password" class="settings-input">
                <span class="settings-hint">Required to change your password.</span>
                @error('current_password')
                    <div class="settings-error">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="settings-group">
                <label class="settings-label">New Password</label>
                <input type="password" name="new_password" placeholder="Enter new password" class="settings-input">
                <span class="settings-hint">Minimum 8 characters.</span>
                @error('new_password')
                    <div class="settings-error">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="settings-group">
                <label class="settings-label">Confirm New Password</label>
                <input type="password" name="new_password_confirmation" placeholder="Confirm new password" class="settings-input">
            </div>
            
            <button type="submit" class="settings-btn">Save Account</button>
        </form>
    </div>
</div>

<!-- ===== APPEARANCE ===== -->
<div class="xp-panel" style="margin-bottom: 0.75rem;">
    <div class="xp-panel-header">Appearance</div>
    <div class="xp-panel-body">
        <form action="{{ route('settings.theme') }}" method="POST">
            @csrf
            <div class="settings-group">
                <label class="settings-label">Theme</label>
                <select name="theme" class="settings-input">
                    @foreach($user->getAvailableThemes() as $key => $label)
                        <option value="{{ $key }}" {{ $user->theme == $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <span class="settings-hint">Choose your preferred look. Changes will apply on refresh.</span>
            </div>
            <button type="submit" class="settings-btn">Save Theme</button>
        </form>
    </div>
</div>

<!-- ===== LAST.FM CONNECTION ===== -->
<div class="xp-panel" style="margin-bottom: 0.75rem;">
    <div class="xp-panel-header">Music Integration</div>
    <div class="xp-panel-body">
        <form action="{{ route('settings.lastfm') }}" method="POST">
            @csrf
            <div class="settings-group">
                <label class="settings-label">Last.fm Username</label>
                <input type="text" name="lastfm_username" placeholder="Enter your Last.fm username" class="settings-input" value="{{ old('lastfm_username', $user->lastfm_username ?? '') }}">
                <span class="settings-hint">Connect your Last.fm account to show your top artists, songs, and albums on your profile.</span>
                <span class="settings-hint" style="color: #6a6a6a;">📌 You'll need a free Last.fm account at <a href="https://www.last.fm" target="_blank" style="color: #1a4a9e;">last.fm</a>.</span>
            </div>
            <button type="submit" class="settings-btn">Connect Last.fm</button>
        </form>
    </div>
</div>

<!-- ===== DANGER ZONE ===== -->
<div class="xp-panel" style="border-color: #c8a0a0; margin-bottom: 0.75rem;">
    <div class="xp-panel-header" style="background: linear-gradient(180deg, #f0d8d8 0%, #e8c8c8 100%); color: #6a2a2a;">Danger Zone</div>
    <div class="xp-panel-body">
        <form action="{{ route('settings.delete') }}" method="POST" onsubmit="return confirm('⚠️ Are you sure you want to delete your account? This action CANNOT be undone! All your data, posts, friends, and messages will be permanently deleted.')">
            @csrf
            @method('DELETE')
            <div class="settings-group">
                <label class="settings-label">Delete Account</label>
                <p style="font-size: 0.75rem; color: #6a6a6a; margin-bottom: 0.5rem;">Once you delete your account, there is no going back. Please be certain.</p>
                <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                    <input type="password" name="password" placeholder="Enter your password to confirm" class="settings-input" style="flex: 1; min-width: 200px;">
                    <button type="submit" class="settings-btn settings-btn-danger">Delete Account</button>
                </div>
                @error('password')
                    <div class="settings-error">{{ $message }}</div>
                @enderror
            </div>
        </form>
    </div>
</div>

<style>
.settings-group {
    margin-bottom: 1rem;
}

.settings-label {
    display: block;
    font-size: 0.7rem;
    font-weight: 600;
    color: #1e1e1e;
    margin-bottom: 0.2rem;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.settings-hint {
    display: block;
    font-size: 0.6rem;
    color: #6a6a6a;
    margin-top: 0.2rem;
}

.settings-error {
    color: #ef4444;
    font-size: 0.7rem;
    margin-top: 0.2rem;
}

.settings-btn {
    padding: 0.3rem 1.2rem;
    border-radius: 4px;
    background: linear-gradient(180deg, #f0edd8 0%, #d4d0c8 100%);
    color: #1e1e1e;
    border: 2px solid #b0a8a0;
    cursor: pointer;
    font-size: 0.8rem;
    font-weight: 600;
    transition: 0.2s;
    font-family: inherit;
}

.settings-btn:hover {
    background: #e0dcd0;
    border-color: #a09890;
}

.settings-btn-danger {
    background: #f0d8d8;
    border-color: #c8a0a0;
    color: #6a2a2a;
}

.settings-btn-danger:hover {
    background: #e8c8c8;
}
</style>

@endsection