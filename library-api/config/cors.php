<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    // rtrim: origin tidak pernah berakhiran slash, tapi di .env gampang kesisipan.
    'allowed_origins' => array_map(fn ($origin) => rtrim($origin, '/'), array_filter(explode(',', env('CORS_ALLOWED_ORIGINS', 'http://localhost:5173,http://127.0.0.1:5173,http://localhost:8000,http://127.0.0.1:8000,http://localhost:8001,http://127.0.0.1:8001')))),

    // Vite menaikkan port (5173 -> 5174 -> ...) kalau port sebelumnya terpakai.
    // Pola ini mengizinkan semua port lokal tanpa perlu daftar satu per satu.
    'allowed_origins_patterns' => ['#^http://(localhost|127\.0\.0\.1|\[::1\])(:\d+)?$#'],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
