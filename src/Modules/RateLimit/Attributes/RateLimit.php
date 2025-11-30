<?php

namespace Framework\Modules\RateLimit\Attributes;

use Attribute;

/**
 * Rate Limit Attribute
 *
 * Limite de taxa de requisições para rotas/controllers
 *
 * @example
 * #[RateLimit(requests: 60, perMinutes: 1)]
 * public function postCreate() { }
 *
 * #[RateLimit(requests: 10, perMinutes: 1, by: 'user')]
 * public function postLogin() { }
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class RateLimit
{
    /**
     * @param int $requests Número máximo de requisições
     * @param int $perMinutes Período em minutos
     * @param string $by Identificador: 'ip', 'user', ou callable
     * @param string $message Mensagem de erro customizada
     */
    public function __construct(
        public int $requests = 60,
        public int $perMinutes = 1,
        public string $by = 'ip',
        public string $message = 'Too many requests. Please try again later.'
    ) {
    }

    /**
     * Obter chave única para rate limit
     */
    public function getKey($request, $user = null): string
    {
        return match($this->by) {
            'ip' => $this->getIp($request),
            'user' => $user ? 'user:' . $user->id : $this->getIp($request),
            default => $this->by
        };
    }

    /**
     * Obter IP do request
     */
    private function getIp($request): string
    {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? '0.0.0.0';

        // Check for proxy
        $headers = $request->getHeaders();
        if (isset($headers['X-Forwarded-For'])) {
            $ip = explode(',', $headers['X-Forwarded-For'][0])[0];
        } elseif (isset($headers['X-Real-IP'])) {
            $ip = $headers['X-Real-IP'][0];
        }

        return 'ip:' . trim($ip);
    }
}
