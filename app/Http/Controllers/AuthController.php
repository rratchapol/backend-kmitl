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


    // public function register(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         "name" => "required|string|max:255",
    //         "email" => "required|string|email|max:255|unique:users",
    //         "password" => "required|string|min:6|confirmed",
    //     ]);

    //     // $verificationCode = rand(0001, 9999); // สุ่มรหัส 4 หลัก
    //     $verificationCode = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);


    //     $user = User::create([
    //         'name' => $validatedData['name'],
    //         'email' => $validatedData['email'],
    //         'password' => bcrypt($validatedData['password']),
    //         'verification_code' => $verificationCode,
    //         // 'email_verified_at' => null,
    //     ]);

    //     // ส่งอีเมลด้วย Mailgun
    //     Mail::to($user->email)->send(new VerificationMail($verificationCode));

    //     return response()->json([
    //         'message' => 'Registration successful, please check your email for verification code',
    //         'user_id' => $user->id
    //     ]);
    // }



    public function login()
    {
        $credentials = request(['email', 'password']);

        // ดึงข้อมูลผู้ใช้จากอีเมลที่ผู้ใช้พยายามเข้าสู่ระบบ
        $user = User::with('customers')->where('email', $credentials['email'])->first();

        // ตรวจสอบว่าผู้ใช้มีการยืนยันอีเมลหรือยัง
        if (!$user || $user->email_verified_at === null) {
            return response()->json(['error' => 'You need to verify your email before logging in.'], 403); // รหัสสถานะ 403 สำหรับ forbidden
        }

        if (!$token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $customerData = $user->customers;
        // ดึงข้อมูล user ตาม ID ของผู้ที่ล็อกอิน
        $dataUser = User::where('id', $user->id)->first();


        // return $this->respondWithToken($token);

            // ดึง user_id และรวมกับ token ใน response
        return response()->json([
            // $this->respondWithToken($token),
            'token' => $token,
            'user_id' => $user->id,
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => $dataUser,
            'user_data' => $customerData // ข้อมูลผู้ใช้ที่ได้จากฐานข้อมูล

        ]);
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

    // public function verifyEmail(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'user_id' => 'required|exists:users,id',
    //         'email' => 'required|email|exists:users,email',
    //         'verification_code' => 'required|numeric',
    //     ]);

    //     // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
    //     $user = User::where('id', $validatedData['user_id'])
    //         ->where('email', $validatedData['email'])
    //         ->first();

    //     // ตรวจสอบว่า verification_code ตรงกันหรือไม่
    //     if ($user && $user->verification_code == $validatedData['verification_code']) {
    //         // ตั้งค่า email_verified_at และลบรหัสยืนยัน
    //         $user->email_verified_at = now();
    //         $user->verification_code = null; // ลบรหัสยืนยันเมื่อยืนยันสำเร็จ
    //         $user->save();

    //         return response()->json(['message' => 'Email verified successfully']);
    //     } else {
    //         return response()->json(['message' => 'Invalid verification code'], 400);
    //     }
    // }


    public function register(Request $request)
{
    $validatedData = $request->validate([
        "name" => "required|string|max:255",
        "email" => "required|string|email|max:255|unique:users",
        "password" => "required|string|min:6|confirmed",
    ]);

    // สร้างรหัส OTP
    $verificationCode = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // สร้างผู้ใช้ใหม่
    $user = User::create([
        'name' => $validatedData['name'],
        'email' => $validatedData['email'],
        'password' => bcrypt($validatedData['password']),
        'verification_code' => $verificationCode,
        // ไม่ต้องเพิ่ม expired_at ในฐานข้อมูล
    ]);

    // ส่งอีเมลด้วย Mailgun
    Mail::to($user->email)->send(new VerificationMail($verificationCode));

    return response()->json([
        'message' => 'Registration successful, please check your email for verification code',
        'user_id' => $user->id
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

    // ตรวจสอบว่า user มีข้อมูลและยังไม่ได้ยืนยันอีเมลภายใน 5 นาที
    if ($user && $user->verification_code == $validatedData['verification_code']) {
        $registrationTime = $user->created_at; // เวลาที่สมัคร

        // ตรวจสอบว่า OTP หมดอายุหรือไม่ (5 นาที)
        if ($registrationTime->diffInMinutes(now()) > 5) {
            // OTP หมดอายุแล้ว
            // ลบผู้ใช้ที่ยังไม่ได้ยืนยันอีเมล
            $user->delete();
            return response()->json(['message' => 'Verification code expired. User deleted, please register again.'], 400);
        }

        // ตั้งค่า email_verified_at และลบรหัสยืนยัน
        $user->email_verified_at = now();
        $user->verification_code = null; // ลบรหัสยืนยันเมื่อยืนยันสำเร็จ
        $user->save();

        return response()->json(['message' => 'Email verified successfully']);
    } else {
        return response()->json(['message' => 'Invalid verification code'], 400);
    }
}

// ฟังก์ชันที่ใช้สำหรับร้องขอลืมรหัสผ่าน
public function forgotPassword(Request $request)
{
    // ตรวจสอบอีเมลที่ผู้ใช้กรอก
    $validatedData = $request->validate([
        'email' => 'required|email|exists:users,email',
    ]);

    // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
    $user = User::where('email', $validatedData['email'])->first();

    // สร้างรหัส OTP สำหรับการรีเซ็ตรหัสผ่าน
    $resetCode = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

    // บันทึกโค้ดยืนยันลงในฐานข้อมูล
    $user->reset_code = $resetCode;
    $user->save();

    // ส่งอีเมลไปยังผู้ใช้
    Mail::to($user->email)->send(new VerificationMail($resetCode));

    return response()->json([
        'message' => 'Please check your email for the reset code.'
    ]);
}

// ฟังก์ชันที่ใช้สำหรับยืนยันโค้ดและรีเซ็ตรหัสผ่าน
public function resetPassword(Request $request)
{
    $validatedData = $request->validate([
        'email' => 'required|email|exists:users,email',
        'reset_code' => 'required|numeric',
        'password' => 'required|string|min:6|confirmed',
    ]);

    // ดึงข้อมูลผู้ใช้จากฐานข้อมูล
    $user = User::where('email', $validatedData['email'])->first();

    // ตรวจสอบว่าโค้ดรีเซ็ตรหัสผ่านถูกต้องหรือไม่
    if ($user && $user->reset_code == $validatedData['reset_code']) {
        // รีเซ็ตรหัสผ่านใหม่
        $user->password = bcrypt($validatedData['password']);
        $user->reset_code = null; // ลบโค้ดรีเซ็ตหลังจากใช้งาน
        $user->save();

        return response()->json([
            'message' => 'Password has been reset successfully.'
        ]);
    } else {
        return response()->json([
            'message' => 'Invalid reset code or email.'
        ], 400);
    }
}


}