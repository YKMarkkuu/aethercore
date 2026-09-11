<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AetherCore - @yield('title', 'Home')</title>
    
        <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/utilities.css') }}">

    @if(Auth::check() && Auth::user()->theme && Auth::user()->theme !== 'aethercore')
        <link rel="stylesheet" href="{{ asset('css/themes/' . Auth::user()->theme . '.css') }}">
    @endif
</head>
<body>
    <div class="app">
        <!-- TOP BAR -->
        <header class="topbar">
            <div class="topbar-left">
                <span class="topbar-brand" id="appLogo">✦ AetherCore</span>
                <input class="topbar-search" id="searchInput" type="text" placeholder="Search friends, spaces, posts...">
            </div>
            
            <div class="topbar-center">
                <div class="mode-toggle">
                    <button class="mode-btn active" id="modeAether" onclick="switchMode('aether')">
                        ✦ AetherCore
                    </button>
                    <button class="mode-btn" id="modeMusic" onclick="switchMode('music')">
                        ♪ AetherTunes
                    </button>
                </div>
            </div>

            <div class="topbar-right">
                <button class="topbar-btn" id="notifBtn">🔔</button>
                <button class="topbar-btn" id="settingsBtn">⚙️</button>
            </div>
        </header>

        <!-- MAIN BODY -->
        <div class="main-body">
            <!-- LEFT SIDEBAR -->
            @include('partials.sidebar-left')

            <!-- MAIN CONTENT -->
            <main class="main-content">
                @yield('content')
            </main>

            <!-- RIGHT SIDEBAR -->
            @include('partials.sidebar-right')

            <!-- SETTINGS MODAL -->
            @include('partials.settings-modal')
        </div>
    </div>

    <!-- Loaded on every page since posts (and their like/comment/share
         actions) can show up in more than one place — see feed.blade.php
         and the profile page's Posts panel, both of which include the
         same partials.post-card. Event listeners are delegated on
         document.body and only do anything if .post-item elements are
         actually present, so this is a no-op on pages with no posts. -->
    <script src="{{ asset('js/post-interactions.js') }}"></script>

    <script>
        let currentMode = 'aether';
        let currentView = 'feed';

        // ===== STORE/LOAD SIDEBAR VIEW =====
        function saveView(view) {
            localStorage.setItem('sidebar_view', view);
        }

        function loadView() {
            return localStorage.getItem('sidebar_view') || 'feed';
        }

        // ===== UPDATE SIDEBAR VIEW =====
        function updateSidebarView(view) {
            document.getElementById('view-feed').classList.add('hidden');
            document.getElementById('view-spaces').classList.add('hidden');
            document.getElementById('view-friends').classList.add('hidden');
            
            const viewElement = document.getElementById('view-' + view);
            if (viewElement) {
                viewElement.classList.remove('hidden');
            }
            
            document.querySelectorAll('.sidebar-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.querySelectorAll('.sidebar-tab').forEach(tab => {
                const tabText = tab.textContent.toLowerCase();
                if (tabText.includes(view)) {
                    tab.classList.add('active');
                }
            });
        }

        // ===== SET VIEW FROM URL (ONLY ON PAGE LOAD) =====
        function setViewFromUrl() {
            const path = window.location.pathname;
            
            // Only change the sidebar view if we're on a main navigation page
            if (path === '/feed' || path === '/') {
                currentView = 'feed';
                saveView('feed');
                updateSidebarView('feed');
            } else if (path === '/spaces') {
                currentView = 'spaces';
                saveView('spaces');
                updateSidebarView('spaces');
            } else if (path === '/friends') {
                currentView = 'friends';
                saveView('friends');
                updateSidebarView('friends');
            }
            // On profile, chat, settings, or any other page, DON'T change the sidebar view
        }

        // ===== VIEW SWITCHING (User clicks tabs) =====
        function switchView(view) {
            currentView = view;
            saveView(view);

            document.getElementById('view-feed').classList.add('hidden');
            document.getElementById('view-spaces').classList.add('hidden');
            document.getElementById('view-friends').classList.add('hidden');

            document.getElementById('view-' + view).classList.remove('hidden');

            document.querySelectorAll('.sidebar-tab').forEach(tab => {
                tab.classList.remove('active');
            });

            document.querySelectorAll('.sidebar-tab').forEach(tab => {
                if (tab.textContent.toLowerCase().includes(view)) {
                    tab.classList.add('active');
                }
            });
        }

        // ===== MODE SWITCHING =====
        function switchMode(mode, isUserInitiated = true) {
            currentMode = mode;

            const aetherBtn = document.getElementById('modeAether');
            const musicBtn = document.getElementById('modeMusic');
            const tabs = document.getElementById('sidebar-tabs');
            
            if (mode === 'aether') {
                aetherBtn.classList.add('active');
                musicBtn.classList.remove('active');
                tabs.style.display = 'flex';
                
                const logo = document.getElementById('appLogo');
                const search = document.getElementById('searchInput');
                logo.textContent = '✦ AetherCore';
                search.placeholder = 'Search friends, spaces, posts...';
                
                document.getElementById('sidebar-aether').classList.remove('hidden');
                document.getElementById('sidebar-music').classList.add('hidden');
                
                // The ONLY real reason to redirect here: you were sitting
                // on the music-only page and just switched back to social
                // mode. Every other page Laravel routed you to is, by
                // definition, a valid page — no need to re-validate it
                // against a hardcoded list here (that list previously had
                // to be updated by hand every time a new route/feature
                // was added, e.g. Spaces, and it's exactly what silently
                // broke /spaces/{id} pages before this fix).
                //
                // isUserInitiated guards this from running on ordinary
                // page loads at all (see DOMContentLoaded below) — it
                // should only ever fire from an actual click on the mode
                // toggle buttons.
                if (isUserInitiated && window.location.pathname === '/music') {
                    window.location.href = '/feed';
                }

                // Restore the sidebar view from localStorage
                const savedView = loadView();
                currentView = savedView;
                updateSidebarView(savedView);
            } else {
                aetherBtn.classList.remove('active');
                musicBtn.classList.add('active');
                tabs.style.display = 'none';
                
                const logo = document.getElementById('appLogo');
                const search = document.getElementById('searchInput');
                logo.textContent = '♪ AetherTunes';
                search.placeholder = 'Search artists, songs, albums...';
                
                document.getElementById('sidebar-aether').classList.add('hidden');
                document.getElementById('sidebar-music').classList.remove('hidden');
                
                if (isUserInitiated && window.location.pathname !== '/music') {
                    window.location.href = '/music';
                }
            }
        }

        // ===== MINI PROFILE POPUP =====
        function toggleProfilePopup() {
            const popup = document.getElementById('profilePopup');
            popup.classList.toggle('hidden');
        }

        document.addEventListener('click', function(event) {
            const popup = document.getElementById('profilePopup');
            const miniProfile = document.querySelector('.mini-profile');
            
            if (!popup.classList.contains('hidden')) {
                if (!popup.contains(event.target) && !miniProfile.contains(event.target)) {
                    popup.classList.add('hidden');
                }
            }
        });

        // ===== STATUS MENU =====
        function toggleStatusMenu() {
            const menu = document.getElementById('statusMenu');
            menu.classList.toggle('hidden');
        }

        document.addEventListener('click', function(event) {
            const menu = document.getElementById('statusMenu');
            const button = document.querySelector('.popup-status-menu .popup-action');
            if (menu && button) {
                if (!menu.classList.contains('hidden')) {
                    if (!menu.contains(event.target) && !button.contains(event.target)) {
                        menu.classList.add('hidden');
                    }
                }
            }
        });

        // ===== ON PAGE LOAD =====
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            
            // Restore the sidebar view from localStorage
            const savedView = loadView();
            currentView = savedView;
            updateSidebarView(savedView);
            
            // Set up mode-specific UI (logo, search placeholder, sidebar
            // panel) based on the page we're already on — isUserInitiated:false
            // means this NEVER redirects, it just paints the right cosmetics.
            if (currentPath === '/music') {
                switchMode('music', false);
            } else {
                switchMode('aether', false);
            }
            
            // Only set the sidebar view on main navigation pages
            const isChatPage = currentPath === '/conversations' || currentPath.startsWith('/conversations/');
            const isProfilePage = currentPath === '/profile' || currentPath.startsWith('/profile/');
            const isSettingsPage = currentPath === '/settings' || currentPath.startsWith('/settings/');
            const isMainPage = currentPath === '/feed' || currentPath === '/' || currentPath === '/spaces' || currentPath === '/friends';
            
            if (isMainPage && !isChatPage && !isProfilePage && !isSettingsPage) {
                setViewFromUrl();
            }
            // On chat, profile, settings pages, the sidebar stays as-is
        });

        // ===== SETTINGS MODAL =====
        function openSettingsModal() {
            document.getElementById('settingsModal').classList.remove('hidden');
            const popup = document.getElementById('profilePopup');
            if (popup) popup.classList.add('hidden');
        }

        function closeSettings() {
            document.getElementById('settingsModal').classList.add('hidden');
        }

        function switchSettingsTab(tab) {
            document.querySelectorAll('.settings-tab').forEach(t => t.classList.add('hidden'));
            document.getElementById('settings-' + tab).classList.remove('hidden');
            document.querySelectorAll('.settings-nav-item').forEach(item => item.classList.remove('active'));
            document.querySelectorAll('.settings-nav-item').forEach(item => {
                if (item.textContent.toLowerCase().includes(tab)) item.classList.add('active');
            });
        }
    </script>
        @stack('scripts')
</body>
</html>