<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Exceção para recursos não encontrados (HTTP 404).
 */
class NotFoundException extends RuntimeException
{
    public function __construct(string $message = 'Recurso não encontrado.')
    {
        parent::__construct($message);
    }
}
