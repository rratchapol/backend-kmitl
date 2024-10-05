<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Like;

class LikeController extends Controller
{
    public function index()
    {
        $likes = Like::with(['user', 'product'])->get();
        return response()->json($likes);
    }

    public function show($id)
    {
        $like = Like::with(['user', 'product'])->findOrFail($id);
        return response()->json($like);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'userlike_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
        ]);

        $like = Like::create($validatedData);
        return response()->json($like, 201);
    }

    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'userlike_id' => 'required|exists:users,id',
            'product_id' => 'required|exists:products,id',
        ]);

        $like = Like::findOrFail($id);
        $like->update($validatedData);

        return response()->json($like);
    }

    public function destroy($id)
    {
        $like = Like::findOrFail($id);
        $like->delete();

        return response()->json(null, 204);
    }

    // ดู likes ทั้งหมดที่ user สร้าง
    public function getLikesByUser($userId)
    {
        $likes = Like::where('userlike_id', $userId)->with(['user', 'product'])->get();
        return response()->json($likes);
    }
}
