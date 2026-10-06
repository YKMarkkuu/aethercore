## AetherCore Delight & Polish Spec

**Status:** planning only. No code was written or changed.

**Scope notes before the audit:**

- **File references.** The copies I read have no line numbers. References use `file` plus selector or function name, which you can grep. I did not invent line numbers.
- **Realtime layer.** It looks dormant. `layouts/app.blade.php` loads none of `resources/js/bootstrap.js` or Echo, so `window.Echo` is probably undefined. That makes `realtime-posts.js` a no-op, and chat is on 3s polling, posts on 5s, and presence on 15–20s. Please confirm. Every "live" feeling below assumes polling unless Reverb is wired in.
- **CSS duplication.** `public/css/app.css` is not loaded by the layout. It still duplicates `base.css`, `components.css` and `utilities.css`, including three different `.settings-modal-content` definitions. Consolidate before adding tokens, or the new tokens will be overridden somewhere.
- **Clock timezone.** `config/app.php` sets timezone to `UTC`, and the guest pages' footer clock uses `now()->format('H:i')`. It shows server UTC time and never ticks.

---

### 1. Current state: what's missing

#### 1a. Interactive elements with weak or missing states

WhereProblemAll button classes (`.settings-btn`, `.xp-action-btn`, `.auth-btn`, `.chat-send-xp`, `.request-btn`, `.delete-btn`, `.topbar-btn`, `.mode-btn`, `.sidebar-tab`, `.xp-create-btn` in `base.css`, `components.css`, `utilities.css`, `guest.css`)Hover only. There is **no `:active` pressed state anywhere**. XP buttons visibly depress instantly, so this is the biggest era miss.Global CSSNo shared `:focus-visible`. `.settings-input`, `.topbar-search`, `.chat-input-xp-field` and `.field-wrapper input` set `outline: none` and change border color only. Only the music widgets use the XP dotted focus (`outline: 2px dotted`).`guest.css` `.window-btn-min` / `-max` in `login`, `register`, `welcome`, `forgot-password` etc.They have hover styling but **no behavior**. They are dead XP chrome.`layouts/app.blade.php` `#notifBtn`, `#settingsBtn`, `#searchInput``#notifBtn` has no handler. `#settingsBtn` has no visible `onclick`. The search input has no behavior. `🔔` and `⚙️` are also emoji, which `docs/design-principles.md` forbids.Remaining emoji and glyph icons`👥` in `profile.blade.php` (Top 8 Friends header) and `friends.blade.php`. `🎵🔥🎤💿📋` in `music.blade.php`. `⚠️` in `settings-sections/danger-delete.blade.php`. `💬` in `conversations/index.blade.php`. `music.blade.php` also hardcodes dark inline colors (`#1a1a2e`), so it ignores themes.`.space-channel-link`, `.space-add-channel-btn`, `.space-member-item` (`base.css`)Hover but no transition, while `.sidebar-tab` has one. Durations vary: `.14s`, `.15s`, `0.2s`, `.22s`. `transition: 0.2s` is shorthand for `all`.`.post-avatar`, `.msg-avatar-xp[data-user-popover]`, `.msg-username-xp`Clickable popover triggers with no hover or active affordance beyond `cursor`.All `.settings-modal` consumers (settings, share, report, create-space, members, role-delete, top-friends, image-adjust)They toggle `.hidden` (`display: none !important`), so they **pop in and out**. `Toast.confirm` animates (120ms) and `.profile-popup` has `popupSlideUp`, but `.user-popover` and `.status-menu` have no animation.

#### 1b. Silent actions

- **Flash messages render nowhere visible.**

- `PostController::store`, `destroy`, `ProfileController::updateStatsPeriod`, `SpaceController::join/leave`, `BlockController` and others return `back()->with('success'|'error', …)`.
- `layouts/app.blade.php` has no flash output. The only `session('success')` display is inside `settings-content.blade.php`, in a hidden modal.
- Posting, deleting a post, blocking and joining a Space are full reloads with **no confirmation at all**. Friend-action errors (`with('error')`) are never seen.
- **Friend actions don't change the UI.**

- `FriendController::sendRequest/acceptRequest/rejectRequest` return `{message}` only when `wantsJson()`. `ajax-forms.js` shows the toast, then calls `form.reset()` because there is no `remove`, `html` or `refresh` field.
- "Add Friend" stays "Add Friend". "Accept" leaves the row on `/friends`.
- A second click returns an HTML error, which makes `ajax-forms.js` fall back to a full reload.
- **Privacy and theme selects bypass AJAX.**

