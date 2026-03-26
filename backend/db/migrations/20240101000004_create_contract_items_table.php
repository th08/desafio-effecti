<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Cria a tabela de itens do contrato.
 */
class CreateContractItemsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('contract_items');
        $table
            ->addColumn('contract_id', 'integer', ['null' => false])
            ->addColumn('service_id', 'integer', ['null' => false])
            ->addColumn('quantity', 'integer', ['null' => false, 'default' => 1])
            ->addColumn('unit_value', 'decimal', ['precision' => 10, 'scale' => 2, 'null' => false])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('contract_id', 'contracts', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addForeignKey('service_id', 'services', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
            ])
            ->addIndex(['contract_id'])
            ->addIndex(['service_id'])
            ->create();
    }
}
