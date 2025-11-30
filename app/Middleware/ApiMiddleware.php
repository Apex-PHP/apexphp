<?php

namespace App\Middleware;

/**
 * Middleware para rotas de API
 */
class ApiMiddleware
{
    public function handle(): bool
    {
        // Configurar headers para API
        header('Content-Type: application/json');
        header('X-Content-Type-Options: nosniff');

        return true;
    }
}
