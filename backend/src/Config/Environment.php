<?php

declare(strict_types=1);

namespace App\Config;

use Dotenv\Dotenv;

/**
 * Carrega e gerencia variáveis de ambiente a partir do .env.
 */
class Environment
{
    private static bool $loaded = false;

    /**
     * Carrega o arquivo .env do diretório raiz do projeto.
     */
    public static function load(): void
    {
        if (self::$loaded) {
            return;
        }

        // Tenta carregar .env do diretório raiz (em dev local).
        // Em Docker, as variáveis já são injetadas via env_file.
        $envPath = __DIR__ . '/../../../';
        if (file_exists($envPath . '.env')) {
            $dotenv = Dotenv::createImmutable($envPath);
            $dotenv->load();
        }

        self::$loaded = true;
    }

    /**
     * Retorna o valor de uma variável de ambiente.
     */
    public static function get(string $key, string $default = ''): string
    {
        return $_ENV[$key] ?? $default;
    }
}
