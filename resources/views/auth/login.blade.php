@extends('layouts.auth')

@section('title', 'Sign In - AetherCore')
@section('content')
<div class="auth-wrapper">
    <div class="auth-container">
        <!-- Windows XP Title Bar -->
        <div class="window-title-bar">
            <div class="window-title">
                <span class="window-icon">🌌</span>
                <span>AetherCore — Sign In</span>
            </div>
            <div class="window-controls">
                <button class="window-btn window-btn-min">─</button>
                <button class="window-btn window-btn-max">☐</button>
                <button class="window-btn window-btn-close">✕</button>
            </div>
        </div>

        <div class="auth-content">
            <div class="auth-left">
                <div class="auth-brand">
                    <div class="brand-logo">🌌</div>
                    <h1>AetherCore</h1>
                    <p class="brand-tagline">The core of your digital world</p>
                </div>

                <div class="auth-features">
                    <div class="feature-item">
                        <span class="feature-icon">🏠</span>
                        <div>
                            <span class="feature-title">AetherSpaces</span>
                            <span class="feature-desc">Create and join communities</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">🎵</span>
                        <div>
                            <span class="feature-title">AetherTunes</span>
                            <span class="feature-desc">Discover music with friends</span>
                        </div>
                    </div>
                    <div class="feature-item">
                        <span class="feature-icon">💬</span>
                        <div>
                            <span class="feature-title">AetherChat</span>
                            <span class="feature-desc">Real-time messaging</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="auth-right">
                <form method="POST" action="{{ route('login') }}" class="auth-form">
                    @csrf

                    <div class="auth-field">
                        <label for="email">Email Address</label>
                        <div class="field-wrapper">
                            <span class="field-icon">✉️</span>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
                        </div>
                        @error('email')
                            <span class="auth-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="auth-field">
                        <label for="password">Password</label>
                        <div class="field-wrapper">
                            <span class="field-icon">🔒</span>
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
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
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

        <!-- Windows XP Footer -->
        <div class="window-footer">
            <div class="footer-left">
                <span class="footer-start">🪟 Start</span>
                <span class="footer-divider">|</span>
                <span class="footer-status">🌌 AetherCore v1.0</span>
            </div>
            <div class="footer-right">
                <span class="footer-time">{{ now()->format('H:i') }}</span>
            </div>
        </div>
    </div>
</div>

