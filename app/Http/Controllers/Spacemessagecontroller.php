<?php

namespace App\Http\Controllers;

use App\Models\SpaceChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpaceMessageController extends Controller
{
    public function store(Request $request, SpaceChannel $channel)
    {
        if (!$channel->space->isMember(Auth::id())) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message = $channel->messages()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        $message->load('user');

        if ($request->wantsJson()) {
            return response()->json(['message' => $this->serialize($message)]);
        }

        return redirect()->back();
    }

    /**
     * Polling endpoint, same "what's new since this id" pattern as DM
     * chat's latestMessages() — deliberately simpler, since v1 Space
     * chat has no edit/delete/reactions to also catch via a "since"
     * timestamp the way DM messages do.
     */
    public function latestMessages(Request $request, SpaceChannel $channel)
    {
        if (!$channel->space->isMember(Auth::id())) {
            abort(403);
        }

        $afterId = (int) $request->query('after', 0);

        $messages = $channel->messages()
            ->with('user')
            ->where('id', '>', $afterId)
            ->orderBy('id')
            ->get();

        return response()->json([
            'messages' => $messages->map(fn ($m) => $this->serialize($m)),
        ]);
    }

    protected function serialize($message): array
    {
        return [
            'id' => $message->id,
            'content' => $message->content,
            'time' => $message->created_at->format('g:i A'),
            'created_at' => $message->created_at->toIso8601String(),
            'user' => [
                'id' => $message->user->id,
                'name' => $message->user->name,
                'display_name' => $message->user->display_name,
                'avatar_url' => $message->user->getAvatarUrl(),
            ],
        ];
    }
}