@extends('layouts.app')

@section('title', 'Editar {MODEL}')

@section('content')
<div class="page-header">
    <h1>Editar {MODEL}</h1>
</div>

<div class="card">
    <form method="POST" action="/{VIEW_FOLDER}/update/{{ ${MODEL_VAR}->id }}">
        @csrf
        <input type="hidden" name="_method" value="PUT">

        {FORM_FIELDS}
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Atualizar</button>
            <a href="/{VIEW_FOLDER}/show/{{ ${MODEL_VAR}->id }}" class="btn btn-secondary">Cancelar</a>
        </div>
    </form>
</div>
@endsection