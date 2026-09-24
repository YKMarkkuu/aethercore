<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - @yield('title', 'Dashboard')</title>
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">
    <link rel="stylesheet" href="{{ asset('css/utilities.css') }}">
    <style>
        .admin-shell { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 200px; flex-shrink: 0;
            background: linear-gradient(180deg, #ece9d8 0%, #d4d0c8 100%);
            border-right: 2px solid #b0a8a0;
            padding: 1rem 0.6rem;
            box-sizing: border-box;
        }
        .admin-sidebar-title {
            font-size: 0.95rem; font-weight: 700; color: #1a4a9e;
            margin-bottom: 1rem; display: flex; align-items: center; gap: 0.4rem;
        }
        .admin-nav-link {
            display: flex; justify-content: space-between; align-items: center;
            padding: 0.4rem 0.6rem; border-radius: 4px; font-size: 0.8rem;
            color: #1e1e1e; text-decoration: none; margin-bottom: 0.2rem;
        }
        .admin-nav-link:hover { background: #e0dcd0; }
        .admin-nav-link.active { background: #3a7bd5; color: #ffffff; font-weight: 600; }
        .admin-nav-badge {
            background: #c04040; color: #ffffff; font-size: 0.6rem; font-weight: 700;
            border-radius: 10px; padding: 0.05rem 0.45rem; min-width: 1rem; text-align: center;
        }
        .admin-nav-link.active .admin-nav-badge { background: rgba(255, 255, 255, 0.3); }
        .admin-nav-divider { border-top: 1px solid #b0a8a0; margin: 0.6rem 0; }
        .admin-main { flex: 1; min-width: 0; padding: 1.5rem; overflow-y: auto; }
        .admin-stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 0.75rem; margin-bottom: 1.5rem;
        }
        .admin-stat-card {
            background: #f0edd8; border: 2px solid #b0a8a0; border-radius: 6px; padding: 0.75rem 1rem;
        }
        .admin-stat-number { font-size: 1.4rem; font-weight: 700; color: #1a4a9e; display: block; }
        .admin-stat-label { font-size: 0.6rem; text-transform: uppercase; color: #6a6a6a; letter-spacing: 0.3px; }
        .admin-filter-bar { display: flex; gap: 0.5rem; margin-bottom: 1rem; flex-wrap: wrap; align-items: center; }
        .admin-filter-bar select, .admin-filter-bar input[type="text"] { width: auto; }
    </style>
</head>
<body style="height: auto; overflow-y: auto; overflow-x: hidden;">
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <div class="admin-sidebar-title">🛡️ Admin</div>

            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span>Dashboard</span>
            </a>
            <a href="{{ route('admin.reports.index') }}" class="admin-nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <span>Reports</span>
                @if(($pendingReportsCount ?? 0) > 0)
                    <span class="admin-nav-badge">{{ $pendingReportsCount }}</span>
                @endif
            </a>
            <a href="{{ route('admin.users.index') }}" class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <span>Users</span>
            </a>

            <div class="admin-nav-divider"></div>
            <a href="{{ route('feed') }}" class="admin-nav-link">← Back to App</a>
        </aside>

        <main class="admin-main">
            @if(session('success'))
                <div style="background: #d4e8d4; border: 2px solid #8ab88a; border-radius: 4px; padding: 0.5rem 1rem; margin-bottom: 1rem; color: #1e4a1e;">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background: #f0d8d8; border: 2px solid #c8a0a0; border-radius: 4px; padding: 0.5rem 1rem; margin-bottom: 1rem; color: #6a2a2a;">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</body>
</html>