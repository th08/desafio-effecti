<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Cria a tabela de clientes.
 */
class CreateClientsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('clients');
        $table
            ->addColumn('name', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('document', 'string', ['limit' => 14, 'null' => false])
            ->addColumn('email', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('status', 'string', ['limit' => 1, 'null' => false, 'default' => 'A'])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['document'], ['unique' => true])
            ->addIndex(['email'], ['unique' => true])
            ->addIndex(['status'])
            ->create();
    }
}
