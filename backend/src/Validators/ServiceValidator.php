<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidationException;

/**
 * Validador de dados de Serviço.
 */
class ServiceValidator
{
    /**
     * Valida dados para criação de serviço.
     */
    public static function validateCreate(array $data): array
    {
        $errors = [];

        // Nome obrigatório
        if (empty($data['name'])) {
            $errors['name'] = 'O nome do serviço é obrigatório.';
        } elseif (strlen($data['name']) > 255) {
            $errors['name'] = 'O nome deve ter no máximo 255 caracteres.';
        }

        // Valor base mensal obrigatório e maior que zero
        if (!isset($data['base_monthly_value'])) {
            $errors['base_monthly_value'] = 'O valor base mensal é obrigatório.';
        } elseif (!is_numeric($data['base_monthly_value']) || (float) $data['base_monthly_value'] <= 0) {
            $errors['base_monthly_value'] = 'O valor base mensal deve ser maior que zero.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $data;
    }

    /**
     * Valida dados para atualização de serviço.
     */
    public static function validateUpdate(array $data): array
    {
        $errors = [];

        if (isset($data['name']) && empty($data['name'])) {
            $errors['name'] = 'O nome do serviço não pode ser vazio.';
        }

        if (isset($data['base_monthly_value'])) {
            if (!is_numeric($data['base_monthly_value']) || (float) $data['base_monthly_value'] <= 0) {
                $errors['base_monthly_value'] = 'O valor base mensal deve ser maior que zero.';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $data;
    }
}
