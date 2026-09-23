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
</head>
<body style="height: auto; overflow-y: auto;">
    <div style="max-width: 1100px; margin: 0 auto; padding: 1.5rem 1rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.5rem;">
            <h1 style="font-size: 1.1rem; color: #1a4a9e;">⚙️ AetherCore Admin</h1>
            <nav style="display: flex; gap: 0.5rem;">
                <a href="{{ route('admin.reports.index') }}" class="settings-btn" style="text-decoration: none;">Reports</a>
                <a href="{{ route('admin.users.index') }}" class="settings-btn" style="text-decoration: none;">Users</a>
                <a href="{{ route('feed') }}" class="settings-btn" style="text-decoration: none;">← Back to App</a>
            </nav>
        </div>

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
    </div>
</body>
</html>
