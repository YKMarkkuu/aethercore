{{--
    TOP 8 SONGS - CASSETTE TAPE

    Expects: $songs = [ ['name' =>, 'artist' =>, 'image' =>, 'playcount' =>], ... ]
    Usage:   @include('partials.profile.top-songs-cassette', ['songs' => $user->lastfm_data['top_songs'] ?? []])

    Self-contained: CSS is scoped under .cassette-tape, JS is an IIFE keyed
    to a per-render id. Replaces the whole old "Top 8 Songs" xp-panel.
--}}
@php
    $cid = 'cassette-' . \Illuminate\Support\Str::random(6);
    $songList = collect($songs ?? [])->take(8)->values();
    $count = $songList->count();
@endphp

<style>
    /* =====================================================
       CASSETTE TAPE - scoped under .cassette-tape so the
       midnight theme's !important overrides can't reach the
       shell, sticky note or J-card.
       ===================================================== */
    .cassette-tape [hidden] { display: none !important; }

    .cassette-tape .cass-header {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    .cassette-tape .cass-header svg { flex-shrink: 0; }

    /* ----- stage: holds the cassette + the overlapping sticky note ----- */
    .cassette-tape .cass-stage {
        position: relative;
        padding: 26px 8px 10px 8px; /* room for the note to hang over the top-left */
    }

    /* ================= CASSETTE SHELL ================= */
    .cassette-tape .cass-shell {
        position: relative;
        width: 92%;
        max-width: 280px;
        aspect-ratio: 16 / 10;
        margin: 0 auto;
        background:
            linear-gradient(135deg, rgba(255, 255, 255, 0.10) 0%, transparent 30%),
            linear-gradient(150deg, #35353a 0%, #222225 55%, #18181a 100%);
        box-shadow:
            1px 1px 0 #101012,
            2px 2px 0 #0a0a0b,
            6px 8px 10px rgba(0, 0, 0, 0.5);
    }
    .cassette-tape .cass-shell::before {
        /* thin top-left edge highlight = the injection-moulded bevel */
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        box-shadow:
            inset 1px 1px 0 rgba(255, 255, 255, 0.18),
            inset -1px -1px 0 rgba(0, 0, 0, 0.5);
    }

    /* screws in each corner */
    .cassette-tape .cass-screw {
        position: absolute;
        width: 7px;
        height: 7px;
        background: radial-gradient(circle at 35% 30%, #7a7a7e, #3a3a3d 70%, #1c1c1e);
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.6);
        z-index: 3;
    }
    .cassette-tape .cass-screw::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 12%;
        right: 12%;
        height: 1px;
        background: rgba(0, 0, 0, 0.7);
        transform: translateY(-50%) rotate(35deg);
    }
    .cassette-tape .cass-screw--tl { top: 6px; left: 6px; }
    .cassette-tape .cass-screw--tr { top: 6px; right: 6px; }
    .cassette-tape .cass-screw--bl { bottom: 18px; left: 6px; }
    .cassette-tape .cass-screw--br { bottom: 18px; right: 6px; }

    /* label strip above the reel window */
    .cassette-tape .cass-label {
        position: absolute;
        top: 16px;
        left: 16px;
        right: 16px;
        height: 22%;
        background: linear-gradient(180deg, #f2ead0 0%, #e6dcbc 100%);
        border: 1px solid #17171a;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.6), 1px 1px 0 rgba(0, 0, 0, 0.4);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 6px;
        overflow: hidden;
    }
    .cassette-tape .cass-label-side {
        font: 700 0.6rem/1 Tahoma, 'Segoe UI', Verdana, sans-serif;
        color: #2a2418;
        letter-spacing: 0.3px;
    }
    .cassette-tape .cass-label-rule {
        flex: 1;
        height: 1px;
        margin: 0 6px;
        background: repeating-linear-gradient(90deg, #9c916f 0 4px, transparent 4px 8px);
    }
    .cassette-tape .cass-label-tag {
        font: 600 0.55rem/1 Tahoma, 'Segoe UI', Verdana, sans-serif;
        color: #6b5d2a;
        white-space: nowrap;
    }

    /* reel window (recessed) */
    .cassette-tape .cass-window {
        position: absolute;
        top: calc(16px + 22% + 6px);
        left: 16px;
        right: 16px;
        bottom: 34px;
        background: linear-gradient(180deg, #0c0c0d, #050506);
        box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.9), inset 0 0 0 1px #000;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 8%;
    }
    /* tape path between the two reels */
    .cassette-tape .cass-window::before {
        content: "";
        position: absolute;
        left: 22%;
        right: 22%;
        top: 50%;
        height: 2px;
        background: #4a3a22;
        transform: translateY(-50%);
        opacity: 0.85;
    }

    .cassette-tape .cass-reel {
        position: relative;
        width: 34%;
        aspect-ratio: 1 / 1;
    }
    .cassette-tape .cass-reel svg {
        width: 100%;
        height: 100%;
        display: block;
        transform-origin: 50% 50%;
    }
    .cassette-tape .cass-reel-spin {
        animation: cass-spin 2.4s linear infinite;
        animation-play-state: paused;
    }
    @keyframes cass-spin {
        to { transform: rotate(360deg); }
    }
    /* spin on hover, keyboard focus within, or while a track is playing */
    @media (hover: hover) {
        .cassette-tape .cass-shell:hover .cass-reel-spin { animation-play-state: running; }
    }
    .cassette-tape .cass-shell:focus-within .cass-reel-spin,
    .cassette-tape.is-playing .cass-reel-spin {
        animation-play-state: running;
    }
    @media (prefers-reduced-motion: reduce) {
        .cassette-tape .cass-reel-spin { animation: none; }
    }

    /* bottom tape-head trapezoid */
    .cassette-tape .cass-foot {
        position: absolute;
        left: 6%;
        right: 6%;
        bottom: 0;
        height: 34px;
        background: linear-gradient(180deg, #2c2c30, #17171a);
        clip-path: polygon(8% 0, 92% 0, 100% 100%, 0% 100%);
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }
    .cassette-tape .cass-foot-hole {
        position: absolute;
        bottom: 9px;
        width: 10px;
        height: 7px;
        background: #050506;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.9);
    }
    .cassette-tape .cass-foot-hole--l { left: 24%; }
    .cassette-tape .cass-foot-hole--r { right: 24%; }
    .cassette-tape .cass-foot-notch {
        position: absolute;
        bottom: 6px;
        left: 50%;
        width: 26px;
        height: 5px;
        background: #050506;
        box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.9);
        transform: translateX(-50%);
    }

    /* ================= STICKY NOTE ================= */
    .cassette-tape .cass-note {
        position: absolute;
        top: 0;
        left: 4%;
        width: 44%;
        min-width: 92px;
        max-width: 150px;
        padding: 8px 9px 10px;
        background:
            radial-gradient(rgba(0, 0, 0, 0.05) 1px, transparent 1px) 0 0/3px 3px,
            #fff4b8;
        color: #3a2f0a;
        box-shadow:
            1px 2px 0 rgba(0, 0, 0, 0.12),
            3px 5px 6px rgba(0, 0, 0, 0.35);
        transform: rotate(-3deg);
        z-index: 4;
    }
    /* strip of masking tape across the top, holding it to the shell */
    .cassette-tape .cass-note::before {
        content: "";
        position: absolute;
        top: -8px;
        left: 50%;
        width: 46px;
        height: 16px;
        background: rgba(226, 220, 198, 0.55);
        border-left: 1px solid rgba(0, 0, 0, 0.06);
        border-right: 1px solid rgba(0, 0, 0, 0.06);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        transform: translateX(-50%) rotate(2deg);
    }
    .cassette-tape .cass-note-title {
        font-family: 'Segoe Print', 'Bradley Hand', 'Comic Sans MS', cursive;
        font-weight: 700;
        font-size: 1.05rem;
        line-height: 1.05;
    }
    .cassette-tape .cass-note-sub {
        margin-top: 4px;
        font-family: 'Segoe Print', 'Bradley Hand', 'Comic Sans MS', cursive;
        font-size: 0.72rem;
        line-height: 1.15;
        color: #5a4c18;
    }

    /* ================= J-CARD TRACK LIST ================= */
    .cassette-tape .cass-jcard {
        display: flex;
        margin-top: 0.5rem;
        background: var(--surface-post);
        border: 1px solid var(--border-default);
        box-shadow: inset 1px 1px 0 #ffffff, 1px 1px 0 rgba(0, 0, 0, 0.12);
        color: var(--text-primary);
    }
    .cassette-tape .cass-jcard-spine {
        flex: 0 0 8px;
        background: var(--accent-dark);
    }
    .cassette-tape .cass-jcard-empty {
        flex: 1;
        padding: 0.9rem 0.8rem;
        font-size: 0.72rem;
        color: var(--text-muted);
        font-family: Tahoma, 'Segoe UI', Verdana, sans-serif;
    }

    .cassette-tape .cass-tracklist {
        flex: 1;
        min-width: 0;
        max-height: 190px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .cassette-tape .cass-track {
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
    .cassette-tape .cass-track:nth-child(even) {
        background: rgba(58, 123, 213, 0.05);
    }
    .cassette-tape .cass-track:last-child { border-bottom: 0; }
    .cassette-tape .cass-track:hover { background: var(--surface-hover); }
    .cassette-tape .cass-track.is-selected {
        background: var(--accent);
        color: #ffffff;
        border-bottom-color: var(--accent-dark);
    }
    .cassette-tape .cass-track.is-selected .cass-track-artist,
    .cassette-tape .cass-track.is-selected .cass-track-plays {
        color: rgba(255, 255, 255, 0.85);
    }
    .cassette-tape .cass-track:focus-visible {
        outline: 2px dotted var(--accent-dark);
        outline-offset: -2px;
    }

    .cassette-tape .cass-track-rank {
        flex: 0 0 auto;
        min-width: 1.4em;
        font-family: 'Courier New', Courier, monospace;
        font-weight: 700;
        font-size: 0.72rem;
        opacity: 0.75;
    }
    .cassette-tape .cass-track-main {
        flex: 1 1 auto;
        min-width: 0;
    }
    .cassette-tape .cass-track-name {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 700;
        font-size: 0.74rem;
        line-height: 1.25;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cassette-tape .cass-track-artist {
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.64rem;
        line-height: 1.2;
        color: var(--text-muted);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cassette-tape .cass-track-plays {
        flex: 0 0 auto;
        font-family: 'Courier New', Courier, monospace;
        font-size: 0.66rem;
        color: var(--text-muted);
        white-space: nowrap;
    }

    /* XP-style vertical scrollbar, same pattern as the vinyl shelf */
    .cassette-tape .cass-tracklist::-webkit-scrollbar { width: 16px; }
    .cassette-tape .cass-tracklist::-webkit-scrollbar-track {
        background: var(--surface-sunken);
        border-left: 1px solid var(--border-default);
    }
    .cassette-tape .cass-tracklist::-webkit-scrollbar-thumb {
        background: var(--surface-primary);
        border: 1px solid var(--border-default);
        border-radius: 0;
        box-shadow: inset 1px 1px 0 #ffffff, inset -1px -1px 0 var(--border-dark);
    }
    .cassette-tape .cass-tracklist::-webkit-scrollbar-thumb:hover { background: var(--surface-hover); }
    .cassette-tape .cass-tracklist::-webkit-scrollbar-button:vertical:single-button {
        width: 16px;
        height: 16px;
        background-color: var(--surface-primary);
        background-repeat: no-repeat;
        background-position: center;
        border: 1px solid var(--border-default);
        box-shadow: inset 1px 1px 0 #ffffff, inset -1px -1px 0 var(--border-dark);
    }
    .cassette-tape .cass-tracklist::-webkit-scrollbar-button:vertical:decrement {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='7' height='7' viewBox='0 0 7 7'%3E%3Cpath d='M0 5h7L3.5 1z' fill='%231e1e1e'/%3E%3C/svg%3E");
    }
    .cassette-tape .cass-tracklist::-webkit-scrollbar-button:vertical:increment {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='7' height='7' viewBox='0 0 7 7'%3E%3Cpath d='M0 2h7L3.5 6z' fill='%231e1e1e'/%3E%3C/svg%3E");
    }
    @supports not selector(::-webkit-scrollbar) {
        .cassette-tape .cass-tracklist { scrollbar-color: #c8c0b8 #e8e4dc; }
    }
</style>

<div class="xp-panel cassette-tape" id="{{ $cid }}">
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
        <div class="cass-stage">
            <div class="cass-shell" tabindex="0" role="img"
                 aria-label="Cassette tape labeled Top 8 Songs, with reels that spin while you browse the tracklist below">
                <span class="cass-screw cass-screw--tl" aria-hidden="true"></span>
                <span class="cass-screw cass-screw--tr" aria-hidden="true"></span>
                <span class="cass-screw cass-screw--bl" aria-hidden="true"></span>
                <span class="cass-screw cass-screw--br" aria-hidden="true"></span>

                <div class="cass-label">
                    <span class="cass-label-side">SIDE A</span>
                    <span class="cass-label-rule" aria-hidden="true"></span>
                    <span class="cass-label-tag">C-60</span>
                </div>

                <div class="cass-window" aria-hidden="true">
                    <span class="cass-reel">
                        <svg viewBox="0 0 40 40" class="cass-reel-spin" data-cass-reel>
                            <circle cx="20" cy="20" r="18" fill="none" stroke="#3a3a3d" stroke-width="1.5"/>
                            <circle cx="20" cy="20" r="14" fill="#151517"/>
                            <g stroke="#57574f" stroke-width="1.4">
                                <line x1="20" y1="9" x2="20" y2="31"/>
                                <line x1="9" y1="20" x2="31" y2="20"/>
                                <line x1="12.2" y1="12.2" x2="27.8" y2="27.8"/>
                                <line x1="27.8" y1="12.2" x2="12.2" y2="27.8"/>
                            </g>
                            <circle cx="20" cy="20" r="5.5" fill="#0c0c0d" stroke="#57574f" stroke-width="1"/>
                            <circle cx="20" cy="20" r="1.6" fill="#57574f"/>
                        </svg>
                    </span>
                    <span class="cass-reel">
                        <svg viewBox="0 0 40 40" class="cass-reel-spin" data-cass-reel>
                            <circle cx="20" cy="20" r="18" fill="none" stroke="#3a3a3d" stroke-width="1.5"/>
                            <circle cx="20" cy="20" r="16.5" fill="#2b2117"/>
                            <circle cx="20" cy="20" r="12" fill="#151517"/>
                            <g stroke="#57574f" stroke-width="1.4">
                                <line x1="20" y1="9" x2="20" y2="31"/>
                                <line x1="9" y1="20" x2="31" y2="20"/>
                                <line x1="12.2" y1="12.2" x2="27.8" y2="27.8"/>
                                <line x1="27.8" y1="12.2" x2="12.2" y2="27.8"/>
                            </g>
                            <circle cx="20" cy="20" r="5.5" fill="#0c0c0d" stroke="#57574f" stroke-width="1"/>
                            <circle cx="20" cy="20" r="1.6" fill="#57574f"/>
                        </svg>
                    </span>
                </div>

                <div class="cass-foot" aria-hidden="true">
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

        <div class="cass-jcard">
            <div class="cass-jcard-spine" aria-hidden="true"></div>
            @if($count === 0)
                <div class="cass-jcard-empty">No top songs found.</div>
            @else
                <div class="cass-tracklist" role="listbox" aria-label="Top 8 songs">
                    @foreach($songList as $i => $song)
                        @php
                            $rank = $i + 1;
                            $name = trim((string) ($song['name'] ?? '')) ?: 'Unknown Track';
                            $artist = trim((string) ($song['artist'] ?? '')) ?: 'Unknown Artist';
                            $plays = (int) ($song['playcount'] ?? 0);
                        @endphp
                        <button type="button" class="cass-track" role="option" aria-selected="false" data-cass-track
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
            @endif
        </div>
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
        if (index === selected) index = -1; // clicking the playing track stops it

        tracks.forEach(function (track, k) {
            var on = k === index;
            track.classList.toggle('is-selected', on);
            track.setAttribute('aria-selected', on ? 'true' : 'false');
        });

        root.classList.toggle('is-playing', index > -1);
        selected = index;
    }

    tracks.forEach(function (track, k) {
        track.addEventListener('click', function () { select(k); });
    });

    // Up/Down move focus between tracks; Escape stops playback.
    root.addEventListener('keydown', function (e) {
        var current = tracks.indexOf(document.activeElement);

        if (e.key === 'Escape' && selected > -1) {
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
})();
</script>
@endif
