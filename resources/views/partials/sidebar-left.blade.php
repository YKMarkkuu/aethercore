<aside class="sidebar">
    <!-- ===== AETHERCORE TABS (Social Mode) ===== -->
    <div id="sidebar-tabs" class="sidebar-tabs">
        <a href="{{ route('feed') }}" class="sidebar-tab {{ request()->routeIs('feed') ? 'active' : '' }}">Feed</a>
        <a href="{{ route('spaces') }}" class="sidebar-tab {{ request()->routeIs('spaces') ? 'active' : '' }}">Spaces</a>
        <a href="{{ route('friends.index') }}" class="sidebar-tab {{ request()->routeIs('friends.index') ? 'active' : '' }}">Friends</a>
    </div>

    <!-- ===== AETHERCORE SIDEBAR CONTENT ===== -->
    <div id="sidebar-aether" class="sidebar-list">
        <!-- Feed View -->
        <div id="view-feed" class="sidebar-view">
            <div class="status-group">
                <div class="status-group-label">Recent Activity</div>
                @forelse($feedPosts ?? [] as $post)
                    <div style="padding: 0.4rem 0; border-bottom: 1px solid #d0c8c0;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div class="friend-avatar">
                                @if($post->user->profile && $post->user->profile->avatar)
                                    <img src="{{ asset('storage/' . $post->user->profile->avatar) }}" alt="Avatar" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
                                @else
                                    {{ $post->user->name[0] }}
                                @endif
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-size: 0.7rem; color: #1a1a1a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <span style="color: #1a4a9e; font-weight: 600;">{{ $post->user->display_name }}</span>
                                    <span style="color: #2a2a2a;">{{ Str::limit($post->content, 20) }}</span>
                                </div>
                                <div style="font-size: 0.6rem; color: #5a5a5a;">{{ $post->created_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    </div>
                @empty
                    <p style="color: #3a3a3a; font-size: 0.75rem; padding: 0.5rem;">No activity yet.</p>
                @endforelse
            </div>
        </div>

        <!-- Spaces View -->
        <div id="view-spaces" class="sidebar-view hidden">
            <div class="status-group">
                <div class="status-group-label">Your Spaces</div>
                <p style="color: #6a6a6a; font-size: 0.7rem; padding: 0.3rem 0.4rem;">You haven't joined any spaces yet.</p>
                <button class="xp-create-btn" onclick="alert('AetherSpace creation coming soon!')">
                    + Create AetherSpace
                </button>
            </div>
        </div>

        <!-- Friends View -->
        <div id="view-friends" class="sidebar-view hidden">
            <div class="status-group">
                <div class="status-group-label">Direct Messages</div>
                <div id="sidebarFriendsList">
                @forelse(Auth::user()->getFriends() as $friend)
                    <a href="{{ route('conversations.start', $friend->id) }}" class="friend-item" data-friend-id="{{ $friend->id }}">
                        <div class="friend-avatar">
                            @if($friend->profile && $friend->profile->avatar)
                                <img src="{{ asset('storage/' . $friend->profile->avatar) }}" alt="Avatar" style="width: 28px; height: 28px; border-radius: 50%; object-fit: cover;">
                            @else
                                {{ $friend->name[0] }}
                            @endif
                        </div>
                        <div class="friend-info">
                            <div class="friend-name">{{ $friend->display_name }}</div>
                            <div class="friend-status" style="color: {{ $friend->getStatusColor() }};">
                    {{ $friend->getStatusLabel() }}
                </div>
                            <div class="friend-now-playing" style="display: none; font-size: 0.55rem; color: #3a7bd5; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; align-items: center; gap: 0.2rem;">
                                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;">
                                    <path d="M9 18V5l12-2v13"/>
                                    <circle cx="6" cy="18" r="3"/>
                                    <circle cx="18" cy="16" r="3"/>
                                </svg>
                                <span class="friend-now-playing-text"></span>
                            </div>
                        </div>
                    </a>
                @empty
                    <p style="color: #3a3a3a; font-size: 0.75rem; padding: 0.5rem;">No friends yet. Add some!</p>
                @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- ===== AETHERTUNES SIDEBAR CONTENT ===== -->
    <div id="sidebar-music" class="sidebar-list hidden">
        <div class="status-group">
            <div class="status-group-label">Home</div>
            <a href="#" class="friend-item">
                <div class="friend-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 9.5 12 3l9 6.5"/>
                        <path d="M5 10v10a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V10"/>
                    </svg>
                </div>
                <div class="friend-info">
                    <div class="friend-name">Recently Played</div>
                    <div class="friend-status">12 songs</div>
                </div>
            </a>
            <a href="#" class="friend-item">
                <div class="friend-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3v4M12 17v4M3 12h4M17 12h4M5.6 5.6l2.8 2.8M15.6 15.6l2.8 2.8M18.4 5.6l-2.8 2.8M8.4 15.6l-2.8 2.8"/>
                    </svg>
                </div>
                <div class="friend-info">
                    <div class="friend-name">Recommendations</div>
                    <div class="friend-status">For you</div>
                </div>
            </a>
        </div>
        <div class="status-group">
            <div class="status-group-label">Library</div>
            <a href="#" class="friend-item">
                <div class="friend-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                        <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                        <line x1="12" y1="19" x2="12" y2="23"/>
                        <line x1="8" y1="23" x2="16" y2="23"/>
                    </svg>
                </div>
                <div class="friend-info">
                    <div class="friend-name">Artists</div>
                    <div class="friend-status">0 saved</div>
                </div>
            </a>
            <a href="#" class="friend-item">
                <div class="friend-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <circle cx="12" cy="12" r="3"/>
                    </svg>
                </div>
                <div class="friend-info">
                    <div class="friend-name">Albums</div>
                    <div class="friend-status">0 saved</div>
                </div>
            </a>
            <a href="#" class="friend-item">
                <div class="friend-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 18V5l12-2v13"/>
                        <circle cx="6" cy="18" r="3"/>
                        <circle cx="18" cy="16" r="3"/>
                    </svg>
                </div>
                <div class="friend-info">
                    <div class="friend-name">Songs</div>
                    <div class="friend-status">0 saved</div>
                </div>
            </a>
        </div>
        <div class="status-group">
            <div class="status-group-label">Playlists</div>
            <a href="#" class="friend-item">
                <div class="friend-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="6" y="3" width="12" height="18" rx="2"/>
                        <line x1="9" y1="8" x2="15" y2="8"/>
                        <line x1="9" y1="12" x2="15" y2="12"/>
                        <line x1="9" y1="16" x2="12" y2="16"/>
                    </svg>
                </div>
                <div class="friend-info">
                    <div class="friend-name">Liked Songs</div>
                    <div class="friend-status">0 songs</div>
                </div>
            </a>
            <a href="#" class="friend-item">
                <div class="friend-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                </div>
                <div class="friend-info">
                    <div class="friend-name">Create Playlist</div>
                    <div class="friend-status">New</div>
                </div>
            </a>
        </div>
        <div class="status-group">
            <div class="status-group-label">Discover</div>
            <a href="#" class="friend-item">
                <div class="friend-avatar">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2c1 3-3 4-3 8a3 3 0 0 0 6 0c0-1-1-2-1-3 2 1 3 3 3 5a5 5 0 0 1-10 0c0-4 3-6 5-10z"/>
                    </svg>
                </div>
                <div class="friend-info">
                    <div class="friend-name">Trending</div>
                    <div class="friend-status">Top 50</div>
                </div>
            </a>
        </div>
    </div>

    <!-- NOW PLAYING -->
    <!-- No server-side Last.fm call anymore — this used to run on EVERY
         page load anywhere this sidebar renders (feed, profile, spaces,
         everywhere), blocking the page on a live Last.fm request with no
         caching. Now it renders instantly with a placeholder and fills
         in via the polling endpoint below. -->
    <div class="now-playing">
        <div class="now-playing-title">Now Playing</div>
        <div class="now-playing-song" id="nowPlayingSong">
            {{ Auth::user()->lastfm_username ? 'Loading…' : 'Not listening' }}
        </div>
        <div class="now-playing-artist" id="nowPlayingArtist">
            {{ Auth::user()->lastfm_username ? '' : 'Connect Last.fm' }}
        </div>
    </div>

    <!-- MINI PROFILE -->
    <div class="mini-profile" onclick="toggleProfilePopup()">
        <div class="mini-profile-avatar">
            @if(Auth::user()->profile && Auth::user()->profile->avatar)
                <img src="{{ asset('storage/' . Auth::user()->profile->avatar) }}" alt="Avatar" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
            @else
                {{ Auth::user()->name[0] ?? '?' }}
            @endif
        </div>
        <div class="mini-profile-info">
            <div class="mini-profile-name">{{ Auth::user()->display_name }}</div>
            <div class="mini-profile-status" style="color: {{ Auth::user()->getStatusColor() }};">
            {{ Auth::user()->getStatusLabel() }}
        </div>
        </div>
        <div class="mini-profile-badge">▶</div>
    </div>

    <!-- MINI PROFILE POPUP -->
    @include('partials.mini-profile-popup')
</aside>

@if(Auth::user()->lastfm_username)
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nowPlayingSong = document.getElementById('nowPlayingSong');
            const nowPlayingArtist = document.getElementById('nowPlayingArtist');

            function pollNowPlaying() {
                fetch('{{ route('now-playing', Auth::user()) }}', {
                    headers: { 'Accept': 'application/json' },
                })
                    .then(response => response.ok ? response.json() : Promise.reject())
                    .then(data => {
                        if (data.now_playing) {
                            nowPlayingSong.textContent = data.now_playing.name;
                            nowPlayingArtist.textContent = data.now_playing.artist;
                        } else {
                            nowPlayingSong.textContent = 'Not listening';
                            nowPlayingArtist.textContent = 'No track currently playing';
                        }
                    })
                    .catch(() => {
                        // Leave whatever was last shown — try again next
                        // interval rather than flashing an error state.
                    });
            }

            pollNowPlaying();
            setInterval(pollNowPlaying, 20000);
        });
    </script>
    @endpush
