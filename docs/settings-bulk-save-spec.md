---
Settings Modal: Bulk-Save Redesign Spec

Status: planning only. No files were changed. I did not see partials/settings-sections/ files for notifications beyond what was supplied, or any settings-content consumers beyond layouts/app.blade.php and settings/index.blade.php. Everything below comes from the files you listed.

**1. Current state assessment**

**Root cause of the "value reverts" bug**

- `public/js/ajax-forms.js` intercepts every non-GET `<form>`. On a JSON success with no remove, html, or refresh field, it calls `form.reset()`. That is the final branch, gated by `form.dataset.ajaxReset !== 'false'`.
- `SettingsController::respond()` returns only `{message, type}`, so every settings form hits that branch.
- `form.reset()` restores each control to its HTML default, meaning the selected or checked attribute rendered at page load. That is the old value, so the select visibly snaps back after a successful save.
- This affects privacy-visibility, -messages, -comments, -status, theme, and notifications. Notifications is worst: each switch fires `this.form.requestSubmit()`, so you toggle, it saves, then it flips back.
**What works**

- `updatePrivacy()` already has the right semantics: only fields present in the request are touched.
- `respond()` handles JSON and redirect modes.
- The scrollspy and accordion in `settings-modal.js` are solid.
- Export (`data-no-ajax` GET link) and delete (`data-confirm` plus the toast.js dialog) work.
**Inconsistencies**

