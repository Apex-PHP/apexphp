<nav class="navbar">
    <div class="container nav-container">
        <a href="/" class="navbar-brand"><img src="/images/logo_branco.svg" alt="ApexPHP Framework" style="height: 50px; width: auto;"</a>

        <ul class="navbar-nav">
            <li><a href="/home/index">Home</a></li>
@if (file_exists(public_path('_docs/swagger.json')))
            <li><a href="/docs">API RESTful</a></li>
@endif
            <li><a href="/users/list">Usuários</a></li>
            <li><a href="/home/about">Sobre</a></li>
@if(auth()->check())
            <li><a href="/home/dashboard">Dashboard</a></li>
            <li><a href="/home/profile">Perfil</a></li>
            <li class="user-info">{{ auth()->user()->name }}</li>
            <li><a href="/auth/logout" class="btn-logout">Sair</a></li>
@else
            <li><a href="/auth/login" class="btn btn-primary">Login</a></li>
            <li><a href="/auth/register" class="btn btn-secondary">Cadastrar</a></li>
@endif
        </ul>
    </div>
</nav>
