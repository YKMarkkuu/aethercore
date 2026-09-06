@extends('layouts.app')

@section('title', $user->name . "'s Profile")
@section('content')

@php
    use Illuminate\Support\Facades\DB;
    
    // Helper function to check if image is a placeholder
    if (!function_exists('isPlaceholderImage')) {
        function isPlaceholderImage($url) {
            if (empty($url)) return true;
            $placeholders = [
                '2a96cbd8b46e442fc41c2b86b821562f',
                'avatar_default', 
                'default_avatar', 
                'noimage',
                'placeholder'
            ];
            foreach ($placeholders as $placeholder) {
                if (strpos($url, $placeholder) !== false) return true;
            }
            return false;
        }
    }
    
    // Helper function to force 300x300 size on Last.fm images
    if (!function_exists('forceImageSize')) {
        function forceImageSize($url) {
            if (empty($url)) return null;
            // NOTE: real Last.fm CDN domain is "lastfm.freetls.fastly.net"
            // (no "-img" segment) — that typo previously made this check
            // never match, so images passed through at whatever size
            // Last.fm originally returned (34s/64s/174s/etc).
            if (strpos($url, 'lastfm.freetls.fastly.net/i/u/') !== false) {
                $url = preg_replace('/\/i\/u\/\d+s\//', '/i/u/300x300/', $url);
                $url = preg_replace('/\/i\/u\/\d+x\d+\//', '/i/u/300x300/', $url);
                return $url;
            }
            // Fallback: return the original URL untouched rather than
            // silently discarding a valid (but unmatched) image URL.
            return $url;
        }
    }
@endphp

