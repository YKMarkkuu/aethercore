## Theme Design Spec

**Status:** design only. No code was written or changed.

**Scope:** how AetherCore themes work, and the first theme to build (a rebuild of the legacy `midnight.css`).

**Read before the spec:** a ~40-line theme file only works if components read tokens instead of hex. Today many don’t (section 4b). That prerequisite is mostly mechanical find-and-replace, but it is the real cost of this work.

---

### 1. What is a theme in AetherCore?

**A theme is a re-lighting of the same desktop, not a recolor of the same page.** Concretely:

- It is one stylesheet, `public/css/themes/{slug}.css`, loaded after `settings.css`. It contains a single `:root { … }` block of token overrides plus `color-scheme`.
- It contains **no selectors, no `!important`, no component rules.** If a theme needs a selector to look right, the component has a token gap, and the fix goes in the component.

Changes per themeNever changes per themeThe surface ramp (`--surface-*`)Layout, geometry, spacing, type (`'Segoe UI'`…)The border ramp (`--border-*`)Motion tokens (`--motion-*`, `--ease-*`) in `motion.css`The text ramp (`--text-*`)**Physical materials**: wood, Bakelite, chrome, cardstock, sticky noteAccent *text/fill* valuesThe semantic hue of statuses (green / amber / red / gray)Status text and tint valuesSelection identity: OS-blue selection with white textBevel highlight strength, `color-scheme`Iconography, copy, bevel *shape*

**Why it isn’t “just changing a color.”** A theme must preserve relationships, not individual values:

1. **Elevation order.** Inputs are wells, cards are raised, `--surface-secondary` is always darker than `--surface-primary` (button and header gradients depend on it), and `--border-dark` is the darkest border.
2. **Pairing contrast.** Every `--text-*` token reaches 4.5:1 on every `--surface-*` it is used on. `--text-faint` is the exception and only needs 3:1.
3. **Hover direction.** Every hover goes toward `--surface-hover`.
4. **Status identity.** Online, idle, DND and offline stay distinguishable by hue and luminance.

A flat `--surface-post: dark` breaks all four silently.

---

### 2. The material problem

**Recommendation: (a) with a pre-wired escape valve.** Materials are theme-invariant objects. The chrome around them and the readouts attached to them are themed.

**The key finding is that the three widgets have two layers.**

- **Object layer (material, invariant):** wood stage and cardstock sleeves; Bakelite deck, shell and sticky note; chrome frame and cream index cards. Paint and ink are hardcoded or widget-local.
- **Readout layer (themed):** vinyl spine, the cassette J-card tracklist, panel header and body, scrollbars. These already use `--surface-post`, `--text-*` and `--border-*`.

**Why (a):**

- Wood and Bakelite are already dark. The vinyl stage runs `#2a2217` to `#45392a`, and the deck is `#69452f` to `#3b2519`. Only the paper (sleeves, cards, labels) is light.
- Paper is paper. A cream card on a dark desk reads as an object under a lamp, which is better than a “dark card.” It is also warmer than the cool chrome around it, so the music panels become the focal points of a dark profile page.
- (b) re-tinting means every material needs a per-theme variant, which doubles work with each theme. (c) rebuilding means rewriting hundreds of hand-tuned lines per widget and surrenders the signature details (reels, stamp, sleeve lean).

**The real bug class is theme tokens used as paint or ink on a material.** Known cases:

- `.cass-label` uses `var(--surface-post)` as the top of its paper gradient, with hardcoded dark text. In a dark theme that becomes dark-on-dark.
- `.rx-active` and `.rx-peek` set `color: var(--text-primary)` on hardcoded cream (`--rx-card`). Light text on cream is unreadable. `.rx-scrobbles` has the same problem with `--text-muted`.
- `.rx-peek` and `.rx-active` borders use `--border-default` on paper.

**Rule:** a material owns its paint and its ink. Each widget scope defines local variables (paper, ink, ink-muted, paper-edge) **pinned to light-theme values**, not aliased to tokens. Themed tokens appear only in the readout layer.

