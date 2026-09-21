<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{
    public function update(Request $request)
    {
        $request->validate(['status' => 'required|in:online,idle,dnd,offline']);

        $user = Auth::user();
        $user->status = $request->status;
        // Picking a status explicitly should reflect immediately, not get
        // clobbered by a stale idle timeout from before they clicked.
        $user->last_seen_at = now();
        $user->last_active_at = now();
        $user->save();

        return back()->with('success', 'Status updated!');
    }

}