<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Events\ChatMessageRead;

use App\Models\Chat;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Log;
class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'sender_id' => 'required|integer',
            'receiver_id' => 'required|integer',
            'message' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = $request->file('image') ? $request->file('image')->store('chat-images') : null;

        $chat = Chat::create([
            'sender_id' => $validated['sender_id'],
            'receiver_id' => $validated['receiver_id'],
            'message' => $validated['message'] ?? null,
            'image' => $imagePath,
        ]);

        broadcast(new ChatMessageSent($chat->toArray()))->toOthers();

        return response()->json(['success' => true, 'chat' => $chat]);
    }


    public function getUsersInConversations($userId)
    {
        // ดึงข้อมูลแชททั้งหมดที่เกี่ยวข้องกับ userId
        $chats = Chat::where('sender_id', $userId)
            ->with(['receiver'])
            ->orderBy('created_at', 'desc')  // เรียงแชทจากใหม่ไปเก่า
            ->get();
    
        // สร้าง collection ของ user_id โดยจะเลือกแค่ buyer หรือ seller ที่ไม่ซ้ำกัน
        $user_ids = $chats->map(function ($chat) use ($userId) {
            return $chat->sender_id == $userId ? $chat->receiver_id : $chat->sender_id;
        });
    
        // ลบค่าซ้ำและใช้ values() เพื่อให้ข้อมูลเป็น array ธรรมดา
        $user_ids = $user_ids->unique()->values();
    
        // ดึงข้อมูลแชทที่เกี่ยวข้องกับ user_ids (ไม่ซ้ำ) พร้อมกับเลือกแค่แชทล่าสุดจากแต่ละ user
        $chats = Chat::whereIn('sender_id', $user_ids)
            ->with(['receiver'])
            ->orWhereIn('receiver_id', $user_ids)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('receiver_id')  // กลุ่มแชทตาม receiver_id
            ->map(function ($group) {
                return $group->first();  // เลือกแค่แชทแรกในแต่ละกลุ่ม (แชทล่าสุด)
            })
            ->values();  // เปลี่ยนค่าผลลัพธ์ให้เป็น array
    
        return response()->json($chats);
    }


    public function fetchMessages($sender_id, $receiver_id)
{
    $chats = Chat::where(function ($query) use ($sender_id, $receiver_id) {
            $query->where('sender_id', $sender_id)
                  ->where('receiver_id', $receiver_id);
        })
        ->orWhere(function ($query) use ($sender_id, $receiver_id) {
            $query->where('sender_id', $receiver_id)
                  ->where('receiver_id', $sender_id);
        })
        ->orderBy('created_at', 'asc')  // เรียงตามวันที่
        ->get();

    // // อัปเดต statusread เป็น 1 สำหรับข้อความที่ receiver ยังไม่ได้อ่าน
    // Chat::where('sender_id', $receiver_id)
    //     ->where('receiver_id', $sender_id)
    //     ->where('statusread', 0)
    //     ->update(['statusread' => 1]);

    // อัปเดต statusread เป็น 1 สำหรับข้อความที่ receiver ยังไม่ได้อ่าน
    $updated = Chat::where('sender_id', $receiver_id)
        ->where('receiver_id', $sender_id)
        ->where('statusread', 0)
        ->update(['statusread' => 1]);

        // ถ้ามีข้อความที่ถูกอ่านแล้วให้ broadcast event
    if ($updated > 0) {
        broadcast(new ChatMessageRead( $receiver_id, $sender_id));
    }

    return response()->json($chats);
}

//   ---------------------------------  ทดลอง
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sender_id' => 'required|integer',
            'receiver_id' => 'required|integer',
            'message' => 'nullable|string',
            'image' => 'nullable|string',
        ]);

        $validated['statusread'] = 0;
    
        $chat = Chat::create($validated);
    
        // Broadcast Event
        broadcast(new ChatMessageSent($chat));
    
        return response()->json($chat, 201);
    }


    public function getUsersInConversation($userId)
    {
        // ดึงข้อมูลแชททั้งหมดที่เกี่ยวข้องกับ userId
        $chats = Chat::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderBy('created_at', 'desc') // เรียงแชทจากใหม่ไปเก่า
            ->get();

        // สร้าง collection ของ user_id โดยจะเลือกแค่คู่สนทนาที่ไม่ซ้ำกัน
        $user_ids = $chats->map(function ($chat) use ($userId) {
            return $chat->sender_id == $userId ? $chat->receiver_id : $chat->sender_id;
        })->unique()->values(); // เอาค่าที่ไม่ซ้ำกันออก

        // ดึงข้อมูลแชทล่าสุดของแต่ละคู่สนทนา พร้อมนับข้อความที่ยังไม่ได้อ่าน
        $conversations = $user_ids->map(function ($chatUserId) use ($userId) {
            // ดึงแชทล่าสุดจากคู่สนทนา
            $latestChat = Chat::where(function ($query) use ($chatUserId, $userId) {
                    $query->where('sender_id', $chatUserId)
                        ->where('receiver_id', $userId);
                })
                ->orWhere(function ($query) use ($chatUserId, $userId) {
                    $query->where('sender_id', $userId)
                        ->where('receiver_id', $chatUserId);
                })
                ->orderBy('created_at', 'desc')
                ->first();

            // นับจำนวนข้อความที่ยังไม่ได้อ่าน (statusread = 0)
            $unreadCount = Chat::where('sender_id', $chatUserId)
                ->where('receiver_id', $userId)
                ->where('statusread', 0)
                ->count();

            $user = Customer::find($chatUserId);

            return [
                'user_id' => $chatUserId,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'mobile' => $user->mobile,
                    'address' => $user->address,
                    'faculty' => $user->faculty,
                    'department' => $user->department,
                    'classyear' => $user->classyear,
                    'role' => $user->role,
                    'pic' => $user->pic,
                    'status' => $user->status,
                ],
                // 'receiver_id' => $userId, // เพิ่ม receiver_id
                'latest_message' => $latestChat ? $latestChat->message : null,
                'latest_message_time' => $latestChat ? $latestChat->created_at : null,
                'unread_count' => $unreadCount,
            ];
        });

        return response()->json($conversations);
}


}
