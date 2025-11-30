@extends('layouts.app')

@section('title', 'Sobre')

@section('content')

    <div class="hero">
        <h1>Bem-vindo ao {{ config('app.name') }}</h1>
        <p>Documentação interativa da API RESTful</p>

        <div class="hero-buttons">
            <a href="/docs/json" target="_blank" class="btn btn-secondary">📄 Download JSON</a>
            <a href="/docs/yaml" target="_blank" class="btn btn-secondary">📄 Download YAML</a>
            <a href="https://github.com/Apex-PHP/apexphp" target="_blank" class="btn btn-secondary">📚 Documentação</a>
        </div>
    </div>

    <div id="swagger-ui"></div>

    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "/docs/json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "BaseLayout",
                defaultModelsExpandDepth: 1,
                defaultModelExpandDepth: 1,
                docExpansion: "list",
                filter: true,
                showRequestHeaders: true,
                persistAuthorization: true,
                tryItOutEnabled: true,
                requestInterceptor: (request) => {
                    // Interceptar requisições para adicionar header customizado se necessário
                    console.log('Request:', request);
                    return request;
                },
                responseInterceptor: (response) => {
                    // Interceptar respostas
                    console.log('Response:', response);
                    return response;
                },
                onComplete: () => {
                    console.log('Swagger UI carregado com sucesso!');
                },
                oauth2RedirectUrl: window.location.origin + '/docs/oauth2-redirect.html',
            });

            window.ui = ui;
        }
    </script>

@endsection

@push('styles')
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/swagger-ui-dist@5/swagger-ui.css">
<style>
.hero .btn {
    margin: 0 15px;
}
.swagger-ui .info {
    margin: 50px 0;
}
.swagger-ui .info .title {
    font-size: 36px;
    color: #3b4151;
}
.custom-links {
    background: #f7f7f7;
    padding: 15px;
    text-align: center;
    border-bottom: 1px solid #e0e0e0;
}
.custom-links a {
    margin: 0 15px;
    color: #667eea;
    text-decoration: none;
    font-weight: 500;
}
.custom-links a:hover {
    text-decoration: underline;
}
</style>
@endpush