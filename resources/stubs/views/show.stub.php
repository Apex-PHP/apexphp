@extends('layouts.app')

@section('title', 'Detalhes - {MODEL}')

@section('content')
<div class="page-header">
    <h1>Detalhes - {MODEL}</h1>
    <div>
        @if(auth()->check())
        <a href="/{VIEW_FOLDER}/edit/{{ ${MODEL_VAR}->id }}" class="btn btn-warning">Editar</a>
        @endif
        <a href="/{VIEW_FOLDER}/list" class="btn btn-secondary">Voltar</a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        {DETAILS}
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        background: white;
        border-radius: 8px;
        padding: 2rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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