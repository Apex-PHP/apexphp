<?php

namespace App\Controllers;

use App\Attributes\PublicRoute;
use App\Attributes\Auth;

class HomeController extends BaseController
{
    /**
     * GET /home/index
     * Página inicial (pública)
     */
    #[PublicRoute]
    public function getIndex()
    {
        return $this->render('home');
    }

    /**
     * GET /home/dashboard
     * Dashboard (requer autenticação)
     */
    #[Auth]
    public function getDashboard()
    {
        $user = $this->auth->user();

        return $this->render('dashboard', [
            'user' => $user,
        ]);
    }

    /**
     * GET /home/profile
     * Perfil do usuário (requer autenticação)
     */
    #[Auth]
    public function getProfile()
    {
        $user = $this->auth->user();

        return $this->render('profile', [
            'user' => $user,
        ]);
    }

    /**
     * GET /home/about
     * Página sobre (pública)
     */
    #[PublicRoute]
    public function getAbout()
    {
        return $this->render('about');
    }

    /**
     * GET /home/test-flash
     * Teste de flash messages
     */
    #[PublicRoute]
    public function getTestFlash()
    {
        $this->session->setFlash('success', 'Mensagem de sucesso teste!');
        $this->session->setFlash('error', 'Mensagem de erro teste!');
        $this->session->setFlash('warning', 'Mensagem de aviso teste!');
        $this->session->setFlash('info', 'Mensagem de info teste!');

        return $this->redirect('/home/index');
    }
}
