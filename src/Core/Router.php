<?php

namespace Framework\Core;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionMethod;
use App\Attributes\Auth;
use App\Attributes\Guest;
use App\Attributes\PublicRoute;

class Router
{
    private ContainerInterface $container;
    private array $routes = [];
    private array $middleware = [];
    private array $groupMiddleware = [];
    private ?string $groupPrefix = null;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Carrega rotas dos arquivos
     */
    public function loadRoutes(): void
    {
        // Carregar rotas web
        $this->group(['middleware' => ['web']], function ($router) {
            if (file_exists(base_path('routes/web.php'))) {
                require_once base_path('routes/web.php');
            }
        });

        // Carregar rotas API
        $this->group(['prefix' => 'api', 'middleware' => ['api']], function ($router) {
            if (file_exists(base_path('routes/api.php'))) {
                require_once base_path('routes/api.php');
            }
        });
    }

    /**
     * Adiciona uma rota GET
     */
    public function get(string $uri, $action): void
    {
        $this->addRoute('GET', $uri, $action);
    }

    /**
     * Adiciona uma rota POST
     */
    public function post(string $uri, $action): void
    {
        $this->addRoute('POST', $uri, $action);
    }

    /**
     * Adiciona uma rota PUT
     */
    public function put(string $uri, $action): void
    {
        $this->addRoute('PUT', $uri, $action);
    }

    /**
     * Adiciona uma rota PATCH
     */
    public function patch(string $uri, $action): void
    {
        $this->addRoute('PATCH', $uri, $action);
    }

    /**
     * Adiciona uma rota DELETE
     */
    public function delete(string $uri, $action): void
    {
        $this->addRoute('DELETE', $uri, $action);
    }

