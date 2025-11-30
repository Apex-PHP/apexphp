@extends('layouts.app')

@section('title', 'Cadastro')

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <h2>Cadastro</h2>

        <form method="POST" action="/auth/register">
            @csrf

            <div class="form-group">
                <label for="name">Nome</label>
                <input
                    type="text"
                    name="name"
                    id="name"
                    class="form-control"
                    value="{{ old('name') }}"
                    required
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    class="form-control"
                    value="{{ old('email') }}"
                    required
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
                <small>Mínimo 6 caracteres</small>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary btn-block">
                    Cadastrar
                </button>
            </div>

            <p class="text-center">
                Já tem conta?
                <a href="/auth/login">Faça login</a>
            </p>
        </form>
    </div>
</div>
@endsection
