@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="dashboard">
    <h1>Dashboard</h1>
    <p>Bem-vindo, <strong>{{ $user->name }}</strong>!</p>
    
    <div class="dashboard-info">
        <p><strong>Email:</strong> {{ $user->email }}</p>
        <p><strong>Role:</strong> <span class="badge badge-{{ $user->role === 'admin' ? 'danger' : 'info' }}">{{ $user->role }}</span></p>
        <p><strong>Membro desde:</strong> {{ $user->created_at?->format('d/m/Y') }}</p>
    </div>

    <div class="dashboard-actions">
        <a href="/home/profile" class="btn btn-primary">Ver Perfil</a>
        <a href="/users/list" class="btn btn-secondary">Ver Usuários</a>
    </div>
</div>
@endsection
