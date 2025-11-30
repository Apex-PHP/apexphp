<?php

namespace Framework\Core;

use Dotenv\Dotenv;
use Framework\Database\Database;
use Framework\Core\View;
use Framework\Session\Session;
use Framework\Auth\Auth;
use Framework\Auth\JwtAuth;

class Application
{
    private static ?self $instance = null;
    private Container $container;
    private Router $router;

    public function __construct()
    {
        self::$instance = $this;

        $this->loadEnvironment();
        $this->container = new Container();
        $this->registerServices();
        $this->router = new Router($this->container);
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    private function loadEnvironment(): void
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->safeLoad();
    }

    private function registerServices(): void
    {
        // Database
        $this->container->singleton('db', function () {
            return Database::getInstance();
        });

        // Session
        $this->container->singleton('session', function () {
            return new Session();
        });

        // View Engine
        $this->container->singleton('view', function () {
            return new View();
        });

        // Auth
        $this->container->singleton('auth', function ($container) {
            return new Auth($container->get('session'));
        });

        // Auth facade
        $this->container->singleton(Auth::class, function ($container) {
            return $container->get('auth');
        });

        // JWT Auth
        $this->container->singleton('jwt', function () {
            return new JwtAuth();
        });

        $this->container->singleton(JwtAuth::class, function ($container) {
            return $container->get('jwt');
        });

        // Router
        $this->container->singleton(Router::class, function ($container) {
            return new Router($container);
        });
    }

    public function run(): void
    {
        try {
            // Iniciar sessão
            $this->container->get('session')->start();

            // Inicializar database
            $this->container->get('db');

            // Carregar rotas
            $this->router->loadRoutes();

            // Despachar requisição
            $response = $this->router->dispatch();
            $this->sendResponse($response);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    private function sendResponse($response): void
    {
        if (is_string($response)) {
            echo $response;
        } elseif (is_array($response)) {
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            echo $response;
        }
    }

    private function handleException(\Exception $e): void
    {
        if (config('app.debug')) {
            $whoops = new \Whoops\Run;
            $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
            $whoops->register();
            throw $e;
        } else {
            http_response_code(500);

            if ($this->isApiRequest()) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Internal Server Error']);
            } else {
                if (file_exists(base_path('resources/views/errors/500.blade.php'))) {
                    echo view('errors.500', ['error' => $e->getMessage()]);
                } else {
                    echo '<h1>500 - Internal Server Error</h1>';
                }
            }
        }
    }

    private function isApiRequest(): bool
    {
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        return strpos($uri, '/api/') === 0 || strpos($uri, '/api') === 0;
    }

    public function getRouter(): Router
    {
        return $this->router;
    }
}