**The escape valve (b-lite):** those local variables are the only knobs. If the cream cards glare in Noir, dim paper by at most ~8% in the theme file by overriding the widget variables. Do not do this up front. Decide after the visual test (section 8).

---

### 3. Recommended first theme: Noir (dark)

**Why dark first:**

- It is the strongest stress test. If the token contract survives a full luminance inversion, every later theme (Silver, Olive) is cheap.
- Night use is the real demand, and it replaces the existing `midnight`.
- It exposes every hardcoded hex immediately, which makes it the best driver for the coverage pass.

**Vibe:** blue-black chrome, like a CRT desk at night, with warm objects glowing in it. Cool chrome plus warm wood, Bakelite and paper is what makes the widgets pop.

**XP fit: yes, with discipline.** Windows XP Media Center shipped a real dark scheme, Royale Noir: black chrome, blue accents, glossy bevels. Noir is inspired by it. Avoid using Microsoft’s scheme name and assets, the same logic as the “no copied sound files” rule in the delight spec.

**Not a fit for this slot:**

- *Daylight* is nearly identical to the default theme.
- *Retro Terminal* implies rebuilt materials (option c) and is out of scope. Recommend removing `daylight` and `retro` from `getAvailableThemes()` until they exist, because the stylesheet 404s today.

---

### 4. Token values for Noir

Contrast figures are approximate WCAG ratios for the stated pairing.

#### Surfaces

TokenLight**Noir**Rationale`--surface-primary``#f0edd8``#2c3038`Card/window face. Blue-black, not warm, so warmth lives only in materials.`--surface-secondary``#d4d0c8``#1b1e24`Page background and the dark end of every gradient. Must stay darker than primary.`--surface-panel-header``#ece9d8``#3a404b`Title strip. Lighter, so the header-to-secondary gradient keeps top-lit bevels.`--surface-hover``#e0dcd0``#3f4652`Hover lifts in dark. Component hovers must point here (see 4b).`--surface-input``#ffffff``#12151a`Text-field and progress-trough well. Darkest value, the inset-well logic.`--surface-post``#f8f5ec``#343942`Raised content inside cards: posts, J-card, vinyl spine.`--surface-sunken``#e8e4dc``#22262d`Scrollbar tracks, thumbnail backing. Between secondary and primary.`--info-note-bg``#4a5a7a``#a9b8d6`Misnamed: it is used as a *text* color (status notes). Light blue-gray, about 6:1 on primary. Recommend renaming to `--info-note-text`.

#### Borders

TokenLight**Noir**Rationale`--border-default``#b0a8a0``#4b525f`Primary panel outline, visible against primary.`--border-muted``#c0b8b0``#404651`Second-tier lines, popup dividers.`--border-light``#d0c8c0``#373c46`Section dividers inside cards.`--border-subtle``#e0dcd0``#2f343c`Row separators, intentionally faint.`--border-dark``#a09890``#0e1115`Shadow-side bevel edge. Must stay the darkest border.

#### Text

TokenLight**Noir**Rationale`--text-primary``#1e1e1e``#e4e7ec`About 10:1 on post, 11:1 on primary. Slightly off-white to avoid glare.`--text-muted``#6a6a6a``#a3a9b4`About 5.5:1 on primary.`--text-faint``#9a9488``#8a909b`Decorative tier (timestamps). About 3.4:1 on post, 4:1 on primary.`--text-on-accent``#ffffff``#ffffff`Unchanged: white on blue is the selection identity.

#### Accent

TokenLight**Noir**Rationale`--accent``#3a7bd5``#3a7bd5`Unchanged: OS-blue selection, fills, active states.`--accent-light``#245edb``#1f4690`Top of the topbar and guest gradients. Chrome goes deep navy.`--accent-dark``#1a4a9e``#14367a`**Fill/gradient role only.** Also the ink color on cream paper (rolodex stamp, `.rx-ink-dark`), so it must stay deep.`--accent-hover-strong``#2f68b8``#2f68b8`Unchanged: deeper on hover is the XP behavior, and white on it is about 5.7:1.

