<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidationException;

/**
 * Validador de dados de Cliente.
 */
class ClientValidator
{
    /**
     * Valida dados para criação de cliente.
     */
    public static function validateCreate(array $data): array
    {
        $errors = [];

        // Nome obrigatório
        if (empty($data['name'])) {
            $errors['name'] = 'O nome é obrigatório.';
        } elseif (strlen($data['name']) > 255) {
            $errors['name'] = 'O nome deve ter no máximo 255 caracteres.';
        }

        // Documento (CPF/CNPJ) obrigatório e válido
        if (empty($data['document'])) {
            $errors['document'] = 'O CPF/CNPJ é obrigatório.';
        } else {
            $sanitized = DocumentValidator::sanitize($data['document']);
            if (!DocumentValidator::validate($sanitized)) {
                $errors['document'] = 'O CPF/CNPJ informado é inválido.';
            }
        }

        // Email obrigatório e válido
        if (empty($data['email'])) {
            $errors['email'] = 'O email é obrigatório.';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'O email informado é inválido.';
        }

        // Status deve ser A ou I (se informado)
        if (isset($data['status']) && !in_array($data['status'], ['A', 'I'], true)) {
            $errors['status'] = 'O status deve ser A (Ativo) ou I (Inativo).';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $data;
    }

    /**
     * Valida dados para atualização de cliente.
     */
    public static function validateUpdate(array $data): array
    {
        $errors = [];

        // Nome (se informado)
        if (isset($data['name']) && empty($data['name'])) {
            $errors['name'] = 'O nome não pode ser vazio.';
        } elseif (isset($data['name']) && strlen($data['name']) > 255) {
            $errors['name'] = 'O nome deve ter no máximo 255 caracteres.';
        }

        // Documento (se informado)
        if (isset($data['document'])) {
            $sanitized = DocumentValidator::sanitize($data['document']);
            if (!DocumentValidator::validate($sanitized)) {
                $errors['document'] = 'O CPF/CNPJ informado é inválido.';
            }
        }

        // Email (se informado)
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'O email informado é inválido.';
        }

        // Status (se informado)
        if (isset($data['status']) && !in_array($data['status'], ['A', 'I'], true)) {
            $errors['status'] = 'O status deve ser A (Ativo) ou I (Inativo).';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $data;
    }
}
