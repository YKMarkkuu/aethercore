@extends('layouts.app')

@section('title', 'Settings')
@section('content')
<div class="settings-page">
    <h2 style="margin-bottom: 1.5rem;">⚙️ Settings</h2>
    
    <div class="card">
        <h3>Account</h3>
        <div class="settings-group">
        <label>Display Name</label>
        <input type="text" value="{{ Auth::user()->display_name }}" class="settings-input" disabled>
        <span class="settings-hint">Change your display name on your profile page</span>
    </div>
        <div class="settings-group">
            <label>Email</label>
            <input type="email" value="{{ Auth::user()->email }}" class="settings-input">
        </div>
    </div>

    <div class="card">
        <h3>Profile</h3>
        <div class="settings-group">
            <label>Bio</label>
            <textarea class="settings-input" rows="3">Welcome to AetherCore!</textarea>
        </div>
    </div>

    <div class="card">
        <h3>Appearance</h3>
        <div class="settings-group">
            <label>Theme</label>
            <select class="settings-input">
                <option>Dark</option>
                <option>Light</option>
                <option>System Default</option>
            </select>
        </div>
    </div>

    <div class="card">
        <h3>Danger Zone</h3>
        <button class="settings-btn settings-btn-danger">Delete Account</button>
    </div>
</div>
@endsection