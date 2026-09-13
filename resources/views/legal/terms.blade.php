@extends('layouts.guest')

@section('title', 'Terms of Service')
@section('content')

<div class="window window-narrow">
    <div class="window-title-bar">
        <div class="window-title">
            <span class="window-icon">📄</span>
            <span>AetherCore — Terms of Service</span>
        </div>
        <div class="window-controls">
            <button type="button" class="window-btn window-btn-min">─</button>
            <button type="button" class="window-btn window-btn-max">☐</button>
            <button type="button" class="window-btn window-btn-close" onclick="history.length > 1 ? history.back() : window.location.href='{{ route('welcome') }}'" title="Close">✕</button>
        </div>
    </div>

    <div class="legal-content">
        <p class="legal-updated">Last updated: {{ date('F j, Y') }}</p>

        <h2>1. Acceptance of Terms</h2>
        <p>By creating an account or otherwise using AetherCore ("the Service"), you agree to these Terms of Service. If you don't agree, please don't use the Service.</p>

        <h2>2. Your Account</h2>
        <p>You're responsible for the security of your account and everything that happens under it. Don't share your password, and let us know if you think your account has been compromised.</p>
        <p>You must provide accurate information when registering and keep it up to date.</p>

        <h2>3. Acceptable Use</h2>
        <p>You agree not to use the Service to:</p>
        <ul>
            <li>Harass, abuse, or harm another person</li>
            <li>Post content that is illegal, hateful, or infringes someone else's rights</li>
            <li>Impersonate another person or entity</li>
            <li>Attempt to disrupt, hack, or reverse-engineer the Service</li>
            <li>Use automated tools to access the Service without permission</li>
        </ul>

        <h2>4. Content You Post</h2>
        <p>You retain ownership of the posts, messages, and other content you create on AetherCore. By posting content, you grant us a license to store and display it as part of operating the Service. You're solely responsible for what you post.</p>
        <p>We may remove content or suspend accounts that violate these Terms.</p>

        <h2>5. Third-Party Integrations</h2>
        <p>AetherCore integrates with third-party services (such as Last.fm) to display information like your listening history. Your use of those integrations is also subject to that third party's own terms.</p>

        <h2>6. Termination</h2>
        <p>You may stop using the Service and delete your account at any time. We may suspend or terminate accounts that violate these Terms.</p>

        <h2>7. Disclaimer</h2>
        <p>The Service is provided "as is" without warranties of any kind. We don't guarantee it will be uninterrupted, error-free, or secure.</p>

        <h2>8. Changes to These Terms</h2>
        <p>We may update these Terms from time to time. Continued use of the Service after changes means you accept the updated Terms.</p>

        <h2>9. Contact</h2>
        <p>Questions about these Terms? Reach out through the contact details provided elsewhere on the Service.</p>
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