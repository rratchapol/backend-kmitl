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

    public function fetchMessages($buyer_id, $seller_id)
    {
        $chats = Chat::where('buyer_id', $buyer_id)
            ->where('seller_id', $seller_id)
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
