<?php

namespace App\Attributes;

use Attribute;

/**
 * Marca um método ou classe como acessível apenas para visitantes (não autenticados)
 * Usuários autenticados são redirecionados
 *
 * @example
 * #[Guest]
 * public function getLogin() { }
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class Guest
{
    public function __construct(
        public string $redirectTo = '/dashboard'
    ) {
    }
}
