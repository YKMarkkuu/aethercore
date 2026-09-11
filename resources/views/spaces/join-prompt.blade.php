@extends('layouts.app')

@section('title', $space->name)
@section('content')

<div class="card" style="max-width: 400px; margin: 2rem auto; text-align: center;">
    <div class="space-icon" style="width: 72px; height: 72px; font-size: 1.5rem; margin: 0 auto 0.75rem;">
        @if($space->getIconUrl())
            <img src="{{ $space->getIconUrl() }}" alt="{{ $space->name }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
        @else
            {{ strtoupper($space->name[0] ?? '?') }}
        @endif
    </div>
    <h2 style="font-size: 1.1rem; color: #1e1e1e; margin-bottom: 0.2rem;">{{ $space->name }}</h2>
    @if($space->description)
        <p style="font-size: 0.8rem; color: #6a6a6a; margin-bottom: 0.5rem;">{{ $space->description }}</p>
    @endif
    <p style="font-size: 0.7rem; color: #6a6a6a; margin-bottom: 1rem;">
        {{ $space->members->count() }} {{ \Illuminate\Support\Str::plural('member', $space->members->count()) }}
    </p>

    <form action="{{ route('spaces.join', $space) }}" method="POST">
        @csrf
        <button type="submit" class="settings-btn" style="width: 100%;">Join Space</button>
    </form>
</div>

@endsection