<?php

namespace App\Controllers;

use App\Models\User;
use App\Attributes\Guest;
use App\Attributes\Auth;
use Framework\Validation\Validation;

class AuthController extends BaseController
{
    /**
     * GET /auth/login
     * Mostra formulário de login
     */
    #[Guest]
    public function getLogin()
    {
        return $this->render('auth.login');
    }

    /**
     * POST /auth/login
     * Processa login
     */
    #[Guest]
    public function postLogin()
    {
        $data = $this->request->all();

        // Validar
        $validation = new Validation($data);
        $validation->rule('required', ['email', 'password']);
        $validation->rule('email', 'email');

        if (!$validation->validate()) {
            $this->session->setFlash('error', $validation->getErrors());
            $this->session->setOldInput($data);

            return $this->redirect('/auth/login');
        }

        // Tentar autenticar
        if (!$this->auth->attempt($data['email'], $data['password'])) {
            $this->session->setFlash('error', 'Credenciais inválidas');

            return $this->redirect('/auth/login');
        }

        $this->session->setFlash('success', 'Login realizado com sucesso!');
        return $this->redirect('/dashboard');
    }

    /**
     * GET /auth/register
     * Mostra formulário de registro
     */
    #[Guest]
    public function getRegister()
    {
        return $this->render('auth.register');
    }

    /**
     * POST /auth/register
     * Processa registro
     */
    #[Guest]
    public function postRegister()
    {
        $data = $this->request->all();

        // Validar
        $validation = new Validation($data);
        $validation->rule('required', ['name', 'email', 'password'])->message('O campo {field} é obrigatório');
        $validation->rule('email', 'email')->message('O campo {field} deve ser um email');
        $validation->rule('lengthMin', 'name', 3)->message('O campo {field} deve ter pelo menos 3 caracteres');
        $validation->rule('lengthMax', 'name', 100)->message('O campo {field} deve ter no máximo 100 caracteres');
        $validation->rule('lengthMin', 'password', 6)->message('O campo {field} deve ter pelo menos 6 caracteres');

        $validation->labels([
            'name' => 'Nome',
            'email' => 'E-mail',
            'password' => 'Senha'
        ]);

        if (!$validation->validate()) {
            $this->session->setFlash('error', $validation->getErrors());
            $this->session->setOldInput($data);

            return $this->redirect('/auth/register');
        }

        // Verificar se email já existe
        if (User::where('email', $data['email'])->exists()) {
            $this->session->setFlash('error', 'Email já cadastrado');
            $this->session->setOldInput($data);

            return $this->redirect('/auth/register');
        }

        // Criar usuário
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => 'user',
        ]);

        // Fazer login automaticamente
        $this->auth->login($user);

        $this->session->setFlash('success', 'Cadastro realizado com sucesso!');
        return $this->redirect('/dashboard');
    }

    /**
     * POST /auth/logout
     * Faz logout
     */
    #[Auth]
    public function postLogout()
    {
        $this->auth->logout();

        $this->session->setFlash('success', 'Logout realizado com sucesso!');
        return $this->redirect('/');
    }

    /**
     * GET /auth/logout
     * Faz logout (GET para facilitar link)
     */
    #[Auth]
    public function getLogout()
    {
        return $this->postLogout();
    }
}
