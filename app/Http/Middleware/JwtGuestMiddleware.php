<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtGuestMiddleware
{
    public function __construct(
        protected JwtService $jwtService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->cookie('jwt_token')
            ?? $request->bearerToken()
            ?? session('jwt_token');

        if ($token) {
            $user = $this->jwtService->getUser($token);

            if ($user) {
                return redirect()->route('dashboard');
            }
        }

        return $next($request);
    }
}
