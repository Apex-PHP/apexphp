<?php

namespace Framework\Modules\APM;

use Framework\Modules\Logging\Logger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * APM Middleware
 *
 * Monitora performance de cada requisição
 */
class APMMiddleware implements MiddlewareInterface
{
    private PerformanceMonitor $monitor;
    private ?Logger $logger;
    private array $config;

    public function __construct(PerformanceMonitor $monitor, ?Logger $logger = null, array $config = [])
    {
        $this->monitor = $monitor;
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * Process middleware
     */
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $this->monitor->startTimer('request');

        // Processar request
        $response = $handler->handle($request);

        $this->monitor->stopTimer('request');

        // Coletar métricas
        $metrics = $this->monitor->getRequestMetrics();
        $alerts = $this->monitor->getAlerts();

        // Logar performance
        if ($this->logger && ($this->config['log_all'] ?? false)) {
            $this->logPerformance($request, $response, $metrics);
        }

        // Logar apenas se houver alertas
        if ($this->logger && !empty($alerts)) {
            $this->logAlerts($request, $alerts);
        }

        // Adicionar headers de performance (se em desenvolvimento)
        if ($this->config['debug_headers'] ?? false) {
            $response = $this->addDebugHeaders($response, $metrics);
        }

        // Adicionar header Server-Timing
        if ($this->config['server_timing'] ?? true) {
            $response = $this->addServerTimingHeader($response);
        }

        return $response;
    }

    /**
     * Logar métricas de performance
     */
    private function logPerformance(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $metrics
    ): void {
        $method = $request->getMethod();
        $uri = (string) $request->getUri();
        $status = $response->getStatusCode();

        $this->logger->performance("{$method} {$uri}", $metrics['duration'], [
            'method' => $method,
            'uri' => $uri,
            'status' => $status,
            'duration_ms' => $metrics['duration_ms'],
            'memory_mb' => $metrics['memory_used_mb'],
            'queries' => $metrics['queries_count'],
        ]);
    }

    /**
     * Logar alertas de performance
     */
    private function logAlerts(ServerRequestInterface $request, array $alerts): void
    {
        $uri = (string) $request->getUri();

        foreach ($alerts as $alert) {
            $level = $alert['severity'] === 'critical' ? 'error' : 'warning';

            $this->logger->channel('performance')->{$level}(
                "Performance Alert: {$alert['message']}",
                [
                    'uri' => $uri,
                    'type' => $alert['type'],
                    'alert' => $alert,
                ]
            );
        }
    }

    /**
     * Adicionar headers de debug
     */
    private function addDebugHeaders(ResponseInterface $response, array $metrics): ResponseInterface
    {
        return $response
            ->withHeader('X-Debug-Time', $metrics['duration_ms'] . 'ms')
            ->withHeader('X-Debug-Memory', $metrics['memory_used_mb'] . 'MB')
            ->withHeader('X-Debug-Queries', (string) $metrics['queries_count']);
    }

    /**
     * Adicionar header Server-Timing
     *
     * Visível no DevTools do Chrome (Network tab)
     */
    private function addServerTimingHeader(ResponseInterface $response): ResponseInterface
    {
        $timings = [];

        foreach ($this->monitor->getMetrics() as $name => $metric) {
            $timings[] = "{$name};dur={$metric['duration_ms']}";
        }

        // Adicionar timing de queries
        $queries = $this->monitor->getQueries();
        if (!empty($queries)) {
            $totalQueryTime = array_sum(array_column($queries, 'time')) * 1000;
            $timings[] = "db;dur=" . round($totalQueryTime, 2) . ";desc=\"" . count($queries) . " queries\"";
        }

        if (!empty($timings)) {
            $response = $response->withHeader('Server-Timing', implode(', ', $timings));
        }

        return $response;
    }
}
