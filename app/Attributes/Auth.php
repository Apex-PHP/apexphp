<?php

namespace App\Attributes;

use Attribute;

/**
 * Marca um método ou classe como requer autenticação
 *
 * @example
 * #[Auth]
 * public function getDashboard() { }
 *
 * #[Auth(roles: ['admin'])]
 * public function getAdmin() { }
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Auth
{
    public function __construct(
        public array $roles = [],
        public array $permissions = []
    ) {
    }
}
