<?php

namespace Framework\Session;

class Session
{
    private static bool $started = false;

    public function __construct()
    {
        if (!self::$started) {
            $this->start();
        }
    }

    public function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
            self::$started = true;

            // Inicializar CSRF token se não existir
            if (!isset($_SESSION["_csrf_token"])) {
                $_SESSION["_csrf_token"] = bin2hex(random_bytes(32));
            }
        }
    }

    public function put(string $key, $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public function all(): array
    {
        return $_SESSION ?? [];
    }

    public function destroy(): void
    {
        session_destroy();
        $_SESSION = [];
    }

    public function regenerate(): void
    {
        session_regenerate_id(true);
    }

    public function setFlash(string $key, $value): void
    {
        $_SESSION["_flash"][$key] = $value;
    }

    public function getFlash(string $key, $default = null)
    {
        $value = $_SESSION["_flash"][$key] ?? $default;

        if (isset($_SESSION["_flash"][$key])) {
            unset($_SESSION["_flash"][$key]);
        }

        return $value;
    }

    public function hasFlash(string $key): bool
    {
        return isset($_SESSION["_flash"][$key]);
    }

    public function setOldInput(array $data = []): void
    {
        $_SESSION["_old_input"] = $data;
    }

    public function getOld(string $key, $default = null)
    {
        $value = $_SESSION["_old_input"][$key] ?? $default;
        return $value;
    }

    public function clearOldInput(): void
    {
        unset($_SESSION["_old_input"]);
    }

    public function setErrors(array $errors): void
    {
        $_SESSION["_errors"] = $errors;
    }

    public function getErrors(): array
    {
        return $_SESSION["_errors"] ?? [];
    }

    public function hasErrors(): bool
    {
        return !empty($_SESSION["_errors"]);
    }

    public function clearErrors(): void
    {
        unset($_SESSION["_errors"]);
    }
}
