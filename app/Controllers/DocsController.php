<?php

namespace App\Controllers;

use App\Attributes\PublicRoute;

class DocsController extends BaseController
{
    /**
     * GET /docs
     * Exibe a interface Swagger UI
     */
    #[PublicRoute]
    public function getIndex()
    {
        return $this->render('docs.swagger');
    }

    /**
     * GET /docs/json
     * Retorna o arquivo swagger.json
     */
    #[PublicRoute]
    public function getJson()
    {
        $jsonPath = __DIR__ . '/../../public/_docs/swagger.json';

        if (!file_exists($jsonPath)) {
            http_response_code(404);
            return $this->json([
                'error' => 'Documentação não encontrada',
                'message' => 'Execute: php generate-swagger'
            ], 404);
        }

        $content = file_get_contents($jsonPath);
        header('Content-Type: application/json');
        return $content;
    }

    /**
     * GET /docs/yaml
     * Retorna o arquivo swagger.yaml
     */
    #[PublicRoute]
    public function getYaml()
    {
        $yamlPath = __DIR__ . '/../../public/_docs/swagger.yaml';

        if (!file_exists($yamlPath)) {
            http_response_code(404);
            return $this->json([
                'error' => 'Documentação YAML não encontrada',
                'message' => 'Execute: php generate-swagger --format=yaml'
            ], 404);
        }

        $content = file_get_contents($yamlPath);
        header('Content-Type: application/x-yaml');
        return $content;
    }
}
