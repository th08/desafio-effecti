<?php

/**
 * Configuração do Phinx para migrations do banco de dados.
 * Lê as credenciais do .env automaticamente.
 */

require __DIR__ . '/vendor/autoload.php';

// Carrega variáveis de ambiente (se o .env existir — no Docker vem via env_file)
$envPath = __DIR__ . '/../';
if (file_exists($envPath . '.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable($envPath);
    $dotenv->load();
}

return [
    'paths' => [
        'migrations' => '%%PHINX_CONFIG_DIR%%/db/migrations',
        'seeds' => '%%PHINX_CONFIG_DIR%%/db/seeds',
    ],
    'environments' => [
        'default_migration_table' => 'phinx_migrations',
        'default_environment' => 'development',
        'development' => [
            'adapter' => 'pgsql',
            'host' => $_ENV['DB_HOST'] ?? 'postgres',
            'name' => $_ENV['DB_DATABASE'] ?? 'desafio_effecti',
            'user' => $_ENV['DB_USERNAME'] ?? 'erp_user',
            'pass' => $_ENV['DB_PASSWORD'] ?? 'erp_secret',
            'port' => $_ENV['DB_PORT'] ?? 5432,
            'charset' => 'utf8',
        ],
    ],
    'version_order' => 'creation',
];