<div class="xp-profile">

    <!-- ===== PROFILE HEADER ===== -->
    <div class="xp-profile-header">
        <div class="xp-profile-cover-wrapper" style="position: relative; overflow: visible !important; border-radius: 6px 6px 0 0; height: 180px;">

            <!-- Banner Container -->
            <div class="xp-profile-cover" id="bannerContainer" style="background: #1a1a2e; height: 180px; width: 100%; position: relative; overflow: hidden; border-radius: 6px 6px 0 0;">
                @if($user->getBannerUrl())
                    <img src="{{ $user->getBannerUrl() }}" 
                         id="bannerImage" 
                         alt="Banner" 
                         style="width: 100%; height: 100%; object-fit: cover; object-position: center center; position: absolute; top: 0; left: 0;"
                         draggable="false">
                @endif
            </div>

            <!-- Edit Banner Button -->
            @if(auth()->id() === $user->id)
                <div style="position: absolute; bottom: 12px; right: 16px; display: flex; gap: 0.5rem; z-index: 5;">
                    <button class="xp-edit-btn" onclick="document.getElementById('bannerInput').click()" style="background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.2); color: white; padding: 4px 14px; border-radius: 4px; font-size: 0.7rem; cursor: pointer; font-family: inherit;">
                        📷 Change Banner
                    </button>
                </div>
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="bannerForm">
                    @csrf
                    <input type="file" id="bannerInput" name="banner" accept="image/*" style="display: none;" onchange="this.form.submit()">
                </form>
            @endif

            <!-- Avatar -->
            <div class="xp-profile-avatar" style="position: absolute; bottom: -40px; left: 30px; z-index: 10; width: 100px; height: 100px; border-radius: 50%; background: #f0edd8; border: 4px solid #ffffff; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; font-weight: 700; color: #1a4a9e; box-shadow: 0 2px 8px rgba(0,0,0,0.15); overflow: hidden; flex-shrink: 0;">
                @if($user->getAvatarUrl())
                    <img src="{{ $user->getAvatarUrl() }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
                @else
                    {{ $user->name[0] ?? '?' }}
                @endif
            </div>

            <!-- Edit Avatar Button -->
            @if(auth()->id() === $user->id)
                <button onclick="document.getElementById('avatarInput').click()" style="position: absolute; bottom: -30px; left: 110px; z-index: 11; background: #6a6a6a; border: 2px solid #f0edd8; color: white; border-radius: 50%; width: 28px; height: 28px; cursor: pointer; font-size: 0.8rem; display: flex; align-items: center; justify-content: center;">
                    ✎
                </button>
                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" id="avatarForm">
                    @csrf
                    <input type="file" id="avatarInput" name="avatar" accept="image/*" style="display: none;" onchange="this.form.submit()">
                </form>
            @endif
        </div>

        <!-- Spacer for avatar -->
        <div style="height: 40px;"></div>

        <!-- ===== PROFILE INFO ===== -->
        <div class="xp-profile-info" style="padding: 0 1.5rem 0.5rem 1.5rem;">
            <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <h1 class="xp-profile-name" id="displayNameDisplay" style="font-size: 1.5rem; font-weight: 700; margin: 0; color: #1e1e1e;">{{ $user->display_name }}</h1>
                @if(auth()->id() === $user->id)
                    <button class="xp-edit-btn" id="editProfileBtn" onclick="toggleEditMode()" style="font-size: 0.6rem; padding: 0.1rem 0.8rem;">Edit</button>
                @endif
            </div>
            <div class="xp-profile-username" style="font-size: 0.85rem; color: #6a6a6a; margin-top: -0.1rem;">@ {{ $user->username ?? $user->name }}</div>
            <div class="xp-profile-status" style="color: {{ $user->getStatusColor() }};">
                {{ $user->getStatusLabel() }}
            </div>
            <div class="xp-profile-bio" id="bioDisplay" style="font-size: 0.9rem; color: #1e1e1e; margin-top: 0.4rem; line-height: 1.5;">{{ $user->profile->bio ?? 'Welcome to AetherCore!' }}</div>

            <!-- Location -->
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-top: 0.3rem; font-size: 0.75rem; color: #6a6a6a;">
                @if($user->profile && $user->profile->location)
                    <span>📍 {{ $user->profile->location }}</span>
                @endif
            </div>

            <!-- Friend Action Button -->
            <div class="xp-profile-actions" style="margin-top: 0.5rem;">
                @if(auth()->id() !== $user->id)
                    @php
                        $isFriend = DB::table('friendships')
                            ->where(function($query) use ($user) {
                                $query->where('user_id', auth()->id())
                                    ->where('friend_id', $user->id)
                                    ->where('status', 'accepted');
                            })
                            ->orWhere(function($query) use ($user) {
                                $query->where('user_id', $user->id)
                                    ->where('friend_id', auth()->id())
                                    ->where('status', 'accepted');
                            })
                            ->exists();

                        $isPending = DB::table('friendships')
                            ->where('user_id', auth()->id())
                            ->where('friend_id', $user->id)
                            ->where('status', 'pending')
                            ->exists();

                        $isRequested = DB::table('friendships')
                            ->where('user_id', $user->id)
                            ->where('friend_id', auth()->id())
                            ->where('status', 'pending')
                            ->exists();
                    @endphp

                    @if($isFriend)
                        <span class="xp-action-btn xp-action-btn-success">Friends</span>
                        <form action="{{ route('friends.reject', $user->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="xp-action-btn xp-action-btn-danger">Unfriend</button>
                        </form>
                    @elseif($isPending)
                        <span class="xp-action-btn xp-action-btn-warning">Request Sent</span>
                        <form action="{{ route('friends.reject', $user->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="xp-action-btn xp-action-btn-danger">Cancel</button>
                        </form>
                    @elseif($isRequested)
                        <form action="{{ route('friends.accept', $user->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="xp-action-btn xp-action-btn-success">Accept Request</button>
                        </form>
                        <form action="{{ route('friends.reject', $user->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="xp-action-btn xp-action-btn-danger">Decline</button>
                        </form>
                    @else
                        <form action="{{ route('friends.request', $user->id) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="xp-action-btn xp-action-btn-primary">Add Friend</button>
                        </form>
                    @endif
                @endif
            </div>

            <!-- Edit Mode Fields -->
            <div id="editMode" style="display: none; margin-top: 0.5rem; border-top: 1px solid #d0c8c0; padding-top: 0.5rem;">
                <form action="{{ route('profile.update') }}" method="POST" id="editProfileForm">
                    @csrf
                    <div style="margin-bottom: 0.5rem;">
                        <label style="font-size: 0.65rem; color: #6a6a6a; display: block;">Display Name</label>
                        <input type="text" name="display_name" value="{{ $user->profile->display_name ?? $user->name }}" class="settings-input" style="width: 100%;">
                    </div>
                    <div style="margin-bottom: 0.5rem;">
                        <label style="font-size: 0.65rem; color: #6a6a6a; display: block;">Bio</label>
                        <textarea name="bio" class="settings-input" rows="2" style="width: 100%;">{{ $user->profile->bio ?? 'Welcome to AetherCore!' }}</textarea>
                    </div>
                    <div style="margin-bottom: 0.5rem;">
                        <label style="font-size: 0.65rem; color: #6a6a6a; display: block;">Location</label>
                        <input type="text" name="location" value="{{ $user->profile->location ?? '' }}" class="settings-input" style="width: 100%;">
                    </div>
                    <button type="submit" class="settings-btn">Save Changes</button>
                    <button type="button" class="settings-btn" style="margin-left: 0.3rem; background: #d4d0c8;" onclick="toggleEditMode()">Cancel</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ===== STATS DIVIDER ===== -->
    <div class="xp-stats-divider">
        <div class="xp-stat-item">
            <span class="xp-stat-number">{{ $user->posts()->count() }}</span>
            <span class="xp-stat-label">Posts</span>
        </div>
        <div class="xp-stat-divider-line"></div>
        <div class="xp-stat-item">
            <span class="xp-stat-number">{{ $user->getFriends()->count() }}</span>
            <span class="xp-stat-label">Friends</span>
        </div>
        <div class="xp-stat-divider-line"></div>
        <div class="xp-stat-item">
            <span class="xp-stat-number">0</span>
            <span class="xp-stat-label">Artists</span>
        </div>
        <div class="xp-stat-divider-line"></div>
        <div class="xp-stat-item">
            <span class="xp-stat-number">0</span>
            <span class="xp-stat-label">Albums</span>
        </div>
        <div class="xp-stat-divider-line"></div>
        <div class="xp-stat-item">
            <span class="xp-stat-number">0</span>
            <span class="xp-stat-label">Playlists</span>
        </div>
    </div>

    <!-- ===== TWO-COLUMN LAYOUT ===== -->
    <div class="xp-profile-body">

        <!-- ===== LEFT COLUMN: About + Top 8 Music ===== -->
        <div class="xp-profile-left">
            <!-- About Panel -->
            <div class="xp-panel">
                <div class="xp-panel-header">About</div>
                <div class="xp-panel-body">
                    <div class="xp-info-row">
                        <span class="xp-info-label">Display Name</span>
                        <span class="xp-info-value">{{ $user->profile->display_name ?? $user->name }}</span>
                    </div>
                    <div class="xp-info-row">
                        <span class="xp-info-label">Username</span>
                        <span class="xp-info-value">@ {{ $user->username ?? $user->name }}</span>
                    </div>
                    <div class="xp-info-row">
                        <span class="xp-info-label">Joined</span>
                        <span class="xp-info-value">{{ $user->created_at->format('F Y') }}</span>
                    </div>
                    <div class="xp-info-row">
                        <span class="xp-info-label">Location</span>
                        <span class="xp-info-value">{{ $user->profile->location ?? 'Not set' }}</span>
                    </div>
                </div>
            </div>

            <!-- ===== TOP 8 PERIOD SELECTOR ===== -->
            <!-- Owner-controlled: applies to everyone viewing this profile -->
            <div class="xp-panel">
                <div class="xp-panel-body" style="padding: 0.3rem 0.4rem; display: flex; align-items: center; gap: 0.5rem;">
                    @php
                        $periods = [
                            'overall' => 'All Time',
                            '12month' => 'Last 365 Days',
                            '6month'  => 'Last 180 Days',
                            '3month'  => 'Last 90 Days',
                            '1month'  => 'Last 30 Days',
                            '7day'    => 'Last 7 Days',
                        ];
                        $currentPeriod = $user->profile->stats_period ?? 'overall';
                    @endphp
                    <span style="font-size: 0.6rem; color: #6a6a6a; white-space: nowrap;">Stats period:</span>
                    @if(auth()->id() === $user->id)
                        <form action="{{ route('profile.stats-period') }}" method="POST" style="flex: 1;">
                            @csrf
                            <select name="period" class="settings-input" style="font-size: 0.7rem;" onchange="this.form.submit()">
                                @foreach($periods as $value => $label)
                                    <option value="{{ $value }}" @selected($currentPeriod === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    @else
                        <span style="font-size: 0.7rem; font-weight: 600; color: #1e1e1e;">{{ $periods[$currentPeriod] ?? 'All Time' }}</span>
                    @endif
                </div>
            </div>

            <!-- ===== TOP 8 ARTISTS ===== -->
            <div class="xp-panel">
                <div class="xp-panel-header">🎵 Top 8 Artists</div>
                <div class="xp-panel-body">
                    @php 
                        $artists = $user->lastfm_data['top_artists'] ?? [];
                    @endphp
                    @if(count($artists) > 0)
                        <div class="xp-top8-grid">
                            @foreach($artists as $artist)
                                @php
                                    $hasRealImage = !empty($artist['image']) && !isPlaceholderImage($artist['image']);
                                    $imageUrl = $hasRealImage ? forceImageSize($artist['image']) : null;
                                @endphp
                                <div class="xp-top8-item">
                                    <div class="xp-top8-thumb">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $artist['name'] }}" loading="lazy">
                                        @else
                                            <div class="xp-top8-icon">
                                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"/>
                                                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                                                    <line x1="12" y1="19" x2="12" y2="23"/>
                                                    <line x1="8" y1="23" x2="16" y2="23"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="xp-top8-name">{{ $artist['name'] }}</div>
                                    <div class="xp-top8-scrobbles">{{ number_format($artist['playcount'] ?? 0) }} scrobbles</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="xp-top8-empty">No top artists found.</span>
                    @endif
                </div>
            </div>

            <!-- ===== TOP 8 ALBUMS ===== -->
            <div class="xp-panel">
                <div class="xp-panel-header">💿 Top 8 Albums</div>
                <div class="xp-panel-body">
                    @php 
                        $albums = $user->lastfm_data['top_albums'] ?? []; 
                    @endphp
                    @if(count($albums) > 0)
                        <div class="xp-top8-grid">
                            @foreach($albums as $album)
                                @php
                                    $hasRealImage = !empty($album['image']) && !isPlaceholderImage($album['image']);
                                    $imageUrl = $hasRealImage ? forceImageSize($album['image']) : null;
                                @endphp
                                <div class="xp-top8-item">
                                    <div class="xp-top8-thumb">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $album['name'] }}" loading="lazy">
                                        @else
                                            <div class="xp-top8-icon">
                                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <circle cx="12" cy="12" r="10"/>
                                                    <circle cx="12" cy="12" r="3"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="xp-top8-name">{{ $album['name'] }}</div>
                                    <div class="xp-top8-subtext">{{ $album['artist'] ?? 'Unknown Artist' }}</div>
                                    <div class="xp-top8-scrobbles">{{ number_format($album['playcount'] ?? 0) }} scrobbles</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="xp-top8-empty">No top albums found.</span>
                    @endif
                </div>
            </div>

            <!-- ===== TOP 8 SONGS ===== -->
            <div class="xp-panel">
                <div class="xp-panel-header">🎵 Top 8 Songs</div>
                <div class="xp-panel-body">
                    @php 
                        $songs = $user->lastfm_data['top_songs'] ?? []; 
                    @endphp
                    @if(count($songs) > 0)
                        <div class="xp-top8-grid">
                            @foreach($songs as $song)
                                @php
                                    $hasRealImage = !empty($song['image']) && !isPlaceholderImage($song['image']);
                                    $imageUrl = $hasRealImage ? forceImageSize($song['image']) : null;
                                @endphp
                                <div class="xp-top8-item">
                                    <div class="xp-top8-thumb">
                                        @if($imageUrl)
                                            <img src="{{ $imageUrl }}" alt="{{ $song['name'] }}" loading="lazy">
                                        @else
                                            <div class="xp-top8-icon">
                                                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M9 18V5l12-2v13"/>
                                                    <circle cx="6" cy="18" r="3"/>
                                                    <circle cx="18" cy="16" r="3"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="xp-top8-name">{{ $song['name'] }}</div>
                                    <div class="xp-top8-subtext">{{ $song['artist'] ?? 'Unknown Artist' }}</div>
                                    <div class="xp-top8-scrobbles">{{ number_format($song['playcount'] ?? 0) }} scrobbles</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span class="xp-top8-empty">No top songs found.</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- ===== RIGHT COLUMN: Top 8 Friends + Posts ===== -->
        <div class="xp-profile-right">
            <!-- Top 8 Friends -->
            <div class="xp-panel">
                <div class="xp-panel-header" style="display: flex; justify-content: space-between; align-items: center; font-size: 0.65rem; padding: 0.2rem 0.6rem;">
                    <span>👥 Top 8 Friends</span>
                    @if(auth()->id() === $user->id)
                        <button class="xp-edit-btn" onclick="toggleTopFriendsModal()" style="font-size: 0.5rem; padding: 0.05rem 0.5rem;">Edit</button>
                    @endif
                </div>
                <div class="xp-panel-body" style="padding: 0.3rem 0.4rem;">
                    @php
                        $topFriends = $user->profile->top_friends ?? [];
                        $friendUsers = collect();
                        if (!empty($topFriends)) {
                            $unordered = App\Models\User::whereIn('id', $topFriends)->get()->keyBy('id');
                            foreach ($topFriends as $fid) {
                                if ($unordered->has($fid)) {
                                    $friendUsers->push($unordered->get($fid));
                                }
                            }
                        }
                    @endphp
                    @if($friendUsers->count() > 0)
                        <div style="display: flex; flex-direction: column; gap: 0.2rem;">
                            @foreach($friendUsers as $index => $friend)
                                <div style="display: flex; align-items: center; gap: 0.4rem; padding: 0.15rem 0.3rem; background: #f8f5ec; border: 1px solid #d0c8c0; border-radius: 4px;">
                                    <span style="font-size: 0.55rem; font-weight: 700; color: #1a4a9e; min-width: 16px;">#{{ $index + 1 }}</span>
                                    <div class="xp-friend-avatar" style="width: 24px; height: 24px; border-radius: 50%; background: linear-gradient(135deg, #3a7bd5, #1a4a9e); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.55rem; color: #ffffff; flex-shrink: 0;">
                                        {{ $friend->name[0] }}
                                    </div>
                                    <a href="{{ route('profile.show', $friend) }}" style="font-size: 0.65rem; color: #1e1e1e; text-decoration: none; flex: 1;">
                                        {{ $friend->display_name }}
                                    </a>
                                    <span style="font-size: 0.5rem; color: #6a6a6a; font-style: italic;">⭐</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span style="font-size: 0.6rem; color: #6a6a6a;">No top friends set yet</span>
                    @endif
                </div>
            </div>

            <!-- Posts -->
            <div class="xp-panel">
                <div class="xp-panel-header" style="font-size: 0.65rem; padding: 0.2rem 0.6rem;">Posts</div>
                <div class="xp-panel-body" style="padding: 0.3rem 0.4rem;">
                    @if(auth()->id() === $user->id)
                        <form action="{{ route('posts.store') }}" method="POST" style="margin-bottom: 0.3rem;">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <textarea name="content" class="settings-input" rows="2" placeholder="Share something..." style="resize: none; font-size: 0.75rem; padding: 0.3rem 0.5rem;"></textarea>
                            <button type="submit" class="settings-btn" style="margin-top: 0.2rem; font-size: 0.65rem; padding: 0.15rem 0.6rem;">Post</button>
                        </form>
                        <hr class="xp-divider" style="margin: 0.2rem 0;">
                    @endif

                    @forelse($user->posts as $post)
                        <div class="xp-post" style="padding: 0.25rem 0.4rem; margin-bottom: 0.25rem;">
                            <div class="xp-post-header" style="font-size: 0.6rem; margin-bottom: 0.1rem;">
                                <span class="xp-post-user">{{ $user->display_name }}</span>
                                <span class="xp-post-time">{{ $post->created_at->diffForHumans() }}</span>
                            </div>
                            <div class="xp-post-content" style="font-size: 0.7rem; padding: 0.1rem 0;">{{ $post->content }}</div>
                            <div class="xp-post-actions" style="font-size: 0.5rem; padding-top: 0.1rem; margin-top: 0.1rem;">
                                <span>Like</span>
                                <span>Comment</span>
                                <span>Share</span>
                                @if(auth()->id() === $post->user_id)
                                    <form action="{{ route('posts.destroy', $post) }}" method="POST" style="display: inline; margin-left: auto;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="xp-delete-btn" style="font-size: 0.5rem;">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p style="font-size: 0.65rem; color: #6a6a6a; text-align: center; padding: 0.3rem 0;">No posts yet</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- ===== EDIT TOP 8 FRIENDS MODAL ===== -->
    @if(auth()->id() === $user->id)
        <div class="settings-modal hidden" id="topFriendsModal">
            <div class="settings-modal-content" style="max-width: 380px; height: auto; max-height: 70vh;">
                <div class="settings-modal-header">
                    <h2>Edit Top 8 Friends</h2>
                    <button class="settings-modal-close" onclick="toggleTopFriendsModal()">✕</button>
                </div>
                <form action="{{ route('profile.top-friends') }}" method="POST">
                    @csrf
                    <div style="padding: 0.75rem 1rem; overflow-y: auto; max-height: 50vh;">
                        <p class="settings-subtitle" style="margin-top: 0;">Pick up to 8 friends, in the order you want them shown.</p>
                        @php
                            $selectedIds = $user->profile->top_friends ?? [];
                        @endphp
                        @forelse($availableFriends as $friend)
                            <label style="display: flex; align-items: center; gap: 0.5rem; padding: 0.3rem 0.2rem; border-bottom: 1px solid #e0dcd0; font-size: 0.8rem; cursor: pointer;">
                                <input type="checkbox" name="friends[]" value="{{ $friend->id }}"
                                    class="top-friend-checkbox"
                                    @checked(in_array($friend->id, $selectedIds))>
                                {{ $friend->name }}
                            </label>
                        @empty
                            <p style="font-size: 0.75rem; color: #6a6a6a;">You don't have any friends yet to add here.</p>
                        @endforelse
                    </div>
                    <div style="padding: 0.6rem 1rem; border-top: 1px solid #b0a8a0; display: flex; justify-content: flex-end; gap: 0.5rem;">
                        <button type="button" class="settings-btn" onclick="toggleTopFriendsModal()">Cancel</button>
                        <button type="submit" class="settings-btn">Save</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

<script>
    function toggleTopFriendsModal() {
        document.getElementById('topFriendsModal').classList.toggle('hidden');
    }

    // Cap selection at 8 friends
    document.addEventListener('DOMContentLoaded', function() {
        const boxes = document.querySelectorAll('.top-friend-checkbox');
        boxes.forEach(box => {
            box.addEventListener('change', function() {
                const checked = document.querySelectorAll('.top-friend-checkbox:checked');
                if (checked.length > 8) {
                    this.checked = false;
                    alert('You can only select up to 8 friends.');
                }
            });
        });
    });
</script>

<script>
    function toggleEditMode() {
        const editMode = document.getElementById('editMode');
        const editBtn = document.getElementById('editProfileBtn');
        const displayName = document.getElementById('displayNameDisplay');
        const bioDisplay = document.getElementById('bioDisplay');
        
        if (editMode.style.display === 'none') {
            editMode.style.display = 'block';
            editBtn.textContent = 'Cancel Edit';
            displayName.style.display = 'none';
            bioDisplay.style.display = 'block';
        } else {
            editMode.style.display = 'none';
            editBtn.textContent = 'Edit Profile';
            displayName.style.display = 'block';
            bioDisplay.style.display = 'block';
        }
    }
</script>

@endsection