**Why `--accent-dark` can’t also be the text color:** it is currently used as link and name text (chat usernames, stat numbers, `.post-user:hover`) and as a gradient end behind white text. In Noir those two roles need opposite luminance. See the new token `--accent-text` in 4b.

#### Statuses (semantic tints)

TokenLight**Noir**Rationale`--success-bg``#d4e8d4``#1f3a26`Dark green tint.`--success-border``#8ab88a``#3f7a4c``--success-text``#1e4a1e``#8fdc9f`About 8:1 on its bg.`--warning-bg``#f0e8c8``#3d3418``--warning-border``#c8b888``#b8993a`**Raised deliberately.** This is the 3px dirty-section bar in settings; it needs about 4.7:1 against primary to stay visible.`--warning-text``#6a5a2a``#e6c96a`About 7:1. Also the dirty `*` marker.`--danger-bg``#f0d8d8``#3f2124``--danger-border``#c8a0a0``#8a4249``--danger-text``#6a2a2a``#f0a0a0`About 6:1.

#### Status text (labels)

TokenLight**Noir**Rationale`--status-online``#3a8a3a``#5fd07a`About 5.8:1 on post.`--status-idle``#c9a840``#e0bd4a`Amber stays amber.`--status-dnd``#c04040``#f27373`About 4.4:1; red text is the hardest to lift.`--status-offline``#888888``#8e95a1`Cool gray. Note `.post-time` borrows this token for timestamps.

#### Status indicators (dots)

TokenLight**Noir**Rationale`--status-online-indicator``#4ade80``#4ade80`Already dark-friendly; the legacy file reused the same value.`--status-idle-indicator``#fbbf24``#fbbf24`Same.`--status-dnd-indicator``#ef4444``#ef4444`Same.`--status-offline-indicator``#6b7280``#7d8594`Bumped to about 3.5:1 so offline is not lost against dark surfaces.

Declare `color-scheme: dark` in the same `:root` block so native controls, selects and default scrollbars follow.

#### 4b. Prerequisite: token coverage pass (“Phase 0”)

The theme file stays tiny only if these are fixed first. Most are exact-value substitutions.

**Mechanical substitutions (no new tokens):**

- `base.css` hardcodes that already equal tokens:

- topbar gradient `#245edb` / `#3a7bd5` / `#1a4a9e` equals `--accent-light` / `--accent` / `--accent-dark`.
- sidebar and right-sidebar gradient `#ece9d8` → `#d4d0c8` equals `--surface-panel-header` → `--surface-secondary`.
- `.main-body` `#d4d0c8` equals `--surface-secondary`.
- mini-profile and now-playing gradients equal primary → secondary.
- `.status-dot` ring `#ece9d8` should be `--surface-panel-header`. A bright ring on dark chrome is wrong.
- `components.css`: `.chat-away-banner` `#fff6d9` to `--warning-bg`, `.settings-btn-danger` and `.delete-btn` fills to danger tokens, scrollbar thumbs `#c8c0b8` to `--border-muted`.
- **`toast.js` injected styles** and the **confirm dialog** are fully hardcoded light. They can reference `var(--…)` in the injected CSS.
- **Hover audit:** hover rules that point at `--surface-primary` or `--surface-secondary` (`.post-item:hover`, `.settings-nav-item:hover`, `.status-option:hover`, `.msg-reaction-pill:hover`, `.xp-friend-badge:hover`) must point at `--surface-hover`. Otherwise Noir inverts hover direction on those elements.
- **Text-role audit:** any `color: var(--accent)` or `var(--accent-dark)` on a themed surface (chat usernames, `.post-user:hover`, `.stat-number`, `.msg-reply-preview-author`, `.vinyl-spine-count strong`, settings sublink active, `.rx` readouts if any) switches to `--accent-text`.
- **Inline Blade hex** (member rows, roles tab, top-friends rows, etc.) breaks `docs/design-principles.md` already. Convert as encountered. A full sweep is the long tail, not a blocker for shipping.

**New tokens (four):**

