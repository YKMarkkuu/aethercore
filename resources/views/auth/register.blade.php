@extends('layouts.guest')

@section('title', 'Create Account')
@section('content')

<div class="window">
    <div class="window-title-bar">
        <div class="window-title">
            <span class="window-icon">✦</span>
            <span>AetherCore — Create Account</span>
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
                <h1>Join AetherCore</h1>
                <p class="brand-tagline">Connect with friends. Express yourself.</p>
            </div>

            <div class="feature-list">
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'sparkle', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">Free Forever</span>
                        <span class="feature-desc">No hidden fees, no credit card</span>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'palette', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">Custom Profiles</span>
                        <span class="feature-desc">Make it yours</span>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'lock', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">Privacy First</span>
                        <span class="feature-desc">You control your data</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="window-right">
            <form method="POST" action="{{ route('register') }}" class="auth-form">
                @csrf

                <div class="auth-field">
                    <label for="name">Display Name</label>
                    <div class="field-wrapper">
                        <span class="field-icon">@include('partials.icon', ['type' => 'person', 'size' => 15])</span>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" placeholder="Your name" required>
                    </div>
                    @error('name')
                        <span class="auth-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="username">Username</label>
                    <div class="field-wrapper">
                        <span class="field-icon">@</span>
                        <input type="text" name="username" id="username" value="{{ old('username') }}" placeholder="username" required>
                    </div>
                    <small class="auth-hint">Letters, numbers, and underscores only</small>
                    @error('username')
                        <span class="auth-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="auth-field">
                    <label for="email">Email Address</label>
                    <div class="field-wrapper">
                        <span class="field-icon">@include('partials.icon', ['type' => 'envelope', 'size' => 15])</span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="you@example.com" required>
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

                <div class="auth-field">
                    <label for="password_confirmation">Confirm Password</label>
                    <div class="field-wrapper">
                        <span class="field-icon">@include('partials.icon', ['type' => 'check', 'size' => 15])</span>
                        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="••••••••" required>
                    </div>
                </div>

                <div class="auth-terms">
                    <label>
                        <input type="checkbox" name="terms" required>
                        I agree to the <a href="{{ route('legal.terms') }}" target="_blank">Terms of Service</a> and <a href="{{ route('legal.privacy') }}" target="_blank">Privacy Policy</a>
                    </label>
                </div>

                <button type="submit" class="auth-btn">
                    <span>▶</span> Create Account
                </button>
            </form>

            <div class="auth-divider">
                <span>Already have an account?</span>
            </div>

            <a href="{{ route('login') }}" class="auth-switch-btn">
                Sign In →
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