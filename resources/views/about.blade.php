@extends('layouts.app')

@section('title', 'Sobre')

@section('content')
<div class="about-container">
    <h1>Sobre o ApexPHP Framework</h1>

    <div class="about-section">
        <h2>O que é?</h2>
        <p>
            O ApexPHP Framework é um framework PHP moderno e profissional construído sobre o SlimPHP,
            com foco em produtividade e boas práticas de desenvolvimento.
        </p>
    </div>

    <div class="about-section">
        <h2>Principais Características</h2>
        <ul class="features-list">
            <li><strong>PHP 8+ Attributes:</strong> Controle de autenticação via Attributes (#[Auth], #[Guest], #[PublicRoute])</li>
            <li><strong>Rotas Dinâmicas:</strong> Mapeamento automático de URLs para métodos de controllers</li>
            <li><strong>Blade Templates:</strong> Engine de templates rápida e elegante</li>
            <li><strong>Eloquent ORM:</strong> Manipulação de banco de dados intuitiva</li>
            <li><strong>Autenticação Dupla:</strong> Session para Web e JWT para APIs</li>
            <li><strong>Middleware:</strong> Pipeline de processamento de requisições</li>
            <li><strong>Validação:</strong> Sistema robusto com Respect\Validation</li>
            <li><strong>Migrations:</strong> Controle de versão do banco de dados</li>
            <li><strong>Segurança:</strong> CSRF, CORS, SQL Injection protection</li>
        </ul>
    </div>

    <div class="about-section">
        <h2>Tecnologias Utilizadas</h2>
        <div class="tech-grid">
            <div class="tech-card">
                <h3>SlimPHP 4</h3>
                <p>Micro-framework PHP rápido e minimalista</p>
            </div>
            <div class="tech-card">
                <h3>Eloquent ORM</h3>
                <p>ORM poderoso do Laravel</p>
            </div>
            <div class="tech-card">
                <h3>BladeOne</h3>
                <p>Engine de templates standalone</p>
            </div>
            <div class="tech-card">
                <h3>Phinx</h3>
                <p>Gerenciamento de migrations</p>
            </div>
            <div class="tech-card">
                <h3>JWT</h3>
                <p>Autenticação stateless para APIs</p>
            </div>
            <div class="tech-card">
                <h3>Respect\Validation</h3>
                <p>Validação de dados robusta</p>
            </div>
        </div>
    </div>

    <div class="about-section">
        <h2>Filosofia de Desenvolvimento</h2>
        <p>
            Este framework foi desenvolvido seguindo os princípios SOLID e as melhores práticas
            da comunidade PHP. Foco em:
        </p>
        <ul>
            <li>Código limpo e legível</li>
            <li>Separação de responsabilidades (MVC)</li>
            <li>Convenção sobre configuração</li>
            <li>Dependency Injection</li>
            <li>PSR Compliance</li>
            <li>Segurança por padrão</li>
        </ul>
    </div>

    <div class="about-section cta">
        <h2>Comece Agora</h2>
        <p>Explore o código, crie seus próprios controllers e construa aplicações incríveis!</p>
        <div class="cta-buttons">
            <a href="/users/list" class="btn btn-primary">Ver Exemplo</a>
            <a href="https://github.com/Apex-PHP/apexphp" class="btn btn-secondary" target="_blank">Ver no GitHub</a>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.about-container {
    max-width: 900px;
    margin: 0 auto;
    padding: 2rem 0;
}

.about-container h1 {
    text-align: center;
    margin-bottom: 3rem;
    color: #2c3e50;
    font-size: 2.5rem;
}

.about-section {
    background: white;
    padding: 2rem;
    margin-bottom: 2rem;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.about-section h2 {
    color: #3498db;
    margin-bottom: 1.5rem;
}

.about-section p {
    line-height: 1.8;
    color: #555;
}

.features-list {
    list-style: none;
    padding: 0;
}

.features-list li {
    padding: 0.75rem 0;
    padding-left: 2rem;
    position: relative;
    color: #555;
}

.features-list li:before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #27ae60;
    font-weight: bold;
    font-size: 1.2rem;
}

.tech-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.tech-card {
    background: #f8f9fa;
    padding: 1.5rem;
    border-radius: 8px;
    border-left: 4px solid #3498db;
}

.tech-card h3 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
}

.tech-card p {
    color: #7f8c8d;
    font-size: 0.9rem;
    margin: 0;
}

.cta {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    text-align: center;
}

.cta h2,
.cta p {
    color: white;
}

.cta-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    margin-top: 2rem;
}

.cta .btn {
    background: white;
    color: #667eea;
}

.cta .btn:hover {
    background: #f8f9fa;
}
</style>
@endpush
