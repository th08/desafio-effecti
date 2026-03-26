<?php

declare(strict_types=1);

namespace App\BusinessRules;

/**
 * Interface para regras de desconto.
 * Implementa o Strategy Pattern — cada regra de desconto é uma estratégia independente.
 * Para adicionar uma nova regra, basta criar uma classe que implemente esta interface.
 */
interface DiscountRuleInterface
{
    /**
     * Calcula o desconto a ser aplicado no contrato.
     *
     * @param float $subtotal Valor subtotal do contrato (soma dos itens sem desconto).
     * @param array $items    Array de itens do contrato (cada item contém quantity, unit_value, etc.).
     * @param array $contract Dados do contrato.
     *
     * @return array{
     *     discount_percent: float,
     *     discount_value: float,
     *     rule_name: string,
     *     rule_description: string
     * }
     */
    public function calculate(float $subtotal, array $items, array $contract): array;

    /**
     * Verifica se a regra está ativa/habilitada.
     */
    public function isEnabled(): bool;
}
