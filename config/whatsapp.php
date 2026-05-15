<?php

return [
    'base_url' => env('WA_SERVICE_URL', 'http://localhost:3000'),
    'timeout' => env('WA_SERVICE_TIMEOUT', 10),
    'token' => env('WA_SERVICE_TOKEN', null),

    // Jika WA service menggunakan header khusus, tambahkan di sini.
    'headers' => [
        // 'X-API-Key' => env('WA_SERVICE_API_KEY'),
    ],
    'webhook_secret' => env('WA_WEBHOOK_SECRET'),
];

