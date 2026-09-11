<?php

namespace App\Http\Controllers;

use App\Models\Space;
use App\Models\SpaceChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpaceChannelController extends Controller
{
    public function store(Request $request, Space $space)
    {
        if (!$space->isOwner(Auth::id())) {
            abort(403, 'Only the Space owner can create channels.');
        }

        $request->validate([
            'name' => 'required|string|max:50|regex:/^[a-z0-9\-]+$/',
        ]);

        $channel = SpaceChannel::create([
            'space_id' => $space->id,
            'name' => $request->name,
            'position' => $space->channels()->max('position') + 1,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'channel' => ['id' => $channel->id, 'name' => $channel->name],
            ]);
        }

        return redirect()->route('spaces.channel', [$space, $channel]);
    }

    public function destroy(SpaceChannel $channel)
    {
        $space = $channel->space;

        if (!$space->isOwner(Auth::id())) {
            abort(403, 'Only the Space owner can delete channels.');
        }

        if ($space->channels()->count() <= 1) {
            abort(422, 'A Space needs at least one channel.');
        }

        $channel->delete();

        return redirect()->route('spaces.show', $space);
    }
}