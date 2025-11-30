# ApexPHP Framework

> Framework MVC profissional em PHP com rotas dinâmicas e autenticação via PHP 8+ Attributes

## Características

- ✅ **PHP 8+ Attributes** - Controle de autenticação via `#[Auth]`, `#[Guest]`, `#[PublicRoute]`
- ✅ **Rotas Dinâmicas** - `GET /users/list` → `UsersController::getList()`
- ✅ **Blade Templates** - Engine rápida e elegante
- ✅ **Eloquent ORM** - Manipulação de dados intuitiva
- ✅ **Autenticação Dupla** - Session (Web) e JWT (API)
- ✅ **Middleware** - Pipeline de processamento
- ✅ **Validação** - Valitron (simples e eficiente)
- ✅ **Migrations** - Phinx para controle do banco
- ✅ **Gerador de CRUD + API REST** - Crie Models, Controllers (Web + API) e Views automaticamente
- ✅ **Swagger/OpenAPI** - Documentação interativa automática da API
- ✅ **PSR Compliant** - Seguindo padrões da comunidade

## Instalação

Abaixo você terá os principais comandos para executar o framework.

Para maiores detalhes consulto nosso [guia de instalação](INSTALL.md).

```bash
# Instalar dependências
composer install

# Configurar ambiente
cp .env.example .env
# Edite o .env com suas configurações de banco

# Executar migrations
php vendor/bin/phinx migrate

# Executar seeders (opcional)
php vendor/bin/phinx seed:run

# Iniciar servidor
php -S localhost:8000 -t public
```

Acesse: http://localhost:8000

## Usuários de Teste

Após executar os seeders:

- **Admin**: admin@email.com / 123456
- **Usuário**: joao@email.com / 123456

## 🚀 Gerador de CRUD + API REST

Crie automaticamente Model, Controllers (Web + API) e Views a partir de uma tabela existente:

```bash
# Gerar CRUD completo + API REST
php apexphp make:crud --table=posts

# ou usando nome do model
php apexphp make:crud --model=Post

# Gerar apenas componentes específicos
php apexphp make:crud --table=products --only=model,api      # Apenas Model e API
php apexphp make:crud --table=products --except=views        # Tudo exceto Views
```

O gerador cria:

- ✅ **Model** com fillable e timestamps
- ✅ **Controller Web** com 7 métodos CRUD e Attributes
- ✅ **Controller API REST** com endpoints completos e autenticação JWT
- ✅ **4 Views** Blade (list, show, create, edit)
- ✅ **Formulários** automáticos baseados nos tipos dos campos
- ✅ **Paginação** configurada
- ✅ **Controle de acesso** com #[Auth] e #[PublicRoute]
- ✅ **Validação automática** e respostas JSON padronizadas
- ✅ **Geração Seletiva** com flags `--only` e `--except`

### Exemplo Completo

```bash
# 1. Criar migration
php vendor/bin/phinx create CreateProductsTable

# 2. Editar migration e executar
php vendor/bin/phinx migrate

# 3. Gerar CRUD
php apexphp make:crud --table=products

# 4. Gerar documentação Swagger
php apexphp make:swagger

# 5. Acessar web, API e documentação
http://localhost:8000/products/list       # Interface web
http://localhost:8000/api/products        # API REST
http://localhost:8000/docs                # Swagger UI
```

