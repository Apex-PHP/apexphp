@extends('layouts.app')

@section('title', 'Login')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h2>Login</h2>

        <form method="POST" action="/auth/login">
            @csrf

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control"
                    value="{{ old('email') }}"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    required
                >
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">
                    Entrar
                </button>
            </div>

            <p class="text-center">
                Não tem conta?
                <a href="/auth/register">Cadastre-se</a>
            </p>
        </form>
    </div>

    <div class="auth-info">
        <h3>Credenciais de Teste</h3>
        <p><strong>Admin:</strong> admin@email.com / 123456</p>
        <p><strong>Usuário:</strong> joao@email.com / 123456</p>
    </div>
</div>
@endsection
