<?php

namespace App\Controllers\Api;

use App\Models\{MODEL};
use App\Controllers\BaseController;

use Framework\Auth\JwtAuth;
use Framework\Validation\Validation;

use App\Attributes\PublicRoute;
use App\Attributes\Auth;
use App\Attributes\ApiDoc;

class {CONTROLLER} extends BaseController
{
    private JwtAuth $jwtAuth;

    public function __construct()
    {
        parent::__construct();
        $this->jwtAuth = app('jwt');
    }

    /**
     * GET /api/{TABLE}
     * Lista todos os registros
     */
    #[PublicRoute]
    #[ApiDoc(
        summary: "Lista todos os {TABLE}",
        description: "Retorna uma lista paginada de {MODEL}",
        tags: ["{MODEL}"],
        responses: [200]
    )]
    public function getIndex()
    {
        $page = $this->request->input('page', 1);
        $perPage = $this->request->input('per_page', config('app.per_page'));

        ${TABLE} = {MODEL}::paginate($perPage, ['*'], 'page', $page);

        return $this->success([
            'data' => ${TABLE}->items(),
            'pagination' => [
                'total' => ${TABLE}->total(),
                'per_page' => ${TABLE}->perPage(),
                'current_page' => ${TABLE}->currentPage(),
                'last_page' => ${TABLE}->lastPage(),
                'from' => ${TABLE}->firstItem(),
                'to' => ${TABLE}->lastItem(),
            ],
        ]);
    }

    /**
     * GET /api/{TABLE}/show/{id}
     * Exibe um registro específico
     */
    #[PublicRoute]
    #[ApiDoc(
        summary: "Exibe um item pelo ID",
        description: "Retorna os detalhes de um item específico",
        tags: ["{MODEL}"],
        responses: [200, 404]
    )]
    public function getShow(int $id)
    {
        ${MODEL_VAR} = {MODEL}::find($id);

        if (!${MODEL_VAR}) {
            return $this->error('Registro não encontrado', null, 404);
        }

        return $this->success(${MODEL_VAR});
    }

    /**
     * POST /api/{TABLE}/create
     * Cria um novo registro
     */
    #[Auth]
    #[ApiDoc(
        summary: "Cria um novo item",
        description: "Cria um novo registro no banco de dados",
        tags: ["{MODEL}"],
        responses: [201, 401, 422]
    )]
    public function postCreate()
    {
        $data = $this->request->all();

        // Validação
        $validation = new Validation($data);
        $validation->rule('required', {FILLABLE})->message('O campo {field} é obrigatório');

        // TODO: Adicionar validações específicas
        // $validation->rule('email', 'email_field')->message('O campo {field} tem que ser um email');
        // $validation->rule('lengthMin', 'name', 3)->message('O campo {field} tem que ter pelo menos 3 caracteres');
        // $validation->rule('lengthMax', 'name', 100)->message('O campo {field} tem que ter no máximo 100 caracteres');

        // $validation->labels({FILLABLE_LABELS});

        if (!$validation->validate()) {
            return $this->error('Dados inválidos', $validation->errors(), 422);
        }

        ${MODEL_VAR} = {MODEL}::create($data);

        return $this->success(${MODEL_VAR}, 'Registro criado com sucesso', 201);
    }

    /**
     * PUT /api/{TABLE}/update/{id}
     * Atualiza um registro
     */
    #[Auth]
    #[ApiDoc(
        summary: "Atualiza um item",
        description: "Atualiza todos os dados de um item existente",
        tags: ["{MODEL}"],
        responses: [200, 401, 404, 422]
    )]
    public function putUpdate(int $id)
    {
        ${MODEL_VAR} = {MODEL}::find($id);

        if (!${MODEL_VAR}) {
            return $this->error('Registro não encontrado', null, 404);
        }

        $data = $this->request->all();

        // Validação
        $validation = new Validation($data);
        $validation->rule('required', {FILLABLE})->message('O campo {field} é obrigatório');

        // TODO: Adicionar validações específicas
        // $validation->rule('email', 'email_field')->message('O campo {field} tem que ser um email');
        // $validation->rule('lengthMin', 'name', 3)->message('O campo {field} tem que ter pelo menos 3 caracteres');
        // $validation->rule('lengthMax', 'name', 100)->message('O campo {field} tem que ter no máximo 100 caracteres');

        // $validation->labels({FILLABLE_LABELS});

        if (!$validation->validate()) {
            return $this->error('Dados inválidos', $validation->errors(), 422);
        }

        ${MODEL_VAR}->update($data);

        return $this->success(${MODEL_VAR}, 'Registro atualizado com sucesso');
    }

    /**
     * PATCH /api/{TABLE}/update/{id}
     * Atualiza parcialmente um registro
     */
    #[Auth]
    #[ApiDoc(
        summary: "Atualiza parcialmente um item",
        description: "Atualiza apenas os campos enviados de um item existente",
        tags: ["{MODEL}"],
        responses: [200, 401, 404]
    )]
    public function patchUpdate(int $id)
    {
        ${MODEL_VAR} = {MODEL}::find($id);

        if (!${MODEL_VAR}) {
            return $this->error('Registro não encontrado', null, 404);
        }

        $data = $this->request->all();

        // Validação
        // Para PATCH, não exigimos todos os campos obrigatórios
        $validation = new Validation($data);
        $validation->rule('required', {FILLABLE})->message('O campo {field} é obrigatório');

        // TODO: Adicionar validações específicas
        // $validation->rule('email', 'email_field')->message('O campo {field} tem que ser um email');
        // $validation->rule('lengthMin', 'name', 3)->message('O campo {field} tem que ter pelo menos 3 caracteres');
        // $validation->rule('lengthMax', 'name', 100)->message('O campo {field} tem que ter no máximo 100 caracteres');

        // $validation->labels({FILLABLE_LABELS});

        ${MODEL_VAR}->update($data);

        return $this->success(${MODEL_VAR}, 'Registro atualizado com sucesso');
    }

    /**
     * DELETE /api/{TABLE}/delete/{id}
     * Deleta um registro
     */
    #[Auth(roles: ['admin'])]
    #[ApiDoc(
        summary: "Deleta um item",
        description: "Remove permanentemente um item (requer role admin)",
        tags: ["{MODEL}"],
        responses: [200, 401, 403, 404]
    )]
    public function deleteDelete(int $id)
    {
        ${MODEL_VAR} = {MODEL}::find($id);

        if (!${MODEL_VAR}) {
            return $this->error('Registro não encontrado', null, 404);
        }

        ${MODEL_VAR}->delete();

        return $this->success([], 'Registro deletado com sucesso');
    }

    /**
     * GET /api/{TABLE}/search
     * Busca registros com filtros
     */
    #[PublicRoute]
    #[ApiDoc(
        summary: "Busca {MODEL} com filtros",
        description: "Retorna uma lista paginada de {MODEL} filtrada por parâmetros customizados",
        tags: ["{MODEL}"],
        responses: [200]
    )]
    public function getSearch()
    {
        $query = {MODEL}::query();

        // TODO: Adicionar filtros específicos
        // Exemplo:
        // if ($search = $this->request->input('search')) {
        //     $query->where('name', 'LIKE', "%{$search}%");
        // }
        // if ($status = $this->request->input('status')) {
        //     $query->where('status', $status);
        // }

        $page = $this->request->input('page', 1);
        $perPage = $this->request->input('per_page', config('app.per_page'));

        ${TABLE} = $query->paginate($perPage, ['*'], 'page', $page);

        return $this->success([
            'data' => ${TABLE}->items(),
            'pagination' => [
                'total' => ${TABLE}->total(),
                'per_page' => ${TABLE}->perPage(),
                'current_page' => ${TABLE}->currentPage(),
                'last_page' => ${TABLE}->lastPage(),
            ],
        ]);
    }
}
