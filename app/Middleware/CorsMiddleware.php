<?php

namespace App\Middleware;

/**
 * Middleware CORS
 */
class CorsMiddleware
{
    public function handle(): bool
    {
        $allowedOrigins = config('cors.allowed_origins', ['*']);
        $allowedMethods = config('cors.allowed_methods', ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']);
        $allowedHeaders = config('cors.allowed_headers', ['Content-Type', 'Authorization', 'X-Requested-With']);

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '*';

        if (in_array('*', $allowedOrigins) || in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        }

        header('Access-Control-Allow-Methods: ' . implode(', ', $allowedMethods));
        header('Access-Control-Allow-Headers: ' . implode(', ', $allowedHeaders));
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');

        // Handle preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        return true;
    }
}
