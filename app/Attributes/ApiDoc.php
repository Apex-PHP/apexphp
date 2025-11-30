<?php

namespace App\Attributes;

use Attribute;

/**
 * Attribute para documentação customizada da API
 *
 * Uso:
 * #[ApiDoc(
 *     summary: "Lista todos os produtos",
 *     description: "Retorna uma lista paginada de produtos",
 *     tags: ["Products"],
 *     requestBody: ["name" => "string", "price" => "number"],
 *     responses: [200, 401, 422]
 * )]
 */
#[Attribute(Attribute::TARGET_METHOD)]
class ApiDoc
{
    public function __construct(
        public ?string $summary = null,
        public ?string $description = null,
        public array $tags = [],
        public ?array $requestBody = null,
        public array $responses = [],
        public ?array $parameters = null,
        public ?string $operationId = null,
        public bool $deprecated = false
    ) {
    }
}
