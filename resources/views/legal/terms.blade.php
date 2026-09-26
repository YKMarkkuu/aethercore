@extends('layouts.guest')

@section('title', 'Terms of Service')
@section('content')

<div class="window window-narrow">
    <div class="window-title-bar">
        <div class="window-title">
            <span class="window-icon">@include('partials.icon', ['type' => 'document', 'size' => 16])</span>
            <span>AetherCore — Terms of Service</span>
        </div>
        <div class="window-controls">
            <button type="button" class="window-btn window-btn-min">─</button>
            <button type="button" class="window-btn window-btn-max">☐</button>
            <button type="button" class="window-btn window-btn-close" onclick="history.length > 1 ? history.back() : window.location.href='{{ route('welcome') }}'" title="Close">✕</button>
        </div>
    </div>

    <div class="legal-content">
        <article class="legal legal-terms">
            <h1>Terms of Service</h1>
            <p><strong>Last updated: September 26, 2026</strong></p>

            <p>These Terms govern your use of AetherCore, including profiles, posts, messages, AetherSpaces, and AetherTunes (the "Service"). By registering or continuing to use the Service, you agree to these Terms and our <a href="{{ url('/privacy') }}">Privacy Policy</a>. Please stop using AetherCore if you do not accept them.</p>

            <h2>1. Eligibility</h2>
            <ul>
                <li>You must be at least 13 years old to create an account.</li>
                <li>If you are 13 to 17, use the Service only with a parent or guardian's permission and after they have reviewed these Terms with you.</li>
                <li>You may not use AetherCore if we have previously banned you or if applicable law bars you from doing so.</li>
                <li>Do not create an account for another person without their consent or make extra accounts to evade a restriction.</li>
            </ul>

            <h2>2. Accounts and access</h2>
            <ul>
                <li>Provide truthful registration details and keep your contact email current.</li>
                <li>Protect your password and account sessions. You are responsible for activity under your account unless it resulted from access outside your control.</li>
                <li>If you suspect someone has accessed your account, change your password, end unfamiliar sessions in Settings, and contact <a href="mailto:kesaruu@aethercore.com">kesaruu@aethercore.com</a>.</li>
                <li>Choose a username that does not falsely suggest you are another person, company, or organization.</li>
            </ul>

            <h2>3. Conduct and prohibited material</h2>
            <p>Use AetherCore in a way that respects other people and applicable law. You may not use the Service to:</p>
            <ul>
                <li>Target, threaten, intimidate, bully, or persistently contact a person who has asked you to stop, including by coordinating others to do so.</li>
                <li>Promote hatred or attack people because of protected or personal characteristics such as race, ethnicity, nationality, religion, sex, gender identity, sexual orientation, or disability.</li>
                <li>Threaten violence, encourage terrorism, or celebrate violence against people.</li>
                <li>Send unwanted sexual material or share intimate material without the subject's consent.</li>
                <li>Publish private details such as a person's home address, phone number, school, workplace, identity documents, or private images without permission.</li>
                <li>Encourage suicide, self-injury, or eating disorders.</li>
                <li>Commit fraud, phishing, scams, spam, or other illegal activity, or distribute links intended to deceive or harm.</li>
                <li>Misrepresent who you are or use material in a way that violates another person's copyright, trademark, privacy, or other rights.</li>
                <li>Break into accounts or systems, distribute malware, collect user information without authorization, or interfere with the Service.</li>
                <li>Use bots or other automation to send messages, publish content, or generate activity in a disruptive or unauthorized way.</li>
                <li>Create or use another account to get around a suspension or ban.</li>
            </ul>

            <h3>Child safety</h3>
            <p>Sexual exploitation or sexualization of anyone under 18, child sexual abuse material, grooming, and attempts to exploit a minor are prohibited. We will remove such material, take action against involved accounts, preserve relevant information, and report it to the Philippine National Police, the National Bureau of Investigation, or other authorities as required by Republic Act No. 11930 and applicable law.</p>

            <h3>AetherSpaces</h3>
            <p>Space owners and moderators may set additional rules for their communities. Those rules may be more restrictive than these Terms, but cannot authorize conduct that these Terms or the law prohibit. Owners and moderators are responsible for managing their spaces consistently with these requirements.</p>

            <h2>4. Reports and enforcement</h2>
            <p>You can block accounts and report content or users through the Service. Our moderators may review reports and may also investigate issues they become aware of independently.</p>
            <p>Depending on the circumstances, we may remove or limit content, issue a warning, restrict features, suspend an account, permanently disable an account, or notify authorities where required or appropriate for safety. We consider factors such as seriousness, context, and prior conduct. We will generally explain account actions and accept appeals at <a href="mailto:kesaruu@aethercore.com">kesaruu@aethercore.com</a>. Notice or an appeal may be limited when it could create a safety risk or interfere with an investigation.</p>
            <p>We cannot review every item posted and do not promise to detect every violation. We may restrict content or access when reasonably needed to protect users, the Service, or our legal obligations.</p>

            <h2>5. Content and permissions</h2>
            <h3>Your work remains yours</h3>
            <p>You retain ownership of the posts, images, messages, profile details, and other material you create. You are responsible for having the rights and permissions needed to share it.</p>

            <h3>Permission needed to operate AetherCore</h3>
            <p>When you submit or publish content, you give AetherCore a worldwide, non-exclusive, royalty-free permission to host, store, reproduce, display, and make technical adaptations to that content only as needed to operate, maintain, improve, and provide the Service. This permission ends when you delete the content or account, except for material already copied or shared by others, backup copies retained for the period described in our Privacy Policy, or records we must preserve by law.</p>

            <h3>AetherCore materials</h3>
            <p>The AetherCore name, branding, interface, and software belong to AetherCore or its licensors. Do not copy or use them in a way that implies endorsement without written permission. Music metadata and related rights remain with Last.fm and the applicable music rights holders.</p>

            <h2>6. Copyright notices</h2>
            <p>If you believe content on AetherCore infringes your copyright, write to <a href="mailto:kesaruu@aethercore.com">kesaruu@aethercore.com</a> with the subject "Copyright Takedown". Include your contact details, identify the protected work and the material at issue, provide links where possible, explain why you believe the use is unauthorized, confirm the accuracy of your notice and your authority to submit it, and provide a physical or electronic signature.</p>
            <p>We will review a sufficiently detailed notice, restrict access to material when appropriate, and notify the account that posted it. If material was removed by mistake, you may send a counter-notice with the material, your explanation, and contact details. We may restore access unless the complainant provides notice of legal action. Accounts that repeatedly infringe rights may be disabled. Knowingly false notices may carry legal consequences.</p>

            <h2>7. External services</h2>
            <p>AetherTunes can connect to Last.fm to display listening information. Your use of Last.fm is governed by its own terms and privacy practices. We do not control Last.fm's availability, data, or other third-party sites linked by users.</p>

            <h2>8. Service availability</h2>
            <p>AetherCore is provided on an "as available" basis. We do not guarantee continuous access, error-free operation, complete security, or that user-provided information is accurate or suitable. Features may change, be added, or be discontinued. User posts express their authors' views, not necessarily ours.</p>

            <h2>9. Liability limits</h2>
            <p>To the maximum extent Philippine law allows, AetherCore and its team are not responsible for indirect, special, incidental, or consequential loss, including lost information, income, or goodwill, arising from the Service or other users' conduct or content.</p>
            <p>Because the Service is currently free, our total liability for claims connected to it is limited to PHP 5,000, to the extent the law permits. Nothing here removes liability that cannot legally be excluded or limits rights provided by the Data Privacy Act.</p>

            <h2>10. Suspension, closure, and deletion</h2>
            <p>You may stop using AetherCore and request account deletion through Settings. You may request an export before leaving. We may suspend or close an account for serious or repeated violations, legal requirements, or significant safety or operational risks. We may also discontinue the Service; where practical, we will provide reasonable notice so users can retrieve their data.</p>
            <p>After an account ends, we handle remaining information under the Privacy Policy. Provisions that need to continue—such as permissions for content already shared, liability limits, and governing law—remain effective as applicable.</p>

            <h2>11. Governing law and contact</h2>
            <p>These Terms are governed by the laws of the Republic of the Philippines. Please contact us first at <a href="mailto:kesaruu@aethercore.com">kesaruu@aethercore.com</a> so we can try to resolve a concern. Unresolved disputes may be brought before the proper Philippine courts.</p>

            <h2>12. Updates</h2>
            <p>We may revise these Terms as the Service changes. For material revisions, we will try to provide notice by email or in the app before they take effect. Continued use after an effective date means you accept the revised Terms; otherwise, you may delete your account and stop using AetherCore.</p>

            <h2>13. General provisions</h2>
            <ul>
                <li>These Terms and the Privacy Policy describe the full agreement about your use of AetherCore.</li>
                <li>If a court cannot enforce one provision, the remaining provisions continue to apply.</li>
                <li>Delaying enforcement of a provision does not waive the right to enforce it later.</li>
                <li>You may not transfer your account or these Terms to another person. AetherCore may transfer them as part of a reorganization or acquisition, with notice where required.</li>
            </ul>

            <h2>14. Contact</h2>
            <p>For questions about these Terms, reports, appeals, or copyright notices, contact the AetherCore team at <a href="mailto:kesaruu@aethercore.com">kesaruu@aethercore.com</a>.</p>
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