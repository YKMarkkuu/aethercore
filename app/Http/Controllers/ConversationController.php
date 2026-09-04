<?php

namespace App\Http\Controllers;

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

        return redirect()->back();
    }

    public function startWithUser($userId)
    {
        $conversation = Auth::user()->getConversationWith($userId);
        return redirect()->route('conversations.show', $conversation);
    }
}