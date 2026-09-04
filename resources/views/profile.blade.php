@extends('layouts.app')

@section('title', $user->name . "'s Profile")
@section('content')

@php
    use Illuminate\Support\Facades\DB;
@endphp

<div class="xp-profile">

    <!-- ===== PROFILE HEADER ===== -->
    <div class="xp-profile-header">
        <div class="xp-profile-cover-wrapper" style="position: relative; overflow: visible !important; border-radius: 6px 6px 0 0; height: 180px;">

            <!-- Banner Container -->
            <div class="xp-profile-cover" id="bannerContainer" style="background: #1a1a2e; height: 180px; width: 100%; position: relative; overflow: hidden; border-radius: 6px 6px 0 0;">
                @if($user->profile && $user->profile->banner)
                    <img src="{{ asset('storage/' . $user->profile->banner) }}" 
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
                @if($user->profile && $user->profile->avatar)
                    <img src="{{ asset('storage/' . $user->profile->avatar) }}" alt="Avatar" style="width: 100%; height: 100%; object-fit: cover;">
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

        <!-- ===== PROFILE INFO + STATS (Side by Side) ===== -->
        <div class="xp-profile-info" style="padding: 0 1.5rem 1rem 1.5rem; display: flex; gap: 2rem; align-items: flex-start;">

            <!-- LEFT: Name, Username, Bio, Actions -->
            <div style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                    <h1 class="xp-profile-name" id="displayNameDisplay" style="font-size: 1.5rem; font-weight: 700; margin: 0; color: #1e1e1e;">{{ $user->display_name }}</h1>
                    @if(auth()->id() === $user->id)
                        <button class="xp-edit-btn" id="editProfileBtn" onclick="toggleEditMode()" style="font-size: 0.6rem; padding: 0.1rem 0.8rem;">Edit</button>
                    @endif
                </div>
                <div class="xp-profile-username" style="font-size: 0.85rem; color: #6a6a6a; margin-top: -0.1rem;">@ {{ $user->username ?? $user->name }}</div>
                <div class="xp-profile-status" style="font-size: 0.7rem; color: #3a8a3a;">🟢 Online</div>
                <div class="xp-profile-bio" id="bioDisplay" style="font-size: 0.9rem; color: #1e1e1e; margin-top: 0.4rem; line-height: 1.5;">{{ $user->profile->bio ?? 'Welcome to AetherCore!' }}</div>

                <!-- Location + Join Date -->
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

            <!-- RIGHT: Stats Boxes -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 0.5rem; flex-shrink: 0; min-width: 180px; margin-top: 0.2rem;">
                <div class="xp-stat-box" style="background: #f0edd8; border: 2px solid #b0a8a0; border-radius: 4px; padding: 0.2rem 0.5rem; text-align: center;">
                    <span class="xp-stat-number" style="display: block; font-size: 1.1rem; font-weight: 700; color: #1a4a9e;">{{ $user->posts()->count() }}</span>
                    <span class="xp-stat-label" style="font-size: 0.5rem; text-transform: uppercase; color: #6a6a6a;">Posts</span>
                </div>
                <div class="xp-stat-box" style="background: #f0edd8; border: 2px solid #b0a8a0; border-radius: 4px; padding: 0.2rem 0.5rem; text-align: center;">
                    <span class="xp-stat-number" style="display: block; font-size: 1.1rem; font-weight: 700; color: #1a4a9e;">{{ $user->getFriends()->count() }}</span>
                    <span class="xp-stat-label" style="font-size: 0.5rem; text-transform: uppercase; color: #6a6a6a;">Friends</span>
                </div>
                <div class="xp-stat-box" style="background: #f0edd8; border: 2px solid #b0a8a0; border-radius: 4px; padding: 0.2rem 0.5rem; text-align: center;">
                    <span class="xp-stat-number" style="display: block; font-size: 1.1rem; font-weight: 700; color: #1a4a9e;">0</span>
                    <span class="xp-stat-label" style="font-size: 0.5rem; text-transform: uppercase; color: #6a6a6a;">Artists</span>
                </div>
                <div class="xp-stat-box" style="background: #f0edd8; border: 2px solid #b0a8a0; border-radius: 4px; padding: 0.2rem 0.5rem; text-align: center;">
                    <span class="xp-stat-number" style="display: block; font-size: 1.1rem; font-weight: 700; color: #1a4a9e;">0</span>
                    <span class="xp-stat-label" style="font-size: 0.5rem; text-transform: uppercase; color: #6a6a6a;">Albums</span>
                </div>
                <div class="xp-stat-box" style="background: #f0edd8; border: 2px solid #b0a8a0; border-radius: 4px; padding: 0.2rem 0.5rem; text-align: center;">
                    <span class="xp-stat-number" style="display: block; font-size: 1.1rem; font-weight: 700; color: #1a4a9e;">0</span>
                    <span class="xp-stat-label" style="font-size: 0.5rem; text-transform: uppercase; color: #6a6a6a;">Playlists</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== TWO-COLUMN LAYOUT ===== -->
    <div class="xp-profile-body">

        <!-- ===== LEFT COLUMN: About / Top 8 ===== -->
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

            <!-- ===== TOP 8 ARTISTS ===== -->
            <div class="xp-panel">
                <div class="xp-panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>🎵 Top 8 Artists</span>
                    @if(auth()->id() === $user->id)
                        <button class="xp-edit-btn" onclick="alert('Edit Top 8 Artists coming soon!')" style="font-size: 0.5rem; padding: 0.05rem 0.5rem;">Edit</button>
                    @endif
                </div>
                <div class="xp-panel-body">
                    @php
                        $topArtists = $user->profile->top_artists ?? [];
                    @endphp
                    @if(count($topArtists) > 0)
                        <div style="display: flex; flex-wrap: wrap; gap: 0.3rem;">
                            @foreach($topArtists as $artist)
                                <span class="xp-top-badge">{{ $artist }}</span>
                            @endforeach
                        </div>
                    @else
                        <span style="font-size: 0.7rem; color: #6a6a6a;">No top artists set yet</span>
                    @endif
                </div>
            </div>

            <!-- ===== TOP 8 SONGS ===== -->
            <div class="xp-panel">
                <div class="xp-panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>🎵 Top 8 Songs</span>
                    @if(auth()->id() === $user->id)
                        <button class="xp-edit-btn" onclick="alert('Edit Top 8 Songs coming soon!')" style="font-size: 0.5rem; padding: 0.05rem 0.5rem;">Edit</button>
                    @endif
                </div>
                <div class="xp-panel-body">
                    @php
                        $topSongs = $user->profile->top_songs ?? [];
                    @endphp
                    @if(count($topSongs) > 0)
                        <div style="display: flex; flex-wrap: wrap; gap: 0.3rem;">
                            @foreach($topSongs as $song)
                                <span class="xp-top-badge">{{ $song }}</span>
                            @endforeach
                        </div>
                    @else
                        <span style="font-size: 0.7rem; color: #6a6a6a;">No top songs set yet</span>
                    @endif
                </div>
            </div>

            <!-- ===== TOP 8 ALBUMS ===== -->
            <div class="xp-panel">
                <div class="xp-panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>🎵 Top 8 Albums</span>
                    @if(auth()->id() === $user->id)
                        <button class="xp-edit-btn" onclick="alert('Edit Top 8 Albums coming soon!')" style="font-size: 0.5rem; padding: 0.05rem 0.5rem;">Edit</button>
                    @endif
                </div>
                <div class="xp-panel-body">
                    @php
                        $topAlbums = $user->profile->top_albums ?? [];
                    @endphp
                    @if(count($topAlbums) > 0)
                        <div style="display: flex; flex-wrap: wrap; gap: 0.3rem;">
                            @foreach($topAlbums as $album)
                                <span class="xp-top-badge">{{ $album }}</span>
                            @endforeach
                        </div>
                    @else
                        <span style="font-size: 0.7rem; color: #6a6a6a;">No top albums set yet</span>
                    @endif
                </div>
            </div>

            <!-- ===== REVAMPED FRIENDS PANEL ===== -->
            <div class="xp-panel">
                <div class="xp-panel-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <span>👥 Top 8 Friends</span>
                    @if(auth()->id() === $user->id)
                        <button class="xp-edit-btn" onclick="alert('Edit Top 8 Friends coming soon!')" style="font-size: 0.5rem; padding: 0.05rem 0.5rem;">Edit</button>
                    @endif
                </div>
                <div class="xp-panel-body">
                    @php
                        $topFriends = $user->profile->top_friends ?? [];
                        $friendUsers = $topFriends ? App\Models\User::whereIn('id', $topFriends)->get() : collect();
                    @endphp
                    @if($friendUsers->count() > 0)
                        <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                            @foreach($friendUsers as $index => $friend)
                                <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.2rem 0.4rem; background: #f8f5ec; border: 1px solid #d0c8c0; border-radius: 4px;">
                                    <span style="font-size: 0.65rem; font-weight: 700; color: #1a4a9e; min-width: 20px;">#{{ $index + 1 }}</span>
                                    <div class="xp-friend-avatar" style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #3a7bd5, #1a4a9e); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.65rem; color: #ffffff; flex-shrink: 0;">
                                        {{ $friend->name[0] }}
                                    </div>
                                    <a href="{{ route('profile.show', $friend) }}" style="font-size: 0.75rem; color: #1e1e1e; text-decoration: none; flex: 1;">
                                        {{ $friend->display_name }}
                                    </a>
                                    <span style="font-size: 0.55rem; color: #6a6a6a; font-style: italic;">⭐</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span style="font-size: 0.7rem; color: #6a6a6a;">No top friends set yet</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- ===== RIGHT COLUMN: Posts ===== -->
        <div class="xp-profile-right">
            <div class="xp-panel">
                <div class="xp-panel-header">Posts</div>
                <div class="xp-panel-body">

                    @if(auth()->id() === $user->id)
                        <form action="{{ route('posts.store') }}" method="POST" style="margin-bottom: 0.75rem;">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->id }}">
                            <textarea name="content" class="settings-input" rows="2" placeholder="Share something..." style="resize: none;"></textarea>
                            <button type="submit" class="settings-btn" style="margin-top: 0.3rem;">Post</button>
                        </form>
                        <hr class="xp-divider">
                    @endif

                   @forelse($user->posts as $post)
                    <div class="xp-post">
                        <div class="xp-post-header">
                            <span class="xp-post-user">{{ $user->display_name }}</span>
                            <span class="xp-post-time">{{ $post->created_at->diffForHumans() }}</span>
                        </div>
                        <div class="xp-post-content">{{ $post->content }}</div>
                        <div class="xp-post-actions">
                            <span>Like</span>
                            <span>Comment</span>
                            <span>Share</span>
                            @if(auth()->id() === $post->user_id)
                                <form action="{{ route('posts.destroy', $post) }}" method="POST" style="display: inline; margin-left: auto;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="xp-delete-btn">Delete</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p style="font-size: 0.75rem; color: #6a6a6a; text-align: center; padding: 1rem 0;">
                        No posts yet
                    </p>
                @endforelse

                </div>
            </div>
        </div>

    </div>
</div>

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
            bioDisplay.style.display = 'none';
        } else {
            editMode.style.display = 'none';
            editBtn.textContent = 'Edit Profile';
            displayName.style.display = 'block';
            bioDisplay.style.display = 'block';
        }
    }
</script>

@endsection