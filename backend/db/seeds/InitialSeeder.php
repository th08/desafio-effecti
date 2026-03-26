<?php

declare(strict_types=1);

use Phinx\Seed\AbstractSeed;

/**
 * Seed inicial com configurações padrão e dados de exemplo.
 */
class InitialSeeder extends AbstractSeed
{
    public function getDependencies(): array
    {
        return [];
    }

    public function run(): void
    {
        // Configurações padrão do sistema
        $settings = $this->table('settings');

        // Verifica se já existem registros para evitar duplicação
        $exists = $this->fetchRow("SELECT COUNT(*) as count FROM settings WHERE key = 'discount_enabled'");
        if ($exists && (int) $exists['count'] > 0) {
            return;
        }

        $settings->insert([
            [
                'key'         => 'discount_enabled',
                'value'       => 'true',
                'description' => 'Habilita ou desabilita o cálculo de descontos progressivos nos contratos.',
                'type'        => 'boolean',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'key'         => 'discount_rules',
                'value'       => json_encode([
                    'tiers' => [
                        ['min_quantity' => 3, 'discount_percent' => 5],
                        ['min_quantity' => 5, 'discount_percent' => 10],
                        ['min_quantity' => 10, 'discount_percent' => 15],
                    ],
                ]),
                'description' => 'Regras de desconto progressivo por quantidade total de itens. Formato JSON com tiers (min_quantity e discount_percent).',
                'type'        => 'json',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ])->saveData();

        // Serviços de exemplo
        $services = $this->table('services');
        $services->insert([
            [
                'name'               => 'Serviço A',
                'base_monthly_value' => 100.00,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
            [
                'name'               => 'Serviço B',
                'base_monthly_value' => 250.00,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s'),
            ],
        ])->saveData();
    }
}
