<?php

namespace App\Controllers;

use Framework\Core\View;
use Framework\Core\Request;
use Framework\Session\Session;
use Framework\Auth\Auth;

abstract class BaseController
{
    protected View $view;
    protected Session $session;
    protected Auth $auth;
    protected Request $request;

    public function __construct()
    {
        $this->view = app('view');
        $this->session = app('session');
        $this->auth = app('auth');
        $this->request = new Request();
    }

    /**
     * Renderiza uma view
     */
    protected function render(string $view, array $data = []): string
    {
        return $this->view->render($view, $data);
    }

    /**
     * Retorna resposta JSON
     */
    protected function json($data, int $status = 200): string
    {
        http_response_code($status);
        header('Content-Type: application/json');
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Redireciona para uma URL
     */
    protected function redirect(string $url): void
    {
        redirect($url);
    }

    /**
     * Volta para a página anterior
     */
    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        redirect($referer);
    }

    /**
     * Resposta de sucesso JSON
     */
    protected function success($data = [], string $message = 'Success', int $status = 200): string
    {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    /**
     * Resposta de erro JSON
     */
    protected function error(string $message = 'Error', $errors = null, int $status = 400): string
    {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->json($response, $status);
    }
}
