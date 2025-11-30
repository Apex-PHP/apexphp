@extends('layouts.app')

@section('title', 'Usuários')

@section('content')
<div class="page-header">
    <h1>Lista de Usuários</h1>
    @if(auth()->check())
        <a href="/users/create" class="btn btn-primary">Novo Usuário</a>
    @endif
</div>

<table class="table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Role</th>
            <th>Criado em</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
        @foreach($users as $user)
        <tr>
            <td>{{ $user->id }}</td>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td><span class="badge badge-{{ $user->role === 'admin' ? 'danger' : 'info' }}">{{ $user->role }}</span></td>
            <td>{{ $user->created_at?->format('d/m/Y H:i') }}</td>
            <td>
                <a href="/users/show/{{ $user->id }}" class="btn btn-sm btn-info">Ver</a>
                @if(auth()->check())
                    <a href="/users/edit/{{ $user->id }}" class="btn btn-sm btn-warning">Editar</a>
                    @if(auth()->hasRole('admin') && auth()->id() !== $user->id)
                        <form method="POST" action="/users/delete/{{ $user->id }}" style="display:inline;">
                            @csrf
                            <input type="hidden" name="_method" value="DELETE">
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Confirma exclusão?')">Deletar</button>
                        </form>
                    @endif
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="pagination">
    @if($users->currentPage() > 1)
        <a href="?page={{ $users->currentPage() - 1 }}" class="btn">Anterior</a>
    @endif
    <span>Página {{ $users->currentPage() }} de {{ $users->lastPage() }}</span>
    @if($users->hasMorePages())
        <a href="?page={{ $users->currentPage() + 1 }}" class="btn">Próxima</a>
    @endif
</div>
@endsection
