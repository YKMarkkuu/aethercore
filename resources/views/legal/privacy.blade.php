@extends('layouts.guest')

@section('title', 'Privacy Policy')
@section('content')

<div class="window window-narrow">
    <div class="window-title-bar">
        <div class="window-title">
            <span class="window-icon">@include('partials.icon', ['type' => 'lock', 'size' => 16])</span>
            <span>AetherCore — Privacy Policy</span>
        </div>
        <div class="window-controls">
            <button type="button" class="window-btn window-btn-min">─</button>
            <button type="button" class="window-btn window-btn-max">☐</button>
            <button type="button" class="window-btn window-btn-close" onclick="history.length > 1 ? history.back() : window.location.href='{{ route('welcome') }}'" title="Close">✕</button>
        </div>
    </div>

    <div class="legal-content">
        <p class="legal-updated">Last updated: {{ date('F j, Y') }}</p>

        <h2>1. Information We Collect</h2>
        <p>When you create an account, we collect the information you provide directly: your name, username, email address, and password (stored securely, never in plain text).</p>
        <p>If you choose to connect a Last.fm account, we store your Last.fm username so we can display your listening activity. We do not receive your Last.fm password.</p>
        <p>We also store content you create — posts, messages, comments, reactions — and basic usage information needed to operate the Service (such as when you last logged in).</p>

        <h2>2. How We Use Information</h2>
        <p>We use your information to:</p>
        <ul>
            <li>Provide and operate core features (profile, feed, chat, Spaces)</li>
            <li>Display your Last.fm listening activity if you've connected it</li>
            <li>Keep your account secure</li>
            <li>Communicate with you about your account when necessary</li>
        </ul>

        <h2>3. What We Don't Do</h2>
        <p>We don't sell your personal information. We don't share your data with advertisers.</p>

        <h2>4. Visibility of Your Information</h2>
        <p>Your profile, posts, and Last.fm stats may be visible to other users of the Service, depending on your settings and who you're connected with as a friend. Direct messages and Space chats are visible only to their participants/members.</p>

        <h2>5. Data Retention</h2>
        <p>We retain your information for as long as your account is active. If you delete your account, your profile and associated content are removed, though some information may persist briefly in backups.</p>

        <h2>6. Cookies & Sessions</h2>
        <p>We use session cookies to keep you logged in and to remember basic preferences (like your sidebar view). We don't use third-party advertising trackers.</p>

        <h2>7. Third-Party Services</h2>
        <p>The Service connects to third-party APIs (such as Last.fm and Deezer) to provide certain features. Interacting with those integrations may be subject to those services' own privacy practices.</p>

        <h2>8. Your Choices</h2>
        <p>You can update or delete your account information at any time from Settings. You can disconnect your Last.fm account at any time.</p>

        <h2>9. Changes to This Policy</h2>
        <p>We may update this Privacy Policy from time to time. We'll note the "last updated" date above when we do.</p>

        <h2>10. Contact</h2>
        <p>Questions about this Privacy Policy? Reach out through the contact details provided elsewhere on the Service.</p>
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