<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\User;

use App\Attributes\PublicRoute;
use App\Attributes\Auth;
use App\Attributes\ApiDoc;
use Framework\Auth\JwtAuth;
use Framework\Validation\Validation;

class UsersController extends BaseController
{
    private JwtAuth $jwtAuth;

    public function __construct()
    {
        parent::__construct();
        $this->jwtAuth = app('jwt');
    }

    /**
     * GET /api/users
     * Lista todos os usuários do sistema
     */
    #[Auth]
    #[ApiDoc(
        summary: "Lista todos os usuários do sistema",
        description: "Retorna uma lista paginada de usuários",
    )]
    public function getIndex()
    {
        $page = $this->request->input('page', 1);
        $perPage = $this->request->input('per_page', 15);

        $users = User::paginate($perPage, ['*'], 'page', $page);

        return $this->success([
            'data' => $users->items(),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem(),
            ],
        ]);
    }

    /**
     * GET /api/users/show/{id}
     * Exibe um usuário específico
     */
    #[Auth]
    #[ApiDoc(
        summary: "Busca um usuário por ID",
        description: "Retorna os detalhes de um usuário específico",
    )]
    public function getShow(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('Registro não encontrado', null, 404);
        }

        return $this->success($user);
    }

    /**
     * POST /api/users/create
     * Cria um novo usuário
     */
    #[Auth]
    #[ApiDoc(
        summary: "Cria um novo usuário",
        description: "Cria um novo registro de usuário no banco de dados",
    )]
    public function postCreate()
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
            return $this->error('Dados inválidos', $validation->errors(), 422);
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $user = User::create($data);

        return $this->success($user, 'Registro criado com sucesso', 201);
    }

    /**
     * PUT /api/users/update/{id}
     * Atualiza um usuário
     */
    #[Auth]
    #[ApiDoc(
        summary: "Atualiza um usuário",
        description: "Atualiza todos os dados de um usuário existente",
    )]
    public function putUpdate(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('Registro não encontrado', null, 404);
        }

        $data = $this->request->all();

        // Validação
        $validation = new Validation($data);
        $validation->rule('required', ['name', 'email', 'role'])->message('O campo {field} é obrigatório');
        $validation->rule('email', 'email')->message('O campo {field} tem que ser um email');
        $validation->rule('lengthMin', 'name', 3)->message('O campo {field} tem que ter pelo menos 3 caracteres');
        $validation->rule('lengthMax', 'name', 100)->message('O campo {field} tem que ter no máximo 100 caracteres');

        $validation->labels([
            'name' => 'Nome',
            'email' => 'E-mail',
        ]);

        if (!$validation->validate()) {
            return $this->error('Dados inválidos', $validation->errors(), 422);
        }

        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $user->update($data);

        return $this->success($user, 'Registro atualizado com sucesso');
    }

    /**
     * PATCH /api/users/update/{id}
     * Atualiza parcialmente um usuário
     */
    #[Auth]
    #[ApiDoc(
        summary: "Atualiza parcialmente um usuário",
        description: "Atualiza apenas os campos enviados de um usuário",
    )]
    public function patchUpdate(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('Registro não encontrado', null, 404);
        }

        $data = $this->request->all();

        // Para PATCH, não exigimos todos os campos obrigatórios
        // TODO: Adicionar validações específicas para os campos enviados

        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $user->update($data);

        return $this->success($user, 'Registro atualizado com sucesso');
    }

    /**
     * DELETE /api/users/delete/{id}
     * Deleta um usuário
     */
    #[Auth(roles: ['admin'])]
    #[ApiDoc(
        summary: "Deleta um usuário",
        description: "Remove permanentemente um usuário (requer perfil admin)",
    )]
    public function deleteDelete(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->error('Registro não encontrado', null, 404);
        }

        $user->delete();

        return $this->success([], 'Registro deletado com sucesso');
    }

    /**
     * GET /api/users/search
     * Busca usuários com filtros
     */
    #[Auth]
    #[ApiDoc(
        summary: "Busca usuários com filtros",
        description: "Retorna uma lista paginada de usuários filtrada por parâmetros customizados",
    )]
    public function getSearch()
    {
        $query = User::query();

        // Adiciona filtros específicos
        if ($search = $this->request->input('search')) {
            $query->where('name', 'LIKE', "%{$search}%");
        }
        if ($status = $this->request->input('status')) {
            $query->where('status', $status);
        }

        $page = $this->request->input('page', 1);
        $perPage = $this->request->input('per_page', 15);

        $users = $query->paginate($perPage, ['*'], 'page', $page);

        return $this->success([
            'data' => $users->items(),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
            ],
        ]);
    }
}
