<?php

declare(strict_types=1);

return [

    'gemini' => [
        'secret_key' => env('GEMINI_API_KEY'),
        'base_url' => env('GEMINI_API_URL'),
    ],

    'groq' => [
        'secret_key' => env('GROQ_API_KEY'),
        'base_url' => env('GROQ_API_URL'),
    ],

    'request_timeout' => env('API_REQUEST_TIMEOUT', 30),
];
