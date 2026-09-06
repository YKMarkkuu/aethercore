<?php

namespace App\Http\Controllers;

use App\Events\NewMessageEvent;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConversationController extends Controller
{
    public function index()
    {
        $conversations = Auth::user()->conversations()
            ->with(['participants.user', 'lastMessage'])
            ->orderBy('last_message_at', 'desc')
            ->get();

        return view('conversations.index', compact('conversations'));
    }

    public function show(Conversation $conversation)
    {
        // Check if user is a participant
        if (!$conversation->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $messages = $conversation->messages()->with('user')->get();

        // Mark messages as read
        Message::markConversationAsRead($conversation->id, Auth::id());

        // Get the other participant for the header
        $otherUser = $conversation->getOtherParticipant(Auth::id());

        return view('conversations.show', compact('conversation', 'messages', 'otherUser'));
    }

    public function store(Request $request, Conversation $conversation)
    {
        if (!$conversation->users()->where('user_id', Auth::id())->exists()) {
            abort(403);
        }

        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
        ]);

        $conversation->update(['last_message_at' => now()]);

        $message->load('user');

        // Broadcast to whoever else is looking at this conversation right
        // now. Not using ->toOthers() here since that relies on Echo's
        // X-Socket-ID header, which plain fetch() (used by the chat view)
        // doesn't send — the sender's own browser instead just skips this
        // event client-side by comparing user IDs.
        //
        // Wrapped in try/catch: the message is already safely saved above
        // by this point, so a broadcasting failure (Reverb not running,
        // queue/config not fully set up yet, etc.) should never turn into
        // a failed response for the person who just sent the message —
        // it would otherwise look like "sending failed" even though it
        // actually went through, which is confusing to debug.
        try {
            broadcast(new NewMessageEvent($message));
        } catch (\Throwable $e) {
            \Log::warning('Broadcast failed for message ' . $message->id . ': ' . $e->getMessage());
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => [
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
                ],
            ]);
        }

        return redirect()->back();
    }

    public function startWithUser($userId)
    {
        $conversation = Auth::user()->getConversationWith($userId);
        return redirect()->route('conversations.show', $conversation);
    }
}