<style>
    /* ===== AUTH WRAPPER ===== */
    .auth-wrapper {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #1a1a2e, #16213e);
        padding: 1rem;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* ===== WINDOW CONTAINER ===== */
    .auth-container {
        background: #f0edd8;
        border: 2px solid #b0a8a0;
        border-radius: 6px;
        width: 100%;
        max-width: 960px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.3);
        overflow: hidden;
    }

    /* ===== WINDOWS XP TITLE BAR ===== */
    .window-title-bar {
        background: linear-gradient(180deg, #1a4a9e 0%, #0d2b6e 100%);
        padding: 6px 8px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        user-select: none;
    }

    .window-title {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #ffffff;
        font-size: 0.8rem;
        font-weight: 600;
        text-shadow: 0 1px 0 rgba(0,0,0,0.3);
    }

    .window-icon {
        font-size: 1rem;
    }

    .window-controls {
        display: flex;
        gap: 4px;
    }

    .window-btn {
        width: 22px;
        height: 22px;
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 3px;
        background: transparent;
        color: #ffffff;
        font-size: 0.6rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s;
    }

    .window-btn:hover {
        background: rgba(255,255,255,0.15);
    }

    .window-btn-close:hover {
        background: #e81123;
        border-color: #e81123;
    }

    /* ===== AUTH CONTENT ===== */
    .auth-content {
        display: flex;
        padding: 2rem;
        gap: 2rem;
        min-height: 400px;
    }

    /* ===== LEFT SIDE — BRANDING ===== */
    .auth-left {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
        padding-right: 1.5rem;
        border-right: 2px solid #d0c8c0;
    }

    .auth-brand {
        text-align: center;
    }

    .brand-logo {
        font-size: 3.5rem;
        display: block;
        margin-bottom: 0.3rem;
    }

    .auth-brand h1 {
        font-size: 2rem;
        font-weight: 700;
        color: #1a4a9e;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .brand-tagline {
        font-size: 0.9rem;
        color: #6a6a6a;
        margin-top: 0.2rem;
    }

    .auth-features {
        display: flex;
        flex-direction: column;
        gap: 0.6rem;
        background: #f8f5ec;
        padding: 1rem;
        border-radius: 6px;
        border: 1px solid #d0c8c0;
    }

    .feature-item {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        padding: 0.4rem 0.6rem;
        border-radius: 4px;
        transition: background 0.15s;
    }

    .feature-item:hover {
        background: #f0edd8;
    }

    .feature-icon {
        font-size: 1.4rem;
        width: 32px;
        text-align: center;
    }

    .feature-title {
        font-weight: 600;
        font-size: 0.8rem;
        color: #1e1e1e;
        display: block;
    }

    .feature-desc {
        font-size: 0.7rem;
        color: #6a6a6a;
    }

    /* ===== RIGHT SIDE — FORM ===== */
    .auth-right {
        flex: 0 0 340px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .auth-form {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .auth-field label {
        display: block;
        font-size: 0.75rem;
        font-weight: 600;
        color: #1e1e1e;
        margin-bottom: 0.25rem;
    }

    .field-wrapper {
        display: flex;
        align-items: center;
        background: #ffffff;
        border: 2px solid #b0a8a0;
        border-radius: 4px;
        transition: border-color 0.2s;
    }

    .field-wrapper:focus-within {
        border-color: #3a7bd5;
    }

    .field-icon {
        padding: 0 0.5rem;
        font-size: 0.9rem;
        color: #6a6a6a;
    }

    .field-wrapper input {
        flex: 1;
        padding: 0.5rem 0.6rem;
        border: none;
        background: transparent;
        font-size: 0.85rem;
        color: #1e1e1e;
        outline: none;
        font-family: inherit;
    }

    .field-wrapper input::placeholder {
        color: #b0a8a0;
    }

    .auth-error {
        color: #d08080;
        font-size: 0.7rem;
        margin-top: 0.2rem;
        display: block;
    }

    .auth-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.75rem;
    }

    .remember-me {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        color: #1e1e1e;
        cursor: pointer;
    }

    .forgot-link {
        color: #3a7bd5;
        text-decoration: none;
    }

    .forgot-link:hover {
        text-decoration: underline;
    }

    .auth-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding: 0.6rem;
        border-radius: 4px;
        border: 2px solid #b0a8a0;
        background: linear-gradient(180deg, #f0edd8, #d4d0c8);
        font-size: 0.9rem;
        font-weight: 700;
        color: #1e1e1e;
        cursor: pointer;
        transition: background 0.15s;
        font-family: inherit;
    }

    .auth-btn:hover {
        background: #e0dcd0;
    }

    .auth-divider {
        text-align: center;
        font-size: 0.7rem;
        color: #6a6a6a;
        margin: 0.8rem 0 0.4rem;
        position: relative;
    }

    .auth-divider::before,
    .auth-divider::after {
        content: '';
        position: absolute;
        top: 50%;
        width: 30%;
        height: 1px;
        background: #d0c8c0;
    }

    .auth-divider::before { left: 0; }
    .auth-divider::after { right: 0; }

    .auth-divider span {
        background: #f0edd8;
        padding: 0 0.6rem;
    }

    .auth-switch-btn {
        display: block;
        text-align: center;
        padding: 0.5rem;
        border-radius: 4px;
        border: 2px solid #b0a8a0;
        background: #f8f5ec;
        color: #1e1e1e;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.85rem;
        transition: background 0.15s;
    }

    .auth-switch-btn:hover {
        background: #f0edd8;
    }

    /* ===== WINDOWS FOOTER ===== */
    .window-footer {
        background: linear-gradient(180deg, #d4d0c8, #b0a8a0);
        padding: 4px 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.7rem;
        color: #1e1e1e;
        border-top: 1px solid #c0b8b0;
    }

    .footer-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .footer-start {
        background: #f0edd8;
        padding: 1px 12px 2px 10px;
        border-radius: 3px;
        border: 1px solid #b0a8a0;
        font-weight: 600;
        font-size: 0.65rem;
        cursor: default;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .footer-divider {
        color: #6a6a6a;
    }

    .footer-status {
        color: #6a6a6a;
        font-size: 0.6rem;
    }

    .footer-time {
        background: #f0edd8;
        padding: 1px 8px;
        border-radius: 3px;
        border: 1px solid #b0a8a0;
        font-size: 0.65rem;
    }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .auth-content {
            flex-direction: column;
            padding: 1.5rem;
        }

        .auth-left {
            border-right: none;
            padding-right: 0;
            border-bottom: 2px solid #d0c8c0;
            padding-bottom: 1.5rem;
        }

        .auth-right {
            flex: 1;
        }

        .auth-features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.4rem;
        }
    }

    @media (max-width: 480px) {
        .auth-features {
            grid-template-columns: 1fr;
        }

        .auth-container {
            border-radius: 4px;
        }

        .auth-brand h1 {
            font-size: 1.6rem;
        }

        .window-title {
            font-size: 0.7rem;
        }
    }
</style>
@endsection