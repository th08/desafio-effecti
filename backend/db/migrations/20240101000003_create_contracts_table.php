<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Cria a tabela de contratos.
 */
class CreateContractsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('contracts');
        $table
            ->addColumn('client_id', 'integer', ['null' => false])
            ->addColumn('start_date', 'date', ['null' => false])
            ->addColumn('end_date', 'date', ['null' => true])
            ->addColumn('status', 'string', ['limit' => 1, 'null' => false, 'default' => 'A'])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('client_id', 'clients', 'id', [
                'delete' => 'RESTRICT',
                'update' => 'CASCADE',
            ])
            ->addIndex(['client_id'])
            ->addIndex(['status'])
            ->create();
    }
}
