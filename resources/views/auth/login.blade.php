@extends('layouts.guest')

@section('title', 'Sign In')
@section('content')

<div class="window">
    <div class="window-title-bar">
        <div class="window-title">
            <span class="window-icon">✦</span>
            <span>AetherCore — Sign In</span>
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
                <h1>AetherCore</h1>
                <p class="brand-tagline">The core of your digital world</p>
            </div>

            <div class="feature-list">
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'home', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">AetherSpaces</span>
                        <span class="feature-desc">Create and join communities</span>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'music', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">AetherTunes</span>
                        <span class="feature-desc">Discover music with friends</span>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'chat', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">AetherChat</span>
                        <span class="feature-desc">Real-time messaging</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="window-right">
            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

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

                <div class="auth-field">
                    <label for="password">Password</label>
                    <div class="field-wrapper">
                        <span class="field-icon">@include('partials.icon', ['type' => 'lock', 'size' => 15])</span>
                        <input type="password" name="password" id="password" placeholder="••••••••" required>
                    </div>
                    @error('password')
                        <span class="auth-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="{{ route('password.request') }}" class="auth-link">Forgot password?</a>
                </div>

                <button type="submit" class="auth-btn">
                    <span>▶</span> Sign In
                </button>
            </form>

            <div class="auth-divider">
                <span>New to AetherCore?</span>
            </div>

            <a href="{{ route('register') }}" class="auth-switch-btn">
                Create Account →
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