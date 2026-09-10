<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title', 'AetherCore')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">

    <!-- Styles -->
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a1a2e;
            min-height: 100vh;
        }

        /* Scrollbar styling - Windows XP style */
        ::-webkit-scrollbar {
            width: 16px;
            height: 16px;
        }

        ::-webkit-scrollbar-track {
            background: #e8e4dc;
            border-left: 1px solid #d0c8c0;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #f0edd8, #d4d0c8);
            border: 1px solid #b0a8a0;
            border-radius: 0;
        }

        ::-webkit-scrollbar-button {
            background: #d4d0c8;
            border: 1px solid #b0a8a0;
            height: 16px;
            width: 16px;
        }

        ::-webkit-scrollbar-button:decrement {
            border-bottom: none;
        }

        ::-webkit-scrollbar-button:increment {
            border-top: none;
        }
    </style>

    @stack('styles')
</head>
<body>
    <main>
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>