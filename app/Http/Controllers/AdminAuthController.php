<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminAuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:admins',
            'password' => 'required|string|min:5',
            'role' => 'nullable|string',
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'role' => $request->role
        ]);

        return response()->json(['message' => 'Admin registered successfully'], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        $admin = Admin::where('username', $credentials['username'])->first();

        if (!$token = auth('admin_api')->attempt($credentials)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // return response()->json(['token' => $token]);
        return response()->json([
            // $this->respondWithToken($token),
            'token' => $token,
            'admin_id' => $admin->id,
            'role' => $admin->role
        ]);
    }

    public function getAllAdmins()
    {
        $admins = Admin::all();

        return response()->json([
            'message' => 'All admins retrieved successfully',
            'data' => $admins
        ], 200);
    }

    public function look(string $admin_id)
    {
        // ดึงสินค้าทั้งหมดที่มี seller_id ตรงกับที่ระบุ
        $admin = Admin::where('id', $admin_id)->get();
    
        // ตรวจสอบว่ามีสินค้าหรือไม่
        if ($admin->isEmpty()) {
            return response()->json(['message' => 'No products found for this admin'], 404);
        }
    
        return response()->json($admin);
    }

    public function updateAdmin(Request $request, $id)
    {
        // Validation
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:admins,username,' . $id,
            'password' => 'required|string|min:5',
            'role' => 'nullable|string',
        ]);

        // ค้นหา Admin ตาม ID
        $admin = Admin::find($id);

        if (!$admin) {
            return response()->json(['error' => 'Admin not found'], 404);
        }

        // อัปเดตข้อมูล Admin
        $admin->name = $request->name;
        $admin->username = $request->username;
        $admin->password = Hash::make($request->password);
        $admin->role = $request->role;

        // บันทึกการเปลี่ยนแปลง
        $admin->save();

        return response()->json(['message' => 'Admin updated successfully'], 200);
    }

    public function profile()
    {
        return response()->json(auth('admin_api')->user());
    }


    public function destroy($id)
    {
        $Admin = Admin::find($id);

        if (!$Admin) {
            return response()->json(['message' => 'Admin not found'], 404);
        }

        $Admin->delete();

        return response()->json(['message' => 'Admin deleted successfully']);
    }
}
