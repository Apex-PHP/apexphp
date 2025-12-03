@extends('layouts.app')

@section('title', '{MODEL}')

@section('content')
<div class="page-header">
    <h1>{MODEL}</h1>
    @if(auth()->check())
    <a href="/{VIEW_FOLDER}/create" class="btn btn-primary">Novo Registro</a>
    @endif
</div>

<table class="table">
    <thead>
        <tr>
            {TABLE_HEADERS}
        </tr>
    </thead>
    <tbody>
        @foreach(${VIEW_FOLDER} as ${MODEL_VAR})
        <tr>
            {TABLE_ROWS}

            <td>
                <a href="/{VIEW_FOLDER}/show/{{ ${MODEL_VAR}->id }}" class="btn btn-sm btn-info">Ver</a>
                @if(auth()->check())
                <a href="/{VIEW_FOLDER}/edit/{{ ${MODEL_VAR}->id }}" class="btn btn-sm btn-warning">Editar</a>
                @if(auth()->hasRole('admin'))
                <form method="POST" action="/{VIEW_FOLDER}/delete/{{ ${MODEL_VAR}->id }}" style="display:inline;">
                    @csrf
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-sm btn-danger"
                        onclick="return confirm('Confirma exclusão?')">Deletar</button>
                </form>
                @endif
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="pagination">
    @if(${VIEW_FOLDER}->currentPage() > 1)
    <a href="?page={{ ${VIEW_FOLDER}->currentPage() - 1 }}" class="btn">Anterior</a>
    @endif
    <span>Página {{ ${VIEW_FOLDER}->currentPage() }} de {{ ${VIEW_FOLDER}->lastPage() }}</span>
    @if(${VIEW_FOLDER}->hasMorePages())
    <a href="?page={{ ${VIEW_FOLDER}->currentPage() + 1 }}" class="btn">Próxima</a>
    @endif
</div>
@endsection