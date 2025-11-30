<?php

namespace Framework\Modules\APM;

/**
 * Performance Monitor
 *
 * Monitora performance da aplicação e coleta métricas
 */
class PerformanceMonitor
{
    private array $timers = [];
    private array $metrics = [];
    private array $queries = [];
    private float $requestStartTime;
    private int $requestMemoryStart;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->requestStartTime = microtime(true);
        $this->requestMemoryStart = memory_get_usage(true);
    }

    /**
     * Iniciar timer
     */
    public function startTimer(string $name): void
    {
        $this->timers[$name] = [
            'start' => microtime(true),
            'memory_start' => memory_get_usage(true),
        ];
    }

    /**
     * Parar timer e retornar duração
     */
    public function stopTimer(string $name): float
    {
        if (!isset($this->timers[$name])) {
            return 0;
        }

        $duration = microtime(true) - $this->timers[$name]['start'];
        $memoryUsed = memory_get_usage(true) - $this->timers[$name]['memory_start'];

        $this->metrics[$name] = [
            'duration' => $duration,
            'duration_ms' => round($duration * 1000, 2),
            'memory_used' => $memoryUsed,
            'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
        ];

        return $duration;
    }

    /**
     * Registrar query SQL
     */
    public function recordQuery(string $sql, array $bindings = [], float $time = 0): void
    {
        $this->queries[] = [
            'sql' => $sql,
            'bindings' => $bindings,
            'time' => $time,
            'time_ms' => round($time * 1000, 2),
        ];
    }

    /**
     * Obter todas as queries
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * Obter queries lentas (acima do threshold)
     */
    public function getSlowQueries(float $threshold = 1.0): array
    {
        return array_filter($this->queries, function($query) use ($threshold) {
            return $query['time'] > $threshold;
        });
    }

    /**
     * Detectar problema de N+1
     */
    public function detectNPlusOne(): array
    {
        $patterns = [];

        foreach ($this->queries as $query) {
            $pattern = preg_replace('/\d+/', '?', $query['sql']);
            $patterns[$pattern] = ($patterns[$pattern] ?? 0) + 1;
        }

        // Queries executadas mais de 10 vezes com padrão similar = possível N+1
        return array_filter($patterns, fn($count) => $count > 10);
    }

    /**
     * Obter métricas gerais da requisição
     */
    public function getRequestMetrics(): array
    {
        $duration = microtime(true) - $this->requestStartTime;
        $memoryUsed = memory_get_usage(true) - $this->requestMemoryStart;

        return [
            'duration' => $duration,
            'duration_ms' => round($duration * 1000, 2),
            'memory_used' => $memoryUsed,
            'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
            'memory_peak' => memory_get_peak_usage(true),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            'queries_count' => count($this->queries),
            'queries_time' => array_sum(array_column($this->queries, 'time')),
            'queries_time_ms' => round(array_sum(array_column($this->queries, 'time')) * 1000, 2),
        ];
    }

    /**
     * Obter todas as métricas coletadas
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }

    /**
     * Obter métrica específica
     */
    public function getMetric(string $name): ?array
    {
        return $this->metrics[$name] ?? null;
    }

    /**
     * Gerar relatório completo
     */
    public function generateReport(): array
    {
        return [
            'request' => $this->getRequestMetrics(),
            'timers' => $this->metrics,
            'queries' => [
                'total' => count($this->queries),
                'slow' => count($this->getSlowQueries()),
                'n_plus_one_detected' => !empty($this->detectNPlusOne()),
                'details' => $this->queries,
            ],
            'alerts' => $this->getAlerts(),
        ];
    }

    /**
     * Obter alertas de performance
     */
    private function getAlerts(): array
    {
        $alerts = [];
        $metrics = $this->getRequestMetrics();

        // Alerta: Requisição lenta
        if ($metrics['duration_ms'] > 1000) {
            $alerts[] = [
                'type' => 'slow_request',
                'message' => "Request took {$metrics['duration_ms']}ms (threshold: 1000ms)",
                'severity' => 'warning',
            ];
        }

        // Alerta: Muitas queries
        if ($metrics['queries_count'] > 50) {
            $alerts[] = [
                'type' => 'too_many_queries',
                'message' => "Request executed {$metrics['queries_count']} queries (threshold: 50)",
                'severity' => 'warning',
            ];
        }

        // Alerta: Queries lentas
        $slowQueries = $this->getSlowQueries(0.5);
        if (!empty($slowQueries)) {
            $alerts[] = [
                'type' => 'slow_queries',
                'message' => count($slowQueries) . " slow queries detected (>500ms)",
                'severity' => 'warning',
            ];
        }

        // Alerta: Possível N+1
        $nPlusOne = $this->detectNPlusOne();
        if (!empty($nPlusOne)) {
            $alerts[] = [
                'type' => 'n_plus_one',
                'message' => "Possible N+1 query problem detected",
                'severity' => 'critical',
                'patterns' => array_keys($nPlusOne),
            ];
        }

        // Alerta: Alto uso de memória
        if ($metrics['memory_peak_mb'] > 128) {
            $alerts[] = [
                'type' => 'high_memory',
                'message' => "Peak memory usage: {$metrics['memory_peak_mb']}MB (threshold: 128MB)",
                'severity' => 'warning',
            ];
        }

        return $alerts;
    }

    /**
     * Limpar métricas
     */
    public function clear(): void
    {
        $this->timers = [];
        $this->metrics = [];
        $this->queries = [];
    }
}
