<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $data;
    private int $room_id;

    public function __construct($room_id, $data)
    {
        $this->room_id = $room_id;
        $this->data = $data;
    }

    public function broadcastOn()
    {
        return new Channel("chat.$this->room_id");
    }

    public function broadcastAs(): string
    {
        return 'chat.message';
    }
}