WhereIssue`updateNotifications()`Calls `$request->boolean($field)` on all four fields unconditionally. A partial payload would silently set the missing ones to false.`updateTheme()`Writes to `users.theme`; everything else writes to `profiles`. The message says "Refresh to see it everywhere."`updateTheme()`Hardcodes the allowed values instead of using `User::getAvailableThemes()`.`updateAccount()``$request->has('email')` is true on every Account Info save, so `email_verified_at` is nulled even when the email is unchanged.`destroySession()`Returns JSON without remove, so the device row stays visible after "Log Out".`BlockController::destroy()`Returns a redirect, so ajax-forms falls back to a full reload and the modal closes.`deleteAccount()`The toast.js confirm handler calls `form.submit()`, which bypasses ajax-forms. A wrong password redirects back with errors rendered inside a hidden modal.[Last.fm](https://last.fm/)There is only `connectLastfm`; no disconnect route, though the Privacy Policy promises one. It also clears cache keys (`lastfm_top_artists_{id}`) that `LastfmService` doesn't use.`settings-content`The partial is rendered twice on `/settings`: once by `layouts/app.blade.php` (hidden modal) and once by `settings/index.blade.php`. That gives duplicate `section-*` IDs and two independent form states.`notify_*`The values are stored but nothing in the supplied code consumes them yet.
**2. New structure**

Do not wrap everything in one `<form>`. The Account, Security, Sessions, and Blocked Users sections contain their own `<form>`s, and HTML forbids nested forms (the parser silently drops the inner tags). Interleaved bulk and separate sections make a single wrapper impossible anyway.

**Approach:** marked fields collected by JS, scoped to the settings root.

- Bulk fields lose their `<form>` and Save button. Each control gets `data-bulk-field="visibility"` and `autocomplete="off"`.
- `settings-save.js` registers every `[data-bulk-field]` inside a `[data-settings-v2]` root. It records the original value at init in `dataset.original`, and collects only dirty fields on save.
- Scoping to the root, not document, keeps two instances from colliding.
**Regroup categories** in `$categories` in `settings-content.blade.php`, adding a `'bulk' => true` flag:

CategorySectionsBulk?Accountaccount-info, security, sessions, blocked-usersnoPrivacyprivacy-visibility, -messages, -comments, -statusyesNotificationsnotificationsyesAppearancethemeyesMusiclastfmnoYour Datadata-export, legalnoDanger Zonedanger-deleteno
Renaming "Data & Privacy" to "Your Data" avoids confusion with the new Privacy category. `partials/icon.blade.php` has no spare distinct icons, so add shield and bell SVG cases rather than reusing lock and chat.

**DOM skeleton** (adds a column wrapper so the bar spans the full dialog):

html

```
<div class="settings-v2" data-settings-v2 data-settings-uid="{{ $uid }}">
  <div class="settings-v2-body">
    <aside>…</aside>
    <div class="settings-v2-content-wrap">…</div>
  </div>
  <div class="settings-v2-savebar" data-savebar>…</div>
</div>
```

On the `/settings` page: wrap the modal include in `layouts/app.blade.php` with `@unless(request()->routeIs('settings.index'))`. That removes the duplicate instance, duplicate IDs, and stale originals.

**3. The save bar**

- **Location:** a direct child of `.settings-v2`, below `.settings-v2-body`. It is outside every `<form>`, and its buttons are `type="button"`. It spans sidebar and content, like the XP Display Properties button row.
- **Buttons, right-aligned (XP order):** OK (apply and close), Cancel (revert and close), Apply (apply, stay open; disabled when clean). A status text sits on the left.
- **Visibility:** always rendered. On bulk categories it is shown. On non-bulk categories it is collapsed unless any bulk field is dirty, so a pending change is never hidden by navigating away.
- **Style (tokens only):**

- Background `linear-gradient(180deg, var(--surface-panel-header), var(--surface-secondary))`.
- `border-top: 1px solid var(--border-default)`.
- Buttons use the existing `.settings-btn`, with `min-width: 75px`.
- Disabled state comes from the `button:disabled` rule already in `motion.css`.
- Press feedback is instant (`motion.css` already sets this).
- **Layout change in `settings-v2.css`:** `.settings-v2` becomes `flex-direction: column`. `.settings-v2-body` takes `flex: 1; min-height: 0` and the old row layout. The `max-width: 768px` rule must move from `.settings-v2` to `.settings-v2-body`. The bar costs about 40px of the modal's 600px cap, so keep the scrollareas at `height: 100%` of the body, not the modal.
**4. Backend design**

New route, in the existing `throttle:actions` group in `routes/web.php`:

php

```
Route::patch('/settings/preferences', [SettingsController::class, 'updatePreferences'])->name('settings.preferences');
```

**Payload:** JSON, changed fields only, real booleans:

json

```
{"settings": {"visibility": "friends", "theme": "midnight", "notify_messages": false}}
```

**Server-side field registry** (a constant in the controller, one source of truth):

FieldTableRule`visibility`profiles`in:public,friends,private``dm_permission`, `comment_permission`, `show_status_to`profiles`in:everyone,friends,nobody``theme`users`in: array_keys($user->getAvailableThemes())``notify_email`, `notify_friend_requests`, `notify_messages`, `notify_likes_comments`profiles`boolean`
**Behavior**

- Validate `settings.*` using rules built from the registry for the keys present. Unknown keys fail with a 422 on `settings.<key>`, which catches client/server drift.
- Apply inside `DB::transaction`, and only touch keys that were sent. Use `profileFor()` (handles a missing profile), and write via direct property assignment like `updatePrivacy()` does.
- Validation is atomic: any failure rejects the whole request. Real users can't produce invalid values from selects, so a failure means tampering or drift, and all-or-nothing is the clearest model.
**Success response** (200): `{message, type: "success", saved: {field: value, …}}`. `saved` holds the server's normalized values, which the client adopts as the new originals. This is the fix for the revert bug: the client never resets to HTML defaults.

**Errors:** standard Laravel 422 `{message, errors: {"settings.visibility": ["…"]}}`. The client strips the `settings.` prefix, finds `[data-bulk-field="visibility"]`, and renders the message in a `.settings-error` div beneath it.

**Old routes** (`settings.privacy`, `settings.theme`, `settings.notifications`): keep during migration, then delete along with their controller methods. Grep for other callers first.

**5. Unsaved-changes tracking**

- **Dirty definition:** current value ≠ `dataset.original`, computed from live state on input and change (delegated on the root). Checkboxes compare `checked` to a boolean original.
- **Apply is disabled when clean.** OK is always enabled (XP behavior), and when clean it just closes.
- **Visual cues:**

- Status text reads "2 unsaved changes".
- The dirty section gets a 3px left border in `var(--warning-border)`, using the existing warning tokens.
- The matching sidebar sublink and category get a trailing `*`. This tells users which tab holds pending edits while they're scrolled elsewhere.
- **Close guard:** the X button, Cancel, backdrop, and Esc all route through `window.SettingsSave.requestClose()`. If dirty, call `Toast.confirm("You have unsaved changes. Discard them?", {danger: true, confirmText: "Discard", cancelText: "Keep Editing"})`. toast.js only supports two buttons, so a three-button Save/Discard/Cancel is a later enhancement. The existing inline `closeSettings()` in `layouts/app.blade.php` becomes a one-line delegate.
- **Cancel and Discard revert** field values to their originals. The modal DOM persists while hidden, so skipping this would show stale dirty values with the bar lit on next open.
- **Page mode** (`/settings`): add a `beforeunload` guard while dirty; the buttons become Apply and Revert.
**6. Interaction with the flash-to-toast bridge**

The bulk save returns JSON directly to `settings-save.js`, which calls `Toast.show('Settings saved.', 'success')` itself. The bridge only fires on DOMContentLoaded for full-page redirects, so it is not involved.

Rules:

- The JSON branch must not call `->with('success')`. A lingering session flash would re-toast on the next navigation.
- A non-JSON fallback branch (old-style request) can still `back()->with('success', …)`, which the bridge picks up.
- Use `respond()` for both branches by passing `saved` through `$extra`.
**7. Sections that stay separate**

SectionRelationship to the new structureAccount InfoOwn form to `settings.account`, own Save button, unaffected. Follow-up: only unverify email when it actually changes.Password & SecurityOwn form, unaffected. Note `sometimes`Logged-in DevicesPer-row DELETE, unaffected. Follow-up: return `'remove' => true` and add `data-ajax-item` on `.device-item`.Blocked UsersPer-row DELETE, unaffected. Follow-up: make `BlockController::destroy` return JSON when `wantsJson()`.[Last.fm](https://last.fm/)Own form, unaffected. Follow-up: add a disconnect route and fix the cache keys.Download Your DataGET link with `data-no-ajax`, unaffected.Delete AccountKeeps its own `data-confirm` flow. Do not add it to the bar. Follow-up: surface the wrong-password error as a toast.
The bar's collapsed state on these categories keeps them visually uncontaminated by the Apply/OK/Cancel buttons.

**8. JS requirements**

- **New `public/js/settings-save.js`:** an IIFE exposing `window.SettingsSave = {isDirty, requestClose, apply}`.

- `init` per root: register fields, snapshot originals, bind delegated listeners, update bar and markers.
- `apply()`: snapshot the dirty set, POST JSON with `X-CSRF-TOKEN` and `Accept: application/json`, then handle the response.
- handle 200 / 422 / 401 / 419 / network error.
- theme swap, close logic, and `beforeunload` for page mode.
- **Don't extend `ajax-forms.js`.** Its contract is one `<form>` and one response, with reset semantics. A bulk mode would pollute it. The new form-less fields are naturally ignored by it.
- **Edits to existing files:**

- `layouts/app.blade.php`: load the script after `settings-modal.js`; delegate `closeSettings()`; add `id="themeStylesheet"` to the theme `<link>`.
- `settings-modal.js`: only if sublink markers aren't handled in the new file (prefer the new file).
- `settings-v2.css`: bar, dirty markers, layout change.
- `toast.js`: no change needed.
- **Save sequence details:**

- Mark busy (`cursor: progress`, label "Applying…").
- On success, set originals only for the keys that were sent, using the server's saved values. If the user edited a field mid-flight, it stays dirty.
- On 422, mark the fields, switch category, and scroll to the first error. Keep Apply enabled.
**9. Migration path**

Incremental. Mixed mode is safe because converted sections have no `<form>` and unconverted ones keep theirs.

1. **Hotfix now:** add `data-ajax-reset="false"` to the privacy, theme, and notifications forms. One attribute each, and it kills the revert bug immediately.
2. **Backend:** add `updatePreferences` and the route, with feature tests (valid save, partial payload leaves other fields untouched, unknown key, invalid value, missing profile). Leave old routes.
3. **Shell:** category regroup, column layout, inert save bar markup, new icons, `@unless` on the page-mode duplicate.
4. **JS + pilot:** add `settings-save.js`; convert Activity Status end to end; verify.
5. Convert the other privacy sections, notifications, and theme.
6. Theme live-apply (and optionally live preview with revert on Cancel).
7. Close guard and `beforeunload`.
8. **Cleanup:** delete old routes, controller methods, and Save buttons.
9. **Separate-section follow-ups** from section 7, each independent.
**10. Risks**

- **Nested forms:** wrapping sections in one form silently breaks the inner forms.
- **Absent checkbox equals false:** the registry must only touch keys present in the payload, unlike today's `updateNotifications`.
- **Safety-relevant semantics change:** "Only me" visibility used to apply on its own. Now a user who flips it and closes the modal stays public. The dirty guard and the bar's prominent status text are mandatory, not polish.
- **Browser form restoration and bfcache** can restore dirty control values after back-navigation. Mitigate with `autocomplete="off"` and compute dirty from live state against `dataset.original`, never from "did an event fire."
- **Two instances:** if the `@unless` is missed, duplicate IDs and stale originals return.
- **Theme files:** only `midnight.css` was supplied. If `daylight` or `retro` don't exist, selecting them 404s the stylesheet.
- **Two tables, one request:** theme is on users, the rest on profiles. The transaction and `profileFor()` null-profile case both need tests.
- **Esc and backdrop handlers:** Esc must not close the modal while a `Toast.confirm` dialog is open (it has its own Esc handler on document).
- **Layout regressions:** the media query move, and `min-height: 0` on the flex body or the scrollareas won't scroll.
- **Toast confirm and delete flow:** keep `data-confirm` off the save bar; `form.submit()` bypasses AJAX.
**11. Mockup description**

The modal looks like an XP property sheet. Left, the sidebar with seven categories (Account, Privacy, Notifications, Appearance, Music, Your Data, Danger Zone), the active one expanded to show its sub-links with scrollspy highlighting. Right, one scrollable pane per category. Along the bottom of the whole dialog runs a bevelled strip with the status on the left and OK / Cancel / Apply on the right.

- **Clean:** status reads "All changes applied" in muted text. Apply is dimmed. OK and Cancel are active.
- **Dirty:** the user changes Profile Visibility to "Friends only". That section gains a thin left border in the warning color, the Privacy category and sublink show a trailing `*`, the status switches to "1 unsaved change" in bold accent-dark, and Apply lights up. Moving to Appearance, picking a theme, adds a second marker. On Account, where the bar would normally collapse, it stays visible because changes are pending.
- **Saving:** Apply, OK, and Cancel are disabled, the cursor becomes progress, and the status reads "Applying…". Fields stay interactive, but any edit made mid-flight remains dirty afterward.
- **Success:** status returns to clean, markers clear, a green toast says "Settings saved.", and the theme swaps live. OK then closes the modal.
- **Error:** the modal jumps to the first failing field, which shows red helper text from the 422 under it. Sections stay marked dirty, and a red toast reads "Some settings couldn't be saved." Apply is re-enabled for retry.
- **Closing dirty:** X, Cancel, backdrop, or Esc opens the XP confirm dialog: "You have unsaved changes. Discard them?" with Discard and Keep Editing.

---