<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Exceção para violação de regras de negócio (HTTP 400).
 */
class BusinessException extends RuntimeException
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}
