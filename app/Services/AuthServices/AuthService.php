<?php

namespace App\Services\AuthServices;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function loginUser($validate)
    {
        if (!Auth::attempt($validate)) {
            return false;
        }

        $token = Auth::user()->createToken('auth_token')->plainTextToken;
        return $token;
    }

    public function logoutUser()
    {
        Auth::user()->currentAccessToken()->delete();
    }

    public function changePassword($data)
    {
        $user = Auth::user();

        if (!Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['كلمة المرور الحالية غير صحيحة']
            ]);
        }

        $user->password = Hash::make($data['new_password']);
        $user->must_change_password = false;
        $user->save();
    }
}
