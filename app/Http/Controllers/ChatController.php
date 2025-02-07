<?php

namespace App\Http\Controllers;

use App\Events\ChatMessageSent;
use App\Models\Chat;
use Illuminate\Http\Request;
use App\Http\Controllers\Log;
class ChatController extends Controller
{
    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'buyer_id' => 'required|integer',
            'seller_id' => 'required|integer',
            'message' => 'nullable|string',
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imagePath = $request->file('image') ? $request->file('image')->store('chat-images') : null;

        $chat = Chat::create([
            'buyer_id' => $validated['buyer_id'],
            'seller_id' => $validated['seller_id'],
            'message' => $validated['message'] ?? null,
            'image' => $imagePath,
        ]);

        broadcast(new ChatMessageSent($chat->toArray()))->toOthers();

        return response()->json(['success' => true, 'chat' => $chat]);
    }


    // public function getUsersInConversation($user_id)
    // {
    //     $chats = Chat::where('buyer_id', $user_id)
    //         ->orWhere('seller_id', $user_id)
    //         ->get();
    
    //     // หาผู้ใช้ที่สนทนาด้วย
    //     $user_ids = $chats->map(function($chat) use ($user_id) {
    //         return $chat->buyer_id == $user_id ? $chat->seller_id : $chat->buyer_id;
    //     });
    
    //     // ลบค่าซ้ำและใช้ values() เพื่อให้ข้อมูลเป็น array ธรรมดา
    //     $user_ids = $user_ids->unique()->values();
    
    //     // return response()->json($user_ids);
    //     return response()->json(['success' => true, 'chat' => $user_ids]);
    // }

    // public function getUsersInConversation($userId)
    // {
    //     $chats = Chat::where('buyer_id', $userId)->with(['receiver'])->get();

        
    //     return response()->json($chats);
    // }

    public function getUsersInConversation($userId)
    {
        // ดึงข้อมูลแชททั้งหมดที่เกี่ยวข้องกับ userId
        $chats = Chat::where('buyer_id', $userId)
            ->with(['receiver'])
            ->orderBy('created_at', 'desc')  // เรียงแชทจากใหม่ไปเก่า
            ->get();
    
        // สร้าง collection ของ user_id โดยจะเลือกแค่ buyer หรือ seller ที่ไม่ซ้ำกัน
        $user_ids = $chats->map(function ($chat) use ($userId) {
            return $chat->buyer_id == $userId ? $chat->seller_id : $chat->buyer_id;
        });
    
        // ลบค่าซ้ำและใช้ values() เพื่อให้ข้อมูลเป็น array ธรรมดา
        $user_ids = $user_ids->unique()->values();
    
        // ดึงข้อมูลแชทที่เกี่ยวข้องกับ user_ids (ไม่ซ้ำ) พร้อมกับเลือกแค่แชทล่าสุดจากแต่ละ user
        $chats = Chat::whereIn('buyer_id', $user_ids)
            ->with(['receiver'])
            ->orWhereIn('seller_id', $user_ids)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('seller_id')  // กลุ่มแชทตาม seller_id
            ->map(function ($group) {
                return $group->first();  // เลือกแค่แชทแรกในแต่ละกลุ่ม (แชทล่าสุด)
            })
            ->values();  // เปลี่ยนค่าผลลัพธ์ให้เป็น array
    
        return response()->json($chats);
    }



    // public function fetchMessages($buyer_id, $seller_id)
    // {
    //     $chats = Chat::where('buyer_id', $buyer_id)
    //         ->where('seller_id', $seller_id)
    //         ->get();

    //     return response()->json($chats);
    // }

    public function fetchMessages($buyer_id, $seller_id)
{
    $chats = Chat::where(function ($query) use ($buyer_id, $seller_id) {
            $query->where('buyer_id', $buyer_id)
                  ->where('seller_id', $seller_id);
        })
        ->orWhere(function ($query) use ($buyer_id, $seller_id) {
            $query->where('buyer_id', $seller_id)
                  ->where('seller_id', $buyer_id);
        })
        ->orderBy('created_at', 'asc')  // เรียงตามวันที่
        ->get();

    return response()->json($chats);
}

//   ---------------------------------  ทดลอง
    public function store(Request $request)
    {
        $validated = $request->validate([
            'buyer_id' => 'required|integer',
            'seller_id' => 'required|integer',
            'message' => 'nullable|string',
            'image' => 'nullable|string',
        ]);
    
        $chat = Chat::create($validated);
    
        // Broadcast Event
        broadcast(new ChatMessageSent($chat));
    
        return response()->json($chat, 201);
    }
}
