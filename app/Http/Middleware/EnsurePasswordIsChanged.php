<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->must_change_password && !$user->hasRole('admin')) {
            return response()->json([
                'message' => 'يجب عليك تغيير كلمة المرور أولاً للوصول بالنظام'
            ], 403);
        }

        return $next($request);
    }
}