@endif

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const list = document.getElementById('sidebarFriendsList');
        if (!list) return;

        const rows = Array.from(list.querySelectorAll('[data-friend-id]'));
        if (rows.length === 0) return;

        const ids = rows.map(row => row.dataset.friendId);

        function pollFriendsNowPlaying() {
            const params = new URLSearchParams();
            ids.forEach(id => params.append('ids[]', id));

            fetch('{{ route("now-playing.batch") }}?' + params.toString(), {
                headers: { 'Accept': 'application/json' },
            })
                .then(response => response.ok ? response.json() : Promise.reject())
                .then(data => {
                    const nowPlaying = data.now_playing || {};
                    rows.forEach(row => {
                        const id = row.dataset.friendId;
                        const el = row.querySelector('.friend-now-playing');
                        const textEl = row.querySelector('.friend-now-playing-text');
                        if (nowPlaying[id]) {
                            textEl.textContent = nowPlaying[id].name + ' — ' + nowPlaying[id].artist;
                            el.style.display = 'flex';
                        } else {
                            el.style.display = 'none';
                        }
                    });
                })
                .catch(() => { /* silent, try again next interval */ });
        }

        pollFriendsNowPlaying();
        setInterval(pollFriendsNowPlaying, 20000);
    });
</script>
@endpush