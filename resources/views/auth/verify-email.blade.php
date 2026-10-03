@extends('layouts.guest')

@section('title', 'Verify Email')
@section('content')

<div class="window">
    <div class="window-title-bar">
        <div class="window-title">
            <span class="window-icon">✦</span>
            <span>AetherCore — Verify Email</span>
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
                <h1>One Quick Check</h1>
                <p class="brand-tagline">Thanks for signing up! Before getting started, could you verify your email address by clicking the link we just emailed to you?</p>
            </div>

            <div class="feature-list">
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'envelope', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">Stay Verified</span>
                        <span class="feature-desc">Keep your account secure and active</span>
                    </div>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">@include('partials.icon', ['type' => 'sparkle', 'size' => 20])</span>
                    <div>
                        <span class="feature-title">Stay Connected</span>
                        <span class="feature-desc">Get access to your profile and communities</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="window-right">
            @if (session('status') == 'verification-link-sent')
                <div class="auth-success auth-error">A new verification link has been sent to your email.</div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}" class="auth-form">
                @csrf

                <button type="submit" class="auth-btn">
                    <span>▶</span> Resend Verification Email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="auth-switch-btn">Sign Out</button>
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
