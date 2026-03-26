<?php

declare(strict_types=1);

namespace App\Validators;

/**
 * Validador de documentos brasileiros (CPF e CNPJ).
 * Detecta automaticamente o tipo pelo comprimento e valida os dígitos verificadores.
 */
class DocumentValidator
{
    /**
     * Valida um documento (CPF ou CNPJ).
     * Remove caracteres não numéricos antes de validar.
     */
    public static function validate(string $document): bool
    {
        $document = self::sanitize($document);

        if (strlen($document) === 11) {
            return self::validateCpf($document);
        }

        if (strlen($document) === 14) {
            return self::validateCnpj($document);
        }

        return false;
    }

    /**
     * Remove caracteres não numéricos do documento.
     */
    public static function sanitize(string $document): string
    {
        return preg_replace('/\D/', '', $document);
    }

    /**
     * Formata o documento conforme o tipo (CPF ou CNPJ).
     */
    public static function format(string $document): string
    {
        $document = self::sanitize($document);

        if (strlen($document) === 11) {
            return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $document);
        }

        if (strlen($document) === 14) {
            return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $document);
        }

        return $document;
    }

    /**
     * Detecta o tipo do documento.
     */
    public static function getType(string $document): string
    {
        $document = self::sanitize($document);
        return strlen($document) === 11 ? 'CPF' : 'CNPJ';
    }

    /**
     * Valida CPF pelos dígitos verificadores.
     */
    private static function validateCpf(string $cpf): bool
    {
        // Rejeita sequências de dígitos iguais (ex: 111.111.111-11)
        if (preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        // Calcula primeiro dígito verificador
        $sum = 0;
        for ($i = 0; $i < 9; $i++) {
            $sum += (int) $cpf[$i] * (10 - $i);
        }
        $remainder = ($sum * 10) % 11;
        if ($remainder === 10) {
            $remainder = 0;
        }
        if ((int) $cpf[9] !== $remainder) {
            return false;
        }

        // Calcula segundo dígito verificador
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $sum += (int) $cpf[$i] * (11 - $i);
        }
        $remainder = ($sum * 10) % 11;
        if ($remainder === 10) {
            $remainder = 0;
        }

        return (int) $cpf[10] === $remainder;
    }

    /**
     * Valida CNPJ pelos dígitos verificadores.
     */
    private static function validateCnpj(string $cnpj): bool
    {
        // Rejeita sequências de dígitos iguais
        if (preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        // Calcula primeiro dígito verificador
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $sum += (int) $cnpj[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit1 = $remainder < 2 ? 0 : 11 - $remainder;
        if ((int) $cnpj[12] !== $digit1) {
            return false;
        }

        // Calcula segundo dígito verificador
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $cnpj[$i] * $weights[$i];
        }
        $remainder = $sum % 11;
        $digit2 = $remainder < 2 ? 0 : 11 - $remainder;

        return (int) $cnpj[13] === $digit2;
    }
}
