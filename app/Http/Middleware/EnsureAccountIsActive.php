<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && !$user->is_active) {
            if ($request->expectsJson()) {
                // Revoke all tokens if API request
                $user->tokens()->delete();
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda telah dinonaktifkan. Hubungi administrator.',
                    'data' => null,
                ], 403);
            }
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Akun Anda telah dinonaktifkan.']);
        }

        return $next($request);
    }
}
