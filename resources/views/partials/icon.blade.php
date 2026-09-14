@php $size = $size ?? 16; @endphp
<svg width="{{ $size }}" height="{{ $size }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="display: inline-block; vertical-align: middle; flex-shrink: 0;">
    @switch($type)
        @case('lock')
            <rect x="5" y="11" width="14" height="9" rx="2"/>
            <path d="M8 11V7a4 4 0 0 1 8 0v4"/>
            @break
        @case('envelope')
            <rect x="3" y="5" width="18" height="14" rx="2"/>
            <path d="m3 7 9 6 9-6"/>
            @break
        @case('person')
            {{-- same path as the DM chat "view profile" icon --}}
            <circle cx="12" cy="8" r="4"/>
            <path d="M4 20c0-4 3.6-6 8-6s8 2 8 6"/>
            @break
        @case('palette')
            <path d="M12 2a10 10 0 1 0 0 20c1.5 0 2-1 2-2s-.5-1.5-1-2 0-2 1-2h2a4 4 0 0 0 4-4c0-5-3.5-10-8-10z"/>
            <circle cx="7.5" cy="10.5" r="1"/>
            <circle cx="10.5" cy="7" r="1"/>
            <circle cx="15" cy="7.5" r="1"/>
            <circle cx="17" cy="11" r="1"/>
            @break
        @case('check')
            <path d="M20 6 9 17l-5-5"/>
            @break
        @case('document')
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <path d="M14 2v6h6"/>
            @break
        @case('home')
            {{-- same path as the AetherTunes sidebar "Recently Played" icon --}}
            <path d="M3 9.5 12 3l9 6.5"/>
            <path d="M5 10v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V10"/>
            @break
        @case('music')
            {{-- same path as the Top 8 Songs icon --}}
            <path d="M9 18V5l12-2v13"/>
            <circle cx="6" cy="18" r="3"/>
            <circle cx="18" cy="16" r="3"/>
            @break
        @case('chat')
            {{-- same path as the chat empty-state icon --}}
            <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/>
            @break
        @case('sparkle')
            {{-- same path as the AetherTunes sidebar "Recommendations" icon --}}
            <path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M18.4 5.6l-2.8 2.8M8.4 15.6l-2.8 2.8"/>
            @break
    @endswitch
</svg>