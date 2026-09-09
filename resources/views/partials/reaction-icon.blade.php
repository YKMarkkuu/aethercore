{{-- Small shared partial so initial server-rendered reaction icons match
     the JS-rendered ones exactly (see REACTION_ICONS in
     conversation-show.blade.php — keep both in sync if you change these). --}}
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
    @switch($type)
        @case('like')
            <path d="M7 10v12"/><path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2h0a3.13 3.13 0 0 1 3 3.88Z"/>
            @break
        @case('love')
            <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
            @break
        @case('laugh')
            <circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2.5 4 2.5 4-2.5 4-2.5"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>
            @break
        @case('wow')
            <circle cx="12" cy="12" r="10"/><circle cx="12" cy="16" r="1.5"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>
            @break
        @case('sad')
            <circle cx="12" cy="12" r="10"/><path d="M16 16.5s-1.5-2-4-2-4 2-4 2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>
            @break
    @endswitch
</svg>