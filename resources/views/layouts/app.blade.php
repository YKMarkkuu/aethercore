<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AetherCore - @yield('title', 'Home')</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
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
        </div>
    </div>

    <!-- SETTINGS MODAL -->
    @include('partials.settings-modal')

    <script>
        let currentMode = 'aether';
        let currentView = 'feed';

        // ===== SET VIEW FROM URL =====
        function setViewFromUrl() {
            const path = window.location.pathname;
            
            if (path === '/feed' || path === '/') {
                currentView = 'feed';
            } else if (path === '/spaces') {
                currentView = 'spaces';
            } else if (path === '/friends') {
                currentView = 'friends';
            } else if (path === '/profile' || path.startsWith('/profile/')) {
                // If we're on a profile page, keep the current view
                // Don't change the tab highlight
                return;
            } else {
                currentView = 'feed';
            }
            
            // Update sidebar view
            document.getElementById('view-feed').classList.add('hidden');
            document.getElementById('view-spaces').classList.add('hidden');
            document.getElementById('view-friends').classList.add('hidden');
            
            const viewElement = document.getElementById('view-' + currentView);
            if (viewElement) {
                viewElement.classList.remove('hidden');
            }
            
            // Update active tab
            document.querySelectorAll('.sidebar-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            document.querySelectorAll('.sidebar-tab').forEach(tab => {
                const tabText = tab.textContent.toLowerCase();
                if (tabText.includes(currentView) ||
                    (currentView === 'feed' && tabText.includes('feed')) ||
                    (currentView === 'spaces' && tabText.includes('spaces')) ||
                    (currentView === 'friends' && tabText.includes('friends'))) {
                    tab.classList.add('active');
                }
            });
        }

        // ===== MODE SWITCHING =====
        function switchMode(mode) {
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
                
                const path = window.location.pathname;
                const validSocialPages = ['/feed', '/spaces', '/friends', '/profile'];
                const isProfilePage = path.startsWith('/profile/');
                
                if (!validSocialPages.includes(path) && !isProfilePage) {
                    window.location.href = '/feed';
                } else {
                    setViewFromUrl();
                }
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
                
                if (window.location.pathname !== '/music') {
                    window.location.href = '/music';
                }
            }
        }

        // ===== VIEW SWITCHING =====
        function switchView(view) {
            currentView = view;

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

        // ===== SETTINGS MODAL =====
        function openSettings() {
            const modal = document.getElementById('settingsModal');
            if (modal) {
                modal.classList.remove('hidden');
                const popup = document.getElementById('profilePopup');
                if (popup) {
                    popup.classList.add('hidden');
                }
            }
        }

        function closeSettings() {
            const modal = document.getElementById('settingsModal');
            if (modal) {
                modal.classList.add('hidden');
            }
        }

        // ===== SETTINGS TAB SWITCHING =====
        function switchSettingsTab(tab) {
            document.querySelectorAll('.settings-tab').forEach(t => {
                t.classList.add('hidden');
            });
            
            document.getElementById('settings-' + tab).classList.remove('hidden');
            
            document.querySelectorAll('.settings-nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            document.querySelectorAll('.settings-nav-item').forEach(item => {
                if (item.textContent.toLowerCase().includes(tab)) {
                    item.classList.add('active');
                }
            });
        }

        // ===== ON PAGE LOAD =====
        document.addEventListener('DOMContentLoaded', function() {
            const currentPath = window.location.pathname;
            
            if (currentPath === '/music') {
                switchMode('music');
            } else {
                switchMode('aether');
            }
            
            setViewFromUrl();
        });
    </script>
</body>
</html>