<?php

declare(strict_types=1);

namespace App\BusinessRules;

use App\Repositories\SettingRepository;

/**
 * Regra de desconto progressivo por quantidade total de itens no contrato.
 *
 * Lê as faixas (tiers) de desconto da tabela de configurações do banco,
 * permitindo que o usuário altere as regras pela tela sem necessidade de deploy.
 *
 * Exemplo de configuração (JSON salvo na settings):
 * {
 *     "tiers": [
 *         {"min_quantity": 3,  "discount_percent": 5},
 *         {"min_quantity": 5,  "discount_percent": 10},
 *         {"min_quantity": 10, "discount_percent": 15}
 *     ]
 * }
 *
 * A lógica soma todas as quantidades dos itens do contrato e aplica o
 * maior percentual de desconto cuja quantidade mínima foi atingida.
 */
class ProgressiveDiscountRule implements DiscountRuleInterface
{
    private SettingRepository $settingRepository;

    public function __construct()
    {
        $this->settingRepository = new SettingRepository();
    }

    /**
     * Verifica se o desconto progressivo está habilitado nas configurações.
     */
    public function isEnabled(): bool
    {
        $setting = $this->settingRepository->findByKey('discount_enabled');
        if (!$setting) {
            return false;
        }

        return $setting->getTypedValue() === true;
    }

    /**
     * Calcula o desconto progressivo com base na quantidade total de itens.
     */
    public function calculate(float $subtotal, array $items, array $contract): array
    {
        $result = [
            'discount_percent'   => 0.0,
            'discount_value'     => 0.0,
            'rule_name'          => 'Desconto Progressivo',
            'rule_description'   => 'Nenhum desconto aplicado.',
        ];

        if (!$this->isEnabled() || $subtotal <= 0) {
            return $result;
        }

        // Carrega as faixas de desconto da configuração
        $tiers = $this->getTiers();
        if (empty($tiers)) {
            return $result;
        }

        // Soma total de quantidades de todos os itens
        $totalQuantity = 0;
        foreach ($items as $item) {
            $totalQuantity += (int) ($item['quantity'] ?? 0);
        }

        // Encontra a maior faixa de desconto que se encaixa
        $applicableTier = null;
        foreach ($tiers as $tier) {
            $minQty = (int) ($tier['min_quantity'] ?? 0);
            if ($totalQuantity >= $minQty) {
                if (!$applicableTier || $minQty > (int) $applicableTier['min_quantity']) {
                    $applicableTier = $tier;
                }
            }
        }

        if ($applicableTier) {
            $discountPercent = (float) ($applicableTier['discount_percent'] ?? 0);
            $discountValue = round($subtotal * ($discountPercent / 100), 2);

            $result['discount_percent'] = $discountPercent;
            $result['discount_value'] = $discountValue;
            $result['rule_description'] = sprintf(
                'Desconto de %.1f%% aplicado para %d+ itens no contrato.',
                $discountPercent,
                (int) $applicableTier['min_quantity']
            );
        }

        return $result;
    }

    /**
     * Carrega as faixas de desconto da tabela de configurações.
     */
    private function getTiers(): array
    {
        $setting = $this->settingRepository->findByKey('discount_rules');
        if (!$setting) {
            return [];
        }

        $config = $setting->getTypedValue();
        return $config['tiers'] ?? [];
    }
}
