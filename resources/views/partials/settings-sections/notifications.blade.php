<p class="settings-subtitle" style="margin-top: -0.3rem;">Choose what you get notified about.</p>

<form action="{{ route('settings.notifications') }}" method="POST" id="notificationsForm">
    @csrf

    <div class="settings-toggle-row">
        <div>
            <div class="settings-toggle-label">Email Notifications</div>
            <div class="settings-toggle-sub">Get emailed about important account activity.</div>
        </div>
        <label class="settings-switch">
            <input type="checkbox" name="notify_email" value="1" @checked($user->profile->notify_email ?? true) onchange="this.form.requestSubmit()">
            <span class="settings-switch-slider"></span>
        </label>
    </div>

    <div class="settings-toggle-row">
        <div>
            <div class="settings-toggle-label">Friend Requests</div>
            <div class="settings-toggle-sub">Notify me when someone sends a friend request.</div>
        </div>
        <label class="settings-switch">
            <input type="checkbox" name="notify_friend_requests" value="1" @checked($user->profile->notify_friend_requests ?? true) onchange="this.form.requestSubmit()">
            <span class="settings-switch-slider"></span>
        </label>
    </div>

    <div class="settings-toggle-row">
        <div>
            <div class="settings-toggle-label">Direct Messages</div>
            <div class="settings-toggle-sub">Notify me about new messages.</div>
        </div>
        <label class="settings-switch">
            <input type="checkbox" name="notify_messages" value="1" @checked($user->profile->notify_messages ?? true) onchange="this.form.requestSubmit()">
            <span class="settings-switch-slider"></span>
        </label>
    </div>

    <div class="settings-toggle-row">
        <div>
            <div class="settings-toggle-label">Likes & Comments</div>
            <div class="settings-toggle-sub">Notify me when someone likes or comments on my posts.</div>
        </div>
        <label class="settings-switch">
            <input type="checkbox" name="notify_likes_comments" value="1" @checked($user->profile->notify_likes_comments ?? true) onchange="this.form.requestSubmit()">
            <span class="settings-switch-slider"></span>
        </label>
    </div>
</form>
