<?php

namespace App\Attributes;

use Attribute;

/**
 * Marca um método ou classe como acessível publicamente (sem autenticação)
 *
 * @example
 * #[PublicRoute]
 * public function getList() { }
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class PublicRoute
{
    public function __construct()
    {
    }
}
