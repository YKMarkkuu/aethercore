@extends('layouts.guest')

@section('title', 'Reset Password')
@section('content')

<div class="window">
    <div class="window-title-bar">
        <div class="window-title">
            <span class="window-icon">✦</span>
            <span>AetherCore — Reset Password</span>
        </div>
        <div class="window-controls">
            <button type="button" class="window-btn window-btn-min">─</button>
            <button type="button" class="window-btn window-btn-max">☐</button>
            <button type="button" class="window-btn window-btn-close" onclick="window.location.href='{{ route('welcome') }}'" title="Back to home">✕</button>
        </div>
    </div>

    <div class="window-content">
        <div class="window-left">
            <div class="brand-block">
                <div class="brand-logo">✦</div>
                <h1>Create New Password</h1>
                <p class="brand-tagline">Choose a strong password and get back in.</p>
            </div>

            <div class="feature-list">
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'lock', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">Secure Setup</span>
                        <span class="feature-desc">Protect your account before continuing</span>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'check', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">Quick Recovery</span>
                        <span class="feature-desc">Restore access in just a few steps</span>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'person', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">Your Profile</span>
                        <span class="feature-desc">Back to your digital home securely</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="window-right">
            <form method="POST" action="{{ route('password.store') }}" class="auth-form">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div class="auth-field">
                    <label for="email">Email Address</label>
                    <div class="field-wrapper">
                        <span class="field-icon">@include('partials.icon', ['type' => 'envelope', 'size' => 15])</span>
                        <input type="email" name="email" id="email" value="{{ old('email', $request->email) }}" placeholder="you@example.com" required autofocus>
                    </div>
                    @error('email')
                        <span class="auth-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password">New Password</label>
                    <div class="field-wrapper">
                        <span class="field-icon">@include('partials.icon', ['type' => 'lock', 'size' => 15])</span>
                        <input type="password" name="password" id="password" placeholder="••••••••" required>
                    </div>
                    @error('password')
                        <span class="auth-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="field-wrapper">
                        <span class="field-icon">@include('partials.icon', ['type' => 'check', 'size' => 15])</span>
                        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="••••••••" required>
                    </div>
                    @error('password_confirmation')
                        <span class="auth-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="auth-btn">
                    <span>▶</span> Reset Password
                </button>
            </form>
        </div>
    </div>

    <div class="window-footer">
        <div class="footer-left">
            <span class="footer-start">✦ Start</span>
            <span class="footer-divider">|</span>
            <span class="footer-status">AetherCore v1.0</span>
        </div>
        <div class="footer-right">
            <span class="footer-time">{{ now()->format('H:i') }}</span>
        </div>
    </div>
</div>

@endsection
