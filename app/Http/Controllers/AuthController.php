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

     /**
     * @OA\Post(
     *     path="/api/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "password_confirmation"},
     *             @OA\Property(property="name", type="string", format="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="secret")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Registration successful"),
     *     @OA\Response(response=400, description="Invalid input")
     * )
     */
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            "name" => "required|string|max:255",
            "email" => "required|string|email|max:255|unique:users",
            "password" => "required|string|min:6|confirmed",
        ]);

        $verificationCode = rand(0001, 9999); // สุ่มรหัส 4 หลัก

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


    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login a user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Login successful"),
     *     @OA\Response(response=403, description="Email not verified"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
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

 

        // return $this->respondWithToken($token);

            // ดึง user_id และรวมกับ token ใน response
        return response()->json([
            // $this->respondWithToken($token),
            'token' => $token,
            'user_id' => $user->id,
            'expires_in' => auth()->factory()->getTTL() * 60

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


     /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout a user",
     *     tags={"Authentication"},
     *     @OA\Response(response=200, description="Successfully logged out"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }


     /**
     * @OA\Post(
     *     path="/api/refresh",
     *     summary="Refresh access token",
     *     tags={"Authentication"},
     *     @OA\Response(response=200, description="Access token refreshed successfully"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }


    /**
     * @OA\Post(
     *     path="/api/verify-email",
     *     summary="Verify user email",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"user_id", "email", "verification_code"},
     *             @OA\Property(property="user_id", type="integer", example=1),
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="verification_code", type="integer", example=1234)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Email verified successfully"),
     *     @OA\Response(response=400, description="Invalid verification code")
     * )
     */
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