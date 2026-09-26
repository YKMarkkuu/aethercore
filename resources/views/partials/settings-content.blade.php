@php
    // Settings modal ALWAYS operates on the logged-in user, regardless
    // of which page it's included on. Previously this used `$user ?? Auth::user()`
    // which leaked another user's settings when the modal was opened
    // from a profile page (where $user = the profile owner).
    $user = Auth::user();

    $categories = [
        'account' => [
            'label' => 'Account',
            'icon' => 'person',
            'subs' => [
                'account-info' => 'Account Info',
                'security' => 'Password & Security',
                'sessions' => 'Logged-in Devices',
                'privacy-visibility' => 'Profile Visibility',
                'privacy-messages' => 'Direct Messages',
                'privacy-status' => 'Activity Status',
                'blocked-users' => 'Blocked Users',
            ],
        ],
        'notifications' => [
            'label' => 'Notifications',
            'icon' => 'chat',
            'subs' => [
                'notifications' => 'Notification Preferences',
            ],
        ],
        'data' => [
            'label' => 'Data & Privacy',
            'icon' => 'lock',
            'subs' => [
                'data-export' => 'Download Your Data',
                'legal' => 'Terms & Privacy Policy',
            ],
        ],
        'appearance' => [
            'label' => 'Appearance',
            'icon' => 'palette',
            'subs' => [
                'theme' => 'Theme',
            ],
        ],
        'music' => [
            'label' => 'Music',
            'icon' => 'music',
            'subs' => [
                'lastfm' => 'Last.fm Connection',
            ],
        ],
        'danger' => [
            'label' => 'Danger Zone',
            'icon' => 'document',
            'subs' => [
                'danger-delete' => 'Delete Account',
            ],
        ],
    ];

    $defaultCategory = array_key_first($categories);
@endphp

<div class="settings-v2" data-settings-v2>

    {{-- ===== LEFT SIDEBAR ===== --}}
    <aside class="settings-v2-sidebar">
        <div class="settings-v2-user">
            <div class="settings-v2-avatar">
                @if($user->getAvatarUrl())
                    <img src="{{ $user->getAvatarUrl() }}" alt="Avatar">
                @else
                    {{ $user->name[0] ?? '?' }}
                @endif
            </div>
            <div>
                <div class="settings-v2-username">{{ $user->display_name }}</div>
                <a href="{{ route('profile.index') }}" class="settings-v2-edit-link">Edit Profile</a>
            </div>
        </div>

        <div class="settings-v2-search">
            <input type="text" placeholder="Search" disabled>
        </div>

        <nav class="settings-v2-nav">
            @foreach($categories as $catKey => $cat)
                <div class="settings-v2-group {{ $catKey === $defaultCategory ? 'active' : '' }}" data-group="{{ $catKey }}">
                    <button type="button" class="settings-v2-group-btn" data-group-toggle="{{ $catKey }}">
                        <span class="settings-v2-group-icon">@include('partials.icon', ['type' => $cat['icon'], 'size' => 15])</span>
                        <span>{{ $cat['label'] }}</span>
                    </button>
                    <div class="settings-v2-subs {{ $catKey === $defaultCategory ? '' : 'hidden' }}" data-group-subs="{{ $catKey }}">
                        @foreach($cat['subs'] as $subKey => $subLabel)
                            <button type="button" class="settings-v2-sublink" data-scroll-target="section-{{ $subKey }}" data-parent-group="{{ $catKey }}">
                                {{ $subLabel }}
                            </button>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </nav>
    </aside>

    {{-- ===== RIGHT: ONE SCROLLABLE PANE PER CATEGORY ===== --}}
    <div class="settings-v2-content-wrap">
        @foreach($categories as $catKey => $cat)
            <div class="settings-v2-scrollarea {{ $catKey === $defaultCategory ? '' : 'hidden' }}"
                 data-category-content="{{ $catKey }}">

                @if(session('success'))
                    <div class="settings-alert success">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="settings-alert error">{{ session('error') }}</div>
                @endif

                @foreach($cat['subs'] as $subKey => $subLabel)
                    <section id="section-{{ $subKey }}" class="settings-v2-section" data-section="{{ $subKey }}">
                        <h2 class="settings-v2-h2">{{ $subLabel }}</h2>

                        @includeIf('partials.settings-sections.' . $subKey, ['user' => $user])
                    </section>
                @endforeach

            </div>
        @endforeach
    </div>
</div>
