<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Cria a tabela de histórico de alterações do contrato.
 */
class CreateContractHistoryTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('contract_history');
        $table
            ->addColumn('contract_id', 'integer', ['null' => false])
            ->addColumn('action', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('changed_data', 'jsonb', ['null' => true])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('contract_id', 'contracts', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
            ])
            ->addIndex(['contract_id'])
            ->create();
    }
}
