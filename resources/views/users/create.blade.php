@extends('layouts.app')

@section('title', 'Novo Usuário')

@section('content')
<div class="page-header">
    <h1>Novo Usuário</h1>
</div>

<div class="form-container">
    <form method="POST" action="/users/create">
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
            <label for="role">Perfil</label>
            <select name="role" id="role" class="form-control">
                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>Usuário</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrador</option>
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Criar Usuário</button>
            <a href="/users/list" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
.form-container {
    max-width: 600px;
    margin: 0 auto;
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
@endpush
