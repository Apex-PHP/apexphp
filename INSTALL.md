# 🚀 Guia de Instalação Rápida

## Passo 1: Instalar Dependências

```bash
cd apexphp
composer install
```

## Passo 2: Configurar Ambiente

```bash
# Copiar arquivo de ambiente
cp .env.example .env

# Editar .env com suas configurações
# Principalmente: DB_DATABASE, DB_USERNAME, DB_PASSWORD
nano .env
```

## Passo 3: Criar Banco de Dados

```sql
CREATE DATABASE apexphp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Ou via linha de comando:
```bash
mysql -u root -p -e "CREATE DATABASE apexphp CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

## Passo 4: Executar Migrations

```bash
php vendor/bin/phinx migrate
```

## Passo 5: Executar Seeders (Opcional)

```bash
php vendor/bin/phinx seed:run
```

Isso criará usuários de teste:
- Admin: admin@email.com / 123456
- User: joao@email.com / 123456

## Passo 6: Iniciar Servidor

```bash
php -S localhost:8000 -t public
```

## Passo 7: Acessar

Abra seu navegador em: http://localhost:8000

---

## Comandos Úteis

```bash
# Ver status das migrations
php vendor/bin/phinx status

# Reverter última migration
php vendor/bin/phinx rollback

# Criar nova migration
php vendor/bin/phinx create MigrationName

# Criar novo seeder
php vendor/bin/phinx seed:create SeederName
```

---

## Teste da API

```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@email.com","password":"123456"}'

# Copie o token retornado

# Testar rota protegida
curl http://localhost:8000/api/auth/me \
  -H "Authorization: Bearer SEU_TOKEN_AQUI"
```

---

## Solução de Problemas

### Erro: "Class 'Dotenv\Dotenv' not found"
```bash
composer dump-autoload
```

### Erro: "Connection refused"
Verifique se o MySQL está rodando e as credenciais no .env estão corretas.

### Erro: "Table 'users' doesn't exist"
```bash
php vendor/bin/phinx migrate
```

### Permissões de escrita
```bash
chmod -R 755 storage/
```

---

## Estrutura de Pastas Importante

- `public/` - Document root (aponte o servidor aqui)
- `app/Controllers/` - Seus controllers
- `app/Models/` - Seus models
- `resources/views/` - Seus templates Blade
- `routes/` - Definição de rotas
- `database/migrations/` - Migrations do banco

---

## Próximos Passos

1. Explore o código em `app/Controllers/UsersController.php`
2. Veja os Attributes em `app/Attributes/`
3. Teste as rotas dinâmicas
4. Crie seu próprio CRUD seguindo o exemplo

---

**Pronto! Seu framework está funcionando! 🎉**
