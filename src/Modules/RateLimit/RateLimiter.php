<?php

namespace Framework\Modules\RateLimit;

use Predis\Client as RedisClient;

/**
 * Rate Limiter
 *
 * Implementa rate limiting baseado em token bucket
 */
class RateLimiter
{
    private RedisClient $redis;
    private string $prefix;

    /**
     * Constructor
     */
    public function __construct(?RedisClient $redis = null)
    {
        $this->redis = $redis ?? new RedisClient([
            'scheme' => env('REDIS_SCHEME', 'tcp'),
            'host'   => env('REDIS_HOST', '127.0.0.1'),
            'port'   => env('REDIS_PORT', 6379),
        ]);

        $this->prefix = env('RATE_LIMIT_PREFIX', 'rate_limit:');
    }

    /**
     * Tentar consumir um token
     *
     * @param string $key Identificador único (IP, user_id, etc)
     * @param int $maxAttempts Número máximo de tentativas
     * @param int $decayMinutes Tempo de reset em minutos
     * @return bool True se permitido, false se excedido
     */
    public function attempt(string $key, int $maxAttempts = 60, int $decayMinutes = 1): bool
    {
        $key = $this->prefix . $key;

        $attempts = (int) $this->redis->get($key);

        if ($attempts >= $maxAttempts) {
            return false;
        }

        $this->redis->incr($key);

        if ($attempts === 0) {
            $this->redis->expire($key, $decayMinutes * 60);
        }

        return true;
    }

    /**
     * Obter número de tentativas restantes
     */
    public function remaining(string $key, int $maxAttempts = 60): int
    {
        $key = $this->prefix . $key;
        $attempts = (int) $this->redis->get($key);

        return max(0, $maxAttempts - $attempts);
    }

    /**
     * Obter número de tentativas feitas
     */
    public function attempts(string $key): int
    {
        $key = $this->prefix . $key;
        return (int) $this->redis->get($key);
    }

    /**
     * Resetar rate limit para uma chave
     */
    public function reset(string $key): void
    {
        $key = $this->prefix . $key;
        $this->redis->del($key);
    }

    /**
     * Obter tempo até reset (em segundos)
     */
    public function availableIn(string $key): int
    {
        $key = $this->prefix . $key;
        return (int) $this->redis->ttl($key);
    }

    /**
     * Limpar (resetar) todas as chaves
     */
    public function clear(): void
    {
        $pattern = $this->prefix . '*';
        $keys = $this->redis->keys($pattern);

        if (!empty($keys)) {
            $this->redis->del($keys);
        }
    }

    /**
     * Too Many Attempts Exception
     */
    public function tooManyAttempts(string $key, int $maxAttempts = 60): bool
    {
        return $this->attempts($key) >= $maxAttempts;
    }
}