    /**
     * Adiciona rota para qualquer verbo HTTP
     */
    public function any(string $uri, $action): void
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];
        foreach ($methods as $method) {
            $this->addRoute($method, $uri, $action);
        }
    }

    /**
     * Agrupa rotas com middleware ou prefix
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousMiddleware = $this->groupMiddleware;
        $previousPrefix = $this->groupPrefix;

        if (isset($attributes['middleware'])) {
            $this->groupMiddleware = array_merge(
                $this->groupMiddleware,
                (array) $attributes['middleware']
            );
        }

        if (isset($attributes['prefix'])) {
            $this->groupPrefix = ($this->groupPrefix ?? '') . '/' . trim($attributes['prefix'], '/');
        }

        $callback($this);

        $this->groupMiddleware = $previousMiddleware;
        $this->groupPrefix = $previousPrefix;
    }

    /**
     * Adiciona uma rota ao array de rotas
     */
    private function addRoute(string $method, string $uri, $action): void
    {
        $uri = '/' . trim(($this->groupPrefix ?? '') . '/' . $uri, '/');

        $this->routes[$method][$uri] = [
            'action' => $action,
            'middleware' => $this->groupMiddleware,
        ];
    }

    /**
     * Adiciona middleware global
     */
    public function middleware(string $name, string $class): void
    {
        $this->middleware[$name] = $class;
    }

    /**
     * Despacha a requisição
     */
    public function dispatch()
    {
        $uri = $this->getCurrentUri();
        $method = $_SERVER['REQUEST_METHOD'];

        // Simular PUT, PATCH, DELETE via _method
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        // Verifica se existe rota definida explicitamente
        if (isset($this->routes[$method][$uri])) {
            return $this->handleDefinedRoute($method, $uri);
        }

        // Tenta rota dinâmica
        return $this->handleDynamicRoute($method, $uri);
    }

    /**
     * Manipula rota definida explicitamente
     */
    private function handleDefinedRoute(string $method, string $uri)
    {
        $route = $this->routes[$method][$uri];
        $action = $route['action'];
        $middleware = $route['middleware'];

        // Aplicar middleware do grupo
        foreach ($middleware as $middlewareName) {
            if (isset($this->middleware[$middlewareName])) {
                $middlewareClass = $this->middleware[$middlewareName];
                $middlewareInstance = $this->container->get($middlewareClass);

                if (!$middlewareInstance->handle()) {
                    return;
                }
            }
        }

        // Executar action
        if (is_callable($action)) {
            return $action();
        } elseif (is_string($action)) {
            return $this->callControllerAction($action);
        }

        throw new \Exception("Invalid route action");
    }

    /**
     * Manipula rota dinâmica (baseada em convenção)
     */
    private function handleDynamicRoute(string $method, string $uri)
    {
        $segments = array_filter(explode('/', trim($uri, '/')));
        $segments = array_values($segments); // Reindexar

        // Se não houver segmentos suficientes, retorna 404
        if (count($segments) < 1) {
            return $this->notFound();
        }

        // Detectar se é API
        $isApi = false;
        if ($segments[0] === 'api') {
            $isApi = true;
            array_shift($segments); // Remove 'api' do caminho
        }

        // Se após remover 'api' não houver mais segmentos, 404
        if (count($segments) < 1) {
            return $this->notFound();
        }

        $controllerName = ucfirst($segments[0]) . 'Controller';

        // Se não especificou action, tenta 'index' como padrão
        if (count($segments) < 2) {
            $action = 'index';
            $params = [];
        } else {
            $action = $segments[1];
            $params = array_slice($segments, 2);
        }

        // Determina o prefixo do método baseado no verbo HTTP
        $methodPrefix = strtolower($method);
        $methodName = $methodPrefix . ucfirst($action);

        // Monta o namespace do controller
        if ($isApi) {
            $controllerClass = "App\\Controllers\\Api\\{$controllerName}";
        } else {
            $controllerClass = "App\\Controllers\\{$controllerName}";
        }

        if (!class_exists($controllerClass)) {
            return $this->notFound();
        }

        if (!method_exists($controllerClass, $methodName)) {
            return $this->notFound();
        }

        // Verificar Attributes do método
        $reflection = new ReflectionMethod($controllerClass, $methodName);
        $classReflection = new ReflectionClass($controllerClass);

        // Verificar Attributes da classe primeiro
        $classAttributes = $classReflection->getAttributes();
        $this->processAttributes($classAttributes, $isApi);

        // Verificar Attributes do método (tem prioridade sobre a classe)
        $methodAttributes = $reflection->getAttributes();
        $this->processAttributes($methodAttributes, $isApi);

        // Criar controller e chamar método
        $controller = $this->container->get($controllerClass);
        return call_user_func_array([$controller, $methodName], $params);
    }

    /**
     * Processa Attributes (Auth, Guest, PublicRoute)
     */
    private function processAttributes(array $attributes, bool $isApi): void
    {
        $auth = $this->container->get(\Framework\Auth\Auth::class);
        $jwtAuth = $this->container->get(\Framework\Auth\JwtAuth::class);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();

            // Attribute: Auth - Requer autenticação
            if ($instance instanceof Auth) {
                if ($isApi) {
                    if (!$jwtAuth->check()) {
                        http_response_code(401);
                        header('Content-Type: application/json');
                        echo json_encode(['error' => 'Não autenticado']);
                        exit;
                    }
                } else {
                    if (!$auth->check()) {
                        session()->setFlash('error', 'Você precisa estar autenticado');
                        redirect('/auth/login');
                    }
                }

                // Verificar roles se especificadas
                if (!empty($instance->roles)) {
                    if (!$auth->hasAnyRole($instance->roles)) {
                        if ($isApi) {
                            http_response_code(403);
                            header('Content-Type: application/json');
                            echo json_encode(['error' => 'Acesso negado']);
                            exit;
                        } else {
                            session()->setFlash('error', 'Você não tem permissão');
                            redirect('/');
                        }
                    }
                }
            }

            // Attribute: Guest - Apenas visitantes
            if ($instance instanceof Guest) {
                if ($auth->check()) {
                    redirect($instance->redirectTo);
                }
            }

            // Attribute: PublicRoute - Não faz nada, apenas documentação
            if ($instance instanceof PublicRoute) {
                // Rota pública, não precisa fazer nada
            }
        }
    }

    /**
     * Chama uma action de controller definida como string
     */
    private function callControllerAction(string $action)
    {
        [$controllerClass, $method] = explode('@', $action);

        $controllerClass = "App\\Controllers\\{$controllerClass}";

        if (!class_exists($controllerClass)) {
            throw new \Exception("Controller {$controllerClass} not found");
        }

        $controller = $this->container->get($controllerClass);

        if (!method_exists($controller, $method)) {
            throw new \Exception("Method {$method} not found in {$controllerClass}");
        }

        return $controller->$method();
    }

    /**
     * Obtém a URI atual
     */
    private function getCurrentUri(): string
    {
        $uri = $_SERVER['REQUEST_URI'];

        // Remove query string
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }

        return '/' . trim($uri, '/');
    }

    /**
     * Retorna resposta 404
     */
    private function notFound()
    {
        http_response_code(404);

        if ($this->isApiRequest()) {
            header('Content-Type: application/json');
            return json_encode(['error' => 'Route not found']);
        }

        if (file_exists(base_path('resources/views/errors/404.blade.php'))) {
            return view('errors.404');
        }

        return '<h1>404 - Not Found</h1>';
    }

    /**
     * Verifica se é uma requisição de API
     */
    private function isApiRequest(): bool
    {
        $uri = $this->getCurrentUri();
        return strpos($uri, '/api/') === 0 || strpos($uri, '/api') === 0;
    }
}
