<?php


namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ChatMessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $chat;

    public function __construct($chat)
    {
        $this->chat = $chat;
    }

    // public function broadcastOn()
    // {
    //     return new Channel('chat');
    // }

    // public function broadcastOn()
    // {
    //     return new PrivateChannel('chat.' . min($this->chat->sender_id, $this->chat->receiver_id) . '.' . max($this->chat->sender_id, $this->chat->receiver_id));
    // }

    public function broadcastOn()
    {
        // เปลี่ยนจาก PrivateChannel เป็น Channel
        return new Channel('chat.' . min($this->chat->sender_id, $this->chat->receiver_id) . '.' . max($this->chat->sender_id, $this->chat->receiver_id));
    }
    

    public function broadcastWith()
    {
        return [
            'sender_id' => $this->chat->sender_id,
            'receiver_id' => $this->chat->receiver_id,
            'message' => $this->chat->message,
            'image' => $this->chat->image,
            'statusread' => $this->chat->statusread,
            'created_at' => $this->chat->created_at
        ];
    }

    public function broadcastAs()
    {
        return 'ChatMessageSent';
    }
}













// namespace App\Events;

// use Illuminate\Broadcasting\Channel;
// use Illuminate\Broadcasting\InteractsWithSockets;
// use Illuminate\Broadcasting\PresenceChannel;
// use Illuminate\Broadcasting\PrivateChannel;
// use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
// use Illuminate\Foundation\Events\Dispatchable;
// use Illuminate\Queue\SerializesModels;

// class ChatMessageSent
// {
//     use Dispatchable, InteractsWithSockets, SerializesModels;

//     /**
//      * Create a new event instance.
//      */
//     public function __construct()
//     {
//         //
//     }

//     /**
//      * Get the channels the event should broadcast on.
//      *
//      * @return array<int, \Illuminate\Broadcasting\Channel>
//      */
//     public function broadcastOn(): array
//     {
//         return [
//             new PrivateChannel('channel-name'),
//         ];
//     }
// }
