<?php

/**
 * Rotas de API
 *
 * Todas as rotas aqui são prefixadas com /api
 *
 * Exemplos de rotas dinâmicas de API:
 * POST /api/auth/login     → Api\AuthController::postLogin()
 * POST /api/auth/register  → Api\AuthController::postRegister()
 * GET  /api/auth/me        → Api\AuthController::getMe()
 * POST /api/auth/logout    → Api\AuthController::postLogout()
 *
 * Para criar um controller de API, coloque-o em:
 * app/Controllers/Api/SeuController.php
 *
 * Exemplo de requisição:
 * POST /api/auth/login
 * Content-Type: application/json
 * {
 *   "email": "admin@email.com",
 *   "password": "123456"
 * }
 *
 * Exemplo de resposta:
 * {
 *   "success": true,
 *   "message": "Login realizado com sucesso",
 *   "data": {
 *     "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
 *     "token_type": "Bearer",
 *     "expires_in": 3600
 *   }
 * }
 *
 * Para rotas protegidas, use o header Authorization:
 * Authorization: Bearer {seu-token-jwt}
 */

/** @var Framework\Core\Router $router */

// Rotas estáticas de API (opcional)
// $router->get('health', function () {
//     return ['status' => 'ok', 'timestamp' => time()];
// });

// Todas as rotas dinâmicas são processadas automaticamente
// com o controle de acesso via Attributes (#[Auth], #[PublicRoute])
