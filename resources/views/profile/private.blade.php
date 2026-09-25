@extends('layouts.app')

@section('title', 'Private Profile')

@section('content')
<div class="card" style="max-width: 400px; margin: 2rem auto; text-align: center;">
    <div class="space-icon" style="width: 72px; height: 72px; font-size: 1.5rem; margin: 0 auto 0.75rem;">
        {{ strtoupper($user->name[0] ?? '?') }}
    </div>
    <h2 style="font-size: 1.1rem; color: #1e1e1e; margin-bottom: 0.4rem;">{{ $user->display_name }}</h2>
    <p style="font-size: 0.8rem; color: #6a6a6a;">
        @if(($user->profile->visibility ?? 'public') === 'friends')
            This profile is only visible to friends.
        @else
            This profile is private.
        @endif
    </p>
</div>
@endsection