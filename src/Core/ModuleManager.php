<?php

namespace Framework\Core;

use DI\Container;

/**
 * Module Manager
 *
 * Gerencia o carregamento e inicialização de módulos
 */
class ModuleManager
{
    /**
     * Container de dependências
     */
    private Container $container;

    /**
     * Service Providers registrados
     */
    private array $providers = [];

    /**
     * Service Providers já inicializados
     */
    private array $booted = [];

    /**
     * Constructor
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Registrar um service provider
     */
    public function register(string $providerClass): void
    {
        if (isset($this->providers[$providerClass])) {
            return;
        }

        $provider = new $providerClass($this->container);

        if (!$provider->isEnabled()) {
            return;
        }

        $provider->register();
        $this->providers[$providerClass] = $provider;
    }

    /**
     * Registrar múltiplos providers
     */
    public function registerProviders(array $providers): void
    {
        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    /**
     * Boot de todos os providers
     */
    public function bootProviders(): void
    {
        foreach ($this->providers as $class => $provider) {
            if (!isset($this->booted[$class])) {
                $provider->boot();
                $this->booted[$class] = true;
            }
        }
    }

    /**
     * Obter provider registrado
     */
    public function getProvider(string $class): ?ServiceProvider
    {
        return $this->providers[$class] ?? null;
    }

    /**
     * Verificar se provider está registrado
     */
    public function hasProvider(string $class): bool
    {
        return isset($this->providers[$class]);
    }

    /**
     * Obter todos os providers
     */
    public function getProviders(): array
    {
        return $this->providers;
    }
}