New tokenLightNoirWhy`--accent-text``#1a4a9e``#8db8ff`Link/name/number text on themed surfaces, and the `:focus-visible` ring color. About 6.5:1 on primary.`--surface-content``#ffffff``#20242a``.main-content` is hardcoded white and is semantically different from `--surface-input`.`--surface-accent-tint``#d8e6fb``#1f3558`“Mine” reaction pills, reply composer bar, accent hover wash.`--bevel-hi``#ffffff``rgba(255,255,255,0.08)`XP bevel highlights are hardcoded `#fff` in scrollbars, J-card and spine insets. White bevels are glaring on dark.

---

### 5. Widget treatment

**All three keep their materials.** What changes is the readout layer and the places where a theme token was leaking onto a material.

**Vinyl shelf (wood)**

- **Stays:** stage and wood gradient, grain, bar, sleeves, plates, badge, drop shadows.
- **Themed (already tokenized):** the spine readout (`--surface-post`, `--text-primary`, `--text-muted`, `--border-default`), scrollbar (`--surface-sunken`, `--surface-primary`, `--border-default`).
- **Fix:**

- Spine progress trough is hardcoded `#fff`, so it should be `--surface-input`. The green XP block fill stays invariant.
- `.vinyl-spine-count strong` moves to `--accent-text`.
- Inset `#fff` bevels on the scrollbar thumb and spine move to `--bevel-hi`.
- **Watch:** the stage’s dark-brown border on a dark panel body. If the shelf edge disappears, add a 1px catch-light on the stage edge using `--bevel-hi`, and nothing else.

**Cassette deck (Bakelite)**

- **Stays:** deck, slot, shell, screws, reels, window, sticky note (yellow paper), label paper.
- **Fix (real bug):** `.cass-label` must own its paper. Give it a widget-local `--cass-paper` pinned to the light values instead of `--surface-post`. Its text is already hardcoded dark.
- **Themed:** the J-card tracklist, which is a readout. Rows, alternating tint, and selection (accent fill, white text) all work via tokens.
- **Fix:** `.cass-track:focus-visible` outline moves from `--accent-dark` to `--accent-text`. On the J-card, deep blue is invisible on a dark surface.

**Rolodex (chrome)**

- **Stays:** frame, rails, bottom edge, cream cards, blue ink (`.rx-active::before`, `.rx-tally`, `.rx-stamp`).
- **Fix (real bug):** introduce widget-local pinned inks (e.g. card ink `#1e1e1e`, card ink-muted `#6a6a6a`, card edge `#b0a8a0`) and stop reading `--text-primary`, `--text-muted` and `--border-default` on cards.
- **Stamp on paper:** `.rx-stamp` uses `--accent-dark`, which stays deep navy in Noir. That works on cream. This is why `--accent-text` is a new token and `--accent-dark` is not lightened.
- **Focus:** `.rx-peek:focus-visible` keeps `--accent-dark` (it sits on cream and gray chrome). That is the one place the ring is deliberately dark.
- **Contingency:** if the large active cream card glares at night, dim `--rx-card*` by at most ~8% in `noir.css`. Do not tint, only dim.

*Dead partial:* `top-artists-phone.blade.php` is not included anywhere. Don’t spend theme effort on it.

---

### 6. What to delete

- **Confirm:** `public/css/themes/midnight.css` is **rebuilt from scratch as a `:root` token block**, not migrated. Its 638 lines are `!important` overrides keyed to class names, which is the architecture this spec replaces.
- Visible symptoms of why migration is wrong: status colors defined three times, the same `.xp-panel-header` background in two blocks, and a purple `#6C63FF` accent for focus and active states, which breaks the project’s “no purple” rule.
- Also remove `daylight` and `retro` from `getAvailableThemes()` until a file exists for each. A 404 stylesheet silently falls back to the default while the settings page claims the theme is active.
- Delete the old file after the new one ships. No compatibility layer.

---

### 7. What NOT to do

