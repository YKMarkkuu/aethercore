<p class="settings-subtitle" style="margin-top: -0.3rem;">Choose what you get notified about.</p>

<div class="settings-toggle-row">
    <div>
        <div class="settings-toggle-label">Email Notifications</div>
        <div class="settings-toggle-sub">Get emailed about important account activity.</div>
    </div>
    <label class="settings-switch">
        <input type="checkbox" name="notify_email" value="1" data-bulk-field="notify_email" @checked($user->profile->notify_email ?? true) autocomplete="off">
        <span class="settings-switch-slider"></span>
    </label>
</div>

<div class="settings-toggle-row">
    <div>
        <div class="settings-toggle-label">Friend Requests</div>
        <div class="settings-toggle-sub">Notify me when someone sends a friend request.</div>
    </div>
    <label class="settings-switch">
        <input type="checkbox" name="notify_friend_requests" value="1" data-bulk-field="notify_friend_requests" @checked($user->profile->notify_friend_requests ?? true) autocomplete="off">
        <span class="settings-switch-slider"></span>
    </label>
</div>

<div class="settings-toggle-row">
    <div>
        <div class="settings-toggle-label">Direct Messages</div>
        <div class="settings-toggle-sub">Notify me about new messages.</div>
    </div>
    <label class="settings-switch">
        <input type="checkbox" name="notify_messages" value="1" data-bulk-field="notify_messages" @checked($user->profile->notify_messages ?? true) autocomplete="off">
        <span class="settings-switch-slider"></span>
    </label>
</div>

<div class="settings-toggle-row">
    <div>
        <div class="settings-toggle-label">Likes & Comments</div>
        <div class="settings-toggle-sub">Notify me when someone likes or comments on my posts.</div>
    </div>
    <label class="settings-switch">
        <input type="checkbox" name="notify_likes_comments" value="1" data-bulk-field="notify_likes_comments" @checked($user->profile->notify_likes_comments ?? true) autocomplete="off">
        <span class="settings-switch-slider"></span>
    </label>
</div>
