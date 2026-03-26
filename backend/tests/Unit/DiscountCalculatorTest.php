<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\BusinessRules\ProgressiveDiscountRule;
use App\BusinessRules\DiscountCalculator;
use App\BusinessRules\DiscountRuleInterface;

/**
 * Testes unitários para cálculo de desconto progressivo.
 * Utiliza mock dos dados de configuração para isolar a lógica de cálculo.
 */
class DiscountCalculatorTest extends TestCase
{
    /**
     * Cria uma regra de desconto fake para testes (sem banco de dados)
     */
    private function createFakeRule(bool $enabled, array $tiers): DiscountRuleInterface
    {
        return new class ($enabled, $tiers) implements DiscountRuleInterface {
            private bool $enabled;
            private array $tiers;

            public function __construct(bool $enabled, array $tiers)
            {
                $this->enabled = $enabled;
                $this->tiers = $tiers;
            }

            public function isEnabled(): bool
            {
                return $this->enabled;
            }

            public function calculate(float $subtotal, array $items, array $contract = []): array
            {
                $result = [
                    'discount_percent'  => 0.0,
                    'discount_value'    => 0.0,
                    'rule_name'         => 'Desconto Progressivo (teste)',
                    'rule_description'  => 'Nenhum desconto aplicado.',
                ];

                if (!$this->enabled || $subtotal <= 0) {
                    return $result;
                }

                // Calcula quantidade total de itens
                $totalQuantity = 0;
                foreach ($items as $item) {
                    $totalQuantity += (int) ($item['quantity'] ?? 0);
                }

                // Ordena faixas por min_quantity desc
                $sorted = $this->tiers;
                usort($sorted, fn($a, $b) => $b['min_quantity'] <=> $a['min_quantity']);

                foreach ($sorted as $tier) {
                    if ($totalQuantity >= $tier['min_quantity']) {
                        $percent = (float) $tier['discount_percent'];
                        $value = round($subtotal * ($percent / 100), 2);
                        $result['discount_percent'] = $percent;
                        $result['discount_value'] = $value;
                        $result['rule_description'] = "Desconto de {$percent}% ({$totalQuantity} itens)";
                        return $result;
                    }
                }

                return $result;
            }
        };
    }

    // ========================
    // Cenários de Desconto
    // ========================

    public function testNoDiscountWhenBelowMinimumQuantity(): void
    {
        $tiers = [
            ['min_quantity' => 3, 'discount_percent' => 5],
            ['min_quantity' => 5, 'discount_percent' => 10],
        ];
        $rule = $this->createFakeRule(true, $tiers);
        $calculator = new DiscountCalculator([$rule]);

        $items = [
            ['quantity' => 1, 'unit_value' => 100],
            ['quantity' => 1, 'unit_value' => 200],
        ];

        $result = $calculator->calculate($items);

        $this->assertEquals(300, $result['subtotal']);
        $this->assertEquals(0, $result['discount_percent']);
        $this->assertEquals(0, $result['discount_value']);
        $this->assertEquals(300, $result['total']);
    }

    public function testFirstTierDiscount(): void
    {
        $tiers = [
            ['min_quantity' => 3, 'discount_percent' => 5],
            ['min_quantity' => 5, 'discount_percent' => 10],
            ['min_quantity' => 10, 'discount_percent' => 15],
        ];
        $rule = $this->createFakeRule(true, $tiers);
        $calculator = new DiscountCalculator([$rule]);

        // 3 itens totais → 5% de desconto
        $items = [
            ['quantity' => 2, 'unit_value' => 100],
            ['quantity' => 1, 'unit_value' => 200],
        ];

        $result = $calculator->calculate($items);

        $this->assertEquals(400, $result['subtotal']);
        $this->assertEquals(5, $result['discount_percent']);
        $this->assertEquals(20, $result['discount_value']); // 400 * 5% = 20
        $this->assertEquals(380, $result['total']);
    }