- `settings-sections/privacy-visibility`, `-messages`, `-comments`, `-status` and `theme` use `onchange="this.form.submit()"`. That does not fire a `submit` event, so `ajax-forms.js` never sees it.
- They do full-page POSTs, closing the settings modal. `updateTheme` literally says "Refresh to see it everywhere." Only `notifications.blade.php` uses `requestSubmit()` correctly.
- **`alert()` is the error and success channel in roughly 15 places.**

- `post-interactions.js`: like, comment, delete, repost, share.
- `chat-thread.js`: react, edit, delete.
- `report-modal.js`, `presence.js` (status picker), `profile-editor.js`, `profile.blade.php` `saveImageAdjust`.
- These break the XP dialog language that `toast.js` already established.
- **Incoming activity is invisible.**

- `pollPostUpdates` in `post-interactions.js` swaps like counts and comment lists with no cue.
- It only covers posts already rendered, so **new posts from others never appear** without a reload.
- `chat-thread.js` appends incoming messages silently: no sound, no unread divider, no tab-title change, no taskbar cue, and `patchMessage` for reactions is silent.
- **Presence changes are silent.**

- `presence-friends.js` `hydrateRow` swaps text and dot class, and `reorder` makes rows jump.
- `presence.js` `applyOwnStatus` is a text swap.
- **Profile saves have no feedback.**

- `profile-editor.js` just exits edit mode.
- `saveImageAdjust` swaps `img.src` with no transition.
- The Top 8 Friends list is rebuilt instantly (`top-friends-editor.js` `updateProfileList`), though it does toast.

#### 1c. Loads that feel like waiting

- **Profile page cold cache blocks the HTML response.**

- `ProfileController::fetchLastFmData` runs synchronously in `index` and `show`.
- On a cold cache, `LastfmService::getTopArtistsDirect` makes 3 Last.fm calls plus a **sequential Deezer lookup per artist** (5s timeout each). `getTopTracksDirect` adds `track.getInfo` per track, and `getNowPlaying` is uncached on every load.
- This is the single largest wait in the app.
- **Sidebar flash of wrong content.**

- The HTML ships `view-feed` visible. `DOMContentLoaded` in `layouts/app.blade.php` then calls `updateSidebarView(loadView())`.
- If you last used Friends, you see Feed, then Friends.
- `switchMode` has a similar flash on `/music`.
- **No placeholders for fetched content.**

- `user-popover.js` shows nothing until the fetch resolves.
- The Now Playing widget starts as "Loading…" (`sidebar-left.blade.php`).
- Avatars, banners and album art pop in with no placeholder fade.
- **Optimistic updates exist only in chat.**

- `chat-thread.js` `sendMessage` is the model to follow.
- Likes, comments, reactions, friend requests, status and post creation all wait for the round trip.
- **Hard navigations everywhere.**

- Mode toggle, sidebar tabs and the stats-period `<select>` all reload the page. Combined with the flash above, every navigation feels like a rebuild.

#### 1d. Static widgets

- **Cassette** (`top-songs-cassette.blade.php`)

- The trailing comment says "Reels are intentionally static."
- `.is-playing` only lifts the sticky note 2px.
- Selecting a track does nothing to the deck.
- **Vinyl shelf** (`top-albums-vinyl.blade.php`)

- `.vinyl-spine-fill` sets its width instantly.
- There is no record behind the sleeve.
- The "Drag or scroll" hint never goes away.
- **Rolodex** (`top-artists-rolodex.blade.php`)

- Selection is a 90ms opacity fade on the active card plus a 150ms nudge.
- `rx-tally` and `rx-stamp` are plain text.
- Peeks are keyed by rank, so nothing physically moves between slots.
- **Dead partial.** `top-artists-phone.blade.php` isn't included by `profile.blade.php`. Decide whether it is an alternate or should be removed.
- **Empty states** are one line of text with no call to action. Example: "No top albums found" with no "Connect Last.fm" link.

#### 1e. Success isn't rewarded

Nothing marks any of these moments:

- Post created, friend accepted, Space joined or created, first friend, first post.
- Last.fm connected (no payoff: "your now-playing appears here").
- Avatar or banner changed, Top 8 saved.
- Report submitted (a native `alert`).
- Theme changed (needs a reload).

---

### 2. The reward loop concept

**Principle:** reward *real* social events, such as a friend arriving, a message, a like, or someone visiting. Don't invent fake urgency. For a passion project, the hook is "my friends' presence and music are alive in here," not streaks or guilt.

