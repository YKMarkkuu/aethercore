<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AetherCore - @yield('title', 'Welcome')</title>
    <!-- base.css first: provides the shared :root theme variables and
         reset, same ones the logged-in app uses. This is what makes
         these pages inherit any future theme automatically instead of
         being a second hardcoded island. -->
    <link rel="stylesheet" href="{{ asset('css/base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/guest.css') }}">
</head>
<body class="guest-body">

    @yield('content')

    <div class="guest-legal-footer">
        &copy; {{ date('Y') }} AetherCore.
        <a href="{{ route('legal.terms') }}">Terms of Service</a>
        <span class="guest-legal-footer-sep">&bull;</span>
        <a href="{{ route('legal.privacy') }}">Privacy Policy</a>
    </div>

</body>
</html>