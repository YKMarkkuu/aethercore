@extends('layouts.admin')
@section('title', 'Reports')
@section('content')

<div style="display: flex; gap: 0.5rem; margin-bottom: 1rem;">
    @foreach(['pending' => 'Pending', 'actioned' => 'Actioned', 'dismissed' => 'Dismissed', 'all' => 'All'] as $key => $label)
        <a href="{{ route('admin.reports.index', ['status' => $key]) }}"
           class="settings-btn {{ $status === $key ? 'settings-btn-danger' : '' }}"
           style="text-decoration: none;">{{ $label }}</a>
    @endforeach
</div>

<div class="xp-panel">
    <div class="xp-panel-header">Reports ({{ $reports->total() }})</div>
    <div class="xp-panel-body">
        @forelse($reports as $report)
            <div style="border-bottom: 1px solid #e0dcd0; padding: 0.6rem 0.3rem;">
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #6a6a6a; flex-wrap: wrap; gap: 0.3rem;">
                    <span>
                        <strong style="color: #1e1e1e;">{{ class_basename($report->reportable_type) }}</strong>
                        reported by
                        @if($report->reporter)
                            <a href="{{ route('profile.show', $report->reporter) }}">{{ $report->reporter->display_name }}</a>
                        @else
                            <em>a deleted account</em>
                        @endif
                        — <span style="text-transform: capitalize;">{{ str_replace('_', ' ', $report->reason) }}</span>
                    </span>
                    <span>{{ $report->created_at->diffForHumans() }}</span>
                </div>

                @if($report->details)
                    <div style="font-size: 0.75rem; color: #1e1e1e; margin-top: 0.2rem;">&ldquo;{{ $report->details }}&rdquo;</div>
                @endif

                <div style="font-size: 0.75rem; background: #f8f5ec; border: 1px solid #d0c8c0; border-radius: 4px; padding: 0.4rem 0.6rem; margin-top: 0.4rem;">
                    @if($report->reportable)
                        @if($report->reportable_type === \App\Models\User::class)
                            <a href="{{ route('profile.show', $report->reportable) }}">{{ $report->reportable->display_name }}</a>
                            (@ {{ $report->reportable->username }})
                        @else
                            {{ \Illuminate\Support\Str::limit(strip_tags($report->reportable->content ?? ''), 200) }}
                        @endif
                    @else
                        <em style="color: #9a9488;">This content no longer exists.</em>
                    @endif
                </div>

                @if($report->status === 'pending')
                    <div style="display: flex; gap: 0.4rem; margin-top: 0.5rem; flex-wrap: wrap;">
                        <form action="{{ route('admin.reports.dismiss', $report) }}" method="POST">
                            @csrf
                            <button type="submit" class="settings-btn" style="font-size: 0.7rem;">Dismiss</button>
                        </form>

                        @if($report->reportable_type !== \App\Models\User::class)
                            <form action="{{ route('admin.reports.delete-content', $report) }}" method="POST" onsubmit="return confirm('Delete this content?')">
                                @csrf
                                <button type="submit" class="settings-btn settings-btn-danger" style="font-size: 0.7rem;">Delete Content</button>
                            </form>
                        @endif

                        @php
                            $targetUser = $report->reportable_type === \App\Models\User::class
                                ? $report->reportable
                                : ($report->reportable->user ?? null);
                        @endphp
                        @if($targetUser)
                            <a href="{{ route('admin.users.index', ['search' => $targetUser->username]) }}" class="settings-btn" style="font-size: 0.7rem; text-decoration: none;">Review User →</a>
                        @endif
                    </div>
                @else
                    <div style="font-size: 0.65rem; color: #6a6a6a; margin-top: 0.4rem;">
                        {{ ucfirst($report->status) }} by {{ $report->reviewer->display_name ?? 'system' }} {{ $report->reviewed_at?->diffForHumans() }}
                    </div>
                @endif
            </div>
        @empty
            <p style="font-size: 0.8rem; color: #6a6a6a; text-align: center; padding: 1rem 0;">No reports here.</p>
        @endforelse
    </div>
</div>

<div style="margin-top: 1rem;">{{ $reports->links() }}</div>

@endsection