- **No `!important`, no selectors in a theme file.** A theme that needs either has a token gap.
- **No pure black or pure white.** `#000` backgrounds and `#fff` text are not XP and cause glare.
- **No neutral-gray “modern dark mode” ramp.** Cool blue-black chrome is deliberate; flat `#121212` loses the era.
- **No purple, no neon glow, no gradient borders.** The legacy file’s `#6C63FF` is exactly the failure mode.
- **No `filter: invert()` or `brightness()` on widgets** to fake dark. Physical objects don’t invert.
- **No re-tinting materials to “match” the theme.** Paper does not become dark paper.
- **No shadow removal.** The legacy file stripped `box-shadow` on cards. XP depth depends on bevels and drop shadows; dark themes need them more, not less.
- **No dimmed status colors.** Online green and DND red must stay saturated enough to read as status.
- **No per-theme motion.** Durations and easing are invariant; a theme that “feels snappier” is a bug.
- **No white bevel highlights at full strength** on dark chrome (use `--bevel-hi`).
- **No theme-specific component CSS** and no emoji icons (SVG only, per `docs/design-principles.md`).

---

### 8. Test checklist

Check each in the light theme first, so regressions from the Phase 0 refactor are separated from Noir issues.

**Materials**

- Vinyl: wood stage reads as wood, sleeves still cream, plate text legible, spine trough dark, green fill visible.
- Vinyl: shelf edge visible against the panel body.
- Cassette: deck reads as Bakelite, label paper is paper (not dark-on-dark), sticky note yellow, reels and glass intact.
- Rolodex: chrome frame intact; every card text (name, count, scrobbles) is dark on cream; stamp and tally blue ink readable.
- Cream cards do not glare. If they do, apply the ≤8% dim and recheck.

**Readability**

- `--text-primary` and `--text-muted` meet 4.5:1 on every surface they sit on (primary, post, input, sunken, content, panel-header, hover). `--text-faint` meets 3:1.
- Links, usernames and numbers use `--accent-text` and are readable everywhere.
- Inputs: typed text, placeholder, and caret are visible; native selects, checkboxes and scrollbars follow `color-scheme`.

**Selection and interaction**

- Accent-blue selection pops with white text: settings nav, J-card selected row, vinyl selected plate, rolodex active peek, mode toggle.
- Every hover lifts toward `--surface-hover` (spot-check: post card, nav items, status menu, reaction pills).
- `:focus-visible` rings are visible on dark surfaces and on paper (dark ring on paper, light ring on dark).

**Status**

- Online, idle, DND and offline read distinctly in grayscale (shape or label redundancy, not hue alone).
- Dot rings blend into the sidebar, not a bright halo.
- Success, warning and danger tints are legible in toasts, alerts and the confirm dialog.

**Chrome**

- Modals, popovers (user popover, status menu, profile popup) and the XP confirm dialog are fully themed, with no leftover light panels.
- Topbar, sidebars, window title bars and settings save bar are themed.
- Settings dirty markers are visible: the 3px warning bar on dirty sections, the `*` on sublinks and categories, the bold unsaved-count status text.

**Invariants**

- `motion.css` is untouched and no `--motion-*` or `--ease-*` is redeclared in the theme file.
- `prefers-reduced-motion` behavior is identical to the light theme.
- Live swap from the settings save bar works both ways (light to Noir to light) without a reload and without a flash of wrong content.
- Guest pages and the admin layout are unaffected. Neither loads a theme, and that is a deliberate scope limit.

---

### 9. Naming

**Recommend renaming `midnight` to `noir`** (display label “Noir (Dark)”, matching the current `'Midnight (Dark)'` pattern).

- It is era-appropriate and echoes XP’s real Royale Noir scheme without using Microsoft’s name.
- “Midnight” is the generic modern-dark-mode name, and the file is being rebuilt from scratch anyway, so the slug can change cleanly.
- **Cost of renaming:** one data migration (`users.theme = 'noir' WHERE theme = 'midnight'`), updating `getAvailableThemes()`, and updating `SettingsBulkSaveTest`, which asserts `'midnight'`.
- **Cheaper alternative:** keep the slug `midnight`, change only the label. Fine if you’d rather avoid the migration, but the slug and label then disagree forever.