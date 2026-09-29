{{--
    TOP 8 ARTISTS - ROTARY PHONE

    Expects: $artists = [ ['name' =>, 'image' =>, 'playcount' =>], ... ]
    Usage:   @include('partials.profile.top-artists-phone', ['artists' => $user->lastfm_data['top_artists'] ?? []])

    Self-contained: CSS is scoped under .rotary-phone, JS is an IIFE keyed
    to a per-render id. Replaces the whole old "Top 8 Artists" xp-panel.
    Pixel-game aesthetic: chunky black outlines, flat fills, hard-edge
    offset shadows, no border-radius, no blur, no gradients.
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
    /* =====================================================
       ROTARY PHONE - scoped under .rotary-phone so the
       midnight theme's !important overrides can't reach the
       base, dial or speech bubble. Pixel-game rules: every
       shape gets a solid 2-3px black border, shadows are flat
       offset blocks (no blur), fills are flat (no gradients).
       ===================================================== */
    .rotary-phone { --pg-ink: #000000; }
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
        gap: 14px;
        padding-top: 4px;
    }

    /* ================= PHONE ================= */
    .rotary-phone .rp-phone {
        position: relative;
        flex: 0 0 auto;
        width: 210px;
        max-width: 100%;
    }

    /* handset, resting diagonally across the top of the base */
    .rotary-phone .rp-handset {
        position: relative;
        width: 86%;
        height: 26px;
        margin: 0 auto 6px;
        background: #4a4640;
        border: 3px solid var(--pg-ink);
        box-shadow: 3px 3px 0 var(--pg-ink);
        transform: rotate(-3deg);
    }
    .rotary-phone .rp-handset::before,
    .rotary-phone .rp-handset::after {
        content: "";
        position: absolute;
        top: 50%;
        width: 22px;
        height: 22px;
        background: #4a4640;
        border: 3px solid var(--pg-ink);
        transform: translateY(-50%) rotate(45deg);
        /* octagon-ish earpiece: clipped square reads as chunky-round in pixel style */
        clip-path: polygon(30% 0, 70% 0, 100% 30%, 100% 70%, 70% 100%, 30% 100%, 0 70%, 0 30%);
    }
    .rotary-phone .rp-handset::before { left: -8px; }
    .rotary-phone .rp-handset::after { right: -8px; }

    /* base plate */
    .rotary-phone .rp-base {
        position: relative;
        aspect-ratio: 1 / 0.86;
        background: #5a5650;
        border: 3px solid var(--pg-ink);
        box-shadow: 5px 5px 0 var(--pg-ink);
    }
    .rotary-phone .rp-base::before {
        /* flat "shade" panel instead of a gradient, for pseudo-3D */
        content: "";
        position: absolute;
        left: 3px;
        right: 3px;
        bottom: 3px;
        height: 22%;
        background: #46423c;
        border-top: 3px solid var(--pg-ink);
    }

    /* raised dial rim + plate */
    .rotary-phone .rp-dial-rim {
        position: absolute;
        top: 8%;
        left: 50%;
        width: 78%;
        aspect-ratio: 1 / 1;
        transform: translateX(-50%);
        background: #706a5f;
        border: 3px solid var(--pg-ink);
        box-shadow: 3px 3px 0 var(--pg-ink);
    }
    .rotary-phone .rp-dial-plate {
        position: absolute;
        inset: 9%;
        background: #efe8c6;
        border: 3px solid var(--pg-ink);
        transition: transform 160ms steps(6, end);
    }
    .rotary-phone .rp-dial-plate::after {
        /* centre hub */
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 20%;
        height: 20%;
        background: #5a5650;
        border: 3px solid var(--pg-ink);
        transform: translate(-50%, -50%);
    }

    /* finger holes, positioned by rotate() + translate() around the plate,
       then counter-rotated so the digit stays upright */
    .rotary-phone .rp-hole {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 19%;
        height: 19%;
        margin: -9.5% 0 0 -9.5%;
        background: #1c1a17;
        border: 2px solid var(--pg-ink);
        color: #efe8c6;
        display: flex;
        align-items: center;
        justify-content: center;
        font: 700 0.72rem/1 'Courier New', Courier, monospace;
        cursor: pointer;
        padding: 0;
        transition: transform 90ms linear;
        /* --a: angle for this hole, --r: radius from centre, set inline */
        transform: rotate(var(--a)) translate(0, var(--r)) rotate(calc(-1 * var(--a)));
    }
    @media (hover: hover) {
        .rotary-phone .rp-hole:hover:not(.is-selected) {
            transform: rotate(var(--a)) translate(0, calc(var(--r) - 3px)) rotate(calc(-1 * var(--a)));
        }
    }
    .rotary-phone .rp-hole:focus-visible {
        outline: 2px dotted #ffffff;
        outline-offset: -4px;
    }
    .rotary-phone .rp-hole.is-selected {
        background: var(--accent-dark);
        border-color: var(--pg-ink);
    }

    /* finger-stop tab, bottom-right of the rim */
    .rotary-phone .rp-stop {
        position: absolute;
        bottom: 4%;
        right: -2%;
        width: 12%;
        height: 22%;
        background: #2a2824;
        border: 3px solid var(--pg-ink);
        box-shadow: 2px 2px 0 var(--pg-ink);
        z-index: 2;
    }

    /* cradle switch + cord stub, bottom of the base, purely decorative */
    .rotary-phone .rp-cradle {
        position: absolute;
        left: 50%;
        bottom: -6px;
        width: 30%;
        height: 10px;
        background: #2a2824;
        border: 3px solid var(--pg-ink);
        transform: translateX(-50%);
    }

    @media (prefers-reduced-motion: reduce) {
        .rotary-phone .rp-dial-plate,
        .rotary-phone .rp-hole { transition: none; }
    }

    /* ================= SPEECH BUBBLE ================= */
    .rotary-phone .rp-bubble-wrap {
        flex: 1 1 180px;
        min-width: 180px;
        padding-left: 4px;
    }
    .rotary-phone .rp-bubble {
        position: relative;
        background: #fdf9e8;
        border: 3px solid var(--pg-ink);
        box-shadow: 5px 5px 0 var(--pg-ink);
        padding: 10px;
    }
    /* comic pointer, aimed back at the phone: two stacked hard triangles */
    .rotary-phone .rp-bubble::before,
    .rotary-phone .rp-bubble::after {
        content: "";
        position: absolute;
        top: 22px;
        border-style: solid;
    }
    .rotary-phone .rp-bubble::before {
        left: -17px;
        border-width: 10px 17px 10px 0;
        border-color: transparent var(--pg-ink) transparent transparent;
    }
    .rotary-phone .rp-bubble::after {
        left: -12px;
        border-width: 8px 13px 8px 0;
        border-color: transparent #fdf9e8 transparent transparent;
    }
    @media (max-width: 560px) {
        .rotary-phone .rp-bubble-wrap { padding-left: 0; padding-top: 14px; }
        .rotary-phone .rp-bubble::before,
        .rotary-phone .rp-bubble::after {
            top: -17px;
            left: 24px;
            border-width: 0 10px 17px 10px;
            border-color: transparent transparent var(--pg-ink) transparent;
        }
        .rotary-phone .rp-bubble::after {
            top: -12px;
            left: 24px;
            border-width: 0 8px 13px 8px;
            border-color: transparent transparent #fdf9e8 transparent;
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
        background: #cdbd94;
        border: 3px solid var(--pg-ink);
        overflow: hidden;
    }
    .rotary-phone .rp-photo img {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        image-rendering: pixelated;
    }
    .rotary-phone .rp-photo svg { position: absolute; inset: 0; width: 100%; height: 100%; }

    .rotary-phone .rp-info { flex: 1; min-width: 0; }
    .rotary-phone .rp-rank-badge {
        display: inline-block;
        padding: 0 5px;
        margin-bottom: 3px;
        background: #1c1a17;
        color: #efe8c6;
        border: 2px solid var(--pg-ink);
        font: 700 0.6rem/1.4 'Courier New', Courier, monospace;
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
        margin-top: 8px;
        padding: 3px 7px;
        background: #efe8c6;
        border: 2px solid var(--pg-ink);
        width: max-content;
        max-width: 100%;
    }
    .rotary-phone .rp-stat strong {
        font: 700 0.85rem/1 'Courier New', Courier, monospace;
        color: #1e1e1e;
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
            {{-- shared pixel-person silhouette, reused when an artist has no Deezer photo --}}
            <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
                <symbol id="{{ $pid }}-blank" viewBox="0 0 20 20" shape-rendering="crispEdges">
                    <rect width="20" height="20" fill="#cdbd94"/>
                    <rect x="7" y="3" width="6" height="6" fill="#5a5650"/>
                    <rect x="4" y="10" width="12" height="7" fill="#5a5650"/>
                    <rect x="4" y="10" width="12" height="2" fill="#46423c"/>
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
