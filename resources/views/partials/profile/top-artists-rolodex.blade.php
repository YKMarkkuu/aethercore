{{--
    TOP 8 ARTISTS - ROLODEX

    Expects: $artists = [ ['name' =>, 'image' =>, 'playcount' =>], ... ]
    Usage:   @include('partials.profile.top-artists-rolodex', ['artists' => $user->lastfm_data['top_artists'] ?? []])

    Self-contained: CSS is scoped under .rolodex, JS is an IIFE keyed
    to a per-render id. Desktop 1990s office-era skeuomorphic card file:
    cool brushed chrome frame, cream index cards, OS-blue ink details.
--}}
@php
    $rid = 'rolodex-' . \Illuminate\Support\Str::random(6);
    $artistList = collect($artists ?? [])->take(8)->values();
    $count = $artistList->count();

    // Same placeholder detection LastfmService already applies before
    // falling back to Deezer, kept local so this partial has no
    // dependency on that class.
    $isRealImage = function ($url) {
        if (empty($url)) {
            return false;
        }

        foreach (['2a96cbd8b46e442fc41c2b86b821562f', 'avatar_default', 'default_avatar', 'noimage', 'placeholder'] as $needle) {
            if (str_contains($url, $needle)) {
                return false;
            }
        }

        return true;
    };

    $compactPlaycount = function ($plays) {
        $plays = (int) $plays;

        if ($plays >= 1000000) {
            return rtrim(rtrim(number_format($plays / 1000000, 1), '0'), '.') . 'm';
        }

        if ($plays >= 1000) {
            return rtrim(rtrim(number_format($plays / 1000, 1), '0'), '.') . 'k';
        }

        return number_format($plays);
    };
@endphp

