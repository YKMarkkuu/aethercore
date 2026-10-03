@extends('layouts.guest')

@section('title', 'Confirm Password')
@section('content')

<div class="window">
    <div class="window-title-bar">
        <div class="window-title">
            <span class="window-icon">✦</span>
            <span>AetherCore — Confirm Password</span>
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
                <h1>Secure Area</h1>
                <p class="brand-tagline">This is a secure area. Please confirm your password before continuing.</p>
            </div>

            <div class="feature-list">
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'lock', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">Protected Access</span>
                        <span class="feature-desc">Confirm your identity before continuing</span>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'check', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">Continue Safely</span>
                        <span class="feature-desc">Keep your account secure and in control</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="window-right">
            <form method="POST" action="{{ route('password.confirm') }}" class="auth-form">
                @csrf

                <div class="auth-field">
                    <label for="password">Password</label>
                    <div class="field-wrapper">
                        <span class="field-icon">@include('partials.icon', ['type' => 'lock', 'size' => 15])</span>
                        <input type="password" name="password" id="password" placeholder="••••••••" required autofocus>
                    </div>
                    @error('password')
                        <span class="auth-error">{{ $message }}</span>
                    @enderror
                </div>

                <button type="submit" class="auth-btn">
                    <span>▶</span> Confirm
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
