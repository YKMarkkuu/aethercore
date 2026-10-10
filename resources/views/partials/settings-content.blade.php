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

<div class="settings-v2"
     data-settings-v2
     data-active-category="{{ $defaultCategory }}"
     data-active-bulk="{{ $categories[$defaultCategory]['bulk'] ? 'true' : 'false' }}"
     data-dirty="false">

    <div class="settings-v2-body">

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
                    <div class="settings-v2-group {{ $catKey === $defaultCategory ? 'active' : '' }}"
                         data-group="{{ $catKey }}"
                         data-bulk="{{ $cat['bulk'] ? 'true' : 'false' }}">
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
                     data-category-content="{{ $catKey }}"
                     data-bulk="{{ $cat['bulk'] ? 'true' : 'false' }}">

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

    {{-- ===== SAVE BAR (inert until Step 4 JS) ===== --}}
    <div class="settings-v2-savebar" data-savebar>
        <div class="settings-savebar-status" data-savebar-status>All changes applied</div>
        <div class="settings-savebar-actions">
            <button type="button" class="settings-btn" data-savebar-apply disabled>Apply</button>
            <button type="button" class="settings-btn" data-savebar-cancel>Cancel</button>
            <button type="button" class="settings-btn" data-savebar-ok>OK</button>
        </div>
    </div>
</div>
