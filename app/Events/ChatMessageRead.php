<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageRead implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public $sender_id;
    public $receiver_id;
    // public $chat;

    public function __construct( $sender_id, $receiver_id)
    {
        $this->sender_id = $sender_id;
        $this->receiver_id = $receiver_id;
    }

    public function broadcastOn()
    {
        return new Channel('chat.' . min($this->sender_id, $this->receiver_id) . '.' . max($this->sender_id, $this->receiver_id));
    }

    public function broadcastWith()
    {
        return [
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            // 'message' => $this->chat->message,
            // 'image' => $this->chat->image,
            // 'created_at' => $this->chat->created_at,
            'statusread' => 1
        ];
    }

    public function broadcastAs()
    {
        return 'ChatMessageRead';
    }
}
