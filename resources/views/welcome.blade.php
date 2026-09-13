@extends('layouts.guest')

@section('title', 'Welcome')
@section('content')

<div class="window">
    <div class="window-title-bar">
        <div class="window-title">
            <span class="window-icon">✦</span>
            <span>AetherCore — Welcome</span>
        </div>
        <div class="window-controls">
            <button type="button" class="window-btn window-btn-min">─</button>
            <button type="button" class="window-btn window-btn-max">☐</button>
            @auth
                <button type="button" class="window-btn window-btn-close" onclick="window.location.href='{{ route('feed') }}'" title="Go to app">✕</button>
            @else
                <button type="button" class="window-btn window-btn-close" onclick="window.location.href='{{ route('login') }}'" title="Sign in">✕</button>
            @endauth
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
                    <span class="feature-icon">🏠</span>
                    <div>
                        <span class="feature-title">AetherSpaces</span>
                        <span class="feature-desc">Create and join your own multi-channel communities</span>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">🎵</span>
                    <div>
                        <span class="feature-title">AetherTunes</span>
                        <span class="feature-desc">Your Top 8 Artists, Albums & Songs, live from Last.fm</span>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">💬</span>
                    <div>
                        <span class="feature-title">AetherChat</span>
                        <span class="feature-desc">Real-time DMs with reactions, edits, and shared posts</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="window-right">
            <p style="font-size: 0.85rem; color: var(--text-primary); line-height: 1.6; margin-bottom: 1rem;">
                A profile, a feed, real-time chat, and your own communities —
                with a little bit of that early-2000s internet charm. No
                algorithm deciding what you see, just you and your friends.
            </p>

            @auth
                <a href="{{ route('feed') }}" class="auth-btn" style="text-decoration: none;">
                    <span>▶</span> Go to Your Feed
                </a>
            @else
                <a href="{{ route('register') }}" class="auth-btn" style="text-decoration: none; margin-bottom: 0.6rem;">
                    <span>▶</span> Create an Account
                </a>
                <div class="auth-divider">
                    <span>Already have an account?</span>
                </div>
                <a href="{{ route('login') }}" class="auth-switch-btn">
                    Sign In →
                </a>
            @endauth
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