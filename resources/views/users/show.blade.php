@extends('layouts.app')

@section('title', 'Detalhes do Usuário')

@section('content')
<div class="page-header">
    <h1>Detalhes do Usuário</h1>
    <div>
        @if(auth()->check())
            <a href="/users/edit/{{ $user->id }}" class="btn btn-warning">Editar</a>
        @endif
        <a href="/users/list" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="detail-row">
            <strong>ID:</strong>
            <span>{{ $user->id }}</span>
        </div>

        <div class="detail-row">
            <strong>Nome:</strong>
            <span>{{ $user->name }}</span>
        </div>

        <div class="detail-row">
            <strong>Email:</strong>
            <span>{{ $user->email }}</span>
        </div>

        <div class="detail-row">
            <strong>Role:</strong>
            <span class="badge badge-{{ $user->role === 'admin' ? 'danger' : 'info' }}">{{ $user->role }}</span>
        </div>

        <div class="detail-row">
            <strong>Criado em:</strong>
            <span>{{ $user->created_at?->format('d/m/Y H:i') }}</span>
        </div>

        @if($user->updated_at)
        <div class="detail-row">
            <strong>Atualizado em:</strong>
            <span>{{ $user->updated_at->format('d/m/Y H:i') }}</span>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.card {
    background: white;
    border-radius: 8px;
    padding: 2rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 1rem 0;
    border-bottom: 1px solid #ecf0f1;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-row strong {
    color: #2c3e50;
}
</style>
@endpush
