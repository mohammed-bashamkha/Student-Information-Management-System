<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if(!Auth::attempt($validate))
        {
            return response()->json([
                'message' => 'بيانات الاعتماد غير صحيحة'
            ],401);
        }

        $token = Auth::user()->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم تسجيل الدخول بنجاح',
            'access_token' => $token,
            'token_type' => 'Bearer'
        ],200);
    }

    public function logout()
    {
        Auth::user()->currentAccessToken()->delete;
        return response()->json([
            'message' => 'تم تسجيل الخروج بنجاح'
        ],200);
    }
}
