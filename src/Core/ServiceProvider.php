<?php

namespace Framework\Core;

use DI\Container;

/**
 * Base Service Provider
 *
 * Classe abstrata para criação de módulos do framework
 */
abstract class ServiceProvider
{
    /**
     * Container de dependências
     */
    protected Container $container;

    /**
     * Configurações do módulo
     */
    protected array $config = [];

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Registrar serviços do módulo
     *
     * Chamado durante a inicialização do container
     */
    abstract public function register(): void;

    /**
     * Bootstrap do módulo
     *
     * Chamado após todos os providers serem registrados
     */
    public function boot(): void
    {
        // Override in child classes if needed
    }

    /**
     * Verificar se módulo está habilitado
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'] ?? true;
    }

    /**
     * Carregar configurações do módulo
     */
    protected function loadConfig(string $configFile): array
    {
        $configPath = __DIR__ . '/../../config/modules/' . $configFile;

        if (file_exists($configPath)) {
            return require $configPath;
        }

        return [];
    }

    /**
     * Registrar middleware
     */
    protected function registerMiddleware(string $middleware): void
    {
        // Implementação depende do sistema de middleware do Slim
        // Pode ser customizado conforme necessário
    }

    /**
     * Publicar arquivos de configuração
     */
    public function publishes(): array
    {
        return [];
    }
}
