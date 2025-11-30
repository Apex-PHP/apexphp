<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\User;

use App\Attributes\PublicRoute;
use App\Attributes\Auth;
use App\Attributes\ApiDoc;
use Framework\Auth\JwtAuth;
use Framework\Validation\Validation;

class AuthController extends BaseController
{
    private JwtAuth $jwtAuth;

    public function __construct()
    {
        parent::__construct();
        $this->jwtAuth = app('jwt');
    }

    /**
     * POST /api/auth/login
     * Login via JWT
     */
    #[PublicRoute]
    #[ApiDoc(
        summary: "Login via JWT",
        description: "Login via JWT",
        tags: ["Auth"],
        requestBody: [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'email' => [
                                'type' => 'string',
                                'description' => 'Email do usuário',
                                'example' => 'admin@email.com'
                            ],
                            'password' => [
                                'type' => 'string',
                                'description' => 'Senha do usuário',
                                'example' => '123456'
                            ]
                        ],
                        'required' => ['email', 'password']
                    ]
                ]
            ]
        ],
        responses: [200, 401, 422]
    )]
    public function postLogin()
    {
        $data = $this->request->all();

        // Validação
        $validation = new Validation($data);
        $validation->rule('required', ['email', 'password'])->message('O campo {field} é obrigatório')->labels([
            'email' => 'E-mail',
            'password' => 'Senha'
        ]);
        $validation->rule('email', 'email')->message('O campo {field} tem que ser um email')->label('E-mail');

        if (!$validation->validate()) {
            return $this->error('Dados inválidos', $validation->getErrors(), 422);
        }

        // Tentar autenticar
        $token = $this->jwtAuth->attempt($data['email'], $data['password']);

        if (!$token) {
            return $this->error('Credenciais inválidas', null, 401);
        }

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 'Login realizado com sucesso');
    }

    /**
     * POST /api/auth/register
     * Registro via API
     */
    #[PublicRoute]
    #[ApiDoc(
        summary: "Registro via API",
        description: "Registro via API",
        tags: ["Auth"],
        requestBody: [
            'required' => true,
            'content' => [
                'application/json' => [
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'email' => [
                                'type' => 'string',
                                'description' => 'Email do usuário',
                                'example' => 'user@example.com'
                            ],
                            'password' => [
                                'type' => 'string',
                                'description' => 'Senha do usuário',
                                'example' => '123456'
                            ]
                        ],
                        'required' => ['email', 'password']
                    ]
                ]
            ]
        ],
        responses: [201, 401, 422]
    )]
    public function postRegister()
    {
        $data = $this->request->all();

        // Validação
        $validation = new Validation($data);
        $validation->rule('required', ['name', 'email', 'role', 'password'])->message('O campo {field} é obrigatório');
        $validation->rule('email', 'email')->message('O campo {field} tem que ser um email');
        $validation->rule('lengthMin', 'name', 3)->message('O campo {field} tem que ter pelo menos 3 caracteres');
        $validation->rule('lengthMax', 'name', 100)->message('O campo {field} tem que ter no máximo 100 caracteres');
        $validation->rule("lengthMin", "password", 6)->message("O campo {field} tem que ter pelo menos 6 caracteres");

        $validation->labels([
            'name' => 'Nome',
            'email' => 'E-mail',
            'password' => 'Senha'
        ]);

        if (!$validation->validate()) {
            return $this->error('Dados inválidos', $validation->getErrors(), 422);
        }

        // Verificar email único
        if (User::where('email', $data['email'])->exists()) {
            return $this->error('Email já cadastrado', null, 422);
        }

        // Criar usuário
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => 'user',
        ]);

        // Gerar token
        $token = $this->jwtAuth->generateToken($user);

        return $this->success([
            'token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 'Usuário criado com sucesso', 201);
    }

    /**
     * GET /api/auth/me
     * Retorna dados do usuário autenticado
     */
    #[Auth]
    #[ApiDoc(
        summary: "Dados do usuário autenticado",
        description: "Retorna dados do usuário autenticado",
        tags: ["Auth"],
        responses: [200, 401]
    )]
    public function getMe()
    {
        $user = $this->jwtAuth->user();

        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at,
        ]);
    }

    /**
     * POST /api/auth/logout
     * Logout (no JWT o logout é feito no cliente)
     */
    #[Auth]
    #[ApiDoc(
        summary: "Logout",
        description: "Logout (no JWT o logout é feito no cliente)",
        tags: ["Auth"],
        responses: [200, 401]
    )]
    public function postLogout()
    {
        // Com JWT, o logout é feito no cliente removendo o token
        return $this->success([], 'Logout realizado com sucesso');
    }

    /**
     * POST /api/auth/refresh
     * Atualiza o token JWT
     */
    #[Auth]
    #[ApiDoc(
        summary: "Atualiza o token JWT",
        description: "Atualiza o token JWT",
        tags: ["Auth"],
        responses: [200, 401]
    )]
    public function postRefresh()
    {
        $token = $this->jwtAuth->getTokenFromHeader();

        if (!$token) {
            return $this->error('Token não fornecido', null, 401);
        }

        $newToken = $this->jwtAuth->refresh($token);

        if (!$newToken) {
            return $this->error('Token inválido', null, 401);
        }

        return $this->success([
            'token' => $newToken,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
        ], 'Token atualizado com sucesso');
    }
}
