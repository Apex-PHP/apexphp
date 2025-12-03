<?php

namespace Framework\Console\Commands;

use Framework\Console\Commands;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Classe destinada a testar a conexão com o banco de dados
 * 
 * @package Framework\Console\Commands
 */
class DataBaseTestCommand extends Commands
{
    protected static $defaultName = 'db:test';
    public $description = 'Teste de conexão com banco de dados';
    public $help = 'Teste de conexão com banco de dados';

    protected function config(): void
    {

    }

    protected function handle()
    {
        // Configurar banco de dados
        $capsule = new Capsule();
        $capsule->addConnection([
            "driver" => env("DB_DRIVER", "mysql"),
            "host" => env("DB_HOST", "localhost"),
            "port" => env("DB_PORT", "3306"),
            "database" => env("DB_DATABASE"),
            "username" => env("DB_USERNAME"),
            "password" => env("DB_PASSWORD"),
            "charset" => env("DB_CHARSET", "utf8mb4"),
            "collation" => env("DB_COLLATION", "utf8mb4_unicode_ci"),
            "prefix" => env("DB_PREFIX", ""),
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        try {
            Capsule::select("SELECT 1");

            $this->info("✓ Conexão com banco OK");
        } catch (\Exception $e) {
            $this->error("  Falha na conexão com o banco de dados.  ");
            $this->output("  💡 Verifique o usuário e senha no arquivo .env");

            return self::FAILURE;
        }

        try {
            $tables = Capsule::select("SHOW TABLES");

            if (empty($tables)) {
                $this->output("⚠️  Nenhuma tabela encontrada\n");
                $this->output("💡 Execute as migrations: php vendor/bin/phinx migrate\n\n");
            } else {
                $this->info("✓ Tabelas encontradas:\n");
                foreach ($tables as $table) {
                    $tableName = array_values((array) $table)[0];
                    $this->info("   - {$tableName}");
                }
                $this->info("\n");
            }
        } catch (\Throwable $th) {
            $this->error("❌ Erro ao listar tabelas: " . $th->getMessage());

            return self::FAILURE;
        }

        $this->info("✅ Teste de conexão concluído com sucesso!");

        return self::SUCCESS;
    }
}