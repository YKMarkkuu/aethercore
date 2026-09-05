<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StatusController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'status' => 'required|in:online,idle,dnd,offline',
        ]);

        $user = Auth::user();
        $user->status = $request->status;
        $user->save();

        return back()->with('success', 'Status updated!');
    }
}