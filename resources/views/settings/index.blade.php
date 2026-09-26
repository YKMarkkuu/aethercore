@extends('layouts.app')

@section('title', 'Settings')
@section('content')
    <div class="xp-panel" style="height: 75vh; min-height: 500px; overflow: hidden; display: flex; margin-bottom: 0;">
        @include('partials.settings-content')
    </div>
@endsection
