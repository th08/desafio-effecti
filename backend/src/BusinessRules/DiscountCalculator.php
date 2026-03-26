<?php

declare(strict_types=1);

namespace App\BusinessRules;

/**
 * Orquestrador de regras de desconto.
 * Aplica todas as regras de desconto ativas e retorna o resultado consolidado.
 *
 * Para adicionar uma nova regra de desconto:
 * 1. Crie uma classe que implemente DiscountRuleInterface
 * 2. Registre-a no array $rules do construtor
 * Não é necessário alterar nenhum outro código do sistema.
 */
class DiscountCalculator
{
    /** @var DiscountRuleInterface[] */
    private array $rules;

    /**
     * @param DiscountRuleInterface[]|null $rules Regras a utilizar (null = regras padrão)
     */
    public function __construct(?array $rules = null)
    {
        // Permite injeção de regras para testes; caso contrário, usa as padrão
        $this->rules = $rules ?? [
            new ProgressiveDiscountRule(),
        ];
    }

    /**
     * Calcula o valor total do contrato aplicando todas as regras de desconto ativas.
     *
     * @param array $items    Itens do contrato (cada item: quantity, unit_value, service, etc.)
     * @param array $contract Dados do contrato.
     *
     * @return array{
     *     subtotal: float,
     *     discount_percent: float,
     *     discount_value: float,
     *     total: float,
     *     applied_rules: array
     * }
     */
    public function calculate(array $items, array $contract = []): array
    {
        // Calcula subtotal (soma de quantity * unit_value de cada item)
        $subtotal = 0.0;
        foreach ($items as $item) {
            $quantity = (int) ($item['quantity'] ?? 0);
            $unitValue = (float) ($item['unit_value'] ?? 0);
            $subtotal += $quantity * $unitValue;
        }

        $subtotal = round($subtotal, 2);
        $totalDiscountPercent = 0.0;
        $totalDiscountValue = 0.0;
        $appliedRules = [];

        // Aplica cada regra de desconto ativa
        foreach ($this->rules as $rule) {
            if (!$rule->isEnabled()) {
                continue;
            }

            $result = $rule->calculate($subtotal, $items, $contract);

            if ($result['discount_value'] > 0) {
                $totalDiscountPercent += $result['discount_percent'];
                $totalDiscountValue += $result['discount_value'];
                $appliedRules[] = [
                    'rule_name'        => $result['rule_name'],
                    'rule_description' => $result['rule_description'],
                    'discount_percent' => $result['discount_percent'],
                    'discount_value'   => $result['discount_value'],
                ];
            }
        }

        $totalDiscountValue = round($totalDiscountValue, 2);
        $total = round($subtotal - $totalDiscountValue, 2);

        // Garante que o total não fique negativo
        if ($total < 0) {
            $total = 0.0;
        }

        return [
            'subtotal'         => $subtotal,
            'discount_percent' => $totalDiscountPercent,
            'discount_value'   => $totalDiscountValue,
            'total'            => $total,
            'applied_rules'    => $appliedRules,
        ];
    }
}
