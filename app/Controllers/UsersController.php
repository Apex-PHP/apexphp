<?php

namespace App\Controllers;

use App\Models\User;
use App\Attributes\PublicRoute;
use App\Attributes\Auth;

use Framework\Validation\Validation;

class UsersController extends BaseController
{
    /**
     * Lista todos os registros
     */
    #[PublicRoute]
    public function getList()
    {
        $page = $this->request->input('page', 1);
        $users = User::paginate(config('app.per_page'), ['*'], 'page', $page);

        return $this->render('users.list', compact('users'));
    }

    /**
     * Exibe um registro específico
     */
    #[Auth]
    public function getShow(int $id)
    {
        $user = User::findOrFail($id);
        return $this->render('users.show', compact('user'));
    }

    /**
     * Formulário de criação
     */
    #[Auth]
    public function getCreate()
    {
        return $this->render('users.create');
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
            $this->session->setFlash('error', $validation->getErrors());
            $this->session->setOldInput($data);

            return $this->redirect('/users/create');
        }

        // Verificar se email já existe
        if (User::where('email', $data['email'])->exists()) {
            $this->session->setFlash('error', 'Email já cadastrado');
            $this->session->setOldInput($data);

            return $this->redirect('/users/create');
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

        $user = User::create($data);

        $this->session->setFlash('success', 'Registro criado com sucesso!');
        return $this->redirect('/users/show/' . $user->id);
    }

    /**
     * Formulário de edição
     */
    #[Auth]
    public function getEdit(int $id)
    {
        $user = User::findOrFail($id);
        return $this->render('users.edit', compact('user'));
    }

    /**
     * Atualiza um registro
     */
    #[Auth]
    public function putUpdate(int $id)
    {
        $user = User::findOrFail($id);
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
            $this->session->setFlash('error', $validation->getErrors());
            $this->session->setOldInput($data);

            return $this->redirect('/users/edit' . $id);
        }

        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        $user->update($data);

        $this->session->setFlash('success', 'Registro atualizado com sucesso!');
        return $this->redirect('/users/show/' . $id);
    }

    /**
     * Deleta um registro
     */
    #[Auth(roles: ['admin'])]
    public function deleteDelete(int $id)
    {
        $user = User::findOrFail($id);
        //$user->delete();

        $this->session->setFlash('success', 'Registro deletado com sucesso!');
        return $this->redirect('/users/list');
    }
}
