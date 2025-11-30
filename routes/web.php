<?php

/**
 * Rotas Web
 *
 * Este arquivo define rotas estáticas.
 * As rotas dinâmicas são processadas automaticamente pelo Router.
 *
 * Exemplos de rotas dinâmicas (não precisam ser definidas aqui):
 * GET  /auth/login         → AuthController::getLogin()
 * POST /auth/login         → AuthController::postLogin()
 * GET  /users/list         → UsersController::getList()
 * POST /users/create       → UsersController::postCreate()
 * PUT  /users/update/5     → UsersController::putUpdate($id)
 * DELETE /users/delete/5   → UsersController::deleteDelete($id)
 *
 * O controle de acesso é feito via Attributes:
 * #[PublicRoute]   - Acesso público
 * #[Auth]          - Requer autenticação
 * #[Guest]         - Apenas visitantes (não autenticados)
 */

/** @var Framework\Core\Router $router */

// Rota raiz
$router->get('', function () {
    return view('home');
});

$router->get('/', function () {
    return view('home');
});

// Atalho para dashboard
$router->get('dashboard', function () {
    return redirect('/home/dashboard');
});

// Atalho para profile
$router->get('profile', function () {
    return redirect('/home/profile');
});

// Exemplos de rotas estáticas (opcional)
// $router->get('sobre', function () {
//     return view('pages.about');
// });

// Grupo de rotas com middleware (opcional)
// $router->group(['middleware' => ['auth']], function ($router) {
//     $router->get('admin/dashboard', 'AdminController@dashboard');
// });
