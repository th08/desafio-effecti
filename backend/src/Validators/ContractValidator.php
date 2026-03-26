<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidationException;

/**
 * Validador de dados de Contrato e seus Itens.
 */
class ContractValidator
{
    /**
     * Valida dados para criação de contrato.
     */
    public static function validateCreate(array $data): array
    {
        $errors = [];

        // Cliente obrigatório
        if (empty($data['client_id'])) {
            $errors['client_id'] = 'O cliente é obrigatório.';
        } elseif (!is_numeric($data['client_id'])) {
            $errors['client_id'] = 'O ID do cliente deve ser numérico.';
        }

        // Data de início obrigatória
        if (empty($data['start_date'])) {
            $errors['start_date'] = 'A data de início é obrigatória.';
        } elseif (!self::isValidDate($data['start_date'])) {
            $errors['start_date'] = 'A data de início deve estar no formato YYYY-MM-DD.';
        }

        // Data de término (opcional, mas se informada, deve ser válida e posterior à de início)
        if (!empty($data['end_date'])) {
            if (!self::isValidDate($data['end_date'])) {
                $errors['end_date'] = 'A data de término deve estar no formato YYYY-MM-DD.';
            } elseif (!empty($data['start_date']) && self::isValidDate($data['start_date'])) {
                if ($data['end_date'] <= $data['start_date']) {
                    $errors['end_date'] = 'A data de término deve ser posterior à data de início.';
                }
            }
        }

        // Status (se informado)
        if (isset($data['status']) && !in_array($data['status'], ['A', 'C'], true)) {
            $errors['status'] = 'O status deve ser A (Ativo) ou C (Cancelado).';
        }

        // Valida itens (se informados na criação)
        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $index => $item) {
                $itemErrors = self::validateItemData($item);
                foreach ($itemErrors as $field => $message) {
                    $errors["items.{$index}.{$field}"] = $message;
                }
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $data;
    }

    /**
     * Valida dados para atualização de contrato.
     */
    public static function validateUpdate(array $data): array
    {
        $errors = [];

        if (isset($data['start_date']) && !self::isValidDate($data['start_date'])) {
            $errors['start_date'] = 'A data de início deve estar no formato YYYY-MM-DD.';
        }

        if (isset($data['end_date']) && !empty($data['end_date']) && !self::isValidDate($data['end_date'])) {
            $errors['end_date'] = 'A data de término deve estar no formato YYYY-MM-DD.';
        }

        if (
            !empty($data['start_date']) && !empty($data['end_date'])
            && self::isValidDate($data['start_date']) && self::isValidDate($data['end_date'])
            && $data['end_date'] <= $data['start_date']
        ) {
            $errors['end_date'] = 'A data de término deve ser posterior à data de início.';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $data;
    }

    /**
     * Valida dados de um item do contrato.
     */
    public static function validateItem(array $data): array
    {
        $errors = self::validateItemData($data);

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $data;
    }

    /**
     * Validação interna dos campos de um item.
     */
    private static function validateItemData(array $data): array
    {
        $errors = [];

        if (empty($data['service_id'])) {
            $errors['service_id'] = 'O serviço é obrigatório.';
        } elseif (!is_numeric($data['service_id'])) {
            $errors['service_id'] = 'O ID do serviço deve ser numérico.';
        }

        if (!isset($data['quantity'])) {
            $errors['quantity'] = 'A quantidade é obrigatória.';
        } elseif (!is_numeric($data['quantity']) || (int) $data['quantity'] < 1) {
            $errors['quantity'] = 'A quantidade deve ser no mínimo 1.';
        }

        if (!isset($data['unit_value'])) {
            $errors['unit_value'] = 'O valor unitário é obrigatório.';
        } elseif (!is_numeric($data['unit_value']) || (float) $data['unit_value'] < 0) {
            $errors['unit_value'] = 'O valor unitário deve ser maior ou igual a zero.';
        }

        return $errors;
    }

    /**
     * Verifica se uma string é uma data válida no formato YYYY-MM-DD.
     */
    private static function isValidDate(string $date): bool
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
}
