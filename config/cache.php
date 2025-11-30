<?php

return [
    'default' => env('CACHE_DRIVER', 'file'),

    'stores' => [
        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'file' => [
            'driver' => 'file',
            'path' => __DIR__ . '/../storage/cache',
        ],
    ],

    'redis' => [
        'default' => [
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'port' => env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD', null),
            'database' => 0,
        ],
    ],
];
