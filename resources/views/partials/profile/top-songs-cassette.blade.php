{{--
    TOP 8 SONGS - CASSETTE DECK

    Expects: $songs = [ ['name' =>, 'artist' =>, 'image' =>, 'playcount' =>], ... ]
    Usage:   @include('partials.profile.top-songs-cassette', ['songs' => $user->lastfm_data['top_songs'] ?? []])

    Self-contained: CSS is scoped under .cassette-deck, JS is an IIFE keyed
    to a per-render id. Replaces the whole old "Top 8 Songs" xp-panel.
    Warm Bakelite deck, recessed glass reel window, dimensional cassette,
    and the existing tracklist as the interaction surface below.
--}}
@php
    $cid = 'cassette-' . \Illuminate\Support\Str::random(6);
    $songList = collect($songs ?? [])->take(8)->values();
    $count = $songList->count();
@endphp

<style>
    /* =====================================================
       CASSETTE DECK - scoped under .cassette-deck so the
       midnight theme's !important overrides stay contained.
       ===================================================== */
    .cassette-deck [hidden] { display: none !important; }

    .cassette-deck .cass-header {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    .cassette-deck .cass-header svg { flex-shrink: 0; }

    /* ================= DECK STAGE ================= */
    .cassette-deck .cass-stage {
        position: relative;
        width: 100%;
        padding: 8px 0 6px;
    }

    /* ================= BAKELITE DECK HOUSING ================= */
    .cassette-deck .cass-deck {
        position: relative;
        width: 100%;
        max-width: 280px;
        height: 202px;
        margin: 0 auto;
        border: 1px solid #2a1a12;
        border-radius: 7px 7px 5px 5px;
        background:
            linear-gradient(135deg, rgba(255,255,255,0.12) 0%, transparent 27%),
            linear-gradient(160deg, #69452f 0%, #543523 52%, #3b2519 100%);
        box-shadow:
            inset 1px 1px 0 rgba(255,255,255,0.14),
            inset -2px -2px 0 rgba(0,0,0,0.24),
            2px 3px 0 rgba(0,0,0,0.17),
            5px 7px 10px rgba(0,0,0,0.4);
        overflow: hidden;
    }

    /* Raised top lip of the deck. */
    .cassette-deck .cass-deck::before {
        content: "";
        position: absolute;
        top: 7px;
        left: 8px;
        right: 8px;
        height: 22px;
        border-radius: 4px;
        background:
            linear-gradient(180deg, rgba(255,255,255,0.10), transparent 45%),
            linear-gradient(180deg, #6e4932, #4a2e20);
        border: 1px solid rgba(38,23,15,0.78);
        box-shadow:
            inset 1px 1px 0 rgba(255,255,255,0.10),
            inset 0 -1px 0 rgba(0,0,0,0.28),
            0 2px 2px rgba(0,0,0,0.18);
        pointer-events: none;
    }

    /* Top-loading slot: the cassette drops into this recessed mouth. */
    .cassette-deck .cass-slot {
        position: absolute;
        z-index: 1;
        left: 50%;
        top: 35px;
        width: 88%;
        height: 157px;
        transform: translateX(-50%);
        border: 1px solid #25170f;
        border-radius: 4px;
        background:
            linear-gradient(180deg, rgba(255,255,255,0.05), transparent 18%),
            linear-gradient(180deg, #2b1b13 0%, #1c120d 70%, #160e0a 100%);
        box-shadow:
            inset 0 2px 4px rgba(0,0,0,0.75),
            inset 0 0 0 1px rgba(0,0,0,0.45),
            0 1px 0 rgba(255,255,255,0.05);
    }

    /* Slot guides help sell a physical top-loading mechanism. */
    .cassette-deck .cass-slot::before,
    .cassette-deck .cass-slot::after {
        content: "";
        position: absolute;
        top: 8px;
        bottom: 8px;
        width: 3px;
        border-radius: 2px;
        background: linear-gradient(180deg, #754e36, #39241a);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.07);
    }
    .cassette-deck .cass-slot::before { left: 5px; }
    .cassette-deck .cass-slot::after { right: 5px; }

    /* ================= CASSETTE / TAPE BODY ================= */
    .cassette-deck .cass-shell {
        position: absolute;
        z-index: 3;
        left: 50%;
        top: 43px;
        width: 82%;
        max-width: 230px;
        height: 137px;
        transform: translateX(-50%) rotate(-0.7deg);
        border: 1px solid #1d120d;
        border-radius: 5px;
        background:
            linear-gradient(180deg, rgba(255,255,255,0.11), transparent 18%),
            linear-gradient(155deg, #664431 0%, #4c3021 53%, #362116 100%);
        box-shadow:
            1px 1px 0 #22140e,
            2px 2px 0 #180e0a,
            4px 6px 7px rgba(0,0,0,0.46),
            inset 1px 1px 0 rgba(255,255,255,0.12),
            inset -1px -2px 0 rgba(0,0,0,0.25);
    }

    /* Dimensional top and bottom edges, rather than a perspective transform. */
    .cassette-deck .cass-shell::before {
        content: "";
        position: absolute;
        z-index: 4;
        left: 2px;
        right: 2px;
        top: 0;
        height: 6px;
        border-radius: 4px 4px 1px 1px;
        background: linear-gradient(180deg, #8a6047 0%, #684630 55%, #4e3021 100%);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.18);
        pointer-events: none;
    }

    .cassette-deck .cass-shell::after {
        content: "";
        position: absolute;
        z-index: 4;
        left: 2px;
        right: 2px;
        bottom: 0;
        height: 8px;
        border-radius: 1px 1px 4px 4px;
        background: linear-gradient(180deg, #3f281b 0%, #2b190f 100%);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
        pointer-events: none;
    }

    .cassette-deck .cass-screw {
        position: absolute;
        z-index: 5;
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: radial-gradient(circle at 34% 28%, #b08b68, #59402e 58%, #22140d 100%);
        box-shadow: 0 1px 1px rgba(0,0,0,0.55);
    }
    .cassette-deck .cass-screw::after {
        content: "";
        position: absolute;
        inset: 1px;
        border-top: 1px solid rgba(18,10,6,0.72);
        transform: rotate(32deg);
    }
    .cassette-deck .cass-screw--tl { top: 9px; left: 8px; }
    .cassette-deck .cass-screw--tr { top: 9px; right: 8px; }
    .cassette-deck .cass-screw--bl { bottom: 10px; left: 8px; }
    .cassette-deck .cass-screw--br { bottom: 10px; right: 8px; }

    /* ================= LABEL / GLASS ================= */
    .cassette-deck .cass-label {
        position: absolute;
        top: 12px;
        left: 13px;
        right: 13px;
        height: 27px;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 7px;
        border: 1px solid #302019;
        border-radius: 2px;
        background: linear-gradient(180deg, var(--surface-post) 0%, #e0dcd0 100%);
        color: #30271b;
        box-shadow:
            inset 1px 1px 0 rgba(255,255,255,0.62),
            inset 0 -1px 0 rgba(0,0,0,0.16),
            1px 1px 0 rgba(0,0,0,0.28);
    }
    .cassette-deck .cass-label-side,
    .cassette-deck .cass-label-tag {
        font: 700 0.55rem/1 Tahoma, 'Segoe UI', Verdana, sans-serif;
        letter-spacing: 0.3px;
    }
    .cassette-deck .cass-label-tag { color: #74521e; }
    .cassette-deck .cass-label-rule {
        flex: 1;
        height: 1px;
        margin: 0 6px;
        background: repeating-linear-gradient(90deg, #9b8f6c 0 4px, transparent 4px 8px);
    }

    /* Recessed dark window: the SVG reels are physically behind this glass. */
    .cassette-deck .cass-window {
        position: absolute;
        z-index: 1;
        left: 13px;
        right: 13px;
        top: 45px;
        bottom: 22px;
        border: 1px solid #18100b;
        border-radius: 2px;
        background:
            linear-gradient(135deg, rgba(255,255,255,0.08), transparent 28%),
            linear-gradient(180deg, #1b1510 0%, #0d0a08 58%, #090604 100%);
        box-shadow:
            inset 0 2px 5px rgba(0,0,0,0.9),
            inset 0 0 0 1px rgba(0,0,0,0.7),
            0 1px 0 rgba(255,255,255,0.05);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 8%;
        overflow: hidden;
    }

    /* Glass reflections sit over the reels without moving them. */
    .cassette-deck .cass-window::after {
        content: "";
        position: absolute;
        inset: 0;
        background: linear-gradient(115deg, rgba(255,255,255,0.14) 0%, rgba(255,255,255,0.03) 24%, transparent 42%);
        pointer-events: none;
    }

    .cassette-deck .cass-window-tape {
        position: absolute;
        left: 21%;
        right: 21%;
        top: 50%;
        height: 3px;
        transform: translateY(-50%);
        border-radius: 2px;
        background: linear-gradient(180deg, #8a6233, #3f2c19 55%, #25170d);
        box-shadow: 0 1px 2px rgba(0,0,0,0.55);
    }

    .cassette-deck .cass-reel {
        position: relative;
        z-index: 2;
        width: 35%;
        aspect-ratio: 1 / 1;
    }

    .cassette-deck .cass-reel svg {
        display: block;
        width: 100%;
        height: 100%;
    }

    .cassette-deck .cass-reel-rim {
        fill: #11100d;
        stroke: #6c6555;
        stroke-width: 1;
    }
    .cassette-deck .cass-reel-well {
        fill: #29251e;
        stroke: #7a6c55;
        stroke-width: 0.9;
    }
    .cassette-deck .cass-reel-hub {
        fill: #100e0b;
        stroke: #998366;
        stroke-width: 1;
    }
    .cassette-deck .cass-reel-spoke {
        stroke: #746a5a;
        stroke-width: 1.35;
        stroke-linecap: round;
    }
    .cassette-deck .cass-reel-glint {
        stroke: rgba(255,255,255,0.25);
        stroke-width: 0.9;
        stroke-linecap: round;
    }

    /* Lower tape-head / transport lip. */
    .cassette-deck .cass-foot {
        position: absolute;
        left: 9%;
        right: 9%;
        bottom: 2px;
        height: 18px;
        z-index: 3;
        border: 1px solid #24160e;
        border-radius: 2px;
        background: linear-gradient(180deg, #684630 0%, #392217 100%);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
    }
    .cassette-deck .cass-foot-hole {
        position: absolute;
        bottom: 4px;
        width: 9px;
        height: 5px;
        border-radius: 1px;
        background: #120b07;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.9);
    }
    .cassette-deck .cass-foot-hole--l { left: 24%; }
    .cassette-deck .cass-foot-hole--r { right: 24%; }
    .cassette-deck .cass-foot-notch {
        position: absolute;
        left: 50%;
        bottom: 3px;
        width: 24px;
        height: 5px;
        border-radius: 1px;
        background: #120b07;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.9);
        transform: translateX(-50%);
    }

    /* ================= STICKY NOTE ON HOUSING ================= */
    .cassette-deck .cass-note {
        position: absolute;
        z-index: 8;
        top: 2px;
        left: 7%;
        width: 112px;
        min-height: 43px;
        padding: 7px 8px 8px;
        background:
            radial-gradient(rgba(0,0,0,0.05) 1px, transparent 1px) 0 0/3px 3px,
            #fff4b8;
        color: #3a2f0a;
        border: 1px solid rgba(134,105,26,0.26);
        box-shadow:
            1px 2px 0 rgba(0,0,0,0.12),
            3px 5px 6px rgba(0,0,0,0.28);
        transform: rotate(-2.4deg);
        transform-origin: 50% 15%;
        transition: transform 150ms ease-out, box-shadow 150ms ease-out;
        pointer-events: none;
    }

    .cassette-deck .cass-note::before {
        content: "";
        position: absolute;
        top: -7px;
        left: 52%;
        width: 39px;
        height: 13px;
        background: rgba(226,220,198,0.58);
        border-left: 1px solid rgba(0,0,0,0.06);
        border-right: 1px solid rgba(0,0,0,0.06);
        box-shadow: 0 1px 2px rgba(0,0,0,0.18);
        transform: translateX(-50%) rotate(2deg);
    }

    .cassette-deck .cass-note-title {
        font: 700 0.78rem/1.05 Tahoma, 'Segoe UI', Verdana, sans-serif;
    }
    .cassette-deck .cass-note-sub {
        margin-top: 4px;
        font: 0.59rem/1.15 Tahoma, 'Segoe UI', Verdana, sans-serif;
        color: #5a4c18;
    }

    @media (prefers-reduced-motion: no-preference) {
        .cassette-deck.is-playing .cass-note {
            transform: rotate(-2.4deg) translateY(-2px) rotate(0.45deg);
            box-shadow:
                1px 3px 0 rgba(0,0,0,0.12),
                3px 7px 7px rgba(0,0,0,0.3);
        }
    }

    @media (prefers-reduced-motion: reduce) {
        .cassette-deck .cass-note { transition: none; }
    }

    /* ================= J-CARD TRACK LIST ================= */
    .cassette-deck .cass-jcard {
        display: flex;
        margin-top: 0.5rem;
        background: var(--surface-post);
        border: 1px solid var(--border-default);
        box-shadow: inset 1px 1px 0 #ffffff, 1px 1px 0 rgba(0, 0, 0, 0.12);
        color: var(--text-primary);
    }
    .cassette-deck .cass-jcard-spine {
        flex: 0 0 8px;
        background: var(--accent-dark);
    }
    .cassette-deck .cass-jcard-empty {
        flex: 1;
        padding: 0.9rem 0.8rem;
        font-size: 0.72rem;
        color: var(--text-muted);
        font-family: Tahoma, 'Segoe UI', Verdana, sans-serif;
    }

    .cassette-deck .cass-tracklist {
        flex: 1;
        min-width: 0;
        max-height: 190px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .cassette-deck .cass-track {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        padding: 5px 8px;
        border: 0;
        border-bottom: 1px solid var(--border-subtle);
        background: var(--surface-post);
        font: inherit;
        color: inherit;
        text-align: left;
        cursor: pointer;
    }
    .cassette-deck .cass-track:nth-child(even) {
        background: rgba(58, 123, 213, 0.05);
    }
    .cassette-deck .cass-track:last-child { border-bottom: 0; }
    .cassette-deck .cass-track:hover { background: var(--surface-hover); }
    .cassette-deck .cass-track.is-selected {
        background: var(--accent);
        color: #ffffff;
        border-bottom-color: var(--accent-dark);
    }
    .cassette-deck .cass-track.is-selected .cass-track-artist,
    .cassette-deck .cass-track.is-selected .cass-track-plays {
        color: rgba(255, 255, 255, 0.85);
    }
    .cassette-deck .cass-track:focus-visible {
        outline: 2px dotted var(--accent-dark);
        outline-offset: -2px;
    }

    .cassette-deck .cass-track-rank {
        flex: 0 0 auto;
        min-width: 1.4em;
        font-family: 'Courier New', Courier, monospace;
        font-weight: 700;
        font-size: 0.72rem;
        opacity: 0.75;
    }
    .cassette-deck .cass-track-main {
        flex: 1 1 auto;
        min-width: 0;
    }
    .cassette-deck .cass-track-name {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 700;
        font-size: 0.74rem;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cassette-deck .cass-track-artist {
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.64rem;
        line-height: 1.2;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cassette-deck .cass-track-plays {
        flex: 0 0 auto;
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.66rem;
        color: var(--text-muted);
        white-space: nowrap;
    }

    /* XP-style vertical scrollbar, same pattern as the existing tracklist. */
    .cassette-deck .cass-tracklist::-webkit-scrollbar { width: 16px; }
    .cassette-deck .cass-tracklist::-webkit-scrollbar-track {
        background: var(--surface-sunken);
        border-left: 1px solid var(--border-default);
    }
    .cassette-deck .cass-tracklist::-webkit-scrollbar-thumb {
        background: var(--surface-primary);
        border: 1px solid var(--border-default);
        border-radius: 0;
        box-shadow: inset 1px 1px 0 #ffffff, inset -1px -1px 0 var(--border-dark);
    }
    .cassette-deck .cass-tracklist::-webkit-scrollbar-thumb:hover { background: var(--surface-hover); }
    .cassette-deck .cass-tracklist::-webkit-scrollbar-button:vertical:single-button {
        width: 16px;
        height: 16px;
        background-color: var(--surface-primary);
        background-repeat: no-repeat;
        background-position: center;
        border: 1px solid var(--border-default);
        box-shadow: inset 1px 1px 0 #ffffff, inset -1px -1px 0 var(--border-dark);
    }
    .cassette-deck .cass-tracklist::-webkit-scrollbar-button:vertical:decrement {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='7' height='7' viewBox='0 0 7 7'%3E%3Cpath d='M0 5h7L3.5 1z' fill='%231e1e1e'/%3E%3C/svg%3E");
    }
    .cassette-deck .cass-tracklist::-webkit-scrollbar-button:vertical:increment {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='7' height='7' viewBox='0 0 7 7'%3E%3Cpath d='M0 2h7L3.5 6z' fill='%231e1e1e'/%3E%3C/svg%3E");
    }
    @supports not selector(::-webkit-scrollbar) {
        .cassette-deck .cass-tracklist { scrollbar-color: #c8c0b8 #e8e4dc; }
    }
</style>

<div class="xp-panel cassette-deck" id="{{ $cid }}">
    <div class="xp-panel-header cass-header">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <rect x="2" y="6" width="20" height="13" rx="0"/>
            <circle cx="8" cy="12.5" r="2.4"/>
            <circle cx="16" cy="12.5" r="2.4"/>
            <path d="M6 19v2M18 19v2"/>
        </svg>
        Top 8 Songs
    </div>

    <div class="xp-panel-body">
        @if($count === 0)
            <div class="cass-stage">
                <div class="cass-deck" role="img" aria-label="Cassette deck for Top 8 Songs">
                    <div class="cass-slot" aria-hidden="true"></div>
                    <div class="cass-note" aria-hidden="true">
                        <div class="cass-note-title">Top 8 Songs</div>
                        <div class="cass-note-sub">8 tracks, most played first</div>
                    </div>
                </div>
            </div>

            <div class="cass-jcard">
                <div class="cass-jcard-spine" aria-hidden="true"></div>
                <div class="cass-jcard-empty">No top songs found.</div>
            </div>
        @else
            <div class="cass-stage">
                <div class="cass-deck" role="img"
                     aria-label="Cassette deck containing the Top 8 Songs tape; use the tracklist below to select a song">
                    <div class="cass-slot" aria-hidden="true"></div>

                    <div class="cass-shell" aria-hidden="true">
                        <span class="cass-screw cass-screw--tl"></span>
                        <span class="cass-screw cass-screw--tr"></span>
                        <span class="cass-screw cass-screw--bl"></span>
                        <span class="cass-screw cass-screw--br"></span>

                        <div class="cass-label">
                            <span class="cass-label-side">SIDE A</span>
                            <span class="cass-label-rule"></span>
                            <span class="cass-label-tag">TOP 8</span>
                        </div>

                        <div class="cass-window">
                            <span class="cass-window-tape"></span>

                            <span class="cass-reel">
                                <svg viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                                    <circle cx="20" cy="20" r="18" class="cass-reel-rim"/>
                                    <circle cx="20" cy="20" r="14.5" class="cass-reel-well"/>
                                    <g class="cass-reel-spoke">
                                        <line x1="20" y1="9.2" x2="20" y2="30.8"/>
                                        <line x1="9.2" y1="20" x2="30.8" y2="20"/>
                                        <line x1="12.2" y1="12.2" x2="27.8" y2="27.8"/>
                                        <line x1="27.8" y1="12.2" x2="12.2" y2="27.8"/>
                                    </g>
                                    <circle cx="20" cy="20" r="5.6" class="cass-reel-hub"/>
                                    <circle cx="20" cy="20" r="1.7" fill="#917e62"/>
                                    <path d="M8.6 14.2A13 13 0 0 1 14 8.8" class="cass-reel-glint" fill="none"/>
                                </svg>
                            </span>

                            <span class="cass-reel">
                                <svg viewBox="0 0 40 40" aria-hidden="true" focusable="false">
                                    <circle cx="20" cy="20" r="18" class="cass-reel-rim"/>
                                    <circle cx="20" cy="20" r="14.5" class="cass-reel-well"/>
                                    <g class="cass-reel-spoke">
                                        <line x1="20" y1="9.2" x2="20" y2="30.8"/>
                                        <line x1="9.2" y1="20" x2="30.8" y2="20"/>
                                        <line x1="12.2" y1="12.2" x2="27.8" y2="27.8"/>
                                        <line x1="27.8" y1="12.2" x2="12.2" y2="27.8"/>
                                    </g>
                                    <circle cx="20" cy="20" r="5.6" class="cass-reel-hub"/>
                                    <circle cx="20" cy="20" r="1.7" fill="#917e62"/>
                                    <path d="M8.6 14.2A13 13 0 0 1 14 8.8" class="cass-reel-glint" fill="none"/>
                                </svg>
                            </span>
                        </div>

                        <div class="cass-foot">
                            <span class="cass-foot-hole cass-foot-hole--l"></span>
                            <span class="cass-foot-hole cass-foot-hole--r"></span>
                            <span class="cass-foot-notch"></span>
                        </div>
                    </div>

                    <div class="cass-note" aria-hidden="true">
                        <div class="cass-note-title">Top 8 Songs</div>
                        <div class="cass-note-sub">8 tracks, most played first</div>
                    </div>
                </div>
            </div>

            <div class="cass-jcard">
                <div class="cass-jcard-spine" aria-hidden="true"></div>
                <div class="cass-tracklist" role="group" aria-label="Top 8 songs">
                    @foreach($songList as $i => $song)
                        @php
                            $rank = $i + 1;
                            $name = trim((string) ($song['name'] ?? '')) ?: 'Unknown Track';
                            $artist = trim((string) ($song['artist'] ?? '')) ?: 'Unknown Artist';
                            $plays = (int) ($song['playcount'] ?? 0);
                        @endphp
                        <button type="button" class="cass-track" data-cass-track aria-pressed="false"
                                title="{{ $name }} - {{ $artist }}">
                            <span class="cass-track-rank" aria-hidden="true">{{ str_pad($rank, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="cass-track-main">
                                <span class="cass-track-name">{{ $name }}</span>
                                <span class="cass-track-artist">{{ $artist }}</span>
                            </span>
                            <span class="cass-track-plays">{{ number_format($plays) }}&nbsp;plays</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

@if($count > 0)
<script>
(function () {
    var root = document.getElementById(@json($cid));
    if (!root) return;

    var tracks = Array.prototype.slice.call(root.querySelectorAll('[data-cass-track]'));
    var selected = -1;
    function select(index) {
        if (index === selected) index = -1; // preserve existing click-to-toggle behavior

        tracks.forEach(function (track, k) {
            var on = k === index;
            track.classList.toggle('is-selected', on);
            track.setAttribute('aria-pressed', on ? 'true' : 'false');
        });

        root.classList.toggle('is-playing', index > -1);
        selected = index;
    }

    tracks.forEach(function (track, k) {
        track.addEventListener('click', function () { select(k); });
    });

    // Up/Down move focus between tracks; Escape stops the selected state.
    root.addEventListener('keydown', function (e) {
        var current = tracks.indexOf(document.activeElement);

        if (e.key === 'Escape' && selected > -1) {
            e.preventDefault();
            select(selected);
            return;
        }
        if (current < 0) return;

        var next = current;
        if (e.key === 'ArrowDown') next = Math.min(tracks.length - 1, current + 1);
        else if (e.key === 'ArrowUp') next = Math.max(0, current - 1);
        else if (e.key === 'Home') next = 0;
        else if (e.key === 'End') next = tracks.length - 1;
        else return;

        e.preventDefault();
        tracks[next].focus();
    });

    /* Reels are intentionally static; selection is communicated by the track row and note motion. */
})();
</script>
@endif