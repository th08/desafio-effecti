#!/bin/bash
set -e

echo "Instalando dependências PHP..."
composer install --optimize-autoloader --no-interaction 2>&1

echo "Backend pronto!"
echo ""
echo "Para rodar as migrations e seeds, execute:"
echo "  docker compose exec php vendor/bin/phinx migrate -c phinx.php"
echo "  docker compose exec php vendor/bin/phinx seed:run -c phinx.php"
echo ""

echo "Iniciando PHP-FPM..."
exec "$@"
