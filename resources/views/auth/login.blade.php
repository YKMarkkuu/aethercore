@extends('layouts.auth')

@section('title', 'Login')
@section('subtitle', 'Welcome back to AetherCore!')

@section('content')
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div style="margin-bottom: 1rem;">
            <label class="auth-label" for="email">Email</label>
            <input class="auth-input" id="email" type="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <div class="auth-error">{{ $message }}</div>
            @enderror
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label class="auth-label" for="password">Password</label>
            <input class="auth-input" id="password" type="password" name="password" required>
            @error('password')
                <div class="auth-error">{{ $message }}</div>
            @enderror
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <label style="font-size: 0.85rem; color: #a0a0b8;">
                <input type="checkbox" name="remember"> Remember me
            </label>
            <a class="auth-link" href="{{ route('password.request') }}">Forgot password?</a>
        </div>

        <button class="auth-btn" type="submit">Sign In ✦</button>

        <div style="text-align: center; margin-top: 1rem; font-size: 0.9rem; color: #a0a0b8;">
            Don't have an account?
            <a class="auth-link" href="{{ route('register') }}">Create one</a>
        </div>
    </form>
@endsection