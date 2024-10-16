<?php

namespace App\Http\Controllers;
use App\Models\Image;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function store(Request $request)
    {
        // Validate รูปภาพที่อัปโหลด
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // จัดเก็บรูปภาพใน storage
        $imagePath = $request->file('image')->store('images', 'public');

        // บันทึก path ลงฐานข้อมูล
        $image = Image::create([
            'image_path' => $imagePath,
        ]);

        // ส่ง path ของรูปภาพกลับไป
        return response()->json(['image_path' => $image->image_path]);
    }
}