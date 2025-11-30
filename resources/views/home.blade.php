@extends('layouts.app')

@section('title', 'Home')

@section('content')
<div class="hero">
    <h1>Bem-vindo ao {{ config('app.name') }}</h1>
    <p>Framework MVC profissional com SlimPHP e PHP 8 Attributes</p>

    @if(auth()->check())
        <p class="welcome-user">Olá, <strong>{{ auth()->user()->name }}</strong>!</p>
        <div class="hero-buttons">
            <a href="/home/dashboard" class="btn btn-primary">Ir para o Dashboard</a>
            <a href="/users/list" class="btn btn-secondary">Ver Usuários</a>
        </div>
    @else
        <div class="hero-buttons">
            <a href="/auth/register" class="btn btn-primary">Começar</a>
            <a href="/auth/login" class="btn btn-secondary">Fazer Login</a>
        </div>
    @endif

</div>

<div class="features">
    <h2>Recursos do Framework</h2>
    <div class="features-grid">
        <div class="feature-card">
            <h3>🎯 Rotas Dinâmicas</h3>
            <p>Sistema inteligente de rotas baseado em convenções HTTP</p>
            <code>GET /users/list → getList()</code>
        </div>

        <div class="feature-card">
            <h3>🔒 PHP Attributes</h3>
            <p>Controle de autenticação via Attributes do PHP 8+</p>
            <code>#[Auth], #[Guest], #[PublicRoute]</code>
        </div>

        <div class="feature-card">
            <h3>🎨 Blade Templates</h3>
            <p>Engine de templates rápida e elegante</p>
            <code>&commat;auth, &commat;guest, &commat;csrf</code>
        </div>

        <div class="feature-card">
            <h3>💾 Eloquent ORM</h3>
            <p>Manipulação de dados intuitiva e poderosa</p>
            <code>User::paginate(10)</code>
        </div>

        <div class="feature-card">
            <h3>🔐 Autenticação Dupla</h3>
            <p>Session para Web e JWT para APIs</p>
            <code>Auth::attempt() / JwtAuth::attempt()</code>
        </div>

        <div class="feature-card">
            <h3>🚀 Produtividade</h3>
            <p>Controllers, Models e Views automáticos</p>
            <code>php console make:crud --table=posts</code>
        </div>
    </div>
</div>

@endsection
