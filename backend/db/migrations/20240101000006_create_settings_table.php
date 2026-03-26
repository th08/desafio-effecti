<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Cria a tabela de configurações do sistema.
 * Armazena regras de negócio configuráveis pelo usuário.
 */
class CreateSettingsTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('settings');
        $table
            ->addColumn('key', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('value', 'text', ['null' => false])
            ->addColumn('description', 'text', ['null' => true])
            ->addColumn('type', 'string', ['limit' => 20, 'null' => false, 'default' => 'string'])
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['key'], ['unique' => true])
            ->create();
    }
}
