@extends('layouts.app')

@section('title', '404 - Não Encontrado')

@section('content')
<div class="error-page">
    <h1>404</h1>
    <h2>Página não encontrada</h2>
    <p>A página que você está procurando não existe.</p>
    <a href="/" class="btn btn-primary">Voltar para o início</a>
</div>
@endsection
