@extends('layouts.app')

@section('title', 'Editar Usuário')

@section('content')
<div class="page-header">
    <h1>Editar Usuário</h1>
</div>

<div class="form-container">
    <form method="POST" action="/users/update/{{ $user->id }}">
        @csrf
        <input type="hidden" name="_method" value="PUT">

        <div class="form-group">
            <label for="name">Nome</label>
            <input
                type="text"
                name="name"
                id="name"
                class="form-control"
                value="{{ old('name', $user->name) }}"
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
                value="{{ old('email', $user->email) }}"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Nova Senha (deixe em branco para manter)</label>
            <input
                type="password"
                name="password"
                id="password"
                class="form-control"
            >
            <small>Mínimo 6 caracteres (opcional)</small>
        </div>

        <div class="form-group">
            <label for="role">Role</label>
            <select name="role" id="role" class="form-control">
                <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>Usuário</option>
                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Administrador</option>
            </select>
        </div>

        <div class="form-group">
            <button type="submit" class="btn btn-primary">Atualizar Usuário</button>
            <a href="/users/show/{{ $user->id }}" class="btn btn-secondary">Cancelar</a>
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
