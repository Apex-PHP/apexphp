@extends('layouts.app')

@section('title', 'Novo {MODEL}')

@section('content')
<div class="page-header">
    <h1>Novo {MODEL}</h1>
</div>

<div class="card">
    <form method="POST" action="/{VIEW_FOLDER}/create">
        @csrf

        {FORM_FIELDS}
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Salvar</button>
            <a href="/{VIEW_FOLDER}/list" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection