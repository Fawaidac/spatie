<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Throwable;

class JwtService
{
    protected string $secret;
    protected string $algo;
    protected int $ttl;
    protected int $refreshTtl;

    public function __construct()
    {
        $this->secret = (string) config('jwt.secret');
        $this->algo = (string) config('jwt.algo', 'HS256');
        $this->ttl = (int) config('jwt.ttl', 1440); // 24 hours in minutes
        $this->refreshTtl = (int) config('jwt.refresh_ttl', 10080); // 7 days in minutes
    }

    /**
     * Generate a signed JWT token for a given user.
     */
    public function generateToken(User $user, bool $remember = false): string
    {
        $now = time();
        $ttlSeconds = ($remember ? $this->refreshTtl : $this->ttl) * 60;
        $exp = $now + $ttlSeconds;

        $payload = [
            'iss'   => config('app.url', 'http://localhost'),
            'sub'   => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'iat'   => $now,
            'exp'   => $exp,
        ];

        return JWT::encode($payload, $this->secret, $this->algo);
    }

    /**
     * Decode and validate a JWT token.
     */
    public function decodeToken(string $token): ?object
    {
        try {
            return JWT::decode($token, new Key($this->secret, $this->algo));
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Extract User model from a valid JWT token.
     */
    public function getUser(string $token): ?User
    {
        $payload = $this->decodeToken($token);

        if (! $payload || ! isset($payload->sub)) {
            return null;
        }

        return User::find($payload->sub);
    }
}
