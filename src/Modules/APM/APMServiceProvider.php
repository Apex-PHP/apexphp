<?php

namespace Framework\Modules\APM;

use Framework\Core\ServiceProvider;
use Framework\Modules\Logging\Logger;

/**
 * APM Service Provider
 *
 * Registra Application Performance Monitoring
 */
class APMServiceProvider extends ServiceProvider
{
    /**
     * Registrar serviços
     */
    public function register(): void
    {
        $this->config = $this->loadConfig('apm.php');

        // Registrar PerformanceMonitor
        $this->container->set(PerformanceMonitor::class, function () {
            return new PerformanceMonitor();
        });

        // Registrar Middleware
        $this->container->set(APMMiddleware::class, function ($container) {
            $logger = $container->has(Logger::class) ? $container->get(Logger::class) : null;

            return new APMMiddleware(
                $container->get(PerformanceMonitor::class),
                $logger,
                $this->config
            );
        });

        // Helper function
        if (!function_exists('apm')) {
            /**
             * Helper para acessar performance monitor
             *
             * @return PerformanceMonitor
             */
            function apm(): PerformanceMonitor {
                global $container;
                return $container->get(PerformanceMonitor::class);
            }
        }

        if (!function_exists('performance')) {
            /**
             * Helper para medir performance de um callable
             *
             * @param string $name Nome da operação
             * @param callable $callback Função a executar
             * @return mixed Retorno do callback
             */
            function performance(string $name, callable $callback) {
                apm()->startTimer($name);
                $result = $callback();
                apm()->stopTimer($name);
                return $result;
            }
        }
    }

    /**
     * Bootstrap do módulo
     */
    public function boot(): void
    {
        // Hook no Eloquent para monitorar queries
        if (class_exists('\Illuminate\Database\Capsule\Manager')) {
            $this->setupDatabaseMonitoring();
        }
    }

    /**
     * Configurar monitoramento de database
     */
    private function setupDatabaseMonitoring(): void
    {
        $monitor = $this->container->get(PerformanceMonitor::class);

        \Illuminate\Database\Capsule\Manager::connection()->listen(
            function ($query) use ($monitor) {
                $monitor->recordQuery(
                    $query->sql,
                    $query->bindings,
                    $query->time / 1000 // Eloquent retorna em milliseconds
                );

                // Log slow queries
                if ($this->config['log_slow_queries'] ?? true) {
                    $threshold = $this->config['slow_query_threshold'] ?? 1.0;

                    if (($query->time / 1000) > $threshold) {
                        if ($this->container->has(Logger::class)) {
                            $logger = $this->container->get(Logger::class);
                            $logger->channel('database')->warning('Slow query detected', [
                                'sql' => $query->sql,
                                'bindings' => $query->bindings,
                                'time_ms' => $query->time,
                            ]);
                        }
                    }
                }
            }
        );
    }
}
