<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Exceção para erros de validação de dados (HTTP 422).
 */
class ValidationException extends RuntimeException
{
    private array $errors;

    public function __construct(array $errors)
    {
        $this->errors = $errors;
        parent::__construct('Erro de validação.');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
