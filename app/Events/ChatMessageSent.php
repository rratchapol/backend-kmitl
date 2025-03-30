<?php


namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Pusher\PushNotifications\PushNotifications;

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

       // เพิ่มการส่ง Push Notification
    // ฟังก์ชันที่ใช้ Pusher Beams ส่งการแจ้งเตือน
    public function sendPushNotification()
    {
        $beamsClient = new PushNotifications([
            'instanceId' => 'c9cab783-ea09-43b4-8eeb-d05d43058870',
            'secretKey' => '5B1CF06684140EEF009AE097CFEF9BCF9090FCBB5BE8C4B2CBE50F0E8B8A9A30',
        ]);
    
        $senderName = $this->chat->sender ? $this->chat->sender->name : 'Unknown';
    
        try {
            $response = $beamsClient->publishToInterests(
                ['chat.' . $this->chat->receiver_id],
                [
                    'apns' => [
                        'aps' => [
                            'alert' => [
                                'title' => 'New message from ' . $senderName,
                                'body' => $this->chat->message,
                            ],
                            'sound' => 'default',
                        ],
                    ],
                    'fcm' => [
                        'notification' => [
                            'title' => 'New message from ' . $senderName,
                            'body' => $this->chat->message,
                        ],
                    ],
                ]
            );
    
            // ตรวจสอบผลลัพธ์ที่ได้รับจาก Pusher Beams
            \Log::info('Response from Pusher Beams:', (array) $response);
    
            // ตรวจสอบสถานะของการส่งการแจ้งเตือน
            if (isset($response->errors)) {
                // จัดการกรณีที่มีข้อผิดพลาด
                \Log::error('Push notification error:', (array) $response->errors);
            } else {
                \Log::info('Push notification sent successfully');
            }
    
        } catch (\Exception $e) {
            // จัดการกรณีที่เกิดข้อผิดพลาดในการติดต่อ Pusher Beams
            \Log::error('Error sending push notification: ' . $e->getMessage());
        }
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
