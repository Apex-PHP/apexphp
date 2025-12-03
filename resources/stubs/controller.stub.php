<?php

namespace App\Controllers;

use App\Models\{MODEL};
use App\Attributes\PublicRoute;
use App\Attributes\Auth;

use Framework\Validation\Validation;

class {CONTROLLER} extends BaseController
{
    /**
     * Lista todos os registros
     */
    #[PublicRoute]
    public function getList()
    {
        $page = $this->request->input('page', 1);
        ${TABLE} = {MODEL}::paginate(config('app.per_page'), ['*'], 'page', $page);

        return $this->render('{VIEW_FOLDER}.list', compact('{TABLE}'));
    }

    /**
     * Exibe um registro específico
     */
    #[Auth]
    public function getShow(int $id)
    {
        ${MODEL_VAR} = {MODEL}::findOrFail($id);

        return $this->render('{VIEW_FOLDER}.show', compact('{MODEL_VAR}'));
    }

    /**
     * Formulário de criação
     */
    #[Auth]
    public function getCreate()
    {
        return $this->render('{VIEW_FOLDER}.create');
    }

    /**
     * Salva um novo registro
     */
    #[Auth]
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
            $this->session->setFlash('error', $validation->getErrors());
            $this->session->setOldInput($data);

            return $this->redirect('/{VIEW_FOLDER}/create');
        }

        ${MODEL_VAR} = {MODEL}::create($data);

        $this->session->setFlash('success', 'Registro criado com sucesso!');

        return $this->redirect('/{VIEW_FOLDER}/show/' . ${MODEL_VAR}->id);
    }

    /**
     * Formulário de edição
     */
    #[Auth]
    public function getEdit(int $id)
    {
        ${MODEL_VAR} = {MODEL}::findOrFail($id);
        return $this->render('{VIEW_FOLDER}.edit', compact('{MODEL_VAR}'));
    }

    /**
     * Atualiza um registro
     */
    #[Auth]
    public function putUpdate(int $id)
    {
        ${MODEL_VAR} = {MODEL}::findOrFail($id);
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
            $this->session->setFlash('error', $validation->getErrors());
            $this->session->setOldInput($data);

            return $this->redirect('/{VIEW_FOLDER}/edit' . $id);
        }            

        ${MODEL_VAR}->update($data);

        $this->session->setFlash('success', 'Registro atualizado com sucesso!');

        return $this->redirect('/{VIEW_FOLDER}/show/' . $id);
    }

    /**
     * Deleta um registro
     */
    #[Auth(roles: ['admin'])]
    public function deleteDelete(int $id)
    {
        ${MODEL_VAR} = {MODEL}::findOrFail($id);
        ${MODEL_VAR}->delete();

        $this->session->setFlash('success', 'Registro deletado com sucesso!');
        
        return $this->redirect('/{VIEW_FOLDER}/list');
    }
}
