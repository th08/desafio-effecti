<?php

declare(strict_types=1);

namespace App\Config;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Bootstrap do Eloquent ORM standalone (fora do Laravel).
 * Configura o Capsule Manager com as credenciais do .env.
 */
class Database
{
    private static bool $booted = false;

    /**
     * Inicializa a conexão com o banco de dados via Eloquent Capsule.
     */
    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        $capsule = new Capsule();

        $capsule->addConnection([
            'driver'    => 'pgsql',
            'host'      => Environment::get('DB_HOST', 'postgres'),
            'port'      => Environment::get('DB_PORT', '5432'),
            'database'  => Environment::get('DB_DATABASE', 'desafio_effecti'),
            'username'  => Environment::get('DB_USERNAME', 'erp_user'),
            'password'  => Environment::get('DB_PASSWORD', 'erp_secret'),
            'charset'   => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix'    => '',
        ]);

        // Disponibiliza o Eloquent globalmente
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        self::$booted = true;
    }
}
