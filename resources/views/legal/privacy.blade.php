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
        <article class="legal legal-privacy">
            <h1>Privacy Policy</h1>
            <p><strong>Last updated: September 26, 2026</strong></p>

            <p>This policy explains what information AetherCore collects, why we collect it, what we do with it, and how you control it. We wrote it to be read, so it avoids legalese where it can. It follows the Philippine Data Privacy Act of 2012 (Republic Act No. 10173), its implementing rules, and the rules of the National Privacy Commission (NPC).</p>

            <h2>The short version</h2>
            <ul>
                <li>We collect what we need to run AetherCore: your account details, your profile, the things you post and send, and basic technical info that keeps your account secure.</li>
                <li>We don't sell your data. We don't show ads, and we don't use advertising trackers.</li>
                <li>Your password is hashed. We never store it in plain text and we can't see it.</li>
                <li>You can see, fix, download, or delete your data. You can also ask us to stop using it.</li>
                <li>If something goes wrong with your data, we'll tell you and the NPC.</li>
                <li>Questions go to <a href="mailto:kesaruu@aethercore.com">kesaruu@aethercore.com</a>.</li>
            </ul>

            <h2>1. Who we are</h2>
            <p>AetherCore ("AetherCore", "we", "us") is a social platform for profiles, posts, communities, messaging, and music. We are the personal information controller for the data described here, which means we decide how and why it is used.</p>
            <p>For anything about your privacy, including requests to use your rights below, contact our privacy team and Data Protection Officer at <a href="mailto:kesaruu@aethercore.com">kesaruu@aethercore.com</a>.</p>

            <h2>2. What we collect</h2>
            <h3>Information you give us</h3>
            <ul>
                <li><strong>Account:</strong> your name, username, email address, and password. The password is hashed with bcrypt before it is stored, so we never keep or see the plain text.</li>
                <li><strong>Profile:</strong> display name, avatar, banner, bio, location (only if you choose to add it), and your Top 8 picks: friends, artists, albums, and songs.</li>
                <li><strong>Content:</strong> the posts, comments, reposts, likes, reactions, images, and GIFs you share, plus direct messages and messages in AetherSpaces.</li>
                <li><strong>Reports and support:</strong> what you send us when you report content, appeal a decision, or contact us.</li>
            </ul>

            <h3>Optional: Last.fm</h3>
            <p>If you connect AetherTunes, you give us your Last.fm username. We use it to fetch your listening activity from Last.fm and show your "now playing" status and music on your profile. We never ask for your Last.fm password. This is entirely optional, and you can disconnect it at any time in your settings.</p>

            <h3>Information collected automatically</h3>
            <ul>
                <li><strong>Technical data:</strong> your IP address, browser and device information, and login session records. We use these to keep you signed in, show you your active sessions, and protect accounts from abuse.</li>
                <li><strong>Essential cookies:</strong> AetherCore uses only the cookies it needs to work: one keeps you logged in and one protects forms from forgery attacks. We don't use tracking, advertising, or analytics cookies.</li>
            </ul>

            <h3>What we don't collect</h3>
            <ul>
                <li>No payment information. AetherCore is currently free.</li>
                <li>We don't ask for sensitive personal information like government IDs, health information, or religious or political affiliation. Please don't put it in your public profile.</li>
            </ul>

            <h2>3. Why we use it</h2>
            <ul>
                <li><strong>Run your account and profile:</strong> account, profile, and content information is needed to provide the Service.</li>
                <li><strong>Deliver messages and AetherSpaces:</strong> message and membership information is used to provide those features.</li>
                <li><strong>Show music activity:</strong> Last.fm information is used when you choose to connect the service.</li>
                <li><strong>Protect accounts and moderate the Service:</strong> technical records, content, and reports support our legitimate interest in safety and help meet legal duties.</li>
                <li><strong>Handle legal requests:</strong> information is shared when required by law.</li>
                <li><strong>Respond to you:</strong> contact details and request information are used to provide support.</li>
            </ul>
            <p>We don't use your data for advertising, and we don't make automated decisions that have legal or similarly significant effects on you. If we ever want to use your data for a new purpose, we'll tell you first.</p>

            <h2>4. Who can see your information</h2>
            <h3>Other users</h3>
            <p>Your username, display name, avatar, banner, bio, Top 8, and public posts can be seen by other people on AetherCore. Direct messages are visible to the people in the conversation. Messages in an AetherSpace are visible to its members. Anything you share can be copied or screenshotted by the people who can see it, so share carefully.</p>

            <h3>Our moderation team</h3>
            <p>Messages are stored on our servers and are not end-to-end encrypted. Our moderators may review content, including messages, when it has been reported, when we need to investigate a safety issue or a Terms violation, or when the law requires it. We don't browse private messages for any other reason.</p>

            <h3>Service providers</h3>
            <p>We use trusted providers to host AetherCore, store backups, and send emails. They process data only on our instructions, under contracts that require them to protect it. Some may process data outside the Philippines. When they do, we remain responsible for your data and require the same level of protection.</p>

            <h3>Last.fm</h3>
            <p>If you connect AetherTunes, we request your listening data from Last.fm. Last.fm handles data under its own privacy policy.</p>

            <h3>Authorities</h3>
            <p>We share information with law enforcement or government agencies when the law requires it, for example under a valid court order. We also report child sexual abuse or exploitation material to the proper authorities as required by Republic Act No. 11930.</p>

            <h3>What we never do</h3>
            <p>We don't sell your personal data. We don't rent it or share it with advertisers or data brokers.</p>

            <h2>5. How we protect it</h2>
            <ul>
                <li>Passwords are hashed with bcrypt and never stored in plain text.</li>
                <li>Connections to AetherCore are encrypted in transit.</li>
                <li>Sessions are stored in our database. You can see your active sessions and log out other devices at any time.</li>
                <li>Only authorized team members can access personal data, and only when their work needs it.</li>
            </ul>
            <p>No system is perfectly secure. If a personal data breach affects your information in a way that the law requires us to report, we'll notify the National Privacy Commission and the affected users within 72 hours of learning about it. We'll explain what happened and what you can do.</p>

            <h2>6. How long we keep it</h2>
            <ul>
                <li><strong>While your account is active:</strong> we keep your data so we can provide the service.</li>
                <li><strong>After you delete your account:</strong> we remove your data from our live systems. Copies in our backups are deleted within 30 days.</li>
                <li><strong>Exceptions:</strong> we may keep specific records longer when the law requires it or when they are needed for an active legal matter or safety investigation. For example, the law requires us to preserve data connected to child exploitation reports. We keep these records only as long as needed and only for that purpose.</li>
            </ul>
            <p>Content you shared with others, such as messages you sent, may still appear in their copies of the conversation. It will no longer be linked to your profile.</p>

            <h2>7. Your rights</h2>
            <p>Under the Data Privacy Act, you have the right to:</p>
            <ul>
                <li><strong>Be informed</strong> about how your data is collected and used. This policy is part of that.</li>
                <li><strong>Access</strong> your data: what we have, where it came from, who received it, and how we use it.</li>
                <li><strong>Correct</strong> data that is wrong or incomplete. You can edit most of it yourself in your settings.</li>
                <li><strong>Delete</strong> your account and data, or ask us to block or remove data that is outdated, false, unlawfully obtained, or no longer needed.</li>
                <li><strong>Data portability:</strong> get a copy of your data in a structured, commonly used format that you can take elsewhere.</li>
                <li><strong>Object</strong> to processing, including processing based on our legitimate interests.</li>
                <li><strong>Withdraw consent</strong> at any time for things you opted into, like the Last.fm connection. This doesn't affect what we did before you withdrew.</li>
                <li><strong>Be compensated</strong> for damage caused by inaccurate, unlawfully obtained, or unauthorized use of your data.</li>
                <li><strong>File a complaint</strong> with the National Privacy Commission at <a href="https://privacy.gov.ph" rel="noopener" target="_blank">privacy.gov.ph</a> if you believe your rights were violated. We'd appreciate the chance to fix it first, but you don't have to contact us before going to the NPC.</li>
            </ul>
            <p>Your lawful heirs or assigns can also exercise these rights if you pass away or become unable to exercise them.</p>

            <h3>How to use your rights</h3>
            <p>You can edit your profile, disconnect Last.fm, manage sessions, and delete your account in your settings. For anything else, including a data export, email <a href="mailto:kesaruu@aethercore.com">kesaruu@aethercore.com</a>. We may ask you to confirm your identity so no one else can get your data. We'll respond within a reasonable time and won't charge you for reasonable requests.</p>

            <h2>8. Teens and children</h2>
            <p>You must be at least 13 years old to use AetherCore. If we learn that someone under 13 has created an account, we'll delete it and its data. If you believe a child under 13 is using AetherCore, please tell us at <a href="mailto:kesaruu@aethercore.com">kesaruu@aethercore.com</a>.</p>
            <p>In the Philippines, anyone under 18 is a minor. If you are between 13 and 17, please read this policy with a parent or guardian and make sure they're okay with you using AetherCore. Parents and guardians can contact us to ask about, correct, or delete their child's data.</p>
            <p><strong>If you're a teen, here's what matters most:</strong> what you post publicly can be seen by anyone on AetherCore, people you don't know may try to message you, and you can block or report anyone who makes you uncomfortable. Never share your home address, school, or phone number on your profile.</p>

            <h2>9. Changes to this policy</h2>
            <p>We'll update this policy when our practices change. If the changes are significant, we'll notify you by email or in the app before they take effect. The "Last updated" date at the top always shows the current version.</p>

            <h2>10. Contact us</h2>
            <p>AetherCore Privacy Team and Data Protection Officer<br>
            Email: <a href="mailto:kesaruu@aethercore.com">kesaruu@aethercore.com</a></p>
        </article>
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