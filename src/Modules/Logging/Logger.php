<?php

namespace Framework\Modules\Logging;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\SyslogHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Formatter\JsonFormatter;
use Monolog\Formatter\LineFormatter;
use Psr\Log\LoggerInterface;

/**
 * Advanced Logger
 *
 * Sistema de logging estruturado com múltiplos canais e handlers
 */
class Logger implements LoggerInterface
{
    private array $loggers = [];
    private array $config;
    private string $defaultChannel = 'app';

    /**
     * Constructor
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * Obter logger de um canal específico
     */
    public function channel(string $name = null): MonologLogger
    {
        $name = $name ?? $this->defaultChannel;

        if (!isset($this->loggers[$name])) {
            $this->loggers[$name] = $this->createLogger($name);
        }

        return $this->loggers[$name];
    }

    /**
     * Criar logger para um canal
     */
    private function createLogger(string $channel): MonologLogger
    {
        $logger = new MonologLogger($channel);

        $channelConfig = $this->config['channels'][$channel] ?? $this->config['channels']['single'];

        // Adicionar handlers baseado na configuração
        foreach ($channelConfig['handlers'] ?? ['file'] as $handlerType) {
            $handler = $this->createHandler($handlerType, $channel, $channelConfig);
            if ($handler) {
                $logger->pushHandler($handler);
            }
        }

        return $logger;
    }

    /**
     * Criar handler específico
     */
    private function createHandler(string $type, string $channel, array $config)
    {
        $level = $this->parseLevel($config['level'] ?? 'debug');
        $path = $config['path'] ?? storage_path('logs');

        return match($type) {
            'file' => new StreamHandler(
                $path . '/' . $channel . '.log',
                $level
            ),

            'daily' => new RotatingFileHandler(
                $path . '/' . $channel . '.log',
                $config['days'] ?? 14,
                $level
            ),

            'syslog' => new SyslogHandler(
                $config['facility'] ?? 'user',
                $level
            ),

            'errorlog' => new ErrorLogHandler(
                ErrorLogHandler::OPERATING_SYSTEM,
                $level
            ),

            default => null,
        };
    }

    /**
     * Parse level name to Monolog level
     */
    private function parseLevel(string $level): int
    {
        return match(strtolower($level)) {
            'debug' => MonologLogger::DEBUG,
            'info' => MonologLogger::INFO,
            'notice' => MonologLogger::NOTICE,
            'warning' => MonologLogger::WARNING,
            'error' => MonologLogger::ERROR,
            'critical' => MonologLogger::CRITICAL,
            'alert' => MonologLogger::ALERT,
            'emergency' => MonologLogger::EMERGENCY,
            default => MonologLogger::DEBUG,
        };
    }

    /**
     * Log em formato estruturado (JSON)
     */
    public function structured(string $level, string $message, array $context = []): void
    {
        $this->log($level, $message, array_merge($context, [
            'timestamp' => date('Y-m-d H:i:s'),
            'pid' => getmypid(),
            'memory' => memory_get_usage(true),
        ]));
    }

    // PSR-3 LoggerInterface methods

    public function emergency($message, array $context = []): void
    {
        $this->channel()->emergency($message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->channel()->alert($message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->channel()->critical($message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->channel()->error($message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->channel()->warning($message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->channel()->notice($message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->channel()->info($message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->channel()->debug($message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $this->channel()->log($level, $message, $context);
    }

    /**
     * Log de performance
     */
    public function performance(string $operation, float $duration, array $context = []): void
    {
        $this->channel('performance')->info("Operation: {$operation}", array_merge($context, [
            'duration_ms' => round($duration * 1000, 2),
            'operation' => $operation,
        ]));
    }

    /**
     * Log de query SQL
     */
    public function query(string $sql, array $bindings = [], float $time = 0): void
    {
        $this->channel('database')->debug($sql, [
            'bindings' => $bindings,
            'time_ms' => round($time * 1000, 2),
        ]);
    }

    /**
     * Log de requisição HTTP
     */
    public function request(string $method, string $uri, int $status, float $duration): void
    {
        $this->channel('requests')->info("{$method} {$uri}", [
            'method' => $method,
            'uri' => $uri,
            'status' => $status,
            'duration_ms' => round($duration * 1000, 2),
        ]);
    }

    /**
     * Log de segurança/autenticação
     */
    public function security(string $event, array $context = []): void
    {
        $this->channel('security')->warning($event, $context);
    }
}