    public function testSecondTierDiscount(): void
    {
        $tiers = [
            ['min_quantity' => 3, 'discount_percent' => 5],
            ['min_quantity' => 5, 'discount_percent' => 10],
            ['min_quantity' => 10, 'discount_percent' => 15],
        ];
        $rule = $this->createFakeRule(true, $tiers);
        $calculator = new DiscountCalculator([$rule]);

        // 7 itens → 10%
        $items = [
            ['quantity' => 5, 'unit_value' => 100],
            ['quantity' => 2, 'unit_value' => 250],
        ];

        $result = $calculator->calculate($items);

        $this->assertEquals(1000, $result['subtotal']); // 500 + 500
        $this->assertEquals(10, $result['discount_percent']);
        $this->assertEquals(100, $result['discount_value']);
        $this->assertEquals(900, $result['total']);
    }

    public function testThirdTierDiscount(): void
    {
        $tiers = [
            ['min_quantity' => 3, 'discount_percent' => 5],
            ['min_quantity' => 5, 'discount_percent' => 10],
            ['min_quantity' => 10, 'discount_percent' => 15],
        ];
        $rule = $this->createFakeRule(true, $tiers);
        $calculator = new DiscountCalculator([$rule]);

        // 15 itens → 15%
        $items = [
            ['quantity' => 10, 'unit_value' => 100],
            ['quantity' => 5, 'unit_value' => 200],
        ];

        $result = $calculator->calculate($items);

        $this->assertEquals(2000, $result['subtotal']); // 1000 + 1000
        $this->assertEquals(15, $result['discount_percent']);
        $this->assertEquals(300, $result['discount_value']);
        $this->assertEquals(1700, $result['total']);
    }

    public function testNoDiscountWhenDisabled(): void
    {
        $tiers = [
            ['min_quantity' => 3, 'discount_percent' => 5],
        ];
        $rule = $this->createFakeRule(false, $tiers);
        $calculator = new DiscountCalculator([$rule]);

        $items = [
            ['quantity' => 10, 'unit_value' => 100],
        ];

        $result = $calculator->calculate($items);

        $this->assertEquals(1000, $result['subtotal']);
        $this->assertEquals(0, $result['discount_percent']);
        $this->assertEquals(0, $result['discount_value']);
        $this->assertEquals(1000, $result['total']);
    }

    public function testNoDiscountWithEmptyItems(): void
    {
        $tiers = [
            ['min_quantity' => 3, 'discount_percent' => 5],
        ];
        $rule = $this->createFakeRule(true, $tiers);
        $calculator = new DiscountCalculator([$rule]);

        $result = $calculator->calculate([]);

        $this->assertEquals(0, $result['subtotal']);
        $this->assertEquals(0, $result['total']);
    }

    public function testExactBoundaryQuantity(): void
    {
        $tiers = [
            ['min_quantity' => 5, 'discount_percent' => 10],
        ];
        $rule = $this->createFakeRule(true, $tiers);
        $calculator = new DiscountCalculator([$rule]);

        // Exatamente 5 itens → deve aplicar desconto
        $items = [
            ['quantity' => 5, 'unit_value' => 100],
        ];

        $result = $calculator->calculate($items);

        $this->assertEquals(500, $result['subtotal']);
        $this->assertEquals(10, $result['discount_percent']);
        $this->assertEquals(50, $result['discount_value']);
        $this->assertEquals(450, $result['total']);
    }

    public function testAppliedRulesDescription(): void
    {
        $tiers = [
            ['min_quantity' => 3, 'discount_percent' => 5],
        ];
        $rule = $this->createFakeRule(true, $tiers);
        $calculator = new DiscountCalculator([$rule]);

        $items = [
            ['quantity' => 3, 'unit_value' => 100],
        ];

        $result = $calculator->calculate($items);

        $this->assertNotEmpty($result['applied_rules']);
        $this->assertEquals(5, $result['applied_rules'][0]['discount_percent']);
        $this->assertStringContainsString('5%', $result['applied_rules'][0]['rule_description']);
    }
}
