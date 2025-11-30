<?php

namespace Framework\Swagger;

use ReflectionClass;
use ReflectionMethod;
use App\Attributes\Auth;
use App\Attributes\PublicRoute;
use App\Attributes\Guest;
use App\Attributes\ApiDoc;
use Illuminate\Database\Eloquent\Model;

class SwaggerGenerator
{
    private array $spec = [];
    private array $paths = [];
    private array $schemas = [];
    private string $basePath;
    private string $controllersPath;

    public function __construct(string $basePath = '/api')
    {
        $this->basePath = $basePath;
        $this->controllersPath = __DIR__ . '/../../app/Controllers/Api';

        $this->initializeSpec();
    }

    /**
     * Inicializa a estrutura básica do OpenAPI
     */
    private function initializeSpec(): void
    {
        $this->spec = [
            'openapi' => '3.0.0',
            'info' => [
                'title' => env('APP_NAME', 'ApexPHP API'),
                'description' => 'API RESTful gerada automaticamente pelo ApexPHP Framework',
                'version' => '1.0.0',
                'contact' => [
                    'name' => 'API Support',
                    'email' => env('APP_EMAIL', 'support@example.com')
                ]
            ],
            'servers' => [
                [
                    'url' => env('APP_URL', 'http://localhost:8000') . $this->basePath,
                    'description' => 'API Server'
                ]
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'JWT',
                        'description' => 'JWT token obtido via /auth/login'
                    ]
                ],
                'schemas' => []
            ],
            'paths' => [],
            'tags' => []
        ];
    }

    /**
     * Gera a documentação completa da API
     */
    public function generate(): array
    {
        if (!is_dir($this->controllersPath)) {
            throw new \Exception("Diretório de controllers API não encontrado: {$this->controllersPath}");
        }

        $controllers = $this->findControllers();

        if (empty($controllers)) {
            error_log("⚠️  Nenhum controller API encontrado em {$this->controllersPath}");
            error_log("💡 Execute: php console make:crud --table=sua_tabela");
        }

        foreach ($controllers as $controllerClass) {
            $this->processController($controllerClass);
        }

        $this->spec['paths'] = $this->paths;
        $this->spec['components']['schemas'] = $this->schemas;

        return $this->spec;
    }

    /**
     * Encontra todos os controllers API
     */
    private function findControllers(): array
    {
        $controllers = [];
        $files = glob($this->controllersPath . '/*Controller.php');

        foreach ($files as $file) {
            $className = basename($file, '.php');
            $fullClass = "App\\Controllers\\Api\\{$className}";

            if (class_exists($fullClass)) {
                $controllers[] = $fullClass;
            }
        }

        return $controllers;
    }

    /**
     * Processa um controller inteiro
     */
    private function processController(string $controllerClass): void
    {
        try {
            $reflection = new ReflectionClass($controllerClass);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

            // Extrair nome do recurso (ProductsController -> products)
            $controllerName = $reflection->getShortName();
            $resource = $this->extractResourceName($controllerName);
            $tag = ucfirst($resource);

            // Adicionar tag se não existir
            if (!in_array($tag, array_column($this->spec['tags'], 'name'))) {
                $this->spec['tags'][] = [
                    'name' => $tag,
                    'description' => "Operações relacionadas a {$tag}"
                ];
            }

            // Tentar obter o Model associado
            $model = $this->findModel($resource);
            if ($model) {
                $this->generateSchema($model, $tag);
            }

            foreach ($methods as $method) {
                // Pular métodos especiais
                if (str_starts_with($method->getName(), '__')) {
                    continue;
                }

                $this->processMethod($method, $resource, $tag, $model);
            }
        } catch (\Exception $e) {
            // Log erro mas continua processando outros controllers
            error_log("Erro ao processar controller {$controllerClass}: " . $e->getMessage());
        }
    }

    /**
     * Processa um método do controller
     */
    private function processMethod(ReflectionMethod $method, string $resource, string $tag, ?Model $model): void
    {
        $methodName = $method->getName();

        // Detectar verbo HTTP e ação
        $httpMethod = $this->extractHttpMethod($methodName);
        $action = $this->extractAction($methodName);

        if (!$httpMethod || !$action) {
            return; // Não é um método de rota
        }

        // Construir path
        $path = $this->buildPath($resource, $action, $method);

        // Analisar attributes
        $attributes = $method->getAttributes();
        $authAttr = $this->getAttribute($attributes, Auth::class);
        $publicAttr = $this->getAttribute($attributes, PublicRoute::class);
        $apiDocAttr = $this->getAttribute($attributes, ApiDoc::class);

        $requiresAuth = (bool) $authAttr;
        $roles = $authAttr ? ($authAttr->newInstance()->roles ?? []) : [];

        // Usar ApiDoc se disponível, senão gerar automático
        $apiDoc = $apiDocAttr ? $apiDocAttr->newInstance() : null;

        $operation = [
            'tags' => $apiDoc && $apiDoc->tags ? $apiDoc->tags : [$tag],
            'summary' => $apiDoc && $apiDoc->summary ? $apiDoc->summary : $this->generateSummary($action, $tag),
            'description' => $apiDoc && $apiDoc->description ? $apiDoc->description : $this->generateDescription($action, $tag),
            'operationId' => $apiDoc && $apiDoc->operationId ? $apiDoc->operationId : "{$methodName}_{$resource}",
            'parameters' => $this->generateParameters($method, $action, $apiDoc),
            'responses' => $this->generateResponses($action, $requiresAuth, $tag, $apiDoc)
        ];

        // Adicionar security se requer autenticação
        if ($requiresAuth) {
            $operation['security'] = [['bearerAuth' => []]];

            if (!empty($roles)) {
                $operation['description'] .= "\n\n**Roles necessárias:** " . implode(', ', $roles);
            }
        }

        // Adicionar requestBody para POST/PUT/PATCH
        if (in_array($httpMethod, ['post', 'put', 'patch']) && in_array($action, ['create', 'update'])) {
            $operation['requestBody'] = $this->generateRequestBody($model, $action, $tag, $apiDoc);
        }

        // Adicione requestBody para POST/PUT/PATCH que estão manualmente nos Controllers
        if (in_array($httpMethod, ['post', 'put', 'patch']) && !empty($apiDoc->requestBody)) {
            $operation['requestBody'] = $apiDoc->requestBody;
        }

        // Deprecado?
        if ($apiDoc && $apiDoc->deprecated) {
            $operation['deprecated'] = true;
        }

        // Adicionar ao path
        if (!isset($this->paths[$path])) {
            $this->paths[$path] = [];
        }

        $this->paths[$path][$httpMethod] = $operation;
    }

    /**
     * Extrai nome do recurso do controller
     */
    private function extractResourceName(string $controllerName): string
    {
        $name = str_replace('Controller', '', $controllerName);
        return strtolower($name);
    }

    /**
     * Extrai verbo HTTP do nome do método
     */
    private function extractHttpMethod(string $methodName): ?string
    {
        $verbs = ['get', 'post', 'put', 'patch', 'delete'];

        foreach ($verbs as $verb) {
            if (str_starts_with(strtolower($methodName), $verb)) {
                return $verb;
            }
        }

        return null;
    }

    /**
     * Extrai ação do nome do método
     */
    private function extractAction(string $methodName): ?string
    {
        $pattern = '/^(get|post|put|patch|delete)(.+)$/i';

        if (preg_match($pattern, $methodName, $matches)) {
            return strtolower($matches[2]);
        }

        return null;
    }

    /**
     * Constrói o path da rota
     */
    private function buildPath(string $resource, string $action, ReflectionMethod $method): string
    {
        $params = $method->getParameters();

        // Rotas especiais
        if ($action === 'index') {
            return "/{$resource}";
        }

        if ($action === 'show' || $action === 'update' || $action === 'delete' || $action === 'edit') {
            return "/{$resource}/{$action}/{id}";
        }

        // Outras rotas
        $path = "/{$resource}/{$action}";

        // Adicionar parâmetros de rota se houver
        foreach ($params as $param) {
            if ($param->getType() && !$param->getType()->isBuiltin()) {
                continue; // Pular parâmetros de classes
            }

            $paramName = $param->getName();
            if ($paramName !== 'id' && !in_array($paramName, ['page', 'per_page', 'search'])) {
                $path .= "/{{$paramName}}";
            }
        }

        return $path;
    }

    /**
     * Gera parâmetros da operação
     */
    private function generateParameters(ReflectionMethod $method, string $action, ?ApiDoc $apiDoc): array
    {
        $parameters = [];

        // Se ApiDoc tem parâmetros customizados, usar eles
        if ($apiDoc && $apiDoc->parameters) {
            return $apiDoc->parameters;
        }

        $params = $method->getParameters();

        foreach ($params as $param) {
            $paramName = $param->getName();
            $paramType = $param->getType();

            // Parâmetro de rota (ex: id)
            if ($paramName === 'id') {
                $parameters[] = [
                    'name' => 'id',
                    'in' => 'path',
                    'required' => true,
                    'description' => 'ID do registro',
                    'schema' => [
                        'type' => 'integer',
                        'format' => 'int64'
                    ]
                ];
            }
        }

        // Adicionar parâmetros de paginação para listagens
        if (in_array($action, ['index', 'list', 'search'])) {
            $parameters[] = [
                'name' => 'page',
                'in' => 'query',
                'required' => false,
                'description' => 'Número da página',
                'schema' => [
                    'type' => 'integer',
                    'default' => 1,
                    'minimum' => 1
                ]
            ];

            $parameters[] = [
                'name' => 'per_page',
                'in' => 'query',
                'required' => false,
                'description' => 'Itens por página',
                'schema' => [
                    'type' => 'integer',
                    'default' => 15,
                    'minimum' => 1,
                    'maximum' => 100
                ]
            ];
        }

        // Adicionar parâmetros de busca
        if ($action === 'search') {
            $parameters[] = [
                'name' => 'search',
                'in' => 'query',
                'required' => false,
                'description' => 'Termo de busca',
                'schema' => [
                    'type' => 'string'
                ]
            ];
        }

        return $parameters;
    }

    /**
     * Gera respostas da operação
     */
    private function generateResponses(string $action, bool $requiresAuth, string $tag, ?ApiDoc $apiDoc): array
    {
        // Se ApiDoc tem respostas customizadas
        if ($apiDoc && !empty($apiDoc->responses)) {
            $responses = [];
            foreach ($apiDoc->responses as $code) {
                $responses[(string) $code] = $this->getResponseForCode($code, $tag);
            }
            return $responses;
        }

        $responses = [];

        // Respostas baseadas na ação
        switch ($action) {
            case 'create':
                $responses['201'] = [
                    'description' => 'Registro criado com sucesso',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => "#/components/schemas/{$tag}Response"
                            ]
                        ]
                    ]
                ];
                $responses['422'] = $this->getResponseForCode(422, $tag);
                break;

            case 'update':
                $responses['200'] = [
                    'description' => 'Registro atualizado com sucesso',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => "#/components/schemas/{$tag}Response"
                            ]
                        ]
                    ]
                ];
                $responses['404'] = $this->getResponseForCode(404, $tag);
                $responses['422'] = $this->getResponseForCode(422, $tag);
                break;

            case 'delete':
                $responses['200'] = [
                    'description' => 'Registro deletado com sucesso',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/SuccessResponse'
                            ]
                        ]
                    ]
                ];
                $responses['404'] = $this->getResponseForCode(404, $tag);
                break;

            case 'show':
                $responses['200'] = [
                    'description' => 'Detalhes do registro',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => "#/components/schemas/{$tag}Response"
                            ]
                        ]
                    ]
                ];
                $responses['404'] = $this->getResponseForCode(404, $tag);
                break;

            case 'index':
            case 'list':
            case 'search':
                $responses['200'] = [
                    'description' => 'Lista de registros',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => "#/components/schemas/{$tag}ListResponse"
                            ]
                        ]
                    ]
                ];
                break;

            default:
                $responses['200'] = [
                    'description' => 'Operação bem-sucedida',
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                '$ref' => '#/components/schemas/SuccessResponse'
                            ]
                        ]
                    ]
                ];
        }

        // Adicionar erros de autenticação
        if ($requiresAuth) {
            $responses['401'] = $this->getResponseForCode(401, $tag);
            $responses['403'] = $this->getResponseForCode(403, $tag);
        }

        return $responses;
    }

    /**
     * Retorna resposta padrão para um código HTTP
     */
    private function getResponseForCode(int $code, string $tag): array
    {
        $responses = [
            401 => [
                'description' => 'Não autenticado - Token JWT ausente ou inválido',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ErrorResponse']
                    ]
                ]
            ],
            403 => [
                'description' => 'Acesso negado - Sem permissão para esta operação',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ErrorResponse']
                    ]
                ]
            ],
            404 => [
                'description' => 'Registro não encontrado',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ErrorResponse']
                    ]
                ]
            ],
            422 => [
                'description' => 'Erro de validação',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ValidationErrorResponse']
                    ]
                ]
            ],
            500 => [
                'description' => 'Erro interno do servidor',
                'content' => [
                    'application/json' => [
                        'schema' => ['$ref' => '#/components/schemas/ErrorResponse']
                    ]
                ]
            ]
        ];

        return $responses[$code] ?? [
            'description' => 'Resposta HTTP ' . $code
        ];
    }

    /**
     * Gera requestBody para operações POST/PUT/PATCH
     */
    private function generateRequestBody(?Model $model, string $action, string $tag, ?ApiDoc $apiDoc): array
    {
        if ($apiDoc && $apiDoc->requestBody) {
            return [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => [
                            'type' => 'object',
                            'properties' => $this->convertToSchemaProperties($apiDoc->requestBody)
                        ]
                    ]
                ]
            ];
        }

        return [
            'required' => true,
            'description' => $action === 'create' ? 'Dados para criar o registro' : 'Dados para atualizar o registro',
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => "#/components/schemas/{$tag}Input"
                    ]
                ]
            ]
        ];
    }

    /**
     * Converte array de tipos para propriedades de schema
     */
    private function convertToSchemaProperties(array $fields): array
    {
        $properties = [];

        foreach ($fields as $name => $type) {
            $properties[$name] = ['type' => $type];
        }

        return $properties;
    }

    /**
     * Encontra o Model associado ao recurso
     */
    private function findModel(string $resource): ?Model
    {
        // Tentar variações do nome
        $singularName = rtrim($resource, 's'); // products -> product
        $modelNames = [
            ucfirst($singularName),
            ucfirst($resource),
            str_replace('_', '', ucwords($singularName, '_'))
        ];

        foreach ($modelNames as $modelName) {
            $modelClass = "App\\Models\\{$modelName}";

            if (class_exists($modelClass)) {
                try {
                    return new $modelClass();
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return null;
    }

    /**
     * Gera schema a partir do Model
     */
    private function generateSchema(?Model $model, string $tag): void
    {
        if (!$model) {
            // Schema genérico
            $this->generateGenericSchemas($tag);
            return;
        }

        $table = $model->getTable();
        $fillable = $model->getFillable();

        try {
            // Usar Capsule diretamente ao invés de Facade
            $columns = \Illuminate\Database\Capsule\Manager::select("SHOW COLUMNS FROM {$table}");

            $properties = [];
            $required = [];

            foreach ($columns as $column) {
                $fieldName = $column->Field;
                $type = $this->mapSqlTypeToJsonType($column->Type);

                $properties[$fieldName] = [
                    'type' => $type['type']
                ];

                if (isset($type['format'])) {
                    $properties[$fieldName]['format'] = $type['format'];
                }

                if ($column->Null === 'NO' && $fieldName !== 'id') {
                    $required[] = $fieldName;
                }
            }

            // Schema completo (com ID e timestamps)
            $this->schemas[$tag] = [
                'type' => 'object',
                'properties' => $properties
            ];

            // Schema de input (sem ID e timestamps)
            $inputProperties = array_filter($properties, function ($key) {
                return !in_array($key, ['id', 'created_at', 'updated_at', 'deleted_at']);
            }, ARRAY_FILTER_USE_KEY);

            $this->schemas["{$tag}Input"] = [
                'type' => 'object',
                'required' => array_diff($required, ['created_at', 'updated_at']),
                'properties' => $inputProperties
            ];

            // Schema de resposta (com envelope)
            $this->schemas["{$tag}Response"] = [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => true],
                    'message' => ['type' => 'string', 'example' => 'Success'],
                    'data' => ['$ref' => "#/components/schemas/{$tag}"]
                ]
            ];

            // Schema de lista com paginação
            $this->schemas["{$tag}ListResponse"] = [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => true],
                    'message' => ['type' => 'string', 'example' => 'Success'],
                    'data' => [
                        'type' => 'object',
                        'properties' => [
                            'data' => [
                                'type' => 'array',
                                'items' => ['$ref' => "#/components/schemas/{$tag}"]
                            ],
                            'pagination' => ['$ref' => '#/components/schemas/Pagination']
                        ]
                    ]
                ]
            ];

        } catch (\Exception $e) {
            // Log mais detalhado do erro
            $errorMsg = "Erro ao gerar schema para {$tag}: " . $e->getMessage();
            error_log($errorMsg);

            // Se for erro de conexão, mostrar mensagem mais clara
            if (strpos($e->getMessage(), 'Access denied') !== false) {
                error_log("Verifique as credenciais do banco de dados no arquivo .env");
            } elseif (strpos($e->getMessage(), 'Connection refused') !== false) {
                error_log("Verifique se o servidor MySQL está rodando");
            } elseif (strpos($e->getMessage(), "doesn't exist") !== false) {
                error_log("A tabela {$table} não existe no banco de dados");
            }

            // Gerar schema genérico como fallback
            $this->generateGenericSchemas($tag);
        }

        // Schemas comuns (apenas uma vez)
        if (!isset($this->schemas['Pagination'])) {
            $this->schemas['Pagination'] = [
                'type' => 'object',
                'properties' => [
                    'total' => ['type' => 'integer', 'example' => 100],
                    'per_page' => ['type' => 'integer', 'example' => 15],
                    'current_page' => ['type' => 'integer', 'example' => 1],
                    'last_page' => ['type' => 'integer', 'example' => 7],
                    'from' => ['type' => 'integer', 'example' => 1],
                    'to' => ['type' => 'integer', 'example' => 15]
                ]
            ];

            $this->schemas['SuccessResponse'] = [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => true],
                    'message' => ['type' => 'string', 'example' => 'Operação realizada com sucesso'],
                    'data' => ['type' => 'object']
                ]
            ];

            $this->schemas['ErrorResponse'] = [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => false],
                    'message' => ['type' => 'string', 'example' => 'Erro na operação']
                ]
            ];

            $this->schemas['ValidationErrorResponse'] = [
                'type' => 'object',
                'properties' => [
                    'success' => ['type' => 'boolean', 'example' => false],
                    'message' => ['type' => 'string', 'example' => 'Dados inválidos'],
                    'errors' => [
                        'type' => 'object',
                        'additionalProperties' => [
                            'type' => 'array',
                            'items' => ['type' => 'string']
                        ],
                        'example' => [
                            'name' => ['Name is required'],
                            'email' => ['Email must be valid']
                        ]
                    ]
                ]
            ];
        }
    }

    /**
     * Gera schemas genéricos quando não há Model
     */
    private function generateGenericSchemas(string $tag): void
    {
        $this->schemas[$tag] = [
            'type' => 'object',
            'properties' => [
                'id' => ['type' => 'integer'],
                'created_at' => ['type' => 'string', 'format' => 'date-time'],
                'updated_at' => ['type' => 'string', 'format' => 'date-time']
            ]
        ];

        $this->schemas["{$tag}Input"] = [
            'type' => 'object',
            'properties' => []
        ];

        $this->schemas["{$tag}Response"] = [
            'type' => 'object',
            'properties' => [
                'success' => ['type' => 'boolean'],
                'message' => ['type' => 'string'],
                'data' => ['$ref' => "#/components/schemas/{$tag}"]
            ]
        ];

        $this->schemas["{$tag}ListResponse"] = [
            'type' => 'object',
            'properties' => [
                'success' => ['type' => 'boolean'],
                'message' => ['type' => 'string'],
                'data' => [
                    'type' => 'object',
                    'properties' => [
                        'data' => [
                            'type' => 'array',
                            'items' => ['$ref' => "#/components/schemas/{$tag}"]
                        ],
                        'pagination' => ['$ref' => '#/components/schemas/Pagination']
                    ]
                ]
            ]
        ];
    }

    /**
     * Mapeia tipo SQL para tipo JSON Schema
     */
    private function mapSqlTypeToJsonType(string $sqlType): array
    {
        $type = strtolower($sqlType);

        if (preg_match('/int|integer|bigint|smallint|tinyint/', $type)) {
            return ['type' => 'integer', 'format' => 'int64'];
        }

        if (preg_match('/decimal|float|double|real/', $type)) {
            return ['type' => 'number', 'format' => 'double'];
        }

        if (preg_match('/bool|boolean/', $type)) {
            return ['type' => 'boolean'];
        }

        if (preg_match('/date/', $type)) {
            return ['type' => 'string', 'format' => 'date'];
        }

        if (preg_match('/datetime|timestamp/', $type)) {
            return ['type' => 'string', 'format' => 'date-time'];
        }

        if (preg_match('/time/', $type)) {
            return ['type' => 'string', 'format' => 'time'];
        }

        return ['type' => 'string'];
    }

    /**
     * Obtém um attribute do método
     */
    private function getAttribute(array $attributes, string $className): ?\ReflectionAttribute
    {
        foreach ($attributes as $attr) {
            if ($attr->getName() === $className) {
                return $attr;
            }
        }
        return null;
    }

    /**
     * Gera summary automático
     */
    private function generateSummary(string $action, string $tag): string
    {
        $summaries = [
            'index' => "Lista todos os {$tag}",
            'list' => "Lista todos os {$tag}",
            'show' => "Busca um {$tag} por ID",
            'create' => "Cria um novo {$tag}",
            'update' => "Atualiza um {$tag}",
            'delete' => "Deleta um {$tag}",
            'search' => "Busca {$tag} com filtros"
        ];

        return $summaries[$action] ?? ucfirst($action) . " {$tag}";
    }

    /**
     * Gera description automático
     */
    private function generateDescription(string $action, string $tag): string
    {
        $descriptions = [
            'index' => "Retorna uma lista paginada de {$tag}",
            'list' => "Retorna uma lista paginada de {$tag}",
            'show' => "Retorna os detalhes de um {$tag} específico",
            'create' => "Cria um novo registro de {$tag}",
            'update' => "Atualiza os dados de um {$tag} existente",
            'delete' => "Remove permanentemente um {$tag}",
            'search' => "Busca {$tag} com filtros customizados"
        ];

        return $descriptions[$action] ?? "Operação {$action} para {$tag}";
    }

    /**
     * Salva a spec em arquivo JSON
     */
    public function saveToFile(string $path): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($path, json_encode($this->spec, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    /**
     * Salva a spec em formato YAML
     */
    public function saveToYaml(string $path): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $yaml = \Symfony\Component\Yaml\Yaml::dump($this->spec, 10, 2);
        file_put_contents($path, $yaml);
    }
}
