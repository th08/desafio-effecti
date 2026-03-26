<?php

declare(strict_types=1);

namespace App\Validators;

use App\Exceptions\ValidationException;

/**
 * Validador de dados de Configurações.
 */
class SettingValidator
{
    /**
     * Valida dados para atualização de configuração.
     */
    public static function validateUpdate(array $data, string $type): array
    {
        $errors = [];

        if (!isset($data['value'])) {
            $errors['value'] = 'O valor é obrigatório.';
        } else {
            // Valida de acordo com o tipo da configuração
            switch ($type) {
                case 'boolean':
                    if (!in_array($data['value'], ['true', 'false', true, false], true)) {
                        $errors['value'] = 'O valor deve ser true ou false.';
                    }
                    break;

                case 'number':
                    if (!is_numeric($data['value'])) {
                        $errors['value'] = 'O valor deve ser numérico.';
                    }
                    break;

                case 'json':
                    if (is_string($data['value'])) {
                        $decoded = json_decode($data['value'], true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $errors['value'] = 'O valor deve ser um JSON válido.';
                        }
                    }
                    break;
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }

        return $data;
    }
}
