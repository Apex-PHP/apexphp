<?php

namespace Framework\Console\Commands;

use Framework\Console\Commands;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Str;

require_once __DIR__ . '/../helpers.php';

/**
 * Classe destinada a criaçao do CRUD de forma 100% otimizada
 * 
 * Com ela podemos criar os controllers, models, views e as rotas de forma otimizada
 */
class CrudCommand extends Commands
{
    protected static $defaultName = 'make:crud';
    public $description = 'Criação dos controllers, models, views e API de forma otimizada';
    public $help = 'Com ela podemos criar os controllers, models, views e as rotas de forma otimizada';

    protected function config(): void
    {
        $this->setOption('table', null, 'required', 'Nome da tabela a ser analisada');
        $this->setOption('only', null, 'optional', 'Componentes a serem gerados (model, controller, views, api)');
        $this->setOption('except', null, 'optional', 'Componentes a serem ignorados (model, controller, views, api)');
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

        $tableName = $this->option('table');
        $only = $this->option('only');
        $except = $this->option('except');

        try {
            Capsule::select("SELECT 1");

            $this->info("✓ Conexão com banco OK");
        } catch (\Exception $e) {
            $this->error("  Falha na conexão com o banco de dados.  ");
            $this->output("  💡 Verifique o usuário e senha no arquivo .env");

            return self::FAILURE;
        }

        // Verificar se a tabela existe
        try {
            $columns = Capsule::select("SHOW COLUMNS FROM {$tableName}");
        } catch (\Exception $e) {
            $this->error("  Erro: Tabela '{$tableName}' não encontrada no banco de dados!  ");
            $this->output("  💡 Execute as migrations primeiro");

            return self::FAILURE;
        }

        $modelName = Str::singular(ucfirst(Str::camel($tableName)));
        $controllerName = $modelName . "sController";
        $viewFolder = strtolower(Str::plural($modelName));

        // Determinar o que será gerado
        $generateAll = !isset($only) && !isset($except);
        $generateModel = $generateAll;
        $generateController = $generateAll;
        $generateViews = $generateAll;
        $generateApi = $generateAll;

        // Processar --only (gera apenas os especificados)
        if (isset($only)) {
            $only = array_map('trim', explode(',', strtolower($only)));
            $generateModel = in_array('model', $only);
            $generateController = in_array('controller', $only) || in_array('web', $only);
            $generateViews = in_array('views', $only) || in_array('view', $only);
            $generateApi = in_array('api', $only);
        }

        // Processar --except (gera tudo exceto os especificados)
        if (isset($except)) {
            $except = array_map('trim', explode(',', strtolower($except)));
            if (in_array('model', $except))
                $generateModel = false;
            if (in_array('controller', $except) || in_array('web', $except))
                $generateController = false;
            if (in_array('views', $except) || in_array('view', $except))
                $generateViews = false;
            if (in_array('api', $except))
                $generateApi = false;
        }

        $this->output("🚀 Gerando CRUD...");
        $this->output("   Tabela: {$tableName}");
        $this->output("   Model: {$modelName}");
        $this->output("   Controller: {$controllerName}");
        $this->output("   Views: resources/views/{$viewFolder}/");
        $this->output("📦 Componentes a gerar:");
        $this->output("   " . ($generateModel ? "<info>✓</info>" : "✗") . " Model");
        $this->output("   " . ($generateController ? "<info>✓</info>" : "✗") . " Controller Web");
        $this->output("   " . ($generateViews ? "<info>✓</info>" : "✗") . " Views");
        $this->output("   " . ($generateApi ? "<info>✓</info>" : "✗") . " API Controller");


        // Processar colunas
        $fields = [];
        $fillable = [];
        $timestamps = false;

        foreach ($columns as $column) {
            $fieldName = $column->Field;

            // Pular campos especiais
            if (
                in_array($fieldName, ["id", "created_at", "updated_at", "deleted_at"])
            ) {
                if (in_array($fieldName, ["created_at", "updated_at"])) {
                    $timestamps = true;
                }
                continue;
            }

            $fields[] = [
                "name" => $fieldName,
                "type" => $column->Type,
                "null" => $column->Null === "YES",
                "key" => $column->Key,
                "default" => $column->Default,
            ];

            $fillable[] = $fieldName;
        }

        // Gerar componentes conforme flags
        if ($generateModel) {
            $this->info("Criando o model {$modelName}");

            $stub = file_get_contents(__DIR__ . '/../../../resources/stubs/model.stub.php');
            $stub = str_replace('{MODEL}', $modelName, $stub);
            $stub = str_replace('{TABLE}', $tableName, $stub);
            $stub = str_replace('{FILLABLE}', "['" . implode("', '", $fillable) . "']", $stub);
            $stub = str_replace('{TIMESTAMPS}', $timestamps ? 'true' : 'false', $stub);

            file_put_contents(__DIR__ . '/../../../app/Models/' . $modelName . '.php', $stub);
        }

        if ($generateController) {
            $this->info("Criando o controller {$controllerName}");

            $stub = file_get_contents(__DIR__ . '/../../../resources/stubs/controller.stub.php');
            $stub = str_replace('{CONTROLLER}', $controllerName, $stub);
            $stub = str_replace('{MODEL}', $modelName, $stub);
            $stub = str_replace('{MODEL_VAR}', strtolower($modelName), $stub);
            $stub = str_replace('{TABLE}', $tableName, $stub);
            $stub = str_replace('{VIEW_FOLDER}', $viewFolder, $stub);
            $stub = str_replace('{FILLABLE}', "['" . implode("', '", $fillable) . "']", $stub);
            $stub = str_replace('{FILLABLE_LABELS}', "['" . implode("' => '', '", $fillable) . "' => '']", $stub);

            file_put_contents(__DIR__ . '/../../../app/Controllers/' . $controllerName . '.php', $stub);
        }

        if ($generateApi) {
            $this->info("Criando a API {$controllerName}");

            $stub = file_get_contents(__DIR__ . '/../../../resources/stubs/api.stub.php');
            $stub = str_replace('{CONTROLLER}', $controllerName, $stub);
            $stub = str_replace('{MODEL}', $modelName, $stub);
            $stub = str_replace('{MODEL_VAR}', strtolower($modelName), $stub);
            $stub = str_replace('{TABLE}', $tableName, $stub);
            $stub = str_replace('{VIEW_FOLDER}', $viewFolder, $stub);
            $stub = str_replace('{FILLABLE}', "['" . implode("', '", $fillable) . "']", $stub);
            $stub = str_replace('{FILLABLE_LABELS}', "['" . implode("' => '', '", $fillable) . "' => '']", $stub);

            file_put_contents(__DIR__ . '/../../../app/Controllers/Api/' . $controllerName . '.php', $stub);
        }

        if ($generateViews) {
            $modelVar = strtolower($modelName);
            $formFieldsHtml = "";

            // Dados para a view list
            $thHtml = "<th>ID</th>\n";
            $tdHtml = "<td>{{ \${$modelVar}->id }}</td>\n";

            // Dados para a view show
            $detailsHtml = "";

            foreach ($fields as $field) {
                $label = ucfirst(str_replace("_", " ", $field["name"]));
                $inputType = getInputType($field["type"]);
                $required = !$field["null"] ? "required" : "";
                $regex = '/\{\{\s*old\(([^,]+),[^)]+\)\s*\}\}/';

                // View form (edit e create)
                if ($inputType === "textarea") {
                    $formFieldsHtml .= "        <div class=\"form-group\">\n";
                    $formFieldsHtml .= "            <label for=\"{$field["name"]}\">{$label}</label>\n";
                    $formFieldsHtml .= "            <textarea name=\"{$field["name"]}\" id=\"{$field["name"]}\" class=\"form-control\" rows=\"4\" {$required}>{{ old('{$field["name"]}', \${$modelVar}->{$field["name"]}) }}</textarea>\n";
                    $formFieldsHtml .= "        </div>\n\n";
                } else {
                    $formFieldsHtml .= "        <div class=\"form-group\">\n";
                    $formFieldsHtml .= "            <label for=\"{$field["name"]}\">{$label}</label>\n";
                    $formFieldsHtml .= "            <input type=\"{$inputType}\" name=\"{$field["name"]}\" id=\"{$field["name"]}\" class=\"form-control\" value=\"{{ old('{$field["name"]}', \${$modelVar}->{$field["name"]}) }}\" {$required}>\n";
                    $formFieldsHtml .= "        </div>\n\n";
                }

                // View list
                $thHtml .= "            <th>{$label}</th>\n";
                $tdHtml .= "            <td>{{ \${$modelVar}->{$field["name"]} }}</td>\n";

                // View show
                $detailsHtml .= "        <div class=\"detail-row\">\n";
                $detailsHtml .= "            <strong>{$label}:</strong>\n";
                $detailsHtml .= "            <span>{{ \${$modelVar}->{$field["name"]} }}</span>\n";
                $detailsHtml .= "        </div>\n\n";
            }
            // View list
            $thHtml .= "            <th>Ações</th>";

            $this->info("Criando as views para {$viewFolder}");

            // Para a view create
            $stub = file_get_contents(__DIR__ . '/../../../resources/stubs/views/create.stub.php');
            $stub = str_replace('{MODEL}', $modelName, $stub);
            $stub = str_replace('{MODEL_VAR}', $modelVar, $stub);
            $stub = str_replace('{VIEW_FOLDER}', $viewFolder, $stub);
            $stub = str_replace('{FORM_FIELDS}', preg_replace($regex, '{{ old($1) }}', $formFieldsHtml), $stub);

            file_put_contents(__DIR__ . '/../../../resources/views/' . $viewFolder . '/create.blade.php', $stub);

            // Para a view edit
            $stub = file_get_contents(__DIR__ . '/../../../resources/stubs/views/edit.stub.php');
            $stub = str_replace('{MODEL}', $modelName, $stub);
            $stub = str_replace('{MODEL_VAR}', $modelVar, $stub);
            $stub = str_replace('{VIEW_FOLDER}', $viewFolder, $stub);
            $stub = str_replace('{FORM_FIELDS}', $formFieldsHtml, $stub);

            file_put_contents(__DIR__ . '/../../../resources/views/' . $viewFolder . '/edit.blade.php', $stub);

            // Para a view list
            $stub = file_get_contents(__DIR__ . '/../../../resources/stubs/views/list.stub.php');
            $stub = str_replace('{MODEL}', $modelName, $stub);
            $stub = str_replace('{MODEL_VAR}', $modelVar, $stub);
            $stub = str_replace('{VIEW_FOLDER}', $viewFolder, $stub);
            $stub = str_replace('{TABLE_HEADERS}', $thHtml, $stub);
            $stub = str_replace('{TABLE_ROWS}', $tdHtml, $stub);

            file_put_contents(__DIR__ . '/../../../resources/views/' . $viewFolder . '/list.blade.php', $stub);

            // Para a view show
            $stub = file_get_contents(__DIR__ . '/../../../resources/stubs/views/show.stub.php');
            $stub = str_replace('{MODEL}', $modelName, $stub);
            $stub = str_replace('{MODEL_VAR}', $modelVar, $stub);
            $stub = str_replace('{VIEW_FOLDER}', $viewFolder, $stub);
            $stub = str_replace('{DETAILS}', $detailsHtml, $stub);

            file_put_contents(__DIR__ . '/../../../resources/views/' . $viewFolder . '/show.blade.php', $stub);
        }

        if ($generateApi) {
            //generateApiController($modelName, $viewFolder, $fields);
        }

        // Mensagem de sucesso
        $this->info("✅ CRUD gerado com sucesso!");

        // Próximos passos personalizados
        $this->info("📝 Próximos passos:");

        $stepNum = 1;

        if ($generateViews && $generateController) {
            $this->info("   {$stepNum}. Acesse http://localhost:8000/{$viewFolder}/list");
            $stepNum++;
        }

        if ($generateApi) {
            $this->info("   {$stepNum}. API disponível em http://localhost:8000/api/{$viewFolder}");
            $stepNum++;
            $this->info("   {$stepNum}. Gere documentação Swagger: php generate-swagger");
            $stepNum++;
        }

        $this->info("   {$stepNum}. Personalize os arquivos gerados conforme necessário");
        $stepNum++;

        if ($generateController || $generateApi) {
            $this->info("   {$stepNum}. Adicione validações no" . ($generateController && $generateApi ? "s Controllers" : " Controller"));
        }

        return self::SUCCESS;
    }

}