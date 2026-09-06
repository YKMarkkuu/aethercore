<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewMessageEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Public channel — matches the blade view's
     * window.Echo.channel('conversation.{id}') (not .private()), so no
     * channel auth route is needed for this to work.
     */
    public function broadcastOn(): Channel
    {
        return new Channel('conversation.' . $this->message->conversation_id);
    }

    /**
     * Keeps the JS-side .listen('NewMessageEvent', ...) working without
     * needing the fully-qualified "App\Events\NewMessageEvent" name.
     */
    public function broadcastAs(): string
    {
        return 'NewMessageEvent';
    }

    /**
     * Shape matches exactly what conversation-show.blade.php's
     * appendMessage() expects to receive over the socket.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'content' => $this->message->content,
            'time' => $this->message->created_at->format('g:i A'),
            'created_at' => $this->message->created_at->toIso8601String(),
            'user' => [
                'id' => $this->message->user->id,
                'name' => $this->message->user->name,
                'display_name' => $this->message->user->display_name,
                'avatar_url' => $this->message->user->getAvatarUrl(),
            ],
        ];
    }
}