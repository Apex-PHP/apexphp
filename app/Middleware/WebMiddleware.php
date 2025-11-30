<?php

namespace App\Middleware;

/**
 * Middleware para rotas web
 */
class WebMiddleware
{
    public function handle(): bool
    {
        // Pode adicionar lógica específica para rotas web
        // Por exemplo: verificar CSRF, iniciar sessão, etc.
        return true;
    }
}
