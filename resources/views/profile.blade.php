@extends('layouts.app')

@section('title', 'Meu Perfil')

@section('content')
<div class="page-header">
    <h1>Meu Perfil</h1>
</div>

<div class="profile-container">
    <div class="profile-card">
        <div class="profile-header">
            <div class="profile-avatar">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h2>{{ $user->name }}</h2>
            <p class="profile-email">{{ $user->email }}</p>
            <span class="badge badge-{{ $user->role === 'admin' ? 'danger' : 'info' }}">{{ ucfirst($user->role) }}</span>
        </div>

        <div class="profile-body">
            <div class="info-group">
                <label>ID</label>
                <span>{{ $user->id }}</span>
            </div>

            <div class="info-group">
                <label>Membro desde</label>
                <span>{{ $user->created_at?->format('d/m/Y') }}</span>
            </div>

            @if($user->updated_at)
            <div class="info-group">
                <label>Última atualização</label>
                <span>{{ $user->updated_at->format('d/m/Y H:i') }}</span>
            </div>
            @endif
        </div>

        <div class="profile-actions">
            <a href="/users/edit/{{ $user->id }}" class="btn btn-primary">Editar Perfil</a>
            <a href="/home/dashboard" class="btn btn-secondary">Voltar</a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.profile-container {
    max-width: 600px;
    margin: 2rem auto;
}

.profile-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.profile-header {
    text-align: center;
    padding-bottom: 2rem;
    border-bottom: 2px solid #ecf0f1;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-size: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-weight: bold;
}

.profile-header h2 {
    margin: 0.5rem 0;
    color: #2c3e50;
}

.profile-email {
    color: #7f8c8d;
    margin: 0.5rem 0 1rem;
}

.profile-body {
    padding: 2rem 0;
}

.info-group {
    display: flex;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 1px solid #ecf0f1;
}

.info-group label {
    font-weight: 600;
    color: #2c3e50;
}

.info-group span {
    color: #7f8c8d;
}

.profile-actions {
    display: flex;
    gap: 1rem;
    justify-content: center;
    padding-top: 2rem;
}
</style>
@endpush
