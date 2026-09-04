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
                            <div class="friend-avatar">{{ $post->user->name[0] }}</div>
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
                @forelse(Auth::user()->getFriends() as $friend)
                    <a href="{{ route('conversations.start', $friend->id) }}" class="friend-item">
                        <div class="friend-avatar">{{ $friend->name[0] }}</div>
                        <div class="friend-info">
                            <div class="friend-name">{{ $friend->display_name }}</div>
                            <div class="friend-status">Online</div>
                        </div>
                    </a>
                @empty
                    <p style="color: #3a3a3a; font-size: 0.75rem; padding: 0.5rem;">No friends yet. Add some!</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- ===== AETHERTUNES SIDEBAR CONTENT ===== -->
    <div id="sidebar-music" class="sidebar-list hidden">
        <div class="status-group">
            <div class="status-group-label">Home</div>
            <a href="#" class="friend-item">
                <div class="friend-avatar">🏠</div>
                <div class="friend-info">
                    <div class="friend-name">Recently Played</div>
                    <div class="friend-status">12 songs</div>
                </div>
            </a>
            <a href="#" class="friend-item">
                <div class="friend-avatar">✨</div>
                <div class="friend-info">
                    <div class="friend-name">Recommendations</div>
                    <div class="friend-status">For you</div>
                </div>
            </a>
        </div>
        <div class="status-group">
            <div class="status-group-label">Library</div>
            <a href="#" class="friend-item">
                <div class="friend-avatar">🎤</div>
                <div class="friend-info">
                    <div class="friend-name">Artists</div>
                    <div class="friend-status">0 saved</div>
                </div>
            </a>
            <a href="#" class="friend-item">
                <div class="friend-avatar">💿</div>
                <div class="friend-info">
                    <div class="friend-name">Albums</div>
                    <div class="friend-status">0 saved</div>
                </div>
            </a>
            <a href="#" class="friend-item">
                <div class="friend-avatar">🎵</div>
                <div class="friend-info">
                    <div class="friend-name">Songs</div>
                    <div class="friend-status">0 saved</div>
                </div>
            </a>
        </div>
        <div class="status-group">
            <div class="status-group-label">Playlists</div>
            <a href="#" class="friend-item">
                <div class="friend-avatar">📋</div>
                <div class="friend-info">
                    <div class="friend-name">Liked Songs</div>
                    <div class="friend-status">0 songs</div>
                </div>
            </a>
            <a href="#" class="friend-item">
                <div class="friend-avatar">➕</div>
                <div class="friend-info">
                    <div class="friend-name">Create Playlist</div>
                    <div class="friend-status">New</div>
                </div>
            </a>
        </div>
        <div class="status-group">
            <div class="status-group-label">Discover</div>
            <a href="#" class="friend-item">
                <div class="friend-avatar">🔥</div>
                <div class="friend-info">
                    <div class="friend-name">Trending</div>
                    <div class="friend-status">Top 50</div>
                </div>
            </a>
        </div>
    </div>

    <!-- NOW PLAYING -->
    <div class="now-playing">
        <div class="now-playing-title">Now Playing</div>
        <div class="now-playing-song">Blinding Lights</div>
        <div class="now-playing-artist">The Weeknd</div>
    </div>

    <!-- MINI PROFILE -->
    <div class="mini-profile" onclick="toggleProfilePopup()">
        <div class="mini-profile-avatar">{{ Auth::user()->name[0] ?? '?' }}</div>
        <div class="mini-profile-info">
            <div class="mini-profile-name">{{ Auth::user()->display_name }}</div>
            <div class="mini-profile-status">Online</div>
        </div>
        <div class="mini-profile-badge">▶</div>
    </div>

    <!-- MINI PROFILE POPUP -->
    @include('partials.mini-profile-popup')
</aside>