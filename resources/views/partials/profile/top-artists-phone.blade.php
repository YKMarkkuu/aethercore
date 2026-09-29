{{--
    TOP 8 ARTISTS - ROTARY PHONE

    Expects: $artists = [ ['name' =>, 'image' =>, 'playcount' =>], ... ]
    Usage:   @include('partials.profile.top-artists-phone', ['artists' => $user->lastfm_data['top_artists'] ?? []])

    Self-contained: CSS is scoped under .rotary-phone, JS is an IIFE keyed
    to a per-render id. Replaces the whole old "Top 8 Artists" xp-panel.
    Realistic skeuomorphic styling: warm Bakelite and paper materials,
    softly rounded forms, subtle gradients and stacked shadows.
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
     /* Warm, softly shaded materials stay scoped to this component. */
    .rotary-phone [hidden] { display: none !important; }

    .rotary-phone .rp-header {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    .rotary-phone .rp-header svg { flex-shrink: 0; }

    .rotary-phone .rp-stage {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-start;
        gap: 16px;
        padding-top: 6px;
    }

    /* ================= PHONE ================= */
    .rotary-phone .rp-phone {
        position: relative;
        flex: 0 0 auto;
        width: 200px;
        max-width: 100%;
        margin: 0 auto;
    }

    /* handset, resting diagonally across the top of the base */
    .rotary-phone .rp-handset {
        position: relative;
        width: 86%;
        height: 24px;
        margin: 0 auto 10px;
        border-radius: 12px;
        background: linear-gradient(180deg, #4c453b 0%, #332e27 55%, #221e19 100%);
        box-shadow: 1px 1px 0 rgba(255, 255, 255, 0.08) inset, 2px 4px 5px rgba(0, 0, 0, 0.4);
        transform: rotate(-3deg);
    }
    .rotary-phone .rp-handset::before,
    .rotary-phone .rp-handset::after {
        content: "";
        position: absolute;
        top: 50%;
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: radial-gradient(circle at 35% 30%, #574f43 0%, #332e27 65%, #221e19 100%);
        box-shadow: 1px 2px 3px rgba(0, 0, 0, 0.45);
        transform: translateY(-50%);
    }
    .rotary-phone .rp-handset::before { left: -10px; }
    .rotary-phone .rp-handset::after { right: -10px; }

    /* base plate */
    .rotary-phone .rp-base {
        position: relative;
        aspect-ratio: 1 / 0.86;
        border-radius: 10px;
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.08) 0%, transparent 28%), linear-gradient(150deg, #4c453b 0%, #332e27 55%, #221e19 100%);
        box-shadow: 1px 1px 0 #171410, 2px 2px 0 #100e0b, 5px 7px 9px rgba(0, 0, 0, 0.45);
    }
    .rotary-phone .rp-base::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: inherit;
        pointer-events: none;
        box-shadow: inset 1px 1px 0 rgba(255, 255, 255, 0.12), inset -1px -1px 0 rgba(0, 0, 0, 0.35);
    }

    /* raised dial rim + plate */
    .rotary-phone .rp-dial-rim {
        position: absolute;
        top: 9%;
        left: 50%;
        width: 76%;
        aspect-ratio: 1 / 1;
        border-radius: 50%;
        transform: translateX(-50%);
        background: linear-gradient(150deg, #5c5449 0%, #3a342b 100%);
        box-shadow: 1px 1px 0 rgba(255, 255, 255, 0.08) inset, 2px 3px 5px rgba(0, 0, 0, 0.4);
    }
    .rotary-phone .rp-dial-plate {
        position: absolute;
        inset: 9%;
        border-radius: 50%;
        background: radial-gradient(circle at 38% 32%, #f5edcf 0%, #e6dcbc 55%, #d8cca4 100%);
        box-shadow: inset 0 1px 2px rgba(255, 255, 255, 0.6), inset 0 -3px 6px rgba(0, 0, 0, 0.18);
        transition: transform 160ms ease-out;
    }
    .rotary-phone .rp-dial-plate::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 19%;
        height: 19%;
        border-radius: 50%;
        background: radial-gradient(circle at 35% 30%, #6a6255 0%, #3a342b 75%);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.45);
        transform: translate(-50%, -50%);
    }

    /* Finger holes remain upright as they are positioned around the dial. */
    .rotary-phone .rp-hole {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 18%;
        height: 18%;
        margin: -9% 0 0 -9%;
        border: 0;
        border-radius: 50%;
        background: radial-gradient(circle at 50% 35%, #2c2822 0%, #17140f 70%);
        box-shadow: inset 0 2px 3px rgba(0, 0, 0, 0.75), inset 0 -1px 0 rgba(255, 255, 255, 0.06);
        color: #efe8c6;
        display: flex;
        align-items: center;
        justify-content: center;
        font: 600 0.7rem/1 Tahoma, 'Segoe UI', Verdana, sans-serif;
        cursor: pointer;
        padding: 0;
        transition: transform 120ms ease-out, box-shadow 120ms ease-out;
        /* --a: angle for this hole, --r: radius from centre, set inline */
        transform: rotate(var(--a)) translate(0, var(--r)) rotate(calc(-1 * var(--a)));
    }
    @media (hover: hover) {
        .rotary-phone .rp-hole:hover:not(.is-selected) {
            transform: rotate(var(--a)) translate(0, calc(var(--r) - 3px)) rotate(calc(-1 * var(--a)));
        }
    }
    .rotary-phone .rp-hole:focus-visible {
        outline: 2px dotted var(--accent-dark, #1a4a9e);
        outline-offset: 2px;
    }
    .rotary-phone .rp-hole.is-selected {
        background: radial-gradient(circle at 50% 35%, var(--accent, #3a7bd5) 0%, var(--accent-dark, #1a4a9e) 75%);
        box-shadow: inset 0 2px 3px rgba(0, 0, 0, 0.45), inset 0 -1px 0 rgba(255, 255, 255, 0.15);
    }

    /* Finger-stop tab, bottom-right of the rim. */
    .rotary-phone .rp-stop {
        position: absolute;
        bottom: 5%;
        right: -1%;
        width: 10%;
        height: 20%;
        border-radius: 3px;
        background: linear-gradient(150deg, #4c453b, #221e19);
        box-shadow: 1px 2px 3px rgba(0, 0, 0, 0.45);
        z-index: 2;
    }

    /* Cradle switch at the bottom of the base. */
    .rotary-phone .rp-cradle {
        position: absolute;
        left: 50%;
        bottom: -5px;
        width: 28%;
        height: 9px;
        border-radius: 3px;
        background: linear-gradient(150deg, #332e27, #17140f);
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
        transform: translateX(-50%);
    }

    @media (prefers-reduced-motion: reduce) {
        .rotary-phone .rp-dial-plate,
        .rotary-phone .rp-hole { transition: none; }
    }

    /* ================= SPEECH BUBBLE ================= */
    .rotary-phone .rp-bubble-wrap {
        flex: 1 1 190px;
        min-width: 190px;
    }
    .rotary-phone .rp-bubble {
        position: relative;
        background: radial-gradient(rgba(0, 0, 0, 0.035) 1px, transparent 1px) 0 0/3px 3px, var(--surface-post, #f8f5ec);
        border-radius: 6px;
        box-shadow: 1px 1px 0 rgba(0, 0, 0, 0.06), 2px 4px 7px rgba(0, 0, 0, 0.18);
        padding: 11px;
    }
    .rotary-phone .rp-bubble::before {
        content: "";
        position: absolute;
        top: 24px;
        left: -11px;
        width: 18px;
        height: 18px;
        background: inherit;
        border-radius: 4px;
        transform: rotate(45deg);
        box-shadow: -1px 1px 2px rgba(0, 0, 0, 0.08);
    }
    @media (max-width: 560px) {
        .rotary-phone .rp-bubble-wrap { padding-top: 16px; }
        .rotary-phone .rp-bubble::before {
            top: -9px;
            left: 26px;
        }
    }

    .rotary-phone .rp-bubble-top {
        display: flex;
        gap: 8px;
        align-items: flex-start;
    }
    .rotary-phone .rp-photo {
        position: relative;
        flex: 0 0 auto;
        width: 56px;
        height: 56px;
        border-radius: 4px;
        background: #cdbd94;
        box-shadow: inset 1px 1px 0 rgba(255, 255, 255, 0.5), 1px 1px 3px rgba(0, 0, 0, 0.25);
        overflow: hidden;
    }
    .rotary-phone .rp-photo img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-fit: cover;
    }
    .rotary-phone .rp-photo svg { position: absolute; inset: 0; width: 100%; height: 100%; }

    .rotary-phone .rp-info { flex: 1; min-width: 0; }
    .rotary-phone .rp-rank-badge {
        display: inline-block;
        padding: 0 6px;
        margin-bottom: 4px;
        border-radius: 3px;
        background: var(--accent-dark, #1a4a9e);
        color: #ffffff;
        box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.2);
        font: 700 0.6rem/1.4 Tahoma, 'Segoe UI', Verdana, sans-serif;
    }
    .rotary-phone .rp-name {
        font: 700 0.85rem/1.2 Tahoma, 'Segoe UI', Verdana, sans-serif;
        color: #1e1e1e;
        overflow-wrap: anywhere;
    }

    .rotary-phone .rp-stat {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 9px;
        padding: 4px 8px;
        border-radius: 3px;
        background: #efe8c6;
        box-shadow: inset 1px 1px 0 rgba(255, 255, 255, 0.7), inset 0 -1px 0 rgba(0, 0, 0, 0.1);
        width: max-content;
        max-width: 100%;
    }
    .rotary-phone .rp-stat strong {
        font: 700 0.9rem/1 Tahoma, 'Segoe UI', Verdana, sans-serif;
        color: var(--accent-dark, #1a4a9e);
    }
    .rotary-phone .rp-stat span {
        font: 600 0.6rem/1 Tahoma, 'Segoe UI', Verdana, sans-serif;
        color: #6a6a6a;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .rotary-phone .rp-empty {
        padding: 0.6rem 0;
        font-size: 0.75rem;
        color: var(--text-muted);
        font-family: Tahoma, 'Segoe UI', Verdana, sans-serif;
    }
</style>

<div class="xp-panel rotary-phone" id="{{ $pid }}">
    <div class="xp-panel-header rp-header">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>
        </svg>
        Top 8 Artists
    </div>

    <div class="xp-panel-body">
        @if($count === 0)
            <div class="rp-empty">No top artists found.</div>
        @else
            {{-- Shared portrait fallback for artists without a photo. --}}
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
                    <circle cx="10" cy="7" r="3.5" fill="url(#{{ $pid }}-portrait-figure)"/>
                    <path d="M3 19c.35-4.55 3.05-7 7-7s6.65 2.45 7 7H3Z" fill="url(#{{ $pid }}-portrait-figure)"/>
                </symbol>
            </svg>

            <div class="rp-stage">
                <div class="rp-phone">
                    <div class="rp-handset" aria-hidden="true"></div>
                    <div class="rp-base">
                        <div class="rp-dial-rim">
                            <div class="rp-dial-plate" data-rp-dial>
                                @foreach($artistList as $i => $artist)
                                    @php
                                        $rank = $i + 1;
                                        $angle = -158 + ($i * 45); // 1 near 11 o'clock, sweeping clockwise to 8
                                        $name = trim((string) ($artist['name'] ?? '')) ?: 'Unknown Artist';
                                        $plays = (int) ($artist['playcount'] ?? 0);
                                        $img = $isRealImage($artist['image'] ?? null) ? $artist['image'] : null;
                                    @endphp
                                    <button type="button"
                                            class="rp-hole @if($i === 0) is-selected @endif"
                                            style="--a: {{ $angle }}deg; --r: -122%;"
                                            data-rp-hole
                                            data-rank="{{ $rank }}"
                                            data-name="{{ $name }}"
                                            data-image="{{ $img }}"
                                            data-plays="{{ number_format($plays) }}"
                                            aria-pressed="{{ $i === 0 ? 'true' : 'false' }}"
                                            aria-label="Dial {{ $rank }}: {{ $name }}, {{ number_format($plays) }} scrobbles">{{ $rank }}</button>
                                @endforeach
                            </div>
                        </div>
                        <span class="rp-stop" aria-hidden="true"></span>
                        <span class="rp-cradle" aria-hidden="true"></span>
                    </div>
                </div>

                <div class="rp-bubble-wrap">
                    <div class="rp-bubble" role="status" aria-live="polite">
                        <div class="rp-bubble-top">
                            <span class="rp-photo" data-rp-photo>
                                <svg aria-hidden="true"><use href="#{{ $pid }}-blank"/></svg>
                            </span>
                            <div class="rp-info">
                                <span class="rp-rank-badge" data-rp-rank>No. 1</span>
                                <div class="rp-name" data-rp-name></div>
                            </div>
                        </div>
                        <div class="rp-stat">
                            <strong data-rp-plays></strong>
                            <span>scrobbles</span>
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

    var dial = root.querySelector('[data-rp-dial]');
    var holes = Array.prototype.slice.call(root.querySelectorAll('[data-rp-hole]'));
    var photoWrap = root.querySelector('[data-rp-photo]');
    var blankHref = '#' + @json($pid) + '-blank';
    var rankEl = root.querySelector('[data-rp-rank]');
    var nameEl = root.querySelector('[data-rp-name]');
    var playsEl = root.querySelector('[data-rp-plays]');

    function render(hole) {
        rankEl.textContent = 'No. ' + hole.dataset.rank;
        nameEl.textContent = hole.dataset.name;
        playsEl.textContent = hole.dataset.plays;

        photoWrap.innerHTML = '';
        if (hole.dataset.image) {
            var img = document.createElement('img');
            img.src = hole.dataset.image;
            img.alt = '';
            img.loading = 'lazy';
            img.onerror = function () {
                photoWrap.innerHTML = '<svg aria-hidden="true"><use href="' + blankHref + '"/></svg>';
            };
            photoWrap.appendChild(img);
        } else {
            photoWrap.innerHTML = '<svg aria-hidden="true"><use href="' + blankHref + '"/></svg>';
        }
    }

    function select(hole) {
        holes.forEach(function (h) {
            var on = h === hole;
            h.classList.toggle('is-selected', on);
            h.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        render(hole);

        // Rotate the dial so the clicked hole swings toward the finger-stop,
        // then spring back to rest - a rotary "pull and release".
        var targetAngle = parseFloat(hole.style.getPropertyValue('--a'));
        dial.style.transform = 'rotate(' + (targetAngle * -0.32) + 'deg)';
        window.clearTimeout(dial._rpSettle);
        dial._rpSettle = window.setTimeout(function () {
            dial.style.transform = 'rotate(0deg)';
        }, 170);
    }

    holes.forEach(function (hole) {
        hole.addEventListener('click', function () { select(hole); });
    });

    // Left/Right cycle through dial positions 1-8; number keys jump directly.
    root.addEventListener('keydown', function (e) {
        var current = holes.indexOf(document.activeElement);
        if (/^[1-8]$/.test(e.key)) {
            var byRank = holes[parseInt(e.key, 10) - 1];
            if (byRank) { byRank.focus(); select(byRank); }
            return;
        }
        if (current < 0) return;

        var next = current;
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') next = Math.min(holes.length - 1, current + 1);
        else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') next = Math.max(0, current - 1);
        else return;

        e.preventDefault();
        holes[next].focus();
    });

    render(holes[0]);
})();
</script>
@endif