📖 Veja mais detalhes em nossa [documentação completa no repositório](https://github.com/Apex-PHP/apexphp-docs).

## Estrutura do Projeto

```
apexphp/
├── app/
│   ├── Attributes/        # Attributes personalizados
│   ├── Controllers/       # Controllers MVC
│   ├── Models/           # Models Eloquent
│   ├── Middleware/       # Middlewares
│   └── Helpers/          # Funções auxiliares
├── config/               # Configurações
├── database/
│   ├── migrations/       # Migrations
│   └── seeds/           # Seeders
├── public/              # Document root
│   ├── index.php        # Entry point
│   ├── css/
│   ├── images/
│   └── js/
├── resources/views/     # Templates Blade
├── routes/              # Rotas
│   ├── web.php
│   └── api.php
└── src/                 # Core do framework
    ├── Auth/
    ├── Core/
    ├── Database/
    ├── Modules/
    ├── Session/
    ├── Swagger/
    └── Validation/
```

## Exemplos de Uso

### Controller com Attributes

```php
<?php

namespace App\Controllers;

use App\Attributes\Auth;
use App\Attributes\PublicRoute;
use App\Attributes\Guest;

class UsersController extends BaseController
{
    // Rota pública
    #[PublicRoute]
    public function getList()
    {
        $users = User::paginate(10);
        return $this->render('users.list', compact('users'));
    }

    // Requer autenticação
    #[Auth]
    public function postCreate()
    {
        $user = User::create($this->request->all());
        return $this->redirect('/users/show/' . $user->id);
    }

    // Apenas administradores
    #[Auth(roles: ['admin'])]
    public function deleteDelete(int $id)
    {
        User::findOrFail($id)->delete();
        return $this->redirect('/users/list');
    }

    // Apenas visitantes (não autenticados)
    #[Guest(redirectTo: '/dashboard')]
    public function getLogin()
    {
        return $this->render('auth.login');
    }
}
```

### Rotas Dinâmicas

O framework mapeia automaticamente URLs para métodos:

| URL                      | Método                               |
| ------------------------ | ------------------------------------ |
| `GET /users/list`        | `UsersController::getList()`         |
| `POST /users/create`     | `UsersController::postCreate()`      |
| `PUT /users/update/5`    | `UsersController::putUpdate($id)`    |
| `PATCH /users/edit/5`    | `UsersController::patchEdit($id)`    |
| `DELETE /users/delete/5` | `UsersController::deleteDelete($id)` |

### API com JWT

```php
// Login
$token = $this->jwtAuth->attempt($email, $password);

// No cliente, enviar token:
// Authorization: Bearer {token}

// Controller de API
namespace App\Controllers\Api;

class UsersController extends BaseController
{
    #[PublicRoute]
    public function postLogin()
    {
        $token = $this->jwtAuth->attempt(
            $this->request->input('email'),
            $this->request->input('password')
        );

        return $this->success(['token' => $token]);
    }

    #[Auth]
    public function getMe()
    {
        $user = $this->jwtAuth->user();
        return $this->success($user);
    }
}
```

### Views Blade

```blade
@extends('layouts.app')

@section('content')
    <h1>Usuários</h1>

    @auth
        <p>Olá, {{ auth()->user()->name }}!</p>
    @endauth

    @foreach($users as $user)
        <div>{{ $user->name }}</div>
    @endforeach

    {{ $users->links() }}
@endsection
```

## Testes de API

```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@email.com","password":"123456"}'

# Resposta:
# {
#   "success": true,
#   "data": {
#     "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
#     "token_type": "Bearer",
#     "expires_in": 3600
#   }
# }

# Usar token
curl http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer SEU_TOKEN"
```

## Comandos Úteis

```bash
# Servidor de desenvolvimento
php -S localhost:8000 -t public

# Migrations
php vendor/bin/phinx migrate
php vendor/bin/phinx rollback
php vendor/bin/phinx seed:run

# Criar migration
php vendor/bin/phinx create CreatePostsTable
```

## PHP Attributes

### #[PublicRoute]

Marca um método como acessível publicamente (sem autenticação)

### #[Auth]

Requer autenticação. Pode especificar roles:

```php
#[Auth(roles: ['admin', 'editor'])]
```

### #[Guest]

Apenas para visitantes não autenticados. Redireciona se já estiver logado:

```php
#[Guest(redirectTo: '/dashboard')]
```

## Segurança

- ✅ Password hashing com bcrypt
- ✅ Proteção CSRF em formulários
- ✅ SQL Injection protection (Eloquent)
- ✅ XSS protection (Blade)
- ✅ Session regeneration
- ✅ JWT com expiração
- ✅ CORS configurável

## Requisitos

- PHP 8.1+
- MySQL 5.7+ ou PostgreSQL
- Composer
- Apache ou Nginx

## Licença

MIT

## Autor

Desenvolvido com foco em produtividade, segurança e boas práticas.
