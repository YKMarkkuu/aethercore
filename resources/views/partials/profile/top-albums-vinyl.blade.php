{{--
    TOP 8 ALBUMS - VINYL SHELF

    Expects: $albums = [ ['name' =>, 'artist' =>, 'image' =>, 'playcount' =>], ... ]
    Usage:   @include('partials.profile.top-albums-vinyl', ['albums' => $user->lastfm_data['top_albums'] ?? []])
--}}
@php
    $vid = 'vinyl-' . \Illuminate\Support\Str::random(6);
    $albumList = collect($albums ?? [])->take(8)->values();
    $count = $albumList->count();
    $totalPlays = (int) $albumList->sum(fn ($a) => (int) ($a['playcount'] ?? 0));

    $coverUrl = function ($url) {
        if (empty($url)) {
            return null;
        }
        foreach (['2a96cbd8b46e442fc41c2b86b821562f', 'avatar_default', 'default_avatar', 'noimage', 'placeholder'] as $needle) {
            if (str_contains($url, $needle)) {
                return null;
            }
        }
        if (str_contains($url, 'lastfm.freetls.fastly.net/i/u/')) {
            $url = preg_replace('/\/i\/u\/\d+s\//', '/i/u/300x300/', $url);
            $url = preg_replace('/\/i\/u\/\d+x\d+\//', '/i/u/300x300/', $url);
        }
        return $url;
    };
@endphp

