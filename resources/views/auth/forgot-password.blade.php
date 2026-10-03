@extends('layouts.guest')

@section('title', 'Forgot Password')
@section('content')

<div class="window">
    <div class="window-title-bar">
        <div class="window-title">
            <span class="window-icon">✦</span>
            <span>AetherCore — Forgot Password</span>
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
                <h1>Reset Access</h1>
                <p class="brand-tagline">We'll email you a secure reset link.</p>
            </div>

            <div class="feature-list">
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'lock', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">Secure Recovery</span>
                        <span class="feature-desc">Protect your account with a fresh password</span>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'sparkle', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">Fast Reset</span>
                        <span class="feature-desc">Quick steps back into your space</span>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'person', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">Safe Access</span>
                        <span class="feature-desc">Keep your profile private and protected</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="window-right">
            <form method="POST" action="{{ route('password.email') }}" class="auth-form">
                @csrf

                @if (session('status'))
                    <div class="auth-success auth-error">{{ session('status') }}</div>
                @endif

                <div class="auth-field">
                    <label for="email">Email Address</label>
                    <div class="field-wrapper">
                        <span class="field-icon">@include('partials.icon', ['type' => 'envelope', 'size' => 15])</span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                    </div>
                    @error('email')
                        <span class="auth-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="auth-btn">
                    <span>▶</span> Send Reset Link
                </button>
            </form>

            <a href="{{ route('login') }}" class="auth-switch-btn">
                Back to Sign In →
            </a>
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
