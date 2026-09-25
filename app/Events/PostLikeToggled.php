<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PostLikeToggled implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $postId,
        public int $likeCount,
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('post.' . $this->postId);
    }

    public function broadcastAs(): string
    {
        return 'PostLikeToggled';
    }

    public function broadcastWith(): array
    {
        return [
            'post_id' => $this->postId,
            'like_count' => $this->likeCount,
        ];
    }
}