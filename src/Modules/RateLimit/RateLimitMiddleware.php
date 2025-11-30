<?php

namespace Framework\Modules\RateLimit;

use Framework\Modules\RateLimit\Attributes\RateLimit as RateLimitAttribute;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use ReflectionClass;
use ReflectionMethod;
use Slim\Psr7\Response;

/**
 * Rate Limit Middleware
 *
 * Processa attributes #[RateLimit] e aplica rate limiting
 */
class RateLimitMiddleware implements MiddlewareInterface
{
    private RateLimiter $limiter;

    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Process middleware
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        // Obter rota atual
        $route = $request->getAttribute('__route__');

        if (!$route) {
            return $handler->handle($request);
        }

        $callable = $route->getCallable();

        if (!is_array($callable) || count($callable) !== 2) {
            return $handler->handle($request);
        }

        [$controller, $method] = $callable;

        // Verificar attribute no método
        $rateLimitAttr = $this->getRateLimitAttribute($controller, $method);

        if (!$rateLimitAttr) {
            return $handler->handle($request);
        }

        // Obter usuário se autenticado
        $user = $request->getAttribute('user');

        // Gerar chave
        $key = $rateLimitAttr->getKey($request, $user);

        // Verificar rate limit
        if ($this->limiter->tooManyAttempts($key, $rateLimitAttr->requests)) {
            return $this->buildRateLimitResponse(
                $rateLimitAttr,
                $key
            );
        }

        // Consumir tentativa
        $this->limiter->attempt($key, $rateLimitAttr->requests, $rateLimitAttr->perMinutes);

        // Adicionar headers de rate limit
        $response = $handler->handle($request);

        return $this->addRateLimitHeaders(
            $response,
            $rateLimitAttr,
            $key
        );
    }

    /**
     * Obter attribute RateLimit do método
     */
    private function getRateLimitAttribute(string $controller, string $method): ?RateLimitAttribute
    {
        try {
            $reflection = new ReflectionMethod($controller, $method);
            $attributes = $reflection->getAttributes(RateLimitAttribute::class);

            if (!empty($attributes)) {
                return $attributes[0]->newInstance();
            }

            // Verificar na classe
            $classReflection = new ReflectionClass($controller);
            $classAttributes = $classReflection->getAttributes(RateLimitAttribute::class);

            if (!empty($classAttributes)) {
                return $classAttributes[0]->newInstance();
            }
        } catch (\Exception $e) {
            // Log error silently
        }

        return null;
    }

    /**
     * Construir resposta de rate limit excedido
     */
    private function buildRateLimitResponse(
        RateLimitAttribute $attr,
        string $key
    ): ResponseInterface {
        $response = new Response();
        $retryAfter = $this->limiter->availableIn($key);

        $body = json_encode([
            'error' => 'Too Many Requests',
            'message' => $attr->message,
            'retry_after' => $retryAfter
        ]);

        $response->getBody()->write($body);

        return $response
            ->withStatus(429)
            ->withHeader('Content-Type', 'application/json')
            ->withHeader('Retry-After', (string) $retryAfter)
            ->withHeader('X-RateLimit-Limit', (string) $attr->requests)
            ->withHeader('X-RateLimit-Remaining', '0')
            ->withHeader('X-RateLimit-Reset', (string) (time() + $retryAfter));
    }

    /**
     * Adicionar headers de rate limit à resposta
     */
    private function addRateLimitHeaders(
        ResponseInterface $response,
        RateLimitAttribute $attr,
        string $key
    ): ResponseInterface {
        $remaining = $this->limiter->remaining($key, $attr->requests);
        $retryAfter = $this->limiter->availableIn($key);

        return $response
            ->withHeader('X-RateLimit-Limit', (string) $attr->requests)
            ->withHeader('X-RateLimit-Remaining', (string) $remaining)
            ->withHeader('X-RateLimit-Reset', (string) (time() + $retryAfter));
    }
}