Five concepts, all built from XP/early-2000s vocabulary:

#### A. Taskbar and system tray with balloon tips (anchor concept)

Add a 30px bottom taskbar to `layouts/app.blade.php`.

- **Start button** (opens a menu with Profile, Settings, Admin if applicable, Log Out). It can eventually absorb the mini-profile popup.
- **Taskbar buttons for unread DMs.** An unread conversation gets a button. On a new message the button **flashes orange** (XP's signature "attention" cue) for 2 cycles, then stays solid orange until opened. Reduced motion means solid orange only.
- **Tray**, left to right:

- Speaker icon, toggling the sound scheme.
- Now-playing note icon, with the current track in its tooltip.
- Presence dot, where click cycles Online, Idle, DND or opens the existing status menu.
- Notification icon with count. It replaces `🔔`.
- A **live client-side clock** in the viewer's local time. It fixes the UTC issue and gives the clock a purpose.
- **Balloon tips** (the XP tray popup with a tail pointing at the tray icon) for social events: friend request, DM when the tab isn't on that chat, "X signed on," and Space mentions.

- Auto-dismiss after 7s, click to open the target, cap of 1 visible with a queue.
- Not for action outcomes. Those stay as Toasts.

#### B. Original sound scheme (opt-in)

A tiny set of short sounds: `click`, `send`, `receive`, `notify`, `error`, `signon`, `signoff`, `select` (widget), `clunk`.

- **Default off.** Toggle from the tray speaker and in Settings, persisted in `localStorage`. Play only after a first user gesture (browser autoplay rules).
- **Author original sounds**, synthesized with WebAudio or tiny audio files. Do not ship Microsoft's, AIM's or any other recognizable sound files. They are copyrighted, and the Privacy Policy/ToS posture is "no third parties."
- **Never play sound for hover.** Sounds attach to outcomes: send, receive, error, sign-on, widget select.

#### C. Buddy-list presence events

- A friend coming online produces a subtle row highlight, a sign-on chime if sound is on, and optionally a balloon: "Alex signed on."
- If they have Last.fm connected, the friend row and popover show an **away-message-style line**: "♪ listening to *Track — Artist*." The data already flows through `now-playing-batch`.
- A track change flashes the row's note icon once.
- Respect `show_status_to` and the offline-hides-activity rule already in `ProfileController::nowPlaying`.
- This is the strongest reason to open the app. Your friends' music and presence are alive.

#### D. Windows behave like windows

- Modals get XP title-bar chrome matching the `.window` / `.window-title-bar` pattern from `guest.css`.
- They **open** with a short scale and fade (`0.96→1`) and **close** faster.
- Dialog semantics are consistent: Esc cancels, Enter confirms, default focus on the safe action.
- Busy state uses native `cursor: progress` (the arrow-plus-spinner) on any `.ajax-busy` form, with no assets needed.
- Error dialogs get the `error` sound.
- On the guest pages, the dead `─` and `☐` buttons get behavior: minimize collapses the window to a "taskbar" button at the bottom, and maximize toggles full width. This is a cheap and delightful first impression.

#### E. "While you were away" and Tip of the Day

A dismissible XP dialog on the first load after a long gap, plus an optional "Tip of the Day" mode with a "Show tips at startup" checkbox. See section 7.

---

### 3. Animation system

**Philosophy:** XP was fast. Interface chrome used short, mostly decelerating transitions and instant pressed states. There were no springs, no blur, no parallax and no page transitions. Physical objects (the music widgets) get weight and a little overshoot, and chrome never does. Things that carry meaning animate. Everything else is instant.

#### 3a. Duration tokens

TokenValueUse`--motion-instant`0ms`:active` press, focus ring, selection state, tab switch, text and number swaps`--motion-hover`100msHover color, background, border and shadow on buttons, rows, links`--motion-quick`140msToggle slider, reaction pill pop, tooltip fade (after a 450ms hover delay), badge appear`--motion-menu`160msMenus, popovers, status menu, user popover (fade plus 4px slide)`--motion-window-in`160msModal and dialog open`--motion-window-out`100msModal and dialog close`--motion-toast-in` / `-out`180ms / 160msToast and balloon. Dwell: 4s default, 7s error and balloon, pause on hover`--motion-object`220msMusic-widget physical moves (sleeve lift, card flip, shell drop)`--motion-slow`320msAccordion collapse, deferred-widget reveal. **Ceiling for any one-shot animation.**`--motion-marquee`1200ms, `steps(8)`XP progress marquee loop

Ambient loops (reel spin, flashing taskbar button) run **only** during an explicit state: hover, selected, playing or unread. There are no idle loops.

#### 3b. Easing tokens

TokenCurveUse`--ease-out``cubic-bezier(0.22, 0.61, 0.36, 1)`Default for entrances and hover`--ease-in``cubic-bezier(0.55, 0.06, 0.68, 0.19)`Exits (closing windows, toasts out)`--ease-inout``cubic-bezier(0.45, 0, 0.55, 1)`Reversible states (switch, accordion)`--ease-thunk``cubic-bezier(0.34, 1.3, 0.64, 1)`**Physical objects only**, overshoot ≤ 8%: cassette drop, stamp, sleeve lift`--ease-linear``linear`Spin loops, marquee`steps(n)`steppedProgress bars, odometer digits, "progressive image reveal." Chunky stepping is more XP than any bezier.

#### 3c. Animate vs. instant

AnimateStay instantModal and popover open/closePressed (`:active`) statesToast and balloon enter/exitFocus ringsMenu and status-menu openTab and sidebar-view switchingHover color shifts (100ms)Text and count changes (pulse the container once, don't tween the number)Widget physical movesPresence dot color swap (pulse once on change)Progress bars (stepped)Checkbox and radio stateNew message or post arrival (brief highlight, `jumpHighlight` pattern)Scroll position jumps during history load

Rule: never animate with `transition: all`. List properties explicitly, and prefer `opacity`, `transform`, and `box-shadow` for hover.

#### 3d. Reduced motion

**Where it exists today:**

- `.vinyl-sleeve` transition.
- Wall phone handset, status, button transitions (CSS plus JS `reducedMotion`).
- Rolodex `.rx-peek` / `.rx-active` (CSS plus JS).
- Cassette `.cass-note` transition.

**Where it's missing:**

- `toast.js` (injected styles and the confirm dialog scale and fade).
- `utilities.css`: `popupSlideUp`, `.settings-switch-slider`, `.settings-accordion-arrow`, the `.reaction-picker-btn:hover` scale, and the `.color-option` hover scale.
- `components.css`: `.msg-highlight-flash` (`jumpHighlight`), and the blanket `transition: 0.2s` on buttons and inputs.
- JS smooth scrolling: `scrollIntoView({behavior:'smooth'})` in `chat-thread.js` and `settings-modal.js`, and `scrollTo({behavior:'smooth'})` in the vinyl partial.

**Policy:**

1. One global block in `base.css` zeroes all `--motion-*` tokens under `prefers-reduced-motion: reduce`. Anything using tokens is covered automatically.
2. Reduced motion removes translate, scale, loops and flashes, but **keeps state changes** (color, selection, visibility).
3. A small `Delight.motion.reduced` JS flag replaces the per-widget `matchMedia` copies, and all JS smooth scrolls consult it.
4. Sound is a separate user setting, not tied to reduced motion.

**Token delivery:** a new `public/css/motion.css` loaded right after `base.css`, plus `public/js/delight.js` exposing `window.Delight` (`motion`, `sound`, `balloon`, `tray`). One place, like `base.css` is for colors.

---

### 4. Micro-interaction inventory

#### Buttons

- **All push buttons** (`.settings-btn`, `.xp-action-btn`, `.auth-btn`, `.chat-send-xp`, `.request-btn`, `.delete-btn`): hover 100ms lift to `--surface-hover`. `:active` is **instant**: invert the bevel (swap light and dark inset shadows) and shift contents 1px. `:focus-visible` is an XP dotted rectangle. A busy button gets `cursor: progress` and its label becomes "Saving…".
- **Topbar buttons and `.mode-btn`:** hover 100ms, instant pressed, mode switch has a stepped underline or slide on the active state.
- **Danger buttons:** same, with the `error` color. The confirm dialog already exists.
- **Post action buttons** (`.post-action-btn`):

- Like: optimistic heart fill, a single 140ms scale pulse (`--ease-thunk`, 1→1.15→1), and the count changes instantly.
- Comment: toggles the panel with a 160ms height reveal.
- Share: instant.
- Report: opens the modal, no extra animation.

#### Cards and list items

- **`.post-item`:** hover is already a background shift, so add the 100ms transition. A **new** post from polling gets the `jumpHighlight` flash once. A deleted post collapses its height in 160ms, then is removed.
- **`.friend-item`, `.conversation-item`, `.space-item`, `.space-channel-link`, `.space-member-item`:** hover 100ms. Unread state is **bold name plus an orange count**. A row that changes presence pulses its dot once.
- **Reorder:** presence-sorted rows slide to their new position over 160ms instead of jumping.
- **`.xp-friend-badge` / `.xp-top-badge`:** hover 100ms, instant pressed.
- **Top 8 Friends rows:** on save, new rows fade in staggered at 40ms, capped at 8.

#### Modals, popovers, menus

- **`.settings-modal` family:** open 160ms (opacity plus scale 0.96→1, `--ease-out`), close 100ms (`--ease-in`). The backdrop fades 120ms. Add a shared `open/closeModal(el)` helper rather than flipping `.hidden`.
- **`.user-popover`:** show a skeleton card immediately (avatar circle, two blocks, marquee strip), then swap contents with no layout jump.
- **`.profile-popup` and `.status-menu`:** same menu timing (160ms). Selecting a status pulses the dot.
- **Confirm dialog** (`toast.js`): keep it as the reference. Add the reduced-motion guard.
- **Image-adjust modal:** the preview crossfades, and Save shows a stepped progress bar.
- **Settings modal accordion:** sub-nav reveal 160ms with `--ease-inout`. The scrollspy highlight changes instantly.

#### Form fields

- **Inputs and textareas** (`.settings-input`, `.chat-input-xp-field`, `.field-wrapper`): focus gives a border-color change (100ms) **plus** an inset shadow. Restore a visible focus outline in the XP style instead of `outline: none`. Validation errors shake once at most (80ms, 3px) or just flip border and message in, because XP didn't shake.
- **Select-on-change controls:** convert `this.form.submit()` to `requestSubmit()` so they use AJAX. On success the select shows a quick checkmark flash or a toast. Theme changes apply live by swapping the theme stylesheet `href` (no reload).
- **Toggle switches** (`.settings-switch`): keep 150ms, add `:active` thumb squeeze and token the duration.
- **Post composer:** the format-toolbar buttons get a pressed state, and the character counter appears near the limit.
- **Chat input:** sending clears and pulses the send button. Failed sends already show retry.

#### Chat

- **New incoming message:** appended with a 140ms fade, plus `receive` sound if enabled and the tab is hidden or the window isn't focused.
- **Unread divider** ("New messages" line) reusing the `.chat-divider` pattern.
- **Reactions:** the picker open is 140ms. A reaction pill appears with a small scale pop (140ms, `--ease-thunk`), and counts change instantly.
- **Reply jump:** keep the highlight flash.
- **Typing indicator:** an MSN-style status line under the input, "Alex is typing a message…". No bouncing dots. It needs whisper events or a lightweight endpoint, so it depends on realtime.
- **Message send:** the pending dim exists, so add the `send` sound and a subtle settle when the row confirms.

#### Navigation

- **Sidebar tabs:** instant switch, no flash. Fix by applying the saved view before first paint, using an inline script in `<head>` or a class on `<html>`.
- **Mode toggle:** for now keep the page load, but show a stepped progress marquee and `cursor: progress` between click and navigation.
- **Page load:** the main content fades in over 100ms only when coming from a normal navigation.

#### Toasts and balloons

- See sections 5c and 2A. Toasts get an SVG icon, a stack cap of 4, pause-on-hover, click-to-dismiss, and error variants that last longer. Balloon tips are a separate component.

#### Music widgets

See section 6.

---

### 5. Loading states

#### 5a. Skeleton style (XP-correct)

No shimmer sweep. A skeleton is flat `--surface-sunken` blocks that hold the final layout, plus a small **XP marquee progress bar** (green-blue blocks marching, `steps(8)`, 1.2s loop) in the panel header or footer. Avoid layout shift by reserving the real dimensions. Under reduced motion the marquee becomes a static filled bar.

Use `.skeleton-block` and `.xp-marquee` utility classes, defined once in `motion.css`.

#### 5b. Per surface

- **Feed**

- Initial render is server-rendered, so no skeleton is needed.
- Add a **"N new posts" bar** at the top, built from polling. Clicking it loads them with `jumpHighlight`.
- Add a skeleton only for any future "load older" action.
- **Comments**

- They arrive with the page. Posting a comment is **optimistic**: append a pending row at 60% opacity, reuse chat's `msg-pending` styling, then confirm or show retry.
- The poller must stop replacing the whole list when ids match (it already checks that).
- **Chat**

- Initial history is server-rendered. Optimistic send exists.
- Add the unread divider, and a "Connection lost, retrying…" balloon row if polling fails more than twice.
- **Profile**

- The highest-value change: **defer the three music widgets.** Render the page instantly with each widget's shell and skeleton, then fetch widget data and fill each in.
- This needs a small data endpoint per widget or one combined endpoint. That is an implementation decision outside this spec.
- Alternative with no new endpoint: serve stale cache first (the service already keeps 7-day stale copies) and revalidate in the background, so first paint never waits on Deezer.
- Reveal uses a 320ms fade-in. Album and artist images fade in over 120ms over the existing placeholder color. Optionally use a stepped "interlaced" wipe on album art for a dial-up nod.
- The now-playing fetch on profile load should be asynchronous and not block the HTML.
- **Sidebar**

- Fix the saved-view flash (section 4, Navigation).
- Now Playing: replace "Loading…" with a tiny marquee, then the track.
- Friends presence: keep the cache hydration (good pattern), and animate row reorders.
- **User popover:** skeleton shown at once (section 4).

#### 5c. Optimistic updates: do and don't

**Optimistic (instant UI, rollback plus error toast on failure):**

- Like and unlike (heart plus count ±1).
- Comment append.
- Reaction toggle (pill and count).
- Friend request / accept / decline (button or row state changes immediately; the server response must include enough to reconcile).
- Presence status pick.
- Post creation (prepend a pending card). This requires `PostController::store` to return JSON with the new post, so it is a controller change.
- Deletes: collapse the row immediately, and revert if the server refuses.

**Not optimistic (wait with a busy state):**

- Reports, blocks, bans and suspensions, account deletion, role and permission changes, password changes, anything permission-dependent or destructive, and settings saves.

#### 5d. Toast vs. spinner vs. nothing

Expected waitTreatment< 100msNothing100–400msDisable the control, `cursor: progress`. No spinner.400ms–2sInline busy: label changes ("Saving…") plus marquee on the control> 2sPanel skeleton plus marquee

OutcomeFeedbackResult is visible in place (like, comment, sent message, status dot)**No toast.** The change is the feedback.Result is *not* visible where the user is (report sent, settings saved, theme applied, Last.fm connected)**Toast.**Any error**Toast, longer dwell, with a Retry action when safe.** Never `alert()`.Social event from someone else**Balloon tip**, not a toast.

Additions to the existing `Toast.show(message, type)` pattern:

- A third type `info`.
- An optional `{ action: { label, onClick } }`.
- A layout-level bridge that converts `session('success')` and `session('error')` into toasts on page load, so every remaining non-AJAX `back()->with(…)` path gets feedback for free. This single change fixes most of the silent-action list.
- The same bridge should respect `data-ajax-item` removal so deleted rows animate out.

---

### 6. The music widgets

Common layer: all three share `Delight.sound` (`select`, `clunk`), `Delight.motion.reduced`, and `--motion-object` / `--ease-thunk`. Each widget keeps its own scoped CSS and IIFE JS, per the repo conventions. Hover effects stay behind `@media (hover: hover)`. Everything has a reduced-motion fallback of "state changes, no movement."

#### Rolodex (Top 8 Artists)

Today, selecting swaps content with a 90ms fade while the peek cards stay in fixed rank slots.

- **Flip.** The outgoing active card tips back over its top edge (`rotateX` 0→-70° with `perspective`, 180ms, `--ease-in`) and settles into its peek slot, while the incoming card rises out of its slot (`rotateX` 70°→0, `--ease-thunk`). Total ≈ 220ms. The photo and text swap at the midpoint, not by fade.
- **Depth.** Peek cards get progressive shading (slightly darker with rank distance). The active card's shadow deepens during the flip, then settles. The frame's bottom edge takes a 2px, 80ms "clunk" bounce on landing.
- **Distance.** Jumping from #1 to #5 riffles through the intermediate cards, with a flip animation per card at 35ms spacing, capped at 4 cards. Larger jumps just do a fast flick.
- **Stamp and tally.** `rx-stamp` ("RANK 03") **stamps down** (`scale 1.35→1`, 80ms, `--ease-thunk`, final rotation kept). `rx-tally` (the handwritten "~12,345") **writes on** with a stepped `clip-path` reveal (`steps(8)`, 300ms).
- **Sound.** A card-riffle tick per flip (pitch-shifted slightly per card), and a paper "thup" on landing.
- **Input.** Keep existing arrows and number keys. Holding an arrow repeats. Mouse wheel steps ranks only while the widget has focus, so there is no scroll-jacking.
- **Reduced motion:** instant swap, stamp and tally appear immediately.

#### Cassette deck (Top 8 Songs)

Today the shell and reels are static. Only the sticky note lifts.

- **Hover** (`hover: hover` only, pointer over the shell): the reels spin slowly (4s linear loop on the reel SVGs, paused outside hover), and a faint glass-glint sweeps the window once. No sound.
- **Play** (select a track): the cassette **drops** 6px into the slot (140ms ease-in, then a 60ms `--ease-thunk` settle), with the `clunk` sound. The reels spin up and keep spinning while the track is selected. A tape counter odometer (3 digits, stepped digit roll) shows the track rank. The sticky note's lift stays.
- **Pack animation (optional):** the left tape pack shrinks and the right grows slowly while playing, using two circles whose radii change over a long linear loop.
- **Now-playing LED:** if the owner's current Last.fm now-playing matches a Top 8 song, a small red LED on the deck lights and that track row shows a "playing" marker. The data is already available through `now-playing`.
- **Eject** (Escape, or click the selected track again): the cassette pops up 10px with `--ease-thunk`, the reels **spin down over about 400ms** (a JS-driven friction decay, since `animation-play-state` cannot ease), and an eject "ka-chunk" plays.
- **Track list:** selected row gets an instant pressed bevel. The row hover stays at 100ms.
- **Reduced motion:** no spin, no drop. The selected state shows as the LED plus the note lifted by color only.

#### Vinyl shelf (Top 8 Albums)

Today the sleeve lean, hover tilt, lift and snap-scroll exist, and the spine meter jumps to its value.

- **Scroll:** native snap stays. Add edge fade gradients that appear only while there is more to scroll in that direction. The "Drag or scroll" hint fades out after the first interaction and does not return (remembered in `localStorage`).
- **Hover:** the tilt is kept. A **record peeks** about 14px out of the top of the sleeve (140ms, `--ease-out`). There is room because the scroller already has 24px of top padding.
- **Click:**

- The sleeve lifts (existing −8px) and the disc slides out to about 40% (220ms, `--ease-thunk`), then turns slowly (6s linear) while selected.
- The spine meter fills with **stepped chunky blocks** over 400ms (`steps`), like an XP progress bar, instead of an instant width.
- Paper-slide sound on lift and a soft thunk on deselect.
- **Deselect:** the disc slides back in (160ms), and the sleeve drops.
- **Drag:** keep the existing drag-and-settle. Add inertia on release only if it can reuse the snap logic.
- **Optional:** a faint vinyl crackle loop at very low volume while a record is selected, **only** if sound is on.
- **Reduced motion:** the disc and meter appear immediately without turning or sliding.

---

### 7. The "welcome back" feeling

#### Baseline (no notifications table needed for v1)

`users.last_seen_at` is overwritten by the heartbeat every 20s, so it can't answer "since last visit." Add a session-baseline concept: on the **first heartbeat after a gap of more than 30 minutes**, store the *previous* `last_seen_at` as `visit_baseline_at` (a new column or a cache key per user). Everything below counts "since `visit_baseline_at`."

Existing data you can already count:

- Unread DMs (`messages.is_read`; `User::getUnreadMessagesCount`).
- Pending friend requests (`friendRequests()`).
- Likes and comments on your posts (`post_likes.created_at`, `comments.created_at`).
- New posts from friends (`posts.created_at`).

**Gaps:** Space unread has no per-member read pointer (DMs have `conversation_participants.last_read_at`; `space_members` has none). It needs a column for "unread in Spaces." The `🔔` notification list needs a real table eventually.

#### The return experience, in order

1. **Login or first load after a gap:** a very short XP-style "Welcome to AetherCore" splash with a stepped progress bar, no longer than 600ms. It appears **only on login**, not on every page.
2. **Balloon tip from the tray**, about a second after load: "You have 3 new messages and 2 friend requests." Click opens the first unread.
3. **"While you were away" dialog** (dismissible, with "Don't show this again" and "Show tips at startup"), listing the counts with links: DMs, requests, likes and comments on your posts, new friend posts, Space activity. If there is nothing new it shows a short Tip of the Day instead, so the dialog never feels empty. Tips should explain real features (Top 8 editing, Last.fm, blocking).
4. **Feed "New since your last visit" divider**, reusing the `.chat-divider` look, placed above the first post newer than the baseline. New posts have no other special highlight, so the feed feels lightly different rather than reshuffled.
5. **Tab title and favicon:** `(3) AetherCore - Feed`. A canvas-drawn badge on the favicon replaces emoji.
6. **Taskbar and sidebar:** unread DM buttons flash then rest orange. Sidebar rows with unread are bold with a count. The tray notification icon shows the total.
7. **Presence:** friends already online show as such. Anyone who signed on while you were away appears in the dialog ("Alex was online 20 min ago") rather than as a balloon.

#### Rules

- Never show the dialog twice in one session, and never more than once per 30-minute gap.
- No numbers shown unless they're true. No fake or inflated counts.
- "Don't show again" is respected permanently, with a Settings toggle to restore it.

#### Placeholder behavior before notifications exist

The tray notification icon opens a small XP-styled list built from the live counts above. It has no persistence, and each row links to its source. A "nothing new" state shows a calm message. Later, the list is backed by a table without changing the UI.

---

### 8. Order of implementation (impact ÷ effort)

#ChangeImpactEffortNotes1**Foundation:** consolidate CSS, add `motion.css` tokens, `:active` plus `:focus-visible` plus global reduced-motionHighLowEverything else builds on it2**Silent-action fixes:** flash-to-toast bridge, replace all `alert()`, fix `this.form.submit()` selects, make friend actions update their UIVery highLowFixes most of section 1b3**Optimistic like, comment, reaction, friend request** with rollback, plus like pulseHighLow–MedReuses the chat pending pattern4**Modal, popover and menu open/close animation** via a shared helperMediumLowUses the `toast.js` confirm as the style reference5**Sidebar saved-view flash fix** and browser-title unread counterMediumLowCheap and noticeable6**Defer or stale-first the profile music widgets**, plus skeleton and marqueeHighMediumRemoves the biggest wait7**Taskbar and tray** (clock, speaker, presence, notification list)HighMed–HighAnchor concept for A, B, C8**Sound scheme** (opt-in, original sounds)MediumLow–MedNeeds the tray toggle from #79**Music widget interaction layers** (Rolodex flip, cassette, vinyl peek)Medium–HighMediumShowpieces, not blockers10**Balloon tips, flashing taskbar buttons, unread dividers, "N new posts" bar**HighMediumNeeds a counts endpoint and polling hooks11**Welcome-back dialog and visit baseline**HighHighNeeds the baseline column and queries12**Buddy-list sign-on events, typing indicator, live theme swap**MediumMed–HighDepends on realtime or extra endpoints13**Guest-page window chrome** (working minimize/maximize, live clock)Low–MediumLowA nice, cheap first impression, so it can slot in early

Suggested phasing: 1–5 are one polish pass and pay off immediately. 6–8 form the second pass, which gives the app a "place." 9–13 are the delight layer.

---

### 9. Anti-patterns to avoid

**Modern-app patterns we should refuse:**

- **Confetti, particle bursts, and heart explosions** on like or post.
- **Shimmer skeletons** (gradient sweeps). Use flat blocks plus the stepped marquee.
- **Springy or bouncy UI chrome.** Overshoot is reserved for physical widget objects.
- **Page and route transitions** (slide, fade between pages), parallax, and scroll-jacking.
- **Blur and glassmorphism** for new components. (`.mode-toggle` already uses `backdrop-filter`. Treat that as the exception, not the template.)
- **Pill buttons, pill toasts, and animated gradient borders.** These are already on the README's anti-pattern list.
- **iMessage-style bouncing typing dots.** Use a text status line.
- **Rolling count-up numbers** on stats. Use instant text, or stepped odometer digits in widgets.
- **Pull-to-refresh, swipe gestures, and haptic metaphors.**
- **Infinite scroll with fade-in per item.**
- **Streaks, daily-login rewards, "you'll lose…" copy, and inflated unread badges.** This is a hangout, not a retention funnel.

**Era-wrong behaviors:**

- Autoplay or hover sounds. Sound is opt-in, outcome-only, and controllable from the tray.
- Copying real Windows, AIM or MSN sound files or logos.
- Any `transition: all` or animation over 320ms for one-shot UI.
- Emoji icons (SVG only, per `docs/design-principles.md`).
- Native `alert()` in new code. Use `Toast` or the confirm dialog.
- Hardcoded hex in Blade templates (`music.blade.php`, inline `style=""` throughout). New work should use tokens.
- Idle ambient animation. If it isn't hovered, selected, playing or unread, it shouldn't move.