<style>
    .vinyl-shelf {
        --v-gap: 14px;
        --v-edge: 6px;
        --v-bar: 40px;
        --v-seat: 8px;
    }
    .vinyl-shelf [hidden] { display: none !important; }
    .vinyl-shelf .vinyl-header,
    .vinyl-shelf .vinyl-header-title,
    .vinyl-shelf .vinyl-hint {
        display: flex;
        align-items: center;
    }
    .vinyl-shelf .vinyl-header { justify-content: space-between; gap: 0.5rem; }
    .vinyl-shelf .vinyl-header-title,
    .vinyl-shelf .vinyl-hint { gap: 0.35rem; }
    .vinyl-shelf .vinyl-header svg { flex-shrink: 0; }
    .vinyl-shelf .vinyl-hint { font-size: 0.58rem; font-weight: 400; opacity: 0.7; }
    .vinyl-shelf .vinyl-stage {
        position: relative;
        border: 2px solid #3a2e1f;
        background: repeating-linear-gradient(90deg, rgba(0,0,0,.14) 0 1px, transparent 1px 26px), linear-gradient(180deg, #2a2217 0%, #3b3123 65%, #45392a 100%);
        box-shadow: inset 0 8px 10px rgba(0,0,0,.55), inset 4px 0 6px rgba(0,0,0,.35), inset -4px 0 6px rgba(0,0,0,.35);
    }
    .vinyl-shelf .vinyl-scroller {
        --v-sleeve: clamp(104px, calc((100% - var(--v-edge) - 5 * var(--v-gap)) / 4.3), 190px);
        position: relative;
        display: grid;
        grid-template-columns: var(--v-edge) repeat(var(--count), var(--v-sleeve)) minmax(var(--v-edge), 1fr);
        grid-template-rows: auto var(--v-bar);
        column-gap: var(--v-gap);
        padding-top: 24px;
        overflow-x: auto;
        overflow-y: hidden;
        scroll-snap-type: x mandatory;
        scroll-padding-left: calc(var(--v-edge) + var(--v-gap));
        overscroll-behavior-x: contain;
        cursor: grab;
    }
    .vinyl-shelf .vinyl-scroller.is-dragging { scroll-snap-type: none; cursor: grabbing; user-select: none; }
    .vinyl-shelf .vinyl-scroller.is-dragging .vinyl-sleeve { pointer-events: none; }
    .vinyl-shelf .vinyl-scroller::-webkit-scrollbar { height: 16px; }
    .vinyl-shelf .vinyl-scroller::-webkit-scrollbar-track { background: var(--surface-sunken); border-top: 1px solid var(--border-default); }
    .vinyl-shelf .vinyl-scroller::-webkit-scrollbar-thumb {
        background: var(--surface-primary);
        border: 1px solid var(--border-default);
        border-radius: 0;
        box-shadow: inset 1px 1px 0 #fff, inset -1px -1px 0 var(--border-dark);
    }
    .vinyl-shelf .vinyl-scroller::-webkit-scrollbar-thumb:hover { background: var(--surface-hover); }
    .vinyl-shelf .vinyl-bar {
        grid-row: 2;
        grid-column: 1 / -1;
        position: relative;
        z-index: 0;
        background: repeating-linear-gradient(180deg, rgba(0,0,0,.07) 0 1px, transparent 1px 5px), repeating-linear-gradient(180deg, transparent 0 9px, rgba(255,255,255,.05) 9px 10px), linear-gradient(180deg, #8b6f47 0%, #5c4a2e 100%);
        box-shadow: inset 2px 0 0 rgba(255,255,255,.12), inset -2px 0 0 rgba(0,0,0,.3), 0 3px 0 #2e2416;
    }
    .vinyl-shelf .vinyl-bar::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 10px;
        background: linear-gradient(180deg, #a88a5d, #95774d);
        border-bottom: 1px solid rgba(0,0,0,.4);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.25);
    }
    .vinyl-shelf .vinyl-slot {
        grid-row: 1;
        align-self: start;
        margin-bottom: calc(-1 * var(--v-seat));
        position: relative;
        z-index: 2;
        scroll-snap-align: start;
    }
    .vinyl-shelf .vinyl-slot.is-selected { z-index: 3; }
    .vinyl-shelf .vinyl-sleeve {
        --lean: -4deg;
        --tilt: 0deg;
        --lift: 0px;
        position: relative;
        display: block;
        width: 100%;
        aspect-ratio: 1 / 1;
        margin: 0;
        padding: 0;
        border: 0;
        border-radius: 0;
        font: inherit;
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
        background-color: #e6dfc4;
        background-image: radial-gradient(rgba(0,0,0,.04) 1px, transparent 1px);
        background-size: 3px 3px;
        box-shadow: 1px 1px 0 #c9c0a0, 2px 2px 0 #b3aa8c, 3px 3px 0 #9a9178, 5px 6px 7px rgba(0,0,0,.45);
        transform: perspective(720px) translateY(var(--lift)) rotateX(var(--lean)) rotateZ(var(--tilt));
        transform-origin: 50% 100%;
        transition: transform .14s ease-out, box-shadow .14s ease-out;
    }
    .vinyl-shelf .vinyl-sleeve::after {
        content: "";
        position: absolute;
        inset: 0;
        pointer-events: none;
        box-shadow: inset 1px 1px 0 rgba(255,255,255,.7), inset -1px -1px 0 rgba(0,0,0,.18);
    }
    .vinyl-shelf .vinyl-sleeve::before {
        content: "";
        position: absolute;
        inset: 5px;
        z-index: 1;
        pointer-events: none;
        background: radial-gradient(circle at 50% 50%, transparent 0, transparent 66%, rgba(255,255,255,.12) 67%, rgba(0,0,0,.09) 69%, transparent 70%);
    }
    @media (hover: hover) {
        .vinyl-shelf .vinyl-sleeve:hover {
            --lean: -6deg;
            --tilt: -2deg;
            --lift: -2px;
            box-shadow: 1px 1px 0 #c9c0a0, 2px 2px 0 #b3aa8c, 3px 3px 0 #9a9178, 7px 9px 9px rgba(0,0,0,.45);
        }
    }
    .vinyl-shelf .vinyl-slot.is-selected .vinyl-sleeve,
    .vinyl-shelf .vinyl-slot.is-selected .vinyl-sleeve:hover {
        --lean: -2deg;
        --tilt: 0deg;
        --lift: -8px;
        box-shadow: 1px 1px 0 #c9c0a0, 2px 2px 0 #b3aa8c, 3px 3px 0 #9a9178, 10px 15px 14px rgba(0,0,0,.5);
    }
    .vinyl-shelf .vinyl-sleeve:focus-visible { outline: 2px dotted #f0edd8; outline-offset: 3px; }
    .vinyl-shelf .vinyl-cover { position: absolute; inset: 5px; overflow: hidden; background: #cdbd94; }
    .vinyl-shelf .vinyl-cover img {
        position: absolute;
        inset: 0;
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        outline: 1px solid rgba(0,0,0,.45);
        outline-offset: -1px;
        user-select: none;
        -webkit-user-drag: none;
    }
    .vinyl-shelf .vinyl-blank { position: absolute; inset: 0; width: 100%; height: 100%; }
    .vinyl-shelf .vinyl-badge {
        position: absolute;
        top: 9px;
        left: 9px;
        z-index: 2;
        min-width: 1.6em;
        padding: 1px 4px;
        background: #f3e8a6;
        color: #2a2418;
        border: 1px solid #6b5d2a;
        box-shadow: 1px 1px 0 rgba(0,0,0,.35);
        font: 700 .62rem/1.2 Tahoma, 'Segoe UI', Verdana, sans-serif;
        text-align: center;
        pointer-events: none;
    }
    .vinyl-shelf .vinyl-plate {
        grid-row: 2;
        z-index: 1;
        align-self: end;
        justify-self: center;
        max-width: calc(100% - 6px);
        margin-bottom: 4px;
        padding: 1px 6px 2px;
        background: #efe8c6;
        color: #2a2418;
        border: 1px solid #2e2416;
        box-shadow: inset 1px 1px 0 rgba(255,255,255,.7), 1px 1px 0 rgba(0,0,0,.35);
        font: 600 .62rem/1.25 Tahoma, 'Segoe UI', Verdana, sans-serif;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        pointer-events: none;
    }
    .vinyl-shelf .vinyl-slot.is-selected + .vinyl-plate {
        background: var(--accent);
        color: #fff;
        border-color: var(--accent-dark);
        box-shadow: inset 1px 1px 0 rgba(255,255,255,.3), 1px 1px 0 rgba(0,0,0,.35);
    }
    .vinyl-shelf .vinyl-stage--empty { height: 96px; }
    .vinyl-shelf .vinyl-stage--empty .vinyl-bar { position: absolute; left: 0; right: 0; bottom: 0; height: var(--v-bar); }
    .vinyl-shelf .vinyl-empty-note {
        position: absolute;
        left: 12px;
        bottom: calc(var(--v-bar) - 6px);
        margin: 0;
        padding: 3px 8px;
        background: #efe8c6;
        color: #2a2418;
        border: 1px solid #2e2416;
        box-shadow: inset 1px 1px 0 rgba(255,255,255,.7), 2px 2px 0 rgba(0,0,0,.35);
        font: 600 .68rem/1.3 Tahoma, 'Segoe UI', Verdana, sans-serif;
    }
    .vinyl-shelf .vinyl-spine {
        margin-top: .45rem;
        background: var(--surface-post);
        border: 1px solid var(--border-default);
        box-shadow: inset 1px 1px 0 #fff, 1px 1px 0 rgba(0,0,0,.12);
        color: var(--text-primary);
        font-family: Tahoma, 'Segoe UI', Verdana, sans-serif;
    }
    .vinyl-shelf .vinyl-spine-empty { padding: .9rem .8rem; font-size: .72rem; color: var(--text-muted); }
    .vinyl-shelf .vinyl-spine-body { display: flex; flex-wrap: wrap; align-items: stretch; }
    .vinyl-shelf .vinyl-spine-band {
        flex: 0 0 46px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1px;
        padding: .4rem 0;
        background: var(--accent-dark);
        color: #fff;
    }
    .vinyl-shelf .vinyl-spine-band small { font-size: .5rem; opacity: .8; }
    .vinyl-shelf .vinyl-spine-band strong { font-size: 1.15rem; line-height: 1; font-weight: 700; }
    .vinyl-shelf .vinyl-spine-main { flex: 1 1 130px; min-width: 0; padding: .5rem .75rem; }
    .vinyl-shelf .vinyl-spine-title { font-size: .95rem; font-weight: 700; line-height: 1.2; overflow-wrap: anywhere; }
    .vinyl-shelf .vinyl-spine-artist { margin-top: .1rem; font-size: .75rem; color: var(--text-muted); overflow-wrap: anywhere; }
    .vinyl-shelf .vinyl-spine-meter-label { margin: .45rem 0 .15rem; font-size: .62rem; color: var(--text-muted); }
    .vinyl-shelf .vinyl-spine-track { height: 14px; padding: 1px; background: #fff; border: 1px solid var(--border-dark); box-shadow: inset 1px 1px 0 rgba(0,0,0,.2); }
    .vinyl-shelf .vinyl-spine-fill { height: 100%; width: 0; background: repeating-linear-gradient(90deg, #33a933 0 7px, transparent 7px 9px); }
    .vinyl-shelf .vinyl-spine-count {
        flex: 0 0 auto;
        display: flex;
        flex-direction: column;
        justify-content: center;
        padding: .5rem .9rem;
        text-align: right;
        border-left: 1px dashed var(--border-default);
    }
    .vinyl-shelf .vinyl-spine-count strong { font-size: 1.3rem; line-height: 1.1; font-weight: 700; color: var(--accent-dark); }
    .vinyl-shelf .vinyl-spine-count small { font-size: .6rem; color: var(--text-muted); }
    @media (prefers-reduced-motion: reduce) { .vinyl-shelf .vinyl-sleeve { transition: none; } }
</style>

<div class="xp-panel vinyl-shelf" id="{{ $vid }}">
    <div class="xp-panel-header vinyl-header">
        <span class="vinyl-header-title">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/><path d="M12 6a6 6 0 0 1 6 6"/>
            </svg>
            Top 8 Albums
        </span>
        @if($count > 0)
            <span class="vinyl-hint">
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
                Drag or scroll
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
            </span>
        @endif
    </div>
    <div class="xp-panel-body">
        @if($count === 0)
            <div class="vinyl-stage vinyl-stage--empty">
                <div class="vinyl-bar" aria-hidden="true"></div>
                <p class="vinyl-empty-note">No top albums found.</p>
            </div>
        @else
            <svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false">
                <symbol id="{{ $vid }}-blank" viewBox="0 0 100 100">
                    <circle cx="50" cy="50" r="38" fill="#1e1e1e"/><circle cx="50" cy="50" r="34" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="0.8"/>
                    <circle cx="50" cy="50" r="29" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="0.8"/><circle cx="50" cy="50" r="24" fill="none" stroke="rgba(255,255,255,0.12)" stroke-width="0.8"/>
                    <circle cx="50" cy="50" r="14" fill="#a8452c"/><circle cx="50" cy="50" r="2.2" fill="#e6dfc4"/><path d="M22 38a30 30 0 0 1 16-16" fill="none" stroke="rgba(255,255,255,0.22)" stroke-width="1.6" stroke-linecap="round"/>
                </symbol>
            </svg>
            <div class="vinyl-stage">
                <div class="vinyl-scroller" data-vinyl-scroller role="group" aria-label="Top 8 albums shelf" style="--count: {{ $count }};">
                    <div class="vinyl-bar" aria-hidden="true"></div>
                    @foreach($albumList as $i => $album)
                        @php
                            $rank = $i + 1;
                            $col = $rank + 1;
                            $name = trim((string) ($album['name'] ?? '')) ?: 'Unknown Album';
                            $artist = trim((string) ($album['artist'] ?? '')) ?: 'Unknown Artist';
                            $plays = (int) ($album['playcount'] ?? 0);
                            $pct = $totalPlays > 0 ? ($plays / $totalPlays) * 100 : 0;
                            $pctLabel = $pct >= 1 ? (string) round($pct) : ($pct > 0 ? '<1' : '0');
                            $pctWidth = $pct > 0 ? round(max($pct, 2), 1) : 0;
                            $img = $coverUrl($album['image'] ?? null);
                        @endphp
                        <div class="vinyl-slot" style="grid-column: {{ $col }};">
                            <button type="button" class="vinyl-sleeve" aria-pressed="false" title="{{ $name }} - {{ $artist }}"
                                    aria-label="Number {{ $rank }}: {{ $name }} by {{ $artist }}, {{ number_format($plays) }} scrobbles"
                                    data-rank="{{ str_pad($rank, 2, '0', STR_PAD_LEFT) }}" data-name="{{ $name }}" data-artist="{{ $artist }}"
                                    data-plays="{{ number_format($plays) }}" data-pct-label="{{ $pctLabel }}" data-pct-width="{{ $pctWidth }}">
                                <span class="vinyl-cover">
                                    <svg class="vinyl-blank" viewBox="0 0 100 100" aria-hidden="true"><use href="#{{ $vid }}-blank"/></svg>
                                    @if($img)<img src="{{ $img }}" alt="" loading="lazy" draggable="false" onerror="this.remove()">@endif
                                </span>
                                <span class="vinyl-badge">#{{ $rank }}</span>
                            </button>
                        </div>
                        <div class="vinyl-plate" style="grid-column: {{ $col }};">{{ number_format($plays) }} scrobbles</div>
                    @endforeach
                </div>
            </div>
            <div class="vinyl-spine" aria-live="polite">
                <div class="vinyl-spine-empty" data-spine-empty>Select a record on the shelf to see its stats.</div>
                <div class="vinyl-spine-body" data-spine-body hidden>
                    <div class="vinyl-spine-band"><small>No.</small><strong data-f="rank"></strong></div>
                    <div class="vinyl-spine-main">
                        <div class="vinyl-spine-title" data-f="name"></div>
                        <div class="vinyl-spine-artist" data-f="artist"></div>
                        <div class="vinyl-spine-meter-label"><span data-f="pct"></span>% of your top 8 listening</div>
                        <div class="vinyl-spine-track"><div class="vinyl-spine-fill" data-f="bar"></div></div>
                    </div>
                    <div class="vinyl-spine-count"><strong data-f="plays"></strong><small>scrobbles</small></div>
                </div>
            </div>
        @endif
    </div>
</div>

@if($count > 0)
<script>
(function () {
    var root = document.getElementById(@json($vid));
    if (!root) return;
    var scroller = root.querySelector('[data-vinyl-scroller]');
    var slots = Array.prototype.slice.call(root.querySelectorAll('.vinyl-slot'));
    var emptyEl = root.querySelector('[data-spine-empty]');
    var bodyEl = root.querySelector('[data-spine-body]');
    var f = {};
    Array.prototype.forEach.call(root.querySelectorAll('[data-f]'), function (el) { f[el.getAttribute('data-f')] = el; });
    var selected = -1;
    function sleeveOf(slot) { return slot.querySelector('.vinyl-sleeve'); }
    function reveal(slot) {
        var sr = scroller.getBoundingClientRect();
        var r = slot.getBoundingClientRect();
        var pad = 8;
        if (r.left < sr.left + pad) scroller.scrollBy({ left: r.left - sr.left - pad });
        else if (r.right > sr.right - pad) scroller.scrollBy({ left: r.right - sr.right + pad });
    }
    function select(index) {
        if (index === selected) index = -1;
        slots.forEach(function (slot, k) {
            var on = k === index;
            slot.classList.toggle('is-selected', on);
            sleeveOf(slot).setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        if (index < 0) {
            emptyEl.hidden = false;
            bodyEl.hidden = true;
        } else {
            var d = sleeveOf(slots[index]).dataset;
            f.rank.textContent = d.rank;
            f.name.textContent = d.name;
            f.artist.textContent = d.artist;
            f.plays.textContent = d.plays;
            f.pct.textContent = d.pctLabel;
            f.bar.style.width = d.pctWidth + '%';
            emptyEl.hidden = true;
            bodyEl.hidden = false;
            reveal(slots[index]);
        }
        selected = index;
    }
    slots.forEach(function (slot, k) { sleeveOf(slot).addEventListener('click', function () { select(k); }); });
    root.addEventListener('keydown', function (e) {
        var active = document.activeElement;
        var current = slots.findIndex(function (s) { return s.contains(active); });
        if (e.key === 'Escape' && selected > -1) { select(selected); return; }
        if (current < 0) return;
        var next = current;
        if (e.key === 'ArrowRight') next = Math.min(slots.length - 1, current + 1);
        else if (e.key === 'ArrowLeft') next = Math.max(0, current - 1);
        else if (e.key === 'Home') next = 0;
        else if (e.key === 'End') next = slots.length - 1;
        else return;
        e.preventDefault();
        sleeveOf(slots[next]).focus({ preventScroll: true });
        reveal(slots[next]);
    });
    var drag = null;
    var moved = false;
    function settle() {
        var pad = parseFloat(getComputedStyle(scroller).scrollPaddingLeft) || 0;
        var best = 0;
        var bestDist = Infinity;
        slots.forEach(function (slot) {
            var target = slot.offsetLeft - pad;
            var dist = Math.abs(target - scroller.scrollLeft);
            if (dist < bestDist) { bestDist = dist; best = target; }
        });
        scroller.scrollTo({ left: Math.max(0, best), behavior: 'smooth' });
    }
    scroller.addEventListener('pointerdown', function (e) {
        if (e.pointerType !== 'mouse' || e.button !== 0) return;
        drag = { x: e.clientX, left: scroller.scrollLeft, id: e.pointerId };
        moved = false;
    });
    scroller.addEventListener('pointermove', function (e) {
        if (!drag) return;
        var dx = e.clientX - drag.x;
        if (!moved) {
            if (Math.abs(dx) < 5) return;
            moved = true;
            scroller.classList.add('is-dragging');
            try { scroller.setPointerCapture(drag.id); } catch (err) { /* not critical */ }
        }
        scroller.scrollLeft = drag.left - dx;
    });
    function endDrag() {
        if (!drag) return;
        var wasDragging = moved;
        drag = null;
        scroller.classList.remove('is-dragging');
        if (wasDragging) settle();
    }
    scroller.addEventListener('pointerup', endDrag);
    scroller.addEventListener('pointercancel', endDrag);
    scroller.addEventListener('click', function (e) {
        if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; }
    }, true);
})();
</script>
@endif