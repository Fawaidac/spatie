<?php

return [
    'secret' => env('JWT_SECRET', env('APP_KEY', 'your-256-bit-secret-key-dash-spatie')),
    'algo' => env('JWT_ALGO', 'HS256'),
    'ttl' => env('JWT_TTL', 60 * 24), // 24 hours in minutes
    'refresh_ttl' => env('JWT_REFRESH_TTL', 60 * 24 * 7), // 7 days in minutes
];
