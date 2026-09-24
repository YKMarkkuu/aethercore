<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'pending_reports' => Report::where('status', 'pending')->count(),
            'total_users' => User::count(),
            'suspended_users' => User::whereNotNull('suspended_until')->where('suspended_until', '>', now())->count(),
            'banned_users' => User::whereNotNull('banned_at')->count(),
            'new_today' => User::whereDate('created_at', today())->count(),
        ];

        $recentReports = Report::with(['reporter', 'reportable'])
            ->where('status', 'pending')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentReports'));
    }
}
