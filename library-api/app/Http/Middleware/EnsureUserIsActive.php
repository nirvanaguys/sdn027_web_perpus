<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->is_active) {
            // Revoke all tokens for the inactive user
            $user->tokens()->delete();

            return response()->json([
                'success' => false,
                'message' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi pustakawan SDN 027 untuk mengaktifkan kembali.'
            ], 403);
        }

        return $next($request);
    }
}
