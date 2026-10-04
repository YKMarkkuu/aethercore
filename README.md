# AetherCore

> **The core of your digital world.**

A social platform that combines the community structure of Discord, the personal expression of MySpace, and the music-sharing of Spotify — wrapped in the skeuomorphic Windows XP / early-2000s internet aesthetic.

Built as a passion project. Not a startup. Not a product. A place on the internet that feels like it remembers what the web used to be.

---

## What it is

AetherCore is a full-stack social platform organized around six pillars:

| Pillar | Description | Status |
|---|---|---|
| **AetherSpaces** | Discord-style community servers with roles, permissions, channels, and moderation | ✅ Shipped |
| **Profiles** | Customizable profile pages with Top 8 music stats | 🟡 In progress |
| **Chat** | Direct messages with reactions, presence, edits, replies, and ex-friend persistence | ✅ Shipped |
| **Feed** | Posts with likes, comments, shares, and privacy-aware visibility | ✅ Shipped |
| **Privacy** | Visibility controls, DM permissions, status sharing, audited across 12+ UI surfaces | ✅ Shipped |
| **AetherTunes** | Last.fm integration with Deezer image fallbacks and cached chart data | ✅ Shipped |

**Roadmap:** Mobile responsive, notifications, user-selectable themes (light / midnight).

---

## Design philosophy

This project has opinions about how the web should look.

**The aesthetic:** Windows XP / early-2000s internet. Not "modern SaaS with a retro font." Not "vibe-coded pastel gradients." Actual era-correct skeuomorphic UI — warm materials, physical objects, soft shadows, handwritten annotations.

**Anti-patterns we actively avoid:**

- Rounded cards for the sake of rounded cards
- Purple gradients
- Pill buttons
- Centered hero text
- Emoji as icons (SVG only)
- Default scrollbars
- Tailwind classes (this project doesn't use Tailwind)

**Design principles:**

- **Physical objects, not flat panels.** The music widgets are a vinyl shelf, a cassette deck, and a Rolodex — not "cards with data."
- **One signature per component.** Each widget has exactly one memorable detail (vinyl shelf → progress bar, cassette → reel window, Rolodex → blue ink tally).
- **Design tokens.** Colors, borders, and text values are defined as CSS custom properties in `public/css/base.css`. New code uses tokens, not hardcoded values.
- **Warm structure, neutral content, blue accents.** Warm materials (wood, Bakelite, chrome) frame neutral cream surfaces. Selection states use OS blue, not decoration colors.

---

## Stack

- **Backend:** Laravel 10 on PHP 8.1
- **Database:** MySQL (`aethercore`)
- **Frontend:** Blade templates + vanilla CSS + vanilla JavaScript
- **Real-time:** Laravel Reverb
- **No build step.** No Tailwind. No React. No Vue. No Vite. Just HTML, CSS, and JS served directly.

This is deliberate. The aesthetic demands hand-written CSS. The build-free approach keeps the codebase approachable and the deploy simple.

---

## Local setup

**Prerequisites:** PHP 8.1+, Composer, MySQL, and a local dev environment (Laragon recommended on Windows).

```bash
git clone https://github.com/YKMarkkuu/aethercore.git
cd aethercore

composer install

cp .env.example .env
php artisan key:generate

# Configure database in .env
# DB_DATABASE=aethercore
# DB_USERNAME=root
# DB_PASSWORD=

php artisan migrate

php artisan serve
```

Visit `http://localhost:8000`.

**Real-time (optional):**

```bash
php artisan reverb:start
```

**Mail (development):** The project uses `MAIL_MAILER=log` by default — emails are written to `storage/logs/laravel.log` instead of being sent. To test the password reset flow, check the log for the reset URL. For production, configure a real SMTP provider (Resend, Postmark, SES).

---

## Project structure

```text
app/
  Http/Controllers/       ProfileController, PostController, SpaceController, SettingsController, ...
  Models/                 User, Space, Post, Message, Conversation, Profile
  Services/               LastfmService (charts, Deezer fallback, caching)
resources/
  views/
    partials/profile/     Top 8 music widgets (vinyl shelf, cassette deck, Rolodex)
    layouts/              app.blade.php (authenticated), guest.blade.php (auth pages)
    auth/                 Login, register, password reset, email verification
public/
  css/                    base.css (tokens), components.css, app.css, utilities.css
  js/                     ajax-forms.js, post-interactions.js, chat-thread.js, presence.js
tests/
  Feature/                Auth, profile, space permissions, privacy
  Unit/                   Last.fm service
```

---

## Key conventions

- **CSS is scoped per component.** Widgets use a prefix (`.vinyl-shelf`, `.cassette-deck`, `.rolodex`) so styles don't leak between components.
- **JavaScript is IIFE-based.** No global state. Each widget initializes itself.
- **Privacy is enforced server-side.** `canViewProfileOf()` and `canSeeStatusOf()` gate every surface that exposes another user's data.
- **External API calls are cached.** [Last.fm](https://last.fm/) chart data caches for 3 hours (2 minutes on empty results, 7 days of stale fallback for outages). Failures log with context.
- **Design tokens are the source of truth.** New CSS uses `var(--token)`, not hardcoded values.

---

## Status
**Early development. Not deployed. Local development only.**

Contributions aren't open — this is a solo passion project. The repo is public so the process is visible.

---

## License
MIT
