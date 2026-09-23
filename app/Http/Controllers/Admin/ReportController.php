<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'pending');

        $reports = Report::with(['reporter', 'reportable', 'reviewer'])
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        return view('admin.reports.index', compact('reports', 'status'));
    }

    public function dismiss(Report $report)
    {
        $report->update([
            'status' => 'dismissed',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Report dismissed.');
    }

    /**
     * Deletes the reported content (post/comment/message) and marks the
     * report actioned. Deliberately does NOT handle reported Users —
     * banning/suspending/deleting an account is a bigger decision than
     * one click on a reports list, so that always routes through
     * Admin\UserController from the Users screen instead.
     */
    public function deleteContent(Report $report)
    {
        if ($report->reportable_type === User::class) {
            return back()->withErrors(['report' => 'Use the Users screen to act on a reported user.']);
        }

        $report->reportable?->delete();

        $report->update([
            'status' => 'actioned',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success', 'Content removed.');
    }
}
