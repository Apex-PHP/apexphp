<?php

/**
 * ApexPHP Framework
 * Entry Point
 */

require __DIR__ . '/../vendor/autoload.php';

use Framework\Core\Application;

// Criar e executar aplicação
$app = new Application();

// Registrar middleware globais
$router = $app->getRouter();

// Middleware para rotas web
$router->middleware('web', App\Middleware\WebMiddleware::class);

// Middleware para rotas de API
$router->middleware('api', App\Middleware\ApiMiddleware::class);

// Middleware CORS (para APIs)
$router->middleware('cors', App\Middleware\CorsMiddleware::class);

// Executar aplicação
$app->run();
