{{--
    TOP 8 ARTISTS - WALL PHONE

    Expects: $artists = [ ['name' =>, 'image' =>, 'playcount' =>], ... ]
    Usage:   @include('partials.profile.top-artists-phone', ['artists' => $user->lastfm_data['top_artists'] ?? []])

    Self-contained: CSS scoped under .wall-phone, JS is an IIFE keyed
    to a per-render id. Replaces the whole old "Top 8 Artists" xp-panel.
    Flat Windows XP-era skeuomorphic styling: warm Bakelite, aged LCD,
    photo buttons, soft stacked shadows.
--}}
@php
    $pid = 'phone-' . \Illuminate\Support\Str::random(6);
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
@endphp

<style>
    /* ================= WALL PHONE ================= */
    .wall-phone [hidden] { display: none !important; }

    .wall-phone {
        --wp-bakelite: #5a3928;
        --wp-bakelite-dark: #3a2419;
        --wp-bakelite-deep: #24160f;
        --wp-face: #7a5239;
        --wp-face-light: #8c6044;
        --wp-cream: #e8d9b5;
        --wp-cream-light: #f3e8c9;
        --wp-lcd: #e8e4dc;
        --wp-lcd-dark: #293126;
        --wp-muted: #716856;
        --wp-border: #a68b61;
        --wp-shadow: rgba(0, 0, 0, 0.38);
    }

    .wall-phone .wp-header {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }

    .wall-phone .wp-header svg {
        flex-shrink: 0;
    }

    .wall-phone .wp-empty {
        padding: 0.6rem 0;
        color: var(--text-muted);
        font: 0.75rem/1.35 Tahoma, 'Segoe UI', Verdana, sans-serif;
    }

    .wall-phone .wp-stage {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        padding: 6px 0 2px;
    }

    .wall-phone .wp-phone {
        position: relative;
        width: 100%;
        height: 220px;
        max-width: 240px;
        margin: 0;
        filter: drop-shadow(3px 5px 5px rgba(0, 0, 0, 0.22));
    }

    .wall-phone .wp-body {
        position: absolute;
        inset: 5px 0 0;
        overflow: hidden;
        border-radius: 9px 9px 6px 6px;
        background:
            linear-gradient(135deg, rgba(255,255,255,0.12), transparent 27%),
            linear-gradient(160deg, var(--wp-bakelite) 0%, var(--wp-bakelite-dark) 58%, var(--wp-bakelite-deep) 100%);
        border: 1px solid #2a1a12;
        box-shadow:
            inset 1px 1px 0 rgba(255,255,255,0.13),
            inset -2px -3px 0 rgba(0,0,0,0.2),
            2px 3px 0 rgba(0,0,0,0.18),
            4px 7px 9px var(--wp-shadow);
    }

    .wall-phone .wp-face {
        position: absolute;
        top: 9px;
        right: 8px;
        bottom: 8px;
        left: 49px;
        padding: 7px 7px 6px;
        border-radius: 5px;
        background:
            linear-gradient(135deg, rgba(255,255,255,0.08), transparent 32%),
            linear-gradient(160deg, var(--wp-face-light) 0%, var(--wp-face) 60%, #68462f 100%);
        border: 1px solid rgba(35, 20, 13, 0.75);
        box-shadow:
            inset 1px 1px 0 rgba(255,255,255,0.10),
            inset -1px -2px 0 rgba(0,0,0,0.22);
    }

    .wall-phone .wp-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        grid-template-rows: repeat(4, minmax(0, 1fr));
        gap: 4px;
        height: 151px;
    }

    .wall-phone .wp-button {
        position: relative;
        min-width: 0;
        min-height: 0;
        padding: 0;
        overflow: hidden;
        border: 2px solid var(--border-default);
        border-radius: 4px;
        background: var(--wp-cream);
        box-shadow:
            inset 1px 1px 0 rgba(255,255,255,0.65),
            inset -1px -1px 0 rgba(0,0,0,0.22),
            1px 1px 2px rgba(0,0,0,0.32);
        cursor: pointer;
        transition: border-color 100ms ease-out, box-shadow 100ms ease-out, transform 100ms ease-out;
    }

    .wall-phone .wp-button:focus-visible {
        outline: 2px dotted var(--accent-dark);
        outline-offset: 2px;
    }

    .wall-phone .wp-button.is-selected {
        border-color: var(--accent);
        box-shadow:
            0 0 0 1px rgba(58,123,213,0.35),
            0 0 7px rgba(58,123,213,0.6),
            inset 1px 1px 0 rgba(255,255,255,0.65),
            inset -1px -1px 0 rgba(0,0,0,0.2);
    }

    @media (hover: hover) {
        .wall-phone .wp-button:hover:not(.is-selected) {
            border-color: var(--accent);
            box-shadow:
                0 0 4px rgba(58,123,213,0.4),
                inset 1px 1px 0 rgba(255,255,255,0.65),
                inset -1px -1px 0 rgba(0,0,0,0.2);
        }
    }

    .wall-phone .wp-photo,
    .wall-phone .wp-photo-fallback {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
    }

    .wall-phone .wp-photo {
        display: block;
        object-fit: cover;
    }

    .wall-phone .wp-photo-fallback {
        display: block;
    }

    .wall-phone .wp-rank {
        position: absolute;
        z-index: 2;
        top: 2px;
        left: 2px;
        min-width: 13px;
        height: 13px;
        padding: 0 3px;
        border-radius: 2px;
        background: rgba(42, 27, 18, 0.88);
        color: #fff4d7;
        box-shadow: 0 1px 1px rgba(0,0,0,0.42);
        font: 700 8px/13px Tahoma, 'Segoe UI', Verdana, sans-serif;
        text-align: center;
        pointer-events: none;
    }

    .wall-phone .wp-button.is-selected .wp-rank {
        background: var(--accent);
        color: #ffffff;
    }

    /* Aged calculator-style LCD. */
    .wall-phone .wp-lcd {
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: center;
        height: 31px;
        margin-top: 6px;
        padding: 3px 6px;
        overflow: hidden;
        border: 1px solid #5d583d;
        border-radius: 2px;
        background:
            linear-gradient(180deg, rgba(255,255,255,0.16), transparent 42%),
            var(--wp-lcd);
        box-shadow:
            inset 1px 1px 2px rgba(0,0,0,0.25),
            inset -1px -1px 0 rgba(255,255,255,0.25),
            1px 1px 2px rgba(0,0,0,0.28);
        color: var(--wp-lcd-dark);
        font-family: Tahoma, 'Segoe UI', Verdana, sans-serif;
    }

    .wall-phone .wp-lcd-name {
        min-width: 0;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        font: 700 9px/12px 'Courier New', Tahoma, 'Segoe UI', Verdana, sans-serif;
        color: #1e1e1e;
    }

    .wall-phone .wp-lcd-plays {
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        color: #6a6a6a;
        font: 8px/10px 'Courier New', Tahoma, 'Segoe UI', Verdana, sans-serif;
    }

    .wall-phone .wp-status {
        position: absolute;
        right: 5px;
        bottom: 7px;
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: #6a6a6a;
        box-shadow: inset 0 1px 1px rgba(0,0,0,0.35);
        transition: background-color 120ms ease-out, box-shadow 120ms ease-out;
    }

    .wall-phone .wp-status.is-connected {
        background: var(--accent);
        box-shadow:
            0 0 5px rgba(58,123,213,0.65),
            inset 0 1px 1px rgba(255,255,255,0.45);
    }

    /* ================= HANDSET ================= */
    .wall-phone .wp-handset-zone {
        position: absolute;
        z-index: 6;
        top: 12px;
        left: 5px;
        width: 40px;
        height: 191px;
        pointer-events: none;
    }

    .wall-phone .wp-handset-shadow {
        position: absolute;
        left: 8px;
        top: 5px;
        width: 27px;
        height: 178px;
        border-radius: 50%;
        background: rgba(0,0,0,0.34);
        filter: blur(4px);
        opacity: 0.6;
    }

    /*
     * One curved handset shape made from CSS: rounded vertical grip plus
     * rounded receiver cups. CSS keeps the silhouette crisp and simple.
     */
    .wall-phone .wp-handset {
        position: absolute;
        left: 9px;
        top: 5px;
        width: 25px;
        height: 169px;
        border-radius: 13px;
        background:
            linear-gradient(90deg, rgba(255,255,255,0.09), transparent 23% 72%, rgba(0,0,0,0.18)),
            linear-gradient(180deg, #6a4936 0%, #4b3022 48%, #382218 100%);
        border: 1px solid #281810;
        box-shadow:
            inset 1px 1px 0 rgba(255,255,255,0.12),
            inset -2px -2px 0 rgba(0,0,0,0.22),
            2px 3px 4px rgba(0,0,0,0.42);
        transform: translateY(0);
        transform-origin: 50% 14px;
        transition: transform 150ms ease-out;
    }

    .wall-phone .wp-handset::before,
    .wall-phone .wp-handset::after {
        content: "";
        position: absolute;
        left: -6px;
        width: 36px;
        height: 47px;
        border-radius: 12px;
        background:
            radial-gradient(circle at 35% 28%, #77543e 0%, #513424 42%, #382319 100%);
        border: 1px solid #281810;
        box-shadow:
            inset 1px 1px 0 rgba(255,255,255,0.11),
            inset -2px -2px 0 rgba(0,0,0,0.2),
            1px 2px 3px rgba(0,0,0,0.34);
    }

    .wall-phone .wp-handset::before { top: -5px; }
    .wall-phone .wp-handset::after { bottom: -5px; }

    .wall-phone .wp-handset.is-lifting {
        transform: translateY(-5px);
    }

    /* ================= VISIBLY COILED CORD ================= */
    .wall-phone .wp-cord {
        position: absolute;
        z-index: 2;
        left: 17px;
        top: 157px;
        width: 31px;
        height: 63px;
        overflow: visible;
    }

    .wall-phone .wp-cord path {
        fill: none;
        stroke: #24170f;
        stroke-width: 3.2;
        stroke-linecap: round;
        stroke-linejoin: round;
        /*
         * Short rounded dashes create visible coil ridges rather than a
         * single wire. The path alternates tighter upper loops and looser
         * lower loops.
         */
        stroke-dasharray: 2.2 3.2;
        filter: drop-shadow(1px 1px 1px rgba(0,0,0,0.35));
    }

    .wall-phone .wp-cord-highlight {
        fill: none;
        stroke: rgba(150,104,69,0.72);
        stroke-width: 1;
        stroke-linecap: round;
        stroke-dasharray: 1.3 4.1;
    }

    @media (prefers-reduced-motion: reduce) {
        .wall-phone .wp-handset,
        .wall-phone .wp-status,
        .wall-phone .wp-button {
            transition: none;
        }
    }
</style>

<div class="xp-panel wall-phone" id="{{ $pid }}">
    <div class="xp-panel-header wp-header">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>
        </svg>
        Top 8 Artists
    </div>

    <div class="xp-panel-body">
        @if($count === 0)
            <div class="wp-empty">No top artists found.</div>
        @else
            {{-- Compact portrait fallback: deliberately simple so it reads at ~40px. --}}
            <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
                <defs>
                    <linearGradient id="{{ $pid }}-portrait-bg" x1="0" y1="0" x2="1" y2="1">
                        <stop offset="0" stop-color="#e5d8b7"/>
                        <stop offset="1" stop-color="#b9a77f"/>
                    </linearGradient>
                    <linearGradient id="{{ $pid }}-portrait-figure" x1="0" y1="0" x2="0.9" y2="1">
                        <stop offset="0" stop-color="#777267"/>
                        <stop offset="1" stop-color="#45433e"/>
                    </linearGradient>
                </defs>
                <symbol id="{{ $pid }}-blank" viewBox="0 0 20 20">
                    <rect width="20" height="20" fill="url(#{{ $pid }}-portrait-bg)"/>
                    <circle cx="10" cy="6.4" r="3.1" fill="url(#{{ $pid }}-portrait-figure)"/>
                    <path d="M3.4 19c.45-4.05 2.65-6.55 6.6-6.55s6.15 2.5 6.6 6.55H3.4Z" fill="url(#{{ $pid }}-portrait-figure)"/>
                </symbol>
            </svg>

            <div class="wp-stage">
                <div class="wp-phone">
                    <div class="wp-body">
                        <div class="wp-face">
                            <div class="wp-grid" role="group" aria-label="Top 8 artists: select an artist to call">
                                @foreach($artistList as $i => $artist)
                                    @php
                                        $rank = $i + 1;
                                        $name = trim((string) ($artist['name'] ?? '')) ?: 'Unknown Artist';
                                        $plays = (int) ($artist['playcount'] ?? 0);
                                        $img = $isRealImage($artist['image'] ?? null) ? $artist['image'] : null;
                                    @endphp

                                    <button type="button"
                                            class="wp-button @if($i === 0) is-selected @endif"
                                            data-wp-button
                                            data-rank="{{ $rank }}"
                                            data-name="{{ $name }}"
                                            data-image="{{ $img }}"
                                            data-plays="{{ number_format($plays) }}"
                                            aria-pressed="{{ $i === 0 ? 'true' : 'false' }}"
                                            aria-label="Rank {{ $rank }}: {{ $name }}, {{ number_format($plays) }} scrobbles">
                                        @if($img)
                                            <img class="wp-photo" src="{{ $img }}" alt="" loading="lazy">
                                        @else
                                            <svg class="wp-photo-fallback" aria-hidden="true"><use href="#{{ $pid }}-blank"/></svg>
                                        @endif
                                        <span class="wp-rank" aria-hidden="true">{{ $rank }}</span>
                                    </button>
                                @endforeach
                            </div>

                            <div class="wp-lcd"
                                 data-wp-lcd
                                 aria-live="polite"
                                 aria-atomic="true">
                                <div class="wp-lcd-name" data-wp-name></div>
                                <div class="wp-lcd-plays" data-wp-plays></div>
                                <span class="wp-status" data-wp-status aria-hidden="true"></span>
                            </div>
                        </div>

                        <div class="wp-handset-zone" aria-hidden="true">
                            <div class="wp-handset-shadow"></div>

                            <svg class="wp-cord" viewBox="0 0 31 63" aria-hidden="true" focusable="false">
                                <path d="M15 1
                                         C9 4, 22 7, 15 11
                                         C7 15, 23 18, 15 22
                                         C7 26, 24 29, 15 33
                                         C5 38, 25 40, 15 45
                                         C7 49, 23 53, 16 57
                                         C13 59, 13 61, 14 63"/>
                                <path class="wp-cord-highlight"
                                      d="M15 1
                                         C9 4, 22 7, 15 11
                                         C7 15, 23 18, 15 22
                                         C7 26, 24 29, 15 33
                                         C5 38, 25 40, 15 45
                                         C7 49, 23 53, 16 57
                                         C13 59, 13 61, 14 63"/>
                            </svg>

                            <div class="wp-handset" data-wp-handset></div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

@if($count > 0)
<script>
(function () {
    var root = document.getElementById(@json($pid));
    if (!root) return;

    var buttons = Array.prototype.slice.call(root.querySelectorAll('[data-wp-button]'));
    var nameEl = root.querySelector('[data-wp-name]');
    var playsEl = root.querySelector('[data-wp-plays]');
    var statusEl = root.querySelector('[data-wp-status]');
    var handset = root.querySelector('[data-wp-handset]');
    var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function fallbackSvg() {
        return '<svg class="wp-photo-fallback" aria-hidden="true"><use href="#' +
            @json($pid) + '-blank"/></svg>';
    }

    function render(button) {
        nameEl.textContent = button.dataset.name;
        playsEl.textContent = button.dataset.plays + ' scrobbles';
        statusEl.classList.add('is-connected');

        buttons.forEach(function (item) {
            var selected = item === button;
            item.classList.toggle('is-selected', selected);
            item.setAttribute('aria-pressed', selected ? 'true' : 'false');
        });

        if (!reducedMotion) {
            handset.classList.remove('is-lifting');
            void handset.offsetWidth;
            handset.classList.add('is-lifting');

            window.clearTimeout(handset._wpLiftTimer);
            handset._wpLiftTimer = window.setTimeout(function () {
                handset.classList.remove('is-lifting');
            }, 150);
        }
    }

    function select(button, focusButton) {
        if (!button) return;
        if (focusButton) button.focus();
        render(button);
    }

    buttons.forEach(function (button) {
        var image = button.querySelector('img');

        if (image) {
            image.addEventListener('error', function () {
                image.remove();
                button.insertAdjacentHTML('afterbegin', fallbackSvg());
            });
        }

        button.addEventListener('click', function () {
            select(button, false);
        });
    });

    root.addEventListener('keydown', function (e) {
        var current = buttons.indexOf(document.activeElement);

        if (/^[1-8]$/.test(e.key)) {
            var byRank = buttons[parseInt(e.key, 10) - 1];
            if (byRank) {
                e.preventDefault();
                select(byRank, true);
            }
            return;
        }

        if (current < 0) return;

        var next = current;

        if (e.key === 'ArrowRight') {
            next = Math.min(buttons.length - 1, current + 1);
        } else if (e.key === 'ArrowLeft') {
            next = Math.max(0, current - 1);
        } else if (e.key === 'ArrowDown') {
            next = Math.min(buttons.length - 1, current + 2);
        } else if (e.key === 'ArrowUp') {
            next = Math.max(0, current - 2);
        } else {
            return;
        }

        e.preventDefault();
        buttons[next].focus();
        select(buttons[next], false);
    });

    render(buttons[0]);
})();
</script>
@endif