<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AuthServices\AuthService;

class AuthController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function login(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        $token = $this->authService->loginUser($validate);

        if (!$token) {
            return response()->json([
                'message' => 'بيانات الاعتماد غير صحيحة'
            ], 401);
        }

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 200);
    }

    public function logout()
    {
        $this->authService->logoutUser();
        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح'
        ], 200);
    }

    public function changePassword(Request $request)
    {
        $validate = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed'
        ]);

        $this->authService->changePassword($validate);

        return response()->json([
            'message' => 'تم تغيير كلمة المرور بنجاح'
        ], 200);
    }
}
