<?php

namespace Framework\Auth;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class JwtAuth
{
    private string $secret;
    private string $algorithm = 'HS256';
    private int $expiration;

    public function __construct()
    {
        $this->secret = env('JWT_SECRET', 'change-this-secret-key');
        $this->expiration = (int) env('JWT_EXPIRATION', 3600);

        if ($this->secret === 'change-this-secret-key') {
            throw new Exception('JWT_SECRET não configurado no .env');
        }
    }

    /**
     * Tenta autenticar e retorna o token JWT
     */
    public function attempt(string $email, string $password): ?string
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user->password)) {
            return null;
        }

        return $this->generateToken($user);
    }

    /**
     * Gera um token JWT para o usuário
     */
    public function generateToken(User $user): string
    {
        $payload = [
            'iss' => env('APP_URL', 'http://localhost'),
            'sub' => $user->id,
            'iat' => time(),
            'exp' => time() + $this->expiration,
            'email' => $user->email,
            'name' => $user->name,
            'role' => $user->role ?? 'user',
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * Valida um token JWT
     */
    public function validateToken(string $token): ?object
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return $decoded;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Obtém o usuário do token
     */
    public function getUserFromToken(string $token): ?User
    {
        $decoded = $this->validateToken($token);

        if (!$decoded) {
            return null;
        }

        return User::find($decoded->sub);
    }

    /**
     * Extrai token do header Authorization
     */
    public function getTokenFromHeader(): ?string
    {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            return null;
        }

        $authorization = $headers['Authorization'];

        if (preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Retorna o usuário autenticado da requisição atual
     */
    public function user(): ?User
    {
        $token = $this->getTokenFromHeader();

        if (!$token) {
            return null;
        }

        return $this->getUserFromToken($token);
    }

    /**
     * Verifica se há um usuário autenticado
     */
    public function check(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Refresh token (gera novo token)
     */
    public function refresh(string $token): ?string
    {
        $user = $this->getUserFromToken($token);

        if (!$user) {
            return null;
        }

        return $this->generateToken($user);
    }
}
