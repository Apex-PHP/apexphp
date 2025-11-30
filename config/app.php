<?php

return [
    'name' => env('APP_NAME', 'ApexPHP Framework'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),

    'per_page' => env('PAGINATION_PER_PAGE', 10),

    'timezone' => 'America/Sao_Paulo',
    'locale' => 'pt_BR',

    'log' => [
        'level' => env('LOG_LEVEL', 'error'),
        'path' => __DIR__ . '/../storage/logs/app.log',
    ],
];
