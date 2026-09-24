@extends('layouts.admin')
@section('title', 'Dashboard')
@section('content')

<h2 style="font-size: 1rem; color: #1e1e1e; margin-bottom: 1rem;">Overview</h2>

<div class="admin-stats-grid">
    <div class="admin-stat-card">
        <span class="admin-stat-number">{{ $stats['pending_reports'] }}</span>
        <span class="admin-stat-label">Pending Reports</span>
    </div>
    <div class="admin-stat-card">
        <span class="admin-stat-number">{{ $stats['total_users'] }}</span>
        <span class="admin-stat-label">Total Users</span>
    </div>
    <div class="admin-stat-card">
        <span class="admin-stat-number">{{ $stats['suspended_users'] }}</span>
        <span class="admin-stat-label">Suspended</span>
    </div>
    <div class="admin-stat-card">
        <span class="admin-stat-number">{{ $stats['banned_users'] }}</span>
        <span class="admin-stat-label">Banned</span>
    </div>
    <div class="admin-stat-card">
        <span class="admin-stat-number">{{ $stats['new_today'] }}</span>
        <span class="admin-stat-label">New Today</span>
    </div>
</div>

<div class="xp-panel">
    <div class="xp-panel-header" style="display: flex; justify-content: space-between; align-items: center;">
        <span>Latest Pending Reports</span>
        <a href="{{ route('admin.reports.index') }}" style="font-size: 0.7rem;">View all &#8594;</a>
    </div>
    <div class="xp-panel-body">
        @forelse($recentReports as $report)
            <div style="display: flex; justify-content: space-between; font-size: 0.75rem; padding: 0.4rem 0.2rem; border-bottom: 1px solid #e0dcd0;">
                <span>
                    <strong>{{ class_basename($report->reportable_type) }}</strong>
                    - {{ str_replace('_', ' ', $report->reason) }}
                    @if($report->reporter)
                        by {{ $report->reporter->display_name }}
                    @endif
                </span>
                <span style="color: #6a6a6a;">{{ $report->created_at->diffForHumans() }}</span>
            </div>
        @empty
            <p style="font-size: 0.75rem; color: #6a6a6a; padding: 0.5rem 0;">Nothing pending - you're caught up.</p>
        @endforelse
    </div>
</div>

@endsection
