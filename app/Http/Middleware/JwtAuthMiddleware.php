<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthMiddleware
{
    public function __construct(
        protected JwtService $jwtService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check for JWT in Cookie, Bearer Header, or Session
        $token = $request->cookie('jwt_token')
            ?? $request->bearerToken()
            ?? session('jwt_token');

        if (! $token) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthenticated. JWT token required.',
                ], 401);
            }

            return redirect()->route('auth.login')
                ->withErrors(['jwt' => 'Silakan login terlebih dahulu untuk mengakses halaman ini.']);
        }

        $user = $this->jwtService->getUser($token);

        if (! $user) {
            // Token is invalid or expired
            cookie()->queue(cookie()->forget('jwt_token'));
            session()->forget('jwt_token');

            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Token JWT sudah tidak berlaku atau kadaluarsa.',
                ], 401);
            }

            return redirect()->route('auth.login')
                ->withErrors(['jwt' => 'Sesi login telah berakhir. Silakan login kembali.']);
        }

        // Authenticate user in Laravel context
        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