<style>
    /* ================= ROLODEX ================= */
    .rolodex [hidden] { display: none !important; }

    .rolodex {
        --rx-frame-hi: #9a9a9a;
        --rx-frame: #6d6d6d;
        --rx-frame-mid: #7e7e7e;
        --rx-frame-lo: #3f3f3f;
        --rx-frame-deep: #2d2d2d;
        --rx-frame-shadow: rgba(0, 0, 0, 0.4);
        --rx-card: #e8e4dc;
        --rx-card-hi: #f8f5ec;
        --rx-card-lo: #d9d4c9;
        --rx-ink: var(--accent, #3a7bd5);
        --rx-ink-dark: var(--accent-dark, #1a4a9e);
        --rx-step: 22px;
        --rx-peek-origin: 16px;
        --rx-card-height: 22px;
        --rx-active-height: 132px;
    }

    .rolodex .rx-header {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .rolodex .rx-header svg {
        flex-shrink: 0;
    }

    .rolodex .rx-empty {
        padding: 0.6rem 0;
        color: var(--text-muted);
        font: 0.75rem/1.35 Tahoma, 'Segoe UI', Verdana, sans-serif;
    }

    .rolodex .rx-stage {
        position: relative;
        width: 100%;
        height: 326px;
        padding: 0;
    }

    /* ================= METAL FRAME ================= */
    .rolodex .rx-frame {
        position: absolute;
        inset: 0;
        overflow: hidden;
        border: 1px solid #3a3a3a;
        border-radius: 5px;
        background:
            repeating-linear-gradient(
                0deg,
                rgba(255,255,255,0.075) 0,
                rgba(255,255,255,0.075) 1px,
                rgba(0,0,0,0.035) 1px,
                rgba(0,0,0,0.035) 3px
            ),
            linear-gradient(
                180deg,
                var(--rx-frame-hi) 0%,
                var(--rx-frame-mid) 17%,
                var(--rx-frame) 64%,
                var(--rx-frame-lo) 100%
            );
        box-shadow:
            inset 1px 1px 0 rgba(255,255,255,0.35),
            inset -1px -1px 0 rgba(0,0,0,0.36),
            2px 5px 9px var(--rx-frame-shadow);
    }

    .rolodex .rx-top-rail {
        position: absolute;
        z-index: 8;
        top: 5px;
        left: 8px;
        right: 8px;
        height: 10px;
        border: 1px solid #4a4a4a;
        border-radius: 2px;
        background:
            linear-gradient(180deg, #b5b5b5 0%, #8c8c8c 42%, #616161 100%);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.48),
            inset 0 -1px 0 rgba(0,0,0,0.36),
            0 1px 2px rgba(0,0,0,0.3);
    }

    .rolodex .rx-top-rail::after {
        content: "";
        position: absolute;
        top: 2px;
        left: 3px;
        right: 3px;
        height: 1px;
        background: rgba(255,255,255,0.52);
    }

    .rolodex .rx-side-rail {
        position: absolute;
        z-index: 9;
        top: 11px;
        bottom: 14px;
        width: 4px;
        background: linear-gradient(90deg, #3a3a3a 0%, #555555 48%, #2d2d2d 100%);
        box-shadow: inset 1px 0 0 rgba(255,255,255,0.16);
    }

    .rolodex .rx-side-rail--left { left: 0; }
    .rolodex .rx-side-rail--right { right: 0; }

    .rolodex .rx-bottom-edge {
        position: absolute;
        z-index: 9;
        left: 0;
        right: 0;
        bottom: 0;
        height: 14px;
        border-top: 1px solid #b0b0b0;
        background:
            repeating-linear-gradient(
                0deg,
                rgba(255,255,255,0.06) 0,
                rgba(255,255,255,0.06) 1px,
                transparent 1px,
                transparent 3px
            ),
            linear-gradient(180deg, #666666 0%, #474747 52%, #333333 100%);
        box-shadow:
            inset 0 1px 0 rgba(255,255,255,0.18),
            0 3px 4px rgba(0,0,0,0.3);
    }

    .rolodex .rx-bottom-edge::before {
        content: "";
        position: absolute;
        top: 3px;
        left: 22%;
        right: 22%;
        height: 2px;
        border-radius: 1px;
        background: #2f2f2f;
        box-shadow: inset 0 1px 0 rgba(0,0,0,0.5);
    }

    /* ================= PEEK STACK =================
       Rank is the stable position key. JS computes a compact slot from
       rank order (highest rank first at top) while excluding active rank.
       The actual position is always calculated from the slot variable.
    */
    .rolodex .rx-peek-stack {
        position: absolute;
        z-index: 4;
        top: 0;
        left: 8px;
        right: 8px;
        height: 155px;
    }

    .rolodex .rx-peek {
        position: absolute;
        left: 0;
        right: 0;
        top: calc(var(--rx-peek-origin) + (var(--peek-slot) * var(--rx-step)));
        height: var(--rx-card-height);
        padding: 0 7px 0 6px;
        border: 1px solid var(--border-default, #b0a8a0);
        border-radius: 3px;
        background:
            linear-gradient(180deg, rgba(255,255,255,0.25), transparent 42%),
            var(--rx-card);
        color: var(--text-primary, #1e1e1e);
        box-shadow:
            1px 2px 2px rgba(0,0,0,0.2),
            0 1px 0 rgba(255,255,255,0.5) inset;
        display: grid;
        grid-template-columns: 27px minmax(0, max-content) minmax(12px, 1fr) auto;
        align-items: center;
        gap: 4px;
        cursor: pointer;
        text-align: left;
        transform: translateY(0) scale(1);
        transform-origin: 50% 50%;
        transition:
            transform 220ms ease-out,
            opacity 160ms ease-out,
            box-shadow 220ms ease-out,
            top 160ms ease-out;
    }

    .rolodex .rx-peek::after {
        content: "";
        position: absolute;
        left: 7px;
        right: 7px;
        bottom: -2px;
        height: 1px;
        background: rgba(55,55,55,0.35);
        pointer-events: none;
    }

    .rolodex .rx-peek-rank {
        font: 700 0.66rem/1 'Courier New', Courier, monospace;
        color: #3a3a3a;
        letter-spacing: 0.2px;
    }

    .rolodex .rx-peek-name {
        min-width: 0;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        font: 700 0.68rem/1.05 Tahoma, 'Segoe UI', Verdana, sans-serif;
    }

    .rolodex .rx-leader {
        min-width: 10px;
        border-top: 1px dotted #888;
        transform: translateY(1px);
    }

    .rolodex .rx-peek-count {
        font: 700 0.62rem/1 'Courier New', Courier, monospace;
        color: #4d4d4d;
        white-space: nowrap;
    }

    .rolodex .rx-peek:focus-visible {
        z-index: 20;
        outline: 2px dotted var(--accent-dark);
        outline-offset: 2px;
    }

    .rolodex .rx-peek[disabled] {
        pointer-events: none;
    }

    @media (hover: hover) {
        .rolodex .rx-peek:hover:not([disabled]) {
            border-color: var(--accent);
            box-shadow:
                0 2px 4px rgba(0,0,0,0.25),
                0 0 0 1px rgba(58,123,213,0.14),
                inset 0 1px 0 rgba(255,255,255,0.5);
        }
    }

    .rolodex .rx-peek.is-incoming {
        z-index: 18;
        transform:
            translateY(var(--rx-forward-y, 0px))
            scale(1.035);
        box-shadow:
            2px 7px 9px rgba(0,0,0,0.3),
            0 0 0 1px rgba(58,123,213,0.16);
    }

    .rolodex .rx-peek.is-outgoing-target {
        z-index: 6;
    }

    /* ================= ACTIVE CARD ================= */
    .rolodex .rx-active {
        position: absolute;
        z-index: 15;
        left: 10px;
        right: 10px;
        bottom: 17px;
        height: var(--rx-active-height);
        padding: 7px;
        border: 1px solid var(--border-default, #b0a8a0);
        border-radius: 4px;
        background:
            linear-gradient(180deg, rgba(255,255,255,0.25), transparent 28%),
            linear-gradient(180deg, var(--rx-card-hi) 0%, var(--rx-card) 68%, var(--rx-card-lo) 100%);
        color: var(--text-primary, #1e1e1e);
        box-shadow:
            1px 2px 0 rgba(255,255,255,0.55) inset,
            -1px -1px 0 rgba(0,0,0,0.14) inset,
            3px 5px 8px rgba(0,0,0,0.36);
        display: grid;
        grid-template-columns: 45% minmax(0, 55%);
        gap: 8px;
        cursor: pointer;
        transform: translateY(0) scale(1);
        transform-origin: 50% 100%;
        transition: transform 220ms ease-out, box-shadow 220ms ease-out;
        outline: none;
    }

    .rolodex .rx-active::before {
        content: "";
        position: absolute;
        top: 7px;
        left: 7px;
        right: 7px;
        height: 2px;
        background: var(--accent);
        opacity: 0.85;
    }

    .rolodex .rx-active:focus-visible {
        box-shadow:
            0 0 0 2px rgba(26,74,158,0.18),
            3px 5px 8px rgba(0,0,0,0.36);
    }

    .rolodex .rx-active.is-sliding-back {
        transform: translateY(var(--rx-back-y, -80px)) scale(0.985);
        box-shadow: 1px 1px 0 rgba(255,255,255,0.45) inset, 2px 5px 7px rgba(0,0,0,0.28);
    }

    @media (hover: hover) {
        .rolodex .rx-active:hover:not(.is-sliding-back) {
            box-shadow:
                1px 2px 0 rgba(255,255,255,0.55) inset,
                -1px -1px 0 rgba(0,0,0,0.14) inset,
                3px 6px 10px rgba(0,0,0,0.4);
        }
    }

    .rolodex .rx-active.is-nudged {
        transform: translateY(-2px) scale(1.006);
        box-shadow:
            1px 2px 0 rgba(255,255,255,0.55) inset,
            -1px -1px 0 rgba(0,0,0,0.14) inset,
            3px 7px 10px rgba(0,0,0,0.4);
    }

    .rolodex .rx-photo-wrap {
        position: relative;
        min-width: 0;
        min-height: 0;
        height: 100%;
        padding-top: 2px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .rolodex .rx-photo {
        display: block;
        width: 100%;
        height: 112px;
        object-fit: cover;
        border: 1px solid #3a3a3a;
        background: var(--rx-card);
        box-shadow:
            1px 1px 0 rgba(255,255,255,0.55),
            1px 2px 2px rgba(0,0,0,0.22);
    }

    .rolodex .rx-photo-fallback {
        display: block;
        width: 100%;
        height: 112px;
        border: 1px solid #3a3a3a;
        background: var(--rx-card);
        box-shadow:
            1px 1px 0 rgba(255,255,255,0.55),
            1px 2px 2px rgba(0,0,0,0.22);
    }

    .rolodex .rx-detail {
        position: relative;
        min-width: 0;
        height: 100%;
        padding: 8px 3px 5px 0;
        font-family: Tahoma, 'Segoe UI', Verdana, sans-serif;
    }

    .rolodex .rx-typed-header {
        min-width: 0;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        font: 700 0.56rem/1.2 'Courier New', Courier, monospace;
        letter-spacing: 0.35px;
        color: #565656;
    }

    .rolodex .rx-name {
        margin-top: 7px;
        max-height: 38px;
        overflow: hidden;
        font: 700 0.9rem/1.08 Tahoma, 'Segoe UI', Verdana, sans-serif;
        overflow-wrap: anywhere;
    }

    .rolodex .rx-scrobbles {
        margin-top: 4px;
        font: 0.64rem/1.2 Tahoma, 'Segoe UI', Verdana, sans-serif;
        color: var(--text-muted);
    }

    .rolodex .rx-tally {
        position: absolute;
        left: 0;
        bottom: 5px;
        color: var(--accent);
        font: 700 0.82rem/1 'Comic Sans MS', 'Segoe Print', Tahoma, cursive;
        transform: rotate(-3deg);
        transform-origin: left center;
        white-space: nowrap;
    }

    .rolodex .rx-stamp {
        position: absolute;
        right: 0;
        bottom: 2px;
        padding: 2px 5px;
        border: 1px solid var(--accent);
        color: var(--accent-dark);
        background: rgba(58,123,213,0.035);
        font: 700 0.55rem/1.15 'Courier New', Courier, monospace;
        letter-spacing: 0.4px;
        transform: rotate(-2deg);
        transform-origin: right bottom;
    }

    .rolodex .rx-announcer {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }

    @media (prefers-reduced-motion: reduce) {
        .rolodex .rx-peek,
        .rolodex .rx-active {
            transition: none;
        }
    }

    @media (max-width: 280px) {
        .rolodex .rx-stage { height: 326px; }
        .rolodex .rx-active { left: 8px; right: 8px; }
    }
</style>

<div class="xp-panel rolodex" id="{{ $rid }}">
    <div class="xp-panel-header rx-header">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="3" y="4" width="18" height="16" rx="2"/>
            <path d="M7 8h10M7 12h7M7 16h10"/>
        </svg>
        Top 8 Artists
    </div>

    <div class="xp-panel-body">
        @if($count === 0)
            <div class="rx-empty">No top artists found.</div>
        @else
            {{-- Shared portrait fallback carried over unchanged from the wall phone. --}}
            <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
                <defs>
                    <linearGradient id="{{ $rid }}-portrait-bg" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#e5d8b7"/>
                        <stop offset="1" stop-color="#b9a77f"/>
                    </linearGradient>
                    <linearGradient id="{{ $rid }}-portrait-figure" x1="0" y1="0" x2="0.9" y2="1">
                        <stop offset="0" stop-color="#777267"/>
                        <stop offset="1" stop-color="#45433e"/>
                    </linearGradient>
                </defs>
                <symbol id="{{ $rid }}-blank" viewBox="0 0 20 20">
                    <rect width="20" height="20" fill="url(#{{ $rid }}-portrait-bg)"/>
                    <circle cx="10" cy="6.4" r="3.1" fill="url(#{{ $rid }}-portrait-figure)"/>
                    <path d="M3.4 19c.45-4.05 2.65-6.55 6.6-6.55s6.15 2.5 6.6 6.55H3.4Z" fill="url(#{{ $rid }}-portrait-figure)"/>
                </symbol>
            </svg>

            <div class="rx-stage">
                <div class="rx-frame">
                    <div class="rx-top-rail" aria-hidden="true"></div>
                    <div class="rx-side-rail rx-side-rail--left" aria-hidden="true"></div>
                    <div class="rx-side-rail rx-side-rail--right" aria-hidden="true"></div>

                    <div class="rx-peek-stack" role="group" aria-label="Artist index cards">
                        @foreach($artistList as $i => $artist)
                            @php
                                $rank = $i + 1;
                                $name = trim((string) ($artist['name'] ?? '')) ?: 'Unknown Artist';
                                $plays = (int) ($artist['playcount'] ?? 0);
                                $img = $isRealImage($artist['image'] ?? null) ? $artist['image'] : null;
                            @endphp

                            <button type="button"
                                    class="rx-peek"
                                    style="--peek-rank: {{ $rank }}; --peek-slot: 0;"
                                    data-rx-peek
                                    data-rank="{{ $rank }}"
                                    data-name="{{ $name }}"
                                    data-image="{{ $img }}"
                                    data-plays="{{ $plays }}"
                                    data-plays-formatted="{{ number_format($plays) }}"
                                    data-compact="{{ $compactPlaycount($plays) }}"
                                    aria-pressed="{{ $rank === 1 ? 'true' : 'false' }}"
                                    @if($rank === 1) disabled tabindex="-1" @endif
                                    aria-label="Rank {{ str_pad($rank, 2, '0', STR_PAD_LEFT) }}: {{ $name }}, {{ number_format($plays) }} scrobbles">
                                <span class="rx-peek-rank">{{ str_pad($rank, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="rx-peek-name">{{ $name }}</span>
                                <span class="rx-leader" aria-hidden="true"></span>
                                <span class="rx-peek-count">{{ $compactPlaycount($plays) }}</span>
                            </button>
                        @endforeach
                    </div>

                    <div class="rx-active"
                         data-rx-active
                         role="region"
                         tabindex="0"
                         aria-label="Selected top artist">
                        <div class="rx-photo-wrap" data-rx-photo></div>

                        <div class="rx-detail">
                            <div class="rx-typed-header" data-rx-header></div>
                            <div class="rx-name" data-rx-name></div>
                            <div class="rx-scrobbles" data-rx-scrobbles></div>
                            <div class="rx-tally" data-rx-tally></div>
                            <div class="rx-stamp" data-rx-stamp></div>
                        </div>
                    </div>

                    <div class="rx-bottom-edge" aria-hidden="true"></div>
                </div>

                <span class="rx-announcer" data-rx-announcer aria-live="polite" aria-atomic="true"></span>
            </div>
        @endif
    </div>
</div>

@if($count > 0)
<script>
(function () {
    var root = document.getElementById(@json($rid));
    if (!root) return;

    var frame = root.querySelector('.rx-frame');
    var active = root.querySelector('[data-rx-active]');
    var announcer = root.querySelector('[data-rx-announcer]');
    var peeks = Array.prototype.slice.call(root.querySelectorAll('[data-rx-peek]'));
    var byRank = {};
    peeks.forEach(function (peek) {
        byRank[parseInt(peek.dataset.rank, 10)] = peek;
    });

    var nameEl = root.querySelector('[data-rx-name]');
    var headerEl = root.querySelector('[data-rx-header]');
    var scrobblesEl = root.querySelector('[data-rx-scrobbles]');
    var tallyEl = root.querySelector('[data-rx-tally]');
    var stampEl = root.querySelector('[data-rx-stamp]');
    var photoWrap = root.querySelector('[data-rx-photo]');
    var activeRank = 1;
    var isAnimating = false;

    var reducedMotion = window.matchMedia &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function padRank(rank) {
        return String(rank).padStart(2, '0');
    }

    function fallbackSvg() {
        return '<svg class="rx-photo-fallback" aria-hidden="true"><use href="#' +
            @json($rid) + '-blank"/></svg>';
    }

    function renderPhoto(peek) {
        photoWrap.innerHTML = '';

        if (peek.dataset.image) {
            var img = document.createElement('img');
            img.className = 'rx-photo';
            img.src = peek.dataset.image;
            img.alt = '';
            img.loading = 'lazy';
            img.addEventListener('error', function () {
                photoWrap.innerHTML = fallbackSvg();
            });
            photoWrap.appendChild(img);
        } else {
            photoWrap.innerHTML = fallbackSvg();
        }
    }

    function renderActive(peek) {
        var rank = parseInt(peek.dataset.rank, 10);
        var formatted = peek.dataset.playsFormatted;

        headerEl.textContent = 'CARD ' + padRank(rank);
        nameEl.textContent = peek.dataset.name;
        scrobblesEl.textContent = formatted + ' scrobbles';
        tallyEl.textContent = '~' + formatted;
        stampEl.textContent = 'RANK ' + padRank(rank);
        renderPhoto(peek);

        announcer.textContent =
            'Selected rank ' + padRank(rank) + ': ' +
            peek.dataset.name + ', ' + formatted + ' scrobbles.';
    }

    /*
     * Rank is the position key. The stack is rebuilt from rank values,
     * highest rank at the top, active rank omitted. No DOM-index math
     * determines a card's resting location.
     */
    function layoutPeeks(excludingRank) {
        var ranks = peeks.map(function (peek) {
            return parseInt(peek.dataset.rank, 10);
        }).sort(function (a, b) {
            return b - a;
        });

        var slot = 0;

        ranks.forEach(function (rank) {
            var peek = byRank[rank];
            var isActive = rank === excludingRank;

            peek.style.setProperty('--peek-rank', String(rank));

            if (isActive) {
                peek.hidden = true;
                peek.disabled = true;
                peek.setAttribute('aria-pressed', 'true');
                peek.tabIndex = -1;
                peek.style.setProperty('--peek-slot', '-1');
                return;
            }

            peek.hidden = false;
            peek.disabled = false;
            peek.setAttribute('aria-pressed', 'false');
            peek.tabIndex = 0;
            peek.style.setProperty('--peek-slot', String(slot));
            slot += 1;
        });
    }

    function peekTopPx(peek) {
        var slot = parseInt(peek.style.getPropertyValue('--peek-slot'), 10);
        if (isNaN(slot)) return 0;

        return 16 + (slot * 22);
    }

    function activeTopPx() {
        var frameRect = frame.getBoundingClientRect();
        var activeRect = active.getBoundingClientRect();
        return activeRect.top - frameRect.top;
    }

    function clearMotionClasses() {
        active.classList.remove('is-sliding-back');
        peeks.forEach(function (peek) {
            peek.classList.remove('is-incoming', 'is-outgoing-target');
            peek.style.removeProperty('--rx-forward-y');
        });
    }

    function nudgeActive() {
        if (reducedMotion || isAnimating) return;

        active.classList.remove('is-nudged');
        void active.offsetWidth;
        active.classList.add('is-nudged');

        window.clearTimeout(active._rxNudgeTimer);
        active._rxNudgeTimer = window.setTimeout(function () {
            active.classList.remove('is-nudged');
        }, 150);
    }

    function commitSelection(newRank, shouldFocus) {
        activeRank = newRank;
        var newPeek = byRank[newRank];

        clearMotionClasses();
        layoutPeeks(activeRank);
        renderActive(newPeek);

        if (shouldFocus) {
            active.focus();
        }
    }

    function selectRank(newRank, shouldFocus) {
        if (!byRank[newRank]) return;
        if (newRank === activeRank) {
            nudgeActive();
            if (shouldFocus) active.focus();
            return;
        }
        if (isAnimating) return;

        var oldRank = activeRank;
        var oldPeek = byRank[oldRank];
        var incoming = byRank[newRank];

        /* Layout uses ranks, not DOM order, before computing travel distances. */
        layoutPeeks(oldRank);

        var incomingTop = peekTopPx(incoming);
        var outgoingTargetTop = peekTopPx(oldPeek);
        var currentActiveTop = activeTopPx();

        isAnimating = true;

        oldPeek.hidden = false;
        oldPeek.disabled = true;
        oldPeek.setAttribute('aria-pressed', 'false');
        oldPeek.classList.add('is-outgoing-target');

        incoming.hidden = false;
        incoming.disabled = true;
        incoming.setAttribute('aria-pressed', 'true');
        incoming.style.setProperty(
            '--rx-forward-y',
            String(currentActiveTop - incomingTop) + 'px'
        );
        incoming.classList.add('is-incoming');

        active.style.setProperty(
            '--rx-back-y',
            String(outgoingTargetTop - currentActiveTop) + 'px'
        );
        active.classList.add('is-sliding-back');

        if (reducedMotion) {
            commitSelection(newRank, shouldFocus);
            isAnimating = false;
            return;
        }

        window.clearTimeout(root._rxSwapTimer);
        root._rxSwapTimer = window.setTimeout(function () {
            commitSelection(newRank, shouldFocus);
            active.style.removeProperty('--rx-back-y');
            isAnimating = false;
        }, 225);
    }

    peeks.forEach(function (peek) {
        peek.addEventListener('click', function () {
            var rank = parseInt(peek.dataset.rank, 10);
            selectRank(rank, true);
        });
    });

    active.addEventListener('click', function () {
        nudgeActive();
    });

    root.addEventListener('keydown', function (e) {
        var focusedPeek = document.activeElement &&
            document.activeElement.matches &&
            document.activeElement.matches('[data-rx-peek]');

        var focusedRank = focusedPeek
            ? parseInt(document.activeElement.dataset.rank, 10)
            : activeRank;

        if (/^[1-8]$/.test(e.key)) {
            var target = parseInt(e.key, 10);
            if (byRank[target]) {
                e.preventDefault();
                selectRank(target, true);
            }
            return;
        }

        if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectRank(Math.max(1, focusedRank - 1), true);
            return;
        }

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectRank(Math.min(peeks.length, focusedRank + 1), true);
            return;
        }

        if (e.key === 'Home') {
            e.preventDefault();
            selectRank(1, true);
            return;
        }

        if (e.key === 'End') {
            e.preventDefault();
            selectRank(peeks.length, true);
        }
    });

    layoutPeeks(activeRank);
    renderActive(byRank[activeRank]);
})();
</script>
@endif