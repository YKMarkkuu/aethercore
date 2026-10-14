@php
    // Settings modal ALWAYS operates on the logged-in user, regardless
    // of which page it's included on. Previously this used `$user ?? Auth::user()`
    // which leaked another user's settings when the modal was opened
    // from a profile page (where $user = the profile owner).
    $user = Auth::user();

    // 'bulk' => true marks categories whose controls are saved together
    // via the Apply / OK / Cancel save bar (wired up in Step 4).
    $categories = [
        'account' => [
            'label' => 'Account',
            'icon' => 'person',
            'bulk' => false,
            'subs' => [
                'account-info' => 'Account Info',
                'security' => 'Password & Security',
                'sessions' => 'Logged-in Devices',
                'blocked-users' => 'Blocked Users',
            ],
        ],
        'privacy' => [
            'label' => 'Privacy',
            'icon' => 'shield',
            'bulk' => true,
            'subs' => [
                'privacy-visibility' => 'Profile Visibility',
                'privacy-messages' => 'Direct Messages',
                'privacy-comments' => 'Post Comments',
                'privacy-status' => 'Activity Status',
            ],
        ],
        'notifications' => [
            'label' => 'Notifications',
            'icon' => 'bell',
            'bulk' => true,
            'subs' => [
                'notifications' => 'Notification Preferences',
            ],
        ],
        'appearance' => [
            'label' => 'Appearance',
            'icon' => 'palette',
            'bulk' => true,
            'subs' => [
                'theme' => 'Theme',
            ],
        ],
        'music' => [
            'label' => 'Music',
            'icon' => 'music',
            'bulk' => false,
            'subs' => [
                'lastfm' => 'Last.fm Connection',
            ],
        ],
        'your-data' => [
            'label' => 'Your Data',
            'icon' => 'document',
            'bulk' => false,
            'subs' => [
                'data-export' => 'Download Your Data',
                'legal' => 'Terms & Privacy Policy',
            ],
        ],
        'danger' => [
            'label' => 'Danger Zone',
            'icon' => 'lock',
            'bulk' => false,
            'subs' => [
                'danger-delete' => 'Delete Account',
            ],
        ],
    ];

    $defaultCategory = array_key_first($categories);
@endphp

<div class="settings"
     data-settings
     data-active-category="{{ $defaultCategory }}"
     data-active-bulk="{{ $categories[$defaultCategory]['bulk'] ? 'true' : 'false' }}"
     data-dirty="false">

    <div class="settings-body">

        {{-- ===== LEFT SIDEBAR ===== --}}
        <aside class="settings-sidebar">
            <div class="settings-user">
                <div class="settings-avatar">
                    @if($user->getAvatarUrl())
                        <img src="{{ $user->getAvatarUrl() }}" alt="Avatar">
                    @else
                        {{ $user->name[0] ?? '?' }}
                    @endif
                </div>
                <div>
                    <div class="settings-username">{{ $user->display_name }}</div>
                    <a href="{{ route('profile.index') }}" class="settings-edit-link">Edit Profile</a>
                </div>
            </div>

            <div class="settings-search">
                <input type="text" placeholder="Search" disabled>
            </div>

            <nav class="settings-nav">
                @foreach($categories as $catKey => $cat)
                    <div class="settings-group {{ $catKey === $defaultCategory ? 'active' : '' }}"
                         data-group="{{ $catKey }}"
                         data-bulk="{{ $cat['bulk'] ? 'true' : 'false' }}">
                        <button type="button" class="settings-group-btn" data-group-toggle="{{ $catKey }}">
                            <span class="settings-group-icon">@include('partials.icon', ['type' => $cat['icon'], 'size' => 15])</span>
                            <span>{{ $cat['label'] }}</span>
                        </button>
                        <div class="settings-subs {{ $catKey === $defaultCategory ? '' : 'hidden' }}" data-group-subs="{{ $catKey }}">
                            @foreach($cat['subs'] as $subKey => $subLabel)
                                <button type="button" class="settings-sublink" data-scroll-target="section-{{ $subKey }}" data-parent-group="{{ $catKey }}">
                                    {{ $subLabel }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
        </aside>

        {{-- ===== RIGHT: ONE SCROLLABLE PANE PER CATEGORY ===== --}}
        <div class="settings-content-wrap">
            @foreach($categories as $catKey => $cat)
                <div class="settings-scrollarea {{ $catKey === $defaultCategory ? '' : 'hidden' }}"
                     data-category-content="{{ $catKey }}"
                     data-bulk="{{ $cat['bulk'] ? 'true' : 'false' }}">

                    @foreach($cat['subs'] as $subKey => $subLabel)
                        <section id="section-{{ $subKey }}" class="settings-section" data-section="{{ $subKey }}">
                            <h2 class="settings-h2">{{ $subLabel }}</h2>

                            @includeIf('partials.settings-sections.' . $subKey, ['user' => $user])
                        </section>
                    @endforeach

                </div>
            @endforeach
        </div>
    </div>

    {{-- ===== SAVE BAR (inert until Step 4 JS) ===== --}}
    <div class="settings-savebar" data-savebar>
        <div class="settings-savebar-status" data-savebar-status>All changes applied</div>
        <div class="settings-savebar-actions">
            <button type="button" class="settings-btn" data-savebar-apply disabled>Apply</button>
            <button type="button" class="settings-btn" data-savebar-cancel>Cancel</button>
            <button type="button" class="settings-btn" data-savebar-ok>OK</button>
        </div>
    </div>
</div>
