<?php

namespace Framework\Auth;

use App\Models\User;
use Framework\Session\Session;

class Auth
{
    private static ?User $user = null;
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Tenta autenticar um usuário
     */
    public function attempt(string $email, string $password): bool
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user->password)) {
            return false;
        }

        $this->login($user);

        return true;
    }

    /**
     * Faz login do usuário
     */
    public function login(User $user): void
    {
        $this->session->put('user_id', $user->id);
        $this->session->put('user_email', $user->email);
        $this->session->regenerate(); // Previne session fixation

        self::$user = $user;
    }

    /**
     * Verifica se o usuário está autenticado
     */
    public function check(): bool
    {
        return $this->session->has('user_id');
    }

    /**
     * Verifica se é visitante (não autenticado)
     */
    public function guest(): bool
    {
        return !$this->check();
    }

    /**
     * Retorna o usuário autenticado
     */
    public function user(): ?User
    {
        if (self::$user !== null) {
            return self::$user;
        }

        if (!$this->check()) {
            return null;
        }

        $userId = $this->session->get('user_id');
        self::$user = User::find($userId);

        return self::$user;
    }

    /**
     * Retorna o ID do usuário autenticado
     */
    public function id(): ?int
    {
        return $this->session->get('user_id');
    }

    /**
     * Faz logout do usuário
     */
    public function logout(): void
    {
        self::$user = null;
        $this->session->remove('user_id');
        $this->session->remove('user_email');
        $this->session->destroy();
    }

    /**
     * Verifica se o usuário tem uma role específica
     */
    public function hasRole(string $role): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        return $user->role === $role;
    }

    /**
     * Verifica se o usuário tem alguma das roles especificadas
     */
    public function hasAnyRole(array $roles): bool
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }

        return in_array($user->role, $roles);
    }
}
