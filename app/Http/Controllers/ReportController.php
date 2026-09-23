<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'reportable_type' => 'required|string|in:' . implode(',', array_keys(Report::REPORTABLE_TYPES)),
            'reportable_id' => 'required|integer',
            'reason' => 'required|string|in:' . implode(',', Report::REASONS),
            'details' => 'nullable|string|max:1000',
        ]);

        // The client sends a short key ("post", "user", ...) which is
        // mapped through the allowlist in Report::REPORTABLE_TYPES —
        // never trust a raw ::class string from the request.
        $modelClass = Report::REPORTABLE_TYPES[$request->reportable_type];

        $target = $modelClass::find($request->reportable_id);
        if (!$target) {
            return response()->json(['error' => 'That content no longer exists.'], 404);
        }

        $ownerId = $request->reportable_type === 'user' ? $target->id : ($target->user_id ?? null);
        if ($ownerId === Auth::id()) {
            return response()->json(['error' => 'You cannot report your own content.'], 422);
        }

        // One OPEN report per (reporter, target): resubmitting while a
        // prior report is still pending updates it in place instead of
        // padding the admin queue with duplicates. Once that report has
        // been reviewed (actioned/dismissed), a fresh report starts a
        // new row, so repeat bad behavior after a dismissal is still
        // reportable.
        $report = Report::updateOrCreate(
            [
                'reporter_id' => Auth::id(),
                'reportable_type' => $modelClass,
                'reportable_id' => $target->id,
                'status' => 'pending',
            ],
            [
                'reason' => $request->reason,
                'details' => $request->details,
            ]
        );

        return response()->json(['success' => true, 'report_id' => $report->id]);
    }
}
