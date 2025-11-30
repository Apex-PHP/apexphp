<?php

if (!function_exists('env')) {
    function env(string $key, $default = null)
    {
        $value = $_ENV[$key] ?? $_SERVER[$key] ?? $default;

        if ($value === false) {
            return $default;
        }

        switch (strtolower((string)$value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'empty':
            case '(empty)':
                return '';
            case 'null':
            case '(null)':
                return null;
        }

        return $value;
    }
}

if (!function_exists('config')) {
    function config(string $key, $default = null)
    {
        static $config = [];

        if (empty($config)) {
            $configFiles = glob(__DIR__ . '/../../config/*.php');
            foreach ($configFiles as $file) {
                $name = basename($file, '.php');
                $config[$name] = require $file;
            }
        }

        $keys = explode('.', $key);
        $value = $config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return __DIR__ . '/../../' . ltrim($path, '/');
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path = ''): string
    {
        return base_path('public/' . ltrim($path, '/'));
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return base_path('storage/' . ltrim($path, '/'));
    }
}

if (!function_exists('view')) {
    function view(string $name, array $data = []): string
    {
        return app('view')->render($name, $data);
    }
}

if (!function_exists('app')) {
    function app(string $abstract = null)
    {
        $container = Framework\Core\Application::getInstance()->getContainer();

        if (is_null($abstract)) {
            return $container;
        }

        return $container->get($abstract);
    }
}

if (!function_exists('dd')) {
    function dd(...$vars): void
    {
        foreach ($vars as $var) {
            var_dump($var);
        }
        die(1);
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url, int $status = 302): void
    {
        header("Location: $url", true, $status);
        exit;
    }
}

if (!function_exists('session')) {
    function session(string $key = null, $default = null)
    {
        $session = app('session');

        if (is_null($key)) {
            return $session;
        }

        return $session->get($key, $default);
    }
}

if (!function_exists('old')) {
    function old(string $key, $default = null)
    {
        return session()->getOld($key, $default);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return session()->get('_csrf_token', '');
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        $token = csrf_token();
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token) . '">';
    }
}

if (!function_exists('auth')) {
    function auth()
    {
        return app('auth');
    }
}

if (!function_exists('user')) {
    function user()
    {
        return auth()->user();
    }
}

if (!function_exists('flash')) {
    function flash(string $key = null, $default = null)
    {
        $session = app('session');

        if (is_null($key)) {
            return $session;
        }

        return $session->getFlash($key, $default);
    }
}
