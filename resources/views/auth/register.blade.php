@extends('layouts.auth')

@section('title', 'Register')
@section('subtitle', 'Create your AetherCore account')

@section('content')
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div style="margin-bottom: 1rem;">
            <label class="auth-label" for="name">Name</label>
            <input class="auth-input" id="name" type="text" name="name" value="{{ old('name') }}" required autofocus>
            @error('name')
                <div class="auth-error">{{ $message }}</div>
            @enderror
        </div>

        <div style="margin-bottom: 1rem;">
            <label class="auth-label" for="email">Email</label>
            <input class="auth-input" id="email" type="email" name="email" value="{{ old('email') }}" required>
            @error('email')
                <div class="auth-error">{{ $message }}</div>
            @enderror
        </div>

        <div style="margin-bottom: 1rem;">
            <label class="auth-label" for="password">Password</label>
            <input class="auth-input" id="password" type="password" name="password" required>
            @error('password')
                <div class="auth-error">{{ $message }}</div>
            @enderror
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label class="auth-label" for="password_confirmation">Confirm Password</label>
            <input class="auth-input" id="password_confirmation" type="password" name="password_confirmation" required>
        </div>

        <button class="auth-btn" type="submit">Create Account ✦</button>

        <div style="text-align: center; margin-top: 1rem; font-size: 0.9rem; color: #a0a0b8;">
            Already have an account?
            <a class="auth-link" href="{{ route('login') }}">Sign in</a>
        </div>
    </form>
@endsection