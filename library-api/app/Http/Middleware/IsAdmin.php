<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== 'admin' || !$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Rute ini hanya dapat diakses oleh Administrator/Pustakawan yang aktif.'
            ], 403);
        }

        return $next($request);
    }
}
