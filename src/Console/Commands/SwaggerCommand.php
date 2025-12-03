<?php

namespace Framework\Console\Commands;

use Framework\Console\Commands;
use Framework\Swagger\SwaggerGenerator;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Classe destinada a geração de documentação da API
 * 
 * @package Framework\Console\Commands
 */
class SwaggerCommand extends Commands
{
    protected static $defaultName = 'make:swagger';
    public $description = 'Gera a documentação da API';
    public $help = 'Gera a documentação da API para melhor entendimento do usuário';

    protected function config(): void
    {
        $this->setOption('format', null, 'optional', 'Formato da documentação (json, yaml)', 'json');
        $this->setOption('output', null, 'optional', 'Arquivo de saída');
    }

    protected function handle(): int
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

        $format = $this->option('format');
        $output = $this->option('output');

        $this->output("🚀 Gerando Swagger...");
        $this->output("   Format: {$format}");
        $this->output("   Output: {$output}");

        try {
            $this->output("🚀 Gerando documentação Swagger/OpenAPI...");

            // Criar gerador
            $generator = new SwaggerGenerator('/api');

            // Gerar documentação
            $this->output("📝 Analisando controllers API...");
            $spec = $generator->generate();

            $this->output("✓ Encontrados " . count($spec['paths']) . " endpoints");
            $this->output("✓ Encontrados " . count($spec['tags']) . " recursos");
            $this->output("✓ Gerados " . count($spec['components']['schemas']) . " schemas");

            // Salvar arquivos
            $publicDir = __DIR__ . '/../../../public/_docs';
            if (!is_dir($publicDir)) {
                mkdir($publicDir, 0755, true);
            }

            // JSON (sempre gerar)
            $jsonPath = $output ?? $publicDir . '/swagger.json';
            $generator->saveToFile($jsonPath);

            $this->output("✓ Arquivo JSON salvo: {$jsonPath}");

            // YAML (opcional)
            if ($format === 'yaml' || $format === 'both') {
                $yamlPath = str_replace('.json', '.yaml', $jsonPath);
                $generator->saveToYaml($yamlPath);

                $this->output("✓ Arquivo YAML salvo: {$yamlPath}");
            }

            $this->output("✅ Documentação gerada com sucesso!");
            $this->output("📖 Próximos passos:");
            $this->output("   1. Acesse http://localhost:8000/docs para ver a documentação");
            $this->output("   2. O arquivo está disponível em: {$jsonPath}");
            $this->output("   3. Importe no Postman/Insomnia para testar");

        } catch (\Exception $e) {
            $this->error("❌ Erro ao gerar documentação: " . $e->getMessage());
            $this->error("Stack trace:\n" . $e->getTraceAsString());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}