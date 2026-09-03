<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AetherCore - @yield('title', 'Auth')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: #0b0b14;
            color: #e0e0e0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .auth-card {
            background: #14142a;
            border-radius: 24px;
            padding: 2.5rem;
            max-width: 440px;
            width: 100%;
            border: 1px solid #1e1e38;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
        }

        .auth-brand {
            text-align: center;
            margin-bottom: 2rem;
        }

        .auth-brand h1 {
            font-size: 2rem;
            font-weight: 800;
            color: #6C63FF;
            letter-spacing: -0.5px;
        }

        .auth-brand p {
            color: #a0a0b8;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        .auth-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 12px;
            background: #1a1a2e;
            border: 1px solid #1e1e38;
            color: white;
            font-size: 0.95rem;
            transition: 0.2s;
        }

        .auth-input:focus {
            outline: none;
            border-color: #6C63FF;
            box-shadow: 0 0 0 3px rgba(108, 99, 255, 0.2);
        }

        .auth-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 500;
            color: #a0a0b8;
            margin-bottom: 0.4rem;
        }

        .auth-btn {
            width: 100%;
            padding: 0.75rem;
            border-radius: 12px;
            background: #6C63FF;
            color: white;
            border: none;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .auth-btn:hover {
            background: #5a52d5;
            transform: translateY(-2px);
        }

        .auth-link {
            color: #6C63FF;
            text-decoration: none;
            font-weight: 500;
        }

        .auth-link:hover {
            text-decoration: underline;
        }

        .auth-divider {
            border: none;
            border-top: 1px solid #1e1e38;
            margin: 1.5rem 0;
        }

        .auth-error {
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: 0.3rem;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-brand">
            <h1>✦ AetherCore</h1>
            <p>@yield('subtitle', 'Welcome back!')</p>
        </div>

        @yield('content')

        <hr class="auth-divider">
        <div style="text-align: center; font-size: 0.8rem; color: #555;">
            ⚡ AetherCore v1.0
        </div>
    </div>
</body>
</html>