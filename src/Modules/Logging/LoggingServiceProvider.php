<?php

namespace Framework\Modules\Logging;

use Framework\Core\ServiceProvider;

/**
 * Logging Service Provider
 *
 * Registra sistema de logging avançado
 */
class LoggingServiceProvider extends ServiceProvider
{
    /**
     * Registrar serviços
     */
    public function register(): void
    {
        $this->config = $this->loadConfig('logging.php');

        // Registrar Logger
        $this->container->set(Logger::class, function () {
            return new Logger($this->config);
        });

        // Alias PSR-3
        $this->container->set(\Psr\Log\LoggerInterface::class, function ($container) {
            return $container->get(Logger::class);
        });

        // Helper function
        if (!function_exists('logger')) {
            /**
             * Helper para acessar logger
             *
             * @param string|null $channel
             * @param string|null $message
             * @param array $context
             * @return Logger|\Monolog\Logger|mixed
             */
            function logger(?string $channel = null, ?string $message = null, array $context = [])
            {
                global $container;
                /** @var Logger $logger */
                $logger = $container->get(Logger::class);

                if ($message === null) {
                    return $channel ? $logger->channel($channel) : $logger;
                }

                return $logger->channel($channel)->info($message, $context);
            }
        }
    }

    /**
     * Bootstrap do módulo
     */
    public function boot(): void
    {
        // Configurar error handler para usar logger
        if ($this->config['log_errors'] ?? true) {
            set_error_handler(function ($severity, $message, $file, $line) {
                if (!(error_reporting() & $severity)) {
                    return;
                }

                logger()->error("PHP Error: {$message}", [
                    'file' => $file,
                    'line' => $line,
                    'severity' => $severity,
                ]);
            });
        }
    }
}
