<?php

namespace App\Http\Controllers;
use App\Mail\VerificationMail;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $validatedData = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|string|email|max:255|unique:users",
            "password" => "required|string|min:6|confirmed",
        ]);

        $verificationCode = rand(1000, 9999); // สุ่มรหัส 4 หลัก

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
            'verification_code' => $verificationCode,
            // 'email_verified_at' => null,
        ]);

        // ส่งอีเมลด้วย Mailgun
        Mail::to($user->email)->send(new VerificationMail($verificationCode));

        return response()->json([
            'message' => 'Registration successful, please check your email for verification code',
            'user_id' => $user->id
        ]);
    }

    public function login()
    {
        $credentials = request(['email', 'password']);

        // ดึงข้อมูลผู้ใช้จากอีเมลที่ผู้ใช้พยายามเข้าสู่ระบบ
        $user = User::where('email', $credentials['email'])->first();

        // ตรวจสอบว่าผู้ใช้มีการยืนยันอีเมลหรือยัง
        if (!$user || $user->email_verified_at === null) {
            return response()->json(['error' => 'You need to verify your email before logging in.'], 403); // รหัสสถานะ 403 สำหรับ forbidden
        }

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }


    public function me()
    {
        // return response()->json(auth()->user());
        $user = auth()->user();
        $customers = $user->customers; // ดึงข้อมูลลูกค้าที่เกี่ยวข้องกับผู้ใช้

        return response()->json([
            'user' => $user,
            // 'customers' => $customers
        ]);
    }


    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }


    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }


    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }

    public function verifyEmail(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'email' => 'required|email|exists:users,email',
            'verification_code' => 'required|numeric',
        ]);

        // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
        $user = User::where('id', $validatedData['user_id'])
            ->where('email', $validatedData['email'])
            ->first();

        // ตรวจสอบว่า verification_code ตรงกันหรือไม่
        if ($user && $user->verification_code == $validatedData['verification_code']) {
            // ตั้งค่า email_verified_at และลบรหัสยืนยัน
            $user->email_verified_at = now();
            $user->verification_code = null; // ลบรหัสยืนยันเมื่อยืนยันสำเร็จ
            $user->save();

            return response()->json(['message' => 'Email verified successfully']);
        } else {
            return response()->json(['message' => 'Invalid verification code'], 400);
        }
    }
}