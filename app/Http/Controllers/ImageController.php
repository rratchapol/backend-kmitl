<?php

namespace App\Http\Controllers;
use App\Models\Image;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    // public function store(Request $request)
    // {
    //     // Validate รูปภาพที่อัปโหลด
    //     $request->validate([
    //         'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    //         // 'image_path' => 'required|string',
    //     ]);

        

    //     // จัดเก็บรูปภาพใน storage
    //     $imagePath = $request->file('image')->store('images', 'public');

    //     // บันทึก path ลงฐานข้อมูล
    //     $image = Image::create([
    //         'image_path' => $imagePath,
    //     ]);

    //     // ส่ง path ของรูปภาพกลับไป
    //     return response()->json(['image_path' => $image->image_path]);
    // }

    public function store(Request $request)
{
    // Validate รูปภาพที่อัปโหลด
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:4096',
        'image_path' => 'nullable|string', // เพิ่ม validation สำหรับ image_path (ไม่บังคับ)
    ]);

    // กำหนด path สำหรับเก็บรูปภาพ
    $imagePath = $request->file('image')->store(
        $request->input('image_path', 'images'), // ใช้ image_path ที่ผู้ใช้ระบุ หรือใช้ 'images' เป็นค่าเริ่มต้น
        'public'
    );

    // บันทึก path ลงฐานข้อมูล
    $image = Image::create([
        'image_path' => $imagePath,
    ]);

    // ส่ง path ของรูปภาพกลับไป
    return response()->json(['image_path' => $image->image_path]);
}
}