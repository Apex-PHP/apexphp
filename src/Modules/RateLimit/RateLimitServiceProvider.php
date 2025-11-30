<?php

namespace Framework\Modules\RateLimit;

use Framework\Core\ServiceProvider;

/**
 * Rate Limit Service Provider
 *
 * Registra serviços de rate limiting no container
 */
class RateLimitServiceProvider extends ServiceProvider
{
    /**
     * Registrar serviços
     */
    public function register(): void
    {
        $this->config = $this->loadConfig('rate-limit.php');

        // Registrar RateLimiter
        $this->container->set(RateLimiter::class, function () {
            return new RateLimiter();
        });

        // Registrar Middleware
        $this->container->set(RateLimitMiddleware::class, function ($container) {
            return new RateLimitMiddleware(
                $container->get(RateLimiter::class)
            );
        });

        // Helper function
        if (!function_exists('rateLimiter')) {
            /**
             * Helper para acessar rate limiter
             *
             * @return RateLimiter
             */
            function rateLimiter(): RateLimiter {
                global $container;
                return $container->get(RateLimiter::class);
            }
        }
    }

    /**
     * Bootstrap do módulo
     */
    public function boot(): void
    {
        // Middleware será registrado automaticamente
        // via RouteCollector quando encontrar attribute #[RateLimit]
    }
}
